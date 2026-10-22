<?php

namespace App\Services\Quality;

/**
 * Dieu phoi luong AI goi y ngoai quan. Day la thu Job AnalyzeLot goi.
 *
 *   anh lo (DA chuan hoa qua ImageNormalizer luc upload)
 *      -> FewShotPromptBuilder (ghep 6 anh mau + nhan mau vao prompt)
 *      -> GeminiVisionClient   (goi API)
 *      -> goi y theo 5 tieu chi (KHONG diem, KHONG hang)
 *
 * Anh mau few-shot va anh nguoi dung deu qua CUNG ImageNormalizer. Lech mot ben
 * thi anh mau va anh that lech phan phoi, AI mo ta sai.
 */
class QualityAssessor
{
    public function __construct(
        private readonly FewShotPromptBuilder $prompt,
        private readonly GeminiVisionClient $gemini,
    ) {}

    /**
     * @param  string[]  $imagePaths  Anh DA chuan hoa 768x768 cua cung mot lo
     * @param  string  $mucDichBan  'an_xanh' | 'an_chin'
     */
    public function assess(array $imagePaths, string $mucDichBan = 'an_chin', string $loai = 'xoai'): array
    {
        $turns = $this->prompt->fewShotTurns($loai);
        $turns[] = $this->prompt->userTurn($imagePaths, $mucDichBan);

        return $this->gemini->assess(
            $this->prompt->systemInstruction(),
            $turns,
            $this->prompt->responseSchema(),
        );
    }
}
