<?php
require_once __DIR__ . '/includes/functions.php';
$data = load_cached_data();
$social = $data['social_trends'];
if (empty($social['youtube']) && empty($social['tiktok'])) $social = fetch_social_trends();
$keywords = $data['top_keywords'];
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - Xu hướng mạng xã hội</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="page-trends">
<div class="tr-container">
    <div class="tr-header">
        <div class="tr-title"><i class="fa-solid fa-hashtag"></i> Xu hướng mạng xã hội</div>
        <a href="index.php" class="wc-back"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>

    <div class="tr-grid">
        <!-- Google Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-solid fa-google" style="color:#ea4335"></i> Google Trends Việt Nam</div>
            <?php if (!empty($social['google'])): $i=1; foreach($social['google'] as $t): ?>
                <div class="tr-item">
                    <span class="tr-rank">#<?= $i++ ?></span>
                    <span class="tr-name"><?= htmlspecialchars($t['title']) ?></span>
                    <?php if (!empty($t['traffic'])): ?><span class="tr-meta"><?= htmlspecialchars($t['traffic']) ?></span><?php endif; ?>
                </div>
            <?php endforeach; else: ?>
                <div class="tr-empty">Đang cập nhật...</div>
            <?php endif; ?>
        </div>

        <!-- YouTube Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-brands fa-youtube" style="color:#ff0000"></i> YouTube Thịnh hành</div>
            <?php if (!empty($social['youtube'])): $i=1; foreach($social['youtube'] as $v): ?>
                <a href="<?= htmlspecialchars($v['url']) ?>" target="_blank" rel="noopener" class="tr-item" style="text-decoration:none">
                    <span class="tr-rank">#<?= $i++ ?></span>
                    <span class="tr-name">
                        <?= htmlspecialchars(mb_substr($v['title'],0,70,'UTF-8')) ?>
                        <div class="tr-channel"><?= htmlspecialchars($v['channel']) ?></div>
                    </span>
                    <span class="tr-meta"><?= $v['views'] ? number_format($v['views']).' lượt xem' : '' ?></span>
                </a>
            <?php endforeach; else: ?>
                <div class="tr-empty">Đang cập nhật...</div>
            <?php endif; ?>
        </div>

        <!-- TikTok Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-brands fa-tiktok" style="color:#fff"></i> TikTok Trending</div>
            <?php if (!empty($social['tiktok'])): $i=1; foreach(array_slice($social['tiktok'],0,12) as $h): ?>
                <a href="<?= htmlspecialchars($h['url']) ?>" target="_blank" rel="noopener" class="tr-item" style="text-decoration:none">
                    <span class="tr-rank">#<?= $i++ ?></span>
                    <span class="tr-name">#<?= htmlspecialchars($h['hashtag']) ?></span>
                    <span class="tr-meta"><i class="fa-solid fa-play"></i> Trending</span>
                </a>
            <?php endforeach; else: ?>
                <div class="tr-empty">Đang cập nhật...</div>
            <?php endif; ?>
        </div>

        <!-- News Keywords Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-solid fa-newspaper" style="color:#3b82f6"></i> Từ khoá nổi bật (Tin tức)</div>
            <?php if (!empty($keywords)): $i=1; foreach(array_slice($keywords,0,20) as $word=>$count): ?>
                <div class="tr-item">
                    <span class="tr-rank">#<?= $i++ ?></span>
                    <span class="tr-name"><?= htmlspecialchars($word) ?></span>
                    <span class="tr-meta"><?= $count ?> bài</span>
                </div>
            <?php endforeach; else: ?>
                <div class="tr-empty">Đang cập nhật...</div>
            <?php endif; ?>
        </div>

        <!-- Facebook Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-brands fa-facebook" style="color:#1877f2"></i> Facebook Trending</div>
            <div style="padding:10px;text-align:center;color:var(--text-muted);font-size:.78rem">
                <i class="fa-regular fa-face-frown" style="font-size:1.5rem;display:block;margin-bottom:6px"></i>
                Facebook không công khai dữ liệu xu hướng qua API miễn phí.<br>
                <a href="https://www.facebook.com/salestools/trends/" target="_blank" rel="noopener" style="color:var(--accent-blue);text-decoration:none;margin-top:6px;display:inline-block">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trên Facebook
                </a>
            </div>
        </div>

        <!-- Threads Trends -->
        <div class="tr-card">
            <div class="tr-card-title"><i class="fa-brands fa-threads" style="color:#fff"></i> Threads Đang thịnh hành</div>
            <div style="padding:10px;text-align:center;color:var(--text-muted);font-size:.78rem">
                <i class="fa-regular fa-face-frown" style="font-size:1.5rem;display:block;margin-bottom:6px"></i>
                Threads chưa có API công khai cho xu hướng.<br>
                <a href="https://www.threads.net/topics" target="_blank" rel="noopener" style="color:var(--accent-blue);text-decoration:none;margin-top:6px;display:inline-block">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trên Threads
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
