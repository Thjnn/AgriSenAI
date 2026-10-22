<?php

/*
|--------------------------------------------------------------------------
| AgriSen — cau hinh ho so lo xoai
|--------------------------------------------------------------------------
|
| NGUON SU THAT DUY NHAT cho: 5 tieu chi ngoai quan + cac lua chon cua tung
| tieu chi, loai khuyet tat, muc dich ban, nguong kiem tra anh, chuan hoa anh.
|
| Prompt, responseSchema cua Gemini, form xac nhan (HITL) va trang ho so
| cong khai deu doc tu day. Sua mot cho nay.
|
| KHONG co diem tong hop, KHONG co xep hang (gop y co Hang 05/10/2026):
| AI chi GOI Y quan sat theo tung tieu chi, nguoi phu trach quyet dinh.
|
*/

return [

    // Doi prompt / schema -> doi version (luu vao ai_suggestions.prompt_version).
    'prompt_version' => 'v2.1-2026-10-22',   // v2.1: bad_photos theo tung anh

    /*
    | 5 tieu chi ngoai quan. `options` = cac gia tri hop le cua `value`.
    | Form "Sua" chi cho chon trong `options` (khong go tu do) de nhan sach.
    */
    'criteria' => [
        'color' => [
            'label' => 'Màu sắc',
            'options' => [
                'xanh' => 'Xanh',
                'xanh_vang' => 'Xanh vàng',
                'vang' => 'Vàng',
                'vang_cam' => 'Vàng cam',
            ],
        ],
        'ripeness' => [
            'label' => 'Độ chín',
            'options' => [
                'xanh_gia' => 'Xanh già',
                'chin_toi' => 'Chín tới',
                'chin_ky' => 'Chín kỹ',
                'qua_chin' => 'Quá chín',
            ],
        ],
        'surface' => [
            'label' => 'Bề mặt / khuyết tật',
            'options' => [
                'sach' => 'Sạch, không khuyết tật',
                'khuyet_tat_nhe' => 'Khuyết tật nhẹ',
                'khuyet_tat_vua' => 'Khuyết tật vừa',
                'khuyet_tat_nang' => 'Khuyết tật nặng',
            ],
        ],
        'shape' => [
            'label' => 'Hình dạng',
            'options' => [
                'binh_thuong' => 'Cân đối',
                'meo_nhe' => 'Méo nhẹ',
                'meo_nang' => 'Méo nặng',
            ],
        ],
        'uniformity' => [
            'label' => 'Độ đồng đều',
            'options' => [
                'dong_deu' => 'Đồng đều',
                'khong_deu' => 'Không đều',
            ],
        ],
    ],

    'defect_types' => [
        'xuoc_vo' => 'Xước vỏ',
        'nam_nang' => 'Nám nắng',
        'vet_nhua' => 'Vết nhựa',
        'tham' => 'Thâm',
        'than_thu' => 'Thán thư',
        'dom_den' => 'Đốm đen',
        'sau_benh' => 'Sâu bệnh',
        'nut' => 'Nứt',
        'thoi' => 'Thối',
        'moc' => 'Mốc',
        'khac' => 'Khác',
    ],

    'defect_severities' => ['nhe', 'trung_binh', 'nang'],

    /*
    | Muc dich ban — quyet dinh cach AI nhin `ripeness`.
    | Xoai xanh la MOT SAN PHAM (an voi muoi ot), khong phai xoai chua chin.
    | Xoai Keo / Tu Quy chin VAN con vo XANH.
    */
    'muc_dich_ban' => [
        'an_xanh' => 'Bán để ăn xanh (xoài xanh chấm muối ớt)',
        'an_chin' => 'Bán để ăn chín',
    ],

    /*
    | AI tu tin duoi nguong nay -> UI khong cho "Dong y tat ca", buoc xem tung the.
    */
    'min_confidence' => 0.6,

    // Gui toi da N anh dat chat luong trong MOT lan goi Gemini.
    'max_photos_per_analysis' => 4,

    /*
    | Kiem tra chat luong anh (port tu assess_quality() trong MVP mau cua co Hang).
    | Chay ca o client (chan som) lan server (khong tin client).
    | Hieu chinh tren dien thoai that: anh mo that ma van qua -> tang blur_min.
    */
    'photo_check' => [
        'blur_min' => 20,          // phuong sai Laplacian tren anh thu nho
        'dark_max' => 60,          // do sang trung binh < 60 = qua toi
        'bright_min' => 220,       // > 220 = chay sang
        'min_side' => 320,         // canh ngan toi thieu (px)
        'sample_side' => 320,      // thu nho ve canh nay truoc khi tinh
    ],

    /*
    | Chuan hoa anh: anh mau few-shot VA anh nguoi dung PHAI qua cung
    | App\Services\Quality\ImageNormalizer, neu khong lech phan phoi -> AI sai.
    */
    'image' => [
        'size' => 768,
        'quality' => 85,
        'bg' => [128, 128, 128],
    ],

];
