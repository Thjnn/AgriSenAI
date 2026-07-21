@extends('layouts.app', ['page' => 'ai'])

@section('title', 'AI đánh giá chất lượng nông sản')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Bien mau rieng cho trang AI danh gia — port tu Test-Giao-Dien/agrisenai-ai-danh-gia.html.
           Khong dinh nghia --nav-h/--safe-b (app.css da so huu). */
        .ai-page {
            --green: #2e7d32;
            --green-medium: #43a047;
            --green-light: #e8f5e9;
            --green-bg: #eff8f0;
            --white: #fff;
            --bg: #fafafa;
            --gray-50: #f5f5f5;
            --gray-100: #eee;
            --gray-200: #e0e0e0;
            --gray-300: #bdbdbd;
            --gray-400: #9e9e9e;
            --text: #212121;
            --text-sec: #757575;
            --text-light: #9e9e9e;
            --amber: #f9a825;
            --amber-bg: #fff8e1;
            --danger: #d32f2f;
            --danger-bg: #ffebee;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, .07);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, .08);
            --r: 14px;
            --r-sm: 10px;
            --r-lg: 18px;
            max-width: 900px;
            margin: 0 auto;
            color: var(--text);
        }
        .ai-page * { box-sizing: border-box; }

        .ai-page .page-head { margin-bottom: 8px; }
        .ai-page .page-title { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .ai-page .page-desc { font-size: 13px; color: var(--text-sec); font-weight: 500; line-height: 1.5; }

        /* Info banner */
        .ai-page .info-banner {
            display: flex; gap: 11px; background: var(--green-light);
            border: 1px solid #cfe8d2; border-radius: var(--r);
            padding: 13px 15px; margin: 16px 0 22px;
        }
        .ai-page .info-banner .ib-icon { font-size: 20px; line-height: 1.2; flex-shrink: 0; }
        .ai-page .info-banner p { font-size: 12.5px; font-weight: 600; color: #2e5730; line-height: 1.5; }
        .ai-page .info-banner b { font-weight: 800; }

        /* Cards / steps */
        .ai-page .card {
            background: var(--white); border: 1px solid var(--gray-100);
            border-radius: var(--r-lg); box-shadow: var(--shadow-sm);
            padding: 18px 18px 20px; margin-bottom: 18px;
        }
        .ai-page .step-head { display: flex; align-items: center; gap: 11px; margin-bottom: 15px; }
        .ai-page .step-num {
            width: 28px; height: 28px; border-radius: 50%; background: var(--green);
            color: #fff; font-size: 14px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ai-page .step-title { font-size: 16px; font-weight: 800; }
        .ai-page .step-hint { font-size: 12px; font-weight: 500; color: var(--text-sec); margin-top: 1px; }

        /* Product chips */
        .ai-page .product-row { display: flex; gap: 10px; overflow-x: auto; scrollbar-width: none; padding-bottom: 2px; }
        .ai-page .product-row::-webkit-scrollbar { display: none; }
        .ai-page .product-chip {
            position: relative;
            flex-shrink: 0; display: flex; flex-direction: column; align-items: center; gap: 6px;
            width: 82px; padding: 12px 6px; border: 1.5px solid var(--gray-200);
            border-radius: var(--r); background: var(--white); cursor: pointer;
            transition: all .15s; -webkit-tap-highlight-color: transparent;
        }
        .ai-page .product-chip:hover { border-color: var(--green-medium); }
        .ai-page .product-chip.active { border-color: var(--green); background: var(--green-light); }
        .ai-page .product-chip .pc-emoji { font-size: 30px; line-height: 1; }
        .ai-page .product-chip .pc-name { font-size: 11.5px; font-weight: 700; color: var(--text-sec); text-align: center; line-height: 1.2; }
        .ai-page .product-chip.active .pc-name { color: var(--green); }
        /* Badge "chuan": loai co bo anh mau (few-shot) */
        .ai-page .product-chip .pc-badge {
            position: absolute; top: 5px; right: 5px;
            width: 15px; height: 15px; border-radius: 50%;
            background: var(--green); color: #fff; font-size: 9px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; line-height: 1;
        }
        .ai-page .chip-legend {
            display: flex; align-items: center; gap: 6px;
            font-size: 11.5px; font-weight: 600; color: var(--text-light); margin-top: 11px;
        }
        .ai-page .chip-legend .lg-dot {
            width: 15px; height: 15px; border-radius: 50%; background: var(--green);
            color: #fff; font-size: 9px; font-weight: 800;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        /* Upload slots */
        .ai-page .upload-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .ai-page .upload-slot {
            position: relative; aspect-ratio: 1 / 1; border: 1.5px dashed var(--gray-300);
            border-radius: var(--r); background: var(--gray-50);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
            cursor: pointer; overflow: hidden; transition: border-color .15s, background .15s;
            -webkit-tap-highlight-color: transparent;
        }
        .ai-page .upload-slot:hover { border-color: var(--green-medium); }
        .ai-page .upload-slot.filled { border-style: solid; border-color: var(--green); }
        .ai-page .upload-slot input { display: none; }
        .ai-page .upload-slot > svg { width: 24px; height: 24px; color: var(--gray-400); }
        .ai-page .upload-slot .us-label { font-size: 11px; font-weight: 700; color: var(--text-sec); text-align: center; padding: 0 4px; line-height: 1.25; }
        .ai-page .upload-slot .us-tag {
            position: absolute; bottom: 6px; left: 6px; background: rgba(0, 0, 0, .6);
            color: #fff; font-size: 9.5px; font-weight: 700; padding: 2px 7px; border-radius: 20px; backdrop-filter: blur(2px);
        }
        .ai-page .upload-slot img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ai-page .upload-slot .us-remove {
            position: absolute; top: 6px; right: 6px; width: 24px; height: 24px; border-radius: 50%;
            border: none; background: rgba(0, 0, 0, .55); color: #fff;
            display: none; align-items: center; justify-content: center; cursor: pointer; z-index: 2;
        }
        .ai-page .upload-slot.filled .us-remove { display: flex; }
        .ai-page .upload-slot .us-remove svg { width: 13px; height: 13px; color: #fff; }
        .ai-page .upload-note { font-size: 11.5px; color: var(--text-light); font-weight: 500; margin-top: 10px; line-height: 1.45; }

        /* Form fields */
        .ai-page .measure-note {
            display: flex; gap: 8px; background: var(--amber-bg); border-radius: var(--r-sm);
            padding: 10px 12px; margin-bottom: 15px; font-size: 12px; font-weight: 600; color: #7a5b00; line-height: 1.45;
        }
        .ai-page .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .ai-page .field { display: flex; flex-direction: column; min-width: 0; }
        .ai-page .field.full { grid-column: 1 / -1; }
        .ai-page .field label { font-size: 12.5px; font-weight: 700; color: var(--text-sec); margin-bottom: 6px; }
        .ai-page .field label .opt { font-weight: 600; color: var(--text-light); }
        .ai-page .field input, .ai-page .field select {
            width: 100%; min-width: 0; border: 1.5px solid var(--gray-200); border-radius: var(--r-sm);
            padding: 12px 14px; font-size: 14.5px; font-weight: 600; color: var(--text);
            background: var(--white); outline: none; transition: border-color .15s;
        }
        .ai-page .field input:focus, .ai-page .field select:focus { border-color: var(--green); }
        .ai-page .field input::placeholder { color: var(--gray-300); font-weight: 500; }

        /* Assess button */
        .ai-page .btn-assess {
            width: 100%; border: none; background: var(--green); color: #fff; font-size: 16px; font-weight: 800;
            padding: 16px; border-radius: var(--r); cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            box-shadow: 0 6px 18px rgba(46, 125, 50, .3); transition: background .15s, transform .1s; margin-bottom: 22px;
        }
        .ai-page .btn-assess:hover { background: #256427; }
        .ai-page .btn-assess:active { transform: scale(.99); }
        .ai-page .btn-assess:disabled { opacity: .55; cursor: not-allowed; box-shadow: none; }
        .ai-page .btn-assess svg { width: 21px; height: 21px; }
        .ai-page .btn-assess .spinner {
            width: 20px; height: 20px; border: 2.5px solid rgba(255, 255, 255, .4);
            border-top-color: #fff; border-radius: 50%; animation: ai-spin .7s linear infinite;
        }
        @keyframes ai-spin { to { transform: rotate(360deg); } }

        /* Result */
        .ai-page .result { display: none; }
        .ai-page .result.show { display: block; animation: ai-fadeUp .4s ease; }
        @keyframes ai-fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .ai-page .result-top { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .ai-page .grade-ring {
            width: 92px; height: 92px; border-radius: 50%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; flex-shrink: 0; color: #fff; box-shadow: var(--shadow-md);
        }
        .ai-page .grade-ring .gr-score { font-size: 28px; font-weight: 800; line-height: 1; }
        .ai-page .grade-ring .gr-max { font-size: 12px; font-weight: 700; opacity: .85; }
        .ai-page .result-meta { flex: 1; min-width: 180px; }
        .ai-page .grade-badge {
            display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 800;
            padding: 5px 12px; border-radius: 20px; margin-bottom: 7px;
        }
        .ai-page .result-product { font-size: 15px; font-weight: 800; }
        .ai-page .result-confidence { font-size: 12.5px; font-weight: 600; color: var(--text-sec); margin-top: 3px; }
        .ai-page .g-dac_biet { background: linear-gradient(135deg, #2e7d32, #43a047); }
        .ai-page .g-hang_1 { background: linear-gradient(135deg, #43a047, #66bb6a); }
        .ai-page .g-hang_2 { background: linear-gradient(135deg, #f9a825, #ffb74d); }
        .ai-page .g-khong_dat { background: linear-gradient(135deg, #d32f2f, #ef5350); }
        .ai-page .badge-dac_biet, .ai-page .badge-hang_1 { background: var(--green-light); color: var(--green); }
        .ai-page .badge-hang_2 { background: var(--amber-bg); color: #b37700; }
        .ai-page .badge-khong_dat { background: var(--danger-bg); color: var(--danger); }

        .ai-page .result-block { margin-top: 20px; }
        .ai-page .rb-title {
            font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px;
            color: var(--text-sec); margin-bottom: 12px;
        }
        .ai-page .score-item { margin-bottom: 12px; }
        .ai-page .score-item:last-child { margin-bottom: 0; }
        .ai-page .score-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 5px; }
        .ai-page .score-row span:last-child { color: var(--green); }
        .ai-page .score-track { height: 8px; background: var(--gray-100); border-radius: 8px; overflow: hidden; }
        .ai-page .score-fill {
            height: 100%; border-radius: 8px; background: linear-gradient(90deg, var(--green-medium), var(--green));
            width: 0; transition: width .8s cubic-bezier(.34, 1.2, .64, 1);
        }

        /* Defects */
        .ai-page .defect { display: flex; gap: 11px; padding: 12px; border: 1px solid var(--gray-100); border-radius: var(--r); margin-bottom: 10px; }
        .ai-page .defect:last-child { margin-bottom: 0; }
        .ai-page .defect-icon {
            width: 34px; height: 34px; border-radius: 9px; background: var(--amber-bg);
            display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;
        }
        .ai-page .defect-body { flex: 1; min-width: 0; }
        .ai-page .defect-top { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; flex-wrap: wrap; }
        .ai-page .defect-name { font-size: 13.5px; font-weight: 800; }
        .ai-page .sev { font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 20px; }
        .ai-page .sev-nhe { background: var(--green-light); color: var(--green); }
        .ai-page .sev-trung_binh { background: var(--amber-bg); color: #b37700; }
        .ai-page .sev-nang { background: var(--danger-bg); color: var(--danger); }
        .ai-page .defect-area { font-size: 11.5px; font-weight: 700; color: var(--text-sec); }
        .ai-page .defect-note { font-size: 12.5px; font-weight: 500; color: var(--text-sec); line-height: 1.45; }
        .ai-page .no-defect {
            display: flex; align-items: center; gap: 9px; font-size: 13.5px; font-weight: 700; color: var(--green);
            background: var(--green-light); border-radius: var(--r); padding: 12px 14px;
        }

        /* Reasoning / advice / farmer input */
        .ai-page .rb-text { font-size: 13.5px; font-weight: 500; color: var(--text); line-height: 1.6; }
        .ai-page .advice-box { display: flex; gap: 11px; background: var(--green-bg); border: 1px solid #cfe8d2; border-radius: var(--r); padding: 14px; }
        .ai-page .advice-box .ab-icon { font-size: 20px; flex-shrink: 0; }
        .ai-page .advice-box p { font-size: 13.5px; font-weight: 600; color: #2e5730; line-height: 1.55; }
        .ai-page .farmer-input { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .ai-page .fi-item { background: var(--gray-50); border-radius: var(--r-sm); padding: 12px 10px; text-align: center; }
        .ai-page .fi-val { font-size: 17px; font-weight: 800; }
        .ai-page .fi-lbl { font-size: 11px; font-weight: 600; color: var(--text-sec); margin-top: 2px; }
        .ai-page .fi-item .fi-src {
            display: inline-block; font-size: 9px; font-weight: 800; color: var(--green);
            background: var(--green-light); border-radius: 5px; padding: 1px 5px; margin-top: 5px;
        }

        /* Override / warning notes */
        .ai-page .note-flag { display: flex; gap: 9px; border-radius: var(--r); padding: 12px 14px; font-size: 12.5px; font-weight: 600; line-height: 1.5; margin-top: 14px; }
        .ai-page .note-override { background: var(--amber-bg); color: #7a5b00; }
        .ai-page .note-warning { background: var(--danger-bg); color: #8a1f1f; }
        .ai-page .note-flag svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }

        .ai-page .tcvn-ref {
            display: flex; gap: 9px; align-items: flex-start; font-size: 11.5px; font-weight: 600;
            color: var(--text-light); line-height: 1.5; margin-top: 16px; padding-top: 14px; border-top: 1px dashed var(--gray-200);
        }
        .ai-page .tcvn-ref svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px; }

        .ai-page .result-actions { display: flex; gap: 10px; margin-top: 18px; }
        .ai-page .btn-ghost {
            flex: 1; border: 1.5px solid var(--gray-200); background: var(--white); color: var(--text);
            font-size: 14px; font-weight: 800; padding: 13px; border-radius: var(--r); cursor: pointer; transition: background .15s;
        }
        .ai-page .btn-ghost:hover { background: var(--gray-50); }
        .ai-page .btn-solid {
            flex: 1; border: none; background: var(--green); color: #fff; font-size: 14px; font-weight: 800;
            padding: 13px; border-radius: var(--r); cursor: pointer; transition: background .15s; text-align: center;
        }
        .ai-page .btn-solid:hover { background: #256427; }

        /* Unusable state */
        .ai-page .unusable { display: none; text-align: center; padding: 26px 18px; }
        .ai-page .unusable.show { display: block; }
        .ai-page .unusable .un-emoji { font-size: 44px; }
        .ai-page .unusable h3 { font-size: 17px; font-weight: 800; margin: 10px 0 6px; }
        .ai-page .unusable p { font-size: 13px; font-weight: 500; color: var(--text-sec); line-height: 1.5; max-width: 340px; margin: 0 auto 16px; }

        /* Alert (loi validate / server) */
        .ai-page .ai-alert {
            display: none; background: var(--danger-bg); border: 1px solid #f5c2c2; color: #8a1f1f;
            border-radius: var(--r); padding: 13px 15px; font-size: 13px; font-weight: 700; margin-bottom: 18px;
        }
        .ai-page .ai-alert.show { display: block; }

        @media (min-width: 640px) {
            .ai-page .page-title { font-size: 25px; }
        }
        @media (min-width: 1024px) {
            .ai-page .page-title { font-size: 27px; }
        }
        @media (max-width: 360px) {
            .ai-page .field-grid { grid-template-columns: 1fr; }
            .ai-page .farmer-input { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="ai-page" id="aiPage">
        <div class="page-head">
            <h1 class="page-title">AI đánh giá chất lượng nông sản</h1>
            <p class="page-desc">
                Chụp ảnh trái cây, AI phân hạng theo tiêu chuẩn quốc gia TCVN 9766:2013 và gợi ý cách bán.
            </p>
        </div>

        <div class="info-banner">
            <span class="ib-icon">🎯</span>
            <p>
                AI chỉ chấm <b>phần nhìn thấy qua ảnh</b> (màu sắc, bề mặt, hình dạng).
                <b>Khối lượng và độ ngọt do bạn cân đo &amp; nhập</b> — AI không đoán từ ảnh để đảm bảo trung thực.
            </p>
        </div>

        <div class="ai-alert" id="aiAlert"></div>

        <form id="assessForm">
                {{-- STEP 1: chon loai --}}
                <div class="card">
                    <div class="step-head">
                        <div class="step-num">1</div>
                        <div>
                            <div class="step-title">Chọn loại nông sản</div>
                            <div class="step-hint">Mỗi loại có bộ mẫu chuẩn riêng để chấm chính xác</div>
                        </div>
                    </div>
                    <div class="product-row" id="productRow">
                        @foreach ($sanPham as $key => $meta)
                            <div class="product-chip @if ($loop->first) active @endif" data-loai="{{ $key }}">
                                @if ($meta['co_dataset'])
                                    <span class="pc-badge" title="Có bộ ảnh mẫu — AI chấm chuẩn nhất">✓</span>
                                @endif
                                <span class="pc-emoji">{{ $meta['emoji'] }}</span>
                                <span class="pc-name">{{ $meta['nhan'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="chip-legend">
                        <span class="lg-dot">✓</span>
                        <span>Có bộ ảnh mẫu chuẩn — AI chấm chính xác nhất. Loại chưa có dấu này vẫn chấm được (AI tự nhận diện) nhưng độ chuẩn thấp hơn.</span>
                    </div>
                </div>

                {{-- STEP 2: upload anh --}}
                <div class="card">
                    <div class="step-head">
                        <div class="step-num">2</div>
                        <div>
                            <div class="step-title">Chụp / tải ảnh</div>
                            <div class="step-hint">1–3 ảnh, nền sáng, rõ nét. Ảnh đầu bắt buộc.</div>
                        </div>
                    </div>
                    <div class="upload-grid" id="uploadGrid">
                        <label class="upload-slot" data-tag="Toàn quả" data-required="1">
                            <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                            <span class="us-label">Toàn quả</span>
                            <span class="us-tag">Bắt buộc</span>
                            <button type="button" class="us-remove" aria-label="Xoá ảnh">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </label>
                        <label class="upload-slot" data-tag="Cận vỏ">
                            <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <span class="us-label">Cận vỏ</span>
                            <button type="button" class="us-remove" aria-label="Xoá ảnh">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </label>
                        <label class="upload-slot" data-tag="Mặt cắt">
                            <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <span class="us-label">Mặt cắt</span>
                            <button type="button" class="us-remove" aria-label="Xoá ảnh">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </label>
                    </div>
                    <p class="upload-note">
                        💡 Chụp trên nền sáng, đủ ánh sáng tự nhiên. Ảnh sẽ được cắt vuông ở giữa — đặt quả ngay
                        giữa khung. Nếu ảnh mờ hoặc không phải nông sản, AI sẽ báo "ảnh chưa dùng được".
                    </p>
                </div>

                {{-- STEP 3: cmap --}}
                <div class="card">
                    <div class="step-head">
                        <div class="step-num">3</div>
                        <div>
                            <div class="step-title">Thông tin cân đo</div>
                            <div class="step-hint">Phần AI không đoán được — bạn nhập</div>
                        </div>
                    </div>
                    <div class="measure-note">
                        <span>⚖️</span>
                        <span>Khối lượng và độ Brix (độ ngọt) không thể suy ra từ ảnh. Bạn cân đo rồi nhập để hệ thống gộp vào kết quả cuối.</span>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label for="variety">Giống <span class="opt">(nếu có)</span></label>
                            <input type="text" id="variety" placeholder="VD: Cát Hòa Lộc">
                        </div>
                        <div class="field">
                            <label for="weight">Khối lượng TB (g/trái)</label>
                            <input type="number" id="weight" inputmode="numeric" placeholder="VD: 420">
                        </div>
                        <div class="field">
                            <label for="quantity">Số trái trong lô</label>
                            <input type="number" id="quantity" inputmode="numeric" placeholder="VD: 85">
                        </div>
                        <div class="field">
                            <label for="brix">Độ Brix <span class="opt">(nếu có)</span></label>
                            <input type="number" id="brix" inputmode="decimal" placeholder="VD: 18">
                        </div>
                        <div class="field full">
                            <label for="region">Vùng trồng</label>
                            <input type="text" id="region" placeholder="VD: Cao Lãnh, Đồng Tháp" value="Cao Lãnh, Đồng Tháp">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-assess" id="assessBtn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14.343 3A6 6 0 0 0 22 10.657V18a1 1 0 0 1-1 1h-6.55L12 22.5L9.55 19H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm5.186-1.68a.507.507 0 0 1 .942 0l.253.61a4.37 4.37 0 0 0 2.25 2.327l.717.32a.53.53 0 0 1 0 .962l-.758.338a4.36 4.36 0 0 0-2.22 2.25l-.246.566a.506.506 0 0 1-.934 0l-.247-.565a4.36 4.36 0 0 0-2.219-2.251l-.76-.338a.53.53 0 0 1 0-.963l.718-.32a4.37 4.37 0 0 0 2.251-2.325z" />
                    </svg>
                    <span>Đánh giá chất lượng</span>
                </button>
            </form>

            {{-- RESULT (inline) --}}
            <div class="card result" id="result">
                {{-- Nhanh: anh chua dung duoc --}}
                <div class="unusable" id="unusableBox">
                    <div class="un-emoji">📷</div>
                    <h3>Ảnh chưa dùng được</h3>
                    <p id="unusableReason">Ảnh bị mờ hoặc thiếu sáng nên AI chưa thể đánh giá chính xác. Vui lòng chụp lại ở nơi đủ sáng, lấy nét vào quả.</p>
                    <button type="button" class="btn-solid" id="retryBtn2" style="max-width:220px;margin:0 auto">Chụp lại</button>
                </div>

                {{-- Nhanh: ket qua binh thuong --}}
                <div id="resultBox">
                    <div class="result-top">
                        <div class="grade-ring g-hang_1" id="gradeRing">
                            <span class="gr-score" id="scoreVal">0.0</span>
                            <span class="gr-max">/10</span>
                        </div>
                        <div class="result-meta">
                            <span class="grade-badge badge-hang_1" id="gradeBadge">Hạng I</span>
                            <div class="result-product" id="resProduct">—</div>
                            <div class="result-confidence" id="resConfidence">Độ tự tin của AI: 0%</div>
                        </div>
                    </div>

                    <div class="note-flag note-warning" id="lowConfNote" style="display:none">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <span>AI chưa chắc chắn về kết quả này. Nên chụp lại rõ hơn hoặc nhờ người có kinh nghiệm xem thêm.</span>
                    </div>
                    <div class="note-flag note-override" id="overrideNote" style="display:none">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        <span id="overrideText"></span>
                    </div>

                    <div class="result-block">
                        <div class="rb-title">Điểm chi tiết (AI chấm qua ảnh)</div>
                        <div id="scoresList"></div>
                    </div>

                    <div class="result-block">
                        <div class="rb-title">Khuyết tật phát hiện</div>
                        <div id="defectsList"></div>
                    </div>

                    <div class="result-block">
                        <div class="rb-title">Nhận xét của AI</div>
                        <p class="rb-text" id="reasoningText"></p>
                    </div>

                    <div class="result-block">
                        <div class="rb-title">Lời khuyên</div>
                        <div class="advice-box">
                            <span class="ab-icon">💡</span>
                            <p id="adviceText"></p>
                        </div>
                    </div>

                    <div class="result-block">
                        <div class="rb-title">Số liệu bạn cung cấp</div>
                        <div class="farmer-input" id="farmerInput"></div>
                    </div>

                    <div class="tcvn-ref">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                        <span>
                            Phân hạng theo <b>TCVN 9766:2013</b>. Điểm AI chỉ mang tính tham khảo về hình thức;
                            hệ thống có quyền hạ hạng theo tiêu chuẩn nhưng không nâng hạng. Kết quả hiển thị kèm
                            ảnh gốc để người mua tự kiểm chứng, không thay thế kiểm định.
                        </span>
                    </div>

                    <div class="result-actions">
                        <button type="button" class="btn-ghost" id="retryBtn">Đánh giá lại</button>
                        <a class="btn-solid" href="#" style="display:flex;align-items:center;justify-content:center">Xem khuyến nghị giá →</a>
                    </div>
                </div>
            </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('assessForm');
            if (!form) return;

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const assessBtn = document.getElementById('assessBtn');
            const result = document.getElementById('result');
            const resultBox = document.getElementById('resultBox');
            const unusableBox = document.getElementById('unusableBox');
            const alertBox = document.getElementById('aiAlert');

            /* ===== Chon loai ===== */
            let currentLoai = document.querySelector('.product-chip.active')?.dataset.loai || '';
            document.querySelectorAll('.product-chip').forEach((chip) => {
                chip.addEventListener('click', () => {
                    document.querySelectorAll('.product-chip').forEach((c) => c.classList.remove('active'));
                    chip.classList.add('active');
                    currentLoai = chip.dataset.loai;
                });
            });

            /* ===== Xem truoc anh ===== */
            document.querySelectorAll('.upload-slot').forEach((slot) => {
                const input = slot.querySelector('input');
                const removeBtn = slot.querySelector('.us-remove');
                input.addEventListener('change', () => {
                    const file = input.files[0];
                    if (!file) return;
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    slot.querySelectorAll('img').forEach((i) => i.remove());
                    slot.appendChild(img);
                    slot.classList.add('filled');
                });
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    input.value = '';
                    slot.querySelectorAll('img').forEach((i) => i.remove());
                    slot.classList.remove('filled');
                });
            });

            /* ===== Anh bat buoc (o Toan qua) ===== */
            function requiredImage() {
                const slot = document.querySelector('.upload-slot[data-required="1"]');
                return slot?.querySelector('input')?.files[0] || null;
            }

            const gradeMeta = {
                dac_biet: { badge: '★ Hạng đặc biệt', ring: 'g-dac_biet', bClass: 'badge-dac_biet' },
                hang_1: { badge: '✔ Hạng I', ring: 'g-hang_1', bClass: 'badge-hang_1' },
                hang_2: { badge: '● Hạng II', ring: 'g-hang_2', bClass: 'badge-hang_2' },
                khong_dat: { badge: '✕ Không đạt', ring: 'g-khong_dat', bClass: 'badge-khong_dat' },
            };
            const sevLabel = { nhe: 'Nhẹ', trung_binh: 'Trung bình', nang: 'Nặng' };

            function showAlert(msg) {
                alertBox.textContent = msg;
                alertBox.classList.add('show');
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            function clearAlert() { alertBox.classList.remove('show'); }

            function renderResult(r) {
                unusableBox.classList.remove('show');
                resultBox.style.display = 'block';

                const meta = gradeMeta[r.grade] || gradeMeta.hang_2;
                const ring = document.getElementById('gradeRing');
                ring.className = 'grade-ring ' + meta.ring;
                document.getElementById('scoreVal').textContent = Number(r.overall_score).toFixed(1);
                const badge = document.getElementById('gradeBadge');
                badge.className = 'grade-badge ' + meta.bClass;
                badge.textContent = meta.badge;
                document.getElementById('resProduct').textContent = r.detected_product || '—';
                document.getElementById('resConfidence').textContent =
                    'Độ tự tin của AI: ' + Math.round((r.confidence || 0) * 100) + '%';

                document.getElementById('lowConfNote').style.display = (r.confidence || 0) < 0.6 ? 'flex' : 'none';
                const ov = document.getElementById('overrideNote');
                if (r.override_note) {
                    ov.style.display = 'flex';
                    document.getElementById('overrideText').textContent = r.override_note;
                } else {
                    ov.style.display = 'none';
                }

                // Diem thanh phan — mau theo nguong: <4 do, 4..<7 cam, >=7 xanh
                const barColor = (v) => v < 4
                    ? { grad: 'linear-gradient(90deg,#ef5350,#d32f2f)', text: '#d32f2f' }
                    : v < 7
                        ? { grad: 'linear-gradient(90deg,#ffb74d,#f9a825)', text: '#b37700' }
                        : { grad: 'linear-gradient(90deg,#66bb6a,#2e7d32)', text: '#2e7d32' };
                const sl = document.getElementById('scoresList');
                sl.innerHTML = '';
                Object.entries(r.scores || {}).forEach(([k, v]) => {
                    const c = barColor(Number(v));
                    const item = document.createElement('div');
                    item.className = 'score-item';
                    item.innerHTML =
                        '<div class="score-row"><span>' + k + '</span><span style="color:' + c.text + '">' +
                        Number(v).toFixed(1) +
                        '</span></div><div class="score-track"><div class="score-fill"></div></div>';
                    sl.appendChild(item);
                    const fill = item.querySelector('.score-fill');
                    fill.style.background = c.grad;
                    requestAnimationFrame(() => {
                        fill.style.width = Math.min(100, v * 10) + '%';
                    });
                });

                // Khuyet tat
                const dl = document.getElementById('defectsList');
                dl.innerHTML = '';
                if (!r.defects || r.defects.length === 0) {
                    dl.innerHTML = '<div class="no-defect">✓ Không phát hiện khuyết tật đáng kể</div>';
                } else {
                    r.defects.forEach((d) => {
                        const el = document.createElement('div');
                        el.className = 'defect';
                        el.innerHTML =
                            '<div class="defect-icon">' + (d.emoji || '⚠️') +
                            '</div><div class="defect-body"><div class="defect-top"><span class="defect-name">' +
                            d.type + '</span><span class="sev sev-' + d.severity + '">' +
                            (sevLabel[d.severity] || d.severity) + '</span><span class="defect-area">~' +
                            d.area_percent + '% bề mặt</span></div><div class="defect-note">' + d.note + '</div></div>';
                        dl.appendChild(el);
                    });
                }

                document.getElementById('reasoningText').textContent = r.reasoning || '';
                document.getElementById('adviceText').textContent = r.advice || '';

                // So lieu nong dan nhap
                const weight = document.getElementById('weight').value;
                const quantity = document.getElementById('quantity').value;
                const brix = document.getElementById('brix').value;
                const fi = document.getElementById('farmerInput');
                fi.innerHTML = '';
                [
                    { lbl: 'Khối lượng TB', val: weight ? weight + ' g' : '—' },
                    { lbl: 'Số trái', val: quantity || '—' },
                    { lbl: 'Độ Brix', val: brix ? brix + '°' : '—' },
                ].forEach((it) => {
                    const box = document.createElement('div');
                    box.className = 'fi-item';
                    box.innerHTML = '<div class="fi-val">' + it.val + '</div><div class="fi-lbl">' + it.lbl +
                        '</div><span class="fi-src">Bạn nhập</span>';
                    fi.appendChild(box);
                });

                result.classList.add('show');
                result.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function renderUnusable(reason) {
                resultBox.style.display = 'none';
                document.getElementById('unusableReason').textContent = reason ||
                    'Ảnh chưa nhìn rõ nông sản. Vui lòng chụp lại ở nơi đủ sáng, lấy nét vào quả.';
                unusableBox.classList.add('show');
                result.classList.add('show');
                result.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function setLoading(on) {
                const label = assessBtn.querySelector('span');
                const icon = assessBtn.querySelector('svg');
                if (on) {
                    assessBtn.disabled = true;
                    icon.style.display = 'none';
                    label.textContent = 'AI đang phân tích…';
                    const sp = document.createElement('div');
                    sp.className = 'spinner';
                    assessBtn.insertBefore(sp, label);
                } else {
                    assessBtn.disabled = false;
                    assessBtn.querySelector('.spinner')?.remove();
                    icon.style.display = '';
                    label.textContent = 'Đánh giá chất lượng';
                }
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearAlert();

                const img = requiredImage();
                if (!img) { showAlert('Vui lòng tải ảnh "Toàn quả" để đánh giá.'); return; }
                if (!currentLoai) { showAlert('Vui lòng chọn loại nông sản.'); return; }

                const fd = new FormData();
                fd.append('anh', img);
                fd.append('loai', currentLoai);

                setLoading(true);
                try {
                    const res = await fetch('{{ url('/danh-gia') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: fd,
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const msg = data.message ||
                            (data.errors ? Object.values(data.errors).flat().join(' ') : 'Không chấm được ảnh, vui lòng thử lại.');
                        showAlert(msg);
                        return;
                    }

                    if (data.usable === false) {
                        renderUnusable(data.reason || data.reasoning);
                    } else {
                        renderResult(data);
                    }
                } catch (err) {
                    showAlert('Lỗi kết nối, vui lòng thử lại.');
                } finally {
                    setLoading(false);
                }
            });

            function resetToForm() {
                result.classList.remove('show');
                clearAlert();
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            document.getElementById('retryBtn')?.addEventListener('click', resetToForm);
            document.getElementById('retryBtn2')?.addEventListener('click', resetToForm);
        })();
    </script>
@endpush
