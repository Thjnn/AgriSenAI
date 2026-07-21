# QR Truy xuất nguồn gốc (Digital Product Passport) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Trang công khai `/trace/{qr_code}` hiển thị "nhật ký chất lượng" của một sản phẩm (ảnh AI, điểm, nhà vườn, timeline) kèm **xác minh toàn vẹn bằng SHA-256** — sửa DB thì dải xác minh chuyển đỏ.

**Architecture:** Dựng trên nền marketplace đã có (Product, AiQualityAssessment, TraceabilityLog, FarmerProfile, `products.qr_code`). Thêm 2 cột hash vào `ai_quality_assessments`, một `TraceabilityService` (computeHash + verify), route public read-only `/trace/{product:qr_code}`, view mobile-first, QR sinh on-the-fly bằng simple-qrcode (SVG). Dữ liệu demo (đã verify được) đến từ seeder marketplace được nâng cấp — KHÔNG phụ thuộc luồng chấm AI live.

**Tech Stack:** Laravel 12, PHP 8.3, MySQL (dev) / SQLite :memory: (test), Blade, simplesoftwareio/simple-qrcode (SVG), PHPUnit.

## Global Constraints

- Migration chỉ THÊM 2 cột nullable vào `ai_quality_assessments` (`image_sha256` VARCHAR(64), `integrity_hash` VARCHAR(64)) — KHÔNG tạo bảng mới, KHÔNG đổi cột khác, KHÔNG bảng `batches`.
- `qr_code` đã là ULID (từ seeder marketplace) — unguessable; KHÔNG dùng `products.id` làm mã.
- **Append-only:** KHÔNG có route/UI nào sửa hay xoá `ai_quality_assessments`. Chỉ có GET.
- Trang tra cứu **PUBLIC, read-only, không auth**, chỉ nhận `qr_code`.
- Route binding `/trace/{product:qr_code}` (scoped) — **KHÔNG** thêm `getRouteKeyName()='qr_code'` toàn cục vào `Product` (sẽ ảnh hưởng mọi `{product}` khác).
- `verify()` trả `verified` khi hash canonical KHỚP **VÀ** ảnh khớp `image_sha256`; `mismatch` khi lệch; `unverified` khi bản ghi chưa có `integrity_hash`.
- Canonical hash (mục 5 spec) — đúng thứ tự: `qr_code | product_id | quality_score | ripeness_level | defect_detected(1/0) | image_sha256 | farmer_id | assessed_at(ISO8601)`.
- Cần `php artisan storage:link` để ảnh AI hiển thị (`public/storage` chưa link).
- QR chỉ chứa URL tuyệt đối `{APP_URL}/trace/{qr_code}` — không nhúng ảnh/điểm. Sinh on-the-fly, không lưu file QR.
- Test extend `Tests\TestCase`, dùng `RefreshDatabase` (sqlite :memory:).
- **Ngoài phạm vi:** nối vào luồng chấm AI live (`recordAssessment` gắn `QualityController` — hiện trang AI không tạo Product); thanh toán; auth thật. Demo chạy hoàn toàn bằng seeder.

---

### Task 1: Migration +2 cột hash + model fillable

**Files:**
- Create: `database/migrations/2026_07_21_000001_add_integrity_to_ai_quality_assessments.php`
- Modify: `app/Models/AiQualityAssessment.php`
- Test: `tests/Feature/AssessmentIntegrityColumnsTest.php`

**Interfaces:**
- Produces: cột `ai_quality_assessments.image_sha256` (nullable, 64) và `integrity_hash` (nullable, 64); `AiQualityAssessment` mass-assign được 2 cột này.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/AssessmentIntegrityColumnsTest.php
namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentIntegrityColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_luu_duoc_hai_cot_hash(): void
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'NV';
        $u->email = 'nv@demo.test';
        $u->password = bcrypt('x');
        $u->role_id = $role->id;
        $u->save();
        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);
        $p = Product::create([
            'farmer_id' => $u->id, 'category_id' => $cat->id, 'name' => 'Xoài',
            'price' => 30000, 'unit' => 'kg', 'quantity_available' => 10,
            'qr_code' => 'QR123', 'status' => 'active',
        ]);

        $a = AiQualityAssessment::create([
            'product_id' => $p->id, 'image_url' => 'products/x.jpg',
            'ripeness_level' => 'chin', 'quality_score' => 90, 'defect_detected' => false,
            'image_sha256' => str_repeat('a', 64), 'integrity_hash' => str_repeat('b', 64),
            'assessed_at' => now(),
        ]);

        $this->assertSame(str_repeat('a', 64), $a->fresh()->image_sha256);
        $this->assertSame(str_repeat('b', 64), $a->fresh()->integrity_hash);
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `& "E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --filter=AssessmentIntegrityColumnsTest`
Expected: FAIL — SQL "no column named image_sha256" (hoặc mass-assign bỏ qua nên cột null → assertSame fail).

- [ ] **Step 3: Viết migration**

`database/migrations/2026_07_21_000001_add_integrity_to_ai_quality_assessments.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_quality_assessments', function (Blueprint $table) {
            $table->string('image_sha256', 64)->nullable()->after('image_url');
            $table->string('integrity_hash', 64)->nullable()->after('raw_ai_response');
        });
    }

    public function down(): void
    {
        Schema::table('ai_quality_assessments', function (Blueprint $table) {
            $table->dropColumn(['image_sha256', 'integrity_hash']);
        });
    }
};
```

- [ ] **Step 4: Thêm 2 cột vào fillable của model**

Trong `app/Models/AiQualityAssessment.php`, thêm `'image_sha256'` và `'integrity_hash'` vào `$fillable`. Kết quả:
```php
    protected $fillable = [
        'product_id', 'image_url', 'image_sha256', 'ripeness_level', 'quality_score',
        'defect_detected', 'defect_note', 'raw_ai_response', 'integrity_hash', 'assessed_at',
    ];
```
(Giữ nguyên `$casts`, `$timestamps = false`, quan hệ `product()`.)

- [ ] **Step 5: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --filter=AssessmentIntegrityColumnsTest`
Expected: PASS. Rồi full suite: `& "E:\laragon\bin\php\...\php.exe" artisan test` → toàn xanh.

- [ ] **Step 6: Chạy migration trên DB dev**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan migrate` (MySQL Laragon phải chạy).
Expected: migrate 1 file OK.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_21_000001_add_integrity_to_ai_quality_assessments.php app/Models/AiQualityAssessment.php tests/Feature/AssessmentIntegrityColumnsTest.php
git commit -m "Them 2 cot hash (image_sha256, integrity_hash) vao ai_quality_assessments"
```

---

### Task 2: simple-qrcode + TraceabilityService (computeHash + verify)

**Files:**
- Modify: `composer.json` (qua `composer require`)
- Create: `app/Services/TraceabilityService.php`
- Test: `tests/Feature/TraceabilityServiceTest.php`

**Interfaces:**
- Consumes: `AiQualityAssessment` (Task 1), `Product`.
- Produces:
  - `TraceabilityService::computeHash(AiQualityAssessment $a): string` — SHA-256 chuỗi canonical.
  - `TraceabilityService::verify(AiQualityAssessment $a): array` — `['status' => 'verified'|'mismatch'|'unverified', 'hash_ok' => bool, 'image_ok' => bool]`.

- [ ] **Step 1: Cài simple-qrcode**

Run: `& "E:\laragon\bin\php\...\php.exe" C:\path\to\composer.phar require simplesoftwareio/simple-qrcode`
(Hoặc nếu `composer` có trong PATH: `composer require simplesoftwareio/simple-qrcode`. Trên Laragon composer thường ở `E:\laragon\bin\composer\composer.phar` — chạy `& "E:\laragon\bin\php\...\php.exe" "E:\laragon\bin\composer\composer.phar" require simplesoftwareio/simple-qrcode`.)
Expected: package cài vào vendor + `composer.json`. Dùng format SVG (không cần GD/Imagick).

- [ ] **Step 2: Viết test thất bại**

```php
<?php
// tests/Feature/TraceabilityServiceTest.php
namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TraceabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeAssessment(array $override = []): AiQualityAssessment
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'NV'; $u->email = 'nv@demo.test'; $u->password = bcrypt('x'); $u->role_id = $role->id; $u->save();
        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);
        $p = Product::create([
            'farmer_id' => $u->id, 'category_id' => $cat->id, 'name' => 'Xoài',
            'price' => 30000, 'unit' => 'kg', 'quantity_available' => 10, 'qr_code' => 'QRX', 'status' => 'active',
        ]);

        return AiQualityAssessment::create(array_merge([
            'product_id' => $p->id, 'image_url' => 'products/x.jpg', 'ripeness_level' => 'chin',
            'quality_score' => 90, 'defect_detected' => false, 'assessed_at' => now(),
        ], $override));
    }

    public function test_computeHash_deterministic(): void
    {
        $a = $this->makeAssessment(['image_sha256' => str_repeat('a', 64)]);
        $svc = app(TraceabilityService::class);
        $this->assertSame($svc->computeHash($a->fresh()), $svc->computeHash($a->fresh()));
    }

    public function test_verify_unverified_khi_chua_co_hash(): void
    {
        $a = $this->makeAssessment(); // integrity_hash null
        $this->assertSame('unverified', app(TraceabilityService::class)->verify($a)['status']);
    }

    public function test_verify_verified_va_mismatch(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/x.jpg', 'noi-dung-anh-that');
        $svc = app(TraceabilityService::class);

        $sha = hash('sha256', 'noi-dung-anh-that');
        $a = $this->makeAssessment(['image_sha256' => $sha]);
        $a->integrity_hash = $svc->computeHash($a);
        $a->save();

        $this->assertSame('verified', $svc->verify($a->fresh())['status']);

        // Sua diem -> hash canonical lech -> mismatch
        $a->quality_score = 50;
        $a->save();
        $this->assertSame('mismatch', $svc->verify($a->fresh())['status']);
    }
}
```

- [ ] **Step 3: Chạy test — kỳ vọng FAIL**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TraceabilityServiceTest`
Expected: FAIL — class `App\Services\TraceabilityService` not found.

- [ ] **Step 4: Viết service**

`app/Services/TraceabilityService.php`:
```php
<?php

namespace App\Services;

use App\Models\AiQualityAssessment;
use Illuminate\Support\Facades\Storage;

/**
 * Xac minh toan ven cho bang ai_quality_assessments (append-only).
 * KHONG chua recordAssessment/live-scan bridge (ngoai pham vi ban demo).
 */
class TraceabilityService
{
    /** SHA-256 cua chuoi canonical — tinh lai duoc de doi chieu (muc 5 spec). */
    public function computeHash(AiQualityAssessment $a): string
    {
        $p = $a->product()->first();

        $canonical = implode('|', [
            $p?->qr_code,
            (string) $a->product_id,
            (string) $a->quality_score,
            (string) $a->ripeness_level,
            $a->defect_detected ? '1' : '0',
            (string) $a->image_sha256,
            (string) $p?->farmer_id,
            $a->assessed_at?->toIso8601String(),
        ]);

        return hash('sha256', $canonical);
    }

    /**
     * @return array{status: string, hash_ok: bool, image_ok: bool}
     */
    public function verify(AiQualityAssessment $a): array
    {
        if (! $a->integrity_hash) {
            return ['status' => 'unverified', 'hash_ok' => false, 'image_ok' => false];
        }

        $hashOk = hash_equals($a->integrity_hash, $this->computeHash($a));

        $imageOk = false;
        if ($a->image_sha256 && Storage::disk('public')->exists($a->image_url)) {
            $imageOk = hash_equals(
                $a->image_sha256,
                hash('sha256', Storage::disk('public')->get($a->image_url)),
            );
        }

        return [
            'status' => ($hashOk && $imageOk) ? 'verified' : 'mismatch',
            'hash_ok' => $hashOk,
            'image_ok' => $imageOk,
        ];
    }
}
```

> Dùng `Storage::disk('public')->exists()`/`->get()` (không `->path()` + `hash_file`) để test `Storage::fake('public')` chạy được.

- [ ] **Step 5: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TraceabilityServiceTest` → PASS (3 test). Rồi full suite → xanh.

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock app/Services/TraceabilityService.php tests/Feature/TraceabilityServiceTest.php
git commit -m "Them simple-qrcode + TraceabilityService (computeHash + verify)"
```

---

### Task 3: Routes + TraceController (read-only)

**Files:**
- Create: `app/Http/Controllers/TraceController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TracePageTest.php`

**Interfaces:**
- Consumes: `Product` (scoped binding qr_code), `TraceabilityService`.
- Produces: route `trace.show` (GET `/trace/{product:qr_code}`), `trace.qr` (GET `/trace/{product:qr_code}/qr`).

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/TracePageTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TracePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_endpoint_tra_svg(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/trace/'.$p->qr_code.'/qr');

        $res->assertOk();
        $this->assertStringContainsString('image/svg+xml', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $res->getContent());
    }

    public function test_qr_code_khong_ton_tai_404(): void
    {
        $this->seed();
        $this->get('/trace/KHONG-CO-MA-NAY')->assertNotFound();
    }

    public function test_product_chua_co_assessment_404(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->assessments()->delete();

        $this->get('/trace/'.$p->qr_code)->assertNotFound();
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TracePageTest`
Expected: FAIL 404 (route chưa có).

- [ ] **Step 3: Viết `TraceController`**

`app/Http/Controllers/TraceController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\TraceabilityService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TraceController extends Controller
{
    public function __construct(private readonly TraceabilityService $svc)
    {
    }

    public function show(Product $product)
    {
        $product->load(['farmer.farmerProfile', 'category', 'primaryImage', 'traceLogs']);

        $assessment = $product->assessments()->latest('assessed_at')->first();
        abort_if(! $assessment, 404, 'Sản phẩm chưa được AI đánh giá');

        $verification = $this->svc->verify($assessment);

        return view('trace.show', compact('product', 'assessment', 'verification'));
    }

    public function qr(Product $product)
    {
        $svg = QrCode::format('svg')->size(300)->margin(1)
            ->generate(route('trace.show', $product->qr_code));

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
```

- [ ] **Step 4: Thêm routes**

Trong `routes/web.php` (thêm `use App\Http\Controllers\TraceController;` ở đầu; đặt các route sau route marketplace):
```php
Route::get('/trace/{product:qr_code}', [TraceController::class, 'show'])
    ->name('trace.show')->middleware('throttle:60,1');

Route::get('/trace/{product:qr_code}/qr', [TraceController::class, 'qr'])
    ->name('trace.qr')->middleware('throttle:60,1');
```
KHÔNG có route POST/PUT/DELETE cho assessment (append-only ở tầng HTTP).

- [ ] **Step 5: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TracePageTest` → PASS (3 test).
Cả 3 test này KHÔNG render view `trace.show`: `test_qr_endpoint_tra_svg` trả SVG (không dùng view), `test_qr_code_khong_ton_tai_404` fail ở route-model binding, `test_product_chua_co_assessment_404` abort 404 TRƯỚC khi render. (Nội dung trang show test ở Task 4.) Rồi full suite → xanh.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/TraceController.php routes/web.php tests/Feature/TracePageTest.php
git commit -m "Them route /trace/{qr_code} + TraceController (public read-only)"
```

---

### Task 4: View `trace/show.blade.php` (mobile-first, khớp demo)

**Files:**
- Create: `resources/views/trace/show.blade.php`
- Modify: `tests/Feature/TracePageTest.php` (thêm 1 test nội dung trang show)

**Interfaces:**
- Consumes: `$product`, `$assessment`, `$verification` từ `TraceController@show`.

- [ ] **Step 1: Thêm test nội dung trang show (thất bại vì chưa có view)**

Thêm method này vào `tests/Feature/TracePageTest.php`:
```php
    public function test_trace_page_public_hien_diem_va_nha_vuon(): void
    {
        $this->seed();
        $p = \App\Models\Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/trace/'.$p->qr_code);

        $res->assertOk();
        $res->assertViewIs('trace.show');
        $res->assertSee($p->name);
        $res->assertSee((string) (int) $p->latestAssessment->quality_score);
    }
```
Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=test_trace_page_public_hien_diem_va_nha_vuon`
Expected: FAIL — `View [trace.show] not found`.

- [ ] **Step 2: Viết view**

Tạo `resources/views/trace/show.blade.php`. **Mobile-first bắt buộc** (giám khảo cầm điện thoại). **Port khung từ `Test-Giao-Dien\agrisenai-truy-xuat.html`**, dùng `layouts.app` giống các trang khác:
- `@extends('layouts.app', ['page' => 'truyxuat'])`, `@section('content')`, CSS-riêng `@push('head')<style>` scoped, chrome từ layout.
- Thứ tự dọc (thuyết phục giảm dần):
  1. **Header:** `$product->name` + `$product->category->name` + badge điểm lớn `{{ (int) $assessment->quality_score }}/100`. Màu badge: ≥90 xanh đậm, 75–89 xanh dương, <75 cam.
  2. **Dải xác minh** (theo `$verification['status']`):
     - `verified` → dải xanh "✓ Đã xác minh — dữ liệu chưa bị chỉnh sửa"
     - `mismatch` → dải đỏ "⚠ Dữ liệu không khớp mã băm gốc"
     - `unverified` → dải xám "Bản ghi cũ, chưa có mã băm"
  3. **Ảnh gốc AI đã quét:** `<img src="{{ Storage::url($assessment->image_url) }}" ...>` (fallback nếu thiếu) — ghi rõ "ảnh AI chấm, không phải ảnh marketing".
  4. **Kết quả AI:** điểm `quality_score`/100; độ chín nhãn VN (xanh/vừa chín/chín/quá chín từ `ripeness_level`); nếu `$assessment->defect_detected` → hộp cảnh báo vàng + `defect_note`; `assessed_at->format('d/m/Y H:i')`; "Đánh giá tự động bởi Gemini Vision".
  5. **Nhà vườn:** `farmer.farmerProfile.farm_name`, `region`, OCOP `str_repeat('⭐', $rating)`, `description`. (SĐT nếu có → ẩn giữa; hiện schema không có cột SĐT ở farmer_profiles nên bỏ.)
  6. **Nhật ký truy xuất:** timeline dọc `$product->traceLogs` — stage→nhãn VN (thu_hoach=Thu hoạch, dong_goi=Đóng gói, van_chuyen=Vận chuyển, danh_gia_ai=Đánh giá AI, dang_ban=Đang bán) + `description`, `location`, `happened_at->format('d/m/Y')`.
  7. **Chân trang — bằng chứng kỹ thuật:** `$product->qr_code`; `integrity_hash` rút gọn `substr($assessment->integrity_hash, 0, 16).'…'` (có nút/details xem đầy đủ); dòng: "Mã băm SHA-256 — bất kỳ ai cũng kiểm chứng bản ghi này không bị sửa sau khi tạo."

- [ ] **Step 3: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TracePageTest` → 4/4 PASS. Rồi full suite → xanh.

- [ ] **Step 4: Commit**

```bash
git add resources/views/trace/show.blade.php tests/Feature/TracePageTest.php
git commit -m "Them view trace/show mobile-first (nhat ky chat luong + dai xac minh)"
```

---

### Task 5: Nâng cấp seeder để demo verify được (ảnh thật + hash)

**Files:**
- Modify: `database/seeders/MarketplaceDemoSeeder.php`
- Test: `tests/Feature/TraceSeededVerifyTest.php`

**Interfaces:**
- Consumes: `TraceabilityService::computeHash` (Task 2), ảnh mẫu `storage/app/reference/xoai/03_hang_1.jpg`.
- Produces: mỗi assessment seed có ảnh thật trong `public` disk + `image_sha256` + `integrity_hash` → `verify()` trả `verified`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/TraceSeededVerifyTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceSeededVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_san_pham_seed_co_the_verified(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();
        $a = $p->latestAssessment;

        $this->assertNotNull($a->integrity_hash, 'assessment seed phai co integrity_hash');
        $this->assertNotNull($a->image_sha256, 'assessment seed phai co image_sha256');

        $status = app(TraceabilityService::class)->verify($a)['status'];
        $this->assertSame('verified', $status);
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TraceSeededVerifyTest`
Expected: FAIL — `integrity_hash` null (seeder chưa set).

- [ ] **Step 3: Sửa seeder — thêm ảnh thật + hash cho mỗi assessment**

Trong `database/seeders/MarketplaceDemoSeeder.php`:

(a) Thêm import ở đầu:
```php
use App\Services\TraceabilityService;
use Illuminate\Support\Facades\Storage;
```

(b) Trong `run()`, trước vòng lặp product, chuẩn bị ảnh mẫu nguồn (copy 1 lần vào biến bytes):
```php
        // Anh mau that de assessment co the verify duoc (demo integrity).
        $anhMauPath = storage_path('app/reference/xoai/03_hang_1.jpg');
        $anhMauBytes = is_file($anhMauPath) ? file_get_contents($anhMauPath) : 'anh-demo';
        $svc = app(TraceabilityService::class);
```

(c) Thay khối tạo `AiQualityAssessment` hiện tại bằng: tạo assessment (chưa hash) → ghi ảnh thật vào public disk → set `image_sha256` → tính `integrity_hash` → save. Cụ thể:
```php
            $relImg = 'products/'.$p->id.'.jpg';
            Storage::disk('public')->put($relImg, $anhMauBytes);

            $a = new \App\Models\AiQualityAssessment();
            $a->product_id = $p->id;
            $a->image_url = $relImg;                       // duong dan tren public disk
            $a->image_sha256 = hash('sha256', $anhMauBytes);
            $a->ripeness_level = $chin;
            $a->quality_score = $diem;
            $a->defect_detected = $diem < 80;
            $a->defect_note = $diem < 80 ? 'Vài vết xước nhẹ ở vỏ.' : null;
            $a->raw_ai_response = ['grade' => $diem >= 90 ? 'dac_biet' : 'hang_1', 'overall_score' => $diem / 10];
            $a->assessed_at = now()->subDays(rand(0, 10));
            $a->save();                                     // save lan 1 de co assessed_at

            $a->integrity_hash = $svc->computeHash($a);
            $a->save();                                     // save lan 2 voi hash
```
(Xoá khối `AiQualityAssessment::create([...])` cũ; các phần khác của seeder — user/farmer/category/product/ProductImage/traceLogs/reviews — GIỮ NGUYÊN.)

> Lưu ý: `Storage::disk('public')` trong test `RefreshDatabase` (không fake) sẽ ghi thật vào `storage/app/public`. Test seed sẽ tạo file thật — chấp nhận được (nhỏ). Nếu muốn sạch, test có thể `Storage::fake('public')` TRƯỚC `$this->seed()`; nhưng seeder dùng `Storage::disk('public')` nên fake sẽ chặn ghi ra đĩa thật. Trong `TraceSeededVerifyTest`, KHÔNG cần fake — verify đọc lại đúng file vừa ghi.

- [ ] **Step 4: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=TraceSeededVerifyTest` → PASS. Rồi full suite → xanh.

- [ ] **Step 5: `storage:link` + seed DB dev + kiểm demo integrity**

Run:
```
& "E:\laragon\bin\php\...\php.exe" artisan storage:link
& "E:\laragon\bin\php\...\php.exe" artisan migrate:fresh --seed
```
Expected: `public/storage` được tạo; seed 10 product, mỗi cái có ảnh + hash. Mở `http://agrisenai.test/trace/<qr_code_bat_ky>` (lấy qr_code: `tinker --execute="echo App\Models\Product::first()->qr_code;"`) → dải "✓ Đã xác minh". Sửa DB: `UPDATE ai_quality_assessments SET quality_score=1 WHERE id=1;` rồi reload → dải chuyển "⚠ Không khớp". (Đây là màn demo mạnh nhất.)

- [ ] **Step 6: Commit**

```bash
git add database/seeders/MarketplaceDemoSeeder.php tests/Feature/TraceSeededVerifyTest.php
git commit -m "Seeder: gan anh that + integrity hash cho assessment (demo verify duoc)"
```

---

### Task 6: Cắm khối QR vào trang chi tiết sản phẩm

**Files:**
- Modify: `resources/views/marketplace/show.blade.php`
- Test: `tests/Feature/ProductDetailQrTest.php`

**Interfaces:**
- Consumes: route `trace.qr`, `trace.show` (Task 3), `$product->qr_code`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/ProductDetailQrTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_chi_tiet_co_khoi_qr_tro_trace(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/thi-truong/'.$p->id);

        $res->assertOk();
        $res->assertSee(route('trace.qr', $p->qr_code), false);   // anh QR
        $res->assertSee(route('trace.show', $p->qr_code), false); // link nhat ky
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=ProductDetailQrTest`
Expected: FAIL — chưa có QR trong view.

- [ ] **Step 3: Thêm khối QR vào `marketplace/show.blade.php`**

Tại chỗ đã chừa sẵn ("Truy xuất nguồn gốc" / cuối cột phải), thêm khối:
```blade
<div class="qr-block">
    <div class="qr-title">Truy xuất nguồn gốc</div>
    <img class="qr-img" src="{{ route('trace.qr', $product->qr_code) }}" alt="Mã QR truy xuất {{ $product->name }}" width="140" height="140">
    <a class="qr-link" href="{{ route('trace.show', $product->qr_code) }}">Xem nhật ký chất lượng →</a>
    <p class="qr-hint">Quét mã để xem ảnh AI đã chấm, điểm chất lượng và xác minh dữ liệu chưa bị sửa.</p>
</div>
```
Thêm CSS tối thiểu cho `.qr-block/.qr-img/.qr-link/.qr-hint` vào block `@push('head')` sẵn có của view (canh giữa, ảnh QR nền trắng bo góc). Chrome/không đụng phần khác.

- [ ] **Step 4: Chạy test — kỳ vọng PASS**

Run: `& "E:\laragon\bin\php\...\php.exe" artisan test --filter=ProductDetailQrTest` → PASS. Rồi **full suite** → toàn xanh.

- [ ] **Step 5: Kiểm trình duyệt end-to-end**

`/thi-truong` → chi tiết → thấy QR → bấm "Xem nhật ký" → `/trace/{qr_code}` hiện dải xác minh + ảnh + timeline.

- [ ] **Step 6: Commit**

```bash
git add resources/views/marketplace/show.blade.php tests/Feature/ProductDetailQrTest.php
git commit -m "Cam khoi QR vao trang chi tiet san pham (tro /trace)"
```

---

## Nghiệm thu tổng (Definition of Done)

- [ ] Migrate thêm 2 cột hash, không đụng cột khác.
- [ ] `/trace/{qr_code}` mở được KHÔNG cần đăng nhập; hiện ảnh AI, điểm + độ chín + cảnh báo lỗi, nhà vườn + OCOP, timeline traceability, dải xác minh.
- [ ] Seeder ra product có assessment **verified** (ảnh thật + hash).
- [ ] **Demo test:** sửa `quality_score` trong DB → reload → dải chuyển "Không khớp".
- [ ] `qr_code` sai/không tồn tại → 404 sạch (không lộ lỗi SQL).
- [ ] QR trên trang chi tiết quét được, mở đúng `/trace/{qr_code}`.
- [ ] QR endpoint trả SVG; QR chứa URL tuyệt đối (`route()`).
- [ ] Append-only: không route POST/PUT/DELETE cho assessment.
- [ ] `php artisan storage:link` đã chạy (ảnh AI hiện).
- [ ] `php artisan test` toàn xanh; mobile-first đọc tốt trên điện thoại.

## Lưu ý demo thực địa (spec mục 14)
- `APP_URL` phải là **public URL** TRƯỚC KHI sinh QR (localhost thì điện thoại giám khảo không mở được). Dùng ngrok/Cloudflare Tunnel/host thật → set `APP_URL` trong `.env` → `php artisan config:clear`.
- Wifi hội trường có thể chặn: chuẩn bị 4G / screenshot dự phòng.

## Việc để sau (ngoài phạm vi plan này)
- **Cầu nối live-scan:** cho `QualityController@assess` tạo/gắn `Product` + gọi `recordAssessment` để nông dân chấm live sinh QR (hiện trang AI không tạo Product). Cần quyết luồng "product ở đâu ra khi chấm live".
- Ảnh sản phẩm thật (khác ảnh mẫu dùng chung) cho từng product.
