<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutDemoPageTest extends TestCase
{
    public function test_trang_demo_tra_ve_200(): void
    {
        $this->get('/layout-demo')->assertOk();
    }

    public function test_trang_demo_dung_khung_layout(): void
    {
        $response = $this->get('/layout-demo');

        $response->assertSee('data-bnav="cart"', false);
        $response->assertSee('id="chatFab"', false);
        $response->assertSee('<footer', false);
    }

    public function test_doi_page_thi_bottom_nav_doi_theo(): void
    {
        $this->get('/layout-demo?page=ai')
            ->assertSee('data-bnav="khuyennghi"', false)
            ->assertDontSee('data-bnav="cart"', false);
    }
}
