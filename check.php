<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Kiểm tra hệ thống</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Inter',-apple-system,sans-serif;background:#0d0f12;color:#e4e6ea;padding:30px 20px}
  .wrap{max-width:760px;margin:0 auto}
  h1{font-size:1.3rem;font-weight:700;letter-spacing:-.02em;margin-bottom:2px}
  .sub{color:#8a8f99;font-size:.78rem;margin-bottom:20px}
  .card{background:#15181d;border:1px solid #1e2228;border-radius:8px;padding:16px 18px;margin-bottom:10px}
  .card h2{font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#8a8f99;margin-bottom:10px}
  .row{display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1a1e24;font-size:.78rem}
  .row:last-child{border-bottom:none}
  .row .l{color:#b0b4ba}
  .row .v{font-weight:500}
  .ok{color:#22c55e}
  .warn{color:#f59e0b}
  .err{color:#ef4444}
  .summary{display:flex;gap:8px;margin-bottom:16px}
  .sum-card{flex:1;background:#15181d;border:1px solid #1e2228;border-radius:8px;padding:12px;text-align:center}
  .sum-card .n{font-size:1.6rem;font-weight:700}
  .sum-card .l{font-size:.65rem;color:#8a8f99;text-transform:uppercase;letter-spacing:.05em;margin-top:2px}
  a{color:#3b82f6;text-decoration:none}
  a:hover{text-decoration:underline}
  .tip{margin-top:10px;padding:10px 12px;background:#1a1e24;border-radius:6px;font-size:.74rem;color:#8a8f99;line-height:1.5}
  .tip i{margin-right:5px}
</style>
</head>
<body>
<div class="wrap">

<h1>🔍 NewsHub — Kiểm tra hệ thống</h1>
<p class="sub">Kiểm tra môi trường, quyền, kết nối, cache &amp; cron</p>

<?php
require_once __DIR__ . '/includes/functions.php';

$ok = $warn = $fail = 0;
$checks = [];

function chk($pass, $label, $detail = '') {
    global $ok, $warn, $fail, $checks;
    if ($pass === true) { $cls = 'ok'; $ok++; $detail = $detail ?: '✓ OK'; }
    elseif ($pass === null) { $cls = 'warn'; $warn++; $detail = $detail ?: '⚠ Cảnh báo'; }
    else { $cls = 'err'; $fail++; $detail = $detail ?: '✗ FAIL'; }
    $checks[] = [$label, $cls, $detail];
}

function section($title) {
    echo '<div class="card"><h2>' . $title . '</h2>';
    global $checks;
    foreach ($checks as $c) {
        echo '<div class="row"><span class="l">' . htmlspecialchars($c[0]) . '</span><span class="v ' . $c[1] . '">' . htmlspecialchars($c[2]) . '</span></div>';
    }
    $checks = [];
    echo '</div>';
}

// ===== 1. PHP ENVIRONMENT =====
chk(PHP_VERSION_ID >= 80000, 'Phiên bản PHP', PHP_VERSION);
chk(extension_loaded('simplexml'), 'simplexml');
chk(extension_loaded('mbstring'), 'mbstring');
chk(extension_loaded('json'), 'json');
chk(function_exists('file_get_contents'), 'file_get_contents');
chk(ini_get('allow_url_fopen') || extension_loaded('curl'), 'allow_url_fopen / curl');
$mem = ini_get('memory_limit');
chk(ini_get('max_execution_time') >= 30, 'max_execution_time', ini_get('max_execution_time') . 's');
chk(preg_match('/^\d+[MG]$/i', $mem) && (int)$mem >= 64, 'memory_limit', $mem);
section('1. Môi trường PHP');

// ===== 2. FILE & DIRECTORY =====
$root = __DIR__;
foreach (['cache', 'logs'] as $d) {
    $p = $root . '/' . $d;
    chk(is_dir($p), "Thư mục $d", is_dir($p) ? (is_writable($p) ? 'Có quyền ghi' : 'Không có quyền ghi') : 'Không tồn tại');
}
$req = ['index.php','api.php','cron.php','tv.php','check.php','includes/functions.php','assets/js/app.js','assets/css/style.css'];
foreach ($req as $f) chk(file_exists($root . '/' . $f), "File $f");
section('2. File & thư mục');

// ===== 3. CACHE DATA =====
$cacheDir = $root . '/cache';
foreach ([
    'news_cache.json' => 'Cache tin tức',
    'finance_cache.json' => 'Cache tài chính',
    'weather_cache.json' => 'Cache thời tiết',
    'social_cache.json' => 'Cache mạng xã hội',
] as $f => $l) {
    $p = $cacheDir . '/' . $f;
    if (!file_exists($p)) { chk(false, $l, 'Chưa có'); continue; }
    $age = time() - filemtime($p);
    $str = $age < 60 ? 'Vài giây' : sprintf('%.0f phút', $age / 60);
    chk($age < 180, $l, $str . ' trước');
}
$hasNews = file_exists($cacheDir . '/news_cache.json');
if (!$hasNews) chk(null, '→ Chạy cron:', 'php cron.php');
section('3. Cache');

// ===== 4. NETWORK =====
foreach ([
    'https://vnexpress.net' => 'VnExpress',
    'https://tuoitre.vn' => 'Tuổi Trẻ',
    'https://query1.finance.yahoo.com' => 'Yahoo Finance',
    'https://wttr.in' => 'wttr.in (thời tiết)',
] as $url => $l) {
    $ctx = stream_context_create(['http' => ['timeout' => 4, 'user_agent' => 'NewsHub/1.0']]);
    $h = @get_headers($url, 0, $ctx);
    $code = $h ? (explode(' ', $h[0])[1] ?? 0) : 0;
    chk($code >= 200 && $code < 400, $l, "HTTP $code");
}
section('4. Kết nối mạng');

// ===== 5. DOCKER & CRON =====
$inDocker = file_exists('/.dockerenv');
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
chk(true, 'Môi trường', $inDocker ? 'Docker container' : ($isWindows ? 'Windows host' : 'Linux host'));
if ($inDocker) {
    $proc = @shell_exec('ps aux 2>/dev/null | grep "cron\.php" | grep -v grep');
    chk(!empty($proc), 'Cron loop', !empty($proc) ? 'Đang chạy (sleep 60s)' : 'Không chạy');
} elseif (!$isWindows) {
    $cronSet = @shell_exec('crontab -l 2>/dev/null | grep -c "cron\.php"') > 0;
    chk($cronSet, 'Crontab', $cronSet ? 'Đã cấu hình' : 'Chưa — chạy thủ công: php cron.php');
} else {
    // Windows: check if cron.php was run recently via cache mtime
    $cacheAge = file_exists($cacheDir . '/news_cache.json') ? time() - filemtime($cacheDir . '/news_cache.json') : 9999;
    chk($cacheAge < 180, 'Cache gần đây', $cacheAge < 180 ? sprintf('%.0f phút trước', $cacheAge / 60) : 'Quá cũ — chạy: php cron.php');
}
section('5. Docker & Cron');

// ===== 6. CONFIG =====
global $RSS_SOURCES;
$srcCount = count($RSS_SOURCES ?? []);
chk($srcCount >= 10, 'RSS sources', "$srcCount nguồn");
chk(file_exists($cacheDir . '/news_cache.json'), 'Dữ liệu có sẵn');
$articles = 0;
if ($hasNews) {
    $j = json_decode(file_get_contents($cacheDir . '/news_cache.json'), true);
    $articles = count($j['articles'] ?? []);
}
chk($articles > 0, 'Bài viết đã cache', $articles > 0 ? "$articles bài" : 'Chưa có');
section('6. Cấu hình');
?>

<div class="summary">
    <div class="sum-card"><div class="n" style="color:#22c55e"><?=$ok?></div><div class="l">Đạt</div></div>
    <div class="sum-card"><div class="n" style="color:#f59e0b"><?=$warn?></div><div class="l">Cảnh báo</div></div>
    <div class="sum-card"><div class="n" style="color:#ef4444"><?=$fail?></div><div class="l">Lỗi</div></div>
</div>

<?php if ($fail === 0 && $warn === 0): ?>
<div class="card" style="text-align:center;padding:12px"><span style="color:#22c55e;font-size:.82rem">✓ Mọi thứ đều ổn, hệ thống sẵn sàng!</span></div>
<?php elseif ($fail === 0): ?>
<div class="card" style="text-align:center;padding:12px"><span style="color:#f59e0b;font-size:.82rem">⚠ Hệ thống hoạt động, có <?=$warn?> cảnh báo nhỏ</span></div>
<?php else: ?>
<div class="card" style="text-align:center;padding:12px"><span style="color:#ef4444;font-size:.82rem">✗ Có <?=$fail?> lỗi cần khắc phục</span></div>
<?php endif; ?>

<div class="tip">
<i>💡</i> Để cache hoạt động: <code>docker compose up -d</code> (cron loop 60s) hoặc chạy thủ công <code>php cron.php</code>.
</div>

<div style="text-align:center;margin-top:16px"><a href="index.php" style="font-size:.8rem">← Về Dashboard</a></div>

</div>
</body>
</html>
