<?php

namespace Tests\Feature;

use Tests\TestCase;

class NavigationConfigTest extends TestCase
{
    public function test_bottom_nav_co_dung_hai_bo(): void
    {
        $sets = config('navigation.bottom_nav');

        $this->assertArrayHasKey('default', $sets);
        $this->assertArrayHasKey('advisory', $sets);
    }

    public function test_bo_default_dung_thu_tu_va_co_gio_hang(): void
    {
        $keys = array_column(config('navigation.bottom_nav.default'), 'key');

        $this->assertSame(['home', 'market', 'ai-scan', 'cart', 'account'], $keys);
    }

    public function test_bo_advisory_thay_gio_hang_bang_khuyen_nghi(): void
    {
        $keys = array_column(config('navigation.bottom_nav.advisory'), 'key');

        $this->assertSame(['home', 'market', 'ai-scan', 'khuyennghi', 'account'], $keys);
        $this->assertNotContains('cart', $keys);
    }

    public function test_top_links_co_du_nam_muc(): void
    {
        $this->assertCount(5, config('navigation.top_links'));
    }

    public function test_moi_link_deu_co_du_khoa_bat_buoc(): void
    {
        $groups = [
            config('navigation.top_links'),
            config('navigation.bottom_nav.default'),
            config('navigation.bottom_nav.advisory'),
            ...config('navigation.drawer_groups'),
        ];

        foreach ($groups as $group) {
            foreach ($group as $link) {
                $this->assertArrayHasKey('key', $link);
                $this->assertArrayHasKey('label', $link);
                $this->assertArrayHasKey('href', $link);
            }
        }
    }

    public function test_drawer_co_hai_nhom_dung_nhu_demo(): void
    {
        $groups = config('navigation.drawer_groups');

        // agrisenai-home.html: 5 link điều hướng, divider, rồi 3 link cá nhân.
        $this->assertCount(2, $groups);
        $this->assertSame(
            ['home', 'market', 'ai', 'khuyennghi', 'truyxuat'],
            array_column($groups[0], 'key')
        );
        $this->assertSame(
            ['cart', 'account', 'settings'],
            array_column($groups[1], 'key')
        );
    }

    public function test_nhan_drawer_copy_nguyen_van_tu_demo(): void
    {
        $labels = array_column(array_merge(...config('navigation.drawer_groups')), 'label', 'key');

        // Nhãn drawer dài hơn nhãn top-nav — dễ nhầm, nên chốt bằng test.
        $this->assertSame('AI đánh giá chất lượng', $labels['ai']);
        $this->assertSame('Khuyến nghị giá bán', $labels['khuyennghi']);
        $this->assertSame('Cài đặt', $labels['settings']);
    }
}
