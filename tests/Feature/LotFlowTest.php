<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Luong 5 buoc KHONG can AI (luoi an toan khi Gemini sap luc thi):
 * tao lo -> anh -> nhap tay 5 tieu chi -> cong bo -> trang QR xanh; sua DB -> do.
 */
class LotFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->staff = User::factory()->create(['name' => 'Nguyễn Văn Tâm', 'org_name' => 'Vựa xoài Mỹ Xương']);
    }

    /** Anh that co chi tiet (anh phang mau cua UploadedFile::fake bi coi la mo). */
    public static function realPhoto(string $name = '03_hang_1.jpg'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'up_').'.jpg';
        copy(storage_path("app/reference/xoai/{$name}"), $tmp);

        return new UploadedFile($tmp, 'anh.jpg', 'image/jpeg', null, true);
    }

    public static function manualItems(): array
    {
        return [
            'color' => ['decision' => 'manual', 'value' => 'vang'],
            'ripeness' => ['decision' => 'manual', 'value' => 'chin_toi'],
            'surface' => ['decision' => 'manual', 'value' => 'khuyet_tat_nhe', 'defect_types' => ['nam_nang'], 'note' => 'Mảng nâu ~5%'],
            'shape' => ['decision' => 'manual', 'value' => 'binh_thuong'],
            'uniformity' => ['decision' => 'manual', 'value' => 'dong_deu'],
        ];
    }

    private function createLot(): Lot
    {
        $this->actingAs($this->staff)->post('/lo', [
            'supplier' => 'Vườn ông Sáu Tâm',
            'variety' => 'Cát Hòa Lộc',
            'purpose' => 'an_chin',
            'weight_kg' => 420,
            'harvest_date' => now()->subDay()->toDateString(),
        ])->assertRedirect();

        return Lot::latest('id')->first();
    }

    private function publishedLot(): Lot
    {
        $lot = $this->createLot();
        $this->post("/lo/{$lot->code}/anh", ['anh' => self::realPhoto(), 'view' => 'toan_qua'])->assertSessionHasNoErrors();
        $this->post("/lo/{$lot->code}/xac-nhan", ['items' => self::manualItems()])->assertSessionHasNoErrors();
        $this->post("/lo/{$lot->code}/cong-bo")->assertSessionHasNoErrors();

        return $lot->fresh();
    }

    public function test_tao_lo_cap_ma_va_ghi_audit(): void
    {
        $lot = $this->createLot();

        $this->assertMatchesRegularExpression('/^XL-\d{6}-[A-Z0-9]{4}$/', $lot->code);
        $this->assertSame('draft', $lot->status);
        $this->assertSame(['created'], $lot->auditLogs()->pluck('action')->all());
    }

    public function test_tao_lo_bat_buoc_muc_dich_ban_va_khoi_luong_duong(): void
    {
        $this->actingAs($this->staff)->post('/lo', ['supplier' => 'X', 'weight_kg' => 0, 'harvest_date' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors(['purpose', 'weight_kg', 'harvest_date']);
    }

    public function test_anh_duoc_chuan_hoa_bam_sha_va_kiem_tra_chat_luong(): void
    {
        $lot = $this->createLot();

        $this->post("/lo/{$lot->code}/anh", ['anh' => self::realPhoto(), 'view' => 'toan_qua'])->assertSessionHas('ok');

        $photo = $lot->photos()->sole();
        $this->assertTrue($photo->isUsable(), json_encode($photo->quality));
        Storage::disk('public')->assertExists($photo->path);
        $this->assertSame($photo->sha256, hash('sha256', Storage::disk('public')->get($photo->path)));
        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($photo->path));
        $this->assertSame([768, 768], [$w, $h]);
    }

    public function test_anh_mo_van_luu_nhung_bi_danh_dau_khong_dat(): void
    {
        $lot = $this->createLot();

        $this->post("/lo/{$lot->code}/anh", ['anh' => UploadedFile::fake()->image('phang.jpg', 800, 800), 'view' => 'toan_qua'])
            ->assertSessionHas('warn');

        $this->assertFalse($lot->photos()->sole()->isUsable());
    }

    public function test_xac_nhan_thieu_tieu_chi_bi_tu_choi(): void
    {
        $lot = $this->createLot();
        $items = self::manualItems();
        unset($items['shape']);
        $items['color']['value'] = 'tim';

        $this->post("/lo/{$lot->code}/xac-nhan", ['items' => $items])
            ->assertSessionHasErrors(['items.shape.decision', 'items.color.value']);
        $this->assertSame(0, $lot->labels()->count());
    }

    public function test_khong_co_goi_y_ai_thi_khong_cho_dong_y(): void
    {
        $lot = $this->createLot();
        $items = self::manualItems();
        $items['color'] = ['decision' => 'accept'];

        $this->post("/lo/{$lot->code}/xac-nhan", ['items' => $items])->assertSessionHasErrors('items.color.decision');
    }

    public function test_chua_xac_nhan_thi_chua_cong_bo_duoc(): void
    {
        $lot = $this->createLot();

        $this->post("/lo/{$lot->code}/cong-bo")->assertSessionHasErrors('lot');
        $this->assertSame('draft', $lot->fresh()->status);
    }

    public function test_tron_luong_thu_cong_den_trang_qr_nguyen_ven(): void
    {
        $lot = $this->publishedLot();

        $this->assertSame('published', $lot->status);
        $this->assertSame(['created', 'photo_added', 'confirmed', 'published'], $lot->auditLogs()->pluck('action')->all());
        $this->assertSame(5, $lot->labels()->where('decision', 'manual')->count());

        auth()->logout();
        $this->get("/ho-so/{$lot->code}")
            ->assertOk()
            ->assertSee('Hồ sơ nguyên vẹn')
            ->assertSee('Nguyễn Văn Tâm')
            ->assertSee('Vựa xoài Mỹ Xương')
            ->assertSee('Khuyết tật nhẹ')
            ->assertSee('Nám nắng')
            ->assertDontSee('Hạng I')
            ->assertDontSee('overall_score');

        $this->get("/ho-so/{$lot->code}/qr")->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_lo_da_cong_bo_bi_khoa(): void
    {
        $lot = $this->publishedLot();

        $this->post("/lo/{$lot->code}/anh", ['anh' => self::realPhoto(), 'view' => 'be_mat'])->assertSessionHasErrors('lot');
        $this->post("/lo/{$lot->code}/xac-nhan", ['items' => self::manualItems()])->assertSessionHasErrors('lot');
        $this->assertSame(1, $lot->photos()->count());
    }

    public function test_sua_ket_luan_trong_db_thi_trang_qr_do(): void
    {
        $lot = $this->publishedLot();
        DB::table('verified_labels')->where('criterion', 'surface')->update(['final_value' => json_encode(['value' => 'sach'])]);

        $this->get("/ho-so/{$lot->code}")->assertSee('hồ sơ đã bị thay đổi')->assertSee('Kết luận trong hệ thống khác');
    }

    public function test_sua_nhat_ky_thi_trang_qr_do(): void
    {
        $lot = $this->publishedLot();
        $log = $lot->auditLogs()->where('action', 'confirmed')->sole();
        DB::table('lot_audit_logs')->where('id', $log->id)->update(['payload' => str_replace('khuyet_tat_nhe', 'sach', $log->payload)]);

        $this->get("/ho-so/{$lot->code}")->assertSee('hồ sơ đã bị thay đổi')->assertSee('Nhật ký bị sửa');
    }

    public function test_doi_anh_tren_dia_thi_trang_qr_do(): void
    {
        $lot = $this->publishedLot();
        Storage::disk('public')->put($lot->photos()->sole()->path, 'anh khac');

        $this->get("/ho-so/{$lot->code}")->assertSee('Ảnh không khớp mã băm');
    }

    public function test_xoa_anh_ghi_audit_xoa_file_va_dua_lo_ve_nhap(): void
    {
        $lot = $this->createLot();
        $this->post("/lo/{$lot->code}/anh", ['anh' => self::realPhoto(), 'view' => 'toan_qua']);
        $this->post("/lo/{$lot->code}/xac-nhan", ['items' => self::manualItems()]);
        $photo = $lot->photos()->sole();

        $this->delete("/lo/{$lot->code}/anh/{$photo->id}")->assertSessionHas('ok');

        $this->assertSame(0, $lot->photos()->count());
        Storage::disk('public')->assertMissing($photo->path);
        $this->assertSame('draft', $lot->fresh()->status);
        $this->assertSame('photo_removed', $lot->auditLogs()->get()->last()->action);
        $this->assertSame($photo->sha256, $lot->auditLogs()->get()->last()->data()['sha256']);
    }

    public function test_khong_xoa_duoc_anh_cua_lo_da_cong_bo_hay_lo_khac(): void
    {
        $lot = $this->publishedLot();
        $photo = $lot->photos()->sole();
        $this->delete("/lo/{$lot->code}/anh/{$photo->id}")->assertSessionHasErrors('lot');
        Storage::disk('public')->assertExists($photo->path);

        $other = $this->createLot();
        $this->delete("/lo/{$other->code}/anh/{$photo->id}")->assertNotFound();

        $this->actingAs(User::factory()->create(['org_name' => 'HTX khác']))
            ->delete("/lo/{$other->code}/anh/{$photo->id}")->assertForbidden();
    }

    public function test_audit_log_khong_sua_xoa_qua_model(): void
    {
        $log = $this->createLot()->auditLogs()->first();

        $this->expectException(\LogicException::class);
        $log->update(['action' => 'x']);
    }

    public function test_lo_chua_cong_bo_khong_xem_cong_khai(): void
    {
        $lot = $this->createLot();

        $this->get("/ho-so/{$lot->code}")->assertNotFound();
        $this->get('/tra-cuu?ma='.$lot->code)->assertSee('Không tìm thấy hồ sơ');
    }

    public function test_tra_cuu_ma_lo_chuyen_den_ho_so(): void
    {
        $lot = $this->publishedLot();

        $this->get('/tra-cuu?ma='.strtolower($lot->code))->assertRedirect("/ho-so/{$lot->code}");
    }

    public function test_nhan_vien_to_chuc_khac_khong_xem_duoc_lo(): void
    {
        $lot = $this->createLot();
        $khac = User::factory()->create(['org_name' => 'HTX Xoài Cao Lãnh']);

        $this->actingAs($khac)->get("/lo/{$lot->code}")->assertForbidden();
        $this->actingAs($khac)->get('/lo')->assertDontSee($lot->code);
        $this->actingAs(User::factory()->admin()->create())->get("/lo/{$lot->code}")->assertOk();
    }

    public function test_tem_qr_chi_in_khi_da_cong_bo(): void
    {
        $lot = $this->createLot();
        $this->get("/lo/{$lot->code}/tem")->assertNotFound();

        $lot = $this->publishedLot();
        $this->get("/lo/{$lot->code}/tem")->assertOk()->assertSee($lot->code)->assertSee('<svg', false);
    }

    public function test_man_hinh_chi_tiet_lo_hien_du_5_buoc(): void
    {
        $lot = $this->createLot();

        $this->get("/lo/{$lot->code}")->assertOk()
            ->assertSee('Ảnh chuẩn hoá')->assertSee('AI gợi ý ngoại quan')
            ->assertSee('Người phụ trách xác nhận')->assertSee('Công bố & tem QR', false)
            ->assertSee('data-photo-capture', false);
    }
}
