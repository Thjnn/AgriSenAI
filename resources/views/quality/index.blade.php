@extends('quality.layout')

@section('title', 'Chấm chất lượng nông sản')

@section('css')
  .drop {
    border: 3px dashed var(--line); border-radius: 16px;
    padding: 28px; text-align: center; cursor: pointer;
    transition: border-color .15s, background .15s;
  }
  .drop:hover { border-color: var(--green); background: var(--green-pale); }
  .drop input { display: none; }
  .drop .icon { font-size: 40px; }
  .drop .txt { font-weight: 800; margin-top: 6px; }
  .drop .sm { font-size: 13px; color: var(--muted); font-weight: 600; }
  #xemtruoc { display: none; margin-top: 16px; }
  #xemtruoc img {
    width: 100%; max-width: 280px; border-radius: 12px;
    border: 2px solid var(--line); display: block; margin: 0 auto;
  }
@endsection

@section('body')
  <h1>Chấm chất lượng nông sản bằng ảnh</h1>
  <p class="sub">
    Chụp một quả, tải ảnh lên. AI chấm hạng theo TCVN 9766:2013 và giải thích lý do.
  </p>

  @if (session('loi'))
    <div class="alert">{{ session('loi') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert">
      Ảnh chưa hợp lệ:
      <ul>
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (empty($loaiCoSan))
    <div class="alert">
      Chưa có bộ ảnh mẫu nào. Chạy <code>php artisan reference:build xoai</code> trước.
    </div>
  @else
    <form action="{{ url('/danh-gia') }}" method="POST" enctype="multipart/form-data" id="form">
      @csrf

      <div class="card">
        <div class="field">
          <label class="lbl" for="anh">Ảnh nông sản</label>
          <label class="drop" for="anh">
            <div class="icon">📷</div>
            <div class="txt">Bấm để chọn ảnh</div>
            <div class="sm">JPG, PNG hoặc WEBP — tối đa 8 MB</div>
            <input type="file" name="anh" id="anh" accept="image/jpeg,image/png,image/webp" required>
          </label>
          <div class="hint">
            Chụp <strong>một quả</strong>, đặt quả <strong>ngay giữa khung hình</strong> và
            chiếm phần lớn khung. Ảnh sẽ được cắt vuông ở giữa — quả nằm lệch ra mép
            sẽ bị cắt mất. Dùng ánh sáng tự nhiên, không bật đèn flash.
          </div>
          <div id="xemtruoc"><img id="anhtruoc" alt="Ảnh đã chọn"></div>
        </div>

        <div class="field">
          <label class="lbl" for="loai">Loại nông sản</label>
          <select name="loai" id="loai" required>
            @foreach ($loaiCoSan as $l)
              <option value="{{ $l }}" @selected(old('loai') === $l)>{{ ucfirst($l) }}</option>
            @endforeach
          </select>
        </div>

        <div class="field">
          <label class="lbl" for="muc_dich_ban">Bán để làm gì?</label>
          <select name="muc_dich_ban" id="muc_dich_ban" required>
            @foreach ($mucDich as $key => $mota)
              <option value="{{ $key }}" @selected(old('muc_dich_ban') === $key)>{{ $mota }}</option>
            @endforeach
          </select>
          <div class="hint">
            Quan trọng: xoài xanh bán để ăn sống thì <strong>vỏ xanh là đúng</strong>,
            AI sẽ không trừ điểm vì "chưa chín".
          </div>
        </div>

        <button type="submit" class="go" id="nut">Chấm chất lượng</button>
      </div>
    </form>

    <div class="note">
      <strong>Lưu ý:</strong> AI chỉ chấm những gì <strong>nhìn thấy được</strong> qua ảnh —
      màu sắc, bề mặt, khuyết tật, hình dạng. Khối lượng và độ ngọt (Brix) do bạn tự nhập
      khi đăng bán, AI không đoán.
    </div>
  @endif

  <script>
    const input = document.getElementById('anh');
    const box = document.getElementById('xemtruoc');
    const img = document.getElementById('anhtruoc');
    const form = document.getElementById('form');
    const nut = document.getElementById('nut');

    input?.addEventListener('change', () => {
      const f = input.files[0];
      if (!f) return;
      img.src = URL.createObjectURL(f);
      box.style.display = 'block';
    });

    form?.addEventListener('submit', () => {
      nut.disabled = true;
      nut.textContent = 'Đang chấm… (khoảng 10 giây)';
    });
  </script>
@endsection
