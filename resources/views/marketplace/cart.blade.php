@extends('layouts.app', ['page' => 'cart'])

@section('title', 'Giỏ hàng — AgriSenAI')

@push('head')
    <style>
        /* Bien mau rieng cho trang Gio hang — port style tu
           Test-Giao-Dien/agrisenai-gio-hang.html (khung .panel/.cart-item).
           Khong dinh nghia --nav-h/--safe-b (app.css da so huu). */
        .cart-page {
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
        .cart-page * { box-sizing: border-box; }

        .cart-page .page-head { margin-bottom: 18px; }
        .cart-page .crumb { font-size: 13.5px; color: var(--text-sec); margin-bottom: 8px; }
        .cart-page .crumb a:hover { color: var(--green); }
        .cart-page .page-title { font-size: 22px; font-weight: 800; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .cart-page .count { font-size: 12.5px; font-weight: 700; color: var(--text-sec); background: var(--gray-50); padding: 4px 10px; border-radius: 999px; }

        .cart-page .flash-ok {
            background: var(--green-light); color: var(--green); font-weight: 700; font-size: 13.5px;
            padding: 12px 14px; border-radius: var(--r-sm); margin-bottom: 16px;
        }

        .cart-page .panel {
            background: var(--white); border: 1px solid var(--gray-100); border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm); padding: 16px; margin-bottom: 20px;
        }
        .cart-page .panel-title { font-size: 16px; font-weight: 800; margin-bottom: 14px; }

        .cart-page .cart-item {
            display: flex; gap: 12px; padding: 14px 0; border-top: 1px solid var(--gray-100);
        }
        .cart-page .cart-item:first-child { border-top: 0; padding-top: 0; }
        .cart-page .cart-thumb {
            width: 84px; height: 84px; border-radius: var(--r-sm); object-fit: cover; flex-shrink: 0;
            background: var(--gray-50);
        }
        .cart-page .cart-info { flex: 1; min-width: 0; }
        .cart-page .cart-name { font-size: 14.5px; font-weight: 800; margin-bottom: 6px; }
        .cart-page .cart-row2 {
            display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 10px; flex-wrap: wrap;
        }
        .cart-page .qty-form { display: flex; align-items: center; gap: 8px; }
        .cart-page .qty-form input[type="number"] {
            width: 64px; border: 1.5px solid var(--gray-200); border-radius: var(--r-sm);
            padding: 7px 8px; font-family: inherit; font-size: 13.5px; text-align: center;
        }
        .cart-page .qty-form button {
            border: 1.5px solid var(--gray-200); background: var(--white); border-radius: var(--r-sm);
            padding: 7px 12px; font-family: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer;
        }
        .cart-page .qty-form button:hover { border-color: var(--green); color: var(--green); }
        .cart-page .cart-price { font-size: 15px; font-weight: 800; color: var(--text); }
        .cart-page .cart-price span { font-size: 11.5px; font-weight: 500; color: var(--text-sec); }
        .cart-page .cart-remove {
            border: 0; background: none; color: var(--text-light); cursor: pointer; font-size: 12.5px;
            font-weight: 700; padding: 7px 4px;
        }
        .cart-page .cart-remove:hover { color: #c62828; }

        .cart-page .sum-row {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13.5px; color: var(--text-sec); padding: 8px 0;
        }
        .cart-page .sum-row.total { border-top: 1px solid var(--gray-100); margin-top: 6px; padding-top: 12px; font-size: 16px; font-weight: 800; color: var(--text); }

        .cart-page .btn-checkout {
            display: block; width: 100%; text-align: center; border: 0; border-radius: var(--r-sm);
            font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer;
            padding: 14px 18px; background: var(--green); color: #fff; margin-top: 14px;
            text-decoration: none;
        }
        .cart-page .btn-checkout:hover { background: #276b2a; }

        .cart-page .empty {
            text-align: center; padding: 48px 18px; background: var(--white);
            border: 1px dashed var(--gray-200); border-radius: var(--r-lg); color: var(--text-sec);
        }
        .cart-page .empty .ico { font-size: 42px; margin-bottom: 10px; }
        .cart-page .empty h3 { font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
        .cart-page .empty p { font-size: 13.5px; margin-bottom: 18px; }
        .cart-page .empty a {
            display: inline-block; border: 0; border-radius: var(--r-sm); font-weight: 800;
            font-size: 14px; padding: 12px 22px; background: var(--green); color: #fff; text-decoration: none;
        }
        .cart-page .empty a:hover { background: #276b2a; }

        @media (min-width: 900px) {
            .cart-page .two-col { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 24px; align-items: start; }
        }
    </style>
@endpush

@section('content')
    <div class="cart-page">
        <div class="page-head">
            <div class="crumb">
                <a href="{{ url('/') }}">Trang chủ</a> ›
                <span>Giỏ hàng</span>
            </div>
            <h1 class="page-title">
                Giỏ hàng
                <span class="count">{{ $products->count() }} sản phẩm</span>
            </h1>
        </div>

        @if (session('ok'))
            <div class="flash-ok">{{ session('ok') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="empty">
                <div class="ico">🛒</div>
                <h3>Giỏ hàng trống</h3>
                <p>Khám phá nông sản đã được AI chứng nhận trên sàn thị trường.</p>
                <a href="{{ url('/thi-truong') }}">Đi chợ ngay</a>
            </div>
        @else
            <div class="two-col">
                <div class="col-main">
                    <div class="panel">
                        <div class="panel-title">🛒 Sản phẩm đã chọn</div>

                        @foreach ($products as $p)
                            <div class="cart-item">
                                <img class="cart-thumb"
                                     src="{{ $p->primaryImage ? asset($p->primaryImage->image_url) : asset('images/logo-agrisenai-mark.png') }}"
                                     alt="{{ $p->name }}"
                                     onerror="this.src='{{ asset('images/logo-agrisenai-mark.png') }}'">
                                <div class="cart-info">
                                    <div class="cart-name">{{ $p->name }}</div>
                                    <div class="cart-row2">
                                        <form class="qty-form" method="POST" action="{{ route('cart.update', $p) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $cart[$p->id] }}" min="1" step="1">
                                            <button type="submit">Cập nhật</button>
                                        </form>
                                        <div style="display:flex;align-items:center;gap:10px">
                                            <div class="cart-price">
                                                {{ number_format($p->price * $cart[$p->id], 0, ',', '.') }}đ
                                                <span>({{ number_format($p->price, 0, ',', '.') }}đ/{{ $p->unit }})</span>
                                            </div>
                                            <form method="POST" action="{{ route('cart.remove', $p) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="cart-remove">Xoá</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="col-aside">
                    <div class="panel">
                        <div class="panel-title">Tóm tắt đơn hàng</div>

                        <div class="sum-row total">
                            <span>Tổng cộng</span>
                            <b>{{ number_format($total, 0, ',', '.') }}đ</b>
                        </div>

                        {{-- Dung url() (khong route('checkout.form')) vi route do khai bao o Task 7;
                             doi sang route('checkout.form') sau khi Task 7 xong neu muon. --}}
                        <a href="{{ url('/thanh-toan') }}" class="btn-checkout">Tiến hành đặt hàng</a>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
