<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Kênh truyền hình</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.ch-page{max-width:1200px;margin:0 auto;padding:20px}
.ch-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-bottom:14px;border-bottom:1px solid var(--border-color)}
.ch-title{font-size:1.3rem;font-weight:700;display:flex;align-items:center;gap:10px}
.ch-title i{color:#ef4444}
.ch-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}
.ch-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius);padding:20px 14px;text-align:center;text-decoration:none;transition:all .15s ease;box-shadow:var(--shadow);display:block}
.ch-card:hover{background:var(--bg-card-hover);border-color:var(--accent-blue);transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.25)}
.ch-card .ch-logo{width:56px;height:56px;margin:0 auto 10px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff}
.ch-card .ch-name{font-size:.82rem;font-weight:600;color:var(--text-primary);margin-bottom:3px}
.ch-card .ch-desc{font-size:.65rem;color:var(--text-muted);line-height:1.3}
.ch-card .ch-badge{display:inline-flex;align-items:center;gap:4px;margin-top:8px;font-size:.6rem;font-weight:600;color:#22c55e}
.ch-card .ch-badge i{font-size:.5rem}
@media(max-width:600px){.ch-grid{grid-template-columns:repeat(2,1fr)}.ch-card .ch-logo{width:44px;height:44px;font-size:1.1rem}}
</style>
</head>
<body>
<div class="ch-page">
    <div class="ch-hdr">
        <div class="ch-title"><i class="fa-solid fa-tower-broadcast"></i> Kênh truyền hình</div>
        <a href="index.php" class="wc-back"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>
    <div class="ch-grid">
<?php
$channels = [
    ['id'=>'vtv1','name'=>'VTV1','desc'=>'Thời sự - Chính trị','color'=>'#dc2626','icon'=>'fa-solid fa-newspaper','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv1.htm'],
    ['id'=>'vtv2','name'=>'VTV2','desc'=>'Khoa học - Giáo dục','color'=>'#2563eb','icon'=>'fa-solid fa-flask','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv2.htm'],
    ['id'=>'vtv3','name'=>'VTV3','desc'=>'Giải trí - Thể thao','color'=>'#16a34a','icon'=>'fa-solid fa-gamepad','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv3.htm'],
    ['id'=>'thvl1','name'=>'THVL1','desc'=>'Tổng hợp - Vĩnh Long','color'=>'#9333ea','icon'=>'fa-solid fa-tv','url'=>'https://www.thvli.vn/live/thvl1-hd'],
    ['id'=>'thvl2','name'=>'THVL2','desc'=>'Giải trí','color'=>'#d946ef','icon'=>'fa-solid fa-music','url'=>'https://www.thvli.vn/live/thvl2-hd'],
    ['id'=>'htv7','name'=>'HTV7','desc'=>'Giải trí - TP.HCM','color'=>'#e11d48','icon'=>'fa-solid fa-star','url'=>'https://htv.com.vn/live/htv7'],
    ['id'=>'htv9','name'=>'HTV9','desc'=>'Thời sự - TP.HCM','color'=>'#0891b2','icon'=>'fa-solid fa-building','url'=>'https://htv.com.vn/live/htv9'],
    ['id'=>'vtc1','name'=>'VTC1','desc'=>'Thời sự','color'=>'#4f46e5','icon'=>'fa-solid fa-broadcast-tower','url'=>'https://vtc.gov.vn/live/1'],
    ['id'=>'hn1','name'=>'H1','desc'=>'Hà Nội','color'=>'#ca8a04','icon'=>'fa-regular fa-building','url'=>'https://hanoitv.vn/live/h1'],
    ['id'=>'hn2','name'=>'H2','desc'=>'Hà Nội - Giải trí','color'=>'#ea580c','icon'=>'fa-solid fa-palette','url'=>'https://hanoitv.vn/live/h2'],
    ['id'=>'dn1','name'=>'ĐN1','desc'=>'Đà Nẵng','color'=>'#0284c7','icon'=>'fa-solid fa-umbrella-beach','url'=>'https://danangtv.vn/live/drt1'],
    ['id'=>'ct1','name'=>'CT1','desc'=>'Cần Thơ','color'=>'#65a30d','icon'=>'fa-solid fa-water','url'=>'https://canthotv.vn/live/thct1'],
    ['id'=>'vtvcab1','name'=>'VTVcab1','desc'=>'Tổng hợp','color'=>'#881337','icon'=>'fa-solid fa-satellite-dish','url'=>'https://vtvcab.vn/live/vtvcab1'],
];
foreach ($channels as $c):
?>
    <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener" class="ch-card" title="Mở trên tab mới">
        <div class="ch-logo" style="background:<?= $c['color'] ?>"><i class="<?= $c['icon'] ?>"></i></div>
        <div class="ch-name"><?= $c['name'] ?></div>
        <div class="ch-desc"><?= $c['desc'] ?></div>
        <div class="ch-badge"><i class="fa-solid fa-circle"></i> LIVE</div>
    </a>
<?php endforeach; ?>
    </div>
</div>
</body>
</html>
