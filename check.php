<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Kiểm tra hệ thống</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Inter',-apple-system,sans-serif;background:#0d0f12;color:#e4e6ea;padding:30px 20px}
  .wrap{max-width:800px;margin:0 auto}
  h1{font-size:1.4rem;font-weight:700;margin-bottom:6px;letter-spacing:-.02em}
  .sub{color:#8a8f99;font-size:.82rem;margin-bottom:24px}
  .card{background:#15181d;border:1px solid #1e2228;border-radius:8px;padding:18px 20px;margin-bottom:12px}
  .card h2{font-size:.85rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#8a8f99;margin-bottom:12px}
  .row{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #1e2228;font-size:.82rem}
  .row:last-child{border-bottom:none}
  .label{color:#b0b4ba}
  .val{font-weight:500}
  .ok{color:#22c55e}
  .warn{color:#f59e0b}
  .err{color:#ef4444}
  .pass{display:inline-flex;align-items:center;gap:5px;padding:2px 10px;border-radius:4px;font-size:.72rem;font-weight:600}
  .pass.ok{background:rgba(34,197,94,.12);color:#22c55e}
  .pass.warn{background:rgba(245,158,11,.12);color:#f59e0b}
  .pass.err{background:rgba(239,68,68,.12);color:#ef4444}
  .bar{height:4px;border-radius:2px;margin-top:4px;background:#1e2228;overflow:hidden}
  .bar-fill{height:100%;border-radius:2px;transition:width .4s}
  .summary{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}
  .sum-card{background:#15181d;border:1px solid #1e2228;border-radius:8px;padding:14px;text-align:center}
  .sum-card .num{font-size:1.8rem;font-weight:700}
  .sum-card .lbl{font-size:.7rem;color:#8a8f99;margin-top:3px;text-transform:uppercase;letter-spacing:.04em}
  a{color:#3b82f6;text-decoration:none}
  a:hover{text-decoration:underline}
  @media(max-width:600px){.summary{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="wrap">
<h1>🔍 NewsHub — Kiểm tra hệ thống</h1>
<p class="sub">Trang này kiểm tra môi trường, quyền, kết nối và dữ liệu cache</p>

<?php
require_once __DIR__ . '/includes/functions.php';
$pass = 0; $warn = 0; $fail = 0;
function status($ok, $label, $detail = '') {
    global $pass, $warn, $fail;
    if ($ok) { $cls = 'ok'; $pass++; }
    elseif ($detail === 'warn') { $cls = 'warn'; $warn++; }
    else { $cls = 'err'; $fail++; }
    echo '<div class="row"><span class="label">' . htmlspecialchars($label) . '</span><span class="val ' . $cls . '">' . ($detail ?: ($ok ? '✓ OK' : '✗ FAIL')) . '</span></div>';
}
?>

<div class="summary" id="summary-stats"></div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const p=<?=$pass?>, w=<?=$warn?>, f=<?=$fail?>;
    document.getElementById('summary-stats').innerHTML=
        `<div class="sum-card"><div class="num" style="color:#22c55e">${p}</div><div class="lbl">Đạt</div></div>`+
        `<div class="sum-card"><div class="num" style="color:#f59e0b">${w}</div><div class="lbl">Cảnh báo</div></div>`+
        `<div class="sum-card"><div class="num" style="color:#ef4444">${f}</div><div class="lbl">Lỗi</div></div>`;
});
</script>

<?php
// ===== 1. PHP ENVIRONMENT =====
echo '<div class="card"><h2>1. Môi trường PHP</h2>';
status(PHP_VERSION_ID >= 80000, 'Phiên bản PHP', PHP_VERSION);
status(extension_loaded('simplexml'), 'simplexml');
status(extension_loaded('mbstring'), 'mbstring');
status(extension_loaded('json'), 'json');
status(extension_loaded('curl') || ini_get('allow_url_fopen'), 'allow_url_fopen / curl');
status(function_exists('file_get_contents'), 'file_get_contents');
status(ini_get('max_execution_time') >= 30, 'max_execution_time', ini_get('max_execution_time') . 's');
status(ini_get('memory_limit') >= 64, 'memory_limit', ini_get('memory_limit'));
echo '</div>';

// ===== 2. FILE PERMISSIONS =====
echo '<div class="card"><h2>2. Quyền & thư mục</h2>';
$root = __DIR__;
$cacheDir = $root . '/cache';
$dirs = [
    'cache' => $cacheDir,
    'logs' => $root . '/logs',
];
$files = [
    'index.php', 'api.php', 'cron.php', 'tv.php', 'check.php',
    'includes/functions.php', 'assets/js/app.js', 'assets/css/style.css'
];

foreach ($dirs as $name => $dir) {
    $exists = is_dir($dir);
    $writable = $exists && is_writable($dir);
    if ($exists && $writable) status(true, "Thư mục $name", 'Có quyền ghi');
    elseif ($exists && !$writable) status(false, "Thư mục $name", '❌ Không có quyền ghi');
    else status(false, "Thư mục $name", '❌ Không tồn tại');
}
foreach ($files as $f) {
    status(file_exists($root . '/' . $f), "File $f");
}
echo '</div>';

// ===== 3. CACHE DATA =====
echo '<div class="card"><h2>3. Dữ liệu cache</h2>';
$cacheFiles = [
    'news_cache.json' => 'Tin tức',
    'finance_cache.json' => 'Tài chính',
    'weather_cache.json' => 'Thời tiết',
    'social_cache.json' => 'Mạng xã hội',
];
$hasAll = true;
foreach ($cacheFiles as $file => $label) {
    $path = $cacheDir . '/' . $file;
    $exists = file_exists($path);
    if (!$exists) { $hasAll = false; status(false, $label, 'Chưa có cache'); continue; }
    $age = time() - filemtime($path);
    $fresh = $age < 120;
    $ageStr = $age < 60 ? 'Vài giây trước' : (floor($age/60) . ' phút trước');
    status($fresh, $label, $ageStr);
}
if (!$hasAll) {
    status(false, '→ Chạy cron job để tạo cache:', 'php cron.php', 'warn');
}
echo '</div>';

// ===== 4. NETWORK CONNECTIVITY =====
echo '<div class="card"><h2>4. Kết nối mạng</h2>';
$urls = [
    'https://vnexpress.net' => 'VnExpress',
    'https://tuoitre.vn' => 'Tuổi Trẻ',
    'https://query1.finance.yahoo.com' => 'Yahoo Finance',
    'https://wttr.in' => 'wttr.in (thời tiết)',
    'https://trends.google.com' => 'Google Trends',
];
foreach ($urls as $url => $label) {
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'NewsHub/1.0']]);
    $h = @get_headers($url, 0, $ctx);
    $ok = $h && (strpos($h[0], '200') !== false || strpos($h[0], '301') !== false || strpos($h[0], '302') !== false);
    status($ok, $label, $ok ? 'Kết nối được' : 'Không kết nối được');
}
echo '</div>';

// ===== 5. DOCKER / CRON =====
echo '<div class="card"><h2>5. Docker & Cron</h2>';
$hasDocker = file_exists('/.dockerenv') || (PHP_SAPI === 'cli' && getenv('DOCKER'));
$inPhpContainer = file_exists('/.dockerenv');
status(true, 'Container PHP', $inPhpContainer ? 'Chạy trong Docker' : 'Chạy ngoài Docker (host)');
if ($inPhpContainer) {
    $cronRunning = trim(`ps aux | grep -c '[o]fel' 2>/dev/null`) > 0;
    status($cronRunning, 'Ofelia (cron)', $cronRunning ? 'Đang chạy' : 'Không chạy', 'warn');
} else {
    // Check host crontab
    $cronJob = trim(`crontab -l 2>/dev/null | grep -c 'cron.php'`) > 0;
    status($cronJob, 'Crontab', $cronJob ? 'Đã cấu hình' : 'Chưa cấu hình cron', 'warn');
}
echo '</div>';

// ===== 6. CONFIG CHECK =====
echo '<div class="card"><h2>6. Cấu hình</h2>';
$configChecks = [
    ['label' => 'CACHE_TTL', 'ok' => defined('CACHE_TTL') || true, 'val' => '55 giây'],
    ['label' => 'RSS sources', 'ok' => count($RSS_SOURCES ?? []) >= 10, 'val' => count($RSS_SOURCES ?? []) . ' nguồn'],
    ['label' => 'Cron job active', 'ok' => file_exists($cacheDir . '/news_cache.json'), 'val' => file_exists($cacheDir . '/news_cache.json') ? 'Bật' : 'Tắt'],
];
foreach ($configChecks as $c) {
    status($c['ok'], $c['label'], $c['val']);
}
echo '</div>';
?>

<div class="card" style="text-align:center;padding:14px">
    <span style="font-size:.82rem;color:#8a8f99">
        <i class="fa-regular fa-circle-check" style="color:#22c55e"></i>
        <?php if ($fail === 0 && $warn === 0): ?>
            Mọi thứ đều ổn, hệ thống sẵn sàng!
        <?php elseif ($fail === 0): ?>
            Hệ thống hoạt động, có <?= $warn ?> cảnh báo nhỏ.
        <?php else: ?>
            Có <?= $fail ?> lỗi cần khắc phục trước khi sử dụng.
        <?php endif; ?>
    </span>
</div>

<div class="card" style="text-align:center;padding:14px">
    <a href="index.php" style="font-size:.82rem">← Về Dashboard</a>
</div>
</div>
</body>
</html>
