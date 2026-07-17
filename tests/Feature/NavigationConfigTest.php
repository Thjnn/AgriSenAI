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
            config('navigation.drawer_links'),
            config('navigation.bottom_nav.default'),
            config('navigation.bottom_nav.advisory'),
        ];

        foreach ($groups as $group) {
            foreach ($group as $link) {
                $this->assertArrayHasKey('key', $link);
                $this->assertArrayHasKey('label', $link);
                $this->assertArrayHasKey('href', $link);
            }
        }
    }
}
