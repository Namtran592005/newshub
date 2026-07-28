<?php
$mode = $_GET['mode'] ?? 'grid';
$ch   = $_GET['ch'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>NewsHub TV - Truyền hình trực tiếp</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{background:#000;font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;overflow:hidden;color:#fff}
  .tv-wrap{width:100vw;height:100vh;display:flex;flex-direction:column}
  .tv-hdr{flex-shrink:0;display:flex;align-items:center;gap:10px;padding:5px 12px;background:rgba(0,0,0,.88);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,.06);z-index:100}
  .tv-logo{font-size:.9rem;font-weight:700;display:flex;align-items:center;gap:7px;letter-spacing:-.02em;flex-shrink:0}
  .tv-logo i{color:#ef4444;font-size:.75rem}
  .tv-nav{display:flex;gap:3px;flex-wrap:wrap;flex:1;overflow-x:auto;padding:2px 0}
  .tv-nav::-webkit-scrollbar{height:2px}
  .tv-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:2px}
  .tv-nav .ch-link{color:rgba(255,255,255,.45);padding:3px 9px;border-radius:4px;font-size:.7rem;text-decoration:none;transition:all .12s;border:1px solid transparent;white-space:nowrap}
  .tv-nav .ch-link:hover{color:#fff;background:rgba(255,255,255,.07)}
  .tv-nav .ch-link.active{color:#fff;background:#3b82f6;border-color:#3b82f6}
  .tv-ctrl{display:flex;gap:4px;flex-shrink:0}
  .tv-btn{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.5);padding:4px 10px;border-radius:4px;font-size:.68rem;cursor:pointer;transition:all .12s;text-decoration:none;display:flex;align-items:center;gap:4px;font-family:inherit}
  .tv-btn:hover{background:rgba(255,255,255,.12);color:#fff}
  .tv-btn.active{background:#3b82f6;color:#fff;border-color:#3b82f6}
  .tv-grid{flex:1;display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:2px;overflow:hidden;background:#050505}
  .tv-grid.solo{grid-template-columns:1fr}
  .tv-ch{position:relative;background:#0a0a0a;min-height:220px;overflow:hidden}
  .tv-ch iframe{width:100%;height:100%;border:none;position:absolute;top:0;left:0}
  .tv-ch .ch-overlay{position:absolute;bottom:0;left:0;right:0;padding:30px 12px 7px;background:linear-gradient(transparent,rgba(0,0,0,.88));pointer-events:none}
  .tv-ch .ch-n{font-size:.8rem;font-weight:600;letter-spacing:.01em}
  .tv-ch .ch-d{font-size:.64rem;color:rgba(255,255,255,.4);margin-top:1px}
  .tv-ch .ch-badge{position:absolute;top:6px;right:6px;font-size:.6rem;padding:2px 7px;border-radius:3px;background:#22c55e;color:#000;font-weight:600;opacity:.85;display:flex;align-items:center;gap:4px}
  .tv-ch .ch-badge i{font-size:.5rem;color:#000}
  .tv-ch .ch-fallback{position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0a0a0a;color:rgba(255,255,255,.3);padding:20px;text-align:center;gap:8px}
  .tv-ch .ch-fallback i{font-size:2rem;color:rgba(255,255,255,.1)}
  .tv-ch .ch-fallback a{color:#3b82f6;text-decoration:none;font-size:.75rem}
  @media(max-width:900px){.tv-grid{grid-template-columns:repeat(2,1fr)}.tv-ch{min-height:170px}}
  @media(max-width:500px){.tv-grid{grid-template-columns:1fr}.tv-ch{min-height:190px}.tv-nav .ch-link{font-size:.64rem;padding:2px 6px}}
</style>
</head>
<body>
<?php
$channels = [
    // VTV official web players (embed from vtv.vn)
    ['id'=>'vtv1','name'=>'VTV1','desc'=>'Thời sự - Chính trị','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv1.htm'],
    ['id'=>'vtv2','name'=>'VTV2','desc'=>'Khoa học - Giáo dục','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv2.htm'],
    ['id'=>'vtv3','name'=>'VTV3','desc'=>'Giải trí - Thể thao','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv3.htm'],
    // THVL official
    ['id'=>'thvl1','name'=>'THVL1','desc'=>'Tổng hợp - Vĩnh Long','url'=>'https://www.thvli.vn/live/thvl1-hd'],
    ['id'=>'thvl2','name'=>'THVL2','desc'=>'Giải trí','url'=>'https://www.thvli.vn/live/thvl2-hd'],
    // HTV via official website
    ['id'=>'htv7','name'=>'HTV7','desc'=>'Giải trí - TP.HCM','url'=>'https://htv.com.vn/live/htv7'],
    ['id'=>'htv9','name'=>'HTV9','desc'=>'Thời sự - TP.HCM','url'=>'https://htv.com.vn/live/htv9'],
    // VTC
    ['id'=>'vtc1','name'=>'VTC1','desc'=>'Thời sự','url'=>'https://vtc.gov.vn/live/1'],
    // Hanoi TV
    ['id'=>'hn1','name'=>'H1','desc'=>'Hà Nội','url'=>'https://hanoitv.vn/live/h1'],
    ['id'=>'hn2','name'=>'H2','desc'=>'Hà Nội - Giải trí','url'=>'https://hanoitv.vn/live/h2'],
    // Regional
    ['id'=>'dn1','name'=>'ĐN1','desc'=>'Đà Nẵng','url'=>'https://danangtv.vn/live/drt1'],
    ['id'=>'ct1','name'=>'CT1','desc'=>'Cần Thơ','url'=>'https://canthotv.vn/live/thct1'],
    // VTVcab / SCTV (via website)
    ['id'=>'vtvcab1','name'=>'VTVcab1','desc'=>'Tổng hợp','url'=>'https://vtvcab.vn/live/vtvcab1'],
];
?>
<div class="tv-wrap">
  <div class="tv-hdr">
    <div class="tv-logo"><i class="fa-solid fa-tower-broadcast"></i> NEWSHUB TV</div>
    <nav class="tv-nav">
      <a href="?mode=grid" class="ch-link <?= $mode==='grid'?'active':'' ?>"><i class="fa-solid fa-border-all"></i></a>
      <?php foreach($channels as $c): ?>
        <a href="?mode=single&ch=<?= $c['id'] ?>" class="ch-link <?= ($ch===$c['id'])?'active':'' ?>"><?= $c['name'] ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="tv-ctrl">
      <a href="index.php" class="tv-btn" title="Dashboard"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
  </div>
  <div class="tv-grid <?= $mode==='single'?'solo':'' ?>">
    <?php if ($mode==='single' && $ch): $sel = current(array_filter($channels, fn($c)=>$c['id']===$ch)); if($sel): ?>
      <div class="tv-ch" style="grid-column:1/-1;grid-row:1/-1;min-height:0;height:calc(100vh-40px)">
        <span class="ch-badge"><i class="fa-solid fa-circle"></i> LIVE</span>
        <iframe src="<?= $sel['url'] ?>" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy" sandbox="allow-same-origin allow-scripts allow-forms allow-popups"></iframe>
        <div class="ch-overlay"><div class="ch-n"><?= $sel['name'] ?></div><div class="ch-d"><?= $sel['desc'] ?></div></div>
      </div>
    <?php endif; else: ?>
      <?php foreach($channels as $c): ?>
        <div class="tv-ch">
          <span class="ch-badge"><i class="fa-solid fa-circle"></i> LIVE</span>
          <iframe src="<?= $c['url'] ?>" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy" sandbox="allow-same-origin allow-scripts allow-forms allow-popups"></iframe>
          <div class="ch-overlay"><div class="ch-n"><?= $c['name'] ?></div><div class="ch-d"><?= $c['desc'] ?></div></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
