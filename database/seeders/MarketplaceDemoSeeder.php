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
use App\Services\TraceabilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            ['Sáu Tâm', 'Vườn xoài ông Sáu Tâm', 5, 'Cao Lãnh'],
            ['Bảy Hoa', 'Vườn xoài cô Bảy Hoa', 4, 'Tháp Mười'],
            ['HTX Mỹ Xương', 'HTX Xoài Mỹ Xương', 5, 'Mỹ Xương'],
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
        // Du an tap trung vao xoai: chia theo muc dich ban (khop agrisenai.muc_dich_ban).
        foreach (['Xoài ăn chín' => 'xoai', 'Xoài ăn xanh' => 'xoai-xanh'] as $ten => $slug) {
            $cats[$slug] = Category::create(['name' => $ten, 'slug' => $slug])->id;
        }

        // ~10 product active + qr + anh + assessment + traceLogs + reviews
        $buyerId = User::where('email', 'muademo@demo.test')->value('id');

        // Anh mau that de assessment co the verify duoc (demo integrity).
        $anhMauPath = storage_path('app/reference/xoai/03_hang_1.jpg');
        $anhMauBytes = is_file($anhMauPath) ? file_get_contents($anhMauPath) : 'anh-demo';
        $svc = app(TraceabilityService::class);
        $data = [
            ['Xoài Cát Hòa Lộc loại 1', 'xoai', 45000, 'kg', 96, 'chin'],
            ['Xoài Cát Chu Mỹ Xương', 'xoai', 30000, 'kg', 90, 'chin'],
            ['Xoài Cát Hòa Lộc xuất khẩu', 'xoai', 60000, 'kg', 97, 'chin'],
            ['Xoài Tứ Quý', 'xoai', 28000, 'kg', 88, 'vua_chin'],
            ['Xoài Đài Loan', 'xoai', 25000, 'kg', 86, 'chin'],
            ['Xoài Keo ăn xanh', 'xoai-xanh', 18000, 'kg', 85, 'xanh'],
            ['Xoài tượng da xanh', 'xoai-xanh', 22000, 'kg', 78, 'xanh'],
            ['Xoài Thanh Ca', 'xoai-xanh', 20000, 'kg', 84, 'xanh'],
            ['Xoài Cát Chu loại 2', 'xoai', 21000, 'kg', 79, 'vua_chin'],
            ['Xoài Úc Đồng Tháp', 'xoai', 35000, 'kg', 92, 'chin'],
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

            $relImg = 'products/'.$p->id.'.jpg';
            Storage::disk('public')->put($relImg, $anhMauBytes);

            $a = new AiQualityAssessment();
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
