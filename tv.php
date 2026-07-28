<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Kênh truyền hình</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.ch-page{max-width:1100px;margin:0 auto;padding:20px}
.ch-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;padding-bottom:14px;border-bottom:1px solid var(--border-color)}
.ch-title{font-size:1.2rem;font-weight:700;display:flex;align-items:center;gap:8px;letter-spacing:-.02em}
.ch-title i{color:#ef4444;font-size:.9rem}
.ch-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px}
.ch-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius);padding:18px 16px;text-decoration:none;transition:all .15s ease;box-shadow:var(--shadow);display:block;position:relative;border-top:3px solid transparent}
.ch-card:hover{background:var(--bg-card-hover);border-color:var(--text-muted);transform:translateY(-1px)}
.ch-card .ch-live{position:absolute;top:12px;right:12px;font-size:.58rem;font-weight:600;color:#22c55e;display:flex;align-items:center;gap:4px;opacity:.7}
.ch-card .ch-live i{font-size:.42rem}
.ch-card .ch-name{font-size:1.35rem;font-weight:700;letter-spacing:-.02em;margin-bottom:2px;line-height:1.2}
.ch-card .ch-desc{font-size:.72rem;color:var(--text-muted);margin-top:4px;font-weight:400}
.ch-card .ch-bar{width:24px;height:3px;border-radius:2px;margin-top:12px;transition:width .2s ease}
.ch-card:hover .ch-bar{width:40px}
@media(max-width:600px){.ch-grid{grid-template-columns:1fr 1fr}.ch-card .ch-name{font-size:1.1rem}}
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
    ['id'=>'vtv1','name'=>'VTV1','desc'=>'Thời sự - Chính trị','color'=>'#dc2626','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv1.htm'],
    ['id'=>'vtv2','name'=>'VTV2','desc'=>'Khoa học - Giáo dục','color'=>'#2563eb','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv2.htm'],
    ['id'=>'vtv3','name'=>'VTV3','desc'=>'Giải trí - Thể thao','color'=>'#16a34a','url'=>'https://vtv.vn/truyen-hinh-truc-tuyen/vtv3.htm'],
    ['id'=>'thvl1','name'=>'THVL1','desc'=>'Tổng hợp - Vĩnh Long','color'=>'#9333ea','url'=>'https://www.thvli.vn/live/thvl1-hd'],
    ['id'=>'thvl2','name'=>'THVL2','desc'=>'Giải trí','color'=>'#d946ef','url'=>'https://www.thvli.vn/live/thvl2-hd'],
    ['id'=>'htv7','name'=>'HTV7','desc'=>'Giải trí - TP.HCM','color'=>'#e11d48','url'=>'https://htv.com.vn/live/htv7'],
    ['id'=>'htv9','name'=>'HTV9','desc'=>'Thời sự - TP.HCM','color'=>'#0891b2','url'=>'https://htv.com.vn/live/htv9'],
    ['id'=>'vtc1','name'=>'VTC1','desc'=>'Thời sự','color'=>'#4f46e5','url'=>'https://vtc.gov.vn/live/1'],
    ['id'=>'hn1','name'=>'H1','desc'=>'Hà Nội','color'=>'#ca8a04','url'=>'https://hanoitv.vn/live/h1'],
    ['id'=>'hn2','name'=>'H2','desc'=>'Hà Nội - Giải trí','color'=>'#ea580c','url'=>'https://hanoitv.vn/live/h2'],
    ['id'=>'dn1','name'=>'ĐN1','desc'=>'Đà Nẵng','color'=>'#0284c7','url'=>'https://danangtv.vn/live/drt1'],
    ['id'=>'ct1','name'=>'CT1','desc'=>'Cần Thơ','color'=>'#65a30d','url'=>'https://canthotv.vn/live/thct1'],
    ['id'=>'vtvcab1','name'=>'VTVcab1','desc'=>'Tổng hợp','color'=>'#881337','url'=>'https://vtvcab.vn/live/vtvcab1'],
];
foreach ($channels as $c):
?>
    <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener" class="ch-card" style="border-top-color:<?= $c['color'] ?>" title="Mở trên tab mới">
        <span class="ch-live"><i class="fa-solid fa-circle"></i> LIVE</span>
        <div class="ch-name"><?= $c['name'] ?></div>
        <div class="ch-desc"><?= $c['desc'] ?></div>
        <div class="ch-bar" style="background:<?= $c['color'] ?>"></div>
    </a>
<?php endforeach; ?>
    </div>
</div>
</body>
</html>
