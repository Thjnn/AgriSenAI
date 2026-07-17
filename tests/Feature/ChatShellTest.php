<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatShellTest extends TestCase
{
    public function test_co_du_moc_id_cho_javascript(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        $this->assertStringContainsString('id="chatFab"', $html);
        $this->assertStringContainsString('id="chatPanel"', $html);
        $this->assertStringContainsString('id="chatOverlay"', $html);
        $this->assertStringContainsString('id="chatClose"', $html);
    }

    public function test_fab_nam_goc_phai_duoi_va_khong_de_bottom_nav(): void
    {
        $html = view('layouts.app', ['page' => 'home'])->render();

        // Mobile: đẩy lên trên bottom-nav. Desktop: về sát góc.
        $this->assertStringContainsString('bottom-[calc(var(--nav-h)+var(--safe-b)+16px)]', $html);
        $this->assertStringContainsString('lg:bottom-6', $html);
        $this->assertStringContainsString('lg:right-6', $html);
    }
}
