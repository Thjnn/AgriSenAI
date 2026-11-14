<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** quality:eval khong goi Gemini that; chi kiem tra file CSV ghi duoc ca khi thu muc chua co. */
class EvalCommandTest extends TestCase
{
    public function test_csv_tao_thu_muc_neu_chua_co(): void
    {
        Http::fake(['*' => Http::response(['error' => 'quota'], 500)]);
        $dir = sys_get_temp_dir().'/agrisen_eval_'.uniqid();
        $csv = "{$dir}/sub/eval.csv";

        $this->artisan('quality:eval', ['loai' => 'xoai', '--limit' => 1, '--csv' => $csv]);

        $this->assertFileExists($csv);
        @unlink($csv);
        @rmdir("{$dir}/sub");
        @rmdir($dir);
    }
}
