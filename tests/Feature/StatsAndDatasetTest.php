<?php

namespace Tests\Feature;

use App\Models\AiSuggestion;
use App\Models\Lot;
use App\Models\User;
use App\Models\VerifiedLabel;
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

    public function test_kpi_hien_tu_so_mau_so(): void
    {
        $pub = Lot::where('status', 'published')->count();
        $total = Lot::count();

        $this->actingAs(User::factory()->admin()->create())->get('/thong-ke')
            ->assertSee('Tỷ lệ hồ sơ hoàn tất')
            ->assertSee("{$pub}/{$total}");
    }

    public function test_thong_ke_theo_phien_ban_prompt_va_lan_sua_nhieu_nhat(): void
    {
        $lot = Lot::where('status', 'draft')->sole();
        $admin = User::where('phone', '0900000000')->sole();
        $label = fn ($sug, $decision, $ai, $final) => VerifiedLabel::create([
            'lot_id' => $lot->id, 'suggestion_id' => $sug->id, 'criterion' => 'ripeness', 'decision' => $decision,
            'ai_value' => ['value' => $ai], 'final_value' => ['value' => $final], 'confirmed_by' => $admin->id, 'confirmed_at' => now(),
        ]);
        $sug = fn ($v) => AiSuggestion::create(['lot_id' => $lot->id, 'model' => 'm', 'prompt_version' => $v, 'photo_ids' => [], 'result' => []]);

        $old = $sug('v1-test');
        $label($old, 'edit', 'chin_toi', 'chin_ky');
        $label($old, 'edit', 'chin_toi', 'chin_ky');
        $new = $sug('v2-test');
        $label($new, 'accept', 'chin_toi', 'chin_toi');
        $label($new, 'accept', 'chin_toi', 'chin_toi');

        $this->actingAs($admin)->get('/thong-ke')
            ->assertSeeInOrder(['v2-test', '100%', 'v1-test', '0%'])
            ->assertSee('Chín tới → <b>Chín kỹ</b>', false)
            ->assertSee('2 lần');
    }
}
