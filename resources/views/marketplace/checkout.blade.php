@extends('layouts.app', ['page' => 'cart'])

@section('title', 'Thanh toán — AgriSenAI')

@push('head')
    <style>
        /* Cung bang mau/khung voi trang Gio hang (marketplace/cart.blade.php). */
        .checkout-page {
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
        .checkout-page * { box-sizing: border-box; }

        .checkout-page .page-head { margin-bottom: 18px; }
        .checkout-page .crumb { font-size: 13.5px; color: var(--text-sec); margin-bottom: 8px; }
        .checkout-page .crumb a:hover { color: var(--green); }
        .checkout-page .page-title { font-size: 22px; font-weight: 800; }

        .checkout-page .panel {
            background: var(--white); border: 1px solid var(--gray-100); border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm); padding: 16px; margin-bottom: 20px;
        }
        .checkout-page .panel-title { font-size: 16px; font-weight: 800; margin-bottom: 14px; }

        .checkout-page .sum-item {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 10px 0; border-top: 1px solid var(--gray-100); font-size: 13.5px;
        }
        .checkout-page .sum-item:first-child { border-top: 0; padding-top: 0; }
        .checkout-page .sum-item .name { font-weight: 700; }
        .checkout-page .sum-item .meta { color: var(--text-sec); font-size: 12.5px; }

        .checkout-page .sum-row.total {
            border-top: 1px solid var(--gray-100); margin-top: 6px; padding-top: 12px;
            display: flex; align-items: center; justify-content: space-between;
            font-size: 16px; font-weight: 800; color: var(--text);
        }

        .checkout-page label { display: block; font-size: 13.5px; font-weight: 700; margin-bottom: 6px; }
        .checkout-page textarea {
            width: 100%; border: 1.5px solid var(--gray-200); border-radius: var(--r-sm);
            padding: 10px 12px; font-family: inherit; font-size: 13.5px; resize: vertical;
            margin-bottom: 6px;
        }
        .checkout-page textarea:focus { outline: none; border-color: var(--green); }
        .checkout-page .field { margin-bottom: 16px; }
        .checkout-page .field-error { color: #c62828; font-size: 12.5px; margin-bottom: 6px; }

        .checkout-page .note-demo {
            background: var(--green-bg); color: var(--green); font-weight: 700; font-size: 12.5px;
            padding: 10px 12px; border-radius: var(--r-sm); margin-bottom: 16px;
        }

        .checkout-page .btn-place {
            display: block; width: 100%; text-align: center; border: 0; border-radius: var(--r-sm);
            font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer;
            padding: 14px 18px; background: var(--green); color: #fff;
        }
        .checkout-page .btn-place:hover { background: #276b2a; }

        @media (min-width: 900px) {
            .checkout-page .two-col { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 24px; align-items: start; }
        }
    </style>
@endpush

@section('content')
    <div class="checkout-page">
        <div class="page-head">
            <div class="crumb">
                <a href="{{ url('/') }}">Trang chủ</a> ›
                <a href="{{ route('cart.index') }}">Giỏ hàng</a> ›
                <span>Thanh toán</span>
            </div>
            <h1 class="page-title">Thanh toán</h1>
        </div>

        <div class="note-demo">
            Đây là luồng demo — chưa tích hợp thanh toán/giao vận thật.
        </div>

        <div class="two-col">
            <div class="col-main">
                <div class="panel">
                    <div class="panel-title">Thông tin giao hàng</div>

                    <form method="POST" action="{{ route('checkout.place') }}">
                        @csrf

                        <div class="field">
                            <label for="shipping_address">Địa chỉ giao hàng <span style="color:#c62828">*</span></label>
                            @error('shipping_address')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <textarea id="shipping_address" name="shipping_address" rows="3"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành...">{{ old('shipping_address') }}</textarea>
                        </div>

                        <div class="field">
                            <label for="note">Ghi chú (tuỳ chọn)</label>
                            <textarea id="note" name="note" rows="2"
                                      placeholder="Ví dụ: giao giờ hành chính...">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn-place">Đặt hàng</button>
                    </form>
                </div>
            </div>

            <aside class="col-aside">
                <div class="panel">
                    <div class="panel-title">Tóm tắt đơn hàng</div>

                    @foreach ($products as $p)
                        <div class="sum-item">
                            <div>
                                <div class="name">{{ $p->name }}</div>
                                <div class="meta">{{ $cart[$p->id] }} {{ $p->unit }} × {{ number_format($p->price, 0, ',', '.') }}đ</div>
                            </div>
                            <div>{{ number_format($p->price * $cart[$p->id], 0, ',', '.') }}đ</div>
                        </div>
                    @endforeach

                    <div class="sum-row total">
                        <span>Tổng cộng</span>
                        <b>{{ number_format($total, 0, ',', '.') }}đ</b>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
