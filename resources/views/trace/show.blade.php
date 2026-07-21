@extends('layouts.app', ['page' => 'truyxuat'])

@section('title', 'Truy xuất — '.$product->name.' — AgriSenAI')

@push('head')
    <style>
        /* Bien mau rieng cho trang Truy xuat nguon goc — port style tu
           Test-Giao-Dien/agrisenai-truy-xuat.html (khung .tx-*).
           Khong dinh nghia --nav-h/--safe-b (app.css da so huu). */
        .trace-page {
            --green: #2e7d32;
            --green-medium: #43a047;
            --green-light: #e8f5e9;
            --green-bg: #eff8f0;
            --white: #fff;
            --gray-50: #fafafa;
            --gray-100: #eeeeee;
            --gray-200: #e0e0e0;
            --text: #212121;
            --text-sec: #757575;
            --text-light: #9e9e9e;
            --amber: #b26a00;
            --red-bg: #fdecea;
            --red: #c62828;
            --gray-bg: #f5f5f5;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 14px rgba(0, 0, 0, .08);
            --r: 16px;
            --r-sm: 10px;
            color: var(--text);
        }
        .trace-page * { box-sizing: border-box; }

        .trace-page .tx-card {
            background: var(--white); border-radius: var(--r); box-shadow: var(--shadow-md);
            padding: 18px; margin-bottom: 16px;
        }

        /* Dai xac minh */
        .trace-page .tx-verify {
            display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 800;
            padding: 10px 14px; border-radius: 12px; margin-bottom: 16px;
        }
        .trace-page .tx-verify.verified { background: var(--green-light); color: var(--green); }
        .trace-page .tx-verify.mismatch { background: var(--red-bg); color: var(--red); }
        .trace-page .tx-verify.unverified { background: var(--gray-bg); color: var(--text-sec); }

        /* Header san pham + diem */
        .trace-page .tx-prod-name { font-size: 21px; font-weight: 800; line-height: 1.3; }
        .trace-page .tx-prod-sub { font-size: 13.5px; color: var(--text-sec); margin-top: 3px; }
        .trace-page .tx-head-row { display: flex; align-items: center; gap: 16px; justify-content: space-between; flex-wrap: wrap; }
        .trace-page .tx-score-badge {
            width: 76px; height: 76px; flex-shrink: 0; border-radius: 50%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #fff; font-weight: 800;
        }
        .trace-page .tx-score-badge b { font-size: 22px; line-height: 1; }
        .trace-page .tx-score-badge small { font-size: 9.5px; font-weight: 700; opacity: .9; margin-top: 2px; }
        .trace-page .tx-score-badge.score-hi { background: linear-gradient(160deg, #2e7d32, #1b5e20); }
        .trace-page .tx-score-badge.score-md { background: linear-gradient(160deg, #1e88e5, #1565c0); }
        .trace-page .tx-score-badge.score-lo { background: linear-gradient(160deg, #fb8c00, #ef6c00); }

        .trace-page .tx-lot {
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            margin-top: 16px; padding-top: 14px; border-top: 1px dashed var(--gray-200);
            font-size: 12.5px; color: var(--text-sec);
        }
        .trace-page .tx-lot b { display: block; font-size: 14.5px; color: var(--text); letter-spacing: .5px; margin-top: 2px; }

        /* Khoi chung */
        .trace-page .tx-sec-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; margin-bottom: 14px; }

        /* Anh AI */
        .trace-page .tx-ai-img { position: relative; width: 100%; aspect-ratio: 4/3; border-radius: var(--r-sm); overflow: hidden; background: var(--gray-50); margin-bottom: 10px; }
        .trace-page .tx-ai-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .trace-page .tx-ai-img-note { font-size: 12px; color: var(--text-light); text-align: center; }

        /* Ket qua AI */
        .trace-page .tx-ai { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .trace-page .tx-score {
            width: 88px; height: 88px; flex-shrink: 0; border-radius: 50%;
            background: conic-gradient(var(--green) calc(var(--pct, 0) * 1%), var(--gray-100) 0);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: var(--green); position: relative;
        }
        .trace-page .tx-score::before { content: ""; position: absolute; inset: 8px; border-radius: 50%; background: var(--white); }
        .trace-page .tx-score b { position: relative; font-size: 23px; line-height: 1; }
        .trace-page .tx-score small { position: relative; font-size: 10px; font-weight: 700; color: var(--text-sec); margin-top: 2px; }
        .trace-page .tx-facts { display: grid; gap: 8px; font-size: 13.5px; color: var(--text-sec); flex: 1; min-width: 180px; }
        .trace-page .tx-facts b { color: var(--text); }
        .trace-page .tx-warn {
            margin-top: 14px; padding: 12px 14px; background: #fff8e1; border: 1px solid #ffe0a3;
            border-radius: var(--r-sm); font-size: 13.5px; line-height: 1.6; color: #8a6100; font-weight: 600;
        }
        .trace-page .tx-note { margin-top: 14px; padding: 12px 14px; background: var(--green-bg); border-radius: var(--r-sm); font-size: 13px; line-height: 1.65; color: var(--text-sec); }

        /* Nong dan */
        .trace-page .tx-farmer { display: flex; gap: 14px; align-items: flex-start; }
        .trace-page .tx-farmer-ava {
            width: 56px; height: 56px; border-radius: 50%; background: var(--green-light); color: var(--green);
            font-weight: 800; font-size: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        }
        .trace-page .tx-farmer-name { font-size: 16px; font-weight: 800; }
        .trace-page .tx-farmer-sub { font-size: 13px; color: var(--text-sec); margin-top: 2px; }
        .trace-page .tx-ocop { color: #f5a623; font-size: 13px; margin-top: 4px; }
        .trace-page .tx-farmer-note { margin-top: 14px; font-size: 13.5px; line-height: 1.7; color: var(--text-sec); }

        /* Timeline */
        .trace-page .tl { position: relative; padding-left: 34px; }
        .trace-page .tl::before { content: ""; position: absolute; left: 13px; top: 8px; bottom: 8px; width: 2px; background: var(--gray-100); }
        .trace-page .tl-item { position: relative; padding-bottom: 22px; }
        .trace-page .tl-item:last-child { padding-bottom: 0; }
        .trace-page .tl-dot {
            position: absolute; left: -34px; top: 0; width: 28px; height: 28px; border-radius: 50%;
            background: var(--green); display: flex; align-items: center; justify-content: center;
            font-size: 13px; color: #fff; box-shadow: 0 0 0 3px var(--white);
        }
        .trace-page .tl-title { font-size: 15px; font-weight: 800; }
        .trace-page .tl-time { font-size: 12.5px; color: var(--text-light); margin-top: 1px; }
        .trace-page .tl-desc { font-size: 13.5px; color: var(--text-sec); line-height: 1.65; margin-top: 5px; }
        .trace-page .tl-loc { font-size: 12.5px; color: var(--text-light); margin-top: 4px; }
        .trace-page .tx-empty-note { font-size: 13.5px; color: var(--text-sec); }

        /* Chan trang bang chung ky thuat */
        .trace-page .tx-foot { text-align: center; font-size: 12px; color: var(--text-light); margin-top: 4px; line-height: 1.7; }
        .trace-page .tx-foot code { background: var(--gray-50); padding: 1px 5px; border-radius: 4px; font-size: 11.5px; }
        .trace-page .tx-foot-item { margin-bottom: 6px; }
        .trace-page .tx-foot details { display: inline; }
        .trace-page .tx-foot summary { display: inline; cursor: pointer; color: var(--green); font-weight: 700; }
        .trace-page .tx-foot .full-hash { word-break: break-all; display: block; margin-top: 6px; }

        @media (min-width: 700px) {
            .trace-page .tx-prod-name { font-size: 24px; }
        }
    </style>
@endpush

@section('content')
    @php
        $ripenessLabels = [
            'xanh' => 'Xanh',
            'vua_chin' => 'Vừa chín',
            'chin' => 'Chín',
            'qua_chin' => 'Quá chín',
        ];
        $stageLabels = [
            'thu_hoach' => 'Thu hoạch',
            'dong_goi' => 'Đóng gói',
            'van_chuyen' => 'Vận chuyển',
            'danh_gia_ai' => 'Đánh giá AI',
            'dang_ban' => 'Đang bán',
        ];
        $score = (int) $assessment->quality_score;
        $scoreClass = $score >= 90 ? 'score-hi' : ($score >= 75 ? 'score-md' : 'score-lo');
        $farmerProfile = $product->farmer->farmerProfile;
        $harvestLog = $product->traceLogs->firstWhere('stage', 'thu_hoach');
    @endphp

    <div class="trace-page">
        {{-- 1. Header: ten + danh muc + diem --}}
        <div class="tx-card">
            @php
                $verifyLabels = [
                    'verified' => '✓ Đã xác minh — dữ liệu chưa bị chỉnh sửa',
                    'mismatch' => '⚠ Dữ liệu không khớp mã băm gốc',
                    'unverified' => 'Bản ghi cũ, chưa có mã băm',
                ];
                $verifyStatus = $verification['status'] ?? 'unverified';
            @endphp
            {{-- 2. Dai xac minh --}}
            <div class="tx-verify {{ $verifyStatus }}">
                {{ $verifyLabels[$verifyStatus] ?? $verifyLabels['unverified'] }}
            </div>

            <div class="tx-head-row">
                <div>
                    <div class="tx-prod-name">{{ $product->name }}</div>
                    <div class="tx-prod-sub">
                        {{ $product->category->name ?? '' }}
                        @if ($farmerProfile?->region)
                            · 📍 {{ $farmerProfile->region }}
                        @endif
                    </div>
                </div>
                <div class="tx-score-badge {{ $scoreClass }}">
                    <b>{{ $score }}</b><small>/ 100 điểm</small>
                </div>
            </div>

            @if ($harvestLog)
                <div class="tx-lot">
                    <span>Mã lô<b>{{ $product->qr_code }}</b></span>
                    <span style="text-align:right">Thu hoạch<b>{{ $harvestLog->happened_at->format('d/m/Y') }}</b></span>
                </div>
            @endif
        </div>

        {{-- 3+4. Anh goc AI + ket qua AI --}}
        <div class="tx-card">
            <div class="tx-sec-title">🤖 Kết quả AI kiểm định</div>

            <div class="tx-ai-img">
                <img src="{{ $assessment->image_url ? \Illuminate\Support\Facades\Storage::url($assessment->image_url) : asset('images/logo-agrisenai-mark.png') }}"
                     alt="Ảnh AI quét {{ $product->name }}"
                     onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
            </div>
            <div class="tx-ai-img-note">Ảnh do AI chấm điểm khi quét, không phải ảnh marketing.</div>

            <div class="tx-ai" style="margin-top: 14px;">
                <div class="tx-score" style="--pct: {{ $score }}">
                    <b>{{ $score }}</b><small>/ 100 điểm</small>
                </div>
                <div class="tx-facts">
                    <div>🍈 Độ chín: <b>{{ $ripenessLabels[$assessment->ripeness_level] ?? $assessment->ripeness_level }}</b></div>
                    <div>📅 Đánh giá lúc: <b>{{ $assessment->assessed_at->format('d/m/Y H:i') }}</b></div>
                    <div>Đánh giá tự động bởi <b>Gemini Vision</b></div>
                </div>
            </div>

            @if ($assessment->defect_detected)
                <div class="tx-warn">⚠️ Phát hiện khuyết tật: {{ $assessment->defect_note }}</div>
            @endif
        </div>

        {{-- 5. Nha vuon --}}
        <div class="tx-card">
            <div class="tx-sec-title">👨‍🌾 Nhà vườn</div>
            <div class="tx-farmer">
                <div class="tx-farmer-ava">{{ mb_substr($farmerProfile?->farm_name ?? $product->farmer->name, 0, 1) }}</div>
                <div>
                    <div class="tx-farmer-name">{{ $farmerProfile?->farm_name ?? $product->farmer->name }}</div>
                    @if ($farmerProfile?->region)
                        <div class="tx-farmer-sub">📍 {{ $farmerProfile->region }}</div>
                    @endif
                    @if ($farmerProfile?->ocop_star_rating)
                        <div class="tx-ocop">{{ str_repeat('⭐', $farmerProfile->ocop_star_rating) }} Chứng nhận OCOP {{ $farmerProfile->ocop_star_rating }} sao</div>
                    @endif
                </div>
            </div>
            @if ($farmerProfile?->description)
                <p class="tx-farmer-note">{{ $farmerProfile->description }}</p>
            @endif
        </div>

        {{-- 6. Nhat ky truy xuat --}}
        <div class="tx-card">
            <div class="tx-sec-title">🧾 Nhật ký truy xuất</div>
            @if ($product->traceLogs->isEmpty())
                <p class="tx-empty-note">Chưa có dữ liệu truy xuất cho lô hàng này.</p>
            @else
                <div class="tl">
                    @foreach ($product->traceLogs as $log)
                        <div class="tl-item">
                            <div class="tl-dot">●</div>
                            <div class="tl-title">{{ $stageLabels[$log->stage] ?? $log->stage }}</div>
                            <div class="tl-time">{{ $log->happened_at->format('d/m/Y') }}</div>
                            @if ($log->description)
                                <div class="tl-desc">{{ $log->description }}</div>
                            @endif
                            @if ($log->location)
                                <div class="tl-loc">📍 {{ $log->location }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 7. Chan trang: bang chung ky thuat --}}
        <div class="tx-card tx-foot">
            <div class="tx-foot-item">Mã QR: <code>{{ $product->qr_code }}</code></div>
            <div class="tx-foot-item">
                Mã băm toàn vẹn:
                @if ($assessment->integrity_hash)
                    <code>{{ substr($assessment->integrity_hash, 0, 16).'…' }}</code>
                    <details>
                        <summary>Xem đầy đủ</summary>
                        <code class="full-hash">{{ $assessment->integrity_hash }}</code>
                    </details>
                @else
                    <code>chưa có</code>
                @endif
            </div>
            <div>Mã băm SHA-256 — bất kỳ ai cũng kiểm chứng bản ghi này không bị sửa sau khi tạo.</div>
        </div>
    </div>
@endsection
