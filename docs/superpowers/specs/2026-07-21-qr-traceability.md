# SPEC v2 — "Nhật ký chất lượng qua QR" (Digital Product Passport)

> **Thay thế hoàn toàn SPEC-qr-traceability (v1).** v1 viết khi chưa có schema nên đề xuất bảng `batches` — **BỎ HẲN**, không tạo bảng đó.
> File dành cho **Claude Code**, bám sát 15 bảng trong `AgriSenAI.sql` (Laravel 13 + MySQL 8).
> Dùng chung hạ tầng với `SPEC-san-thuong-mai.md`.

---

## 0. Thay đổi so với v1 (đọc trước)

| v1 (SAI — bỏ) | v2 (ĐÚNG — theo schema thật) |
|---|---|
| Tạo bảng `batches` | KHÔNG tạo. Dùng `products` + `ai_quality_assessments` |
| `batch_code` ULID | Dùng cột có sẵn **`products.qr_code`** |
| Đơn vị chứng nhận = "lô hàng" | Đơn vị = **product** |
| Snapshot ảnh/điểm/nhà vườn trong 1 bảng | Ảnh AI ở `ai_quality_assessments.image_url`; nhà vườn ở `farmer_profiles`; nhật ký ở `traceability_logs` |
| Route `/trace/{batch_code}` | Route `/trace/{qr_code}` |

⚠️ Trong file SQL, comment "Mã QR truy xuất nguồn gốc" bị đặt nhầm ở khối `farmer_profiles`, nhưng cột `qr_code` thực tế thuộc bảng **`products`**. QR gắn với product.

---

## 1. Mục tiêu

Sau khi AI chấm điểm xong một sản phẩm:
1. Sinh **`products.qr_code` duy nhất** (nếu chưa có).
2. Sinh mã QR trỏ tới URL công khai `/trace/{qr_code}`.
3. Giám khảo đóng vai người mua quét QR → mở trang public hiển thị **nhật ký**: ảnh gốc AI đã quét, điểm chất lượng AI chứng nhận, thông tin nhà vườn, và timeline truy xuất.

**Điểm ăn giải:** biến "AI tự chấm" thành bằng chứng công khai, xác minh được, không sửa được sau khi tạo.

---

## 2. Nguyên tắc kiến trúc (KHÔNG vi phạm)

1. **QR chỉ chứa URL**, không nhúng ảnh/điểm: `{APP_URL}/trace/{qr_code}`.
2. **`qr_code` phải unguessable** — KHÔNG dùng `products.id`. Dùng ULID (26 ký tự) hoặc `Str::random(12)`, đảm bảo UNIQUE (schema đã có ràng buộc UNIQUE).
3. **Bản ghi đánh giá là append-only.** Chấm lại → INSERT bản ghi `ai_quality_assessments` MỚI, không UPDATE bản cũ. Trang tra cứu hiển thị bản mới nhất theo `assessed_at`, nhưng vẫn giữ lịch sử.
4. **Trang tra cứu PUBLIC, read-only**, không auth, không nhận input ngoài `qr_code`.
5. **Không có route nào sửa/xoá `ai_quality_assessments`.**

---

## 3. Migration bổ sung (chỉ 2 cột — không tạo bảng mới)

Để có tính "xác minh toàn vẹn" (điểm mạnh nhất khi demo), thêm 2 cột vào `ai_quality_assessments`:

```php
Schema::table('ai_quality_assessments', function (Blueprint $table) {
    $table->string('image_sha256', 64)->nullable()->after('image_url');
    $table->string('integrity_hash', 64)->nullable()->after('raw_ai_response');
});
```

- `image_sha256` = SHA-256 **nội dung file ảnh** → phát hiện tráo ảnh.
- `integrity_hash` = SHA-256 của chuỗi canonical (mục 5) → phát hiện sửa điểm/nhà vườn.

> Nullable để tương thích dữ liệu cũ. Bản ghi cũ không có hash → hiển thị "Chưa xác minh" thay vì "Không khớp".

---

## 4. Models liên quan

```php
// AiQualityAssessment.php → 'ai_quality_assessments'
class AiQualityAssessment extends Model {
    public $timestamps = false;            // bảng chỉ có assessed_at
    protected $fillable = [
        'product_id','image_url','image_sha256','ripeness_level','quality_score',
        'defect_detected','defect_note','raw_ai_response','integrity_hash','assessed_at'
    ];
    protected $casts = [
        'raw_ai_response' => 'array',
        'quality_score'   => 'decimal:2',
        'defect_detected' => 'boolean',
        'assessed_at'     => 'datetime',
    ];
    public function product() { return $this->belongsTo(Product::class); }
}
```

```php
// Product.php — bổ sung (đã mô tả đầy đủ ở SPEC-san-thuong-mai mục 2)
public function latestAssessment() {
    return $this->hasOne(AiQualityAssessment::class)->latestOfMany('assessed_at');
}
public function assessments() { return $this->hasMany(AiQualityAssessment::class); }
public function traceLogs()   { return $this->hasMany(TraceabilityLog::class)->orderBy('happened_at'); }
public function getRouteKeyName(): string { return 'qr_code'; }   // dùng cho route /trace/{product}
```

> ⚠️ `getRouteKeyName() = 'qr_code'` sẽ ảnh hưởng MỌI route binding `{product}` (kể cả trang sàn). Nếu trang sàn muốn bind theo `id`, ĐỪNG override toàn cục — thay vào đó khai báo binding riêng cho route trace:
> ```php
> Route::get('/trace/{product:qr_code}', [TraceController::class,'show'])->name('trace.show');
> ```
> **Chọn cách này** (binding theo scope route), an toàn hơn.

---

## 5. Integrity hash — chuỗi canonical

Tính **tại thời điểm INSERT** bản ghi assessment, theo đúng thứ tự này (để tái tạo được):

```php
public function computeHash(AiQualityAssessment $a): string
{
    $p = $a->product()->first();
    $canonical = implode('|', [
        $p->qr_code,
        (string) $a->product_id,
        (string) $a->quality_score,
        (string) $a->ripeness_level,
        $a->defect_detected ? '1' : '0',
        (string) $a->image_sha256,
        (string) $p->farmer_id,
        $a->assessed_at->toIso8601String(),
    ]);
    return hash('sha256', $canonical);
}
```

`image_sha256 = hash_file('sha256', Storage::disk('public')->path($relativeImagePath));`

---

## 6. Service tạo bản ghi + QR

`app/Services/TraceabilityService.php`:

```php
class TraceabilityService
{
    /** Gọi NGAY SAU khi QualityAssessmentService chấm xong */
    public function recordAssessment(Product $product, array $aiResult, string $imagePath): AiQualityAssessment
    {
        return DB::transaction(function () use ($product, $aiResult, $imagePath) {
            // 1. Đảm bảo product có qr_code
            if (empty($product->qr_code)) {
                $product->qr_code = (string) Str::ulid();
                $product->save();
            }

            // 2. Tạo bản ghi assessment (append-only)
            $a = new AiQualityAssessment();
            $a->product_id      = $product->id;
            $a->image_url       = $imagePath;
            $a->image_sha256    = hash_file('sha256', Storage::disk('public')->path($imagePath));
            $a->ripeness_level  = $aiResult['ripeness_level'];      // xanh|vua_chin|chin|qua_chin
            $a->quality_score   = $aiResult['quality_score'];       // 0-100
            $a->defect_detected = $aiResult['defect_detected'] ?? false;
            $a->defect_note     = $aiResult['defect_note'] ?? null;
            $a->raw_ai_response = $aiResult['raw'] ?? $aiResult;    // full JSON Gemini
            $a->assessed_at     = now();
            $a->save();                                             // save lần 1 để có assessed_at chuẩn

            // 3. Hash toàn vẹn
            $a->integrity_hash = $this->computeHash($a);
            $a->save();

            // 4. Ghi 1 mốc traceability
            TraceabilityLog::create([
                'product_id'  => $product->id,
                'stage'       => 'danh_gia_ai',
                'description' => "AI chấm chất lượng: {$a->quality_score}/100",
                'location'    => $product->farmer?->farmerProfile?->region,
                'happened_at' => now(),
            ]);

            return $a;
        });
    }

    public function computeHash(AiQualityAssessment $a): string { /* mục 5 */ }

    public function verify(AiQualityAssessment $a): array
    {
        if (!$a->integrity_hash) return ['status' => 'unverified'];

        $hashOk  = hash_equals($a->integrity_hash, $this->computeHash($a));
        $path    = Storage::disk('public')->path($a->image_url);
        $imageOk = is_file($path) && $a->image_sha256
                   && hash_equals($a->image_sha256, hash_file('sha256', $path));

        return [
            'status'    => ($hashOk && $imageOk) ? 'verified' : 'mismatch',
            'hash_ok'   => $hashOk,
            'image_ok'  => $imageOk,
        ];
    }
}
```

---

## 7. Sinh mã QR

```
composer require simplesoftwareio/simple-qrcode
```

Dùng **format SVG** (không cần GD/Imagick — an toàn khi demo):

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

$svg = QrCode::format('svg')->size(300)->margin(1)
        ->generate(route('trace.show', $product->qr_code));
```

- **Không lưu file QR ra disk** — sinh on-the-fly qua route.
- QR phải chứa **URL tuyệt đối** (`route()` sinh sẵn absolute) — nếu là localhost, điện thoại giám khảo sẽ không mở được (xem mục 11).

---

## 8. Routes

```php
// routes/web.php — PUBLIC, không middleware auth
Route::get('/trace/{product:qr_code}', [TraceController::class, 'show'])
     ->name('trace.show')->middleware('throttle:60,1');

Route::get('/trace/{product:qr_code}/qr', [TraceController::class, 'qr'])
     ->name('trace.qr')->middleware('throttle:60,1');
```

KHÔNG có route POST/PUT/DELETE nào cho assessment (đảm bảo append-only ở tầng HTTP).

---

## 9. TraceController (read-only)

```php
class TraceController extends Controller
{
    public function __construct(private TraceabilityService $svc) {}

    public function show(Product $product)
    {
        $product->load([
            'farmer.farmerProfile',
            'category',
            'primaryImage',
            'traceLogs',
        ]);

        $assessment = $product->assessments()->latest('assessed_at')->first();
        abort_if(!$assessment, 404, 'Sản phẩm chưa được AI đánh giá');

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

---

## 10. View `trace/show.blade.php` — màn hình giám khảo thấy khi quét

**Mobile-first bắt buộc** (họ cầm điện thoại). Style clean/minimal e-commerce, nhất quán `agrisenai-home.html`. KHÔNG 3D/Duolingo.

Thứ tự dọc, theo mức độ thuyết phục giảm dần:

1. **Header:** tên sản phẩm + `category.name` + badge điểm lớn (`quality_score`/100).
2. **Dải xác minh** (chi tiết ăn điểm nhất) — theo `$verification['status']`:
   - `verified` → dải xanh: "✓ Đã xác minh — dữ liệu chưa bị chỉnh sửa"
   - `mismatch` → dải đỏ: "⚠ Dữ liệu không khớp mã băm gốc"
   - `unverified` → dải xám: "Bản ghi cũ, chưa có mã băm"
3. **Ảnh gốc AI đã quét:** `Storage::url($assessment->image_url)` — nhấn mạnh đây là ảnh AI chấm, không phải ảnh marketing.
4. **Kết quả AI:**
   - Điểm `quality_score`/100
   - Độ chín `ripeness_level` → nhãn VN: xanh / vừa chín / chín / quá chín
   - Nếu `defect_detected` → hộp cảnh báo vàng + `defect_note`
   - Thời điểm `assessed_at` (định dạng d/m/Y H:i)
   - Ghi nguồn: "Đánh giá tự động bởi Gemini Vision"
5. **Nhà vườn:** `farmer.farmerProfile.farm_name`, `region`, `ocop_star_rating` (⭐×n) nếu có, `description`. SĐT ẩn giữa số (`0901***567`).
6. **Nhật ký truy xuất:** timeline dọc từ `traceLogs` (stage → nhãn VN: thu_hoach=Thu hoạch, dong_goi=Đóng gói, van_chuyen=Vận chuyển, danh_gia_ai=Đánh giá AI, dang_ban=Đang bán) + `description`, `location`, `happened_at`.
7. **Chân trang — bằng chứng kỹ thuật:** `qr_code`, `integrity_hash` rút gọn (`substr(...,0,16)…` + nút xem đầy đủ), kèm dòng: "Mã băm SHA-256 — bất kỳ ai cũng kiểm chứng bản ghi này không bị sửa sau khi tạo."

Màu badge theo điểm: ≥90 xanh lá đậm; 75–89 xanh dương; <75 cam.

---

## 11. Nối vào luồng chấm điểm hiện có

**KHÔNG sửa logic `QualityAssessmentService`.** Chỉ thêm 1 dòng sau khi có kết quả:

```php
$result     = $qualityAssessmentService->assess($imagePath, $productType);
$assessment = $traceabilityService->recordAssessment($product, $result, $imagePath);

return view('assessment.result', compact('product', 'assessment'));
```

Trên trang kết quả (phía nhà vườn), thêm block:
- QR inline: `<img src="{{ route('trace.qr', $product->qr_code) }}" alt="QR truy xuất">`
- Nút "Mở trang tra cứu" → `route('trace.show', $product->qr_code)`
- Text: "Quét mã này để xem nhật ký chất lượng của sản phẩm."

---

## 12. Checklist nghiệm thu

- [ ] Chấm điểm xong → product tự có `qr_code` unique, tạo bản ghi `ai_quality_assessments` mới.
- [ ] Chấm lại lần 2 → INSERT bản ghi mới, bản cũ còn nguyên; trang trace hiện bản mới nhất.
- [ ] QR quét bằng điện thoại thật mở đúng `/trace/{qr_code}`.
- [ ] `/trace/{qr_code}` mở được KHÔNG cần đăng nhập.
- [ ] Hiển thị đủ: ảnh gốc, điểm + độ chín + cảnh báo lỗi, nhà vườn + OCOP, timeline traceability.
- [ ] Dải "Đã xác minh" hiện đúng.
- [ ] **Demo test:** sửa tay `quality_score` trong DB → reload → dải chuyển sang "Không khớp". (Đây là màn demo mạnh nhất trước giám khảo.)
- [ ] `qr_code` sai/không tồn tại → 404 sạch, không lộ lỗi SQL.
- [ ] Mobile-first: đọc tốt trên màn hình điện thoại.

---

## 13. Thứ tự implement

1. Migration bổ sung 2 cột (mục 3).
2. Model `AiQualityAssessment` + quan hệ Product (mục 4) — nhớ `$timestamps = false`.
3. `composer require simplesoftwareio/simple-qrcode`.
4. `TraceabilityService` (mục 5, 6).
5. Routes + `TraceController` (mục 8, 9).
6. View `trace/show.blade.php` mobile-first (mục 10).
7. Nối vào luồng chấm điểm + block QR trên trang kết quả (mục 11).
8. Chạy checklist mục 12, đặc biệt test "sửa DB → dải đổi màu".

---

## 14. Lưu ý demo thực địa (dễ hỏng nhất)

- **`APP_URL` phải là public URL TRƯỚC KHI sinh QR.** Chạy localhost thì điện thoại giám khảo không mở được. Dùng ngrok / Cloudflare Tunnel / host thật, set `APP_URL` trong `.env`, rồi `php artisan config:clear`.
- `php artisan storage:link` — nếu quên, ảnh gốc không hiện.
- Wifi hội trường có thể chặn: chuẩn bị sẵn phương án 4G hoặc screenshot dự phòng.
- Seed sẵn vài product đã có assessment + qr_code để quét thử ngay, không phụ thuộc việc chấm live.
