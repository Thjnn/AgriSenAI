@extends('layouts.app', ['page' => 'market'])

@section('title', 'Sàn thị trường nông sản')

@push('head')
    <style>
        /* Bien mau rieng cho trang San thi truong — port style tu Test-Giao-Dien/agrisenai-thi-truong.html
           (khung .pcard/.pgrid cua trang bang gia). Khong dinh nghia --nav-h/--safe-b (app.css da so huu). */
        .market-page {
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
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, .07);
            --r: 14px;
            --r-sm: 10px;
            --r-lg: 18px;
            color: var(--text);
        }
        .market-page * { box-sizing: border-box; }

        .market-page .page-head { margin-bottom: 8px; }
        .market-page .page-title { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .market-page .page-desc { font-size: 13px; color: var(--text-sec); font-weight: 500; line-height: 1.5; margin-bottom: 18px; }

        /* Thanh loc */
        .market-page .filter-bar {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            background: var(--white);
            border: 1px solid var(--gray-100);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm);
            padding: 14px;
            margin-bottom: 20px;
        }
        .market-page .filter-field { display: flex; flex-direction: column; min-width: 0; }
        .market-page .filter-field label { font-size: 12px; font-weight: 700; color: var(--text-sec); margin-bottom: 6px; }
        .market-page .filter-field select,
        .market-page .filter-field input {
            width: 100%; border: 1.5px solid var(--gray-200); border-radius: var(--r-sm);
            padding: 11px 13px; font-size: 13.5px; font-weight: 600; color: var(--text);
            background: var(--white); outline: none; transition: border-color .15s; font-family: inherit;
        }
        .market-page .filter-field select:focus,
        .market-page .filter-field input:focus { border-color: var(--green); }
        .market-page .filter-submit {
            border: none; background: var(--green); color: #fff; font-size: 13.5px; font-weight: 800;
            padding: 12px; border-radius: var(--r-sm); cursor: pointer; transition: background .15s;
        }
        .market-page .filter-submit:hover { background: #256427; }

        .market-page .sec-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .market-page .sec-title { font-size: 17px; font-weight: 800; }
        .market-page .sec-count { font-size: 12.5px; font-weight: 600; color: var(--text-sec); }

        /* Grid san pham */
        .market-page .pgrid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .market-page .pcard {
            display: block; background: var(--white); border-radius: var(--r); overflow: hidden;
            box-shadow: var(--shadow-sm); border: 1px solid var(--gray-100);
            transition: transform .15s, box-shadow .2s;
        }
        .market-page .pcard:hover { box-shadow: var(--shadow-md); }
        .market-page .pcard:active { transform: scale(.97); }
        .market-page .pcard-img { width: 100%; aspect-ratio: 1.1 / 1; position: relative; background: var(--gray-50); }
        .market-page .pcard-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .market-page .pcard-badge {
            position: absolute; left: 8px; bottom: 8px; right: 8px;
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px;
            background: var(--green); color: #fff; box-shadow: var(--shadow-sm);
            width: fit-content; max-width: calc(100% - 16px);
        }
        .market-page .pcard-body { padding: 12px; }
        .market-page .pcard-name { font-size: 14px; font-weight: 700; line-height: 1.3; margin-bottom: 3px; }
        .market-page .pcard-farm { font-size: 11px; color: var(--text-light); font-weight: 500; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .market-page .pcard-ocop { font-size: 11px; color: var(--amber); font-weight: 700; margin-bottom: 6px; }
        .market-page .pcard-price { font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
        .market-page .pcard-price span { font-size: 11px; font-weight: 500; color: var(--text-sec); }
        .market-page .pcard-rating { font-size: 12px; font-weight: 600; color: var(--text-sec); }

        .market-page .empty-state {
            text-align: center; padding: 40px 18px; background: var(--white);
            border: 1px dashed var(--gray-200); border-radius: var(--r-lg); color: var(--text-sec);
            font-size: 13.5px; font-weight: 600; margin-bottom: 24px;
        }

        .market-page .pagination-wrap { margin-bottom: 24px; }

        @media (min-width: 640px) {
            .market-page .page-title { font-size: 25px; }
            .market-page .filter-bar { grid-template-columns: repeat(4, 1fr); align-items: end; }
            .market-page .pgrid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
        }
        @media (min-width: 1024px) {
            .market-page .page-title { font-size: 27px; }
            .market-page .pgrid { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
@endpush

@section('content')
    <div class="market-page">
        <div class="page-head">
            <h1 class="page-title">Sàn thị trường nông sản</h1>
            <p class="page-desc">
                Nông sản đã được AI chấm điểm chất lượng và mang mã QR truy xuất nguồn gốc — mua yên tâm, bán minh bạch.
            </p>
        </div>

        <form class="filter-bar" method="GET" action="{{ url('/thi-truong') }}">
            <div class="filter-field">
                <label for="category_id">Loại nông sản</label>
                <select name="category_id" id="category_id">
                    <option value="">Tất cả loại</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="sort">Sắp xếp</label>
                <select name="sort" id="sort">
                    <option value="">Mới nhất</option>
                    <option value="score_desc" @selected(request('sort') === 'score_desc')>Điểm AI cao nhất</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                </select>
            </div>
            <div class="filter-field">
                <label for="min_score">Điểm AI tối thiểu</label>
                <input type="number" name="min_score" id="min_score" min="0" max="100" placeholder="VD: 70"
                       value="{{ request('min_score') }}">
            </div>
            <button type="submit" class="filter-submit">Lọc kết quả</button>
        </form>

        <div class="sec-head">
            <h2 class="sec-title">Sản phẩm chứng nhận</h2>
            <span class="sec-count">{{ $products->total() }} sản phẩm</span>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">Chưa có sản phẩm nào.</div>
        @else
            <div class="pgrid">
                {{-- Dung url() (khong route('products.show')) vi route do khai bao o Task 5;
                     doi sang route('products.show', $p) sau khi Task 5 xong neu muon. --}}
                @foreach ($products as $p)
                    <a href="{{ url('/thi-truong/'.$p->id) }}" class="pcard">
                        <div class="pcard-img">
                            <img src="{{ $p->primaryImage ? asset($p->primaryImage->image_url) : asset('images/logo-agrisenai-mark.png') }}"
                                 alt="{{ $p->name }}" onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
                            <span class="pcard-badge">✓ AI chứng nhận · {{ (int) $p->latestAssessment->quality_score }}/100</span>
                        </div>
                        <div class="pcard-body">
                            <div class="pcard-name">{{ $p->name }}</div>
                            <div class="pcard-farm">{{ $p->farmer->farmerProfile?->farm_name ?? $p->farmer->name }}</div>
                            @if ($p->farmer->farmerProfile?->ocop_star_rating)
                                <div class="pcard-ocop">OCOP {{ str_repeat('⭐', $p->farmer->farmerProfile->ocop_star_rating) }}</div>
                            @endif
                            <div class="pcard-price">{{ number_format($p->price, 0, ',', '.') }}đ<span>/{{ $p->unit }}</span></div>
                            <div class="pcard-rating">
                                @if ($p->reviews_count)
                                    ⭐ {{ number_format($p->reviews_avg_rating, 1) }} ({{ $p->reviews_count }})
                                @else
                                    <span style="color:#9e9e9e">Chưa có đánh giá</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
