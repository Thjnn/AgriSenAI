# Thiết kế: `layouts/app.blade.php` — khung nav + footer dùng chung

Ngày: 2026-07-17
Trạng thái: đã duyệt, chờ lập kế hoạch triển khai

## Mục tiêu

Dựng layout Blade dùng chung cho các trang public của AgriSenAi, tái hiện **chính xác** khung giao diện của prototype `Test-Giao-Dien/agrisenai-home.html`, viết bằng Tailwind v4 utility classes trên bộ token `@theme` trong `resources/css/app.css` (token này sẽ được thay sang bảng màu demo — xem "Xung đột bảng màu").

## Bối cảnh

- Prototype `Test-Giao-Dien/` đã có khung hoàn chỉnh: `assets/agrisen-chrome.js` sinh nav/drawer/mob-header/footer/bottom-nav/chat, `assets/agrisen-ui.css` (59KB CSS thuần) là nguồn thông số giao diện.
- Laravel mới ở giai đoạn đầu: chỉ có `home.blade.php` (2.579 dòng CSS inline) và `quality/layout.blade.php` (layout tối giản, không liên quan khung này). Chưa có `layouts/app.blade.php`.
- `resources/css/app.css` đã cài Tailwind v4 + khai báo `@theme`, nhưng token trong đó (`--color-brand: #58CC02`) lấy từ `resources/views/home.blade.php` — **không phải** bảng màu của prototype. Xem "Xung đột bảng màu" bên dưới.
- `vite.config.js` đang nạp font `Instrument Sans` qua bunny, trong khi cả `app.css` lẫn prototype đều dùng **Nunito** — Nunito hiện chưa được nạp.
- Chưa có auth package (không Breeze/Jetstream/Fortify). DB đã có bảng `roles` (`nong_dan` / `nguoi_mua` / `admin`) và `users.role_id`, `users.avatar`.

## Xung đột bảng màu (đã giải quyết)

Project đang tồn tại hai hệ thiết kế khác hẳn nhau:

| | Demo `agrisenai-home.html` | `app.css` + `home.blade.php` |
|---|---|---|
| Xanh chính | `#2e7d32` (Material green 800) | `#58CC02` (Duolingo) |
| Xanh nhạt | `#e8f5e9` | `#E8F8D4` |
| Chữ | `#212121` / `#757575` | `#3C3C3C` / `#777777` |
| Viền | `#eeeeee` / `#e0e0e0` | `#E5E5E5` / `#CDCDCD` |
| Bo góc | 14 / 10 / 18px | 16 / 24px |

**Quyết định: lấy bảng màu demo (`#2e7d32`).** Token trong `app.css` sẽ được thay bằng bảng màu prototype. `home.blade.php` không bị ảnh hưởng vì nó dùng CSS inline riêng, chưa đụng tới token.

## Quyết định thiết kế

**Tailwind utility, không bê `agrisen-ui.css`.** Khớp hạ tầng Vite + Tailwind v4 đã dựng sẵn, tránh hai hệ CSS song song. `agrisen-ui.css` chỉ dùng làm nguồn tra thông số (màu, kích thước, breakpoint).

**Font Nunito nạp qua Vite.** Sửa `vite.config.js` từ `bunny('Instrument Sans')` sang `bunny('Nunito', { weights: [400,500,600,700,800] })` — khớp weight demo đang dùng.

**Có `@auth` / `@guest` ngay từ đầu.** Auth chắc chắn nằm trong kế hoạch (đã có migration `users`/`roles`, có prototype trang đăng nhập/đăng ký). Làm sẵn nhánh khách rẻ hơn gỡ ra sau. Trạng thái đã-đăng-nhập khớp demo tuyệt đối.

**Chat widget: chỉ phần vỏ.** FAB + panel + đóng/mở khớp demo; logic hội thoại AI để feature riêng, không nhét vào layout.

**Mobile không có footer.** Đúng như demo (`.site-footer { display: none }`, chỉ hiện từ 1024px) và đã được xác nhận là chủ ý — bottom-nav thay vai trò footer trên mobile.

## Cấu trúc file

```
resources/views/layouts/app.blade.php        ← <html>, head, body, ráp các mảnh
resources/views/components/chrome/
    top-nav.blade.php      (≥1024)
    mob-header.blade.php   (<1024)
    drawer.blade.php       (<1024)
    bottom-nav.blade.php   (<1024)
    footer.blade.php       (≥1024)
    chat-fab.blade.php     (mọi breakpoint)
resources/views/components/icon.blade.php    ← kho SVG dùng lại
config/navigation.php                        ← toàn bộ link, một chỗ duy nhất
resources/css/app.css                        ← thêm token còn thiếu
resources/js/app.js                          ← drawer + chat toggle
```

Tách 6 mảnh vì mỗi mảnh có breakpoint và trạng thái auth riêng; gộp một file sẽ thành ~800 dòng khó sửa — đúng bệnh của 5 trang prototype hiện tại.

`components/icon.blade.php` gom SVG bị lặp (icon `home`/`cart`/`user` xuất hiện ở cả top-nav, drawer, bottom-nav, footer): `<x-icon name="cart" class="w-6 h-6"/>`.

## Giao diện sử dụng

```blade
@extends('layouts.app', ['page' => 'home'])
@section('title', 'Trang chủ')
@section('content')
    ...
@endsection
```

`$page` là khoá điều hướng, thay cho `data-page` của prototype. Giá trị hợp lệ: `home` | `market` | `ai` | `khuyennghi` | `cart` | `account` | `notif`. Mặc định chuỗi rỗng (không mục nào `active`). Nó quyết định mục nào `active` và bottom-nav dùng bộ nào.

`layouts/app.blade.php` cung cấp: `@yield('title')`, `@yield('content')`, `@stack('head')`, `@stack('scripts')`.

## config/navigation.php

Trả về mảng gồm:

- `top_links` — 5 mục: Trang chủ, Sàn thị trường, AI đánh giá, Khuyến nghị, Về chúng tôi
- `drawer_links` — điều hướng mobile
- `bottom_nav` — hai bộ (xem dưới)
- `footer_columns` — 3 cột link: Khám phá / Hỗ trợ / Liên hệ

Footer demo có 4 cột: cột đầu là brand + mô tả + social (render thẳng trong component, không qua config), 3 cột sau lấy từ `footer_columns`.

## Bottom-nav theo ngữ cảnh

```php
'bottom_nav' => [
    'default'  => ['home', 'market', 'ai-scan', 'cart',       'account'],
    'advisory' => ['home', 'market', 'ai-scan', 'khuyennghi', 'account'],
]
```

Component tự chọn bộ: `$page` thuộc `['ai', 'khuyennghi']` → `advisory` (Khuyến nghị thế chỗ Giỏ hàng); mọi giá trị khác → `default`. `ai-scan` luôn là nút tròn nổi ở giữa (`.bnav-scan-circle` trong demo).

## Trạng thái đăng nhập

| Vùng | `@guest` | `@auth` |
|---|---|---|
| top-nav phải | Đăng nhập / Đăng ký | ô tìm kiếm, chuông + chấm đỏ, giỏ, avatar |
| drawer chân | nút "Đăng nhập" | avatar + tên + vai trò |
| bottom-nav "Tài khoản" | → `/dang-nhap` | → trang tài khoản theo `$user->role->name` |

Vai trò lấy từ `$user->role->name` (`nong_dan` / `nguoi_mua`), quyết định đích của mục Tài khoản và nội dung hồ sơ ở drawer.

Nhánh khách là thiết kế mới (demo không có mẫu), dựng theo ngôn ngữ của demo: nút "Đăng ký" nền `--color-brand` chữ trắng bo góc 14px (`--radius-card`), nút "Đăng nhập" dạng ghost chữ `--color-ink-sec` hover sang `--color-brand`.

(Mô tả "viền dưới 4px" ở bản spec trước là của hệ Duolingo `#58CC02` — đã bỏ cùng bảng màu đó.)

Vì auth chưa cài, `@auth` sẽ luôn false lúc này — layout render ra nhánh khách. Đây là hành vi đúng, không phải lỗi.

## Responsive

Bê nguyên thông số demo:

- **< 1024px:** mob-header sticky, drawer trượt trái + overlay, bottom-nav cố định, **footer ẩn**, `body` chừa `padding-bottom: calc(var(--nav-h) + var(--safe-b) + 8px)`
- **≥ 1024px:** top-nav sticky, footer hiện, không drawer/bottom-nav
- **chat-fab:** `right: 20px; bottom: calc(var(--nav-h) + var(--safe-b) + 16px)` — mobile nằm trên bottom-nav, không đè; từ 1024px về `right: 24px; bottom: 24px`
- **Token:** `--nav-h: 64px`, `--safe-b: env(safe-area-inset-bottom, 0px)`
- **Breakpoint:** Tailwind có sẵn `sm:640` `lg:1024` `xl:1280`; demo còn dùng 900px và 360px → thêm `--breakpoint-tab: 900px` vào `@theme`, dùng `max-[360px]:` cho máy nhỏ

## JavaScript

Vanilla trong `resources/js/app.js` (~40 dòng), không thêm Alpine — chỉ có 2 thứ cần toggle:

- Drawer: mở qua nút hamburger, đóng qua overlay / nút X / phím Esc; khoá scroll nền khi mở
- Chat panel: toggle qua FAB, đóng qua overlay / nút X / Esc

## Phạm vi loại trừ

- `quality/layout.blade.php` giữ nguyên (đang chạy)
- `home.blade.php` giữ nguyên — việc chuyển nó sang layout mới là công việc riêng
- Layout admin (`layouts/admin.blade.php`) và trang đăng nhập/đăng ký (`layouts/auth.blade.php`) dùng khung khác, tách sau
- 5 trang prototype cũ trong `Test-Giao-Dien/` không đụng tới
- Logic chat AI, logic giỏ hàng, đếm số thông báo — chỉ dựng vỏ, không nối dữ liệu

## Tiêu chí hoàn thành

- Render một trang mẫu dùng `@extends('layouts.app', ['page' => 'home'])`, so sánh trực quan với `agrisenai-home.html` ở 360px, 640px, 900px, 1024px, 1280px — khung phải trùng khớp
- Bottom-nav đổi đúng bộ khi `$page` là `ai` / `khuyennghi`
- chat-fab không đè bottom-nav ở mọi breakpoint < 1024px
- Drawer mở/đóng được bằng cả overlay và Esc
- `npm run build` chạy sạch
