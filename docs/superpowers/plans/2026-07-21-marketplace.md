# Sàn thị trường (Marketplace) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây sàn thị trường công khai hiển thị nông sản "AI chứng nhận" (danh sách + lọc/sort, chi tiết, giỏ hàng session, checkout mockup) trên nền 15 bảng có sẵn.

**Architecture:** Laravel MVC. Models Eloquent map đúng schema hiện có (không migration mới). `Product::scopeCertified()` là bộ lọc trung tâm. Dữ liệu demo đến từ seeder. UI dùng `layouts.app` (chrome đã dựng) + bám 3 file demo trong `Test-Giao-Dien/`. Giỏ hàng lưu session (không bảng cart).

**Tech Stack:** Laravel 12, PHP 8.3, MySQL (dev) / SQLite :memory: (test), Blade, Tailwind v4 (chrome), PHPUnit.

## Global Constraints

- KHÔNG tạo bảng mới; chỉ dùng 15 bảng đã migrate. (Không có phần này trong plan → sai spec.)
- Sàn đặt ở `/thi-truong` (page `market`); **giữ nguyên** trang chủ `/` (HomeController cũ).
- Model chỉ-1-timestamp phải đặt `public $timestamps = false;`: Category, OrderItem, AiQualityAssessment, FarmerProfile, ProductImage, TraceabilityLog, Review. Chỉ `Product` và `Order` dùng timestamps mặc định.
- `User` có `#[Fillable(['name','email','password'])]` — `role_id` **KHÔNG** mass-assign được; phải gán `$u->role_id = ...; $u->save();`.
- Định nghĩa "AI chứng nhận" (dùng nhất quán): `status='active'` AND có ≥1 `ai_quality_assessments` AND `qr_code IS NOT NULL`.
- **KHÔNG** thêm `getRouteKeyName()='qr_code'` toàn cục vào Product (thuộc feature QR sau; route trace dùng scoped binding).
- Route-model binding `{product}` mặc định theo `id`.
- Test: extend `Tests\TestCase`, dùng `Illuminate\Foundation\Testing\RefreshDatabase` (chạy trên sqlite :memory:).
- Ngoài phạm vi plan này: trang QR `/trace`, integrity hash, khối QR trên trang chi tiết, thanh toán/giao vận thật, auth login thật.

---

### Task 1: Eloquent models + quan hệ + scopeCertified

**Files:**
- Create: `app/Models/Category.php`, `app/Models/Product.php`, `app/Models/ProductImage.php`, `app/Models/AiQualityAssessment.php`, `app/Models/FarmerProfile.php`, `app/Models/TraceabilityLog.php`, `app/Models/Review.php`, `app/Models/Order.php`, `app/Models/OrderItem.php`
- Modify: `app/Models/User.php` (thêm 2 quan hệ)
- Test: `tests/Feature/ProductCertifiedScopeTest.php`

**Interfaces:**
- Produces:
  - `Product::certified()` — scope, trả builder chỉ product đã chứng nhận.
  - `Product` quan hệ: `farmer()`, `category()`, `images()`, `primaryImage()`, `assessments()`, `latestAssessment()`, `traceLogs()`, `reviews()`.
  - `Order->items()`, `Order->buyer()`; `OrderItem->product()`.
  - `User->farmerProfile()`, `User->products()`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/ProductCertifiedScopeTest.php
namespace Tests\Feature;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCertifiedScopeTest extends TestCase
{
    use RefreshDatabase;

    /** Tao 1 farmer + category, tra ve id de gan product. */
    private function farmerAndCategory(): array
    {
        $role = Role::firstOrCreate(['name' => Role::NONG_DAN]);
        $u = new User();
        $u->name = 'Nông dân A';
        $u->email = 'a@demo.test';
        $u->password = bcrypt('secret');
        $u->role_id = $role->id;   // role_id khong fillable -> gan truc tiep
        $u->save();

        $cat = Category::create(['name' => 'Xoài', 'slug' => 'xoai']);

        return [$u->id, $cat->id];
    }

    private function makeProduct(int $farmerId, int $catId, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'farmer_id' => $farmerId,
            'category_id' => $catId,
            'name' => 'Xoài Cát',
            'price' => 30000,
            'unit' => 'kg',
            'quantity_available' => 100,
            'qr_code' => 'QR'.uniqid(),
            'status' => 'active',
        ], $attrs));
    }

    public function test_certified_scope_chi_lay_product_active_co_qr_va_co_assessment(): void
    {
        [$farmerId, $catId] = $this->farmerAndCategory();

        // (1) hop le
        $ok = $this->makeProduct($farmerId, $catId);
        AiQualityAssessment::create([
            'product_id' => $ok->id, 'image_url' => 'x.jpg', 'ripeness_level' => 'chin',
            'quality_score' => 90, 'defect_detected' => false, 'assessed_at' => now(),
        ]);

        // (2) draft -> loai
        $this->makeProduct($farmerId, $catId, ['status' => 'draft', 'qr_code' => 'QRd']);
        // (3) khong co qr -> loai
        $this->makeProduct($farmerId, $catId, ['qr_code' => null]);
        // (4) active + qr nhung KHONG co assessment -> loai
        $this->makeProduct($farmerId, $catId, ['qr_code' => 'QRn']);

        $ids = Product::certified()->pluck('id')->all();

        $this->assertSame([$ok->id], $ids);
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=ProductCertifiedScopeTest`
Expected: FAIL với "Class 'App\Models\Product' not found" (hoặc Category/Role).

- [ ] **Step 3: Tạo các model**

`app/Models/Category.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'slug', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

`app/Models/FarmerProfile.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerProfile extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'farm_name', 'ocop_certificate', 'ocop_star_rating', 'region', 'description'];
    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

`app/Models/ProductImage.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'image_url', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean', 'created_at' => 'datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

`app/Models/AiQualityAssessment.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiQualityAssessment extends Model
{
    public $timestamps = false;   // bang chi co assessed_at
    protected $fillable = [
        'product_id', 'image_url', 'ripeness_level', 'quality_score',
        'defect_detected', 'defect_note', 'raw_ai_response', 'assessed_at',
    ];
    protected $casts = [
        'raw_ai_response' => 'array',
        'quality_score' => 'decimal:2',
        'defect_detected' => 'boolean',
        'assessed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

`app/Models/TraceabilityLog.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceabilityLog extends Model
{
    public $timestamps = false;   // bang chi co happened_at
    protected $fillable = ['product_id', 'stage', 'description', 'location', 'happened_at'];
    protected $casts = ['happened_at' => 'datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

`app/Models/Review.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public $timestamps = false;   // bang chi co created_at
    protected $fillable = ['product_id', 'buyer_id', 'rating', 'comment'];
    protected $casts = ['created_at' => 'datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
```

`app/Models/Order.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['buyer_id', 'order_code', 'total_amount', 'status', 'shipping_address', 'note'];
    protected $casts = ['total_amount' => 'decimal:2'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

`app/Models/OrderItem.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;   // bang khong co timestamp
    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

`app/Models/Product.php`:
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'farmer_id', 'category_id', 'name', 'description', 'price',
        'unit', 'quantity_available', 'qr_code', 'status',
    ];
    protected $casts = ['price' => 'decimal:2', 'quantity_available' => 'decimal:2'];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AiQualityAssessment::class);
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(AiQualityAssessment::class)->latestOfMany('assessed_at');
    }

    public function traceLogs(): HasMany
    {
        return $this->hasMany(TraceabilityLog::class)->orderBy('happened_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** "AI chung nhan": active + co qr_code + co it nhat 1 assessment. */
    public function scopeCertified(Builder $q): Builder
    {
        return $q->where('status', 'active')
            ->whereNotNull('qr_code')
            ->whereHas('assessments');
    }
}
```

- [ ] **Step 4: Thêm 2 quan hệ vào User**

Trong `app/Models/User.php`, thêm vào trong class (sau method `accountHref()`), và bổ sung `use Illuminate\Database\Eloquent\Relations\HasMany;` ở đầu file:
```php
    public function farmerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FarmerProfile::class);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }
```

- [ ] **Step 5: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=ProductCertifiedScopeTest`
Expected: PASS (1 test).

- [ ] **Step 6: Commit**

```bash
git add app/Models tests/Feature/ProductCertifiedScopeTest.php
git commit -m "Them Eloquent models + Product::scopeCertified"
```

---

### Task 2: Seeder demo (dữ liệu sống cho sàn)

**Files:**
- Create: `database/seeders/MarketplaceDemoSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/MarketplaceSeederTest.php`

**Interfaces:**
- Consumes: models Task 1, `Role` (có sẵn), `RoleSeeder` (có sẵn).
- Produces: sau `php artisan db:seed` → ≥8 product thoả `certified()`; đúng 1 user `nguoi_mua` demo email `muademo@demo.test`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/MarketplaceSeederTest.php
namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_sinh_du_san_pham_chung_nhan_va_1_nguoi_mua(): void
    {
        $this->seed(); // chay DatabaseSeeder

        $this->assertGreaterThanOrEqual(8, Product::certified()->count());
        $this->assertTrue(Role::where('name', Role::NGUOI_MUA)->exists());

        $buyer = User::where('email', 'muademo@demo.test')->first();
        $this->assertNotNull($buyer);

        // moi product chung nhan deu co anh primary + assessment
        $p = Product::certified()->with(['primaryImage', 'latestAssessment'])->first();
        $this->assertNotNull($p->primaryImage);
        $this->assertNotNull($p->latestAssessment);
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=MarketplaceSeederTest`
Expected: FAIL (chưa đủ 8 certified — seeder chưa có).

- [ ] **Step 3: Viết `MarketplaceDemoSeeder`**

`database/seeders/MarketplaceDemoSeeder.php`:
```php
<?php
namespace Database\Seeders;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\FarmerProfile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Role;
use App\Models\TraceabilityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $nongDan = Role::firstOrCreate(['name' => Role::NONG_DAN])->id;
        $nguoiMua = Role::firstOrCreate(['name' => Role::NGUOI_MUA])->id;

        // 1 nguoi mua demo (fallback buyer_id cho checkout)
        $this->makeUser('Người Mua Demo', 'muademo@demo.test', $nguoiMua);

        // 3 nong dan + farmer_profiles
        $farmers = [];
        foreach ([
            ['Sáu Tâm', 'Vườn ông Sáu Tâm', 5, 'Cao Lãnh'],
            ['Bảy Hoa', 'Vườn cô Bảy Hoa', 4, 'Lai Vung'],
            ['HTX Lai Vung', 'HTX Quýt hồng Lai Vung', 5, 'Lai Vung'],
        ] as $i => [$ten, $farm, $sao, $vung]) {
            $u = $this->makeUser($ten, 'farmer'.$i.'@demo.test', $nongDan);
            FarmerProfile::create([
                'user_id' => $u->id, 'farm_name' => $farm,
                'ocop_star_rating' => $sao, 'region' => $vung.', Đồng Tháp',
                'description' => 'Nhà vườn tại '.$vung.', canh tác theo hướng an toàn.',
            ]);
            $farmers[] = $u->id;
        }

        // categories
        $cats = [];
        foreach (['Xoài' => 'xoai', 'Quýt hồng' => 'quyt-hong', 'Sen' => 'sen',
                  'Nhãn' => 'nhan', 'Sầu riêng' => 'sau-rieng', 'Bưởi' => 'buoi'] as $ten => $slug) {
            $cats[$slug] = Category::create(['name' => $ten, 'slug' => $slug])->id;
        }

        // ~10 product active + qr + anh + assessment + traceLogs + reviews
        $buyerId = User::where('email', 'muademo@demo.test')->value('id');
        $data = [
            ['Xoài Cát Hòa Lộc loại 1', 'xoai', 32000, 'kg', 94, 'chin'],
            ['Xoài Cát Chu', 'xoai', 26000, 'kg', 88, 'vua_chin'],
            ['Quýt hồng Lai Vung', 'quyt-hong', 45000, 'kg', 91, 'chin'],
            ['Quýt hồng loại xuất khẩu', 'quyt-hong', 58000, 'kg', 97, 'chin'],
            ['Nhãn tiêu da bò', 'nhan', 28000, 'kg', 90, 'chin'],
            ['Sầu riêng Ri6', 'sau-rieng', 95000, 'kg', 96, 'chin'],
            ['Bưởi da xanh', 'buoi', 40000, 'kg', 93, 'chin'],
            ['Hạt sen tươi Tháp Mười', 'sen', 60000, 'kg', 89, 'chin'],
            ['Xoài tượng da xanh', 'xoai', 22000, 'kg', 78, 'xanh'],
            ['Quýt đường', 'quyt-hong', 35000, 'kg', 85, 'vua_chin'],
        ];

        foreach ($data as $i => [$ten, $slug, $gia, $dvi, $diem, $chin]) {
            $farmerId = $farmers[$i % count($farmers)];
            $p = Product::create([
                'farmer_id' => $farmerId, 'category_id' => $cats[$slug],
                'name' => $ten, 'description' => $ten.' — nông sản Đồng Tháp, chất lượng tuyển chọn.',
                'price' => $gia, 'unit' => $dvi, 'quantity_available' => rand(50, 300),
                'qr_code' => (string) Str::ulid(), 'status' => 'active',
            ]);

            ProductImage::create([
                'product_id' => $p->id,
                'image_url' => 'images/products/'.$slug.'.jpg',   // dat anh that sau; view co fallback
                'is_primary' => true,
            ]);

            AiQualityAssessment::create([
                'product_id' => $p->id, 'image_url' => 'products/'.$slug.'-'.$p->id.'.jpg',
                'ripeness_level' => $chin, 'quality_score' => $diem, 'defect_detected' => $diem < 80,
                'defect_note' => $diem < 80 ? 'Vài vết xước nhẹ ở vỏ.' : null,
                'raw_ai_response' => ['grade' => $diem >= 90 ? 'dac_biet' : 'hang_1', 'overall_score' => $diem / 10],
                'assessed_at' => now()->subDays(rand(0, 10)),
            ]);

            foreach (['thu_hoach' => 'Thu hoạch tại vườn', 'dong_goi' => 'Đóng gói',
                      'dang_ban' => 'Đưa lên sàn'] as $stage => $mota) {
                TraceabilityLog::create([
                    'product_id' => $p->id, 'stage' => $stage, 'description' => $mota,
                    'location' => 'Đồng Tháp', 'happened_at' => now()->subDays(rand(0, 12)),
                ]);
            }

            if ($i % 2 === 0) {
                Review::create([
                    'product_id' => $p->id, 'buyer_id' => $buyerId,
                    'rating' => rand(4, 5), 'comment' => 'Hàng tươi, đóng gói kỹ, sẽ mua lại.',
                ]);
            }
        }
    }

    private function makeUser(string $ten, string $email, int $roleId): User
    {
        $u = User::firstOrNew(['email' => $email]);
        $u->name = $ten;
        $u->password = Hash::make('password');
        $u->role_id = $roleId;   // role_id khong fillable
        $u->save();

        return $u;
    }
}
```

- [ ] **Step 4: Nối vào `DatabaseSeeder`**

`database/seeders/DatabaseSeeder.php` — đặt method `run()` thành:
```php
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MarketplaceDemoSeeder::class,
        ]);
    }
```
(Giữ nguyên phần `namespace`/`use`/khai báo class; chỉ thay thân `run()`. RoleSeeder dùng `insert` nên nếu chạy 2 lần sẽ trùng — với test `RefreshDatabase` mỗi lần DB sạch nên không sao.)

- [ ] **Step 5: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=MarketplaceSeederTest`
Expected: PASS.

- [ ] **Step 6: Chạy seeder trên DB dev + kiểm bằng mắt**

Run: `php artisan migrate:fresh --seed`
Expected: không lỗi; `php artisan tinker --execute="echo App\Models\Product::certified()->count();"` in ra ≥ 8.

- [ ] **Step 7: Commit**

```bash
git add database/seeders tests/Feature/MarketplaceSeederTest.php
git commit -m "Them MarketplaceDemoSeeder (san pham chung nhan + nha vuon + review)"
```

---

### Task 3: Trang danh sách sàn `/thi-truong`

**Files:**
- Create: `app/Http/Controllers/MarketplaceController.php`, `resources/views/marketplace/index.blade.php`
- Modify: `routes/web.php`, `config/navigation.php`
- Test: `tests/Feature/MarketplaceIndexTest.php`

**Interfaces:**
- Consumes: `Product::certified()`, `Category`, quan hệ Task 1; seeder Task 2 (cho test).
- Produces: route tên `marketplace.index` (`/thi-truong`), `MarketplaceController@index`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/MarketplaceIndexTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_san_hien_san_pham_chung_nhan(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/thi-truong');

        $res->assertOk();
        $res->assertViewIs('marketplace.index');
        $res->assertSee($p->name);
        $res->assertSee('AI chứng nhận');
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=MarketplaceIndexTest`
Expected: FAIL 404 (route chưa có).

- [ ] **Step 3: Viết controller (index)**

`app/Http/Controllers/MarketplaceController.php`:
```php
<?php
namespace App\Http\Controllers;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::certified()
            ->with(['primaryImage', 'category', 'farmer.farmerProfile', 'latestAssessment'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->min_score, fn ($q, $s) => $q->whereHas(
                'latestAssessment', fn ($a) => $a->where('quality_score', '>=', $s)
            ))
            ->when($request->sort === 'score_desc', fn ($q) => $q->orderByDesc(
                AiQualityAssessment::select('quality_score')
                    ->whereColumn('product_id', 'products.id')
                    ->latest('assessed_at')->limit(1)
            ))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->latest('products.id')
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
```

- [ ] **Step 4: Thêm route + cập nhật nav**

Trong `routes/web.php`, thêm (sau route `/danh-gia`):
```php
use App\Http\Controllers\MarketplaceController;

Route::get('/thi-truong', [MarketplaceController::class, 'index'])->name('marketplace.index');
```
Trong `config/navigation.php`, đổi **mọi** entry có `'key' => 'market'` từ `'href' => '#'` thành `'href' => '/thi-truong'` (top_links, drawer nhóm chính, bottom_nav 2 biến thể). Đổi luôn footer link "Nông sản" `'href' => '#'` → `'href' => '/thi-truong'`.

- [ ] **Step 5: Viết view danh sách**

Tạo `resources/views/marketplace/index.blade.php`. **Port khung từ `Test-Giao-Dien/agrisenai-thi-truong.html`** (thanh lọc + grid card) nhưng:
- `@extends('layouts.app', ['page' => 'market'])`, bọc nội dung trong `@section('content')`; CSS-riêng-của-trang đưa vào `@push('head')<style>` (giống trang AI đã làm), chrome lấy từ layout.
- Thanh lọc = form GET: `<select name="category_id">` (từ `$categories`), `<select name="sort">` (score_desc / price_asc / price_desc), `<input name="min_score" type="number">`. Submit giữ query string.
- Grid lặp `$products` — mỗi card:

```blade
{{-- Dung url() (khong route('products.show')) vi route do khai bao o Task 5;
     doi sang route('products.show', $p) sau khi Task 5 xong neu muon. --}}
@foreach ($products as $p)
    <a href="{{ url('/thi-truong/'.$p->id) }}" class="pcard">
        <div class="pcard-img">
            <img src="{{ $p->primaryImage ? asset($p->primaryImage->image_url) : asset('images/logo-agrisenai-mark.png') }}"
                 alt="{{ $p->name }}" onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
            <span class="pcard-badge">✓ AI chứng nhận · {{ (int) $p->latestAssessment->quality_score }}/100</span>
        </div>
        <div class="pcard-body">
            <div class="pcard-name">{{ $p->name }}</div>
            <div class="pcard-farm">{{ $p->farmer->farmerProfile?->farm_name ?? $p->farmer->name }}</div>
            @if ($p->farmer->farmerProfile?->ocop_star_rating)
                <div class="pcard-ocop">OCOP {{ str_repeat('⭐', $p->farmer->farmerProfile->ocop_star_rating) }}</div>
            @endif
            <div class="pcard-price">{{ number_format($p->price, 0, ',', '.') }}đ<span>/{{ $p->unit }}</span></div>
            <div class="pcard-rating">
                @if ($p->reviews_count)
                    ⭐ {{ number_format($p->reviews_avg_rating, 1) }} ({{ $p->reviews_count }})
                @else
                    <span style="color:#9e9e9e">Chưa có đánh giá</span>
                @endif
            </div>
        </div>
    </a>
@endforeach
```
- Dưới grid: `{{ $products->links() }}` (phân trang). Nếu rỗng: hiện dòng "Chưa có sản phẩm nào."
- Style card `.pcard*` port từ demo (clean e-commerce, mobile-first). Badge "AI chứng nhận" phải nổi bật (nền xanh brand).

- [ ] **Step 6: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=MarketplaceIndexTest`
Expected: PASS.

- [ ] **Step 7: Kiểm trình duyệt**

Run (đã seed ở Task 2): mở `http://agrisenai.test/thi-truong`.
Expected: lưới sản phẩm, mỗi card có badge "AI chứng nhận" + điểm; nav "Sàn thị trường" active.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/MarketplaceController.php resources/views/marketplace/index.blade.php routes/web.php config/navigation.php tests/Feature/MarketplaceIndexTest.php
git commit -m "Them trang san /thi-truong (danh sach san pham chung nhan)"
```

---

### Task 4: Lọc / sắp xếp / phân trang (nghiệm thu bộ lọc)

**Files:**
- Modify: `resources/views/marketplace/index.blade.php` (nếu cần chỉnh form) — logic đã ở controller Task 3
- Test: `tests/Feature/MarketplaceFilterTest.php`

**Interfaces:**
- Consumes: `MarketplaceController@index` (Task 3).

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/MarketplaceFilterTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_loc_theo_category(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->get('/thi-truong?category_id='.$p->category_id);

        $res->assertOk();
        $res->assertSee($p->name);
        // san pham khac category khong hien: lay 1 product khac category
        $other = Product::certified()->where('category_id', '!=', $p->category_id)->first();
        if ($other) {
            $res->assertDontSee($other->name);
        }
    }

    public function test_sort_price_asc_giu_query_khi_phan_trang(): void
    {
        $this->seed();
        $res = $this->get('/thi-truong?sort=price_asc');
        $res->assertOk();
        // link phan trang (neu co) phai chua sort=price_asc
        $res->assertSee('sort=price_asc', false);
    }

    public function test_loc_min_score(): void
    {
        $this->seed();
        $res = $this->get('/thi-truong?min_score=95');
        $res->assertOk();
        // chi con product diem >=95
        $low = Product::certified()->whereHas('latestAssessment', fn ($a) => $a->where('quality_score', '<', 95))->first();
        if ($low) {
            $res->assertDontSee('>'.$low->name.'<', false);
        }
    }
}
```

- [ ] **Step 2: Chạy test**

Run: `php artisan test --filter=MarketplaceFilterTest`
Expected: PASS nếu Task 3 controller đúng. Nếu `test_sort_price_asc_giu_query...` FAIL vì phân trang chỉ 1 trang (không có link) → sửa test: seed thêm >12 product HOẶC bỏ assert đó. Cụ thể: nếu ≤12 product certified thì `paginate(12)` không sinh link. Tăng seeder không thuộc task này → thay assert bằng: `$this->get('/thi-truong?sort=price_asc&page=1')->assertOk();` và assert thứ tự giá tăng dần bằng cách so vị trí 2 tên sản phẩm rẻ nhất/đắt nhất trong HTML.

Phiên bản assert thứ tự (thay `test_sort_price_asc...`):
```php
    public function test_sort_price_asc_dung_thu_tu(): void
    {
        $this->seed();
        $re = Product::certified()->orderBy('price')->first();
        $dat = Product::certified()->orderByDesc('price')->first();

        $html = $this->get('/thi-truong?sort=price_asc')->getContent();

        $this->assertLessThan(
            strpos($html, $dat->name),
            strpos($html, $re->name),
            'San pham re nhat phai xuat hien truoc san pham dat nhat khi sort=price_asc'
        );
    }
```

- [ ] **Step 3: Nếu cần, chỉnh form lọc trong view** cho khớp param (`category_id`, `sort`, `min_score`) và giữ giá trị đã chọn (`@selected`, `value=old`). Không đổi controller.

- [ ] **Step 4: Chạy lại test — kỳ vọng PASS**

Run: `php artisan test --filter=MarketplaceFilterTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/marketplace/index.blade.php tests/Feature/MarketplaceFilterTest.php
git commit -m "Nghiem thu loc/sort/phan trang trang san"
```

---

### Task 5: Trang chi tiết sản phẩm `/thi-truong/{product}`

**Files:**
- Modify: `app/Http/Controllers/MarketplaceController.php` (thêm `show`), `routes/web.php`
- Create: `resources/views/marketplace/show.blade.php`
- Test: `tests/Feature/MarketplaceShowTest.php`

**Interfaces:**
- Consumes: quan hệ Product (Task 1), seeder (Task 2).
- Produces: route tên `products.show` (`/thi-truong/{product}`), `MarketplaceController@show`.

> **Ghi chú phạm vi:** KHÔNG render khối QR ở task này (thuộc feature QR sau). Chi tiết chừa sẵn phần cuối "Truy xuất nguồn gốc" bằng traceLogs; QR sẽ cắm vào sau.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/MarketplaceShowTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_trang_chi_tiet_hien_diem_ai_va_nha_vuon(): void
    {
        $this->seed();
        $p = Product::certified()->with('latestAssessment')->first();

        $res = $this->get('/thi-truong/'.$p->id);

        $res->assertOk();
        $res->assertViewIs('marketplace.show');
        $res->assertSee($p->name);
        $res->assertSee((string) (int) $p->latestAssessment->quality_score);
        $res->assertSee($p->farmer->farmerProfile->farm_name);
    }

    public function test_product_khong_active_tra_404(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->update(['status' => 'hidden']);

        $this->get('/thi-truong/'.$p->id)->assertNotFound();
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=MarketplaceShowTest`
Expected: FAIL 404.

- [ ] **Step 3: Thêm `show` vào controller**

Thêm method vào `MarketplaceController`:
```php
    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $product->load([
            'images', 'category', 'farmer.farmerProfile',
            'assessments' => fn ($q) => $q->latest('assessed_at'),
            'traceLogs',
            'reviews.buyer',
        ]);

        return view('marketplace.show', [
            'product' => $product,
            'assessment' => $product->assessments->first(),
        ]);
    }
```

- [ ] **Step 4: Thêm route**

Trong `routes/web.php`, thêm ngay dưới route index:
```php
Route::get('/thi-truong/{product}', [MarketplaceController::class, 'show'])->name('products.show');
```

- [ ] **Step 5: Viết view chi tiết**

Tạo `resources/views/marketplace/show.blade.php`, `@extends('layouts.app', ['page' => 'market'])`. **Port khung từ `Test-Giao-Dien/agrisenai-chi-tiet-san-pham.html`** (2 cột desktop / 1 cột mobile), bind dữ liệu thật:
- **Ảnh:** gallery từ `$product->images` (fallback `images/logo-agrisenai-mark.png`).
- **Khối "AI chứng nhận chất lượng":** `$assessment->quality_score`/100; độ chín nhãn VN (map `xanh`=Xanh, `vua_chin`=Vừa chín, `chin`=Chín, `qua_chin`=Quá chín); nếu `$assessment->defect_detected` → hộp cảnh báo + `$assessment->defect_note`; thời điểm `$assessment->assessed_at->format('d/m/Y H:i')`; ghi "Đánh giá bởi AI Gemini Vision".
- **Nhà vườn:** `farm_name`, `region`, OCOP sao, `description`.
- **Giá + tồn kho:** `number_format($product->price,0,',','.')`đ /`unit`, `quantity_available`.
- **Nút "Thêm vào giỏ":** `<form method="POST" action="{{ route('cart.add', $product) }}">@csrf<input type="number" name="quantity" value="1" min="1">...</form>` (route có ở Task 6).
- **Truy xuất nguồn gốc:** timeline dọc `$product->traceLogs` — map stage → nhãn VN (thu_hoach=Thu hoạch, dong_goi=Đóng gói, van_chuyen=Vận chuyển, danh_gia_ai=Đánh giá AI, dang_ban=Đang bán) + `description`, `location`, `happened_at->format('d/m/Y')`.
- **Đánh giá:** danh sách `$product->reviews` — `buyer->name`, `str_repeat('⭐', $review->rating)`, `comment`.
- CSS-riêng qua `@push('head')`.

Vì nút "Thêm vào giỏ" trỏ `route('cart.add')` chưa tồn tại tới Task 6 → tạm để `<form method="POST" action="{{ url('/gio-hang/them/'.$product->id) }}">` (URL thẳng) để view render được ngay; đổi sang `route()` sau khi Task 6 khai báo route.

- [ ] **Step 6: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=MarketplaceShowTest`
Expected: PASS.

- [ ] **Step 7: Kiểm trình duyệt**

Mở `http://agrisenai.test/thi-truong` → bấm 1 card → trang chi tiết hiện đủ ảnh, điểm AI, nhà vườn, timeline, review.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/MarketplaceController.php resources/views/marketplace/show.blade.php routes/web.php tests/Feature/MarketplaceShowTest.php
git commit -m "Them trang chi tiet san pham /thi-truong/{product}"
```

---

### Task 6: Giỏ hàng session

**Files:**
- Create: `app/Http/Controllers/CartController.php`, `resources/views/marketplace/cart.blade.php`
- Modify: `routes/web.php`, `resources/views/marketplace/show.blade.php` (đổi form về `route('cart.add')`)
- Test: `tests/Feature/CartTest.php`

**Interfaces:**
- Consumes: `Product` (Task 1).
- Produces: routes `cart.add` (POST `/gio-hang/them/{product}`), `cart.index` (GET `/gio-hang`), `cart.update` (PATCH `/gio-hang/{product}`), `cart.remove` (DELETE `/gio-hang/{product}`). Session key `cart` = `[product_id => quantity]`.

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/CartTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_them_va_xem_gio(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 2])
            ->assertRedirect();

        $res = $this->get('/gio-hang');
        $res->assertOk();
        $res->assertSee($p->name);
        $res->assertSessionHas('cart', [$p->id => 2.0]);
    }

    public function test_cap_nhat_va_xoa(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 1]);

        $this->patch('/gio-hang/'.$p->id, ['quantity' => 5])->assertRedirect();
        $this->assertSame(5.0, session('cart')[$p->id]);

        $this->delete('/gio-hang/'.$p->id)->assertRedirect();
        $this->assertArrayNotHasKey($p->id, session('cart', []));
    }

    public function test_khong_them_product_khong_active(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $p->update(['status' => 'hidden']);

        $this->post('/gio-hang/them/'.$p->id, ['quantity' => 1])->assertNotFound();
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=CartTest`
Expected: FAIL (route chưa có).

- [ ] **Step 3: Viết `CartController`**

`app/Http/Controllers/CartController.php`:
```php
<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $qty = max(1, (float) $request->input('quantity', 1));
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã thêm vào giỏ');
    }

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->with('primaryImage')->get();
        $total = $products->sum(fn ($p) => $p->price * ($cart[$p->id] ?? 0));

        return view('marketplace.cart', compact('products', 'cart', 'total'));
    }

    public function update(Request $request, Product $product)
    {
        $qty = (float) $request->input('quantity', 1);
        $cart = $request->session()->get('cart', []);
        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $qty;
        }
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã cập nhật giỏ');
    }

    public function remove(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('ok', 'Đã xoá khỏi giỏ');
    }
}
```

- [ ] **Step 4: Thêm routes**

Trong `routes/web.php`:
```php
use App\Http\Controllers\CartController;

Route::post('/gio-hang/them/{product}', [CartController::class, 'add'])->name('cart.add');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}', [CartController::class, 'remove'])->name('cart.remove');
```
Cập nhật `config/navigation.php`: entry `'key' => 'cart'` `'#'` → `'/gio-hang'`.
Sửa `marketplace/show.blade.php`: form thêm giỏ đổi `action="{{ url('/gio-hang/them/'.$product->id) }}"` → `action="{{ route('cart.add', $product) }}"`.

- [ ] **Step 5: Viết view giỏ hàng**

Tạo `resources/views/marketplace/cart.blade.php`, `@extends('layouts.app', ['page' => 'cart'])`. **Port khung từ `Test-Giao-Dien/agrisenai-gio-hang.html`**:
- Bảng/list các `$products`: ảnh (`primaryImage`, fallback logo), tên, giá, ô số lượng (form PATCH `route('cart.update', $p)` với `@method('PATCH')`), nút xoá (form DELETE `route('cart.remove', $p)` với `@method('DELETE')`), thành tiền `price * $cart[$p->id]`.
- Tổng tạm tính `number_format($total,0,',','.')`đ.
- Nút "Tiến hành đặt hàng" → dùng `url('/thanh-toan')` (route `checkout.form` khai báo ở Task 7; đổi sang `route('checkout.form')` sau nếu muốn).
- Giỏ rỗng: hiện "Giỏ hàng trống" + link về `/thi-truong`.
- Hiện `session('ok')` nếu có (flash).

- [ ] **Step 6: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=CartTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/CartController.php resources/views/marketplace/cart.blade.php resources/views/marketplace/show.blade.php routes/web.php config/navigation.php tests/Feature/CartTest.php
git commit -m "Them gio hang session (them/sua/xoa)"
```

---

### Task 7: Checkout mockup (tạo order + order_items)

**Files:**
- Create: `app/Http/Controllers/CheckoutController.php`, `resources/views/marketplace/checkout.blade.php`, `resources/views/marketplace/order-confirm.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CheckoutTest.php`

**Interfaces:**
- Consumes: session `cart`, `Product`, `Order`, `OrderItem`, `User` (buyer demo email `muademo@demo.test`).
- Produces: routes `checkout.form` (GET `/thanh-toan`), `checkout.place` (POST `/thanh-toan`), `order.show` (GET `/don-hang/{order:order_code}`).

- [ ] **Step 1: Viết test thất bại**

```php
<?php
// tests/Feature/CheckoutTest.php
namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_dat_hang_tao_order_va_order_items(): void
    {
        $this->seed();
        $p = Product::certified()->first();

        $res = $this->withSession(['cart' => [$p->id => 3.0]])
            ->post('/thanh-toan', [
                'shipping_address' => '12 Nguyễn Huệ, Cao Lãnh',
                'note' => 'Giao giờ hành chính',
            ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertEqualsWithDelta((float) $p->price * 3, (float) $order->total_amount, 0.01);
        $this->assertSame(1, $order->items()->count());
        $res->assertRedirect(route('order.show', $order->order_code));
    }

    public function test_gio_rong_khong_dat_duoc(): void
    {
        $this->seed();
        $this->withSession(['cart' => []])
            ->post('/thanh-toan', ['shipping_address' => 'x'])
            ->assertStatus(400);
    }

    public function test_thieu_dia_chi_bao_loi(): void
    {
        $this->seed();
        $p = Product::certified()->first();
        $this->withSession(['cart' => [$p->id => 1.0]])
            ->post('/thanh-toan', [])
            ->assertSessionHasErrors('shipping_address');
    }
}
```

- [ ] **Step 2: Chạy test — kỳ vọng FAIL**

Run: `php artisan test --filter=CheckoutTest`
Expected: FAIL (route chưa có).

- [ ] **Step 3: Viết `CheckoutController`**

`app/Http/Controllers/CheckoutController.php`:
```php
<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function form(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();
        $total = $products->sum(fn ($p) => $p->price * ($cart[$p->id] ?? 0));

        return view('marketplace.checkout', compact('products', 'cart', 'total'));
    }

    public function placeOrder(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        abort_if(empty($cart), 400, 'Giỏ hàng trống');

        $data = $request->validate([
            'shipping_address' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        // buyer_id NOT NULL: chua login -> fallback ve user nguoi mua demo
        $buyerId = auth()->id() ?? User::where('email', 'muademo@demo.test')->value('id');

        $order = DB::transaction(function () use ($cart, $products, $data, $buyerId) {
            $total = 0;
            foreach ($cart as $pid => $qty) {
                $total += ($products[$pid]->price ?? 0) * $qty;
            }

            $order = Order::create([
                'buyer_id' => $buyerId,
                'order_code' => 'DH'.now()->format('ymd').strtoupper(Str::random(5)),
                'total_amount' => $total,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($cart as $pid => $qty) {
                if (! isset($products[$pid])) {
                    continue;
                }
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'unit_price' => $products[$pid]->price,
                    'subtotal' => $products[$pid]->price * $qty,
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('order.show', $order->order_code);
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('marketplace.order-confirm', compact('order'));
    }
}
```

- [ ] **Step 4: Thêm routes**

Trong `routes/web.php`:
```php
use App\Http\Controllers\CheckoutController;

Route::get('/thanh-toan', [CheckoutController::class, 'form'])->name('checkout.form');
Route::post('/thanh-toan', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
Route::get('/don-hang/{order:order_code}', [CheckoutController::class, 'show'])->name('order.show');
```

- [ ] **Step 5: Viết 2 view**

`resources/views/marketplace/checkout.blade.php` (`@extends('layouts.app', ['page' => 'cart'])`):
- Tóm tắt `$products` + số lượng + tổng `$total`.
- Form POST `route('checkout.place')`: `@csrf`, `<textarea name="shipping_address">` (bắt buộc, hiện `@error`), `<textarea name="note">` (tuỳ chọn), nút "Đặt hàng".
- Dòng ghi rõ: "Đây là luồng demo — chưa tích hợp thanh toán/giao vận thật."

`resources/views/marketplace/order-confirm.blade.php` (`@extends('layouts.app', ['page' => 'cart'])`):
- Tiêu đề "Đặt hàng thành công", `$order->order_code`, trạng thái "Chờ xác nhận".
- Bảng `$order->items` (tên `item->product->name`, số lượng, đơn giá, thành tiền), tổng `$order->total_amount`.
- Nút "Tiếp tục mua" → `route('marketplace.index')`.

- [ ] **Step 6: Chạy test — kỳ vọng PASS**

Run: `php artisan test --filter=CheckoutTest`
Expected: PASS.

- [ ] **Step 7: Chạy toàn bộ suite**

Run: `php artisan test`
Expected: tất cả xanh (bao gồm các test chrome/layout cũ + 6 test mới của marketplace).

- [ ] **Step 8: Kiểm trình duyệt end-to-end**

`/thi-truong` → chi tiết → thêm giỏ → `/gio-hang` → đặt hàng → trang xác nhận có order_code.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/CheckoutController.php resources/views/marketplace/checkout.blade.php resources/views/marketplace/order-confirm.blade.php routes/web.php tests/Feature/CheckoutTest.php
git commit -m "Them checkout mockup (tao order + order_items)"
```

---

## Nghiệm thu tổng (Definition of Done)

- [ ] `/thi-truong` hiện lưới sản phẩm, MỌI item có badge "AI chứng nhận" + điểm.
- [ ] Lọc category / sort điểm-giá / min_score hoạt động, giữ query khi phân trang.
- [ ] Eager-load đủ (không N+1): index dùng `with([...])->withAvg->withCount`.
- [ ] Chi tiết hiện: ảnh, điểm AI + độ chín + cảnh báo lỗi, nhà vườn + OCOP, timeline traceability, reviews. (QR để feature sau.)
- [ ] Thêm/sửa/xoá giỏ (session) hoạt động.
- [ ] Checkout tạo `orders` + `order_items`, `order_code` unique, status='pending'.
- [ ] `php artisan migrate:fresh --seed` ra sàn đầy dữ liệu (≥8 product certified).
- [ ] `php artisan test` toàn xanh.
- [ ] Mobile-first: sàn + chi tiết + giỏ đọc tốt trên điện thoại (dùng chrome `layouts.app`).

## Việc để feature kế tiếp (QR truy xuất — spec `2026-07-21-qr-traceability.md`)

- Migration +2 cột `ai_quality_assessments` (`image_sha256`, `integrity_hash`).
- `TraceabilityService` + integrity hash, `simple-qrcode`.
- Route `/trace/{product:qr_code}` + `TraceController` + view `trace/show.blade.php`.
- Cắm khối QR vào `marketplace/show.blade.php` (đã chừa chỗ ở Task 5).
- Ảnh sản phẩm thật đặt vào `public/images/products/` (seeder đang trỏ path quy ước + view có fallback).
