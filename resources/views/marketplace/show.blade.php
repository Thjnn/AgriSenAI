@extends('layouts.app', ['page' => 'market'])

@section('title', $product->name.' — AgriSenAI')

@push('head')
    <style>
        /* Bien mau rieng cho trang Chi tiet san pham — port style tu
           Test-Giao-Dien/agrisenai-chi-tiet-san-pham.html (khung .dp-*).
           Khong dinh nghia --nav-h/--safe-b (app.css da so huu). */
        .detail-page {
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
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, .07);
            --r: 14px;
            --r-sm: 10px;
            --r-lg: 18px;
            color: var(--text);
        }
        .detail-page * { box-sizing: border-box; }

        .detail-page .crumb {
            display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
            font-size: 13.5px; color: var(--text-sec); margin-bottom: 18px;
        }
        .detail-page .crumb a:hover { color: var(--green); }
        .detail-page .crumb .sep { color: var(--gray-300); }
        .detail-page .crumb .cur { color: var(--text); font-weight: 700; }

        .detail-page .dp-top {
            display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; margin-bottom: 24px;
        }

        /* Anh */
        .detail-page .dp-main-img {
            position: relative; width: 100%; aspect-ratio: 4 / 3; border-radius: var(--r-lg);
            overflow: hidden; background: var(--gray-50); box-shadow: var(--shadow-sm);
        }
        .detail-page .dp-main-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .detail-page .dp-ai-badge {
            position: absolute; top: 14px; left: 14px; display: flex; align-items: center; gap: 6px;
            background: rgba(255, 255, 255, .94); color: var(--green); font-weight: 800; font-size: 13px;
            padding: 7px 12px; border-radius: 999px; box-shadow: var(--shadow-sm);
        }
        .detail-page .dp-thumbs { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .detail-page .dp-thumb {
            width: 72px; height: 72px; border-radius: var(--r-sm); overflow: hidden;
            border: 2px solid transparent; background: var(--gray-50); padding: 0;
        }
        .detail-page .dp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .detail-page .dp-thumb.active { border-color: var(--green); }

        /* Cot thong tin */
        .detail-page .dp-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .detail-page .dp-badge {
            font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 8px;
            background: var(--green-light); color: var(--green);
        }
        .detail-page .dp-badge.ocop { background: #fff4e5; color: #b26a00; }
        .detail-page .dp-badge.stock { background: var(--gray-50); color: var(--text-sec); }
        .detail-page .dp-title { font-size: 24px; font-weight: 800; line-height: 1.25; margin-bottom: 8px; }
        .detail-page .dp-sub { font-size: 14px; color: var(--text-sec); margin-bottom: 16px; }

        .detail-page .dp-price-box { background: var(--green-bg); border-radius: var(--r); padding: 16px 18px; margin-bottom: 18px; }
        .detail-page .dp-price { font-size: 30px; font-weight: 800; color: var(--green); line-height: 1; }
        .detail-page .dp-price span { font-size: 15px; font-weight: 700; color: var(--text-sec); }

        .detail-page .dp-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 18px; margin-bottom: 20px; }
        .detail-page .dp-meta-item { font-size: 13.5px; color: var(--text-sec); }
        .detail-page .dp-meta-item b { display: block; color: var(--text); font-size: 15px; margin-top: 2px; }

        .detail-page .dp-qty { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
        .detail-page .dp-qty label { font-size: 14px; font-weight: 700; }
        .detail-page .dp-qty input[type="number"] {
            width: 76px; border: 1.5px solid var(--gray-200); border-radius: var(--r-sm);
            padding: 9px 10px; font-family: inherit; font-size: 14px; text-align: center;
        }
        .detail-page .dp-btn {
            border: 0; border-radius: var(--r-sm); font-family: inherit; font-size: 15px;
            font-weight: 800; cursor: pointer; padding: 13px 22px; background: var(--green); color: #fff;
        }
        .detail-page .dp-btn:hover { background: #276b2a; }

        /* The chung */
        .detail-page .dp-card {
            background: var(--white); border: 1px solid var(--gray-100); border-radius: var(--r-lg);
            padding: 20px; box-shadow: var(--shadow-sm); margin-bottom: 20px;
        }
        .detail-page .dp-card-title { display: flex; align-items: center; gap: 8px; font-size: 17px; font-weight: 800; margin-bottom: 14px; }
        .detail-page .dp-desc { font-size: 14.5px; line-height: 1.75; color: var(--text-sec); }

        .detail-page .dp-two { display: grid; grid-template-columns: 1fr; gap: 20px; }

        /* AI */
        .detail-page .dp-ai-row { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .detail-page .dp-score {
            width: 92px; height: 92px; flex-shrink: 0; border-radius: 50%;
            background: conic-gradient(var(--green) calc(var(--pct, 0) * 1%), var(--gray-100) 0);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: var(--green); position: relative;
        }
        .detail-page .dp-score::before { content: ""; position: absolute; inset: 8px; border-radius: 50%; background: var(--white); }
        .detail-page .dp-score b { position: relative; font-size: 22px; line-height: 1; }
        .detail-page .dp-score small { position: relative; font-size: 10px; color: var(--text-sec); font-weight: 700; margin-top: 2px; }
        .detail-page .dp-ai-facts { flex: 1; display: grid; gap: 10px; min-width: 180px; }
        .detail-page .dp-fact { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text-sec); }
        .detail-page .dp-fact b { color: var(--text); }
        .detail-page .dp-warn {
            margin-top: 14px; padding: 12px 14px; background: #fff8e1; border: 1px solid #ffe0a3;
            border-radius: var(--r-sm); font-size: 13.5px; line-height: 1.6; color: #8a6100; font-weight: 600;
        }
        .detail-page .dp-ai-note {
            margin-top: 14px; padding: 12px 14px; background: var(--green-bg); border-radius: var(--r-sm);
            font-size: 13px; color: var(--text-sec);
        }

        /* Nong dan */
        .detail-page .dp-farmer-row { display: flex; gap: 14px; align-items: flex-start; }
        .detail-page .dp-farmer-ava {
            width: 56px; height: 56px; border-radius: 50%; background: var(--green-light);
            color: var(--green); font-weight: 800; font-size: 22px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .detail-page .dp-farmer-name { font-size: 16px; font-weight: 800; }
        .detail-page .dp-farmer-sub { font-size: 13.5px; color: var(--text-sec); margin-top: 2px; }
        .detail-page .dp-stars { color: #f5a623; font-size: 13px; margin-top: 4px; }
        .detail-page .dp-farmer-note { margin-top: 14px; font-size: 13.5px; line-height: 1.7; color: var(--text-sec); }

        /* Timeline truy xuat */
        .detail-page .tl { position: relative; padding-left: 30px; }
        .detail-page .tl::before {
            content: ""; position: absolute; left: 11px; top: 6px; bottom: 6px; width: 2px; background: var(--gray-100);
        }
        .detail-page .tl-item { position: relative; padding-bottom: 20px; }
        .detail-page .tl-item:last-child { padding-bottom: 0; }
        .detail-page .tl-dot {
            position: absolute; left: -30px; top: 1px; width: 24px; height: 24px; border-radius: 50%;
            background: var(--green); display: flex; align-items: center; justify-content: center; font-size: 12px; color: #fff;
        }
        .detail-page .tl-head { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
        .detail-page .tl-title { font-size: 14.5px; font-weight: 800; }
        .detail-page .tl-time { font-size: 12.5px; color: var(--text-light); }
        .detail-page .tl-desc { font-size: 13.5px; color: var(--text-sec); line-height: 1.65; margin-top: 3px; }
        .detail-page .tl-loc { font-size: 12.5px; color: var(--text-light); margin-top: 3px; }

        /* Danh gia */
        .detail-page .dp-rv { display: flex; gap: 14px; padding: 16px 0; border-top: 1px solid var(--gray-100); }
        .detail-page .dp-rv:first-of-type { border-top: 0; padding-top: 0; }
        .detail-page .dp-rv-ava {
            width: 42px; height: 42px; border-radius: 50%; background: var(--green-light); color: var(--green);
            font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .detail-page .dp-rv-name { font-size: 14.5px; font-weight: 800; }
        .detail-page .dp-rv-meta { font-size: 12.5px; color: var(--text-light); margin: 2px 0 6px; }
        .detail-page .dp-rv-text { font-size: 14px; color: var(--text-sec); line-height: 1.65; }
        .detail-page .dp-empty-note { font-size: 13.5px; color: var(--text-sec); }

        @media (min-width: 900px) {
            .detail-page .dp-top { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; }
            .detail-page .dp-two { grid-template-columns: 1fr 1fr; }
            .detail-page .dp-title { font-size: 28px; }
            .detail-page .dp-price { font-size: 32px; }
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
        $farmerProfile = $product->farmer->farmerProfile;
        $mainImage = $product->images->first();
    @endphp

    <div class="detail-page">
        <div class="crumb">
            <a href="{{ url('/thi-truong') }}">Sàn thị trường</a>
            <span class="sep">/</span>
            <span class="cur">{{ $product->name }}</span>
        </div>

        <div class="dp-top">
            <div>
                <div class="dp-main-img">
                    @if ($assessment)
                        <span class="dp-ai-badge">🤖 AI {{ (int) $assessment->quality_score }}/100</span>
                    @endif
                    <img src="{{ $mainImage ? asset($mainImage->image_url) : asset('images/logo-agrisenai-mark.png') }}"
                         alt="{{ $product->name }}"
                         onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
                </div>
                @if ($product->images->count() > 1)
                    <div class="dp-thumbs">
                        @foreach ($product->images as $img)
                            <button type="button" class="dp-thumb @if($loop->first) active @endif">
                                <img src="{{ asset($img->image_url) }}" alt="Ảnh {{ $loop->iteration }} của {{ $product->name }}"
                                     onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <div class="dp-badges">
                    @if ($product->category)
                        <span class="dp-badge">{{ $product->category->name }}</span>
                    @endif
                    @if ($farmerProfile?->ocop_star_rating)
                        <span class="dp-badge ocop">⭐ OCOP {{ $farmerProfile->ocop_star_rating }} sao</span>
                    @endif
                    <span class="dp-badge stock">Còn {{ number_format($product->quantity_available, 0, ',', '.') }} {{ $product->unit }}</span>
                </div>

                <h1 class="dp-title">{{ $product->name }}</h1>
                <div class="dp-sub">
                    📍 {{ $farmerProfile?->region }} · {{ $farmerProfile?->farm_name ?? $product->farmer->name }}
                </div>

                <div class="dp-price-box">
                    <div class="dp-price">{{ number_format($product->price, 0, ',', '.') }}đ<span>/{{ $product->unit }}</span></div>
                </div>

                @if ($assessment)
                    <div class="dp-meta">
                        <div class="dp-meta-item">Độ chín (AI)<b>{{ $ripenessLabels[$assessment->ripeness_level] ?? $assessment->ripeness_level }}</b></div>
                        <div class="dp-meta-item">Ngày đánh giá<b>{{ $assessment->assessed_at->format('d/m/Y H:i') }}</b></div>
                    </div>
                @endif

                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <div class="dp-qty">
                        <label for="quantity">Số lượng</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1">
                        <button type="submit" class="dp-btn">🛒 Thêm vào giỏ</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($product->description)
            <div class="dp-card">
                <div class="dp-card-title">📝 Mô tả sản phẩm</div>
                <p class="dp-desc">{{ $product->description }}</p>
            </div>
        @endif

        <div class="dp-two">
            @if ($assessment)
                <div class="dp-card">
                    <div class="dp-card-title">🤖 AI chứng nhận chất lượng</div>
                    <div class="dp-ai-row">
                        <div class="dp-score" style="--pct: {{ (int) $assessment->quality_score }}">
                            <b>{{ (int) $assessment->quality_score }}</b><small>/ 100 điểm</small>
                        </div>
                        <div class="dp-ai-facts">
                            <div class="dp-fact">🍈 Độ chín: <b>{{ $ripenessLabels[$assessment->ripeness_level] ?? $assessment->ripeness_level }}</b></div>
                            <div class="dp-fact">📅 Đánh giá lúc: <b>{{ $assessment->assessed_at->format('d/m/Y H:i') }}</b></div>
                            <div class="dp-fact">Đánh giá bởi AI Gemini Vision</div>
                        </div>
                    </div>
                    @if ($assessment->defect_detected)
                        <div class="dp-warn">⚠️ Phát hiện khuyết tật: {{ $assessment->defect_note }}</div>
                    @endif
                </div>
            @endif

            <div class="dp-card">
                <div class="dp-card-title">👨‍🌾 Nhà vườn</div>
                <div class="dp-farmer-row">
                    <div class="dp-farmer-ava">{{ mb_substr($farmerProfile?->farm_name ?? $product->farmer->name, 0, 1) }}</div>
                    <div>
                        <div class="dp-farmer-name">{{ $farmerProfile?->farm_name ?? $product->farmer->name }}</div>
                        <div class="dp-farmer-sub">📍 {{ $farmerProfile?->region }}</div>
                        @if ($farmerProfile?->ocop_star_rating)
                            <div class="dp-stars">{{ str_repeat('⭐', $farmerProfile->ocop_star_rating) }} OCOP {{ $farmerProfile->ocop_star_rating }} sao</div>
                        @endif
                    </div>
                </div>
                @if ($farmerProfile?->description)
                    <p class="dp-farmer-note">{{ $farmerProfile->description }}</p>
                @endif
            </div>
        </div>

        <div class="dp-card">
            <div class="dp-card-title">🔗 Truy xuất nguồn gốc</div>
            @if ($product->traceLogs->isEmpty())
                <p class="dp-empty-note">Chưa có dữ liệu truy xuất cho sản phẩm này.</p>
            @else
                <div class="tl">
                    @foreach ($product->traceLogs as $log)
                        <div class="tl-item">
                            <div class="tl-dot">●</div>
                            <div class="tl-head">
                                <span class="tl-title">{{ $stageLabels[$log->stage] ?? $log->stage }}</span>
                                <span class="tl-time">{{ $log->happened_at->format('d/m/Y') }}</span>
                            </div>
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

        <div class="dp-card">
            <div class="dp-card-title">⭐ Đánh giá của người mua ({{ $product->reviews->count() }})</div>
            @if ($product->reviews->isEmpty())
                <p class="dp-empty-note">Chưa có đánh giá nào.</p>
            @else
                @foreach ($product->reviews as $review)
                    <div class="dp-rv">
                        <div class="dp-rv-ava">{{ mb_substr($review->buyer->name, 0, 1) }}</div>
                        <div>
                            <div class="dp-rv-name">{{ $review->buyer->name }}</div>
                            <div class="dp-rv-meta">{{ str_repeat('⭐', $review->rating) }}</div>
                            <div class="dp-rv-text">{{ $review->comment }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
