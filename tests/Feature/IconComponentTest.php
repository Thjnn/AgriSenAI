<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IconComponentTest extends TestCase
{
    public function test_render_ra_the_svg(): void
    {
        $html = Blade::render('<x-icon name="home" />');

        $this->assertStringContainsString('<svg', $html);
    }

    public function test_class_truyen_vao_duoc_gan_len_svg(): void
    {
        $html = Blade::render('<x-icon name="cart" class="h-5 w-5" />');

        $this->assertStringContainsString('h-5 w-5', $html);
    }

    public function test_ten_khong_ton_tai_thi_khong_render_gi(): void
    {
        $html = trim(Blade::render('<x-icon name="khong-co-icon-nay" />'));

        $this->assertSame('', $html);
    }

    public function test_stroke_width_mac_dinh_la_1_8(): void
    {
        $html = Blade::render('<x-icon name="home" />');

        $this->assertStringContainsString('stroke-width="1.8"', $html);
    }

    public function test_stroke_width_ghi_de_duoc_va_khong_bi_lap(): void
    {
        $html = Blade::render('<x-icon name="home" stroke-width="2.5" />');

        $this->assertStringContainsString('stroke-width="2.5"', $html);
        $this->assertStringNotContainsString('stroke-width="1.8"', $html);

        // Thuộc tính trùng thì trình duyệt lấy cái đầu — override sẽ hỏng âm thầm.
        $this->assertSame(1, substr_count($html, 'stroke-width='));
    }

    public function test_khong_icon_nao_render_stroke_width_hai_lan(): void
    {
        $names = ['home', 'market', 'ai', 'scan', 'chart', 'cart', 'user', 'bell',
            'settings', 'search', 'close', 'send', 'zalo', 'pin', 'mail', 'phone'];

        foreach ($names as $name) {
            $html = Blade::render('<x-icon :name="$name" stroke-width="2.5" />', ['name' => $name]);

            $this->assertSame(
                1,
                substr_count($html, 'stroke-width='),
                "Icon '{$name}' render stroke-width nhiều hơn một lần"
            );
        }
    }

    public function test_scan_mang_net_day_2_theo_demo(): void
    {
        // I.scan trong agrisen-chrome.js hardcode stroke-width="2" (khác I.ai dùng 1.8)
        // vì nó là nút AI Scan tròn nổi ở bottom-nav. Giữ đúng như vậy.
        $this->assertStringContainsString('stroke-width="2"', Blade::render('<x-icon name="scan" />'));
        $this->assertStringContainsString('stroke-width="1.8"', Blade::render('<x-icon name="ai" />'));
    }

    public function test_moi_icon_duoc_config_nhac_den_deu_ton_tai(): void
    {
        $groups = [
            config('navigation.bottom_nav'),
            ...config('navigation.drawer_groups'),
        ];

        foreach ($groups as $group) {
            foreach ($group as $item) {
                $html = Blade::render('<x-icon :name="$name" />', ['name' => $item['icon']]);

                $this->assertStringContainsString('<svg', $html, "Thiếu icon: {$item['icon']}");
            }
        }
    }
}
