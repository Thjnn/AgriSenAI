@extends('layouts.app', ['page' => 'cart'])

@section('title', 'Đặt hàng thành công — AgriSenAI')

@push('head')
    <style>
        /* Cung bang mau/khung voi trang Gio hang / Thanh toan. */
        .order-confirm-page {
            --green: #2e7d32;
            --green-medium: #43a047;
            --green-light: #e8f5e9;
            --green-bg: #eff8f0;
            --white: #fff;
            --bg: #fafafa;
            --gray-50: #f5f5f5;
            --gray-100: #eee;
            --gray-200: #e0e0e0;
            --text: #212121;
            --text-sec: #757575;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --r-sm: 10px;
            --r-lg: 18px;
            color: var(--text);
        }
        .order-confirm-page * { box-sizing: border-box; }

        .order-confirm-page .hero {
            text-align: center; background: var(--white); border: 1px solid var(--gray-100);
            border-radius: var(--r-lg); box-shadow: var(--shadow-sm); padding: 32px 18px; margin-bottom: 20px;
        }
        .order-confirm-page .hero .ico { font-size: 46px; margin-bottom: 10px; }
        .order-confirm-page .hero h1 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .order-confirm-page .hero .code { font-size: 15px; font-weight: 700; color: var(--green); margin-bottom: 6px; }
        .order-confirm-page .hero .status {
            display: inline-block; background: var(--green-light); color: var(--green);
            font-size: 12.5px; font-weight: 700; padding: 5px 12px; border-radius: 999px;
        }

        .order-confirm-page .panel {
            background: var(--white); border: 1px solid var(--gray-100); border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm); padding: 16px; margin-bottom: 20px;
        }
        .order-confirm-page .panel-title { font-size: 16px; font-weight: 800; margin-bottom: 14px; }

        .order-confirm-page table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .order-confirm-page th, .order-confirm-page td {
            text-align: left; padding: 10px 6px; border-top: 1px solid var(--gray-100);
        }
        .order-confirm-page th { color: var(--text-sec); font-weight: 700; font-size: 12.5px; }
        .order-confirm-page tbody tr:first-child td { border-top: 0; }
        .order-confirm-page .num { text-align: right; }

        .order-confirm-page .sum-row.total {
            border-top: 1px solid var(--gray-100); margin-top: 6px; padding-top: 12px;
            display: flex; align-items: center; justify-content: space-between;
            font-size: 16px; font-weight: 800; color: var(--text);
        }

        .order-confirm-page .addr { font-size: 13.5px; color: var(--text-sec); margin-bottom: 4px; }

        .order-confirm-page .btn-continue {
            display: block; width: 100%; text-align: center; border: 0; border-radius: var(--r-sm);
            font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer;
            padding: 14px 18px; background: var(--green); color: #fff; text-decoration: none;
        }
        .order-confirm-page .btn-continue:hover { background: #276b2a; }
    </style>
@endpush

@section('content')
    <div class="order-confirm-page">
        <div class="hero">
            <div class="ico">✅</div>
            <h1>Đặt hàng thành công</h1>
            <div class="code">Mã đơn hàng: {{ $order->order_code }}</div>
            <span class="status">Chờ xác nhận</span>
        </div>

        <div class="panel">
            <div class="panel-title">Địa chỉ giao hàng</div>
            <div class="addr">{{ $order->shipping_address }}</div>
            @if ($order->note)
                <div class="addr">Ghi chú: {{ $order->note }}</div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-title">Sản phẩm đã đặt</div>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="num">SL</th>
                        <th class="num">Đơn giá</th>
                        <th class="num">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td class="num">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                            <td class="num">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sum-row total">
                <span>Tổng cộng</span>
                <b>{{ number_format($order->total_amount, 0, ',', '.') }}đ</b>
            </div>
        </div>

        <a href="{{ route('marketplace.index') }}" class="btn-continue">Tiếp tục mua</a>
    </div>
@endsection
