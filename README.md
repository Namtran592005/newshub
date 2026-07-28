# NewsHub — Real-time News Dashboard

Dashboard tin tức thời gian thực, tối giản, kỹ thuật. Tự động tổng hợp từ RSS, hiển thị biểu đồ, tài chính, thời tiết, đồng hồ thế giới, xu hướng mạng xã hội, và truyền hình trực tiếp.

## Tính năng

- **Tổng hợp tin tức** từ 12 nguồn RSS (VnExpress, Tuổi Trẻ, Dân trí, Vietnamnet, Thanh Niên, Znews, VietNamPlus, 24h, BBC, TechCrunch, Reddit, HN)
- **Tin nóng** — tự động chọn tin quan trọng, ưu tiên trong nước, carousel cuộn ngang
- **Băng chạy thời sự** — ticker dạng chạy vô tận, click để mở bài
- **Phân trang + filter** — lọc theo nguồn, chuyên mục, thời gian, tìm kiếm realtime
- **6 biểu đồ** — timeline 24h, doughnut chuyên mục, bar từ khoá, polar area, source bar, hourly bar
- **Tài chính** — VN-INDEX, HNX, giá vàng (SJC/thế giới), dầu Brent/WTI, xăng dầu VN, USD/VND. Có tab lọc theo loại
- **Thời tiết** — 4 tỉnh lớn + tự động phát hiện vị trí người dùng qua Geolocation API, hiển thị nhiệt độ, độ ẩm, gió, dự báo 4 ngày
- **Đồng hồ thế giới** — `worldclock.php` — 20+ múi giờ, cập nhật realtime, phân biệt ngày/đêm
- **Xu hướng mạng xã hội** — `trends.php` — Google Trends VN, YouTube thịnh hành, TikTok hashtag, Facebook/Threads
- **Xu hướng Google** — top từ khoá tìm kiếm tại Việt Nam
- **Định vị khu vực** — tự động phát hiện vị trí → thêm thời tiết thành phố của bạn, ưu tiên tin tức từ nguồn địa phương (Bắc/Nam/Trung)
- **Theme tối** — dark mode duy nhất
- **Đếm ngược tự động** — countdown đến lần làm mới tiếp theo (60s)
- **View modes** — `?view=all|news|finance|charts|breaking` — mỗi tab màn hình riêng
- **Danh sách kênh TV** — `tv.php` — 13 kênh VTV1-3, THVL1-2, HTV7/9, VTC1, H1/H2, ĐN1, CT1, VTVcab1 — click vào kênh để mở trang chính thức trên tab mới
- **Responsive** — 3 breakpoint (1100/768/480px)
- **Tự động cập nhật** — cron job (Ofelia) thu thập dữ liệu mỗi 5 phút, dashboard chỉ đọc cache, không fetch khi người dùng truy cập
- **Chịu tải cao** — trang luôn hoạt động kể cả không ai truy cập; zero dependency vào trình duyệt người dùng

## Yêu cầu

- Docker & Docker Compose
- Web server (Caddy, Nginx, Apache) có PHP 8.0+ với `mbstring`, `simplexml`
- Kết nối internet để cron job fetch RSS & dữ liệu tài chính

## Cài đặt

```bash
# 1. Clone vào thư mục web server
git clone https://github.com/Namtran592005/newshub.git
cd newshub

# 2. Khởi động Ofelia + PHP CLI (cron job)
docker compose up -d

# 3. Truy cập qua web server
# http://localhost/newshub/
```

Lần đầu chạy, cron job sẽ tự động thu thập dữ liệu trong vòng 1-2 phút. Trang sẽ hiển thị "Đang chờ dữ liệu từ cronjob..." cho đến khi có cache.

### Chạy thủ công (không Docker)

```bash
php cron.php
# Hoặc cấu hình crontab:
# */5 * * * * php /path/to/newshub/cron.php
```

## Kiến trúc

Hệ thống hoạt động theo mô hình **cronjob-driven**:

```
┌─────────────────┐     ┌──────────────┐     ┌──────────────────┐
│   Ofelia (cron) │────▶│  PHP CLI     │────▶│  Cache JSON      │
│   @every 5m     │     │  cron.php    │     │  (news_cache)    │
└─────────────────┘     └──────────────┘     └──────────────────┘
                                                    │
┌──────────────┐                                    │
│  Trình duyệt │────▶  api.php  (đọc cache) ────────┘
│  (index.php) │
└──────────────┘
```

- `cron.php` — xoá cache cũ, fetch lại từ RSS/tài chính/thời tiết, ghi cache
- `api.php` — chỉ đọc cache, KHÔNG BAO GIỜ fetch trực tiếp
- Trang luôn hoạt động ngay cả khi không ai truy cập (cron duy trì cache)

## Cấu trúc thư mục

```
NewsHub/
├── index.php              # Dashboard chính
├── api.php                # API endpoint JSON (chỉ đọc cache)
├── cron.php               # Cron job entrypoint (Ofelia gọi)
├── tv.php                 # Trang danh sách kênh truyền hình
├── worldclock.php         # Đồng hồ thế giới (real-time)
├── trends.php             # Xu hướng mạng xã hội
├── docker-compose.yml     # Docker Compose (PHP CLI + Ofelia cron)
├── includes/
│   └── functions.php      # RSS parser, cache, finance, weather, trends
├── assets/
│   ├── css/style.css      # Dark/light theme, responsive, TV, weather, clocks
│   └── js/app.js          # Charts, filters, pagination, ticker, geolocation
├── cache/                 # Cache JSON (do cron job tạo)
└── logs/                  # Caddy logs
```

## API endpoints

Tất cả endpoint chỉ đọc cache (do cron job tạo), không fetch RSS.

| Endpoint | Mô tả |
|----------|-------|
| `api.php?action=all` | Toàn bộ dữ liệu (articles, stats, finance, weather, trends, clocks) |
| `api.php?action=refresh` | Reload từ cache hiện tại (không fetch) |
| `api.php?action=stats` | Chỉ thống kê + tài chính + thời tiết |
| `api.php?action=finance` | Chỉ dữ liệu thị trường |
| `api.php?action=weather` | Chỉ dữ liệu thời tiết |
| `api.php?action=social` | Chỉ xu hướng mạng xã hội |
| `api.php?action=clocks` | Chỉ đồng hồ thế giới |
| Tham số `&location=Hanoi` | Gửi vị trí người dùng để nhận thời tiết + ưu tiên khu vực |

### Cron job

| Endpoint | Mô tả |
|----------|-------|
| `php cron.php` | Regenerate toàn bộ cache (gọi nội bộ, không public) |

## View modes

```
index.php?view=all       # Dashboard đầy đủ
index.php?view=news      # Tin tức toàn màn hình
index.php?view=finance   # Tài chính toàn màn hình
index.php?view=charts    # Biểu đồ + thống kê
index.php?view=breaking  # Tin nóng full screen
```

## Nguồn dữ liệu

- **RSS feeds**: VnExpress, Tuổi Trẻ, Dân trí, Vietnamnet, Thanh Niên, Znews, VietNamPlus, 24h, BBC, TechCrunch, Reddit, Hacker News
- **Tài chính**: Yahoo Finance (^VNINDEX, ^HNX, GC=F, SI=F, BZ=F, CL=F, USDVND=X), SJC, Petrolimex
- **Xu hướng**: Google Trends Vietnam, YouTube (Invidious API), TikTok (scrape + fallback), Facebook/Threads
- **Thời tiết**: wttr.in — Hà Nội, HCM, Đà Nẵng, Cần Thơ + tự động theo vị trí
- **TV**: VTV, THVL, HTV, VTC, Hanoitv, Danangtv, Canthotv, VTVcab (chuyển hướng đến trang chính thức)
