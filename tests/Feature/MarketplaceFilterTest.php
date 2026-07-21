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
        // San pham khac category khong duoc hien thi nhu MOT THE SAN PHAM.
        // Luu y: khong dung assertDontSee($other->name) tho — ten san pham "Quýt hồng Lai Vung"
        // la substring cua ten nong trai "HTX Quýt hồng Lai Vung" (xem MarketplaceDemoSeeder),
        // nen co the "trung" voi phan farm-name cua mot the khac cung trang -> false fail/pass.
        // Neo vao dung vi tri render ten san pham (<div class="pcard-name">{{ $p->name }}</div>)
        // bang cach doi hoi ky tu ngay truoc/sau la '>' va '<'.
        $other = Product::certified()->where('category_id', '!=', $p->category_id)->first();
        $this->assertNotNull($other, 'Fixture phai co san pham thuoc category khac de kiem tra loc');
        $res->assertDontSee('>'.$other->name.'<', false);
    }

    public function test_sort_price_asc_dung_thu_tu(): void
    {
        $this->seed();
        $re = Product::certified()->orderBy('price')->first();
        $dat = Product::certified()->orderByDesc('price')->first();

        $response = $this->get('/thi-truong?sort=price_asc');
        $response->assertOk();
        $html = $response->getContent();

        $this->assertLessThan(
            strpos($html, $dat->name),
            strpos($html, $re->name),
            'San pham re nhat phai xuat hien truoc san pham dat nhat khi sort=price_asc'
        );
    }

    public function test_loc_min_score(): void
    {
        $this->seed();
        $res = $this->get('/thi-truong?min_score=95');
        $res->assertOk();
        // chi con product diem >=95
        $low = Product::certified()->whereHas('latestAssessment', fn ($a) => $a->where('quality_score', '<', 95))->first();
        $this->assertNotNull($low, 'Fixture phai co san pham diem < 95 de kiem tra loc min_score');
        $res->assertDontSee('>'.$low->name.'<', false);
    }
}
