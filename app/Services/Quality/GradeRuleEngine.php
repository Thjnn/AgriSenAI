<?php

namespace App\Services\Quality;

/**
 * Rule engine: doi chieu ket qua cua Gemini voi TCVN.
 *
 * NGUYEN TAC BAT DI BAT DICH: chi duoc HA hang, KHONG BAO GIO duoc NANG hang.
 *
 * Ly do: AI sai theo huong de dai (cham qua hong thanh hang cao) thi nong dan
 * ban hang do, khach mat niem tin, san mat uy tin. AI sai theo huong khat khe
 * thi chi thiet mot chut cho nong dan, va co the khieu nai lai. Nen he thong
 * duoc thiet ke de LUON nghieng ve phia an toan.
 *
 * Day cung la cho bu dap cho han che cua dataset: trong 71 qua xoai KHONG co
 * qua nao thoi/moc/nut, nen khong co anh mau de day Gemini nhan dien qua hong.
 * Thay vi bia mot anh "neo san" gia, ta chan bang LUAT o day.
 * Xem docs/files-ke-hoach/quy-trinh-xay-module-ai.md muc 5.
 */
class GradeRuleEngine
{
    /**
     * @param  array  $ai  Ket qua tho tu Gemini
     * @return array Ket qua da doi chieu, kem `rule_engine` giai trinh
     */
    public function apply(array $ai): array
    {
        $order = array_keys(config('agrisenai.grades'));   // tot -> te
        $rules = [];

        $aiGrade = $ai['grade'] ?? 'khong_dat';
        $finalIdx = $this->idx($aiGrade, $order);

        // Anh khong dung duoc thi khong cham, tra ve nguyen trang de nguoi xem.
        if (($ai['usable'] ?? false) === false) {
            $ai['rule_engine'] = [
                'hang_ai_cham' => null,
                'hang_cuoi_cung' => null,
                'da_ha_hang' => false,
                'luat_da_ap_dung' => ['Ảnh không dùng được — không chấm.'],
                'can_nguoi_xem_lai' => true,
            ];

            return $ai;
        }

        $defects = $ai['defects'] ?? [];
        $tongDienTich = array_sum(array_map(
            fn ($d) => (float) ($d['area_percent'] ?? 0),
            $defects
        ));

        // ── LUAT 1 (manh nhat): khuyet tat AN VAO THIT QUA -> loai bo ngay.
        // Khong xet dien tich, khong xet diem AI cham.
        $loaiBo = config('agrisenai.defects_loai_bo');
        foreach ($defects as $d) {
            if (in_array($d['type'] ?? '', $loaiBo, true)) {
                $rules[] = sprintf(
                    'Phát hiện khuyết tật `%s` — loại khuyết tật ăn vào thịt quả. '
                    .'Theo TCVN, quả không còn được xét hạng → ép xuống `khong_dat`.',
                    $d['type']
                );
                $finalIdx = max($finalIdx, $this->idx('khong_dat', $order));
                break;
            }
        }

        // ── LUAT 2: tong dien tich khuyet tat vuot nguong cua hang.
        $hangTheoDienTich = $this->gradeByArea($tongDienTich, $order);
        if ($this->idx($hangTheoDienTich, $order) > $finalIdx) {
            $rules[] = sprintf(
                'Tổng khuyết tật ~%d%% bề mặt, vượt ngưỡng %d%% của hạng `%s` '
                .'→ hạ xuống `%s` theo TCVN 9766:2013.',
                round($tongDienTich),
                config("agrisenai.grades.{$order[$finalIdx]}.dien_tich_khuyet_tat_toi_da"),
                $order[$finalIdx],
                $hangTheoDienTich
            );
            $finalIdx = $this->idx($hangTheoDienTich, $order);
        }

        // ── LUAT 3: diem tong khong du nguong toi thieu cua hang.
        $score = (float) ($ai['overall_score'] ?? 0);
        $hangTheoDiem = $this->gradeByScore($score, $order);
        if ($this->idx($hangTheoDiem, $order) > $finalIdx) {
            $rules[] = sprintf(
                'Điểm tổng %.1f không đạt mức tối thiểu %.1f của hạng `%s` → hạ xuống `%s`.',
                $score,
                config("agrisenai.grades.{$order[$finalIdx]}.diem_toi_thieu"),
                $order[$finalIdx],
                $hangTheoDiem
            );
            $finalIdx = $this->idx($hangTheoDiem, $order);
        }

        $final = $order[$finalIdx];

        // AI khong chac chan -> can nguoi xem lai (nhung van tra ket qua).
        $confidence = (float) ($ai['confidence'] ?? 0);
        $canXemLai = $confidence < 0.6;
        if ($canXemLai) {
            $rules[] = sprintf(
                'Độ tin cậy của AI chỉ %.2f (dưới 0,60) → nên có người xem lại trước khi chốt giá.',
                $confidence
            );
        }

        $ai['grade'] = $final;
        $ai['rule_engine'] = [
            'hang_ai_cham' => $aiGrade,
            'hang_cuoi_cung' => $final,
            'da_ha_hang' => $final !== $aiGrade,
            'tong_dien_tich_khuyet_tat' => round($tongDienTich, 1),
            'luat_da_ap_dung' => $rules ?: ['Không có luật nào phải áp dụng — giữ nguyên hạng AI chấm.'],
            'can_nguoi_xem_lai' => $canXemLai,
        ];

        return $ai;
    }

    /** Hang thap nhat ma tong dien tich khuyet tat con nam trong nguong. */
    private function gradeByArea(float $area, array $order): string
    {
        foreach ($order as $grade) {
            if ($area <= config("agrisenai.grades.{$grade}.dien_tich_khuyet_tat_toi_da")) {
                return $grade;
            }
        }

        return end($order);
    }

    /** Hang cao nhat ma diem tong con dat nguong toi thieu. */
    private function gradeByScore(float $score, array $order): string
    {
        foreach ($order as $grade) {
            if ($score >= config("agrisenai.grades.{$grade}.diem_toi_thieu")) {
                return $grade;
            }
        }

        return end($order);
    }

    /** Vi tri trong thang: cang lon cang te. Hang la thi coi nhu te nhat. */
    private function idx(string $grade, array $order): int
    {
        $i = array_search($grade, $order, true);

        return $i === false ? count($order) - 1 : $i;
    }
}
