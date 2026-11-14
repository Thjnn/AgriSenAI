<?php

namespace Tests\Unit;

use App\Services\Quality\EvalMetrics;
use PHPUnit\Framework\TestCase;

/** So lieu benchmark AI: chi bao P/R/F1 khi mau so > 0 (gop y thay Nguyen 31/47). */
class EvalMetricsTest extends TestCase
{
    public function test_dem_tp_fp_fn_tn_va_tinh_p_r_f1(): void
    {
        $m = EvalMetrics::binary([[true, true], [true, true], [true, false], [false, true], [false, false]]);

        $this->assertSame(['tp' => 2, 'fp' => 1, 'fn' => 1, 'tn' => 1], array_intersect_key($m, array_flip(['tp', 'fp', 'fn', 'tn'])));
        $this->assertEqualsWithDelta(2 / 3, $m['precision'], 1e-9);
        $this->assertEqualsWithDelta(2 / 3, $m['recall'], 1e-9);
        $this->assertEqualsWithDelta(2 / 3, $m['f1'], 1e-9);
    }

    public function test_mau_so_0_thi_tra_null_khong_chia_0(): void
    {
        $m = EvalMetrics::binary([[false, false]]);

        $this->assertNull($m['precision']);
        $this->assertNull($m['recall']);
        $this->assertNull($m['f1']);
    }
}
