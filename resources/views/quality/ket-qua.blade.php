@extends('quality.layout')

@section('title', 'Kết quả chấm chất lượng')

@section('css')
  .top-row { display: flex; gap: 20px; flex-wrap: wrap; }
  .top-row .anh { flex: 0 0 240px; }
  .top-row .anh img { width: 100%; border-radius: 14px; border: 2px solid var(--line); display: block; }
  .top-row .anh .cap { font-size: 12px; color: var(--muted); text-align: center; margin-top: 6px; font-weight: 600; }
  .top-row .tomtat { flex: 1 1 320px; }

  .badge {
    display: inline-block; padding: 8px 16px; border-radius: 999px;
    font-weight: 900; font-size: 18px; letter-spacing: -.2px;
  }
  .g-dac_biet  { background: var(--green-light); color: var(--green-dark); border: 2px solid var(--green); }
  .g-hang_1    { background: var(--yellow-light); color: #8A6A00; border: 2px solid var(--yellow); }
  .g-hang_2    { background: var(--orange-light); color: #A85F00; border: 2px solid var(--orange); }
  .g-khong_dat { background: var(--red-light); color: #B32020; border: 2px solid var(--red); }

  .diem { font-size: 46px; font-weight: 900; line-height: 1.1; margin: 14px 0 2px; }
  .diem span { font-size: 20px; color: var(--muted); font-weight: 700; }
  .meta { font-size: 14px; color: var(--muted); font-weight: 600; }

  h2 { font-size: 17px; font-weight: 900; margin-bottom: 14px; }

  .bar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
  .bar-row .ten { flex: 0 0 110px; font-weight: 700; font-size: 14px; }
  .bar-row .track { flex: 1; height: 12px; background: var(--line); border-radius: 999px; overflow: hidden; }
  .bar-row .fill { height: 100%; border-radius: 999px; background: var(--green); }
  .bar-row .num { flex: 0 0 38px; text-align: right; font-weight: 800; font-size: 14px; }

  .defect {
    display: flex; gap: 12px; align-items: flex-start;
    border: 2px solid var(--line); border-radius: 12px;
    padding: 12px 14px; margin-bottom: 10px;
  }
  .defect .pct {
    flex: 0 0 52px; text-align: center; font-weight: 900; font-size: 17px;
    background: var(--bg); border-radius: 8px; padding: 6px 0;
  }
  .defect .d-ten { font-weight: 800; font-size: 14px; }
  .defect .d-note { font-size: 13px; color: var(--muted); font-weight: 600; }
  .sev { font-size: 11px; font-weight: 900; padding: 2px 8px; border-radius: 999px; margin-left: 6px; }
  .sev-nhe { background: var(--green-light); color: var(--green-dark); }
  .sev-trung_binh { background: var(--yellow-light); color: #8A6A00; }
  .sev-nang { background: var(--red-light); color: #B32020; }

  .box-ly-do { background: var(--bg); border-radius: 12px; padding: 16px; font-size: 15px; }
  .box-advice {
    background: var(--green-pale); border: 2px solid var(--green);
    border-radius: 12px; padding: 16px; font-size: 15px; font-weight: 600;
  }

  .rule { border-color: var(--orange); background: #FFF8F0; }
  .rule ul { margin: 8px 0 0 18px; font-size: 14px; font-weight: 600; }
  .rule li { margin-bottom: 6px; }
@endsection

@section('body')

  @php
    $g = $kq['grade'];
    $re = $kq['rule_engine'] ?? [];
  @endphp

  @if (! ($kq['usable'] ?? true))
    <div class="alert">
      <strong>Ảnh này chưa chấm được.</strong><br>
      {{ $kq['reasoning'] ?? 'Ảnh không nhìn rõ nông sản.' }}
    </div>
    <a href="{{ url('/danh-gia') }}" class="back">← Chụp lại và thử lần nữa</a>
  @else

  <h1>Kết quả</h1>
  <p class="sub">AI chấm dựa trên những gì nhìn thấy trong ảnh, neo theo TCVN 9766:2013.</p>

  {{-- Tóm tắt --}}
  <div class="card">
    <div class="top-row">
      <div class="anh">
        <img src="{{ $anh }}" alt="Ảnh đã chấm">
        <div class="cap">Ảnh sau chuẩn hoá — đúng thứ AI nhìn thấy</div>
      </div>

      <div class="tomtat">
        <span class="badge g-{{ $g }}">{{ $grades[$g]['nhan'] }}</span>

        <div class="diem">
          {{ number_format($kq['overall_score'], 1) }}<span>/10</span>
        </div>

        <div class="meta">
          {{ $kq['detected_product'] ?? 'Nông sản' }}
          &nbsp;·&nbsp; Độ tin cậy {{ round(($kq['confidence'] ?? 0) * 100) }}%
        </div>
      </div>
    </div>
  </div>

  {{-- Rule engine: chỉ hiện khi có can thiệp --}}
  @if (($re['da_ha_hang'] ?? false) || ($re['can_nguoi_xem_lai'] ?? false))
    <div class="card rule">
      <h2>⚖️ Đối chiếu tiêu chuẩn</h2>

      @if ($re['da_ha_hang'] ?? false)
        <p style="font-weight:700">
          AI chấm <strong>{{ $grades[$re['hang_ai_cham']]['nhan'] }}</strong>,
          nhưng đối chiếu TCVN thì phải hạ xuống
          <strong>{{ $grades[$re['hang_cuoi_cung']]['nhan'] }}</strong>.
        </p>
      @endif

      <ul>
        @foreach ($re['luat_da_ap_dung'] ?? [] as $luat)
          <li>{{ $luat }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Điểm thành phần --}}
  <div class="card">
    <h2>Điểm từng mặt</h2>
    @php
      $ten = [
        'color' => 'Màu sắc', 'surface' => 'Bề mặt', 'shape' => 'Hình dạng',
        'uniformity' => 'Đồng đều', 'ripeness' => 'Độ chín',
      ];
    @endphp
    @foreach ($kq['scores'] ?? [] as $k => $v)
      @php
        $mau = $v >= 8 ? 'var(--green)' : ($v >= 6 ? 'var(--yellow)' : ($v >= 4 ? 'var(--orange)' : 'var(--red)'));
      @endphp
      <div class="bar-row">
        <div class="ten">{{ $ten[$k] ?? $k }}</div>
        <div class="track">
          <div class="fill" style="width: {{ min(100, $v * 10) }}%; background: {{ $mau }}"></div>
        </div>
        <div class="num">{{ number_format($v, 1) }}</div>
      </div>
    @endforeach
  </div>

  {{-- Khuyết tật --}}
  <div class="card">
    <h2>Khuyết tật phát hiện được</h2>
    @forelse ($kq['defects'] ?? [] as $d)
      <div class="defect">
        <div class="pct">{{ (int) $d['area_percent'] }}%</div>
        <div>
          <div class="d-ten">
            {{ str_replace('_', ' ', $d['type']) }}
            <span class="sev sev-{{ $d['severity'] }}">{{ str_replace('_', ' ', $d['severity']) }}</span>
          </div>
          <div class="d-note">{{ $d['note'] }}</div>
        </div>
      </div>
    @empty
      <p style="color:var(--muted); font-weight:600">Không phát hiện khuyết tật đáng kể.</p>
    @endforelse
  </div>

  {{-- Lý do --}}
  <div class="card">
    <h2>Vì sao chấm hạng này?</h2>
    <div class="box-ly-do">{{ $kq['reasoning'] }}</div>
  </div>

  {{-- Lời khuyên --}}
  <div class="card">
    <h2>💡 Lời khuyên bán hàng</h2>
    <div class="box-advice">{{ $kq['advice'] }}</div>
  </div>

  <a href="{{ url('/danh-gia') }}" class="back">← Chấm quả khác</a>

  @endif
@endsection
