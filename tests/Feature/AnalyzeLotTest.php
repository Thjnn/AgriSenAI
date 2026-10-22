<?php

namespace Tests\Feature;

use App\Models\AiSuggestion;
use App\Models\Lot;
use App\Models\User;
use App\Services\LotWorkflow;
use App\Services\Quality\FewShotPromptBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** AI goi y + HITL. Khong goi mang that: Http::fake thay Gemini. */
class AnalyzeLotTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Lot $lot;

    public static function aiResult(array $override = []): array
    {
        return array_replace_recursive([
            'usable' => true,
            'unusable_reason' => null,
            'detected_product' => 'Xoài',
            'bad_photos' => [],
            'color' => ['value' => 'vang', 'distribution' => [['name' => 'vàng', 'percent' => 90]], 'note' => 'Vàng đều'],
            'ripeness' => ['value' => 'chin_toi', 'matches_purpose' => true, 'note' => 'Chín tới'],
            'surface' => ['value' => 'khuyet_tat_nhe', 'defects' => [
                ['type' => 'xuoc_vo', 'severity' => 'nhe', 'area_percent' => 2, 'location' => 'gần cuống', 'into_flesh' => false],
            ], 'note' => 'Một vết xước nông gần cuống'],
            'shape' => ['value' => 'binh_thuong', 'note' => 'Cân đối'],
            'uniformity' => ['value' => 'dong_deu', 'note' => 'Chỉ thấy một quả'],
            'confidence' => 0.82,
        ], $override);
    }

    private function fakeGemini(array $result): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($result)]]]]],
        ])]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test']);

        $this->staff = User::factory()->create();
        $wf = app(LotWorkflow::class);
        $this->lot = $wf->create(['supplier' => 'Vườn A', 'purpose' => 'an_xanh', 'weight_kg' => 100, 'harvest_date' => now()->toDateString()], $this->staff);
        $wf->addPhoto($this->lot, storage_path('app/reference/xoai/02_dac_biet_xanh.jpg'), 'toan_qua', $this->staff);
        $this->actingAs($this->staff);
    }

    public function test_bam_ai_goi_y_luu_suggestion_va_chuyen_trang_thai(): void
    {
        $this->fakeGemini(self::aiResult());

        $this->post("/lo/{$this->lot->code}/phan-tich")->assertRedirect();

        $sugg = AiSuggestion::sole();
        $this->assertNull($sugg->error);
        $this->assertSame('vang', $sugg->result['color']['value']);
        $this->assertSame('gemini-test', $sugg->model);
        $this->assertSame(config('agrisenai.prompt_version'), $sugg->prompt_version);
        $this->assertSame('analyzed', $this->lot->fresh()->status);
        $this->assertContains('ai_suggested', $this->lot->auditLogs()->pluck('action')->all());

        $this->getJson("/lo/{$this->lot->code}/phan-tich")->assertJson(['status' => 'analyzed']);
    }

    public function test_prompt_gui_muc_dich_ban_va_anh_lo_kem_few_shot(): void
    {
        $this->fakeGemini(self::aiResult());

        app(LotWorkflow::class)->analyze($this->lot);

        Http::assertSent(function (Request $req) {
            $contents = $req['contents'];
            $last = end($contents);

            return $req->hasHeader('x-goog-api-key', 'test-key')
                && count($contents) === 13                          // 6 cap few-shot + anh lo
                && str_contains($last['parts'][0]['text'], 'an_xanh')
                && $last['parts'][1]['text'] === 'Ảnh 1:'
                && isset($last['parts'][2]['inline_data'])
                && ! str_contains(json_encode($req['generationConfig']['responseSchema']), 'overall_score');
        });
    }

    public function test_gemini_loi_van_ghi_lai_va_cho_nhap_tay(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'API key not valid']], 400)]);

        app(LotWorkflow::class)->analyze($this->lot);

        $sugg = AiSuggestion::sole();
        $this->assertNull($sugg->result);
        $this->assertStringContainsString('API key not valid', $sugg->error);

        $this->get("/lo/{$this->lot->code}")->assertSee('AI không trả lời được')->assertSee('nhập thủ công');
        $this->post("/lo/{$this->lot->code}/xac-nhan", ['items' => LotFlowTest::manualItems()])->assertSessionHasNoErrors();
    }

    public function test_dong_y_lay_dung_gia_tri_ai_sua_va_bac_bo_ghi_dung(): void
    {
        $this->fakeGemini(self::aiResult());
        app(LotWorkflow::class)->analyze($this->lot);

        $this->post("/lo/{$this->lot->code}/xac-nhan", ['items' => [
            'color' => ['decision' => 'accept'],
            'ripeness' => ['decision' => 'edit', 'value' => 'xanh_gia', 'note' => 'Bán ăn xanh'],
            'surface' => ['decision' => 'accept'],
            'shape' => ['decision' => 'reject'],
            'uniformity' => ['decision' => 'accept'],
        ]])->assertSessionHasNoErrors();

        $labels = $this->lot->currentLabels();
        $this->assertSame(['value' => 'vang', 'note' => 'Vàng đều'], $labels['color']->final_value);
        $this->assertSame(['xuoc_vo'], $labels['surface']->final_value['defect_types']);
        $this->assertSame('xanh_gia', $labels['ripeness']->final_value['value']);
        $this->assertSame('chin_toi', $labels['ripeness']->ai_value['value']);
        $this->assertNull($labels['shape']->final_value);
        $this->assertNotNull($labels['color']->suggestion_id);
        $this->assertSame('confirmed', $this->lot->fresh()->status);
    }

    public function test_sua_ma_chon_gia_tri_ngoai_danh_sach_bi_tu_choi(): void
    {
        $this->fakeGemini(self::aiResult());
        app(LotWorkflow::class)->analyze($this->lot);
        $items = LotFlowTest::manualItems();
        $items['shape'] = ['decision' => 'edit', 'value' => 'tron_vo'];

        $this->post("/lo/{$this->lot->code}/xac-nhan", ['items' => $items])->assertSessionHasErrors('items.shape.value');
    }

    public function test_anh_khong_dung_duoc_thi_khong_co_goi_y_de_dong_y(): void
    {
        $this->fakeGemini(['usable' => false, 'unusable_reason' => 'Ảnh không phải xoài', 'detected_product' => 'Chuối', 'confidence' => 0.9]);
        app(LotWorkflow::class)->analyze($this->lot);

        $this->get("/lo/{$this->lot->code}")->assertSee('Ảnh không phải xoài');

        $items = LotFlowTest::manualItems();
        $items['color'] = ['decision' => 'accept'];
        $this->post("/lo/{$this->lot->code}/xac-nhan", ['items' => $items])->assertSessionHasErrors('items.color.decision');
    }

    public function test_ai_danh_dau_tung_anh_hong_ma_van_goi_y_tu_anh_con_lai(): void
    {
        $wf = app(LotWorkflow::class);
        $man = $wf->addPhoto($this->lot, storage_path('app/reference/xoai/03_hang_1.jpg'), 'be_mat', $this->staff);
        $this->fakeGemini(self::aiResult(['bad_photos' => [['index' => 2, 'reason' => 'Ảnh chụp màn hình máy tính']]]));
        $sugg = $wf->analyze($this->lot);

        $this->assertSame([$man->id => 'Ảnh chụp màn hình máy tính'], $sugg->badPhotos());
        $this->get("/lo/{$this->lot->code}")
            ->assertSee('AI: Ảnh chụp màn hình máy tính')
            ->assertSee('AI đã bỏ qua 1 ảnh')
            ->assertSee('id="acceptAll"', false);
    }

    public function test_ca_lo_khong_dung_duoc_thi_danh_dau_moi_anh_da_gui(): void
    {
        $this->fakeGemini(['usable' => false, 'unusable_reason' => 'Ảnh chụp màn hình', 'detected_product' => '?', 'bad_photos' => [], 'confidence' => 1]);
        $sugg = app(LotWorkflow::class)->analyze($this->lot);

        $this->assertSame($sugg->photo_ids, array_keys($sugg->badPhotos()));
    }

    public function test_do_tu_tin_thap_thi_an_nut_dong_y_tat_ca(): void
    {
        $this->fakeGemini(self::aiResult(['confidence' => 0.4]));
        app(LotWorkflow::class)->analyze($this->lot);

        $this->get("/lo/{$this->lot->code}")->assertDontSee('id="acceptAll"', false)->assertSee('thấp, hãy xem kỹ');
    }

    public function test_khong_co_anh_dat_thi_khong_goi_ai(): void
    {
        Http::fake();
        $lot = app(LotWorkflow::class)->create(['supplier' => 'B', 'purpose' => 'an_chin', 'weight_kg' => 5, 'harvest_date' => now()->toDateString()], $this->staff);

        $this->post("/lo/{$lot->code}/phan-tich")->assertSessionHas('warn');

        Http::assertNothingSent();
        $this->assertSame('draft', $lot->fresh()->status);
    }

    public function test_schema_va_trang_khong_con_diem_hay_hang(): void
    {
        $schema = json_encode(app(FewShotPromptBuilder::class)->responseSchema());
        foreach (['grade', 'overall_score', 'scores'] as $bo) {
            $this->assertStringNotContainsString("\"{$bo}\"", $schema);
        }

        $labels = file_get_contents(storage_path('app/reference/xoai/labels.json'));
        $this->assertStringNotContainsString('"overall_score"', $labels);
        $this->assertStringNotContainsString('"grade"', $labels);

        $this->assertStringNotContainsString('Hạng', app(FewShotPromptBuilder::class)->systemInstruction());
    }

    public function test_nhan_mau_few_shot_hop_le_voi_config(): void
    {
        $data = json_decode(file_get_contents(storage_path('app/reference/xoai/labels.json')), true);

        foreach ($data['labels'] as $item) {
            foreach (config('agrisenai.criteria') as $key => $c) {
                $this->assertArrayHasKey($item['label'][$key]['value'], $c['options'], "{$item['file']} {$key}");
            }
            foreach ($item['label']['surface']['defects'] as $d) {
                $this->assertArrayHasKey($d['type'], config('agrisenai.defect_types'), $item['file']);
            }
        }
    }
}
