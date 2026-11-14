# Góp ý thầy Nguyên (29–50) — phần code — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Đóng các đầu việc trong góp ý thầy Nguyên mà app có thể làm bằng chứng: rubric "dập", câu giới hạn AI thống nhất, ghi thiết bị chụp, đính chính hồ sơ đã công bố, KPI có mẫu số, benchmark AI trung thực (TP/FP/FN/TN, P/R/F1, cảnh báo nhãn chưa duyệt).

**Architecture:** Mọi thay đổi trạng thái vẫn đi qua `App\Services\LotWorkflow` và ghi audit qua `AuditChain::append` (chỉ thêm, không sửa). Đính chính = dòng audit `corrected` trên lô cũ + lô mới có dòng audit `corrects`; không thêm cột DB. Số liệu benchmark tính bằng một class thuần để test không cần mạng.

**Tech Stack:** Laravel 13 / PHP 8.3, Blade + Tailwind v4, PHPUnit (SQLite `:memory:`).

**Spec:** `docs/Giao-Vien/Nguyen_GopY_ AgriSen_Word_Final.docx` (mục 29–34, 43, 47, 49).

## Global Constraints

- PHP: `E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` (không có trên PATH).
- Test: luôn `php artisan config:clear && php artisan test` (config cache làm test 419). Chạy lại `php artisan config:cache` sau nếu Laragon cần.
- `config/agrisenai.php` là nguồn sự thật duy nhất cho tiêu chí/khuyết tật/câu chữ dùng chung. Không hardcode trong view.
- Không sửa/xóa dòng audit; chỉ thêm dòng bù.
- Không có điểm tổng, hạng, xếp hạng (test `test_schema_va_trang_khong_con_diem_hay_hang` phải còn xanh).
- UI tiếng Việt; comment + tên test tiếng Việt không dấu; route URL tiếng Việt.
- Không gọi mạng trong test (`Http::fake`, `Storage::fake('public')`, ảnh thật `LotFlowTest::realPhoto()`).
- Không công bố số % khi mẫu số = 0 hoặc khi nhãn chưa có người duyệt (yêu cầu thầy mục 31, 43, 47).
- `vendor/bin/pint` trước mỗi commit. Commit thẳng `main`, nhớ push.

## Ngoài phạm vi (không phải code / để P3)

Rubric ảnh ví dụ/phản ví dụ, SOP chụp, phiếu phỏng vấn, WTP, LOI, unit economics Excel, TAM/SAM/SOM, sơ đồ B2B, bảng "AI làm được/không làm được", số liệu 4tr/6tr — là tài liệu Word/Excel/PPT.
Mục 34 (dự phòng khi API lỗi) **đã có**: `AnalyzeLotTest::test_gemini_loi_van_ghi_lai_va_cho_nhap_tay` + quyết định `manual`. Không làm thêm.
Đếm lượt quét QR (KPI "lượt mở hồ sơ") cần bảng mới → để sau Bán kết.
Tách tập kiểm thử theo lô/vườn → P3.

## Review Focus

1. Đính chính một lô **hai lần** → phải bị từ chối (422), không sinh hai lô thay thế.
2. Trang `/ho-so/{cũ}` sau đính chính vẫn phải **xanh "nguyên vẹn"** (dòng `corrected` nằm sau `published` không được làm lệch `verify()`).
3. Lô thay thế còn nháp → trang cũ **không được** link tới `/ho-so/{mới}` (sẽ 404), chỉ ghi "đang lập".
4. Nhân viên tổ chức khác gửi POST đính chính → 403.
5. Stats khi chưa có ảnh/lô nào → hiện "chưa có dữ liệu", không chia cho 0.

---

### Task 1: Rubric "Thâm / dập" + câu giới hạn AI dùng chung (mục 29, 32)

**Files:**
- Modify: `config/agrisenai.php` (`defect_types.tham`, thêm key `ai_disclaimer`)
- Modify: `resources/views/public/show.blade.php:77`
- Modify: `resources/views/lots/show.blade.php` (thẻ bước 4, ngay dưới tiêu đề xác nhận)
- Test: `tests/Feature/LotFlowTest.php`

**Interfaces:**
- Produces: `config('agrisenai.ai_disclaimer')` — string.

Prompt chỉ dùng *key* của `defect_types` (`FewShotPromptBuilder.php:26,181`), nên đổi nhãn không đổi prompt → **không** bump `prompt_version`.

- [ ] **Step 1: Viết test hỏng**

```php
public function test_cau_gioi_han_ai_lay_tu_config_tren_trang_cong_khai(): void
{
    config(['agrisenai.ai_disclaimer' => 'CAU GIOI HAN THU']);
    $lot = $this->publishedLot();
    $this->get("/ho-so/{$lot->code}")->assertSee('CAU GIOI HAN THU');
    $this->actingAs($this->staff)->get("/lo/{$lot->code}")->assertSee('CAU GIOI HAN THU');
}

public function test_nhan_khuyet_tat_tham_gom_ca_dap(): void
{
    $this->assertSame('Thâm / dập', config('agrisenai.defect_types.tham'));
}
```

- [ ] **Step 2: Chạy, xác nhận FAIL**
Run: `php artisan config:clear && php artisan test --filter="cau_gioi_han|tham_gom"` → FAIL.

- [ ] **Step 3: Sửa config + view**
`'tham' => 'Thâm / dập'`. Thêm:
```php
// Mot cau duy nhat dung cho trang QR, man hinh lo, PPT, video (gop y thay Nguyen muc 32).
'ai_disclaimer' => 'AI chỉ gợi ý dấu hiệu ngoại quan nhìn thấy qua ảnh; người phụ trách xác nhận từng tiêu chí. Hồ sơ không phải chứng nhận chất lượng, không đo độ Brix hay an toàn thực phẩm.',
```
Thay đoạn text cứng ở `public/show.blade.php:77` bằng `{{ config('agrisenai.ai_disclaimer') }}`; thêm cùng câu (chữ nhỏ `text-[12px] text-ink-sec`) vào thẻ bước 4 của `lots/show.blade.php` (hiện cả khi đã công bố).

- [ ] **Step 4: Chạy toàn bộ test → PASS**
- [ ] **Step 5: Commit** `Gop y thay Nguyen 29/32: nhan Tham/dap, cau gioi han AI lay tu config`

---

### Task 2: Ghi thiết bị chụp vào nhật ký ảnh (mục 30)

**Files:**
- Modify: `app/Services/LotWorkflow.php` (`addPhoto`)
- Modify: `app/Http/Controllers/LotController.php:87`
- Test: `tests/Feature/LotFlowTest.php`

**Interfaces:**
- Produces: `LotWorkflow::addPhoto(Lot $lot, string $srcPath, string $view, User $by, ?string $device = null): LotPhoto` — payload audit `photo_added` có thêm key `device` (string|null, tối đa 200 ký tự).

Seeder gọi không có `$device` → null, không phải sửa.

- [ ] **Step 1: Test hỏng**

```php
public function test_audit_anh_ghi_thiet_bi_chup(): void
{
    $lot = $this->createLot();
    $this->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14; SM-A155F)')
        ->post("/lo/{$lot->code}/anh", ['anh' => self::realPhoto(), 'view' => 'toan_qua']);
    $log = $lot->auditLogs()->where('action', 'photo_added')->first();
    $this->assertStringContainsString('SM-A155F', $log->data()['device']);
}
```

- [ ] **Step 2: FAIL** — `--filter=thiet_bi_chup`.
- [ ] **Step 3:** Thêm tham số, ghi `'device' => $device === null ? null : Str::limit($device, 200, '')` vào payload; controller truyền `$request->userAgent()`.
- [ ] **Step 4: Toàn bộ test PASS** (chuỗi băm demo vẫn xanh vì payload mới chỉ áp cho dòng mới).
- [ ] **Step 5: Commit** `Gop y 30: ghi thiet bi chup vao nhat ky anh`

---

### Task 3: Đính chính hồ sơ đã công bố (mục 33, 49)

**Files:**
- Modify: `app/Services/LotWorkflow.php` (thêm `correct`)
- Modify: `app/Models/Lot.php` (thêm `correction()`)
- Modify: `app/Http/Controllers/LotController.php` (thêm `correct`)
- Modify: `app/Http/Controllers/PublicLotController.php` (`show` truyền `correction`)
- Modify: `routes/web.php` (trong group `auth`)
- Modify: `resources/views/lots/show.blade.php` (thẻ bước 5, nhánh `isPublished()`)
- Modify: `resources/views/public/show.blade.php` (băng vàng ngay dưới băng xanh/đỏ)
- Test: `tests/Feature/LotFlowTest.php`

**Interfaces:**
- Produces:
  - `LotWorkflow::correct(Lot $old, string $reason, User $by): Lot` — trả lô mới (status `draft`).
  - `Lot::correction(): ?array` — `['reason' => string, 'by' => string, 'new_code' => string, 'at' => Carbon]` lấy từ dòng audit `corrected` cuối, null nếu chưa đính chính.
  - Route `POST /lo/{lot}/dinh-chinh` name `lots.correct`, field `ly_do` (required, string, max 500).

Hành vi của `correct` (một transaction):
1. Nếu `! $old->isPublished()` → `ValidationException` key `lot`, "Chỉ đính chính lô đã công bố.".
2. Nếu `$old->correction() !== null` → `ValidationException` key `lot`, "Lô này đã được đính chính, xem lô {new_code}.".
3. `$new = $this->create($old->only(['supplier','variety','purpose','weight_kg']) + ['harvest_date' => $old->harvest_date->toDateString()], $by)`.
4. Audit lô mới: `corrects` `{old_code, reason}`.
5. Audit lô cũ: `corrected` `{reason, by: $by->name, org: $by->org_name, new_code}`. **Không** đổi status/published_at lô cũ.

`verify()` không cần sửa: chuỗi vẫn liên tục, nó đọc dòng `published` cuối.

Trang công khai lô cũ: nếu có `correction` → băng vàng "Hồ sơ này đã được đính chính ngày …: {reason}". Nếu lô mới `isPublished()` → link "Xem hồ sơ đính chính {new_code}" tới `public.show`; ngược lại "Hồ sơ đính chính {new_code} đang được lập.".

Màn hình lô (`lots/show`, nhánh đã công bố): form `<textarea name="ly_do">` + nút "Đính chính hồ sơ" (chỉ khi chưa đính chính); nếu đã đính chính → link sang `lots.show` lô mới. Thay câu "Muốn đổi thì tạo lô mới." trong `assertEditable` thành "Muốn đổi thì dùng Đính chính hồ sơ.".

- [ ] **Step 1: Test hỏng**

```php
public function test_dinh_chinh_tao_lo_moi_va_giu_lo_cu_nguyen_ven(): void
{
    $old = $this->publishedLot();
    $this->post("/lo/{$old->code}/dinh-chinh", ['ly_do' => 'Ghi nhầm khối lượng'])->assertRedirect();

    $new = Lot::latest('id')->first();
    $this->assertNotSame($old->code, $new->code);
    $this->assertSame('draft', $new->status);
    $this->assertSame('published', $old->fresh()->status);
    $this->assertSame($new->code, $old->fresh()->correction()['new_code']);
    $this->assertTrue($new->auditLogs()->where('action', 'corrects')->exists());

    $this->get("/ho-so/{$old->code}")
        ->assertSee('Hồ sơ nguyên vẹn')      // bang xanh public/show.blade.php:13
        ->assertSee('Ghi nhầm khối lượng')
        ->assertSee('đang được lập')
        ->assertDontSee(route('public.show', $new));
}

public function test_dinh_chinh_hai_lan_bi_tu_choi(): void
{
    $old = $this->publishedLot();
    $this->post("/lo/{$old->code}/dinh-chinh", ['ly_do' => 'Lần 1']);
    $this->post("/lo/{$old->code}/dinh-chinh", ['ly_do' => 'Lần 2'])->assertSessionHasErrors('lot');
    $this->assertSame(1, $old->auditLogs()->where('action', 'corrected')->count());
}

public function test_dinh_chinh_lo_chua_cong_bo_hoac_thieu_ly_do_bi_tu_choi(): void
{
    $lot = $this->createLot();
    $this->post("/lo/{$lot->code}/dinh-chinh", ['ly_do' => 'x'])->assertSessionHasErrors('lot');
    $pub = $this->publishedLot();
    $this->post("/lo/{$pub->code}/dinh-chinh", ['ly_do' => ''])->assertSessionHasErrors('ly_do');
}

public function test_to_chuc_khac_khong_dinh_chinh_duoc(): void
{
    $lot = $this->publishedLot();
    $this->actingAs(User::factory()->create(['org_name' => 'HTX khác']))
        ->post("/lo/{$lot->code}/dinh-chinh", ['ly_do' => 'x'])->assertForbidden();
}

public function test_lo_dinh_chinh_da_cong_bo_thi_trang_cu_co_link(): void
{
    $old = $this->publishedLot();
    $this->post("/lo/{$old->code}/dinh-chinh", ['ly_do' => 'Sai giống']);
    $new = Lot::latest('id')->first();
    $this->post("/lo/{$new->code}/anh", ['anh' => self::realPhoto(), 'view' => 'toan_qua']);
    $this->post("/lo/{$new->code}/xac-nhan", ['items' => self::manualItems()]);
    $this->post("/lo/{$new->code}/cong-bo");

    $this->get("/ho-so/{$old->code}")->assertSee(route('public.show', $new));
}
```

- [ ] **Step 2: FAIL** — `--filter=dinh_chinh`.
- [ ] **Step 3:** Cài `Lot::correction()`, `LotWorkflow::correct()`, controller (`authorizeLot` → validate `ly_do` → `correct` → redirect `lots.show` lô mới với flash `ok` "Đã tạo lô đính chính. Chụp ảnh và xác nhận lại."), route, 2 view.
- [ ] **Step 4: Toàn bộ test PASS** (gồm 3 test "trang QR đỏ" cũ).
- [ ] **Step 5: Commit** `Gop y 33/49: dinh chinh ho so da cong bo bang dong audit bu, khong xoa lich su`

---

### Task 4: KPI có mẫu số trên Thống kê (mục 43)

**Files:**
- Modify: `app/Http/Controllers/StatsController.php` (`index`)
- Modify: `resources/views/stats/index.blade.php`
- Test: `tests/Feature/StatsAndDatasetTest.php` (đã seed 3 lô trong `setUp`) + Create `tests/Feature/StatsKpiEmptyTest.php` (DB trống, `RefreshDatabase`, không seed)

**Interfaces:**
- Produces (biến view): `kpis` — list of `['label' => string, 'num' => int, 'den' => int, 'note' => string]`.

KPI (đúng bảng mẫu của thầy, mục "Mẫu chỉ số KPI"):

| label | num | den | note |
|---|---|---|---|
| Tỷ lệ hồ sơ hoàn tất | lô `published` | tổng lô | Nhật ký lô |
| Tỷ lệ ảnh phải chụp lại | ảnh `quality.ok = false` | tổng ảnh | Kiểm tra ảnh phía server |
| Tỷ lệ nhập tay khi không có/không dùng gợi ý AI | nhãn `decision = manual` | tổng nhãn | |
| Hồ sơ phải đính chính | lô có audit `corrected` | lô `published` | Từ Task 3 |

Đếm ảnh lỗi bằng PHP trên cột JSON (`LotPhoto::get(['quality'])`) như cách `$minutes` đang làm, để chạy được cả MySQL lẫn SQLite.
View: mỗi KPI hiện `num/den (xx%)`; khi `den === 0` hiện **"Chưa có dữ liệu"**, không hiện %. Thêm dòng chú thích: "Chỉ số đo trên dữ liệu thật trong hệ thống; chưa có lượt quét QR."

- [ ] **Step 1: Test hỏng**

```php
// tests/Feature/StatsKpiEmptyTest.php — DB trong
public function test_kpi_khong_chia_cho_0_khi_chua_co_du_lieu(): void
{
    $this->actingAs(User::factory()->admin()->create())->get('/thong-ke')
        ->assertSee('Tỷ lệ ảnh phải chụp lại')
        ->assertSee('Chưa có dữ liệu')
        ->assertDontSee('NAN');
}

// tests/Feature/StatsAndDatasetTest.php — setUp da seed 3 lo cong bo
public function test_kpi_hien_tu_so_mau_so(): void
{
    $this->actingAs(User::factory()->admin()->create())->get('/thong-ke')
        ->assertSee('Tỷ lệ hồ sơ hoàn tất')
        ->assertSee('3/3');
}
```

- [ ] **Step 2: FAIL** — `--filter=kpi_`.
- [ ] **Step 3:** Cài vào `StatsController::index` + view.
- [ ] **Step 4: Toàn bộ test PASS**
- [ ] **Step 5: Commit** `Gop y 43: KPI co tu so/mau so, khong hien % khi chua co du lieu`

---

### Task 5: Benchmark AI trung thực (mục 31, 47)

**Files:**
- Create: `app/Services/Quality/EvalMetrics.php`
- Modify: `app/Console/Commands/EvalTestset.php`
- Modify: `storage/app/testset/xoai/ground_truth.json` (mỗi label thêm `"reviewed_by": null`)
- Test: `tests/Unit/EvalMetricsTest.php`

**Interfaces:**
- Produces: `EvalMetrics::binary(array $pairs): array` — `$pairs` là list `[bool $that, bool $ai]` (true = có khuyết tật). Trả `['tp'=>int,'fp'=>int,'fn'=>int,'tn'=>int,'precision'=>?float,'recall'=>?float,'f1'=>?float]`; tỉ số nào có mẫu số 0 → `null`.

Command:
- Thay dòng "Co/khong khuyet tat khop" bằng bảng TP/FP/FN/TN + Precision/Recall/F1 (dương tính = `surface !== 'sach'`), in `—` khi null. Giữ "khớp đúng / lệch 1 bậc" và "ăn vào thịt".
- Đếm `reviewed = số label có reviewed_by khác null`. Nếu `reviewed < n` in `$this->warn("Chi {$reviewed}/{$n} nhan co nguoi duyet — KHONG dua con so nay vao ho so/PPT.")`. Bỏ dòng comment cứng "n=25 sai so ~±10" (thay bằng cảnh báo động này).
- Option `--csv=` : ghi từng dòng `file,that,ai,ket_qua,reviewed_by` (UTF-8 BOM để Excel đọc tiếng Việt) — làm "danh sách lỗi" cho mục 31. Mặc định không ghi.

- [ ] **Step 1: Test hỏng**

```php
public function test_dem_tp_fp_fn_tn_va_tinh_p_r_f1(): void
{
    $m = EvalMetrics::binary([[true,true],[true,true],[true,false],[false,true],[false,false]]);
    $this->assertSame(['tp'=>2,'fp'=>1,'fn'=>1,'tn'=>1], array_intersect_key($m, array_flip(['tp','fp','fn','tn'])));
    $this->assertEqualsWithDelta(2/3, $m['precision'], 1e-9);
    $this->assertEqualsWithDelta(2/3, $m['recall'], 1e-9);
    $this->assertEqualsWithDelta(2/3, $m['f1'], 1e-9);
}

public function test_mau_so_0_thi_tra_null_khong_chia_0(): void
{
    $m = EvalMetrics::binary([[false,false]]);
    $this->assertNull($m['precision']);
    $this->assertNull($m['recall']);
    $this->assertNull($m['f1']);
}
```

- [ ] **Step 2: FAIL** — `--filter=EvalMetricsTest`.
- [ ] **Step 3:** Cài `EvalMetrics::binary`, sửa command, thêm `reviewed_by: null` vào 25 label (giữ nguyên các trường khác; sửa bằng script, không format lại `_huong_dan`). Thêm một dòng vào `_huong_dan`: "Nguoi co chuyen mon duyet nhan nao thi ghi ten vao reviewed_by."
- [ ] **Step 4: Toàn bộ test PASS.** Không chạy `quality:eval` thật (tốn quota) trừ khi người dùng yêu cầu.
- [ ] **Step 5: Commit** `Gop y 31/47: eval co TP/FP/FN/TN, P/R/F1, canh bao nhan chua duyet, xuat CSV`

---

### Task 6: Chốt

- [ ] `vendor/bin/pint`
- [ ] `php artisan config:clear && php artisan test` → toàn bộ xanh (66 + test mới).
- [ ] `npm run build` (view đổi class Tailwind), mở `/ho-so/{mã}` một lô đã đính chính trên Laragon để nhìn băng vàng.
- [ ] `php artisan config:cache` nếu Laragon cần; `git push`.
- [ ] Cập nhật README mục tính năng: đính chính hồ sơ, KPI, cách đọc kết quả eval.
