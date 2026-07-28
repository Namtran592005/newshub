<?php $view = $_GET['view'] ?? 'all'; $bodyClass = 'view-'.$view; ?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NewsHub - <?= $view==='all'?'Dashboard':ucfirst($view) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script src="assets/js/app.js" defer></script>
</head>
<body class="<?= $bodyClass ?>">
<div class="container">
    <header class="header">
        <div class="header-left">
            <div class="header-title">NEWSHUB</div>
            <div class="live-indicator">
                <span class="live-dot"></span>
                <span>TRỰC TIẾP</span>
            </div>
            <nav class="view-nav">
                <a href="?view=all" class="view-link <?= $view==='all'?'active':'' ?>" title="Tổng quan"><i class="fa-solid fa-grip"></i></a>
                <a href="?view=news" class="view-link <?= $view==='news'?'active':'' ?>" title="Tin tức"><i class="fa-regular fa-newspaper"></i></a>
                <a href="?view=finance" class="view-link <?= $view==='finance'?'active':'' ?>" title="Tài chính"><i class="fa-solid fa-chart-simple"></i></a>
                <a href="?view=charts" class="view-link <?= $view==='charts'?'active':'' ?>" title="Biểu đồ"><i class="fa-solid fa-chart-pie"></i></a>
                <a href="?view=breaking" class="view-link <?= $view==='breaking'?'active':'' ?>" title="Tin nóng"><i class="fa-solid fa-triangle-exclamation"></i></a>
                <a href="trends.php" class="view-link" title="Xu hướng MXH" target="_blank"><i class="fa-solid fa-hashtag"></i></a>
                <a href="worldclock.php" class="view-link" title="Đồng hồ thế giới" target="_blank"><i class="fa-regular fa-clock"></i></a>
                <a href="tv.php" class="view-link" title="TV Trực tiếp" target="_blank"><i class="fa-solid fa-tv"></i></a>
            </nav>
        </div>
        <div class="header-right">
            <span class="countdown" id="countdown"><i class="fa-regular fa-hourglass-half"></i> <span id="countdown-value">60</span>s</span>
            <span class="updated-at" id="updated-at"><i class="fa-regular fa-clock"></i> Đang tải...</span>
            <button class="btn-refresh" type="button"><i class="fa-solid fa-rotate"></i> Làm mới</button>
        </div>
    </header>

    <!-- ===== BREAKING NEWS ===== -->
    <div class="breaking-section">
        <div class="breaking-header">
            <span class="breaking-label"><i class="fa-solid fa-triangle-exclamation"></i> TIN NÓNG</span>
            <span class="breaking-sub">Tin tức quan trọng trong nước & quốc tế</span>
            <div class="breaking-controls">
                <button class="bc-btn" id="bc-prev" title="Trái"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="bc-btn" id="bc-play" title="Tự động cuộn"><i class="fa-solid fa-pause"></i></button>
                <button class="bc-btn" id="bc-next" title="Phải"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="breaking-track" id="breaking-track"></div>
    </div>

    <!-- ===== NEWS TICKER ===== -->
    <div class="ticker-bar">
        <div class="ticker-label"><i class="fa-solid fa-circle"></i> MỚI NHẤT</div>
        <div class="ticker-track" id="ticker-track"><span class="ticker-placeholder">Đang tải tin mới...</span></div>
    </div>

    <!-- ===== WEATHER ===== -->
    <div class="weather-row" id="weather-row"></div>

    <!-- ===== STATS ===== -->
    <div class="stats-row" id="stats-row">
        <div class="stat-card">
            <span class="stat-label"><i class="fa-regular fa-newspaper"></i> Tổng bài viết</span>
            <span class="stat-value blue" id="total-articles">--</span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid fa-bolt"></i> Tin nóng</span>
            <span class="stat-value cyan" id="total-breaking">--</span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid fa-tags"></i> Từ khoá</span>
            <span class="stat-value green" id="total-keywords">--</span>
        </div>
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid fa-layer-group"></i> Chuyên mục</span>
            <span class="stat-value orange" id="total-categories">--</span>
        </div>
    </div>

    <div class="filter-bar" id="filter-bar">
        <div class="filter-group">
            <i class="fa-solid fa-filter"></i>
            <select id="filter-source" class="filter-select"><option value="all">Tất cả nguồn</option></select>
        </div>
        <div class="filter-group">
            <i class="fa-solid fa-folder"></i>
            <select id="filter-category" class="filter-select"><option value="all">Tất cả chuyên mục</option></select>
        </div>
        <div class="filter-group">
            <i class="fa-regular fa-clock"></i>
            <select id="filter-time" class="filter-select">
                <option value="all">Mọi lúc</option>
                <option value="1">1 giờ qua</option>
                <option value="6">6 giờ qua</option>
                <option value="24">24 giờ qua</option>
            </select>
        </div>
        <div class="filter-search">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="filter-search" class="filter-input" placeholder="Tìm kiếm tin tức..." autocomplete="off">
        </div>
        <span class="filter-result-count" id="filter-count"></span>
    </div>

    <div class="main-grid">
        <div class="news-feed">
            <div class="feed-header">
                <span class="feed-title"><i class="fa-solid fa-bolt"></i> Tin tức mới nhất</span>
                <span class="feed-count" id="feed-count"></span>
            </div>
            <div id="news-feed" class="news-feed-list"></div>
            <div class="pagination" id="pagination"></div>

            <!-- ===== FINANCE SECTION ===== -->
            <div class="finance-section" id="finance-section" style="margin-top:14px">
                <div class="finance-header">
                    <span class="feed-title"><i class="fa-solid fa-chart-simple"></i> Thị trường tài chính</span>
                    <span class="feed-count" id="finance-updated"></span>
                </div>
                <div class="finance-tabs">
                    <button class="ftab active" data-tab="all">Tất cả</button>
                    <button class="ftab" data-tab="indices">Chứng khoán</button>
                    <button class="ftab" data-tab="gold">Vàng</button>
                    <button class="ftab" data-tab="commodities">Hàng hoá</button>
                    <button class="ftab" data-tab="petrol">Xăng dầu</button>
                    <button class="ftab" data-tab="currency">Ngoại tệ</button>
                </div>
                <div class="finance-grid" id="finance-grid"></div>
            </div>

            <!-- ===== CHARTS ROW ===== -->
            <div class="charts-row" id="charts-row" style="margin-top:14px">
                <div class="charts-row-header">
                    <span class="feed-title"><i class="fa-solid fa-chart-bar"></i> Thống kê & Biểu đồ</span>
                </div>
                <div class="charts-row-grid">
                    <div class="chart-card">
                        <div class="chart-card-title">Phân bổ nguồn tin</div>
                        <div class="chart-container" style="height:200px"><canvas id="chart-source"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card-title">So sánh chuyên mục</div>
                        <div class="chart-container" style="height:200px"><canvas id="chart-compare"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card-title">Tổng quan bài viết</div>
                        <div class="chart-container" style="height:200px"><canvas id="chart-overview"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card-title">Phân bổ theo giờ</div>
                        <div class="chart-container" style="height:200px"><canvas id="chart-hourly"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SIDEBAR ===== -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-chart-line"></i> Xu hướng 24h</div>
                <div class="chart-container"><canvas id="chart-timeline"></canvas></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-chart-pie"></i> Chuyên mục</div>
                <div class="chart-container" style="height:200px"><canvas id="chart-category"></canvas></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-fire"></i> Từ khoá nổi bật</div>
                <div id="keywords-cloud" class="keywords-cloud"></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-chart-simple"></i> Top từ khoá</div>
                <div class="chart-container" style="height:220px"><canvas id="chart-keywords"></canvas></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-ranking-star"></i> Xu hướng tìm kiếm</div>
                <div id="trending-list" class="trending-list"></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-globe"></i> Phân bổ nguồn</div>
                <div id="source-list" class="source-list"></div>
            </div>
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fa-solid fa-bars-progress"></i> Chuyên mục</div>
                <div id="category-list" class="category-list"></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
