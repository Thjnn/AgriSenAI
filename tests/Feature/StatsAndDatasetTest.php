<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\User;
use App\Services\AuditChain;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class StatsAndDatasetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seeder_demo_tao_3_lo_cong_bo_deu_nguyen_ven(): void
    {
        $published = Lot::where('status', 'published')->get();

        $this->assertCount(3, $published);
        foreach ($published as $lot) {
            $this->assertTrue(app(AuditChain::class)->verify($lot)['ok'], $lot->code);
            $this->assertTrue($lot->photos->every->isUsable(), $lot->code);
        }
        $this->assertSame(1, Lot::where('status', 'draft')->count());
    }

    public function test_trang_chu_hien_ho_so_moi_cong_bo(): void
    {
        $this->get('/')->assertOk()->assertSee('Hồ sơ mới công bố')->assertSee(Lot::where('status', 'published')->first()->code);
    }

    public function test_chi_admin_xem_thong_ke(): void
    {
        $this->actingAs(User::where('phone', '0911111111')->sole())->get('/thong-ke')->assertForbidden();

        $this->actingAs(User::where('phone', '0900000000')->sole())->get('/thong-ke')
            ->assertOk()->assertSee('Thời gian lập hồ sơ')->assertSee('Gợi ý AI được người dùng chấp nhận');
    }

    public function test_xuat_dataset_zip_co_anh_va_labels_jsonl(): void
    {
        $res = $this->actingAs(User::where('phone', '0900000000')->sole())->get('/thong-ke/dataset');
        $res->assertOk();

        $zip = new ZipArchive;
        $zip->open($res->baseResponse->getFile()->getPathname());
        $lines = array_filter(explode("\n", $zip->getFromName('labels.jsonl')));
        $first = json_decode($lines[0], true);

        $this->assertCount(3, $lines);
        $this->assertArrayHasKey('surface', $first['labels']);
        $this->assertNotFalse($zip->locateName($first['photos'][0]['file']));
        $zip->close();
    }
}
