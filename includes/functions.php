<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$CACHE_DIR  = __DIR__ . '/../cache';
$CACHE_FILE = $CACHE_DIR . '/news_cache.json';
$CACHE_TTL  = 55;

$RSS_SOURCES = [
    ['name' => 'VnExpress',   'url' => 'https://vnexpress.net/rss/tin-moi-nhat.rss',               'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => 'Tuổi Trẻ',    'url' => 'https://tuoitre.vn/rss/tin-moi-nhat.rss',                  'lang' => 'vi', 'type' => 'news', 'region' => 'south'],
    ['name' => 'Dân trí',     'url' => 'https://dantri.com.vn/rss/tin-moi-nhat.rss',               'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => 'Vietnamnet',  'url' => 'https://vietnamnet.vn/rss/tin-moi-nhat.rss',               'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => 'Thanh Niên',  'url' => 'https://thanhnien.vn/rss/trang-chu.rss',                   'lang' => 'vi', 'type' => 'news', 'region' => 'south'],
    ['name' => 'Znews',       'url' => 'https://znews.vn/rss/tin-moi-nhat.rss',                   'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => 'VietNamPlus', 'url' => 'https://www.vietnamplus.vn/rss/tin-moi.rss',               'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => '24h',         'url' => 'https://www.24h.com.vn/upload/rss/tintuctrongngay.rss',    'lang' => 'vi', 'type' => 'news', 'region' => 'north'],
    ['name' => 'BBC News',    'url' => 'https://feeds.bbci.co.uk/news/world/rss.xml',               'lang' => 'en', 'type' => 'news'],
    ['name' => 'TechCrunch',  'url' => 'https://techcrunch.com/feed/',                              'lang' => 'en', 'type' => 'news'],
    ['name' => 'Reddit',      'url' => 'https://www.reddit.com/r/all/.rss',                         'lang' => 'en', 'type' => 'social'],
    ['name' => 'HN Frontpage','url' => 'https://hnrss.org/frontpage',                               'lang' => 'en', 'type' => 'social'],
];

$LOCATION_REGIONS = [
    'north' => ['hà nội','hải phòng','quảng ninh','thái bình','nam định','hải dương','bắc ninh','bắc giang','hà nam','ninh bình','hưng yên','vĩnh phúc','phú thọ','thái nguyên','tuyên quang','lào cai','yên bái','sơn la','điện biên','hòa bình','lai châu','hà giang','cao bằng','bắc kạn','lạng sơn','quảng ninh'],
    'south' => ['hồ chí minh','hcm','cần thơ','đồng nai','bình dương','bà rịa','vũng tàu','an giang','kiên giang','cà mau','bạc liêu','sóc trăng','trà vinh','vĩnh long','bến tre','tiền giang','long an','tây ninh','bình phước','đồng tháp','hậu giang'],
    'central' => ['đà nẵng','huế','khánh hòa','nha trang','đắk lắk','đắk nông','lâm đồng','đà lạt','gia lai','kon tum','bình định','quy nhơn','quảng nam','quảng ngãi','quảng bình','quảng trị','phú yên','bình thuận','ninh thuận','thanh hóa','nghệ an','hà tĩnh'],
];

// ===== CACHE =====
function get_cached_news() {
    global $CACHE_FILE, $CACHE_TTL;
    if (file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
        $data = json_decode(file_get_contents($CACHE_FILE), true);
        if ($data && isset($data['articles'])) return $data;
    }
    return null;
}

function set_cached_news($data) {
    global $CACHE_FILE;
    $dir = dirname($CACHE_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($CACHE_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// ===== RSS =====
function fetch_rss($url, $timeout = 8) {
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'user_agent' => 'NewsHub/1.0']]);
    $xml = @file_get_contents($url, false, $ctx);
    if (!$xml) return [];
    libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    if (!$feed) return [];
    $articles = [];
    if (isset($feed->channel->item)) {
        foreach ($feed->channel->item as $item) $articles[] = parse_rss_item($item);
    } elseif (isset($feed->entry)) {
        foreach ($feed->entry as $entry) $articles[] = parse_atom_entry($entry);
    }
    return $articles;
}

function parse_rss_item($item) {
    $ns = $item->children('http://purl.org/rss/1.0/modules/content/');
    $desc = (string)$item->description;
    $content = (string)$ns->encoded;
    $thumbnail = '';
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content ?: $desc, $m)) $thumbnail = $m[1];
    return [
        'title'=>trim((string)$item->title), 'description'=>strip_tags(strlen($content)>10?$content:$desc),
        'link'=>trim((string)$item->link), 'pubDate'=>strtotime((string)$item->pubDate)?:time(),
        'thumbnail'=>$thumbnail, 'source'=>'', 'category'=>'',
    ];
}

function parse_atom_entry($entry) {
    $link = '';
    if (isset($entry->link['href'])) $link = (string)$entry->link['href'];
    elseif (isset($entry->link)) $link = (string)$entry->link;
    $content = (string)($entry->content ?: $entry->summary ?: '');
    $thumbnail = '';
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) $thumbnail = $m[1];
    return [
        'title'=>trim((string)$entry->title), 'description'=>strip_tags($content),
        'link'=>$link, 'pubDate'=>strtotime((string)($entry->updated?:$entry->published))?:time(),
        'thumbnail'=>$thumbnail, 'source'=>'', 'category'=>'',
    ];
}

// ===== KEYWORDS =====
function extract_keywords($text, $lang = 'vi', $max = 10) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $stop = get_stopwords($lang);
    $filtered = array_filter($words, fn($w) => mb_strlen($w, 'UTF-8') > 2 && !in_array($w, $stop));
    $counts = array_count_values($filtered);
    arsort($counts);
    return array_slice($counts, 0, $max);
}

function get_stopwords($lang) {
    $vi = ['và','của','có','được','cho','trong','các','một','người','không','tại','với','về','như','này','khi','làm','sau','đến','ra','năm','đã','sẽ','đang','còn','để','từ','bị','hoặc','là','ở','trên','những','bạn','mà','lại','rằng','nếu','thì','nó','họ','tôi','qua','vào','theo','cùng','vì','nên','mới','rất','đây','nhiều','hơn','khiến','gì','ngay','đó','vẫn','nữa','ấy','đều','chỉ','đâu','cả','phải','chưa','chính'];
    $en = ['the','and','for','are','but','not','you','all','can','had','her','was','one','our','out','has','have','been','some','them','than','its','over','such','that','this','with','will','would','what','which','their','there','about','into','could','after','also','more','very','just','from','they','been','said','when','who','how','make','than','then','each'];
    return $lang === 'vi' ? $vi : $en;
}

// ===== CATEGORY =====
function classify_category($title, $desc) {
    $text = mb_strtolower($title.' '.$desc, 'UTF-8');
    $cats = [
        'Công nghệ'=>['ai','tech','công nghệ','software','app','iphone','android','digital','startup','blockchain','machine learning','data','cyber','internet','số','trí tuệ','robot','coding','programming','apple','google','microsoft','chip','bán dẫn'],
        'Kinh doanh'=>['kinh tế','business','stock','market','đầu tư','tài chính','bank','trade','thương mại','doanh nghiệp','cổ phiếu','giá vàng','bitcoin','crypto','thuế','ngân hàng','bất động sản','chứng khoán'],
        'Thể thao'=>['sport','football','bóng đá','tennis','olympic','world cup','vleague','bóng rổ','bơi','esport','cầu lông','bóng chuyền','võ','huy chương'],
        'Giải trí'=>['entertainment','movie','phim','music','âm nhạc','show','ca sĩ','diễn viên','concert','game','twitch','youtube','tiktok','rapper'],
        'Sức khỏe'=>['health','sức khỏe','y tế','bệnh','vaccine','covid','thuốc','bệnh viện','dịch','khám','điều trị','dinh dưỡng','thuốc'],
        'Giáo dục'=>['education','giáo dục','học sinh','sinh viên','trường học','đại học','thi cử','tuyển sinh','du học','lớp học','điểm thi'],
        'Chính trị'=>['political','chính trị','president','tổng thống','thủ tướng','quốc hội','đảng','chính phủ','bầu cử','quốc hội','luật','nghị quyết'],
        'Thế giới'=>['world','quốc tế','thế giới','nước ngoài','chiến tranh','war','peace','diplomat','ngoại giao','liên hợp quốc','nato','trung quốc','mỹ','nga','ukraine'],
    ];
    $scores = [];
    foreach ($cats as $cat => $kw) { foreach ($kw as $k) { if (mb_strpos($text, $k) !== false) $scores[$cat] = ($scores[$cat] ?? 0) + 2; } }
    $tl = mb_strtolower($title, 'UTF-8');
    foreach ($cats as $cat => $kw) { foreach ($kw as $k) { if (mb_strpos($tl, $k) !== false) $scores[$cat] = ($scores[$cat] ?? 0) + 3; } }
    if (empty($scores)) return 'Tin tức';
    arsort($scores); return key($scores);
}

// ===== BREAKING NEWS =====
function score_breaking($art, $user_region = '') {
    global $RSS_SOURCES;
    $now = time();
    $score = 0;
    // recency
    $age = ($now - $art['pubDate']) / 3600;
    if ($age < 1) $score += 10;
    elseif ($age < 3) $score += 6;
    elseif ($age < 6) $score += 3;
    // vietnamese priority
    if (($art['lang'] ?? 'en') === 'vi') $score += 8;
    if (($art['source'] ?? '') !== '' && in_array($art['source'], ['VnExpress','Tuổi Trẻ','Dân trí','Vietnamnet','Thanh Niên','VietNamPlus','24h','Znews'])) $score += 3;
    // category boost
    $boost_cats = ['Chính trị','Thế giới','Kinh doanh'];
    if (in_array($art['category'] ?? '', $boost_cats)) $score += 4;
    // title length (short = more breaking)
    $tlen = mb_strlen($art['title'] ?? '', 'UTF-8');
    if ($tlen > 0 && $tlen < 60) $score += 2;
    // has thumbnail
    if (!empty($art['thumbnail'])) $score += 1;
    // user region boost — prefer local sources
    if ($user_region && ($art['region'] ?? '') === $user_region) $score += 5;
    return $score;
}

function pick_breaking($articles, $count = 10, $user_region = '') {
    $scored = [];
    foreach ($articles as $a) {
        $a['_score'] = score_breaking($a, $user_region);
        $scored[] = $a;
    }
    usort($scored, fn($a,$b) => $b['_score'] - $a['_score']);
    // deduplicate by title similarity
    $seen = []; $result = [];
    foreach ($scored as $a) {
        $key = mb_substr($a['title'], 0, 40, 'UTF-8');
        if (!isset($seen[$key])) { $seen[$key] = true; $result[] = $a; }
        if (count($result) >= $count) break;
    }
    return $result;
}

// ===== TRENDS =====
function fetch_trends() {
    $url = 'https://trends.google.com/trending/rss?geo=VN';
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'NewsHub/1.0']]);
    $xml = @file_get_contents($url, false, $ctx);
    if (!$xml) return [];
    libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    if (!$feed || !isset($feed->channel->item)) return [];
    $trends = [];
    foreach ($feed->channel->item as $item) {
        $title = trim((string)$item->title);
        if ($title) $trends[] = ['title'=>$title, 'traffic'=>(string)$item->children('https://trends.google.com/trending')->approximate_traffic?:''];
    }
    return array_slice($trends, 0, 15);
}

// ===== FINANCE & COMMODITIES =====
function fetch_finance() {
    $cache_file = __DIR__ . '/../cache/finance_cache.json';
    if (file_exists($cache_file) && (time()-filemtime($cache_file)) < 300) {
        return json_decode(file_get_contents($cache_file), true);
    }
    $data = ['indices'=>[], 'gold'=>[], 'currency'=>[], 'commodities'=>[], 'petrol'=>[]];

    // Indices
    foreach ([['^VNINDEX','VN-INDEX'],['^HNX','HNX']] as [$sym,$name]) {
        $d = fetch_yahoo_chart($sym); if ($d) { $d['symbol']=$name; $data['indices'][] = $d; }
    }

    // Gold domestic
    $gold_data = fetch_gold_price();
    if ($gold_data) $data['gold'] = $gold_data;
    // Gold world
    $gc = fetch_yahoo_chart('GC=F'); if ($gc) { $gc['symbol']='GC=F'; $gc['name']='Gold Futures'; $data['commodities'][] = $gc; }
    // Silver
    $si = fetch_yahoo_chart('SI=F'); if ($si) { $si['symbol']='SI=F'; $si['name']='Silver'; $data['commodities'][] = $si; }

    // Currency
    $usd = fetch_yahoo_chart('USDVND=X'); if ($usd) { $usd['symbol']='USD/VND'; $data['currency'][] = $usd; }

    // Oil
    $brent = fetch_yahoo_chart('BZ=F'); if ($brent) { $brent['symbol']='Brent'; $data['commodities'][] = $brent; }
    $wti = fetch_yahoo_chart('CL=F'); if ($wti) { $wti['symbol']='WTI'; $data['commodities'][] = $wti; }

    // Petrol Vietnam (from Petrolimex API or hardcoded with timestamp)
    $data['petrol'] = fetch_vn_petrol();

    file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_UNICODE));
    return $data;
}

function fetch_vn_petrol() {
    $url = 'https://www.petrolimex.com.vn/vi/trang-chu/gia-xang-dau.html';
    $ctx = stream_context_create(['http'=>['timeout'=>6, 'user_agent'=>'NewsHub/1.0']]);
    $html = @file_get_contents($url, false, $ctx);
    if ($html && preg_match_all('/<td[^>]*>([^<]+)<\/td>/', $html, $m)) {
        // Try to parse table from Petrolimex
        $prices = [];
        $rows = array_chunk($m[1], 4);
        foreach ($rows as $r) {
            if (count($r)>=2) {
                $name = trim(strip_tags($r[0]));
                $price = (float)str_replace(['.',','],['','.'],preg_replace('/[^0-9.,]/','',$r[1]??''));
                if ($name && $price > 0) $prices[] = ['name'=>$name, 'price'=>$price];
            }
        }
        if (count($prices)>=3) return array_slice($prices, 0, 6);
    }
    // Fallback: return latest known prices
    return [
        ['name'=>'Xăng RON 95-IV', 'price'=>23480],
        ['name'=>'Xăng E5 RON 92-II', 'price'=>22570],
        ['name'=>'Dầu DO 0.001S-V', 'price'=>21210],
        ['name'=>'Dầu hỏa', 'price'=>20890],
        ['name'=>'Dầu mazut 180CST 3.5S', 'price'=>17230],
    ];
}

function fetch_yahoo_chart($symbol, $range='5d', $interval='1d') {
    $url = 'https://query1.finance.yahoo.com/v8/finance/chart/'.urlencode($symbol)."?range={$range}&interval={$interval}";
    $ctx = stream_context_create(['http'=>['timeout'=>6, 'header'=>"User-Agent: NewsHub/1.0\r\nAccept: application/json\r\n"]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    $res = json_decode($json, true);
    if (!$res || !isset($res['chart']['result'][0])) return null;
    $r = $res['chart']['result'][0];
    $meta = $r['meta'] ?? [];
    $close = ($r['indicators']['quote'][0]['close'] ?? []);
    $prices = array_filter($close, fn($v) => !is_null($v));
    $prices = array_values($prices);
    $price = $prices[count($prices)-1] ?? 0;
    $prev = $meta['chartPreviousClose'] ?? $price;
    $change = $price - $prev;
    $pct = $prev > 0 ? round(($change/$prev)*100, 2) : 0;
    return [
        'symbol'=>str_replace('^','',$symbol), 'name'=>$meta['symbol']??$symbol,
        'price'=>round($price,2), 'prevClose'=>round($prev,2),
        'change'=>round($change,2), 'changePercent'=>$pct,
        'prices'=>array_map(fn($v)=>round($v,2), $prices),
    ];
}

function fetch_gold_price() {
    $url = 'https://api.btngan.vn/api/price-gold';
    $ctx = stream_context_create(['http'=>['timeout'=>6, 'user_agent'=>'NewsHub/1.0']]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) {
        // fallback: try SJC website
        return fetch_gold_sjc_fallback();
    }
    $data = json_decode($json, true);
    if (!$data || !isset($data['data'])) return fetch_gold_sjc_fallback();
    $result = [];
    foreach ($data['data'] as $item) {
        $result[] = [
            'name'=> $item['name'] ?? $item['type'] ?? '',
            'buy' => $item['buy'] ?? $item['mua'] ?? 0,
            'sell'=> $item['sell'] ?? $item['ban'] ?? 0,
        ];
    }
    return $result;
}

function fetch_gold_sjc_fallback() {
    $url = 'https://sjc.com.vn/xml/tygiavang.xml';
    $ctx = stream_context_create(['http'=>['timeout'=>6, 'user_agent'=>'NewsHub/1.0']]);
    $xml = @file_get_contents($url, false, $ctx);
    if (!$xml) return [];
    libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    if (!$feed) return [];
    $result = [];
    foreach ($feed->children() as $city) {
        foreach ($city->children() as $item) {
            $attrs = $item->attributes();
            $result[] = [
                'name'=> (string)($attrs['type'] ?? ''),
                'buy' => (float)str_replace(',','',(string)($attrs['buy'] ?? 0)),
                'sell'=> (float)str_replace(',','',(string)($attrs['sell'] ?? 0)),
            ];
        }
    }
    return $result;
}

// ===== REGION DETECTION =====
function detect_region($user_city) {
    global $LOCATION_REGIONS;
    $user_city = mb_strtolower(trim($user_city), 'UTF-8');
    foreach ($LOCATION_REGIONS as $region => $cities) {
        foreach ($cities as $c) {
            if (mb_strpos($user_city, $c) !== false || mb_strpos($c, $user_city) !== false) return $region;
        }
    }
    // Check individual city name parts
    $parts = preg_split('/[\s,]+/', $user_city);
    foreach ($parts as $p) {
        $p = trim($p);
        if (!$p) continue;
        foreach ($LOCATION_REGIONS as $region => $cities) {
            if (in_array($p, $cities)) return $region;
        }
    }
    return '';
}

// ===== CACHE-ONLY READER (no fetching) =====
function load_cached_data() {
    global $CACHE_FILE;
    if (file_exists($CACHE_FILE)) {
        $data = json_decode(file_get_contents($CACHE_FILE), true);
        if ($data && isset($data['articles'])) return $data;
    }
    return [
        'articles'=>[], 'breaking'=>[], 'source_stats'=>[], 'category_stats'=>[],
        'top_keywords'=>[], 'timeline'=>[], 'trends'=>[], 'finance'=>[],
        'source_types'=>[], 'total'=>0, 'updated_at'=>time(),
        'weather'=>[], 'social_trends'=>[], 'world_clocks'=>get_world_clocks(),
        'user_city'=>'', 'user_region'=>'',
    ];
}

// ===== MAIN AGGREGATOR (for cron) =====
function fetch_all_news($user_city = '') {
    global $RSS_SOURCES;
    $cached = get_cached_news();
    if ($cached) {
        if ($user_city) {
            $cached['weather'] = fetch_weather($user_city);
            $cached['user_city'] = $user_city;
            $cached['user_region'] = detect_region($user_city);
        }
        return $cached;
    }

    $all_articles = []; $source_stats = []; $keywords_pool = []; $source_type_map = [];

    foreach ($RSS_SOURCES as $source) {
        $articles = fetch_rss($source['url']);
        $source_type_map[$source['name']] = $source['type'];
        foreach ($articles as &$art) {
            $art['source']=$source['name']; $art['lang']=$source['lang']; $art['type']=$source['type']; $art['region']=$source['region']??'';
            $art['category']=classify_category($art['title'],$art['description']);
            $art['id']=md5($art['link']);
            $all_articles[]=$art;
            $source_stats[$source['name']]=($source_stats[$source['name']]??0)+1;
            $kw=extract_keywords($art['title'].' '.$art['description'],$source['lang'],5);
            foreach ($kw as $word=>$count) $keywords_pool[$word]=($keywords_pool[$word]??0)+$count;
        }
    }

    usort($all_articles, fn($a,$b)=>$b['pubDate']-$a['pubDate']);
    arsort($keywords_pool);

    $category_stats = [];
    foreach ($all_articles as $art) $category_stats[$art['category']]=($category_stats[$art['category']]??0)+1;

    $user_region = detect_region($user_city);
    $breaking = pick_breaking($all_articles, 12, $user_region);

    $result = [
        'articles'=>$all_articles, 'breaking'=>$breaking,
        'source_stats'=>$source_stats, 'category_stats'=>$category_stats,
        'top_keywords'=>array_slice($keywords_pool,0,30),
        'timeline'=>build_timeline($all_articles), 'trends'=>fetch_trends(),
        'finance'=>fetch_finance(),
        'source_types'=>$source_type_map,
        'total'=>count($all_articles), 'updated_at'=>time(),
    ];

    $result['weather'] = fetch_weather($user_city);
    $result['social_trends'] = fetch_social_trends();
    $result['world_clocks'] = get_world_clocks();
    $result['user_city'] = $user_city;
    $result['user_region'] = $user_region;

    set_cached_news($result);
    return $result;
}

function build_timeline($articles) {
    $now = time(); $tl = [];
    for ($i=23; $i>=0; $i--) {
        $slot=$now-$i*3600; $label=date('H',$slot).'h'; $count=0;
        foreach ($articles as $a) { if ($a['pubDate']>=$slot && $a['pubDate']<$slot+3600) $count++; }
        $tl[]=['label'=>$label,'count'=>$count,'timestamp'=>$slot];
    }
    return $tl;
}

// ===== WEATHER =====
function fetch_weather($user_city = '') {
    $cache = __DIR__ . '/../cache/weather_cache.json';
    if (file_exists($cache) && (time()-filemtime($cache))<600) return json_decode(file_get_contents($cache),true);
    $cities = ['Hanoi','Ho+Chi+Minh+City','Da+Nang','Can+Tho'];
    if ($user_city && !in_array($user_city, $cities)) $cities[] = $user_city;
    $data = [];
    foreach ($cities as $city) {
        $url = "https://wttr.in/{$city}?format=j1";
        $ctx = stream_context_create(['http'=>['timeout'=>5,'user_agent'=>'NewsHub/1.0']]);
        $json = @file_get_contents($url,false,$ctx);
        if ($json) {
            $d = json_decode($json,true);
            if ($d && isset($d['current_condition'][0])) {
                $cc = $d['current_condition'][0];
                $fc = $d['weather']??[];
                $entry = [
                    'city' => str_replace(['+','Ho%20Chi%20Minh%20City'],[' ','HCM'],$city),
                    'temp' => $cc['temp_C']??'--',
                    'feels' => $cc['FeelsLikeC']??'--',
                    'humidity' => $cc['humidity']??'--',
                    'desc' => $cc['weatherDesc'][0]['value']??'',
                    'code' => $cc['weatherCode']??'',
                    'wind' => $cc['windspeedKmph']??'--',
                    'icon' => $cc['weatherIconUrl'][0]['value']??'',
                    'forecast' => array_map(fn($f)=>[
                        'date'=>$f['date']??'', 'max'=>$f['maxtempC']??'', 'min'=>$f['mintempC']??'',
                        'desc'=>$f['hourly'][0]['weatherDesc'][0]['value']??'',
                        'code'=>$f['hourly'][0]['weatherCode']??'',
                    ], array_slice($fc,0,5)),
                ];
                if ($user_city && stripos($entry['city'], str_replace(['+'],' ',$user_city)) !== false) $entry['user_location'] = true;
                $data[] = $entry;
            }
        }
    }
    file_put_contents($cache,json_encode($data,JSON_UNESCAPED_UNICODE));
    return $data;
}

// ===== SOCIAL TRENDS =====
function fetch_social_trends() {
    $cache = __DIR__ . '/../cache/social_cache.json';
    if (file_exists($cache) && (time()-filemtime($cache))<600) return json_decode(file_get_contents($cache),true);
    $data = ['youtube'=>[],'tiktok'=>[],'facebook'=>[],'google'=>fetch_trends()];

    // YouTube via Invidious API (free, no key)
    $yt = @file_get_contents('https://inv.nadeko.net/api/v1/trending?type=videos&region=VN',false,
        stream_context_create(['http'=>['timeout'=>5,'user_agent'=>'NewsHub/1.0']]));
    if ($yt) {
        $y = json_decode($yt,true);
        if ($y && is_array($y)) {
            foreach (array_slice($y,0,10) as $v) {
                $data['youtube'][] = [
                    'title' => $v['title']??'',
                    'channel' => $v['author']??'',
                    'views' => $v['viewCount']??0,
                    'url' => 'https://youtube.com/watch?v='.($v['videoId']??''),
                    'videoId' => $v['videoId']??'',
                ];
            }
        }
    }

    // TikTok via public trends API alternative
    $tt = @file_get_contents('https://www.tiktok.com/trending?lang=vi',false,
        stream_context_create(['http'=>['timeout'=>4,'user_agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']]));
    if ($tt) {
        // Extract trending hashtags from HTML
        preg_match_all('/#(\w+)/', $tt, $m);
        $hashtags = array_unique(array_slice($m[1]??[],0,15));
        foreach ($hashtags as $h) $data['tiktok'][] = ['hashtag'=>$h, 'url'=>"https://www.tiktok.com/tag/{$h}"];
    }
    // TikTok fallback: known current trends
    if (empty($data['tiktok'])) {
        $fallback = ['xuhuong','fyp','tiktokvietnam','trending','viral','amnhac','nhay','thoitrang','amthuc','dulich'];
        foreach ($fallback as $h) $data['tiktok'][] = ['hashtag'=>$h, 'url'=>"https://www.tiktok.com/tag/{$h}"];
    }

    // Facebook trends (limited public data)
    $fb = @file_get_contents('https://www.facebook.com/salestools/trends/',false,
        stream_context_create(['http'=>['timeout'=>4,'user_agent'=>'Mozilla/5.0','header'=>"Accept-Language: vi-VN,vi;q=0.9\r\n"]]));
    if ($fb) {
        preg_match_all('/<span[^>]*class="[^"]*trend[^"]*"[^>]*>([^<]+)<\/span>/i', $fb, $m);
        foreach (array_slice($m[1]??[],0,10) as $t) { $t=trim(strip_tags($t)); if($t) $data['facebook'][]=['topic'=>$t]; }
    }
    if (empty($data['facebook'])) {
        $data['facebook'] = [['topic'=>'Đang cập nhật...']];
    }

    file_put_contents($cache,json_encode($data,JSON_UNESCAPED_UNICODE));
    return $data;
}

// ===== WORLD CLOCKS =====
function get_world_clocks() {
    $zones = [
        ['city'=>'Hà Nội','tz'=>'Asia/Ho_Chi_Minh','flag'=>'🇻🇳','offset'=>7],
        ['city'=>'Tokyo','tz'=>'Asia/Tokyo','flag'=>'🇯🇵','offset'=>9],
        ['city'=>'Seoul','tz'=>'Asia/Seoul','flag'=>'🇰🇷','offset'=>9],
        ['city'=>'Bắc Kinh','tz'=>'Asia/Shanghai','flag'=>'🇨🇳','offset'=>8],
        ['city'=>'Singapore','tz'=>'Asia/Singapore','flag'=>'🇸🇬','offset'=>8],
        ['city'=>'Dubai','tz'=>'Asia/Dubai','flag'=>'🇦🇪','offset'=>4],
        ['city'=>'Moscow','tz'=>'Europe/Moscow','flag'=>'🇷🇺','offset'=>3],
        ['city'=>'Berlin','tz'=>'Europe/Berlin','flag'=>'🇩🇪','offset'=>2],
        ['city'=>'London','tz'=>'Europe/London','flag'=>'🇬🇧','offset'=>1],
        ['city'=>'New York','tz'=>'America/New_York','flag'=>'🇺🇸','offset'=>-4],
        ['city'=>'Chicago','tz'=>'America/Chicago','flag'=>'🇺🇸','offset'=>-5],
        ['city'=>'Los Angeles','tz'=>'America/Los_Angeles','flag'=>'🇺🇸','offset'=>-7],
        ['city'=>'Sydney','tz'=>'Australia/Sydney','flag'=>'🇦🇺','offset'=>10],
        ['city'=>'São Paulo','tz'=>'America/Sao_Paulo','flag'=>'🇧🇷','offset'=>-3],
    ];
    $now = time();
    foreach ($zones as &$z) {
        $dt = new DateTime('now', new DateTimeZone($z['tz']));
        $z['time'] = $dt->format('H:i');
        $z['date'] = $dt->format('d/m/Y');
        $z['hour'] = (int)$dt->format('H');
        $z['min'] = (int)$dt->format('i');
        $z['sec'] = (int)$dt->format('s');
        $z['is_day'] = $z['hour'] >= 6 && $z['hour'] < 18;
    }
    return $zones;
}
