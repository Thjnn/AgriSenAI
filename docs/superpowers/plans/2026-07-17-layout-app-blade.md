# Layout `app.blade.php` Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dựng `resources/views/layouts/app.blade.php` — khung nav + footer + drawer + bottom-nav + chat dùng chung, tái hiện chính xác `Test-Giao-Dien/agrisenai-home.html`, đầy đủ responsive.

**Architecture:** Layout Blade gọi 6 anonymous component trong `resources/views/components/chrome/`. Toàn bộ link nằm trong `config/navigation.php`. Style bằng Tailwind v4 utility trên bộ token `@theme` lấy từ bảng màu demo. Logic phụ thuộc dữ liệu (bộ bottom-nav theo `$page`, nhánh guest/auth) được test bằng PHPUnit qua `data-*` attribute; phần thuần thị giác verify bằng so sánh trực quan với demo.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Tailwind CSS v4, Vite 8, PHPUnit 12.5

**Spec:** `docs/superpowers/specs/2026-07-17-layout-app-blade-design.md`

## Global Constraints

- **Nguồn màu duy nhất: `Test-Giao-Dien/assets/agrisen-ui.css` (khối `:root`).** Đã đối chiếu: cả 5 trang HTML trong `Test-Giao-Dien` đều dùng chung đúng bảng này, còn `agrisen-pages.css` / `agrisen-admin.css` không khai màu riêng. Khi phân vân về một màu, mở file đó ra tra — không tự chế, không lấy từ `home.blade.php`.
- KHÔNG dùng bảng màu Duolingo cũ của `resources/views/home.blade.php` (`#58CC02` `#46A302` `#E8F8D4` `#3C3C3C`). Nếu thấy các mã này xuất hiện trong code mới thì là sai.
- Xanh chính `#2e7d32`, xanh vừa `#43a047`, xanh nhạt `#e8f5e9`, nền footer `#1f5c22`, chữ `#212121` / `#757575`, chấm đỏ `#ef5350`.
- Bo góc: 14px (`rounded-card`), 10px (`rounded-card-sm`), 18px (`rounded-card-lg`).
- Không viết hex trực tiếp trong Blade — dùng token (`bg-brand`, `text-ink-sec`...). Ngoại lệ: các giá trị `rgba()` trong `shadow-[...]` mà demo hardcode, giữ nguyên.
- `--nav-h: 64px`, `--safe-b: env(safe-area-inset-bottom, 0px)`.
- Breakpoint: `sm` 640px, `tab` 900px (custom), `lg` 1024px, `xl` 1280px, cộng `max-[360px]`.
- Font Nunito, weight 400/500/600/700/800.
- Mobile (< 1024px) **không hiển thị footer** — đúng demo, không phải thiếu sót.
- Chat chỉ dựng vỏ — không nối AI, không hội thoại giả.
- KHÔNG sửa `resources/views/home.blade.php`, `resources/views/quality/`, hay bất kỳ file nào trong `Test-Giao-Dien/`.
- Mọi text hiển thị bằng tiếng Việt, copy nguyên văn từ demo.

---

### Task 1: Bảng màu, font, breakpoint

**Files:**
- Modify: `resources/css/app.css:3-24` (khối `@theme`)
- Modify: `vite.config.js:11-15` (khối `fonts`)

**Interfaces:**
- Produces: token Tailwind cho mọi task sau — `bg-brand` `text-brand` `bg-brand-light` `bg-brand-medium` `bg-brand-deep` `bg-brand-footer` `text-brand-mint` `text-ink` `text-ink-sec` `text-ink-light` `bg-surface` `bg-gray-50` `border-gray-100` `border-gray-200` `text-gray-300` `text-gray-400` `bg-danger` `rounded-card` `rounded-card-sm` `rounded-card-lg`, breakpoint `tab:`, và CSS var `--nav-h` / `--safe-b`.

- [ ] **Step 1: Thay khối `@theme` trong `resources/css/app.css`**

Thay toàn bộ khối `@theme { ... }` hiện tại (dòng 3-24) bằng:

```css
@theme {
  --font-sans: 'Nunito', ui-sans-serif, system-ui, -apple-system, sans-serif;

  /* ---------------------------------------------------------------
     Bảng màu copy nguyên văn từ :root của Test-Giao-Dien/assets/agrisen-ui.css
     (5 trang HTML trong Test-Giao-Dien đều dùng chung đúng bảng này).
     Tên biến đổi từ --green-* sang --color-brand-* để hợp quy ước Tailwind v4.
     --------------------------------------------------------------- */

  /* --green         */ --color-brand: #2e7d32;
  /* --green-medium  */ --color-brand-medium: #43a047;
  /* --green-light   */ --color-brand-light: #e8f5e9;
  /* --green-bg      */ --color-brand-bg: #eff8f0;
  /* --green-banner-1*/ --color-brand-banner-1: #d5edda;
  /* --green-banner-2*/ --color-brand-banner-2: #b8dfc0;

  /* Ba màu dưới đây demo hardcode trong agrisen-ui.css chứ không đặt tên biến;
     đưa vào đây để không phải viết hex rải rác trong Blade. */
  --color-brand-deep: #256427;    /* gradient đầu panel chat, :active nút gửi */
  --color-brand-footer: #1f5c22;  /* nền footer */
  --color-brand-mint: #9fe0a6;    /* chữ nhấn + icon liên hệ trong footer */

  /* --text       */ --color-ink: #212121;
  /* --text-sec   */ --color-ink-sec: #757575;
  /* --text-light */ --color-ink-light: #9e9e9e;

  /* --bg      */ --color-surface: #fafafa;
  /* --gray-50 */ --color-gray-50: #f5f5f5;
  /* --gray-100*/ --color-gray-100: #eeeeee;
  /* --gray-200*/ --color-gray-200: #e0e0e0;
  /* --gray-300*/ --color-gray-300: #bdbdbd;
  /* --gray-400*/ --color-gray-400: #9e9e9e;

  --color-danger: #ef5350;   /* chấm đỏ thông báo (demo hardcode) */
  --color-online: #7cfc9a;   /* chấm "đang hoạt động" ở panel chat */

  /* --shadow-sm/md/lg */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.07);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.08);

  /* --r / --r-sm / --r-lg — đổi tên để tránh đụng class rounded-r (bo góc phải) */
  --radius-card: 14px;
  --radius-card-sm: 10px;
  --radius-card-lg: 18px;

  --breakpoint-tab: 900px;
}
```

Bảng đối chiếu để người thực hiện kiểm lại — **mọi giá trị phải khớp tuyệt đối** với `Test-Giao-Dien/assets/agrisen-ui.css`:

| Demo (`agrisen-ui.css`) | Token Tailwind | Giá trị |
|---|---|---|
| `--green` | `--color-brand` | `#2e7d32` |
| `--green-medium` | `--color-brand-medium` | `#43a047` |
| `--green-light` | `--color-brand-light` | `#e8f5e9` |
| `--green-bg` | `--color-brand-bg` | `#eff8f0` |
| `--green-banner-1` | `--color-brand-banner-1` | `#d5edda` |
| `--green-banner-2` | `--color-brand-banner-2` | `#b8dfc0` |
| `--text` | `--color-ink` | `#212121` |
| `--text-sec` | `--color-ink-sec` | `#757575` |
| `--text-light` | `--color-ink-light` | `#9e9e9e` |
| `--bg` | `--color-surface` | `#fafafa` |
| `--gray-50` … `--gray-400` | `--color-gray-50` … `--color-gray-400` | `#f5f5f5` `#eeeeee` `#e0e0e0` `#bdbdbd` `#9e9e9e` |
| `--r` / `--r-sm` / `--r-lg` | `--radius-card` / `-sm` / `-lg` | `14px` / `10px` / `18px` |

Ba màu demo hardcode, không có tên biến: `#256427` → `--color-brand-deep`, `#1f5c22` → `--color-brand-footer`, `#9fe0a6` → `--color-brand-mint`, `#ef5350` → `--color-danger`, `#7cfc9a` → `--color-online`.

Lưu ý: `--color-gray-50..400` ghi đè thang xám mặc định của Tailwind bằng giá trị demo. **Chỉ dùng đúng 5 bậc này** (`gray-50` `gray-100` `gray-200` `gray-300` `gray-400`); `gray-500` trở lên vẫn là thang mặc định của Tailwind và sẽ lệch tông — không dùng.

- [ ] **Step 2: Thêm CSS var và nền body vào `resources/css/app.css`**

Thêm ngay sau khối `@theme`:

```css
@layer base {
  :root {
    --nav-h: 64px;
    --safe-b: env(safe-area-inset-bottom, 0px);
  }

  html {
    -webkit-text-size-adjust: 100%;
    scroll-behavior: smooth;
  }
}
```

`--nav-h` / `--safe-b` để ở `:root` chứ không ở `@theme` vì `env()` không dùng được trong `@theme`, và hai biến này chỉ phục vụ `calc()` chứ không cần sinh ra utility class.

- [ ] **Step 3: Đổi font trong `vite.config.js` sang Nunito**

Thay khối `fonts` trong `laravel({...})`:

```js
            fonts: [
                bunny('Nunito', {
                    weights: [400, 500, 600, 700, 800],
                }),
            ],
```

(Trước đó là `bunny('Instrument Sans', { weights: [400, 500, 600] })` — sai font, và thiếu weight 700/800 mà demo dùng cho tiêu đề.)

- [ ] **Step 4: Build để xác minh cấu hình hợp lệ**

Run: `npm run build`
Expected: build thành công, không lỗi; thư mục `public/build/assets/` có file `.css`.

- [ ] **Step 5: Xác minh bảng màu khớp demo, không sót và không lẫn màu cũ**

Run:

```bash
for c in 2e7d32 43a047 e8f5e9 eff8f0 d5edda b8dfc0 256427 1f5c22 9fe0a6 212121 757575 fafafa f5f5f5 eeeeee e0e0e0 bdbdbd 9e9e9e ef5350 7cfc9a; do
  printf "#%s -> %s\n" "$c" "$(grep -oci "#$c" resources/css/app.css)"
done
echo "--- mau cu (phai bang 0) ---"
grep -oci "58CC02\|46A302\|2B8700\|E8F8D4\|F0FBDF\|3C3C3C" resources/css/app.css
echo "--- font ---"
grep -c "Nunito" resources/css/app.css && grep -c "Nunito" vite.config.js
```

Expected: 19 dòng đầu **đều là 1** (mọi màu demo đều có mặt); dòng "màu cũ" phải là **0** (không còn sót token Duolingo `#58CC02`); hai dòng font đều ≥ 1.

Nếu có màu nào ra 0 → thiếu token, đối chiếu lại bảng ở Step 1. Nếu "màu cũ" ra khác 0 → khối `@theme` cũ chưa bị thay hết.

- [ ] **Step 6: Xác minh token thật sự đi vào CSS build ra**

Run: `grep -c "2e7d32" public/build/assets/*.css && grep -c "Nunito" public/build/assets/*.css`
Expected: cả hai đều trả về số ≥ 1. Nếu ra 0 nghĩa là token chưa được sinh — kiểm tra lại cú pháp `@theme`.

- [ ] **Step 7: Commit**

```bash
git add resources/css/app.css vite.config.js
git commit -m "Doi bang mau sang tong demo va nap font Nunito"
```

---

### Task 2: `config/navigation.php`

**Files:**
- Create: `config/navigation.php`
- Test: `tests/Feature/NavigationConfigTest.php`

**Interfaces:**
- Produces: `config('navigation.top_links')`, `config('navigation.drawer_groups')`, `config('navigation.bottom_nav')`, `config('navigation.footer_columns')`, `config('navigation.contact')`.
- Mỗi link là mảng `['key' => string, 'label' => string, 'href' => string, 'icon' => string]`. `key` khớp giá trị `$page`; `icon` khớp tên trong `<x-icon name="...">` ở Task 3.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/NavigationConfigTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class NavigationConfigTest extends TestCase
{
    public function test_bottom_nav_co_dung_hai_bo(): void
    {
        $sets = config('navigation.bottom_nav');

        $this->assertArrayHasKey('default', $sets);
        $this->assertArrayHasKey('advisory', $sets);
    }

    public function test_bo_default_dung_thu_tu_va_co_gio_hang(): void
    {
        $keys = array_column(config('navigation.bottom_nav.default'), 'key');

        $this->assertSame(['home', 'market', 'ai-scan', 'cart', 'account'], $keys);
    }

    public function test_bo_advisory_thay_gio_hang_bang_khuyen_nghi(): void
    {
        $keys = array_column(config('navigation.bottom_nav.advisory'), 'key');

        $this->assertSame(['home', 'market', 'ai-scan', 'khuyennghi', 'account'], $keys);
        $this->assertNotContains('cart', $keys);
    }

    public function test_top_links_co_du_nam_muc(): void
    {
        $this->assertCount(5, config('navigation.top_links'));
    }

    public function test_moi_link_deu_co_du_khoa_bat_buoc(): void
    {
        $groups = [
            config('navigation.top_links'),
            config('navigation.bottom_nav.default'),
            config('navigation.bottom_nav.advisory'),
            ...config('navigation.drawer_groups'),
        ];

        foreach ($groups as $group) {
            foreach ($group as $link) {
                $this->assertArrayHasKey('key', $link);
                $this->assertArrayHasKey('label', $link);
                $this->assertArrayHasKey('href', $link);
            }
        }
    }

    public function test_drawer_co_hai_nhom_dung_nhu_demo(): void
    {
        $groups = config('navigation.drawer_groups');

        // agrisenai-home.html: 5 link điều hướng, divider, rồi 3 link cá nhân.
        $this->assertCount(2, $groups);
        $this->assertSame(
            ['home', 'market', 'ai', 'khuyennghi', 'truyxuat'],
            array_column($groups[0], 'key')
        );
        $this->assertSame(
            ['cart', 'account', 'settings'],
            array_column($groups[1], 'key')
        );
    }

    public function test_nhan_drawer_copy_nguyen_van_tu_demo(): void
    {
        $labels = array_column(array_merge(...config('navigation.drawer_groups')), 'label', 'key');

        // Nhãn drawer dài hơn nhãn top-nav — dễ nhầm, nên chốt bằng test.
        $this->assertSame('AI đánh giá chất lượng', $labels['ai']);
        $this->assertSame('Khuyến nghị giá bán', $labels['khuyennghi']);
        $this->assertSame('Cài đặt', $labels['settings']);
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn nó fail**

Run: `php artisan test --filter=NavigationConfigTest`
Expected: FAIL — `config('navigation.bottom_nav')` trả về `null` vì file chưa tồn tại.

- [ ] **Step 3: Tạo `config/navigation.php`**

```php
<?php

/*
 * Toàn bộ liên kết điều hướng của khung giao diện public.
 * Nguồn: Test-Giao-Dien/agrisenai-home.html + assets/agrisen-chrome.js
 *
 * 'key'  khớp với biến $page mà trang truyền vào layout — quyết định mục nào active.
 * 'icon' khớp tên trong <x-icon name="...">.
 */
return [

    'top_links' => [
        ['key' => 'home',       'label' => 'Trang chủ',      'href' => '/'],
        ['key' => 'market',     'label' => 'Sàn thị trường', 'href' => '#'],
        ['key' => 'ai',         'label' => 'AI đánh giá',    'href' => '/danh-gia'],
        ['key' => 'khuyennghi', 'label' => 'Khuyến nghị',    'href' => '#'],
        ['key' => 'about',      'label' => 'Về chúng tôi',   'href' => '#'],
    ],

    /*
     * Drawer chia HAI NHÓM, ngăn bởi một đường kẻ (drawer-divider trong demo).
     * Đối chiếu: <nav class="drawer-nav"> trong agrisenai-home.html — 8 link, 1 divider
     * đặt sau 'truyxuat'. Nhãn copy nguyên văn ("AI đánh giá chất lượng", không phải
     * "AI đánh giá" — nhãn ngắn đó là của top-nav).
     *
     * Lưu ý: agrisen-chrome.js có thêm link 'notif' (Thông báo) mà agrisenai-home.html
     * không có. Bám theo home.html vì đó là bản demo được chọn làm chuẩn.
     */
    'drawer_groups' => [
        [
            ['key' => 'home',       'label' => 'Trang chủ',            'href' => '/',         'icon' => 'home'],
            ['key' => 'market',     'label' => 'Sàn thị trường',       'href' => '#',         'icon' => 'market'],
            ['key' => 'ai',         'label' => 'AI đánh giá chất lượng','href' => '/danh-gia','icon' => 'ai'],
            ['key' => 'khuyennghi', 'label' => 'Khuyến nghị giá bán',  'href' => '#',         'icon' => 'chart'],
            ['key' => 'truyxuat',   'label' => 'Quét QR truy xuất',    'href' => '#',         'icon' => 'qr'],
        ],
        [
            ['key' => 'cart',     'label' => 'Giỏ hàng',  'href' => '#', 'icon' => 'cart'],
            ['key' => 'account',  'label' => 'Tài khoản', 'href' => '#', 'icon' => 'user'],
            ['key' => 'settings', 'label' => 'Cài đặt',   'href' => '#', 'icon' => 'settings'],
        ],
    ],

    'bottom_nav' => [
        'default' => [
            ['key' => 'home',    'label' => 'Trang chủ',  'href' => '/',         'icon' => 'home'],
            ['key' => 'market',  'label' => 'Thị trường', 'href' => '#',         'icon' => 'market'],
            ['key' => 'ai-scan', 'label' => 'AI Scan',    'href' => '/danh-gia', 'icon' => 'scan'],
            ['key' => 'cart',    'label' => 'Giỏ hàng',   'href' => '#',         'icon' => 'cart'],
            ['key' => 'account', 'label' => 'Tài khoản',  'href' => '#',         'icon' => 'user'],
        ],
        'advisory' => [
            ['key' => 'home',       'label' => 'Trang chủ',  'href' => '/',         'icon' => 'home'],
            ['key' => 'market',     'label' => 'Thị trường', 'href' => '#',         'icon' => 'market'],
            ['key' => 'ai-scan',    'label' => 'AI Scan',    'href' => '/danh-gia', 'icon' => 'scan'],
            ['key' => 'khuyennghi', 'label' => 'Khuyến nghị','href' => '#',         'icon' => 'chart'],
            ['key' => 'account',    'label' => 'Tài khoản',  'href' => '#',         'icon' => 'user'],
        ],
    ],

    'footer_columns' => [
        [
            'title' => 'Khám phá',
            'links' => [
                ['label' => 'Trang chủ',        'href' => '/'],
                ['label' => 'Nông sản',         'href' => '#'],
                ['label' => 'AI đánh giá',      'href' => '/danh-gia'],
                ['label' => 'Khuyến nghị giá',  'href' => '#'],
                ['label' => 'Quét QR truy xuất','href' => '#'],
            ],
        ],
        [
            'title' => 'Hỗ trợ',
            'links' => [
                ['label' => 'Hướng dẫn sử dụng',    'href' => '#'],
                ['label' => 'Câu hỏi thường gặp',   'href' => '#'],
                ['label' => 'Chính sách bảo mật',   'href' => '#'],
                ['label' => 'Điều khoản dịch vụ',   'href' => '#'],
                ['label' => 'Về chúng tôi',         'href' => '#'],
            ],
        ],
    ],

    'contact' => [
        ['icon' => 'pin',   'text' => 'TP. Cao Lãnh, Tỉnh Đồng Tháp'],
        ['icon' => 'mail',  'text' => 'hotro@agrisenai.vn'],
        ['icon' => 'phone', 'text' => '1900 1234'],
    ],

];
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=NavigationConfigTest`
Expected: PASS — 7 test, 0 lỗi.

- [ ] **Step 5: Commit**

```bash
git add config/navigation.php tests/Feature/NavigationConfigTest.php
git commit -m "Them config/navigation.php cho khung dieu huong"
```

---

### Task 3: Component `<x-icon>`

**Files:**
- Create: `resources/views/components/icon.blade.php`
- Test: `tests/Feature/IconComponentTest.php`

**Interfaces:**
- Consumes: tên icon dùng trong `config/navigation.php` (Task 2).
- Produces: `<x-icon name="home" class="h-5 w-5" />` → thẻ `<svg>`. Tên hợp lệ: `home` `market` `ai` `chart` `qr` `cart` `user` `bell` `settings` `search` `chat` `close` `send` `fb` `yt` `zalo` `pin` `mail` `phone` `scan`. Tên lạ → không render gì.
- Độ dày nét: `<x-icon name="home" stroke-width="2.5" />` ghi đè mặc định `1.8`.

Gom SVG vào một chỗ vì icon `home`/`cart`/`user` bị lặp ở top-nav, drawer, bottom-nav và footer. Path SVG copy nguyên văn từ `Test-Giao-Dien/assets/agrisen-chrome.js` (biến `I`) — đó là bản đã gom sẵn của demo.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/IconComponentTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IconComponentTest extends TestCase
{
    public function test_render_ra_the_svg(): void
    {
        $html = Blade::render('<x-icon name="home" />');

        $this->assertStringContainsString('<svg', $html);
    }

    public function test_class_truyen_vao_duoc_gan_len_svg(): void
    {
        $html = Blade::render('<x-icon name="cart" class="h-5 w-5" />');

        $this->assertStringContainsString('h-5 w-5', $html);
    }

    public function test_ten_khong_ton_tai_thi_khong_render_gi(): void
    {
        $html = trim(Blade::render('<x-icon name="khong-co-icon-nay" />'));

        $this->assertSame('', $html);
    }

    public function test_stroke_width_mac_dinh_la_1_8(): void
    {
        $html = Blade::render('<x-icon name="home" />');

        $this->assertStringContainsString('stroke-width="1.8"', $html);
    }

    public function test_stroke_width_ghi_de_duoc_va_khong_bi_lap(): void
    {
        $html = Blade::render('<x-icon name="home" stroke-width="2.5" />');

        $this->assertStringContainsString('stroke-width="2.5"', $html);
        $this->assertStringNotContainsString('stroke-width="1.8"', $html);

        // Thuộc tính trùng thì trình duyệt lấy cái đầu — override sẽ hỏng âm thầm.
        $this->assertSame(1, substr_count($html, 'stroke-width='));
    }

    public function test_khong_icon_nao_render_stroke_width_hai_lan(): void
    {
        $names = ['home', 'market', 'ai', 'scan', 'chart', 'cart', 'user', 'bell',
                  'settings', 'search', 'close', 'send', 'zalo', 'pin', 'mail', 'phone'];

        foreach ($names as $name) {
            $html = Blade::render('<x-icon :name="$name" stroke-width="2.5" />', ['name' => $name]);

            $this->assertSame(
                1,
                substr_count($html, 'stroke-width='),
                "Icon '{$name}' render stroke-width nhiều hơn một lần"
            );
        }
    }

    public function test_scan_mang_net_day_2_theo_demo(): void
    {
        // I.scan trong agrisen-chrome.js hardcode stroke-width="2" (khác I.ai dùng 1.8)
        // vì nó là nút AI Scan tròn nổi ở bottom-nav. Giữ đúng như vậy.
        $this->assertStringContainsString('stroke-width="2"', Blade::render('<x-icon name="scan" />'));
        $this->assertStringContainsString('stroke-width="1.8"', Blade::render('<x-icon name="ai" />'));
    }

    public function test_moi_icon_duoc_config_nhac_den_deu_ton_tai(): void
    {
        $groups = [
            config('navigation.bottom_nav.default'),
            config('navigation.bottom_nav.advisory'),
            config('navigation.contact'),
            ...config('navigation.drawer_groups'),
        ];

        foreach ($groups as $group) {
            foreach ($group as $item) {
                $html = Blade::render('<x-icon :name="$name" />', ['name' => $item['icon']]);

                $this->assertStringContainsString('<svg', $html, "Thiếu icon: {$item['icon']}");
            }
        }
    }
}
```

Test cuối là chốt chặn thật: nó bắt lỗi khi ai đó thêm link vào config với icon chưa vẽ.

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=IconComponentTest`
Expected: FAIL — component `icon` chưa tồn tại.

- [ ] **Step 3: Tạo `resources/views/components/icon.blade.php`**

```blade
@props(['name', 'strokeWidth' => '1.8'])

{{--
    Kho SVG dùng chung cho khung giao diện.
    Path copy nguyên văn từ Test-Giao-Dien/assets/agrisen-chrome.js (biến I),
    riêng 'zalo' lấy từ footer của Test-Giao-Dien/agrisenai-home.html (chrome.js không có).
    Kích thước do class truyền từ ngoài quyết định, không hardcode.

    stroke-width phải là prop chứ không nằm trong chuỗi $stroke: nếu hardcode
    trong $stroke rồi đổ {{ $attributes }} sau, thẻ svg sẽ có hai thuộc tính
    stroke-width và trình duyệt lấy cái ĐẦU — mọi override đều bị nuốt im lặng.
    Blade tự ánh xạ thuộc tính stroke-width="2.5" sang $strokeWidth.
--}}

@php
    $stroke = 'fill="none" viewBox="0 0 24 24" stroke="currentColor"';
@endphp

@switch($name)
    @case('home')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
        @break

    @case('market')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
        @break

    @case('ai')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
        @break

    {{-- Cùng path với 'ai' nhưng nét dày 2 — I.scan trong chrome.js hardcode như vậy,
         vì nó dùng cho nút AI Scan tròn nổi ở bottom-nav, nền xanh chữ trắng. --}}
    @case('scan')
        <svg {!! $stroke !!} stroke-width="2" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
        @break

    @case('chart')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        @break

    @case('qr')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" {{ $attributes }}><path d="M0 0h24v24H0z" fill="none"/><path fill="currentColor" d="M3 4.5v5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5h-5C3.67 3 3 3.67 3 4.5M5 5h4v4H5zM3 19.5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5h-5c-.83 0-1.5.67-1.5 1.5zM5 15h4v4H5zM19.5 3h-5c-.83 0-1.5.67-1.5 1.5v5c0 .83.67 1.5 1.5 1.5h5c.83 0 1.5-.67 1.5-1.5v-5c0-.83-.67-1.5-1.5-1.5M19 9h-4V5h4zm-6 4h2v2h-2zm2 2h2v2h-2zm-2 2h2v2h-2zm4 0h2v2h-2zm2 2h2v2h-2zm-4 0h2v2h-2zm2-6h2v2h-2zm2 2h2v2h-2z"/></svg>
        @break

    @case('cart')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
        @break

    @case('user')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        @break

    @case('bell')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        @break

    @case('settings')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        @break

    @case('search')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" {{ $attributes }}><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
        @break

    @case('chat')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" {{ $attributes }}><path d="M14.343 3A6 6 0 0 0 22 10.657V18a1 1 0 0 1-1 1h-6.55L12 22.5L9.55 19H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm5.186-1.68a.507.507 0 0 1 .942 0l.253.61a4.37 4.37 0 0 0 2.25 2.327l.717.32a.53.53 0 0 1 0 .962l-.758.338a4.36 4.36 0 0 0-2.22 2.25l-.246.566a.506.506 0 0 1-.934 0l-.247-.565a4.36 4.36 0 0 0-2.219-2.251l-.76-.338a.53.53 0 0 1 0-.963l.718-.32a4.37 4.37 0 0 0 2.251-2.325z"/></svg>
        @break

    @case('close')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @break

    @case('send')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
        @break

    @case('fb')
        <svg viewBox="0 0 24 24" fill="currentColor" {{ $attributes }}><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5H17V3.6c-.3-.04-1.3-.13-2.47-.13-2.45 0-4.13 1.5-4.13 4.25v2.37H7.7V13h2.7v8h3.1z"/></svg>
        @break

    @case('yt')
        <svg viewBox="0 0 24 24" fill="currentColor" {{ $attributes }}><path d="M21.6 7.2a2.5 2.5 0 00-1.75-1.77C18.3 5 12 5 12 5s-6.3 0-7.85.43A2.5 2.5 0 002.4 7.2 26 26 0 002 12a26 26 0 00.4 4.8 2.5 2.5 0 001.75 1.77C5.7 19 12 19 12 19s6.3 0 7.85-.43a2.5 2.5 0 001.75-1.77A26 26 0 0022 12a26 26 0 00-.4-4.8zM10 15V9l5.2 3-5.2 3z"/></svg>
        @break

    @case('zalo')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
        @break

    @case('pin')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
        @break

    @case('mail')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        @break

    @case('phone')
        <svg {!! $stroke !!} stroke-width="{{ $strokeWidth }}" {{ $attributes }}><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
        @break
@endswitch
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=IconComponentTest`
Expected: PASS — 8 test, 0 lỗi.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/icon.blade.php tests/Feature/IconComponentTest.php
git commit -m "Them component x-icon gom SVG dung chung"
```

---

### Task 4: Model `Role` và helper vai trò trên `User`

**Files:**
- Create: `app/Models/Role.php`
- Modify: `app/Models/User.php`
- Test: `tests/Unit/UserRoleTest.php`

**Interfaces:**
- Produces: `$user->role` (BelongsTo Role), `$user->isFarmer(): bool`, `$user->accountHref(): string`.
- Layout dùng `isFarmer()` / `accountHref()` chứ không đọc thẳng `$user->role->name`, để chỗ nào cũng ra cùng một kết quả và test được mà không cần DB.

Bảng `roles` đã có sẵn trong migration `0001_01_01_000000_create_users_table.php` với cột `name` chứa `nong_dan` / `nguoi_mua` / `admin`; `users.role_id` là khoá ngoại. Task này chỉ thêm phần Eloquent còn thiếu.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Unit/UserRoleTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    // Kế thừa Tests\TestCase (có boot app) chứ không phải PHPUnit\Framework\TestCase:
    // User dùng #[Fillable] — attribute của Laravel 13 cần container để đọc.
    // Vẫn không đụng database vì chỉ dùng setRelation().

    public function test_nong_dan_thi_isFarmer_tra_ve_true(): void
    {
        $user = new User();
        $user->setRelation('role', new Role(['name' => 'nong_dan']));

        $this->assertTrue($user->isFarmer());
    }

    public function test_nguoi_mua_thi_isFarmer_tra_ve_false(): void
    {
        $user = new User();
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->assertFalse($user->isFarmer());
    }

    public function test_chua_co_role_thi_coi_nhu_nguoi_mua(): void
    {
        $user = new User();
        $user->setRelation('role', null);

        $this->assertFalse($user->isFarmer());
    }

    public function test_accountHref_khac_nhau_theo_vai_tro(): void
    {
        $farmer = new User();
        $farmer->setRelation('role', new Role(['name' => 'nong_dan']));

        $buyer = new User();
        $buyer->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->assertNotSame($farmer->accountHref(), $buyer->accountHref());
    }
}
```

`setRelation` nạp thẳng quan hệ vào bộ nhớ nên test chạy không cần database.

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=UserRoleTest`
Expected: FAIL — class `App\Models\Role` không tồn tại.

- [ ] **Step 3: Tạo `app/Models/Role.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Role extends Model
{
    public const NONG_DAN = 'nong_dan';
    public const NGUOI_MUA = 'nguoi_mua';
    public const ADMIN = 'admin';

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

`$timestamps = false` vì bảng `roles` chỉ có `created_at` (dùng `useCurrent()`), không có `updated_at`.

- [ ] **Step 4: Thêm quan hệ và helper vào `app/Models/User.php`**

Thêm `use` cần thiết ở đầu file:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

Rồi thêm vào trong class `User`, sau method `casts()`:

```php
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Người dùng có phải nông dân không. Không có vai trò thì coi như người mua. */
    public function isFarmer(): bool
    {
        return $this->role?->name === Role::NONG_DAN;
    }

    /** Đường dẫn trang tài khoản tương ứng vai trò. */
    public function accountHref(): string
    {
        return $this->isFarmer() ? '/tai-khoan/nong-dan' : '/tai-khoan/nguoi-mua';
    }
```

- [ ] **Step 5: Chạy test để xác nhận pass**

Run: `php artisan test --filter=UserRoleTest`
Expected: PASS — 4 test, 0 lỗi.

- [ ] **Step 6: Commit**

```bash
git add app/Models/Role.php app/Models/User.php tests/Unit/UserRoleTest.php
git commit -m "Them model Role va helper vai tro tren User"
```

---

### Task 5: Khung `layouts/app.blade.php`

**Files:**
- Create: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/LayoutSkeletonTest.php`

**Interfaces:**
- Consumes: `$page` (string, mặc định `''`).
- Produces: `@yield('title')`, `@yield('content')`, `@stack('head')`, `@stack('scripts')`; và các slot `<x-chrome.*>` sẽ được điền ở Task 6-10.
- Trang dùng: `@extends('layouts.app', ['page' => 'home'])`.

Task này dựng khung `<html>` + thẻ `<main>` trước; các component chrome tạm để trống rồi từng task sau thay thế. Làm vậy để mỗi task sau đều render được và test được ngay.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/LayoutSkeletonTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutSkeletonTest extends TestCase
{
    public function test_render_duoc_khi_khong_truyen_page(): void
    {
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('lang="vi"', $html);
    }

    public function test_co_the_main_boc_noi_dung(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('<main', $html);
    }

    public function test_title_mac_dinh_co_ten_thuong_hieu(): void
    {
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('AgriSenAi', $html);
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=LayoutSkeletonTest`
Expected: FAIL — view `layouts.app` không tồn tại.

- [ ] **Step 3: Tạo `resources/views/layouts/app.blade.php`**

```blade
{{--
    Khung giao diện public dùng chung.

    Cách dùng:
        @extends('layouts.app', ['page' => 'home'])
        @section('title', 'Trang chủ')
        @section('content') ... @endsection

    $page: home | market | ai | khuyennghi | cart | account | notif
           Quyết định mục nào active và bottom-nav dùng bộ nào.

    Tái hiện Test-Giao-Dien/agrisenai-home.html. Mobile (< lg) không có footer — đúng demo.
--}}
@php($page = $page ?? '')

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') — AgriSenAi @else AgriSenAi — Sàn nông sản Đồng Tháp @endif</title>
    <meta name="description" content="@yield('description', 'Sàn nông sản Đồng Tháp ứng dụng AI đánh giá chất lượng và khuyến nghị thời điểm bán tối ưu, kết nối nông dân với người mua.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

{{-- pb chừa chỗ cho bottom-nav; từ lg trở lên bottom-nav ẩn nên bỏ pb --}}
<body class="bg-surface font-sans text-ink antialiased pb-[calc(var(--nav-h)+var(--safe-b)+8px)] lg:pb-0">

    <x-chrome.top-nav :page="$page" />
    <x-chrome.mob-header />
    <x-chrome.drawer :page="$page" />

    <main class="mx-auto max-w-[1200px] px-5 py-4 max-[360px]:px-4 max-[360px]:py-3.5 sm:px-7 sm:py-5 tab:px-8 tab:py-6 lg:py-7 xl:max-w-[1320px] xl:px-12 xl:py-8">
        @yield('content')
    </main>

    <x-chrome.footer />
    <x-chrome.bottom-nav :page="$page" />
    <x-chrome.chat />

    @stack('scripts')
</body>

</html>
```

- [ ] **Step 4: Tạo 6 component rỗng để layout render được**

Mỗi file sẽ được thay bằng nội dung thật ở các task sau. Tạo trước để `<x-chrome.*>` không ném lỗi "component not found".

```bash
mkdir -p resources/views/components/chrome
printf '@props(["page" => ""])\n' > resources/views/components/chrome/top-nav.blade.php
printf '' > resources/views/components/chrome/mob-header.blade.php
printf '@props(["page" => ""])\n' > resources/views/components/chrome/drawer.blade.php
printf '' > resources/views/components/chrome/footer.blade.php
printf '@props(["page" => ""])\n' > resources/views/components/chrome/bottom-nav.blade.php
printf '' > resources/views/components/chrome/chat.blade.php
```

- [ ] **Step 5: Chạy test để xác nhận pass**

Run: `php artisan test --filter=LayoutSkeletonTest`
Expected: PASS — 3 test, 0 lỗi.

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/app.blade.php resources/views/components/chrome/
git commit -m "Them khung layouts/app.blade.php va cac component chrome rong"
```

---

### Task 6: Component `<x-chrome.bottom-nav>`

**Files:**
- Modify: `resources/views/components/chrome/bottom-nav.blade.php`
- Test: `tests/Feature/BottomNavTest.php`

**Interfaces:**
- Consumes: `config('navigation.bottom_nav')` (Task 2), `<x-icon>` (Task 3), `$user->accountHref()` (Task 4).
- Produces: mỗi mục mang `data-bnav="<key>"` để test bám vào; mục đang active thêm `data-bnav-active="1"`.

Đây là mảnh có logic thật nên làm trước các mảnh thuần thị giác. Quy tắc: `$page` ∈ `['ai', 'khuyennghi']` → bộ `advisory`; còn lại → `default`.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/BottomNavTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class BottomNavTest extends TestCase
{
    private function render(string $page): string
    {
        return view('layouts.app', ['page' => $page])->render();
    }

    public function test_trang_chu_dung_bo_default_co_gio_hang(): void
    {
        $html = $this->render('home');

        $this->assertStringContainsString('data-bnav="cart"', $html);
        $this->assertStringNotContainsString('data-bnav="khuyennghi"', $html);
    }

    public function test_trang_thi_truong_van_dung_bo_default(): void
    {
        $html = $this->render('market');

        $this->assertStringContainsString('data-bnav="cart"', $html);
        $this->assertStringNotContainsString('data-bnav="khuyennghi"', $html);
    }

    public function test_trang_ai_thi_khuyen_nghi_thay_cho_gio_hang(): void
    {
        $html = $this->render('ai');

        $this->assertStringContainsString('data-bnav="khuyennghi"', $html);
        $this->assertStringNotContainsString('data-bnav="cart"', $html);
    }

    public function test_trang_khuyen_nghi_cung_dung_bo_advisory(): void
    {
        $html = $this->render('khuyennghi');

        $this->assertStringContainsString('data-bnav="khuyennghi"', $html);
        $this->assertStringNotContainsString('data-bnav="cart"', $html);
    }

    public function test_page_la_khoa_khong_biet_thi_ve_bo_default(): void
    {
        $html = $this->render('khong-ton-tai');

        $this->assertStringContainsString('data-bnav="cart"', $html);
    }

    public function test_muc_dang_o_duoc_danh_dau_active(): void
    {
        $html = $this->render('market');

        $this->assertMatchesRegularExpression(
            '/data-bnav="market"[^>]*data-bnav-active="1"/',
            $html
        );
    }

    public function test_trang_ai_thi_nut_scan_active(): void
    {
        $html = $this->render('ai');

        $this->assertMatchesRegularExpression(
            '/data-bnav="ai-scan"[^>]*data-bnav-active="1"/',
            $html
        );
    }
}
```

Test cuối bắt một chi tiết dễ sót: `$page` là `ai` nhưng key của mục lại là `ai-scan` — nếu so khớp thẳng chuỗi thì nút AI Scan sẽ không bao giờ sáng.

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=BottomNavTest`
Expected: FAIL — component đang rỗng, không có `data-bnav` nào.

- [ ] **Step 3: Viết `resources/views/components/chrome/bottom-nav.blade.php`**

```blade
@props(['page' => ''])

@php
    // Ở trang AI đánh giá / Khuyến nghị thì Khuyến nghị thế chỗ Giỏ hàng.
    $set = in_array($page, ['ai', 'khuyennghi'], true) ? 'advisory' : 'default';
    $items = config("navigation.bottom_nav.{$set}");

    // Nút giữa mang key 'ai-scan' nhưng trang AI truyền $page = 'ai'.
    $activeKey = $page === 'ai' ? 'ai-scan' : $page;
@endphp

<nav class="fixed inset-x-0 bottom-0 z-[100] flex h-[var(--nav-h)] items-stretch justify-around border-t border-gray-100 bg-white pb-[var(--safe-b)] lg:hidden">
    @foreach ($items as $item)
        @php
            $isActive = $item['key'] === $activeKey;
            $href = $item['key'] === 'account' && auth()->check()
                ? auth()->user()->accountHref()
                : $item['href'];
        @endphp

        @if ($item['key'] === 'ai-scan')
            {{-- Nút tròn nổi lên khỏi thanh nav --}}
            <a href="{{ $href }}"
               data-bnav="{{ $item['key'] }}"
               @if ($isActive) data-bnav-active="1" @endif
               class="relative flex flex-1 flex-col items-center justify-end overflow-visible pb-[7px]">
                <span class="absolute -top-6 left-1/2 flex h-[54px] w-[54px] -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-brand shadow-[0_4px_14px_rgba(46,125,50,0.35)] transition-transform active:scale-[0.92]">
                    {{-- icon 'scan' tự mang nét dày 2 theo demo, không cần truyền stroke-width. --}}
                    <x-icon name="scan" class="h-6 w-6 text-white" />
                </span>
                <span class="mt-auto block text-[10px] font-bold leading-none text-brand">{{ $item['label'] }}</span>
            </a>
        @else
            <a href="{{ $href }}"
               data-bnav="{{ $item['key'] }}"
               @if ($isActive) data-bnav-active="1" @endif
               class="flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] font-semibold {{ $isActive ? 'text-brand' : 'text-gray-400' }}">
                <x-icon :name="$item['icon']" class="h-[22px] w-[22px]" :stroke-width="$isActive ? '2.5' : '1.8'" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=BottomNavTest`
Expected: PASS — 7 test, 0 lỗi.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/chrome/bottom-nav.blade.php tests/Feature/BottomNavTest.php
git commit -m "Bottom-nav doi bo theo trang: khuyen nghi thay gio hang o trang AI"
```

---

### Task 7: Component `<x-chrome.top-nav>`

**Files:**
- Modify: `resources/views/components/chrome/top-nav.blade.php`
- Test: `tests/Feature/TopNavTest.php`

**Interfaces:**
- Consumes: `config('navigation.top_links')` (Task 2), `<x-icon>` (Task 3), `$user->accountHref()` (Task 4).
- Produces: `data-topnav="<key>"` cho từng link, `data-topnav-active="1"` cho mục đang ở; `data-topnav-guest` / `data-topnav-auth` bọc hai nhánh đăng nhập.

Chỉ hiện từ 1024px (`hidden lg:block`).

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/TopNavTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class TopNavTest extends TestCase
{
    public function test_khach_thay_nut_dang_nhap_dang_ky(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-topnav-guest', $html);
        $this->assertStringNotContainsString('data-topnav-auth', $html);
        $this->assertStringContainsString('Đăng nhập', $html);
        $this->assertStringContainsString('Đăng ký', $html);
    }

    public function test_da_dang_nhap_thi_thay_chuong_gio_avatar(): void
    {
        $user = new User(['name' => 'Trần Thu Hà']);
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-topnav-auth', $html);
        $this->assertStringNotContainsString('data-topnav-guest', $html);
    }

    public function test_du_nam_link_dieu_huong(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertSame(5, substr_count($html, 'data-topnav="'));
    }

    public function test_link_dung_trang_duoc_danh_dau_active(): void
    {
        $html = view('layouts.app', ['page' => 'khuyennghi'])->render();

        $this->assertMatchesRegularExpression(
            '/data-topnav="khuyennghi"[^>]*data-topnav-active="1"/',
            $html
        );
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=TopNavTest`
Expected: FAIL — component đang rỗng.

- [ ] **Step 3: Viết `resources/views/components/chrome/top-nav.blade.php`**

```blade
@props(['page' => ''])

@php($links = config('navigation.top_links'))

<nav class="sticky top-0 z-[100] hidden border-b border-gray-100 bg-white lg:block">
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-8 xl:max-w-[1320px] xl:px-12">

        <a href="/" class="flex items-center gap-2.5 text-xl font-extrabold text-ink">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-card-sm">
                <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
            </span>
            AgriSenAi
        </a>

        <ul class="flex items-center gap-8">
            @foreach ($links as $link)
                @php($isActive = $link['key'] === $page)
                <li>
                    <a href="{{ $link['href'] }}"
                       data-topnav="{{ $link['key'] }}"
                       @if ($isActive) data-topnav-active="1" @endif
                       class="text-sm font-bold transition-colors hover:text-brand {{ $isActive ? 'text-brand' : 'text-ink-sec' }}">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-3">
            <div class="flex w-[260px] items-center gap-2 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[9px] transition-colors focus-within:border-brand">
                <x-icon name="search" class="h-4 w-4 shrink-0 text-gray-400" />
                <input type="text" placeholder="Tìm nông sản..."
                       class="w-full border-none bg-transparent text-[13px] font-medium text-ink outline-none placeholder:text-gray-300">
            </div>

            @auth
                <span data-topnav-auth class="flex items-center gap-3">
                    <a href="#" aria-label="Thông báo"
                       class="relative flex h-[38px] w-[38px] items-center justify-center rounded-full bg-gray-50 transition-colors hover:bg-gray-100">
                        <x-icon name="bell" class="h-[18px] w-[18px] text-ink" />
                        <span class="absolute right-[7px] top-[7px] h-2 w-2 rounded-full border-2 border-white bg-danger"></span>
                    </a>

                    <a href="#" aria-label="Giỏ hàng"
                       class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-gray-50 transition-colors hover:bg-gray-100">
                        <x-icon name="cart" class="h-[18px] w-[18px] text-ink" />
                    </a>

                    <a href="{{ auth()->user()->accountHref() }}" aria-label="Tài khoản"
                       class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-light">
                        <x-icon name="user" class="h-5 w-5 text-brand" />
                    </a>
                </span>
            @endauth

            @guest
                <span data-topnav-guest class="flex items-center gap-2">
                    <a href="#" class="px-4 py-2 text-sm font-bold text-ink-sec transition-colors hover:text-brand">Đăng nhập</a>
                    <a href="#" class="rounded-card bg-brand px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-brand-medium">Đăng ký</a>
                </span>
            @endguest
        </div>
    </div>
</nav>
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=TopNavTest`
Expected: PASS — 4 test, 0 lỗi.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/chrome/top-nav.blade.php tests/Feature/TopNavTest.php
git commit -m "Them top-nav desktop voi nhanh guest va auth"
```

---

### Task 8: Component `<x-chrome.mob-header>` và `<x-chrome.drawer>`

**Files:**
- Modify: `resources/views/components/chrome/mob-header.blade.php`
- Modify: `resources/views/components/chrome/drawer.blade.php`
- Test: `tests/Feature/DrawerTest.php`

**Interfaces:**
- Consumes: `config('navigation.drawer_groups')` (Task 2) — mảng gồm 2 nhóm, giữa hai nhóm phải có một đường kẻ ngăn (`drawer-divider` trong demo); `<x-icon>` (Task 3), `$user->isFarmer()` / `accountHref()` (Task 4).
- Produces: các id JS bám vào ở Task 10 — `#drawer`, `#drawerOverlay`, `#drawerOpen`, `#drawerClose`. Trạng thái mở = thêm class `open` (xem Task 10).

Gộp hai mảnh vào một task vì nút hamburger ở mob-header không có ý nghĩa nếu thiếu drawer — tách ra thì không task nào test độc lập được.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/DrawerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class DrawerTest extends TestCase
{
    public function test_co_du_moc_id_cho_javascript(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('id="drawer"', $html);
        $this->assertStringContainsString('id="drawerOverlay"', $html);
        $this->assertStringContainsString('id="drawerOpen"', $html);
        $this->assertStringContainsString('id="drawerClose"', $html);
    }

    public function test_khach_thay_nut_dang_nhap_o_chan_drawer(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-drawer-guest', $html);
        $this->assertStringNotContainsString('data-drawer-user', $html);
    }

    public function test_nong_dan_thay_dung_ten_va_vai_tro(): void
    {
        $user = new User(['name' => 'Nguyễn Văn Sáu']);
        $user->setRelation('role', new Role(['name' => 'nong_dan']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('data-drawer-user', $html);
        $this->assertStringContainsString('Nguyễn Văn Sáu', $html);
        $this->assertStringContainsString('Nông dân', $html);
    }

    public function test_nguoi_mua_thay_dung_vai_tro(): void
    {
        $user = new User(['name' => 'Trần Thu Hà']);
        $user->setRelation('role', new Role(['name' => 'nguoi_mua']));

        $this->actingAs($user);

        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('Người mua', $html);
    }

    public function test_link_drawer_dung_trang_duoc_danh_dau_active(): void
    {
        $html = view('layouts.app', ['page' => 'ai'])->render();

        $this->assertMatchesRegularExpression(
            '/data-drawer="ai"[^>]*data-drawer-active="1"/',
            $html
        );
    }

    public function test_co_du_tam_link_va_mot_duong_ke_ngan_nhom(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        // agrisenai-home.html: 8 link, 1 divider đặt sau 'truyxuat'.
        $this->assertSame(8, substr_count($html, 'data-drawer="'));

        foreach (['home', 'market', 'ai', 'khuyennghi', 'truyxuat', 'cart', 'account', 'settings'] as $key)  {
            $this->assertStringContainsString('data-drawer="' . $key . '"', $html);
        }
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=DrawerTest`
Expected: FAIL — cả hai component đang rỗng.

- [ ] **Step 3: Viết `resources/views/components/chrome/mob-header.blade.php`**

```blade
{{-- Header mobile, ẩn từ lg trở lên (chỗ đó dùng top-nav). --}}
<header class="sticky top-0 z-50 bg-white px-5 py-3.5 max-[360px]:px-4 max-[360px]:py-3 sm:px-7 sm:py-4 tab:px-8 lg:hidden">

    <div class="mb-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="button" id="drawerOpen" aria-label="Mở menu"
                    class="flex h-10 w-10 shrink-0 flex-col items-center justify-center gap-1 rounded-card-sm border-none bg-transparent active:bg-gray-50">
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
                <span class="block h-0.5 w-5 rounded-sm bg-ink"></span>
            </button>

            <a href="/" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[11px]">
                <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
            </a>

            <div>
                <div class="text-lg font-extrabold leading-[1.15]">Agri<span class="text-brand">SenAi</span></div>
                <div class="text-xs font-medium text-ink-sec">Sàn nông sản Đồng Tháp</div>
            </div>
        </div>

        <div class="flex gap-1.5">
            @auth
                <a href="#" aria-label="Thông báo"
                   class="relative flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 active:bg-gray-100">
                    <x-icon name="bell" class="h-5 w-5 text-ink" />
                    <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full border-2 border-white bg-danger"></span>
                </a>
                <a href="#" aria-label="Giỏ hàng"
                   class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 active:bg-gray-100">
                    <x-icon name="cart" class="h-5 w-5 text-ink" />
                </a>
            @endauth

            <a href="/danh-gia" aria-label="AI đánh giá"
               class="flex h-10 w-10 items-center justify-center rounded-full bg-brand">
                <x-icon name="scan" class="h-5 w-5 text-white" />
            </a>
        </div>
    </div>

    <div class="flex items-center gap-2.5 rounded-card border-[1.5px] border-gray-100 bg-gray-50 px-4 py-[11px] focus-within:border-brand">
        <x-icon name="search" class="h-[18px] w-[18px] shrink-0 text-gray-400" />
        <input type="text" placeholder="Tìm nông sản..."
               class="w-full border-none bg-transparent text-sm font-medium text-ink outline-none placeholder:text-gray-300">
    </div>
</header>
```

- [ ] **Step 4: Viết `resources/views/components/chrome/drawer.blade.php`**

```blade
@props(['page' => ''])

@php($groups = config('navigation.drawer_groups'))

{{-- Overlay + drawer. Class 'open' do resources/js/app.js gắn/gỡ. --}}
<div id="drawerOverlay"
     class="invisible fixed inset-0 z-[998] bg-black/40 opacity-0 transition-[opacity,visibility] duration-[250ms] lg:hidden"></div>

<aside id="drawer"
       class="fixed inset-y-0 left-0 z-[999] flex w-[78%] max-w-[300px] -translate-x-full flex-col bg-white shadow-[4px_0_24px_rgba(0,0,0,0.1)] transition-transform duration-[280ms] ease-[cubic-bezier(0.4,0,0.2,1)] lg:hidden">

    <div class="flex items-center gap-3 border-b border-gray-100 px-5 pb-5 pt-6">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
            <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
        </span>
        <div class="flex-1">
            <div class="text-[19px] font-extrabold">Agri<span class="text-brand">SenAi</span></div>
            <div class="text-[11px] font-semibold text-ink-sec">Sàn nông sản Đồng Tháp</div>
        </div>
        <button type="button" id="drawerClose" aria-label="Đóng menu"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-50 active:bg-gray-100">
            <x-icon name="close" class="h-4 w-4 text-ink" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-3">
        @foreach ($groups as $group)
            {{-- Đường kẻ ngăn giữa các nhóm (drawer-divider trong demo), không kẻ trước nhóm đầu. --}}
            @unless ($loop->first)
                <div class="mx-5 my-2.5 h-px bg-gray-100"></div>
            @endunless

            @foreach ($group as $link)
                @php($isActive = $link['key'] === $page)
                @php($href = $link['key'] === 'account' && auth()->check() ? auth()->user()->accountHref() : $link['href'])
                <a href="{{ $href }}"
                   data-drawer="{{ $link['key'] }}"
                   @if ($isActive) data-drawer-active="1" @endif
                   class="flex items-center gap-3.5 px-5 py-[13px] text-[15px] font-bold transition-colors active:bg-gray-50 {{ $isActive ? 'bg-brand-light text-brand' : 'text-ink' }}">
                    <x-icon :name="$link['icon']" class="h-[21px] w-[21px] shrink-0" />
                    {{ $link['label'] }}
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="border-t border-gray-100 px-5 pb-[22px] pt-4">
        @auth
            <a href="{{ auth()->user()->accountHref() }}" data-drawer-user
               class="flex items-center gap-3 rounded-card bg-gray-50 p-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-light">
                    <x-icon name="user" class="h-5 w-5 text-brand" />
                </span>
                <span>
                    <span class="block text-sm font-bold">{{ auth()->user()->name }}</span>
                    <span class="block text-[11px] font-semibold text-ink-sec">
                        {{ auth()->user()->isFarmer() ? 'Nông dân' : 'Người mua' }}
                    </span>
                </span>
            </a>
        @endauth

        @guest
            <a href="#" data-drawer-guest
               class="flex items-center justify-center rounded-card bg-brand px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-medium">
                Đăng nhập
            </a>
        @endguest
    </div>
</aside>
```

Ghi chú so với demo: demo không có nút X đóng drawer (chỉ đóng bằng overlay). Ở đây thêm `#drawerClose` vì đóng bằng overlay là thao tác không nhìn thấy được — người dùng bàn phím và người dùng đọc màn hình không có cách nào đóng. Đây là bổ sung có chủ đích, không phải lệch demo do sơ ý.

- [ ] **Step 5: Chạy test để xác nhận pass**

Run: `php artisan test --filter=DrawerTest`
Expected: PASS — 6 test, 0 lỗi.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/chrome/mob-header.blade.php resources/views/components/chrome/drawer.blade.php tests/Feature/DrawerTest.php
git commit -m "Them mob-header va drawer cho mobile"
```

---

### Task 9: Component `<x-chrome.footer>`

**Files:**
- Modify: `resources/views/components/chrome/footer.blade.php`
- Test: `tests/Feature/FooterTest.php`

**Interfaces:**
- Consumes: `config('navigation.footer_columns')` và `config('navigation.contact')` (Task 2), `<x-icon>` (Task 3).

Footer 4 cột: cột đầu là brand + mô tả + social (viết thẳng trong component), 3 cột sau lấy từ config (2 cột link + 1 cột liên hệ). **Ẩn dưới 1024px** — đúng demo.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/FooterTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class FooterTest extends TestCase
{
    public function test_an_tren_mobile_bang_class_hidden_lg_block(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertMatchesRegularExpression(
            '/<footer[^>]*class="[^"]*hidden[^"]*lg:block/',
            $html
        );
    }

    public function test_co_du_ba_cot_tu_config(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('Khám phá', $html);
        $this->assertStringContainsString('Hỗ trợ', $html);
        $this->assertStringContainsString('Liên hệ', $html);
    }

    public function test_co_thong_tin_lien_he(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('hotro@agrisenai.vn', $html);
        $this->assertStringContainsString('1900 1234', $html);
        $this->assertStringContainsString('TP. Cao Lãnh, Tỉnh Đồng Tháp', $html);
    }

    public function test_co_dong_ban_quyen(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('© 2026 AgriSenAi', $html);
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=FooterTest`
Expected: FAIL — component đang rỗng.

- [ ] **Step 3: Viết `resources/views/components/chrome/footer.blade.php`**

```blade
{{-- Footer chỉ hiện từ lg trở lên — đúng demo, mobile dùng bottom-nav thay thế. --}}
<footer class="mt-12 hidden bg-brand-footer text-white/[0.82] lg:block">

    <div class="mx-auto grid max-w-[1200px] grid-cols-[1.7fr_1fr_1fr_1.5fr] gap-10 px-8 pb-9 pt-[52px] xl:max-w-[1320px] xl:px-12">

        <div>
            <div class="mb-4 flex items-center gap-[11px]">
                <span class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl bg-white p-1">
                    <img src="{{ asset('images/logo-agrisenai-mark.png') }}" alt="AgriSenAi" class="block h-full w-full object-contain">
                </span>
                <span class="text-[21px] font-extrabold text-white">Agri<span class="text-brand-mint">SenAi</span></span>
            </div>

            <p class="mb-5 max-w-[300px] text-[13px] leading-[1.65]">
                Sàn nông sản Đồng Tháp ứng dụng AI đánh giá chất lượng và khuyến nghị thời điểm bán tối ưu, kết nối nông dân với người mua.
            </p>

            <div class="flex gap-2.5">
                @foreach ([['fb', 'Facebook'], ['yt', 'YouTube'], ['zalo', 'Zalo']] as [$icon, $label])
                    <a href="#" aria-label="{{ $label }}"
                       class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-white/[0.22]">
                        <x-icon :name="$icon" class="h-[18px] w-[18px] text-white" />
                    </a>
                @endforeach
            </div>
        </div>

        @foreach (config('navigation.footer_columns') as $col)
            <div>
                <h4 class="mb-4 text-[13px] font-extrabold uppercase tracking-[0.6px] text-white">{{ $col['title'] }}</h4>
                @foreach ($col['links'] as $link)
                    <a href="{{ $link['href'] }}"
                       class="block py-1.5 text-[13px] font-medium text-white/[0.78] transition-colors hover:text-white">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        @endforeach

        <div>
            <h4 class="mb-4 text-[13px] font-extrabold uppercase tracking-[0.6px] text-white">Liên hệ</h4>
            @foreach (config('navigation.contact') as $item)
                <div class="mb-[13px] flex items-start gap-2.5 text-[13px] leading-[1.5]">
                    <x-icon :name="$item['icon']" class="mt-0.5 h-4 w-4 shrink-0 text-brand-mint" />
                    <span>{{ $item['text'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-white/[0.13] p-5 text-center text-xs text-white/60">
        © {{ date('Y') }} AgriSenAi · Sàn nông sản Đồng Tháp. Mọi quyền được bảo lưu.
    </div>
</footer>
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=FooterTest`
Expected: PASS — 4 test, 0 lỗi.

Nếu test `test_co_dong_ban_quyen` fail vì năm hệ thống không phải 2026, đó là test sai chứ không phải code sai — sửa test thành `assertStringContainsString('AgriSenAi · Sàn nông sản Đồng Tháp', $html)`.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/chrome/footer.blade.php tests/Feature/FooterTest.php
git commit -m "Them footer 4 cot (chi hien tu 1024px nhu demo)"
```

---

### Task 10: Component `<x-chrome.chat>` (vỏ)

**Files:**
- Modify: `resources/views/components/chrome/chat.blade.php`
- Test: `tests/Feature/ChatShellTest.php`

**Interfaces:**
- Consumes: `<x-icon>` (Task 3).
- Produces: id cho JS ở Task 11 — `#chatFab`, `#chatPanel`, `#chatOverlay`, `#chatClose`.

Chỉ dựng vỏ: FAB + panel + đầu panel + khung tin nhắn rỗng + ô nhập. Không hội thoại giả, không gọi AI.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/ChatShellTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatShellTest extends TestCase
{
    public function test_co_du_moc_id_cho_javascript(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('id="chatFab"', $html);
        $this->assertStringContainsString('id="chatPanel"', $html);
        $this->assertStringContainsString('id="chatOverlay"', $html);
        $this->assertStringContainsString('id="chatClose"', $html);
    }

    public function test_fab_nam_goc_phai_duoi_va_khong_de_bottom_nav(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        // Mobile: đẩy lên trên bottom-nav. Desktop: về sát góc.
        $this->assertStringContainsString('bottom-[calc(var(--nav-h)+var(--safe-b)+16px)]', $html);
        $this->assertStringContainsString('lg:bottom-6', $html);
        $this->assertStringContainsString('lg:right-6', $html);
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=ChatShellTest`
Expected: FAIL — component đang rỗng.

- [ ] **Step 3: Viết `resources/views/components/chrome/chat.blade.php`**

```blade
{{--
    Vỏ trợ lý AI giá bán. Chỉ có phần nhìn — chưa nối AI, chưa có hội thoại.
    Class 'open' do resources/js/app.js gắn/gỡ.
--}}

<button type="button" id="chatFab" aria-label="Trợ lý AI giá bán"
        class="fixed right-5 bottom-[calc(var(--nav-h)+var(--safe-b)+16px)] z-[900] flex h-14 w-14 items-center justify-center rounded-full border-none bg-brand shadow-[0_6px_20px_rgba(46,125,50,0.42)] transition-transform active:scale-[0.92] hover:shadow-[0_8px_26px_rgba(46,125,50,0.52)] lg:bottom-6 lg:right-6">
    <x-icon name="chat" class="h-[26px] w-[26px] text-white" />
</button>

<div id="chatOverlay"
     class="invisible fixed inset-0 z-[1000] bg-black/40 opacity-0 transition-[opacity,visibility] duration-[250ms]"></div>

<aside id="chatPanel" aria-hidden="true"
       class="fixed inset-y-0 right-0 z-[1001] flex w-full max-w-[420px] translate-x-full flex-col bg-surface shadow-[-4px_0_24px_rgba(0,0,0,0.12)] transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]">

    <div class="flex shrink-0 items-center justify-between gap-3 bg-[linear-gradient(120deg,var(--color-brand-deep),var(--color-brand-medium))] px-[18px] py-4 text-white">
        <div class="flex items-center gap-[11px]">
            <span class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full bg-white/[0.18]">
                <x-icon name="chat" class="h-5 w-5 text-white" />
            </span>
            <div>
                <div class="text-[15px] font-extrabold">Trợ lý giá bán</div>
                <div class="mt-0.5 flex items-center gap-[5px] text-[11px] font-semibold text-white/85">
                    <span class="h-[7px] w-[7px] rounded-full bg-online shadow-[0_0_0_2px_rgba(124,252,154,0.3)]"></span>
                    Đang hoạt động
                </div>
            </div>
        </div>

        <button type="button" id="chatClose" aria-label="Đóng trợ lý"
                class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-full border-none bg-white/[0.16] text-white">
            <x-icon name="close" class="h-[18px] w-[18px]" />
        </button>
    </div>

    {{-- Khung tin nhắn — sẽ do feature chat đổ dữ liệu vào sau. --}}
    <div id="chatBody" class="flex flex-1 flex-col gap-3 overflow-y-auto p-[18px]"></div>

    <div class="flex shrink-0 items-center gap-2.5 border-t border-gray-100 bg-white px-3.5 pt-3 pb-[calc(12px+env(safe-area-inset-bottom,0px))]">
        <input type="text" placeholder="Hỏi về giá bán..."
               class="flex-1 rounded-[22px] border-[1.5px] border-gray-200 bg-white px-4 py-[11px] text-sm text-ink outline-none focus:border-brand">
        <button type="button" aria-label="Gửi"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-none bg-brand transition-colors active:bg-brand-deep">
            <x-icon name="send" class="h-5 w-5 text-white" />
        </button>
    </div>
</aside>
```

- [ ] **Step 4: Chạy test để xác nhận pass**

Run: `php artisan test --filter=ChatShellTest`
Expected: PASS — 2 test, 0 lỗi.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/chrome/chat.blade.php tests/Feature/ChatShellTest.php
git commit -m "Them vo tro ly chat (FAB + panel), chua noi AI"
```

---

### Task 11: JavaScript đóng/mở drawer và chat

**Files:**
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `#drawer`, `#drawerOverlay`, `#drawerOpen`, `#drawerClose` (Task 8); `#chatFab`, `#chatPanel`, `#chatOverlay`, `#chatClose` (Task 10).
- Produces: class `open` trên panel/overlay. Tailwind không sinh sẵn class `open` nên Task này cũng thêm rule tương ứng vào `app.css`.

- [ ] **Step 1: Thêm rule cho trạng thái `open` vào `resources/css/app.css`**

Thêm vào cuối file:

```css
/* Trạng thái mở của drawer và chat panel — class 'open' do resources/js/app.js gắn. */
@layer components {
  #drawer.open,
  #chatPanel.open {
    transform: translateX(0);
  }

  #drawerOverlay.open,
  #chatOverlay.open {
    opacity: 1;
    visibility: visible;
  }
}
```

- [ ] **Step 2: Viết `resources/js/app.js`**

Thay toàn bộ nội dung file:

```js
/**
 * Khung giao diện: đóng/mở drawer (menu mobile) và panel trợ lý chat.
 * Cả hai đều khoá scroll nền khi mở và đóng được bằng phím Esc.
 */

/**
 * Nối một panel trượt với nút mở, overlay và nút đóng.
 *
 * @param {object} refs
 * @param {string} refs.panel   id của panel
 * @param {string} refs.overlay id của lớp phủ
 * @param {string} refs.open    id của nút mở
 * @param {string} refs.close   id của nút đóng
 */
function bindSlidePanel({ panel, overlay, open, close }) {
    const panelEl = document.getElementById(panel);
    const overlayEl = document.getElementById(overlay);
    const openEl = document.getElementById(open);
    const closeEl = document.getElementById(close);

    if (!panelEl || !overlayEl) return;

    const setOpen = (isOpen) => {
        panelEl.classList.toggle('open', isOpen);
        overlayEl.classList.toggle('open', isOpen);
        panelEl.setAttribute('aria-hidden', String(!isOpen));
        // Khoá scroll nền để không cuộn trang phía sau khi panel đang mở.
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    openEl?.addEventListener('click', () => setOpen(true));
    closeEl?.addEventListener('click', () => setOpen(false));
    overlayEl.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panelEl.classList.contains('open')) {
            setOpen(false);
        }
    });
}

bindSlidePanel({
    panel: 'drawer',
    overlay: 'drawerOverlay',
    open: 'drawerOpen',
    close: 'drawerClose',
});

bindSlidePanel({
    panel: 'chatPanel',
    overlay: 'chatOverlay',
    open: 'chatFab',
    close: 'chatClose',
});
```

- [ ] **Step 3: Build để xác minh không lỗi cú pháp**

Run: `npm run build`
Expected: build thành công, không lỗi.

- [ ] **Step 4: Commit**

```bash
git add resources/js/app.js resources/css/app.css
git commit -m "Them JS dong/mo drawer va chat panel"
```

---

### Task 12: Trang demo và đối chiếu trực quan với prototype

**Files:**
- Create: `resources/views/layout-demo.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/LayoutDemoPageTest.php`

**Interfaces:**
- Consumes: toàn bộ layout từ Task 5-11.

Trang này để đối chiếu khung với demo và để bạn có mẫu copy khi code trang thật. Route đặt sau các route hiện có, không đụng `/` hay `/danh-gia`.

- [ ] **Step 1: Viết test thất bại**

Tạo `tests/Feature/LayoutDemoPageTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutDemoPageTest extends TestCase
{
    public function test_trang_demo_tra_ve_200(): void
    {
        $this->get('/layout-demo')->assertOk();
    }

    public function test_trang_demo_dung_khung_layout(): void
    {
        $response = $this->get('/layout-demo');

        $response->assertSee('data-bnav="cart"', false);
        $response->assertSee('id="chatFab"', false);
        $response->assertSee('<footer', false);
    }

    public function test_doi_page_thi_bottom_nav_doi_theo(): void
    {
        $this->get('/layout-demo?page=ai')
            ->assertSee('data-bnav="khuyennghi"', false)
            ->assertDontSee('data-bnav="cart"', false);
    }
}
```

- [ ] **Step 2: Chạy test để chắc chắn fail**

Run: `php artisan test --filter=LayoutDemoPageTest`
Expected: FAIL — 404, route chưa có.

- [ ] **Step 3: Tạo `resources/views/layout-demo.blade.php`**

```blade
@extends('layouts.app', ['page' => $page])

@section('title', 'Thử layout')

@section('content')
    <h1 class="mb-2 text-2xl font-extrabold text-ink">Trang thử layout</h1>
    <p class="mb-6 text-sm text-ink-sec">
        Trang này chỉ để đối chiếu khung nav/footer với
        <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">Test-Giao-Dien/agrisenai-home.html</code>.
        Đổi <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">?page=</code> để xem bottom-nav đổi bộ.
    </p>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach (['home', 'market', 'ai', 'khuyennghi', 'cart', 'account'] as $key)
            <a href="{{ url('/layout-demo?page=' . $key) }}"
               class="rounded-card px-3 py-1.5 text-xs font-bold {{ $page === $key ? 'bg-brand text-white' : 'bg-gray-100 text-ink-sec hover:bg-gray-200' }}">
                {{ $key }}
            </a>
        @endforeach
    </div>

    {{-- Khối cao để kiểm tra sticky nav và chỗ chừa cho bottom-nav. --}}
    @for ($i = 1; $i <= 12; $i++)
        <div class="mb-3 rounded-card border border-gray-100 bg-white p-5">
            <div class="text-sm font-bold text-ink">Khối nội dung {{ $i }}</div>
            <div class="text-[13px] text-ink-sec">Cuộn xuống để kiểm tra nav dính trên và bottom-nav dưới.</div>
        </div>
    @endfor
@endsection
```

- [ ] **Step 4: Thêm route vào `routes/web.php`**

Thêm vào cuối file:

```php
// Trang thử khung giao diện — đối chiếu với Test-Giao-Dien/agrisenai-home.html
Route::get('/layout-demo', function (Illuminate\Http\Request $request) {
    return view('layout-demo', ['page' => $request->query('page', 'home')]);
});
```

- [ ] **Step 5: Chạy test để xác nhận pass**

Run: `php artisan test --filter=LayoutDemoPageTest`
Expected: PASS — 3 test, 0 lỗi.

- [ ] **Step 6: Chạy toàn bộ test**

Run: `php artisan test`
Expected: PASS toàn bộ. Nếu `ExampleTest` fail thì đó là test có sẵn của Laravel gọi `/` — không thuộc phạm vi task này.

- [ ] **Step 7: Đối chiếu trực quan với demo**

```bash
npm run build
php artisan serve
```

Mở song song hai cửa sổ:
- `http://127.0.0.1:8000/layout-demo`
- `Test-Giao-Dien/agrisenai-home.html` (mở thẳng bằng trình duyệt)

Dùng DevTools device toolbar, kiểm tra ở **360px, 640px, 900px, 1024px, 1280px**. Đối chiếu:

| Điểm kiểm | Kỳ vọng |
|---|---|
| < 1024px | có mob-header + bottom-nav, **không** footer |
| ≥ 1024px | có top-nav + footer, **không** mob-header/bottom-nav/drawer |
| chat-fab | luôn ở góc phải dưới; < 1024px nằm **trên** bottom-nav, không đè |
| Nút AI Scan | tròn, nổi lên khỏi thanh nav, viền trắng 4px |
| Cuộn trang | top-nav (desktop) và mob-header dính trên |
| Drawer | mở bằng hamburger, đóng bằng overlay / nút X / Esc; nền không cuộn khi đang mở |
| Chat panel | mở bằng FAB, trượt từ phải, đóng được bằng Esc |
| Font | Nunito |
| `?page=ai` | bottom-nav hiện Khuyến nghị thay Giỏ hàng, nút AI Scan sáng |

**Kiểm màu bằng công cụ, không ước lượng bằng mắt.** Mắt rất dễ bỏ qua chênh lệch một hai bậc xám. Mở DevTools → color picker, hút màu ở đúng các điểm sau trên cả hai trang và so số:

| Điểm hút màu | Phải bằng |
|---|---|
| Nền nút AI Scan | `#2e7d32` |
| Chữ link nav đang active | `#2e7d32` |
| Chữ link nav không active | `#757575` |
| Nền footer | `#1f5c22` |
| Chữ "SenAi" trong footer | `#9fe0a6` |
| Nền ô tìm kiếm | `#f5f5f5` |
| Viền ô tìm kiếm | `#eeeeee` |
| Chấm đỏ trên chuông | `#ef5350` |
| Nền drawer link đang active | `#e8f5e9` |
| Chấm "đang hoạt động" ở chat | `#7cfc9a` |

Nếu thấy bất kỳ sắc xanh nõn chuối nào (`#58CC02`) thì token cũ còn sót — quay lại Task 1.

Ghi lại chỗ nào lệch; sửa rồi kiểm lại trước khi commit.

- [ ] **Step 8: Commit**

```bash
git add resources/views/layout-demo.blade.php routes/web.php tests/Feature/LayoutDemoPageTest.php
git commit -m "Them trang /layout-demo de doi chieu khung voi prototype"
```

---

## Ghi chú cho người thực hiện

**Về chênh lệch có chủ đích so với demo** — ba chỗ sau là cố ý, đừng "sửa lại cho giống":

1. Drawer có nút X đóng (demo chỉ đóng bằng overlay) — lý do ở Task 8.
2. Nhánh guest ở top-nav và drawer là thiết kế mới — demo luôn ở trạng thái đã đăng nhập.
3. Chat panel rỗng — demo có hội thoại giả, ở đây cố ý bỏ.

**Về `@auth` lúc này luôn false** — project chưa cài auth package. Trang render ra sẽ hiện nút Đăng nhập/Đăng ký. Đúng như thiết kế, không phải lỗi. Test nhánh `@auth` dùng `actingAs()` với `User` dựng bằng `setRelation()` nên chạy được không cần DB.

**Nếu Tailwind không sinh class** — v4 quét file để biết class nào được dùng. Class ghép chuỗi động (`'text-' . $color`) sẽ không được sinh. Kế hoạch này luôn viết class đầy đủ trong `@if`/ternary nên không dính, nhưng nếu bạn sửa thì giữ nguyên nguyên tắc đó.
