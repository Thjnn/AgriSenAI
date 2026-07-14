<?php

namespace App\Services\Quality;

/**
 * Dieu phoi toan bo luong cham chat luong. Day la thu Controller goi.
 *
 *   anh nong dan
 *      -> ImageNormalizer      (xoay EXIF, cat vuong, 768x768 JPEG)
 *      -> FewShotPromptBuilder (ghep 6 anh mau + nhan vang vao prompt)
 *      -> GeminiVisionClient   (goi API)
 *      -> GradeRuleEngine      (doi chieu TCVN, CHI duoc ha hang)
 *      -> ket qua
 *
 * Luu y kien truc: anh nong dan upload di qua DUNG ImageNormalizer da dung de
 * dung bo anh mau. Neu chi chuan hoa mot ben thi anh mau va anh that lech phan
 * phoi, va AI se cham sai.
 */
class QualityAssessor
{
    public function __construct(
        private readonly ImageNormalizer $normalizer,
        private readonly FewShotPromptBuilder $prompt,
        private readonly GeminiVisionClient $gemini,
        private readonly GradeRuleEngine $rules,
    ) {
    }

    /**
     * @param  string  $imagePath    Anh goc nong dan upload
     * @param  string  $loai         Loai nong san (vd: 'xoai')
     * @param  string  $mucDichBan   'an_xanh' | 'an_chin'
     * @param  bool    $daChuanHoa   True neu anh DA qua ImageNormalizer roi
     *                               (vd: anh trong tap test) — khong chuan hoa lai.
     */
    public function assess(
        string $imagePath,
        string $loai,
        string $mucDichBan = 'an_chin',
        bool $daChuanHoa = false,
    ): array {
        $anh = $imagePath;
        $tmp = null;

        if (! $daChuanHoa) {
            $tmp = tempnam(sys_get_temp_dir(), 'agrisen_').'.jpg';
            $this->normalizer->normalize($imagePath, $tmp);
            $anh = $tmp;
        }

        try {
            $turns = $this->prompt->fewShotTurns($loai);
            $turns[] = $this->prompt->userTurn($anh, $mucDichBan);

            $ketQua = $this->gemini->assess(
                $this->prompt->systemInstruction(),
                $turns,
                $this->prompt->responseSchema(),
            );
        } finally {
            if ($tmp !== null && is_file($tmp)) {
                unlink($tmp);
            }
        }

        return $this->rules->apply($ketQua);
    }
}
