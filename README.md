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
- **Theme sáng/tối** — toggle, lưu localStorage, mặc định dark
- **Đếm ngược tự động** — countdown đến lần làm mới tiếp theo (60s)
- **View modes** — `?view=all|news|finance|charts|breaking` — mỗi tab màn hình riêng
- **TV Trực tiếp** — `tv.php` — xem VTV1-3, THVL1-2, HTV7/9, VTC1, H1/H2, ĐN1, CT1, VTVcab1 — chế độ grid/single
- **Responsive** — 3 breakpoint (1100/768/480px)
- **Tự động cập nhật** mỗi 60 giây, cache backend 5 phút

## Yêu cầu

- PHP 8.0+ (với `simplexml`, `mbstring`, `json`)
- Web server (Caddy, Nginx, Apache) hoặc Docker
- Kết nối internet để fetch RSS & dữ liệu tài chính

## Cài đặt

```bash
# 1. Clone
git clone https://github.com/your-username/newshub.git
cd newshub

# 2. Cấp quyền ghi cache
chmod 755 cache/

# 3. (Docker) Khởi động
docker compose up -d

# 4. Truy cập
# http://localhost/NewsHub/
```

## Cấu trúc thư mục

```
NewsHub/
├── index.php              # Dashboard chính
├── api.php                # API endpoint JSON
├── tv.php                 # Trang xem TV trực tiếp
├── worldclock.php         # Đồng hồ thế giới (real-time)
├── trends.php             # Xu hướng mạng xã hội
├── includes/
│   └── functions.php      # RSS parser, cache, finance, weather, trends
├── assets/
│   ├── css/style.css      # Dark/light theme, responsive, TV, weather, clocks
│   └── js/app.js          # Charts, filters, pagination, ticker, geolocation
└── cache/                 # Cache JSON (tự động tạo)
```

## API endpoints

| Endpoint | Mô tả |
|----------|-------|
| `api.php?action=all` | Toàn bộ dữ liệu (articles, stats, finance, weather, trends, clocks) |
| `api.php?action=refresh` | Xoá cache, fetch lại từ RSS |
| `api.php?action=stats` | Chỉ thống kê + tài chính + thời tiết |
| `api.php?action=finance` | Chỉ dữ liệu thị trường |
| `api.php?action=weather` | Chỉ dữ liệu thời tiết |
| `api.php?action=social` | Chỉ xu hướng mạng xã hội |
| `api.php?action=clocks` | Chỉ đồng hồ thế giới |
| Tham số `&location=Hanoi` | Gửi vị trí người dùng để nhận thời tiết + ưu tiên khu vực |

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
- **TV**: VTV, THVL, HTV, VTC, Hanoitv, Danangtv, Canthotv, VTVcab
