<?php

namespace App\Services\Quality;

/**
 * Bang nham lan nhi phan cho benchmark AI (gop y thay Nguyen 31/47).
 * Ti so nao co mau so 0 -> null: khong bao so khi chua do duoc.
 */
class EvalMetrics
{
    /**
     * @param  list<array{0: bool, 1: bool}>  $pairs  [that, ai], true = co khuyet tat
     * @return array{tp: int, fp: int, fn: int, tn: int, precision: ?float, recall: ?float, f1: ?float}
     */
    public static function binary(array $pairs): array
    {
        $tp = $fp = $fn = $tn = 0;
        foreach ($pairs as [$that, $ai]) {
            match (true) {
                $that && $ai => $tp++,
                ! $that && $ai => $fp++,
                $that && ! $ai => $fn++,
                default => $tn++,
            };
        }

        $precision = $tp + $fp > 0 ? $tp / ($tp + $fp) : null;
        $recall = $tp + $fn > 0 ? $tp / ($tp + $fn) : null;
        $f1 = $precision !== null && $recall !== null && $precision + $recall > 0
            ? 2 * $precision * $recall / ($precision + $recall)
            : null;

        return compact('tp', 'fp', 'fn', 'tn', 'precision', 'recall', 'f1');
    }
}
