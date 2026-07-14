<?php

/*
|--------------------------------------------------------------------------
| AgriSenAi — cau hinh module AI danh gia chat luong nong san
|--------------------------------------------------------------------------
|
| Day la NGUON SU THAT DUY NHAT cho: thang hang, loai khuyet tat, nguong
| dien tich theo TCVN, va tham so chuan hoa anh.
|
| Prompt few-shot, rule engine va validate response cua Gemini deu doc tu
| day. Sua tieu chuan thi sua o mot cho nay.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Thang hang chat luong — neo theo TCVN 9766:2013 (qua co mui / qua tuoi)
    |----------------------------------------------------------------------
    |
    | CANH BAO: hai nguong `dien_tich_toi_da` duoi day (10% va 25%) lay theo
    | thong le tieu chuan qua tuoi. CAN doi chieu ban TCVN goc de xac nhan
    | truoc khi dem di thi. Xem docs/danh-gia-dataset.md muc 1.
    |
    */
    'grades' => [
        'dac_biet' => [
            'nhan' => 'Hạng đặc biệt',
            'diem_toi_thieu' => 9.0,
            // Tieu chuan qua tuoi CHO PHEP khuyet tat be mat rat nhe o hang dac biet,
            // mien khong anh huong hinh thuc chung (vd: vet nhua nho o cuong khi cat).
            'dien_tich_khuyet_tat_toi_da' => 2,   // % be mat
        ],
        'hang_1' => [
            'nhan' => 'Hạng I',
            'diem_toi_thieu' => 7.0,
            'dien_tich_khuyet_tat_toi_da' => 10,
        ],
        'hang_2' => [
            'nhan' => 'Hạng II',
            'diem_toi_thieu' => 5.0,
            'dien_tich_khuyet_tat_toi_da' => 25,
        ],
        'khong_dat' => [
            'nhan' => 'Không đạt',
            'diem_toi_thieu' => 0.0,
            'dien_tich_khuyet_tat_toi_da' => 100,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Tieu chi cham diem — CHI nhung gi NHIN THAY duoc qua anh
    |----------------------------------------------------------------------
    |
    | Khoi luong (gram) va do Brix do nong dan tu nhap. AI KHONG duoc suy doan.
    |
    */
    'scores' => ['color', 'surface', 'shape', 'uniformity', 'ripeness'],

    /*
    |----------------------------------------------------------------------
    | Khuyet tat
    |----------------------------------------------------------------------
    */
    'defect_types' => [
        'xuoc_vo', 'nam_nang', 'vet_nhua', 'tham', 'sau_benh', 'nut', 'thoi',
        'moc', 'meo_mo', 'greening', 'canker', 'blackspot', 'khac',
    ],

    'defect_severities' => ['nhe', 'trung_binh', 'nang'],

    /*
    | Khuyet tat AN VAO THIT QUA -> rule engine ep xuong `khong_dat`,
    | bat ke Gemini cham bao nhieu diem. Day la "neo san bang LUAT", bu cho
    | viec dataset chua co anh qua thoi/moc that.
    | Xem docs/danh-gia-dataset.md muc 4.
    */
    'defects_loai_bo' => ['thoi', 'moc', 'nut'],

    /*
    |----------------------------------------------------------------------
    | Muc dich ban — quyet dinh cach cham `ripeness`
    |----------------------------------------------------------------------
    |
    | Xoai xanh la MOT SAN PHAM (an voi muoi ot), khong phai xoai chua chin.
    | Cam sanh chin VAN co vo XANH.
    |
    | Nong dan khai muc dich khi dang ban; AI cham do chin SO VOI muc dich do,
    | khong so voi mot thang tuyet doi. Thieu tham so nay, AI se tru diem oan
    | moi qua vo xanh.
    |
    */
    'muc_dich_ban' => [
        'an_xanh' => 'Bán để ăn xanh (xoài xanh chấm muối ớt, cam/quýt vỏ xanh)',
        'an_chin' => 'Bán để ăn chín',
    ],

    /*
    |----------------------------------------------------------------------
    | Chuan hoa anh
    |----------------------------------------------------------------------
    |
    | Anh reference VA anh nong dan upload PHAI qua CUNG mot ham chuan hoa
    | (App\Services\Quality\ImageNormalizer). Neu chi chuan hoa mot ben thi
    | lech phan phoi -> AI cham sai.
    |
    | Khong dung mask tron cho xoai: xoai thuon dai, mask tron cat mat hai dau
    | qua, ma hinh dang la mot trong 5 tieu chi cham.
    |
    */
    'image' => [
        'size' => 768,
        'quality' => 85,
        'bg' => [128, 128, 128],   // nen xam trung tinh khi anh co alpha
    ],

];
