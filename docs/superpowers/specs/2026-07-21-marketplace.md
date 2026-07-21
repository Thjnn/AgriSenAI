# SPEC — Sàn trực tuyến hiển thị sản phẩm "AI chứng nhận" + QR

> File dành cho **Claude Code** implement trong AgriSenAi (Laravel 13 + MySQL).
> Backend bám SÁT 15 bảng đã có trong `AgriSenAI.sql`. Giỏ hàng/thanh toán/giao hàng chỉ làm **logic cơ bản hoặc mockup**.
> Đọc hết mục 1–4 trước khi code. KHÔNG tạo bảng mới nếu không có mục "migration bổ sung" cho phép.

---

## 0. Bối cảnh schema (đọc kỹ — quyết định toàn bộ thiết kế)

15 bảng đã tồn tại. Các bảng liên quan trực tiếp chức năng này:

| Bảng | Vai trò trong chức năng |
|------|--------------------------|
| `products` | Đơn vị được bán & được chứng nhận. Có sẵn cột `qr_code`, `status` ENUM(draft/active/sold_out/hidden) |
| `ai_quality_assessments` | Điểm AI (`quality_score`), độ chín (`ripeness_level`), lỗi (`defect_detected`), `raw_ai_response` JSON, `image_url` |
| `product_images` | Ảnh sản phẩm, cờ `is_primary` |
| `categories` | Xoài / Quýt hồng / Sen / Nem... |
| `users` + `farmer_profiles` | `products.farmer_id → users.id`; profile có `farm_name`, `ocop_star_rating`, `region` |
| `traceability_logs` | Nhật ký giai đoạn (thu_hoach/dong_goi/van_chuyen/dang_ban) |
| `reviews` | Đánh giá của người mua (rating 1-5) |
| `orders` / `order_items` | Giao dịch (chỉ làm cơ bản/mockup) |

**Định nghĩa "sản phẩm đã được AI chứng nhận"** (dùng nhất quán toàn hệ thống):
```
Một product được coi là "AI chứng nhận" KHI VÀ CHỈ KHI:
  - products.status = 'active'
  - CÓ ít nhất 1 bản ghi trong ai_quality_assessments (product_id khớp)
  - products.qr_code IS NOT NULL
```
> Đây là điều kiện lọc chính của sàn. Đặt nó thành 1 query scope tái dùng (mục 3).

⚠️ **Lưu ý về cột `qr_code`:** file SQL đặt nhầm comment "Mã QR" ở `farmer_profiles`, nhưng cột `qr_code` THỰC SỰ nằm ở bảng `products` (đúng chỗ). QR gắn với **product**, không phải nhà vườn. Đơn vị chứng nhận + truy xuất = **product**. (Điều này thống nhất với spec QR truy xuất: dùng `products.qr_code` làm mã, `/trace/{qr_code}` là trang tra cứu.)

---

## 1. Phạm vi & mức độ hoàn thiện (tránh làm quá tay)

| Luồng | Mức độ yêu cầu |
|-------|----------------|
| **Danh sách sản phẩm chứng nhận** (sàn) | ĐẦY ĐỦ — đây là trọng tâm |
| **Trang chi tiết sản phẩm** | ĐẦY ĐỦ — hiển thị điểm AI, QR, nhà vườn, ảnh, review, traceability |
| **Lọc / tìm kiếm / sắp xếp** | ĐẦY ĐỦ mức cơ bản (theo category, điểm AI, giá) |
| **Giỏ hàng** | CƠ BẢN — session-based, không cần bảng cart riêng |
| **Thanh toán** | MOCKUP — tạo `orders` + `order_items` với status='pending', KHÔNG tích hợp cổng thanh toán |
| **Giao hàng** | MOCKUP — chỉ lưu `shipping_address`, hiển thị status tĩnh |

---

## 2. Models & quan hệ Eloquent

Tạo/hoàn thiện các model ánh xạ đúng bảng (giữ nguyên tên bảng snake_case tiếng Việt qua `$table` nếu cần).

```php
// Product.php  → table 'products'
class Product extends Model {
    protected $fillable = [
        'farmer_id','category_id','name','description','price','unit',
        'quantity_available','qr_code','status'
    ];
    protected $casts = ['price'=>'decimal:2','quantity_available'=>'decimal:2'];

    public function farmer()      { return $this->belongsTo(User::class, 'farmer_id'); }
    public function category()    { return $this->belongsTo(Category::class); }
    public function images()      { return $this->hasMany(ProductImage::class); }
    public function primaryImage(){ return $this->hasOne(ProductImage::class)->where('is_primary', true); }
    public function assessments() { return $this->hasMany(AiQualityAssessment::class); }
    public function latestAssessment() {
        return $this->hasOne(AiQualityAssessment::class)->latestOfMany('assessed_at');
    }
    public function traceLogs()   { return $this->hasMany(TraceabilityLog::class)->orderBy('happened_at'); }
    public function reviews()     { return $this->hasMany(Review::class); }

    // "đã chứng nhận" (mục 0)
    public function scopeCertified($q) {
        return $q->where('status','active')
                 ->whereNotNull('qr_code')
                 ->whereHas('assessments');
    }
}
```

```php
// User.php (đã có sẵn của Laravel) — thêm:
public function farmerProfile() { return $this->hasOne(FarmerProfile::class); }
public function products()      { return $this->hasMany(Product::class, 'farmer_id'); }

// FarmerProfile.php → 'farmer_profiles'
public function user() { return $this->belongsTo(User::class); }

// AiQualityAssessment.php → 'ai_quality_assessments'
protected $casts = ['raw_ai_response'=>'array','quality_score'=>'decimal:2','defect_detected'=>'boolean','assessed_at'=>'datetime'];
public function product() { return $this->belongsTo(Product::class); }

// Category, ProductImage, TraceabilityLog, Review, Order, OrderItem tương tự — map đúng FK ở SQL.
```

> `AiQualityAssessment` KHÔNG có `updated_at`/created_at chuẩn Laravel (chỉ có `assessed_at`). Set `public $timestamps = false;` và tự quản `assessed_at`. Áp dụng tương tự cho các bảng chỉ có 1 timestamp (`farmer_profiles`, `product_images`, `traceability_logs`, `reviews`, `notifications`, `roles`).

---

## 3. Query hiển thị sàn (hiệu năng — tránh N+1)

Controller danh sách sàn:
```php
$products = Product::certified()
    ->with([
        'primaryImage',
        'category',
        'farmer.farmerProfile',
        'latestAssessment',
    ])
    ->withAvg('reviews','rating')
    ->withCount('reviews')
    // lọc tuỳ chọn:
    ->when($request->category_id, fn($q,$c)=>$q->where('category_id',$c))
    ->when($request->min_score, fn($q,$s)=>$q->whereHas('latestAssessment', fn($a)=>$a->where('quality_score','>=',$s)))
    // sắp xếp:
    ->when($request->sort === 'score_desc', fn($q)=>$q->orderByDesc(
        AiQualityAssessment::select('quality_score')
            ->whereColumn('product_id','products.id')
            ->latest('assessed_at')->limit(1)
    ))
    ->when($request->sort === 'price_asc', fn($q)=>$q->orderBy('price'))
    ->paginate(12)
    ->withQueryString();
```
- Luôn eager-load để tránh N+1 (danh sách 12 sp × nhiều quan hệ).
- Điểm AI hiển thị = `latestAssessment.quality_score`.

---

## 4. Routes

```php
// routes/web.php

// SÀN (public — người mua/giám khảo xem không cần login)
Route::get('/', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/san-pham', [MarketplaceController::class, 'index'])->name('products.index'); // alias nếu cần
Route::get('/san-pham/{product}', [MarketplaceController::class, 'show'])->name('products.show');

// Trang tra cứu QR (public) — dùng qr_code làm key, KHỚP spec QR truy xuất
Route::get('/trace/{qr_code}', [TraceController::class, 'show'])->name('trace.show');

// GIỎ HÀNG (session-based, cơ bản)
Route::post('/gio-hang/them/{product}', [CartController::class, 'add'])->name('cart.add');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}', [CartController::class, 'remove'])->name('cart.remove');

// CHECKOUT (mockup — chỉ tạo order pending)
Route::get('/thanh-toan', [CheckoutController::class, 'form'])->name('checkout.form');
Route::post('/thanh-toan', [CheckoutController::class, 'placeOrder'])->name('checkout.place'); // tạo order + order_items
Route::get('/don-hang/{order:order_code}', [CheckoutController::class, 'show'])->name('order.show');
```
- `{product}` bind theo `id` mặc định. Nếu muốn URL đẹp có thể thêm slug sau — không bắt buộc.

---

## 5. MarketplaceController

```php
class MarketplaceController extends Controller
{
    public function index(Request $request) {
        $categories = Category::all();
        $products = /* query mục 3 */;
        return view('marketplace.index', compact('products','categories'));
    }

    public function show(Product $product) {
        abort_unless($product->status === 'active', 404);
        $product->load([
            'images','category','farmer.farmerProfile',
            'assessments' => fn($q)=>$q->latest('assessed_at'),
            'traceLogs',
            'reviews.buyer',
        ]);
        $assessment = $product->assessments->first(); // mới nhất
        return view('marketplace.show', compact('product','assessment'));
    }
}
```

---

## 6. Giao diện — 3 màn hình

Phong cách: **clean/minimal e-commerce**, mobile-first. KHÔNG 3D/Duolingo. Nhất quán hệ màu với `agrisenai-home.html` đã có.

### 6.1 Danh sách sàn (`marketplace/index.blade.php`)
Grid card sản phẩm. Mỗi card gồm:
- Ảnh primary (`primaryImage.image_url`), fallback ảnh mặc định.
- **Badge "AI chứng nhận"** + điểm (`latestAssessment.quality_score`/100). Đây là điểm nhấn — mọi sản phẩm trên sàn đều có badge này (vì đã lọc `certified`).
- Tên, giá + đơn vị, tên nhà vườn (`farmer.farmerProfile.farm_name`).
- Badge OCOP nếu `ocop_star_rating` không null (⭐×n).
- Rating trung bình (reviews_avg_rating) + số review.
- Icon QR nhỏ (gợi ý "có truy xuất") — click vào card đi tới trang chi tiết.

Thanh lọc trên cùng: dropdown category, sort (điểm AI cao→thấp / giá tăng dần), ô nhập điểm tối thiểu. Submit GET (giữ query string).

### 6.2 Chi tiết sản phẩm (`marketplace/show.blade.php`)
Bố cục 2 cột desktop, 1 cột mobile:
- **Cột trái:** gallery ảnh (`product_images`).
- **Cột phải:**
  - Tên + giá + đơn vị + tồn kho (`quantity_available`).
  - **Khối "AI chứng nhận chất lượng":** điểm (`quality_score`), độ chín (`ripeness_level` → nhãn tiếng Việt: xanh/vừa chín/chín/quá chín), cảnh báo nếu `defect_detected` (kèm `defect_note`), thời điểm chấm (`assessed_at`). Ghi nguồn "Đánh giá bởi AI Gemini Vision".
  - **QR code:** render QR trỏ `route('trace.show', $product->qr_code)` (dùng simple-qrcode, xem spec QR). Nút "Xem nhật ký truy xuất".
  - **Thông tin nhà vườn:** `farm_name`, `region`, OCOP sao, description.
  - Nút "Thêm vào giỏ" (POST `cart.add`).
- **Dưới cùng:** 
  - **Nhật ký truy xuất** (`traceLogs`): timeline dọc các stage.
  - **Đánh giá** (`reviews`): danh sách rating + comment.

### 6.3 Giỏ hàng + checkout (mockup)
- Giỏ hàng: bảng sản phẩm, số lượng, tạm tính. Nút "Tiến hành đặt hàng".
- Checkout: form nhập `shipping_address` + note → tạo `orders` (status='pending', `order_code` random) + `order_items` từ giỏ. KHÔNG xử lý thanh toán thật. Sau khi đặt → trang xác nhận hiển thị order_code + trạng thái "Chờ xác nhận".
- Ghi rõ trong UI: đây là luồng demo, chưa tích hợp thanh toán/giao vận thật.

---

## 7. Giỏ hàng — logic cơ bản (session)

Không dùng bảng cart. Lưu trong session:
```php
// cấu trúc: session('cart') = [ product_id => quantity, ... ]
public function add(Request $req, Product $product) {
    abort_unless($product->status === 'active', 404);
    $cart = session('cart', []);
    $cart[$product->id] = ($cart[$product->id] ?? 0) + (float) $req->input('quantity', 1);
    session(['cart' => $cart]);
    return back()->with('ok','Đã thêm vào giỏ');
}
```
Trang giỏ hàng load `Product::whereIn('id', array_keys($cart))->with('primaryImage')->get()` rồi ghép số lượng + tính tạm tính.

---

## 8. Checkout mockup — tạo order

```php
public function placeOrder(Request $req) {
    $cart = session('cart', []);
    abort_if(empty($cart), 400);
    $data = $req->validate([
        'shipping_address' => 'required|string|max:255',
        'note' => 'nullable|string',
    ]);
    $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

    DB::transaction(function () use ($cart, $products, $data, &$order) {
        $total = 0;
        foreach ($cart as $pid => $qty) $total += $products[$pid]->price * $qty;

        $order = Order::create([
            'buyer_id' => auth()->id() ?? 1, // demo: fallback user mẫu nếu chưa login
            'order_code' => 'DH'.now()->format('ymd').strtoupper(Str::random(5)),
            'total_amount' => $total,
            'status' => 'pending',
            'shipping_address' => $data['shipping_address'],
            'note' => $data['note'] ?? null,
        ]);
        foreach ($cart as $pid => $qty) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $pid,
                'quantity' => $qty,
                'unit_price' => $products[$pid]->price,
                'subtotal' => $products[$pid]->price * $qty,
            ]);
        }
    });

    session()->forget('cart');
    return redirect()->route('order.show', $order->order_code);
}
```
> `buyer_id` NOT NULL trong schema → nếu demo không login, seed 1 user `nguoi_mua` mẫu và fallback id đó. Ghi chú rõ.

---

## 9. Seeder demo (bắt buộc để sàn có dữ liệu lúc thi)

Tạo `DatabaseSeeder` (hoặc seeder riêng) sinh dữ liệu thật để giám khảo thấy sàn "sống":
1. `roles` đã có sẵn trong SQL (nong_dan/nguoi_mua/admin).
2. ~3 user nông dân + `farmer_profiles` (có OCOP sao), 1 user người mua demo.
3. `categories`: Xoài, Quýt hồng, Sen, Nhãn, Sầu riêng, Bưởi.
4. ~8–12 `products` status='active', có `qr_code` (ULID/random unique), gắn category + farmer.
5. Mỗi product ≥1 `product_images` (is_primary=true) + ≥1 `ai_quality_assessments` (quality_score thực tế 70–98, ripeness_level, raw_ai_response mẫu).
6. Vài `traceability_logs` (thu_hoach → dong_goi → dang_ban) + vài `reviews`.

> Điều kiện: mọi product seed phải thoả `certified()` để lên sàn ngay.

---

## 10. Checklist nghiệm thu (Definition of Done)

- [ ] Trang `/` hiển thị lưới sản phẩm, MỌI item đều có badge "AI chứng nhận" + điểm.
- [ ] Lọc theo category, sort theo điểm AI / giá hoạt động (giữ query string khi phân trang).
- [ ] Không có query N+1 (kiểm bằng Laravel Debugbar/telescope hoặc đếm query).
- [ ] Trang chi tiết hiển thị đủ: ảnh, điểm AI + độ chín + cảnh báo lỗi, QR trỏ `/trace/{qr_code}`, thông tin nhà vườn, traceability timeline, reviews.
- [ ] QR trên trang chi tiết quét được, mở đúng trang tra cứu (khớp spec QR).
- [ ] Thêm giỏ / sửa số lượng / xoá hoạt động (session).
- [ ] Checkout tạo `orders` + `order_items` đúng, order_code unique, status='pending'.
- [ ] Seeder chạy 1 lệnh ra sàn đầy dữ liệu.
- [ ] Mobile-first: sàn + chi tiết đọc tốt trên điện thoại.

---

## 11. Thứ tự implement đề xuất

1. Models + quan hệ (mục 2) + scope `certified`.
2. Seeder demo (mục 9) — để có data test ngay.
3. `MarketplaceController` index + view danh sách (mục 3, 5, 6.1).
4. Trang chi tiết (mục 6.2) — nối QR sang spec truy xuất.
5. Giỏ hàng session (mục 7).
6. Checkout mockup (mục 8) + trang xác nhận đơn.
7. Chạy checklist mục 10, đặc biệt kiểm N+1 và QR.

---

## 12. Đồng bộ với spec QR truy xuất (QUAN TRỌNG)

Chức năng này DÙNG CHUNG hạ tầng với "SPEC-qr-traceability":
- Mã QR = `products.qr_code` (KHÔNG tạo bảng `batches` riêng — bỏ phần đó trong spec QR cũ, chuyển sang dùng `products` + `ai_quality_assessments` + `traceability_logs` + `farmer_profiles` có sẵn).
- Trang `/trace/{qr_code}` đọc: product → latestAssessment (điểm + ảnh AI) → farmer profile → traceLogs. Đây chính là "nhật ký chất lượng" mà giám khảo quét thấy.
- Nếu muốn giữ tính "bất biến/xác minh" (integrity hash) như spec QR: thêm 1 migration bổ sung 2 cột vào `ai_quality_assessments` (`integrity_hash VARCHAR(64)`, `image_sha256 VARCHAR(64)`) và tính hash lúc tạo assessment. Tuỳ chọn nâng cao, không bắt buộc cho bản cơ bản.
