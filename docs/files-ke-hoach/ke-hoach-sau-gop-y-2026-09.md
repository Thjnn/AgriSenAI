# Kế hoạch triển khai AgriSenAI — sau góp ý 28/09/2026

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Biến AgriSenAI từ "giao diện demo + thử API" thành một MVP chạy được trọn luồng
**chụp xoài → AI chấm hạng → tạo lô → in tem QR → người mua B2B quét, tin, đặt hàng**,
đồng thời trả lời từng điểm yếu mà giảng viên đã chỉ ra.

**Architecture:** Laravel 13 monolith, giữ nguyên các khối đã có (QualityAssessor, TraceabilityService,
trang `/trace`). Thêm một "cầu nối" `LotPublisher` để kết quả AI đi thẳng vào sản phẩm + QR, rồi mở rộng
trang hồ sơ QR (đa ngôn ngữ, PUC, dự báo bảo quản, RFQ). Logic ở client (kiểm tra ảnh, hàng đợi offline)
viết thành ES module thuần để test được bằng `node --test`, không thêm thư viện.

**Tech Stack:** PHP 8.3, Laravel 13, MySQL (test: sqlite :memory:), Gemini Vision, Blade + JS thuần,
PWA (service worker sẵn có), Node 24 (chỉ để chạy test JS).

**Spec:** góp ý giảng viên — `docs/Giao-Vien/Cô-HằngGóp Ý- 28-09-26.docx`. Nền tảng AI:
`docs/files-ke-hoach/quy-trinh-xay-module-ai.md`, `docs/files-ke-hoach/danh-gia-dataset.md`.

---

## 0. Cách dùng file này

- Làm **theo thứ tự Giai đoạn 1 → 2 → 3**. Trong một giai đoạn, task có đánh dấu 👤 là việc **của nhóm**
  (chụp ảnh, ra vựa, soạn thuyết trình) — Claude không làm thay được, nhưng các task code không bị chặn
  bởi chúng trừ khi ghi rõ.
- Mỗi task kết thúc bằng: test xanh → commit. Tick `- [x]` ngay khi xong để lần sau mở ra biết đang ở đâu.
- Nói với Claude: *"làm Task 1.2 trong docs/files-ke-hoach/ke-hoach-sau-gop-y-2026-09.md"* là đủ ngữ cảnh.

### Lệnh hay dùng (Windows / Laragon)

```bash
# php KHONG co trong PATH
P=/e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe
$P artisan test                         # toan bo test PHP
$P artisan test --filter=TenTest        # 1 file test
node --test tests/js/                   # test JS (tu Giai doan 2)
$P artisan reference:build xoai         # dung lai anh mau
$P artisan quality:eval xoai            # do do chinh xac AI tren tap test (goi Gemini that)
$P artisan storage:link                 # 1 lan, de anh tren disk public hien duoc
```

---

## Global Constraints

- **Chỉ làm xoài.** Không thêm loại nông sản khác (chốt ngày 30/09/2026).
- AI **chỉ chấm cái nhìn thấy**: màu, bề mặt, hình dạng, độ đồng đều, độ chín. Khối lượng, Brix do người dùng nhập — không bao giờ để AI đoán.
- Mọi ảnh (mẫu, test, ảnh người dùng) đi qua **cùng một** `App\Services\Quality\ImageNormalizer` (768×768 JPEG q85).
- **Điểm AI không bao giờ lấy từ client.** Client chỉ gửi mã phiên; server tự đọc kết quả đã lưu.
- Bảng `ai_quality_assessments` là **append-only**: không `update` điểm, muốn chấm lại thì tạo bản ghi mới.
- Không đổi công thức `TraceabilityService::computeHash()` — sẽ làm hỏng hash của dữ liệu seed đã commit. Mọi xác thực bổ sung đi qua Audit Log (Task 3.2).
- Chữ hiển thị: tiếng Việt có dấu. Comment trong code: tiếng Việt **không dấu** (theo style repo).
- Tên route/URL tiếng Việt không dấu, gạch nối (`/danh-gia/dang-ban`), theo style `routes/web.php`.
- Không thêm package npm/composer nếu không ghi rõ trong task.
- Chưa có đăng nhập thật: mọi lô tạo từ web gán cho **nông dân demo** `config('agrisenai.nong_dan_demo')`.

## Review Focus

Những tình huống dễ làm hỏng demo mà test thông thường không bắt — mỗi dòng đã có test ở task tương ứng:

1. **Bấm "Đăng bán" hai lần** (mạng chậm, nông dân sốt ruột) → chỉ được tạo **một** lô. *(Task 1.2, test `test_bam_hai_lan_chi_tao_mot_lo`)*
2. **Client sửa điểm/hạng trong request** → server phải bỏ qua, dùng kết quả đã lưu. *(Task 1.2, test `test_bo_qua_diem_client_gui_len`)*
3. **Phiên chấm hết hạn / mã phiên bịa** → báo "chấm lại", không lỗi 500. *(Task 1.2)*
4. **Ảnh không dùng được** (mờ, không phải xoài) → không sinh phiên, không cho đăng bán. *(Task 1.1, test `test_anh_khong_dung_duoc_khong_sinh_phien`)*
5. **Trang QR với `?lang=` lạ** (`?lang=fr`, `?lang=../`) → rơi về tiếng Việt, không lỗi. *(Task 2.3)*

---

## Tổng quan & đối chiếu góp ý

| Góp ý của giảng viên | Task trả lời |
|---|---|
| "Thiếu MVP, chưa có bằng chứng AI chạy ngoài thực địa" | 1.1–1.4 (luồng thật), **1.7 thử nghiệm tại vựa** |
| "Thiếu dữ liệu huấn luyện, cần chuẩn hoá đầu vào (hộp chụp)" | 1.5 (ảnh xoài hỏng), 1.6 (quy trình chụp chuẩn + khung ngắm), 2.1 (kiểm tra ảnh trên máy) |
| "Tập trung 1–2 chỉ số: phát hiện xoài thâm…" | 1.5 — mục tiêu đo: AI bắt được quả thâm/dập/thối |
| Chatbot tư vấn giá/thời tiết (GĐ1 trong lộ trình của cô) | 2.5 |
| Offline PWA & sync | 3.3 |
| Kiểm tra chất lượng ảnh trên thiết bị | 2.1 |
| Ký số & Audit log | 3.2 (đã có nền: hash SHA-256 + dải đỏ) |
| Hồ sơ số đa ngôn ngữ | 2.3 |
| Mã số vùng trồng (PUC), VietGAP/GlobalGAP | 2.4 |
| RFQ / gửi mẫu / giữ lô — "làm sao không nhảy sang Zalo" | 3.1 |
| Dự báo bảo quản & độ chín | 2.2 |
| Kể chuyện nỗi đau HTX, "bán cao hơn 10–15%" | 👤 Mục 5 |

> Thứ tự khác lộ trình của cô ở một chỗ: **kiểm tra ảnh trên máy (2.1) được kéo lên GĐ2** vì làm bằng
> JS thuần chỉ mất ~1 buổi (không cần TensorFlow.js) mà trả lời thẳng câu hỏi "ảnh chụp khác nhau".
> **Offline sync (3.3) đẩy xuống GĐ3** vì tốn công nhất.

---

## Giai đoạn 0 — Dọn nền (≤ 30 phút)

### Task 0.1: Chốt phần "chỉ làm xoài" đang dở

- [ ] **Step 1:** Chạy `$P artisan test` → mong đợi `79 passed`.
- [ ] **Step 2:** Commit các file đã sửa (add đích danh, KHÔNG add các file `.docx` trong `docs/`):

```bash
git add app/Services/Quality/FewShotPromptBuilder.php config/agrisenai.php \
  database/seeders/MarketplaceDemoSeeder.php resources/views/home.blade.php \
  storage/app/reference/xoai/labels.json tests/Feature/MarketplaceFilterTest.php \
  database/AgriSenAI.sql database/AgriSenAI_seed.sql \
  docs/files-ke-hoach/ke-hoach-sau-gop-y-2026-09.md
git commit -m "Thu hep pham vi ve xoai: prompt, nhan mau, seeder demo + ke hoach trien khai"
git push
```

- [x] **Step 3 (xong 30/09):** `database/AgriSenAI_seed.sql` đã đổi sang toàn xoài (5 vườn/HTX, 3 danh mục
  `xoai` / `xoai-xanh` / `xoai-che-bien`, 12 lô). `integrity_hash` tự tính bằng câu `UPDATE … SHA2` cuối file;
  đã import thử vào DB tạm: 8/8 assessment `verified`, tổng tiền 5 đơn khớp `order_items`.

---

## Giai đoạn 1 — MVP chạy trọn luồng (mục tiêu: vòng bán kết)

### Task 1.1: Lưu kết quả chấm thành "phiên" phía server

**Vì sao:** Hiện `POST /danh-gia` chấm xong là xoá ảnh, không lưu gì. Muốn "đăng bán lô vừa chấm" thì server
phải giữ lại ảnh chuẩn hoá + kết quả AI, và chỉ đưa cho client một **mã phiên** — để client không sửa được điểm.

**Files:**
- Modify: `app/Http/Controllers/QualityController.php` (hàm `assess`, khoảng dòng 66–113)
- Create: `tests/Feature/Concerns/GiaLapAi.php`
- Create: `tests/Feature/PhienChamTest.php`

**Interfaces:**
- Produces:
  - JSON `POST /danh-gia` có thêm khoá `phien` (ULID 26 ký tự) khi `usable = true`.
  - Cache key `phien_cham:{phien}` → `array{ket_qua: array, loai: string, muc_dich_ban: string, anh: string}` (TTL 2 giờ). `anh` là đường dẫn trên disk `local`: `pending/{phien}.jpg`.
  - Request nhận thêm tuỳ chọn `muc_dich_ban` ∈ `an_chin|an_xanh` (mặc định `an_chin`).
  - Trait `Tests\Feature\Concerns\GiaLapAi::giaLapAi(array $ghiDe = []): array` — mock `QualityAssessor`, trả về kết quả mẫu.

- [ ] **Step 1: Viết trait giả lập AI** (dùng lại ở Task 1.2, 1.3, 2.2)

```php
<?php
// tests/Feature/Concerns/GiaLapAi.php
namespace Tests\Feature\Concerns;

use App\Services\Quality\QualityAssessor;

trait GiaLapAi
{
    /** Ket qua mau dung shape QualityAssessor::assess() tra ve. */
    protected function ketQuaMau(array $ghiDe = []): array
    {
        return array_replace([
            'usable' => true,
            'detected_product' => 'Xoài',
            'grade' => 'dac_biet',
            'overall_score' => 9.2,
            'confidence' => 0.9,
            'scores' => ['color' => 9.0, 'surface' => 9.2, 'shape' => 9.0, 'uniformity' => 9.1, 'ripeness' => 9.5],
            'defects' => [],
            'reasoning' => 'Vỏ vàng đều, không khuyết tật đáng kể.',
            'advice' => 'Nên bán ngay.',
            'rule_engine' => ['da_ha_hang' => false],
        ], $ghiDe);
    }

    /** Thay QualityAssessor that (goi Gemini) bang ban gia. */
    protected function giaLapAi(array $ghiDe = []): array
    {
        $kq = $this->ketQuaMau($ghiDe);
        $this->mock(QualityAssessor::class, fn ($m) => $m->shouldReceive('assess')->andReturn($kq));

        return $kq;
    }
}
```

- [ ] **Step 2: Viết test (sẽ fail)**

```php
<?php
// tests/Feature/PhienChamTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\GiaLapAi;
use Tests\TestCase;

class PhienChamTest extends TestCase
{
    use GiaLapAi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function cham(array $them = [])
    {
        return $this->post('/danh-gia', array_merge([
            'anh' => UploadedFile::fake()->image('xoai.jpg', 900, 900),
            'loai' => 'xoai',
        ], $them));
    }

    public function test_cham_xong_tra_ve_ma_phien_va_luu_anh(): void
    {
        $this->giaLapAi();

        $res = $this->cham()->assertOk()->assertJsonStructure(['phien']);
        $phien = $res->json('phien');

        $this->assertSame(26, strlen($phien));
        Storage::disk('local')->assertExists("pending/{$phien}.jpg");
        $luu = Cache::get("phien_cham:{$phien}");
        $this->assertSame('dac_biet', $luu['ket_qua']['grade']);
        $this->assertSame('an_chin', $luu['muc_dich_ban']);
    }

    public function test_nhan_muc_dich_an_xanh(): void
    {
        $this->giaLapAi();
        $phien = $this->cham(['muc_dich_ban' => 'an_xanh'])->json('phien');

        $this->assertSame('an_xanh', Cache::get("phien_cham:{$phien}")['muc_dich_ban']);
    }

    public function test_muc_dich_la_bi_tu_choi(): void
    {
        $this->giaLapAi();
        $this->cham(['muc_dich_ban' => 'an_song'])->assertStatus(422);
    }

    public function test_anh_khong_dung_duoc_khong_sinh_phien(): void
    {
        $this->giaLapAi(['usable' => false, 'reasoning' => 'Ảnh quá mờ.']);

        $res = $this->cham()->assertOk();

        $this->assertFalse($res->json('usable'));
        $this->assertNull($res->json('phien'));
        $this->assertSame([], Storage::disk('local')->allFiles('pending'));
    }
}
```

- [ ] **Step 3: Chạy — mong đợi FAIL**

`$P artisan test --filter=PhienChamTest` → FAIL (`phien` không có trong JSON).

- [ ] **Step 4: Sửa `QualityController::assess`**

Thêm `use`:

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
```

Trong mảng rule của `Validator::make` thêm:

```php
            'muc_dich_ban' => ['nullable', Rule::in(array_keys(config('agrisenai.muc_dich_ban')))],
```

và message: `'muc_dich_ban.in' => 'Mục đích bán không hợp lệ.',`

Thay khối từ `$data = $validator->validated();` tới hết hàm bằng:

```php
        $data = $validator->validated();
        $mucDich = $data['muc_dich_ban'] ?? 'an_chin';

        // Chuan hoa TRUOC de khoi phai chuan hoa lai ben trong QualityAssessor.
        $chuanHoa = tempnam(sys_get_temp_dir(), 'agrisen_').'.jpg';

        try {
            $this->normalizer->normalize($request->file('anh')->getRealPath(), $chuanHoa);

            $ketQua = $this->assessor->assess(
                imagePath: $chuanHoa,
                loai: $data['loai'],
                mucDichBan: $mucDich,
                daChuanHoa: true,
            );

            $payload = $this->toPayload($ketQua);

            // Chi anh dung duoc moi thanh "phien" — de buoc dang ban doc lai tu server,
            // khong tin diem client gui len.
            if ($payload['usable']) {
                $phien = (string) Str::ulid();
                $anh = "pending/{$phien}.jpg";
                Storage::disk('local')->put($anh, file_get_contents($chuanHoa));
                Cache::put("phien_cham:{$phien}", [
                    'ket_qua' => $ketQua,
                    'loai' => $data['loai'],
                    'muc_dich_ban' => $mucDich,
                    'anh' => $anh,
                ], now()->addHours(2));
                $payload['phien'] = $phien;
            }
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Không chấm được ảnh: '.$e->getMessage(),
            ], 422);
        } finally {
            if (is_file($chuanHoa)) {
                unlink($chuanHoa);
            }
        }

        return response()->json($payload);
```

Xoá dòng comment cũ "Da bo `muc_dich_ban`…" trong docblock của `assess`.

- [ ] **Step 5: Chạy — mong đợi PASS**

`$P artisan test --filter=PhienChamTest` → 4 passed. Rồi `$P artisan test` → toàn bộ xanh.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/QualityController.php tests/Feature/Concerns/GiaLapAi.php tests/Feature/PhienChamTest.php
git commit -m "AI danh gia: luu ket qua cham thanh phien phia server (chuan bi dang ban lo)"
```

---

### Task 1.2: `LotPublisher` — biến phiên chấm thành lô hàng có QR

**Files:**
- Create: `app/Services/LotPublisher.php`
- Create: `app/Http/Controllers/LotController.php`
- Modify: `routes/web.php` (thêm 1 route sau các route `/danh-gia`)
- Modify: `config/agrisenai.php` (thêm khoá `nong_dan_demo`)
- Test: `tests/Feature/DangBanLoTest.php`

**Interfaces:**
- Consumes: cache `phien_cham:{phien}` (Task 1.1), `TraceabilityService::computeHash()`.
- Produces:
  - `LotPublisher::publish(string $phien, array $thongTin): Product` — ném `DomainException` nếu phiên không tồn tại/đã dùng.
  - `$thongTin` keys: `ten` (string), `gia` (float), `so_luong` (float), `don_vi` (`kg|thung`), `giong` (?string), `khoi_luong` (?int, g/trái), `brix` (?float), `ma_vung_trong` (?string — dùng từ Task 2.4), `dieu_kien_kho` (?`thuong|lanh` — dùng từ Task 2.2).
  - Route `POST /danh-gia/dang-ban` name `quality.publish` → 201 JSON `{product_id, qr_code, trace_url, qr_url, tem_url, product_url}`; 410 khi phiên hết hạn; 422 khi form sai.
  - Assessment tạo ra: `quality_score = overall_score × 10`, `ripeness_level = an_xanh ? 'xanh' : 'chin'`, `raw_ai_response = ket_qua + ['so_lieu_nong_dan' => …, 'muc_dich_ban' => …]`.
  - Trace log mới với `stage = 'danh_gia_ai'` và `'dang_ban'`.

- [ ] **Step 1: Thêm config** — trong `config/agrisenai.php`, ngay trước khối `'image' => [`:

```php
    /*
    |----------------------------------------------------------------------
    | Nong dan demo — chu so huu cac lo tao tu web khi CHUA co dang nhap
    |----------------------------------------------------------------------
    | Email phai ton tai (MarketplaceDemoSeeder tao farmer0@demo.test).
    | Khi co dang nhap that: thay bang auth()->id() trong LotController.
    */
    'nong_dan_demo' => env('AGRISEN_NONG_DAN_DEMO', 'farmer0@demo.test'),
```

- [ ] **Step 2: Viết test (sẽ fail)**

```php
<?php
// tests/Feature/DangBanLoTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\GiaLapAi;
use Tests\TestCase;

class DangBanLoTest extends TestCase
{
    use GiaLapAi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();                 // can nong dan demo farmer0@demo.test
        Storage::fake('local');
        Storage::fake('public');
    }

    private function chamLay(array $ghiDe = []): string
    {
        $this->giaLapAi($ghiDe);

        return $this->post('/danh-gia', [
            'anh' => UploadedFile::fake()->image('xoai.jpg', 900, 900),
            'loai' => 'xoai',
        ])->json('phien');
    }

    private function dangBan(string $phien, array $them = [])
    {
        return $this->postJson('/danh-gia/dang-ban', array_merge([
            'phien' => $phien, 'ten' => 'Xoài Cát Hòa Lộc lô 07',
            'gia' => 42000, 'so_luong' => 120, 'don_vi' => 'kg',
            'giong' => 'Cát Hòa Lộc', 'khoi_luong' => 420, 'brix' => 17.5,
        ], $them));
    }

    public function test_dang_ban_tao_lo_co_qr_va_hash_hop_le(): void
    {
        $res = $this->dangBan($this->chamLay())->assertCreated();

        $p = Product::with('latestAssessment', 'traceLogs')->findOrFail($res->json('product_id'));
        $this->assertSame('active', $p->status);
        $this->assertNotNull($p->qr_code);
        $this->assertTrue(Product::certified()->whereKey($p->id)->exists());
        $this->assertEquals(92, (float) $p->latestAssessment->quality_score);
        $this->assertSame('chin', $p->latestAssessment->ripeness_level);
        $this->assertSame(420, $p->latestAssessment->raw_ai_response['so_lieu_nong_dan']['khoi_luong']);
        $this->assertContains('danh_gia_ai', $p->traceLogs->pluck('stage')->all());

        $kt = app(TraceabilityService::class)->verify($p->latestAssessment);
        $this->assertSame('verified', $kt['status']);

        $this->get($res->json('trace_url'))->assertOk()->assertSee('Xoài Cát Hòa Lộc lô 07');
    }

    public function test_bam_hai_lan_chi_tao_mot_lo(): void
    {
        $phien = $this->chamLay();
        $truoc = Product::count();

        $this->dangBan($phien)->assertCreated();
        $this->dangBan($phien)->assertStatus(410);

        $this->assertSame($truoc + 1, Product::count());
    }

    public function test_bo_qua_diem_client_gui_len(): void
    {
        $res = $this->dangBan($this->chamLay(['overall_score' => 5.1, 'grade' => 'hang_2']), [
            'overall_score' => 10, 'grade' => 'dac_biet', 'quality_score' => 100,
        ])->assertCreated();

        $p = Product::with('latestAssessment')->find($res->json('product_id'));
        $this->assertEquals(51, (float) $p->latestAssessment->quality_score);
    }

    public function test_phien_bia_tra_410(): void
    {
        $this->dangBan('01J0000000000000000000FAKE')
            ->assertStatus(410)
            ->assertJsonFragment(['message' => 'Phiên chấm đã hết hạn hoặc đã được đăng bán. Vui lòng chấm lại.']);
    }

    public function test_gia_am_bi_tu_choi(): void
    {
        $this->dangBan($this->chamLay(), ['gia' => -5])->assertStatus(422)->assertJsonValidationErrors('gia');
    }

    public function test_xoai_an_xanh_vao_danh_muc_xoai_xanh(): void
    {
        $this->giaLapAi();
        $phien = $this->post('/danh-gia', [
            'anh' => UploadedFile::fake()->image('xoai.jpg', 900, 900),
            'loai' => 'xoai', 'muc_dich_ban' => 'an_xanh',
        ])->json('phien');

        $p = Product::with('category', 'latestAssessment')->find($this->dangBan($phien)->json('product_id'));
        $this->assertSame('xoai-xanh', $p->category->slug);
        $this->assertSame('xanh', $p->latestAssessment->ripeness_level);
    }
}
```

- [ ] **Step 3: Chạy — mong đợi FAIL** (`404` vì chưa có route).

- [ ] **Step 4: Viết `LotPublisher`**

```php
<?php

namespace App\Services;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TraceabilityLog;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Cau noi AI -> san: bien 1 phien cham (Task 1.1) thanh 1 lo hang co QR.
 * Diem/hang LUON doc tu cache phia server, khong bao gio tu request.
 */
class LotPublisher
{
    public function __construct(private readonly TraceabilityService $trace)
    {
    }

    public function publish(string $phien, array $thongTin): Product
    {
        $key = "phien_cham:{$phien}";

        // pull = lay + xoa trong 1 buoc -> bam 2 lan thi lan 2 khong con phien.
        $data = Cache::pull($key);
        if (! $data) {
            throw new DomainException('Phiên chấm đã hết hạn hoặc đã được đăng bán. Vui lòng chấm lại.');
        }

        try {
            $product = DB::transaction(fn () => $this->taoLo($data, $thongTin));
        } catch (Throwable $e) {
            Cache::put($key, $data, now()->addHour());   // loi giua chung -> tra phien lai de thu lai

            throw $e;
        }

        Storage::disk('local')->delete($data['anh']);

        return $product;
    }

    private function taoLo(array $data, array $tt): Product
    {
        $kq = $data['ket_qua'];
        $anXanh = $data['muc_dich_ban'] === 'an_xanh';

        $farmerId = User::where('email', config('agrisenai.nong_dan_demo'))->valueOrFail('id');
        $category = Category::firstOrCreate(
            ['slug' => $anXanh ? 'xoai-xanh' : 'xoai'],
            ['name' => $anXanh ? 'Xoài ăn xanh' : 'Xoài ăn chín'],
        );
        $hang = config("agrisenai.grades.{$kq['grade']}.nhan", $kq['grade']);
        $diem = round((float) $kq['overall_score'], 1);

        $p = Product::create([
            'farmer_id' => $farmerId,
            'category_id' => $category->id,
            'name' => $tt['ten'],
            'description' => trim(($tt['giong'] ?? '').' — AI chấm '.$hang.', '.$diem.'/10. '.($kq['reasoning'] ?? ''), ' —'),
            'price' => $tt['gia'],
            'unit' => $tt['don_vi'],
            'quantity_available' => $tt['so_luong'],
            'qr_code' => (string) Str::ulid(),
            'status' => 'active',
        ]);

        $bytes = Storage::disk('local')->get($data['anh']);
        $relImg = "products/{$p->id}.jpg";
        Storage::disk('public')->put($relImg, $bytes);

        ProductImage::create([
            'product_id' => $p->id,
            'image_url' => 'storage/'.$relImg,          // asset() + storage:link
            'is_primary' => true,
        ]);

        $defects = $kq['defects'] ?? [];
        $a = new AiQualityAssessment();
        $a->product_id = $p->id;
        $a->image_url = $relImg;
        $a->image_sha256 = hash('sha256', $bytes);
        $a->ripeness_level = $anXanh ? 'xanh' : 'chin';
        $a->quality_score = $diem * 10;
        $a->defect_detected = $defects !== [];
        $a->defect_note = $defects ? implode('; ', array_map(
            fn ($d) => "{$d['type']} ~{$d['area_percent']}%", $defects)) : null;
        $a->raw_ai_response = $kq + [
            'muc_dich_ban' => $data['muc_dich_ban'],
            'so_lieu_nong_dan' => [
                'giong' => $tt['giong'] ?? null,
                'khoi_luong' => isset($tt['khoi_luong']) ? (int) $tt['khoi_luong'] : null,
                'brix' => isset($tt['brix']) ? (float) $tt['brix'] : null,
                'ma_vung_trong' => $tt['ma_vung_trong'] ?? null,
                'dieu_kien_kho' => $tt['dieu_kien_kho'] ?? null,
            ],
        ];
        $a->assessed_at = now();
        $a->save();
        $a->integrity_hash = $this->trace->computeHash($a);
        $a->save();

        TraceabilityLog::create([
            'product_id' => $p->id, 'stage' => 'danh_gia_ai',
            'description' => "AI chấm {$hang} — {$diem}/10 theo TCVN 9766:2013",
            'location' => 'AgriSenAI', 'happened_at' => now(),
        ]);
        TraceabilityLog::create([
            'product_id' => $p->id, 'stage' => 'dang_ban',
            'description' => 'Đưa lô lên sàn AgriSenAI', 'location' => 'Online', 'happened_at' => now(),
        ]);

        return $p;
    }
}
```

> Kiểm tra: `User::valueOrFail()` có từ Laravel 10. Nếu IDE báo không có, dùng
> `User::where(...)->firstOrFail()->id`.

- [ ] **Step 5: Viết `LotController`**

```php
<?php

namespace App\Http\Controllers;

use App\Services\LotPublisher;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function store(Request $request, LotPublisher $publisher): JsonResponse
    {
        // Chi nhan THONG TIN NONG DAN NHAP. Diem/hang khong co trong rule -> bi bo qua.
        $tt = $request->validate([
            'phien' => ['required', 'string', 'size:26'],
            'ten' => ['required', 'string', 'max:200'],
            'gia' => ['required', 'numeric', 'min:1000', 'max:10000000'],
            'so_luong' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'don_vi' => ['required', 'in:kg,thung'],
            'giong' => ['nullable', 'string', 'max:100'],
            'khoi_luong' => ['nullable', 'integer', 'min:50', 'max:3000'],
            'brix' => ['nullable', 'numeric', 'min:0', 'max:35'],
            'ma_vung_trong' => ['nullable', 'string', 'regex:/^[A-Z0-9.\-]{4,50}$/'],
            'dieu_kien_kho' => ['nullable', 'in:thuong,lanh'],
        ]);

        try {
            $p = $publisher->publish($tt['phien'], $tt);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 410);
        }

        return response()->json([
            'product_id' => $p->id,
            'qr_code' => $p->qr_code,
            'trace_url' => route('trace.show', $p->qr_code),
            'qr_url' => route('trace.qr', $p->qr_code),
            'tem_url' => route('trace.tem', $p->qr_code),
            'product_url' => route('products.show', $p),
        ], 201);
    }
}
```

> `trace.tem` được tạo ở Task 1.4. Để test Task 1.2 xanh ngay, thêm luôn route `trace.tem` trỏ tạm vào
> `TraceController@show` ở step 6 — Task 1.4 sẽ đổi sang `tem`.

- [ ] **Step 6: Route** — trong `routes/web.php`, sau 2 route `/danh-gia`:

```php
Route::post('/danh-gia/dang-ban', [\App\Http\Controllers\LotController::class, 'store'])
    ->name('quality.publish')->middleware('throttle:20,1');
```

và sau route `trace.qr`:

```php
Route::get('/trace/{product:qr_code}/tem', [TraceController::class, 'show'])   // Task 1.4 doi sang 'tem'
    ->name('trace.tem')->middleware('throttle:60,1');
```

- [ ] **Step 7: Chạy** `$P artisan test --filter=DangBanLoTest` → 6 passed; `$P artisan test` → xanh.
- [ ] **Step 8: Commit**

```bash
git add app/Services/LotPublisher.php app/Http/Controllers/LotController.php routes/web.php config/agrisenai.php tests/Feature/DangBanLoTest.php
git commit -m "Cau noi AI -> san: dang ban lo tu phien cham, sinh QR + hash toan ven"
```

---

### Task 1.3: Giao diện "Đăng bán lô này" trên trang AI đánh giá

**Files:**
- Modify: `resources/views/quality/index.blade.php`
  - form nhập (khoảng dòng 390–420): thêm chọn **mục đích bán**
  - `.result-actions` (khoảng dòng 498): thêm nút + panel đăng bán
  - script: gửi `muc_dich_ban` khi chấm, lưu `r.phien`, xử lý đăng bán
- Test: `tests/Feature/QualityPageTest.php` (tạo mới)

**Interfaces:**
- Consumes: `r.phien` từ JSON chấm (Task 1.1); `POST route('quality.publish')` (Task 1.2).
- Produces: DOM id `mucDichBan` (select), `publishBtn`, `publishPanel`, `publishForm`, `publishDone`, `publishQr`.

- [ ] **Step 1: Test (fail)**

```php
<?php
// tests/Feature/QualityPageTest.php
namespace Tests\Feature;

use Tests\TestCase;

class QualityPageTest extends TestCase
{
    public function test_trang_co_chon_muc_dich_va_panel_dang_ban(): void
    {
        $res = $this->get('/danh-gia')->assertOk();

        $res->assertSee('id="mucDichBan"', false);
        $res->assertSee('id="publishPanel"', false);
        $res->assertSee(route('quality.publish'), false);
    }
}
```

- [ ] **Step 2: Thêm chọn mục đích bán** — trong `.field-grid`, trước ô `variety`:

```blade
                        <div class="field full">
                            <label for="mucDichBan">Mục đích bán</label>
                            <select id="mucDichBan" name="muc_dich_ban">
                                @foreach (config('agrisenai.muc_dich_ban') as $k => $nhan)
                                    <option value="{{ $k }}" @selected($k === 'an_chin')>{{ $nhan }}</option>
                                @endforeach
                            </select>
                        </div>
```

Nếu `<select>` chưa có style, thêm vào CSS: `.ai-page .field select { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; font:inherit; background:var(--surface); }`
(đối chiếu tên biến màu với CSS `.field input` hiện có và dùng đúng các biến đó).

- [ ] **Step 3: Nút + panel** — thay khối `.result-actions` bằng:

```blade
                    <div class="result-actions">
                        <button type="button" class="btn-ghost" id="retryBtn">Đánh giá lại</button>
                        <button type="button" class="btn-solid" id="publishBtn">Đăng bán lô này →</button>
                    </div>

                    <div class="publish-panel" id="publishPanel" hidden>
                        <form id="publishForm" data-url="{{ route('quality.publish') }}">
                            <div class="field-grid">
                                <div class="field full"><label for="pTen">Tên lô hàng</label>
                                    <input id="pTen" name="ten" required maxlength="200"></div>
                                <div class="field"><label for="pGia">Giá (đ)</label>
                                    <input id="pGia" name="gia" type="number" min="1000" required inputmode="numeric"></div>
                                <div class="field"><label for="pSoLuong">Số lượng</label>
                                    <input id="pSoLuong" name="so_luong" type="number" min="1" required inputmode="decimal"></div>
                                <div class="field"><label for="pDonVi">Đơn vị</label>
                                    <select id="pDonVi" name="don_vi"><option value="kg">kg</option><option value="thung">thùng</option></select></div>
                            </div>
                            <div class="ai-alert" id="publishErr" hidden></div>
                            <button type="submit" class="btn-assess" id="publishSubmit"><span>Tạo lô &amp; mã QR</span></button>
                        </form>
                        <div class="publish-done" id="publishDone" hidden>
                            <img id="publishQr" alt="Mã QR lô hàng" width="180" height="180">
                            <div class="publish-links">
                                <a id="publishTem" class="btn-solid" target="_blank">🖨️ In tem QR</a>
                                <a id="publishTrace" class="btn-ghost" target="_blank">Xem hồ sơ lô</a>
                                <a id="publishProduct" class="btn-ghost">Xem trên sàn</a>
                            </div>
                        </div>
                    </div>
```

CSS thêm cạnh `.result-actions`:

```css
        .ai-page .publish-panel { margin-top: 18px; padding: 16px; border: 1px dashed var(--green); border-radius: 14px; }
        .ai-page .publish-done { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .ai-page .publish-done[hidden], .ai-page .publish-panel[hidden] { display: none; }
        .ai-page .publish-links { display: flex; flex-direction: column; gap: 8px; }
```

- [ ] **Step 4: Script**
  1. Trong hàm gửi chấm (tìm chỗ tạo `FormData` cho `/danh-gia`), thêm:
     `fd.append('muc_dich_ban', document.getElementById('mucDichBan').value);`
  2. Khai báo `let phienHienTai = null;` đầu IIFE. Trong hàm render kết quả dùng được (hàm chứa `document.getElementById('scoreVal')`), thêm ở đầu: `phienHienTai = r.phien || null;` và reset panel:

```js
                document.getElementById('publishPanel').hidden = true;
                document.getElementById('publishDone').hidden = true;
                document.getElementById('publishForm').hidden = false;
                document.getElementById('publishBtn').hidden = !phienHienTai;
                const giong = document.getElementById('variety').value.trim();
                document.getElementById('pTen').value =
                    'Xoài ' + (giong ? giong + ' ' : '') + '— ' + (meta.badge || '');
                const kg = (Number(document.getElementById('weight').value) *
                            Number(document.getElementById('quantity').value)) / 1000;
                document.getElementById('pSoLuong').value = kg > 0 ? kg.toFixed(1) : '';
```

  3. Cuối IIFE thêm handler:

```js
            document.getElementById('publishBtn').addEventListener('click', () => {
                document.getElementById('publishPanel').hidden = false;
                document.getElementById('pGia').focus();
            });

            document.getElementById('publishForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const f = e.currentTarget;
                const btn = document.getElementById('publishSubmit');
                const err = document.getElementById('publishErr');
                err.hidden = true;
                btn.disabled = true;                       // chan bam 2 lan phia client (server cung chan)

                const val = (id) => document.getElementById(id).value.trim() || null;
                const body = {
                    phien: phienHienTai,
                    ten: f.ten.value.trim(), gia: Number(f.gia.value),
                    so_luong: Number(f.so_luong.value), don_vi: f.don_vi.value,
                    giong: val('variety'),
                    khoi_luong: val('weight') ? Number(val('weight')) : null,
                    brix: val('brix') ? Number(val('brix')) : null,
                };

                try {
                    const res = await fetch(f.dataset.url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Không tạo được lô.');

                    document.getElementById('publishQr').src = data.qr_url;
                    document.getElementById('publishTem').href = data.tem_url;
                    document.getElementById('publishTrace').href = data.trace_url;
                    document.getElementById('publishProduct').href = data.product_url;
                    f.hidden = true;
                    document.getElementById('publishDone').hidden = false;
                    document.getElementById('publishBtn').hidden = true;
                    phienHienTai = null;
                } catch (ex) {
                    err.textContent = ex.message;
                    err.hidden = false;
                    btn.disabled = false;
                }
            });
```

- [ ] **Step 5:** `$P artisan test` → xanh.
- [ ] **Step 6: Kiểm tra tay** (cần `GEMINI_API_KEY` + `$P artisan storage:link` + DB đã seed): mở `/danh-gia`, chấm 1 ảnh từ `storage/app/testset/xoai/t01.jpg`, bấm **Đăng bán lô này**, nhập giá → thấy QR; bấm "Xem hồ sơ lô" → trang trace hiện **đúng ảnh vừa chụp**, dải xanh "đã xác minh". Dùng skill `run` nếu nhờ Claude chạy.
- [ ] **Step 7: Commit** `git commit -m "Trang AI danh gia: nut dang ban lo + hien QR ngay sau khi cham"`

---

### Task 1.4: Tem QR in được (dán lên thùng xoài)

**Files:**
- Modify: `app/Http/Controllers/TraceController.php` (thêm `tem`)
- Modify: `routes/web.php` (route `trace.tem` trỏ sang `tem`)
- Create: `resources/views/trace/tem.blade.php`
- Test: `tests/Feature/TemQrTest.php`

**Interfaces:**
- Consumes: route `trace.tem` (Task 1.2), `$product->latestAssessment`.
- Produces: `TraceController::tem(Product $product)` → view `trace.tem` với `$product`, `$assessment`, `$svg` (chuỗi SVG inline).

- [ ] **Step 1: Test (fail)**

```php
<?php
// tests/Feature/TemQrTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_tem_co_qr_inline_ten_ma_lo_va_hang(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/trace/'.$p->qr_code.'/tem')->assertOk()->assertViewIs('trace.tem');

        $res->assertSee('<svg', false);
        $res->assertSee($p->name);
        $res->assertSee($p->qr_code);
        $res->assertSee('Quét để xem hồ sơ lô');
    }

    public function test_tem_lo_chua_cham_404(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->assessments()->delete();

        $this->get('/trace/'.$p->qr_code.'/tem')->assertNotFound();
    }
}
```

- [ ] **Step 2: Controller** — thêm vào `TraceController`:

```php
    public function tem(Product $product)
    {
        $assessment = $product->assessments()->latest('assessed_at')->first();
        abort_if(! $assessment, 404, 'Sản phẩm chưa được AI đánh giá');

        $svg = QrCode::format('svg')->size(220)->margin(0)
            ->generate(route('trace.show', $product->qr_code));

        return view('trace.tem', compact('product', 'assessment', 'svg'));
    }
```

và đổi route: `[TraceController::class, 'tem']`.

- [ ] **Step 3: View** `resources/views/trace/tem.blade.php` — trang độc lập (không dùng layout app), khổ 100×70 mm:

```blade
@php
    $hangKey = $assessment->raw_ai_response['grade'] ?? null;
    $hang = config("agrisenai.grades.{$hangKey}.nhan", 'Đã chấm AI');
@endphp
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tem QR — {{ $product->name }}</title>
    <style>
        @page { size: 100mm 70mm; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #f2f2f2; }
        .tem { width: 100mm; height: 70mm; margin: 12px auto; background: #fff; padding: 5mm;
               display: grid; grid-template-columns: 34mm 1fr; gap: 4mm; border: 1px dashed #999; }
        .qr svg { width: 34mm; height: 34mm; }
        .qr small { display: block; font-size: 7pt; text-align: center; margin-top: 1mm; }
        h1 { font-size: 11pt; margin: 0 0 2mm; line-height: 1.2; }
        .hang { display: inline-block; font-weight: 800; font-size: 10pt; padding: 1mm 2.5mm;
                border: 1.5px solid #1b5e20; color: #1b5e20; border-radius: 2mm; }
        .dong { font-size: 8pt; margin-top: 1.5mm; }
        .ma { font-family: ui-monospace, monospace; font-size: 7pt; word-break: break-all; }
        .brand { grid-column: 1 / -1; font-size: 7pt; color: #555; border-top: 1px solid #ddd; padding-top: 1.5mm; }
        .no-print { text-align: center; margin: 12px; }
        @media print { body { background: #fff; } .tem { margin: 0; border: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="tem">
        <div class="qr">{!! $svg !!}<small>Quét để xem hồ sơ lô</small></div>
        <div>
            <h1>{{ $product->name }}</h1>
            <span class="hang">{{ $hang }} · {{ number_format($assessment->quality_score / 10, 1) }}/10</span>
            <div class="dong">Nhà vườn: {{ $product->farmer?->farmerProfile?->farm_name ?? $product->farmer?->name }}</div>
            <div class="dong">Ngày chấm AI: {{ $assessment->assessed_at?->format('d/m/Y') }}</div>
            <div class="dong">Mã lô: <span class="ma">{{ $product->qr_code }}</span></div>
        </div>
        <div class="brand">AgriSenAI · Chấm ngoại quan theo TCVN 9766:2013 · Dữ liệu chống sửa (SHA-256)</div>
    </div>
    <div class="no-print"><button onclick="window.print()">🖨️ In tem</button></div>
</body>
</html>
```

> Kiểm tra: `farmerProfile` là tên quan hệ trên `User` (TraceController đang eager-load `farmer.farmerProfile` — dùng đúng tên đó).

- [ ] **Step 4:** `$P artisan test` → xanh. Mở `/trace/{qr}/tem` → Ctrl+P → xem trước đúng 1 tem.
- [ ] **Step 5: Commit** `git commit -m "Tem QR in duoc cho lo xoai (100x70mm)"`

---

### Task 1.5 👤 + Claude: Ảnh xoài hỏng — dạy AI "phát hiện xoài thâm/dập/thối"

**Vì sao:** Đây là **chỉ số chính** giảng viên gợi ý tập trung. Hiện AI chấm đúng `khong_dat` **0/2**,
vá bằng prompt không ăn thua (`docs/files-ke-hoach/quy-trinh-xay-module-ai.md` mục 9, 12).

**Files:**
- Data: `dataset/thu-thap/xoai-hong/` (ngoài git), `dataset/thu-thap/nguon-anh.csv`
- Modify: `storage/app/reference/xoai/source.json`, `labels.json`
- Modify: `storage/app/testset/xoai/source.json`, `ground_truth.json`

- [ ] **Step 1 👤: Chụp ~10 quả xoài hỏng** tại vựa/chợ Cao Lãnh (xin quả loại ra):
  3 quả **thâm/dập** (ưu tiên — đúng chỉ số cô nêu), 3 quả **thối/mốc**, 2 quả **nứt/chảy nhựa**, 2 quả **thâm nhẹ ranh giới hạng 2 / không đạt**.
  Chụp theo `docs/quy-trinh-chup-anh-chuan.md` (Task 1.6). Nền **trộn**: bàn gỗ + băng chuyền xanh/rổ nhựa như ảnh tốt — **không** chụp tất cả trên một nền.
  Đặt tên `xoai_dap_01.jpg`, `xoai_thoi_02.jpg`, `xoai_nut_03.jpg`…; ghi mỗi ảnh 1 dòng vào `nguon-anh.csv` với `nguon_url = tu_chup`.
- [ ] **Step 2 👤: Bỏ 3 ảnh Shutterstock** (`xoai_hong_thoi_10/11/12`) khỏi thư mục — có bản quyền, không dùng khi thi.
- [ ] **Step 3 (Claude): Soát ảnh** — xem từng ảnh, loại ảnh mờ / nhiều quả / watermark; đề xuất nhãn (grade, defects, area_percent) và chia: **2 ảnh vào reference** (1 thâm/dập nặng, 1 thối) làm neo sàn mới `07_khong_dat_dap.jpg`, `08_khong_dat_thoi.jpg`; **phần còn lại vào testset** (`t26…`).
- [ ] **Step 4 👤: Duyệt nhãn** — nhờ chủ vựa / cán bộ khuyến nông xác nhận hạng từng quả (ghi tên người duyệt vào `_huong_dan` của `labels.json`). Đây là điều kiện để con số accuracy có giá trị trước giám khảo.
- [ ] **Step 5 (Claude): Dựng lại + đo**

```bash
$P artisan reference:build xoai
$P artisan reference:build xoai --set=testset
$P artisan quality:eval xoai
```

  8 ảnh mẫu ≈ 1,1 MB/request: đặt `GEMINI_TIMEOUT=180` trong `.env` nếu chưa có.
- [ ] **Step 6: Tiêu chí đạt:** bắt đúng `khong_dat` **≥ 70%** ảnh hỏng trong testset, và độ chính xác các hạng khác **không giảm quá 1 ảnh** so với lần đo trước. Chưa đạt → ghi số liệu thật vào `docs/files-ke-hoach/quy-trinh-xay-module-ai.md` mục 12 (không giấu), thử đổi ảnh neo.
- [ ] **Step 7:** Cập nhật bảng hạn chế ở `docs/files-ke-hoach/quy-trinh-xay-module-ai.md` mục 12 + commit ảnh 768px và các file json.

---

### Task 1.6: Quy trình chụp chuẩn + khung ngắm trên màn hình

**Vì sao:** Trả lời trực tiếp "điều kiện ánh sáng/thiết bị khác nhau" và gợi ý "hộp chụp ảnh tiêu chuẩn".

**Files:**
- Create: `docs/quy-trinh-chup-anh-chuan.md`
- Modify: `resources/views/quality/index.blade.php` (khung ngắm đè lên ảnh xem trước + checklist 4 dòng)
- Test: thêm 1 test vào `tests/Feature/QualityPageTest.php`

- [ ] **Step 1: Viết `docs/quy-trinh-chup-anh-chuan.md`** gồm đúng các mục:
  1. **Chuẩn tối thiểu (điện thoại, không cần dụng cụ):** 1 quả/ảnh, quả chiếm 50–80% khung, nằm trong khung elip trên màn hình; ánh sáng ban ngày trong bóng râm, **không flash**; nền trơn màu trung tính (bàn gỗ, giấy xám, rổ nhựa trơn); giữ máy song song mặt quả, cách 25–35 cm; lấy nét bằng cách chạm vào quả.
  2. **Hộp chụp chuẩn cho HTX (~150.000đ):** thùng carton ~40×40×40 cm, lót giấy A3 xám trung tính, dải LED 5.000–6.500K dán 2 bên thành hộp, khoét lỗ nắp đặt điện thoại cố định độ cao 30 cm. Mục đích: mọi ảnh cùng ánh sáng + khoảng cách → AI ổn định hơn.
  3. **Vì sao vẫn chạy được khi không có hộp:** mọi ảnh qua `ImageNormalizer` (cắt vuông, 768×768, cùng định dạng); ảnh mờ/tối bị chặn ngay trên máy (Task 2.1); AI được dạy bằng ảnh mẫu trộn nhiều nền.
  4. **Ảnh bị từ chối khi:** nhiều quả chồng nhau; quả < 30% khung; ngược sáng; có tay che quá 20% quả.
  Kèm 2 ảnh minh hoạ đúng/sai lấy từ `storage/app/testset/xoai/` (tham chiếu đường dẫn, không copy).
- [ ] **Step 2: Test (fail)** — thêm vào `QualityPageTest`:

```php
    public function test_co_khung_ngam_va_huong_dan_chup(): void
    {
        $res = $this->get('/danh-gia')->assertOk();
        $res->assertSee('class="khung-ngam"', false);
        $res->assertSee('Không dùng đèn flash');
    }
```

- [ ] **Step 3: Khung ngắm** — trong khối xem trước ảnh (phần tử chứa `<img>` preview; tìm `preview` trong view), thêm ngay sau thẻ `<img>`:

```blade
<div class="khung-ngam" aria-hidden="true"></div>
```

CSS (phần tử cha cần `position: relative`):

```css
        .ai-page .khung-ngam { position: absolute; inset: 10% 14%; border: 2.5px dashed rgba(255,255,255,.9);
            border-radius: 50%; box-shadow: 0 0 0 9999px rgba(0,0,0,.18); pointer-events: none; }
```

và ngay dưới vùng tải ảnh, checklist:

```blade
<ul class="meo-chup">
    <li>1 quả / ảnh, nằm trọn trong khung elip</li>
    <li>Ánh sáng ban ngày, trong bóng râm</li>
    <li>Không dùng đèn flash</li>
    <li>Nền trơn: bàn gỗ, giấy xám, rổ nhựa</li>
</ul>
```

- [ ] **Step 4:** `$P artisan test` → xanh. **Step 5: Commit.**

---

### Task 1.7 👤: Thử nghiệm thực địa tại vựa — bằng chứng "AI chạy ngoài đời"

**Vì sao:** Điểm yếu thứ 4 của góp ý. Không cần code; cần **số liệu thật + ảnh chụp buổi thử**.

- [ ] **Step 1:** Hẹn 1 vựa/HTX xoài ở Cao Lãnh (Mỹ Xương, Tịnh Thới…). Mang điện thoại đã cài PWA + (nếu có) hộp chụp Task 1.6.
- [ ] **Step 2:** Chủ vựa phân loại **30 quả** theo cách họ vẫn làm (loại 1 / loại 2 / dạt) — **ghi trước khi AI chấm**.
- [ ] **Step 3:** Chấm từng quả trên `/danh-gia`; ghi vào bảng: `stt | hạng chủ vựa | hạng AI | điểm AI | thời gian chấm (giây) | ghi chú`.
- [ ] **Step 4:** Tạo 1 lô thật bằng Task 1.3, in tem Task 1.4, dán lên thùng, quét bằng điện thoại khác → chụp ảnh làm minh chứng.
- [ ] **Step 5 (Claude):** Từ bảng, tính tỷ lệ khớp, ma trận nhầm lẫn, thời gian TB; viết `docs/thu-nghiem-thuc-dia-<ngay>.md` (số liệu + hạn chế trung thực + ảnh). Nếu được, đưa 30 ảnh này vào testset để `quality:eval` đo lại.
- [ ] **Step 6:** Hỏi chủ vựa 3 câu, ghi nguyên văn: *Có dùng không nếu miễn phí? Trả bao nhiêu/tháng thì chấp nhận? Người mua sỉ của họ có cần hồ sơ QR không?* — dùng cho phần kinh doanh.

---

## Giai đoạn 2 — Thực chiến tại vựa, sẵn sàng B2B

### Task 2.1: Kiểm tra ảnh mờ/tối ngay trên điện thoại

**Vì sao:** Chặn ảnh hỏng trong < 0,5 giây, không tốn 4G/API. Làm bằng JS thuần (Laplacian variance + độ sáng TB) — không cần TensorFlow.js.

**Files:**
- Create: `public/js/kiem-tra-anh.mjs` (ES module, dùng được cả trình duyệt lẫn Node)
- Create: `tests/js/kiem-tra-anh.test.mjs`
- Modify: `package.json` (script `test:js`)
- Modify: `resources/views/quality/index.blade.php` (gọi kiểm tra khi chọn ảnh)

**Interfaces:**
- Produces (từ `kiem-tra-anh.mjs`):
  - `toGray(rgba: Uint8ClampedArray, w: number, h: number): Float32Array`
  - `doSang(gray: Float32Array): number` — 0..255
  - `doNet(gray: Float32Array, w: number, h: number): number` — phương sai Laplacian
  - `danhGiaAnh({doSang, doNet}, nguong = NGUONG): {ok: boolean, loi: string[]}`
  - `NGUONG = {toiMin: 55, sangMax: 215, netMin: 60}` (hiệu chỉnh ở step 6)
  - `kiemTraFile(file: File, canh = 256): Promise<{ok, loi, doSang, doNet}>` — chỉ chạy trên trình duyệt

- [ ] **Step 1: Test (fail)**

```js
// tests/js/kiem-tra-anh.test.mjs
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { toGray, doSang, doNet, danhGiaAnh, NGUONG } from '../../public/js/kiem-tra-anh.mjs';

const W = 64, H = 64;
const phang = (v) => new Float32Array(W * H).fill(v);
const banCo = () => Float32Array.from({ length: W * H }, (_, i) => ((i % W) + Math.floor(i / W)) % 2 ? 230 : 30);

test('toGray doi RGBA sang xam theo trong so luma', () => {
    const g = toGray(new Uint8ClampedArray([255, 0, 0, 255, 0, 255, 0, 255]), 2, 1);
    assert.ok(Math.abs(g[0] - 76.2) < 0.5);
    assert.ok(Math.abs(g[1] - 149.7) < 0.5);
});

test('anh phang: do net = 0 -> bi bao mo', () => {
    const g = phang(128);
    assert.equal(doNet(g, W, H), 0);
    const kq = danhGiaAnh({ doSang: doSang(g), doNet: doNet(g, W, H) });
    assert.equal(kq.ok, false);
    assert.ok(kq.loi.some((l) => l.includes('mờ')));
});

test('ban co: net va du sang -> dat', () => {
    const g = banCo();
    const kq = danhGiaAnh({ doSang: doSang(g), doNet: doNet(g, W, H) });
    assert.deepEqual(kq, { ok: true, loi: [] });
});

test('anh qua toi va qua chay', () => {
    assert.ok(danhGiaAnh({ doSang: 20, doNet: 999 }).loi.some((l) => l.includes('tối')));
    assert.ok(danhGiaAnh({ doSang: 240, doNet: 999 }).loi.some((l) => l.includes('chói')));
});

test('nguong mac dinh hop ly', () => {
    assert.ok(NGUONG.toiMin < NGUONG.sangMax);
});
```

- [ ] **Step 2:** `package.json` → `"scripts": { ..., "test:js": "node --test tests/js/" }`. Chạy `node --test tests/js/` → FAIL (không có module).
- [ ] **Step 3: Viết module**

```js
// public/js/kiem-tra-anh.mjs
// Kiem tra nhanh anh TREN MAY truoc khi gui len server: mo? toi? choi?
// Khong dung thu vien — chay duoc ca trinh duyet lan Node (de test).

export const NGUONG = { toiMin: 55, sangMax: 215, netMin: 60 };

export function toGray(rgba, w, h) {
    const g = new Float32Array(w * h);
    for (let i = 0; i < w * h; i++) {
        g[i] = 0.299 * rgba[i * 4] + 0.587 * rgba[i * 4 + 1] + 0.114 * rgba[i * 4 + 2];
    }
    return g;
}

export function doSang(gray) {
    let s = 0;
    for (const v of gray) s += v;
    return s / gray.length;
}

// Phuong sai cua Laplacian 4-lan-can: anh mo -> bien it -> phuong sai thap.
export function doNet(gray, w, h) {
    let n = 0, sum = 0, sumSq = 0;
    for (let y = 1; y < h - 1; y++) {
        for (let x = 1; x < w - 1; x++) {
            const i = y * w + x;
            const lap = gray[i - w] + gray[i + w] + gray[i - 1] + gray[i + 1] - 4 * gray[i];
            sum += lap; sumSq += lap * lap; n++;
        }
    }
    if (n === 0) return 0;
    const tb = sum / n;
    return sumSq / n - tb * tb;
}

export function danhGiaAnh({ doSang: sang, doNet: net }, nguong = NGUONG) {
    const loi = [];
    if (net < nguong.netMin) loi.push('Ảnh bị mờ — giữ chắc tay và chạm vào quả để lấy nét.');
    if (sang < nguong.toiMin) loi.push('Ảnh quá tối — ra chỗ sáng hơn (không dùng flash).');
    if (sang > nguong.sangMax) loi.push('Ảnh bị chói — tránh nắng gắt chiếu thẳng vào quả.');
    return { ok: loi.length === 0, loi };
}

// Chi dung tren trinh duyet: thu nho anh xuong ~256px roi do.
export async function kiemTraFile(file, canh = 256) {
    const bmp = await createImageBitmap(file);
    const tile = Math.min(1, canh / Math.max(bmp.width, bmp.height));
    const w = Math.max(3, Math.round(bmp.width * tile));
    const h = Math.max(3, Math.round(bmp.height * tile));
    const cv = new OffscreenCanvas(w, h);
    const ctx = cv.getContext('2d');
    ctx.drawImage(bmp, 0, 0, w, h);
    const gray = toGray(ctx.getImageData(0, 0, w, h).data, w, h);
    const s = doSang(gray), n = doNet(gray, w, h);
    return { ...danhGiaAnh({ doSang: s, doNet: n }), doSang: s, doNet: n };
}
```

- [ ] **Step 4:** `node --test tests/js/` → 5 passed.
- [ ] **Step 5: Gắn vào trang** — trong `quality/index.blade.php`, thêm trước `</script>` hiện có một script module riêng:

```blade
    <script type="module">
        import { kiemTraFile } from '{{ asset('js/kiem-tra-anh.mjs') }}';
        const input = document.querySelector('#assessForm input[type=file]');
        const btn = document.getElementById('assessBtn');
        const box = document.getElementById('aiAlert');
        input?.addEventListener('change', async () => {
            const f = input.files?.[0];
            if (!f) return;
            try {
                const kq = await kiemTraFile(f);
                console.debug('[kiem-tra-anh]', kq);          // dung de hieu chinh nguong
                btn.disabled = !kq.ok;
                if (!kq.ok) { box.textContent = kq.loi.join(' '); box.style.display = 'flex'; }
                else { box.style.display = 'none'; }
            } catch { btn.disabled = false; }                 // trinh duyet cu: bo qua, de server lo
        });
    </script>
```

(Kiểm tra id `aiAlert` và cách script hiện tại hiện/ẩn nó — dùng đúng cơ chế đó thay cho `style.display` nếu khác.)
- [ ] **Step 6: Hiệu chỉnh ngưỡng** — mở `/danh-gia`, DevTools Console, lần lượt chọn 10 ảnh tốt trong `storage/app/testset/xoai/` và 3 ảnh cố tình chụp mờ/tối; ghi `doNet`, `doSang`. Đặt `netMin` ≈ 50% giá trị nhỏ nhất của ảnh tốt, sửa `NGUONG` + ghi bảng số đo vào comment đầu file.
- [ ] **Step 7:** `$P artisan test` + `node --test tests/js/` → xanh. Commit.

---

### Task 2.2: Dự báo thời gian bảo quản & cự ly vận chuyển

**Vì sao:** Người mua B2B hỏi "xoài này chở ra Hà Nội có kịp không?". Làm bằng **bảng luật minh bạch** trong config (giải thích được với giám khảo), không nhờ AI đoán.

**Files:**
- Modify: `config/agrisenai.php` (khoá `bao_quan`)
- Create: `app/Services/DuBaoBaoQuan.php`
- Modify: `resources/views/trace/show.blade.php` (khối "Dự báo bảo quản"), `quality/index.blade.php` (chọn điều kiện kho trong panel đăng bán, gửi `dieu_kien_kho`)
- Test: `tests/Unit/DuBaoBaoQuanTest.php`, thêm 1 test vào `tests/Feature/TracePageTest.php`

**Interfaces:**
- Consumes: `ripeness_level` của assessment; `raw_ai_response.so_lieu_nong_dan.dieu_kien_kho` (Task 1.2).
- Produces: `DuBaoBaoQuan::duBao(string $ripeness, string $kho = 'thuong'): array{ngay_min:int, ngay_max:int, cu_ly_km:int, loi_khuyen:string}`

- [ ] **Step 1: Config** (trước `'image' =>`):

```php
    /*
    | Du bao bao quan — BANG LUAT, khong phai AI doan.
    | CAN XAC MINH so ngay voi can bo khuyen nong / tai lieu sau thu hoach xoai
    | truoc khi dem di thi. Kho lanh = 12-13 do C (xoai bi ton thuong lanh < 10 do C).
    | cu_ly: xe tai ~500 km/ngay, chua 1 ngay du phong.
    */
    'bao_quan' => [
        'km_moi_ngay' => 500,
        'ngay' => [
            'xanh'     => ['thuong' => [6, 9],  'lanh' => [14, 21]],
            'vua_chin' => ['thuong' => [4, 6],  'lanh' => [10, 14]],
            'chin'     => ['thuong' => [2, 4],  'lanh' => [6, 9]],
            'qua_chin' => ['thuong' => [0, 1],  'lanh' => [2, 3]],
        ],
    ],
```

- [ ] **Step 2: Test (fail)**

```php
<?php
// tests/Unit/DuBaoBaoQuanTest.php
namespace Tests\Unit;

use App\Services\DuBaoBaoQuan;
use Tests\TestCase;

class DuBaoBaoQuanTest extends TestCase
{
    public function test_xoai_chin_nhiet_do_thuong(): void
    {
        $kq = app(DuBaoBaoQuan::class)->duBao('chin', 'thuong');
        $this->assertSame([2, 4], [$kq['ngay_min'], $kq['ngay_max']]);
        $this->assertSame(500, $kq['cu_ly_km']);          // (2-1) * 500
    }

    public function test_kho_lanh_keo_dai(): void
    {
        $kq = app(DuBaoBaoQuan::class)->duBao('xanh', 'lanh');
        $this->assertSame(14, $kq['ngay_min']);
        $this->assertSame(6500, $kq['cu_ly_km']);
    }

    public function test_qua_chin_khong_nen_cho_xa(): void
    {
        $kq = app(DuBaoBaoQuan::class)->duBao('qua_chin');
        $this->assertSame(0, $kq['cu_ly_km']);
        $this->assertStringContainsString('tiêu thụ ngay', $kq['loi_khuyen']);
    }

    public function test_gia_tri_la_roi_ve_mac_dinh(): void
    {
        $kq = app(DuBaoBaoQuan::class)->duBao('khong_ro', 'tu_lanh');
        $this->assertSame([2, 4], [$kq['ngay_min'], $kq['ngay_max']]);   // = chin + thuong
    }
}
```

- [ ] **Step 3: Service**

```php
<?php

namespace App\Services;

/** Du bao so ngay bao quan + cu ly van chuyen toi da tu bang luat config('agrisenai.bao_quan'). */
class DuBaoBaoQuan
{
    public function duBao(string $ripeness, string $kho = 'thuong'): array
    {
        $cfg = config('agrisenai.bao_quan');
        $bang = $cfg['ngay'][$ripeness] ?? $cfg['ngay']['chin'];
        [$min, $max] = $bang[$kho] ?? $bang['thuong'];

        $cuLy = max(0, $min - 1) * $cfg['km_moi_ngay'];

        $loiKhuyen = match (true) {
            $cuLy === 0 => 'Nên tiêu thụ ngay tại địa phương, không phù hợp vận chuyển xa.',
            $cuLy < 1000 => "Phù hợp giao trong vùng / TP.HCM (dưới {$cuLy} km đường bộ).",
            default => "Đủ thời gian vận chuyển đường bộ xa (tới ~{$cuLy} km, VD: ra Hà Nội ~1.700 km cần kho lạnh).",
        };

        return ['ngay_min' => $min, 'ngay_max' => $max, 'cu_ly_km' => $cuLy, 'loi_khuyen' => $loiKhuyen];
    }
}
```

- [ ] **Step 4:** Hiện trên trang trace: trong `TraceController::show` truyền thêm
  `$baoQuan = app(DuBaoBaoQuan::class)->duBao($assessment->ripeness_level ?? 'chin', $assessment->raw_ai_response['so_lieu_nong_dan']['dieu_kien_kho'] ?? 'thuong');`
  và trong view thêm khối: *"Dự báo bảo quản: {{ngay_min}}–{{ngay_max}} ngày ({{kho}}). {{loi_khuyen}}"* kèm dòng nhỏ *"Ước tính theo bảng luật, không phải cam kết."*
  Test thêm vào `TracePageTest`: `$this->get('/trace/'.$p->qr_code)->assertSee('Dự báo bảo quản');`
- [ ] **Step 5:** Trong panel đăng bán (Task 1.3), thêm select `dieu_kien_kho` (`thuong` = Nhiệt độ thường, `lanh` = Kho lạnh 12–13°C) và gửi trong `body`.
- [ ] **Step 6:** `$P artisan test` → xanh. Commit.

---

### Task 2.3: Hồ sơ QR đa ngôn ngữ (Việt / Anh / Trung)

**Files:**
- Create: `lang/vi/truy_xuat.php`, `lang/en/truy_xuat.php`, `lang/zh/truy_xuat.php`
- Modify: `app/Http/Controllers/TraceController.php` (`show`, `tem` đọc `?lang=`)
- Modify: `resources/views/trace/show.blade.php` (thay chữ cứng bằng `__('truy_xuat.*')`, thêm nút chuyển ngôn ngữ)
- Test: `tests/Feature/TraceDaNgonNguTest.php`

**Interfaces:**
- Produces: query `?lang=vi|en|zh` (mặc định `vi`); khoá dịch trong `truy_xuat.php`: `tieu_de`, `hang_chat_luong`, `diem_ai`, `nha_vuon`, `nhat_ky`, `xac_minh_ok`, `xac_minh_loi`, `bao_quan`, `ban_goc_tieng_viet`, `hang.dac_biet|hang_1|hang_2|khong_dat`, `do_chin.xanh|vua_chin|chin|qua_chin`, `khuyet_tat.<key>` cho mọi key trong `config('agrisenai.defect_types')`, `giai_doan.thu_hoach|dong_goi|danh_gia_ai|dang_ban|van_chuyen`.

- [ ] **Step 1: Test (fail)**

```php
<?php
// tests/Feature/TraceDaNgonNguTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceDaNgonNguTest extends TestCase
{
    use RefreshDatabase;

    private function url(string $q = ''): string
    {
        $this->seed();

        return '/trace/'.Product::certified()->first()->qr_code.$q;
    }

    public function test_mac_dinh_tieng_viet(): void
    {
        $this->get($this->url())->assertOk()->assertSee('Hạng chất lượng');
    }

    public function test_tieng_anh(): void
    {
        $this->get($this->url('?lang=en'))->assertOk()->assertSee('Quality grade')->assertSee('lang="en"', false);
    }

    public function test_tieng_trung(): void
    {
        $this->get($this->url('?lang=zh'))->assertOk()->assertSee('质量等级');
    }

    public function test_lang_la_roi_ve_tieng_viet(): void
    {
        $this->get($this->url('?lang=../../etc'))->assertOk()->assertSee('Hạng chất lượng');
    }

    public function test_moi_khoa_dich_deu_co_du_3_ngon_ngu(): void
    {
        $vi = array_keys(\Illuminate\Support\Arr::dot(require lang_path('vi/truy_xuat.php')));
        foreach (['en', 'zh'] as $l) {
            $k = array_keys(\Illuminate\Support\Arr::dot(require lang_path("{$l}/truy_xuat.php")));
            $this->assertSame([], array_diff($vi, $k), "Thieu khoa dich trong {$l}");
        }
    }
}
```

- [ ] **Step 2: Controller** — đầu `show` và `tem`:

```php
        $lang = in_array(request('lang'), ['vi', 'en', 'zh'], true) ? request('lang') : 'vi';
        app()->setLocale($lang);
```

- [ ] **Step 3: File dịch** — tạo `lang/vi/truy_xuat.php` trước (liệt kê **mọi** chữ cứng đang có trong `trace/show.blade.php`), rồi dịch sang `en`, `zh`. Ví dụ phần đầu:

```php
<?php // lang/en/truy_xuat.php
return [
    'tieu_de' => 'Lot traceability record',
    'hang_chat_luong' => 'Quality grade',
    'diem_ai' => 'AI score',
    'nha_vuon' => 'Grower',
    'nhat_ky' => 'Lot history',
    'xac_minh_ok' => 'Verified — data has not been altered since grading',
    'xac_minh_loi' => 'WARNING — data differs from the original grading record',
    'bao_quan' => 'Shelf-life forecast',
    'ban_goc_tieng_viet' => 'original text in Vietnamese',
    'hang' => ['dac_biet' => 'Extra class', 'hang_1' => 'Class I', 'hang_2' => 'Class II', 'khong_dat' => 'Out of grade'],
    'do_chin' => ['xanh' => 'Green (mature)', 'vua_chin' => 'Half-ripe', 'chin' => 'Ripe', 'qua_chin' => 'Over-ripe'],
    // khuyet_tat.*, giai_doan.* ...
];
```

```php
<?php // lang/zh/truy_xuat.php
return [
    'tieu_de' => '批次溯源档案',
    'hang_chat_luong' => '质量等级',
    'diem_ai' => 'AI评分',
    'nha_vuon' => '种植户',
    'nhat_ky' => '批次记录',
    'xac_minh_ok' => '已验证 — 评级后数据未被修改',
    'xac_minh_loi' => '警告 — 数据与原始评级记录不一致',
    'bao_quan' => '保鲜期预测',
    'ban_goc_tieng_viet' => '越南语原文',
    'hang' => ['dac_biet' => '特级', 'hang_1' => '一级', 'hang_2' => '二级', 'khong_dat' => '等外'],
    'do_chin' => ['xanh' => '青熟', 'vua_chin' => '半熟', 'chin' => '成熟', 'qua_chin' => '过熟'],
    // khuyet_tat.*, giai_doan.* ...
];
```

  Tên hạng tiếng Anh theo thuật ngữ Codex (Extra Class / Class I / Class II). **👤 Nhờ người biết tiếng Trung soát bản zh** trước khi thi.
- [ ] **Step 4: View** — `<html lang>` lấy `app()->getLocale()` (sửa trong layout nếu trang dùng `layouts.app`, chỉ khi view đặt biến); thay chữ cứng bằng `{{ __('truy_xuat.xxx') }}`; phần chữ tự do (reasoning của AI, mô tả nhà vườn) giữ tiếng Việt + nhãn nhỏ `({{ __('truy_xuat.ban_goc_tieng_viet') }})` khi locale ≠ vi. Thêm thanh chuyển: `VI · EN · 中文` là link `?lang=…`.
- [ ] **Step 5:** `$P artisan test` → xanh (gồm test cũ `TracePageTest`). Commit.

---

### Task 2.4: Mã số vùng trồng (PUC) & chứng nhận VietGAP/GlobalGAP

**Files:**
- Create: migration `database/migrations/2026_10_01_000001_add_puc_and_certificates.php`
- Create: `app/Models/Certificate.php`
- Modify: `app/Models/Product.php` (`fillable` + `planting_area_code`), `app/Models/FarmerProfile.php` (quan hệ `certificates`)
- Modify: `app/Services/LotPublisher.php` (ghi `planting_area_code` từ `ma_vung_trong`)
- Modify: `database/seeders/MarketplaceDemoSeeder.php` (PUC + 1 VietGAP còn hạn + 1 GlobalGAP hết hạn cho demo)
- Modify: `resources/views/trace/show.blade.php` (khối "Vùng trồng & chứng nhận"), `quality/index.blade.php` (ô PUC trong panel đăng bán)
- Test: `tests/Feature/PucChungNhanTest.php`

**Interfaces:**
- Produces: cột `products.planting_area_code` (string 50, nullable); bảng `certificates(id, farmer_profile_id, loai enum[vietgap,globalgap,haccp,ocop,khac], so_hieu string 100, het_han date nullable, file_path string nullable, timestamps)`; `Certificate::conHieuLuc(): bool`.

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('planting_area_code', 50)->nullable()->after('qr_code')
                ->comment('Ma so vung trong (PUC) cua lo');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_profile_id')->constrained('farmer_profiles')->cascadeOnDelete();
            $table->enum('loai', ['vietgap', 'globalgap', 'haccp', 'ocop', 'khac']);
            $table->string('so_hieu', 100);
            $table->date('het_han')->nullable();
            $table->string('file_path')->nullable()->comment('Anh/PDF chung nhan tren disk public');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('planting_area_code'));
    }
};
```

- [ ] **Step 2: Model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = ['farmer_profile_id', 'loai', 'so_hieu', 'het_han', 'file_path'];
    protected $casts = ['het_han' => 'date'];

    public function farmerProfile(): BelongsTo
    {
        return $this->belongsTo(FarmerProfile::class);
    }

    public function conHieuLuc(): bool
    {
        return $this->het_han === null || $this->het_han->endOfDay()->isFuture();
    }
}
```

`FarmerProfile`: `public function certificates(): HasMany { return $this->hasMany(Certificate::class); }`
`Product::$fillable` thêm `'planting_area_code'`.

- [ ] **Step 3: Test (fail)**

```php
<?php
// tests/Feature/PucChungNhanTest.php
namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PucChungNhanTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_qr_hien_puc_va_chung_nhan(): void
    {
        $this->seed();
        $p = Product::certified()->whereNotNull('planting_area_code')->firstOrFail();

        $res = $this->get('/trace/'.$p->qr_code)->assertOk();
        $res->assertSee($p->planting_area_code);
        $res->assertSee('VietGAP');
    }

    public function test_chung_nhan_het_han_bi_danh_dau(): void
    {
        $this->seed();
        $c = Certificate::where('loai', 'globalgap')->firstOrFail();
        $this->assertFalse($c->conHieuLuc());

        $p = Product::certified()->where('farmer_id', $c->farmerProfile->user_id)->firstOrFail();
        $this->get('/trace/'.$p->qr_code)->assertSee('Hết hạn');
    }
}
```

- [ ] **Step 4:** Seeder: gán `planting_area_code` dạng `VN-DTOR-0xxx` cho các sản phẩm của nông dân 0 và 2; tạo cho farmer 0: `vietgap` hết hạn `now()->addYear()`, `globalgap` hết hạn `now()->subMonth()`.
  > 👤 Xác minh **định dạng mã PUC thật** (Cục BVTV cấp) và thay mẫu `VN-DTOR-…` nếu khác.
- [ ] **Step 5:** `LotPublisher::taoLo` thêm `'planting_area_code' => $tt['ma_vung_trong'] ?? null` vào `Product::create`. Panel đăng bán thêm ô `ma_vung_trong` (placeholder "VD: VN-DTOR-0123").
- [ ] **Step 6:** View trace: khối "Vùng trồng & chứng nhận" — hiện PUC; mỗi chứng nhận: tên loại (VietGAP/GlobalGAP/HACCP/OCOP), số hiệu, hạn, badge xanh "Còn hiệu lực" / đỏ "Hết hạn". Eager load `farmer.farmerProfile.certificates` trong `TraceController::show`. Thêm khoá dịch vào 3 file `truy_xuat.php` (test "đủ 3 ngôn ngữ" của Task 2.3 sẽ bắt nếu quên).
- [ ] **Step 7:** `$P artisan migrate` (máy dev) · `$P artisan test` → xanh. Commit.
- Ngoài phạm vi: upload file chứng nhận từ giao diện — chờ có đăng nhập thật (nông dân chỉ được sửa chứng nhận của mình).

---

### Task 2.5: Trang khuyến nghị giá & thời tiết (`/khuyen-nghi`)

**Vì sao:** Nằm trong GĐ1 lộ trình của cô ("Chatbot tư vấn giá/thời tiết"); menu hiện trỏ `#`. Làm dạng **bảng luật + lời giải thích**, không chatbot LLM — rẻ, giải thích được, không bịa số.

**Files:**
- Create: `app/Services/KhuyenNghiGia.php`, `app/Services/ThoiTietClient.php`, `app/Http/Controllers/KhuyenNghiController.php`, `resources/views/khuyen-nghi/index.blade.php`
- Modify: `routes/web.php`, `config/navigation.php` (2 chỗ `'href' => '#'` của `khuyennghi` → `/khuyen-nghi`), `database/seeders/MarketplaceDemoSeeder.php` (seed `market_price_reference` 30 ngày cho `xoai`, `xoai-xanh`), nút "Xem khuyến nghị giá →" trên `/danh-gia` trỏ về `/khuyen-nghi?product=<id>` sau khi đăng bán.
- Test: `tests/Unit/KhuyenNghiGiaTest.php`, `tests/Feature/KhuyenNghiPageTest.php`

**Interfaces:**
- `ThoiTietClient::duBao3Ngay(string $region = 'Cao Lãnh'): array<array{date:string, temperature:float, humidity:float, rain_forecast:string}>` — gọi Open-Meteo (miễn phí, không cần key) `https://api.open-meteo.com/v1/forecast?latitude=10.46&longitude=105.63&daily=temperature_2m_max,precipitation_probability_max&timezone=Asia/Ho_Chi_Minh`, lưu vào `weather_data_cache` theo `(region, date)`; lỗi mạng → trả dữ liệu cache gần nhất, không ném lỗi.
- `KhuyenNghiGia::tinh(Product $p): array{hanh_dong: 'ban_ngay'|'cho_them'|'giam_gia', gia_goi_y: int, ly_do: string[]}` — luật:
  1. `qua_chin` hoặc điểm < 50 → `giam_gia`, giá = TB thị trường 7 ngày × 0,85.
  2. Xác suất mưa ≥ 70% trong 2 ngày tới **và** `chin` → `ban_ngay` ("mưa làm khó thu hoạch/vận chuyển, xoài chín không chờ được").
  3. Giá TB 7 ngày tăng ≥ 5% so với 7 ngày trước **và** `xanh|vua_chin` → `cho_them`.
  4. Còn lại → `ban_ngay`, giá = TB 7 ngày × hệ số hạng (`dac_biet` 1,15 · `hang_1` 1,0 · `hang_2` 0,85).
  Lưu mỗi lần tính vào `price_recommendations` (có `weather_ref_id`, `market_price_ref_id`).
- Test Unit: 4 luật trên, mỗi luật 1 test, dùng `Http::fake()` cho Open-Meteo và dữ liệu giá tự tạo.
- Test Feature: `/khuyen-nghi` 200, liệt kê lô của nông dân demo; `/khuyen-nghi?product=<id>` hiện hành động + lý do; `Http::fake()` trả 500 → trang vẫn 200 với dòng "Chưa lấy được thời tiết".

> Task này lớn hơn các task khác — trước khi làm, nhờ Claude **viết plan chi tiết riêng**
> (`docs/superpowers/plans/…-khuyen-nghi.md`) theo đúng interface trên.

---

## Giai đoạn 3 — Thương mại hoá

### Task 3.1: RFQ — Yêu cầu mẫu thử / Báo giá / Giữ lô trên trang QR

**Vì sao:** Trả lời câu hỏi khó nhất của góp ý: *"Làm sao giao dịch không nhảy sang Zalo?"* →
(1) trang QR **không lộ SĐT nhà vườn**, mọi liên hệ đi qua form; (2) mỗi yêu cầu có mã, trạng thái;
(3) phí kết nối chỉ thu khi yêu cầu chuyển `chot` — nhà vườn có lợi khi để giao dịch trên nền tảng vì
lô được **giữ uy tín hồ sơ AI + lịch sử giao dịch** (người mua sau nhìn thấy "đã chốt 12 đơn").

**Files:**
- Create: migration `…_create_lot_requests_table.php`, `app/Models/LotRequest.php`, `app/Http/Controllers/LotRequestController.php`
- Modify: `routes/web.php`, `resources/views/trace/show.blade.php` (3 nút + form), `lang/*/truy_xuat.php`
- Test: `tests/Feature/YeuCauLoTest.php`

**Interfaces:**
- Bảng `lot_requests(id, product_id FK cascade, ma string 20 unique, loai enum[gui_mau,bao_gia,giu_lo], ten_nguoi_mua string 120, cong_ty string 150 null, so_dien_thoai string 15, so_luong_kg decimal(10,2) null, gia_de_xuat decimal(12,2) null, ghi_chu text null, trang_thai enum[moi,da_lien_he,chot,huy] default moi, timestamps)`.
- Route `POST /trace/{product:qr_code}/yeu-cau` name `trace.request`, throttle `5,1` → redirect back + flash `Đã gửi yêu cầu {ma}. AgriSenAI sẽ liên hệ trong 24 giờ.`; tạo 1 dòng `notifications` (`type = 'rfq'`) cho nông dân.
- `ma` = `RFQ-` + 6 ký tự in hoa ngẫu nhiên.
- Validate: `so_dien_thoai` regex `/^(0|\+84)(3|5|7|8|9)\d{8}$/`; `gia_de_xuat` bắt buộc khi `loai=bao_gia`; `so_luong_kg` bắt buộc khi `loai=giu_lo` và ≤ `quantity_available`.

Test phải có (viết đầy đủ khi thực hiện, cùng style các test trên):
1. `test_gui_bao_gia_tao_yeu_cau_va_thong_bao` — 1 dòng `lot_requests` `trang_thai=moi` + 1 `notifications` cho `farmer_id`.
2. `test_bao_gia_thieu_gia_bi_tu_choi` — lỗi `gia_de_xuat`.
3. `test_giu_lo_vuot_so_luong_bi_tu_choi`.
4. `test_so_dien_thoai_sai_bi_tu_choi` (`12345`).
5. `test_trang_qr_khong_lo_so_dien_thoai_nha_vuon` — `assertDontSee($farmer->phone)` (kiểm tra tên cột SĐT thật trong bảng `users` trước).
6. `test_gui_qua_nhieu_bi_throttle` — lần 6 trong 1 phút → 429.

Ngoài phạm vi: trang quản lý yêu cầu cho nhà vườn, thanh toán đặt cọc thật (cần đăng nhập + cổng thanh toán). Trên slide chỉ nêu "đặt cọc qua ví trung gian — giai đoạn sau".

---

### Task 3.2: Audit log chống gian lận (chuỗi hash + HMAC)

**Vì sao:** Đã có hash cho **một** kết quả chấm. Góp ý muốn **mọi thay đổi hồ sơ lô** (giá, PUC, số lượng, chứng nhận) đều có lịch sử không sửa/xoá được.

**Files:**
- Create: migration `…_create_audit_logs_table.php`, `app/Models/AuditLog.php`, `app/Services/AuditChain.php`, `app/Observers/AuditObserver.php`, `app/Console/Commands/VerifyAuditChain.php`
- Modify: `app/Providers/AppServiceProvider.php` (đăng ký observer cho `Product`, `Certificate`, `AiQualityAssessment`), `config/agrisenai.php` (`audit_key => env('AGRISEN_AUDIT_KEY')`), `.env.example`, `resources/views/trace/show.blade.php` (khối "Lịch sử thay đổi")
- Test: `tests/Feature/AuditChainTest.php`

**Interfaces:**
- Bảng `audit_logs(id, doi_tuong string 50, doi_tuong_id bigint, hanh_dong enum[tao,sua,xoa], truoc json null, sau json null, nguoi string 120 null, prev_hash char(64), hash char(64), created_at)` — **không** có `updated_at`; model chặn `update`/`delete` (ném `LogicException`).
- `hash = hash_hmac('sha256', prev_hash.'|'.json_encode([doi_tuong, doi_tuong_id, hanh_dong, truoc, sau, nguoi, created_at]), config('agrisenai.audit_key'))`; bản ghi đầu tiên `prev_hash = str_repeat('0', 64)`.
- `AuditChain::ghi(Model $m, string $hanhDong, ?array $truoc, ?array $sau): AuditLog` (khoá bằng `DB::transaction` + `lockForUpdate` trên bản ghi cuối để chuỗi không rẽ nhánh).
- `AuditChain::kiemTra(): array{ok: bool, loi_tai_id: ?int}`; lệnh `php artisan audit:verify`.
- Trang QR: "Lịch sử thay đổi (n)" liệt kê thời điểm + trường đổi (giá cũ → mới); dải cảnh báo nếu `kiemTra()` lỗi ở bản ghi thuộc lô này.

Test phải có:
1. `test_sua_gia_sinh_audit_log_co_truoc_sau`.
2. `test_chuoi_hop_le_sau_nhieu_thay_doi` — `kiemTra()['ok'] === true`.
3. `test_sua_tay_db_bi_phat_hien` — `DB::table('audit_logs')->where('id', 2)->update(['sau' => '{"price":1}'])` → `ok=false`, `loi_tai_id=2`.
4. `test_model_khong_cho_update_delete` — `expectException(LogicException::class)`.
5. `test_thieu_audit_key_thi_bao_loi_ro_rang` — key rỗng → exception có chữ `AGRISEN_AUDIT_KEY`.

> Ghi chú bảo mật cho thuyết minh: HMAC chống người ngoài sửa DB; **không** chống được người giữ khoá.
> Hướng mở rộng: định kỳ công bố `hash` cuối chuỗi (VD: đăng lên trang công khai mỗi ngày) để bên thứ ba đối chiếu.

---

### Task 3.3: Offline hoàn toàn — chụp khi mất sóng, tự gửi khi có mạng

**Files:**
- Create: `public/js/hang-doi-offline.mjs`, `tests/js/hang-doi-offline.test.mjs`
- Modify: `resources/views/quality/index.blade.php` (khi `navigator.onLine === false` hoặc `fetch` lỗi mạng → lưu vào hàng đợi; hiện danh sách "Đang chờ gửi (n)"), `public/sw.js` (precache `/danh-gia` + 2 file `.mjs`; tăng tên cache version)

**Interfaces:**
- `taoHangDoi(kho)` — `kho` là adapter `{ them(item), tatCa(), xoa(id) }`; có 2 cài đặt: `khoIndexedDb('agrisen', 'hang-doi')` (trình duyệt) và `khoBoNho()` (test).
- Item: `{ id, taoLuc, anh: Blob, loai, muc_dich_ban, so_lieu: {...} }`.
- `hangDoi.guiHet(guiMot: (item) => Promise<Response>)` — gửi **tuần tự**; 2xx/422 → xoá khỏi hàng đợi (422 = ảnh hỏng, không thử lại mãi); lỗi mạng/5xx → giữ lại, dừng vòng; trả `{ daGui, conLai }`.
- Kích hoạt `guiHet` khi: tải trang, sự kiện `online`, và nút "Gửi ngay".

Test JS phải có (với `khoBoNho`): thêm 2 → `tatCa().length === 2`; `guiHet` với fake 200 → rỗng; fake ném `TypeError` (mất mạng) ở item 1 → còn 2, `daGui=0`; fake 422 → item bị xoá; thứ tự gửi đúng FIFO.

Kiểm tra tay: DevTools → Network → Offline → chụp 2 ảnh → thấy "Đang chờ gửi (2)" → bật mạng → tự chấm, hiện 2 kết quả.

> Giới hạn cần nói thẳng khi thi: chấm AI **vẫn cần mạng** (Gemini chạy trên cloud). Offline = không mất dữ liệu,
> không phải chấm offline. Chấm offline thật cần model nhỏ chạy trên máy — hướng nghiên cứu sau.

---

## 4. Backlog (chưa xếp lịch)

- Dọn `storage/app/private/pending/*.jpg` quá 1 ngày (phiên bỏ dở) — lệnh `schedule` hằng ngày.
- Xoá `HomeController::generateProducts()` / `getCategories()` / `getRegion()` / `getOcop()` (code chết, còn dữ liệu quýt/bưởi) và `resources/views/home.blade.php` (không route nào dùng) — hỏi nhóm trước khi xoá.
- Đăng nhập thật cho nông dân / người mua → thay `nong_dan_demo`, mở upload chứng nhận, trang quản lý RFQ.
- Phân loại **kích cỡ** xoài (chỉ số thứ 2 cô gợi ý): cần vật tham chiếu kích thước trong ảnh (VD: tờ A4 / đồng xu) — nghiên cứu sau khi Task 1.7 có số liệu.
- `defect_note` chưa nằm trong `computeHash` — sau khi có Audit Log (3.2), mọi sửa `defect_note` đã bị ghi vết.

## 5. Việc ngoài code 👤

| Việc | Người hỗ trợ | Đầu vào từ kế hoạch này |
|---|---|---|
| Câu chuyện "nỗi đau HTX & người mua B2B" cho thuyết trình | Cô Trâm | Số liệu Task 1.7; ảnh tem QR thật trên thùng |
| Chứng minh "bán cao hơn 10–15% nhờ hồ sơ minh bạch" | Cô Hương | Phỏng vấn chủ vựa (Task 1.7 step 6); so giá lô có/không có hồ sơ |
| Mô hình phí: gói phần mềm 1,5–3,5 tr/tháng + phí kết nối | Cô Hương | Cơ chế RFQ (Task 3.1) làm bằng chứng thu phí được |
| Duyệt nhãn vàng AI | Chủ vựa / khuyến nông | Task 1.5 step 4 |
| Đối chiếu ngưỡng 1/10, 1/4 với bản gốc TCVN 9766:2013 | Nhóm | `config/agrisenai.php` → `grades` |
| Soát bản dịch tiếng Trung | Người biết tiếng Trung | Task 2.3 |
| Cập nhật thuyết minh `.docx`: bỏ cam/quýt, thêm mục "Hạn chế đã biết" | Nhóm | `docs/files-ke-hoach/quy-trinh-xay-module-ai.md` mục 12 |

## 6. Theo dõi tiến độ

| Task | Trạng thái | Ghi chú |
|---|---|---|
| 0.1 Chốt phạm vi xoài | ⬜ | |
| 1.1 Phiên chấm | ⬜ | |
| 1.2 LotPublisher | ⬜ | |
| 1.3 UI đăng bán | ⬜ | |
| 1.4 Tem QR | ⬜ | |
| 1.5 Ảnh xoài hỏng 👤 | ⬜ | |
| 1.6 Quy trình chụp | ⬜ | |
| 1.7 Thử nghiệm vựa 👤 | ⬜ | |
| 2.1 Kiểm tra ảnh trên máy | ⬜ | |
| 2.2 Dự báo bảo quản | ⬜ | |
| 2.3 QR đa ngôn ngữ | ⬜ | |
| 2.4 PUC & chứng nhận | ⬜ | |
| 2.5 Khuyến nghị giá | ⬜ | cần plan riêng |
| 3.1 RFQ | ⬜ | |
| 3.2 Audit log | ⬜ | |
| 3.3 Offline sync | ⬜ | |
