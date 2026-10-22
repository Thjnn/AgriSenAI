# Hướng dẫn tạo link tạm thời để người khác xem web

Dùng Cloudflare Tunnel (`cloudflared`) để tạo một link `https://....trycloudflare.com` trỏ về web đang chạy trên máy mình. Không cần tài khoản, không cần deploy.

## Cách nhanh: chạy `chia-se-link.bat`

Bấm đúp file `chia-se-link.bat` ở thư mục gốc dự án. File tự làm các bước bên dưới: tìm `cloudflared`, kiểm tra Laragon, xoá `public/hot`, `npm run build`, `php artisan config:cache`, rồi mở tunnel và in link. Đóng cửa sổ là tắt link.

Các bước tay dưới đây chỉ cần khi file `.bat` báo lỗi.

## Điều kiện

- Laragon đang bật (Apache chạy cổng 80), vào được `http://agrisenai.test` trên máy mình.
- Máy đã có `cloudflared`. Kiểm tra:

  ```powershell
  cloudflared --version
  ```

  Nếu báo "not recognized" thì cài rồi mở lại terminal:

  ```powershell
  winget install Cloudflare.cloudflared
  ```

## Các bước

### 1. Tắt Vite dev và build giao diện

Nếu đang chạy `npm run dev` thì bấm `Ctrl+C` ở cửa sổ đó. Sau đó build:

```powershell
npm run build
```

Lý do: khi `npm run dev` chạy, Laravel tạo file `public/hot` và lấy CSS/JS từ `http://localhost:5173`. Người ngoài không vào được địa chỉ đó nên trang sẽ mất hết giao diện.

Kiểm tra lại: trong thư mục `public/` **không còn** file `hot`. Nếu còn thì xóa tay:

```powershell
Remove-Item public\hot
```

### 2. Mở tunnel

```powershell
cloudflared tunnel --url http://agrisenai.test --http-host-header agrisenai.test
```

Chờ vài giây, terminal in ra một dòng dạng:

```
https://ten-ngau-nhien.trycloudflare.com
```

Gửi link đó cho người cần xem.

### 3. Tắt khi xong

Bấm `Ctrl+C` trong cửa sổ đang chạy `cloudflared`. Link chết ngay.

## Lưu ý

- Mỗi lần mở tunnel sẽ ra một link **mới**, link cũ không dùng lại được.
- Phải giữ máy, Laragon và cửa sổ `cloudflared` bật suốt thời gian người khác xem.
- Ai có link cũng vào được, nên chỉ mở khi cần và đừng để dữ liệu thật trong lúc chia sẻ.
- Muốn code tiếp sau khi chia sẻ xong thì chạy lại `npm run dev` như bình thường.

## Lỗi thường gặp

| Hiện tượng | Nguyên nhân | Cách sửa |
|---|---|---|
| Trang hiện chữ trần, không có CSS/JS | Còn file `public/hot` hoặc chưa build | Tắt `npm run dev`, xóa `public/hot`, chạy `npm run build` |
| Link mở ra trang mặc định của Laragon | Thiếu `--http-host-header` | Chạy lại lệnh ở bước 2 đủ tham số |
| Form/đăng nhập lỗi, link trong trang là `http://` thay vì `https://` | Laravel chưa tin proxy | Kiểm tra `bootstrap/app.php` có dòng `$middleware->trustProxies(at: '*');` |
| `cloudflared` báo "not recognized" | Chưa cài hoặc chưa mở lại terminal | Cài bằng `winget` như ở phần Điều kiện |
| Link báo lỗi 502 / Bad Gateway | Laragon (Apache) chưa chạy | Bật Laragon, thử vào `http://agrisenai.test` trước |

## Ghi chú về `trustProxies`

Dòng `$middleware->trustProxies(at: '*');` trong `bootstrap/app.php` cần có để Laravel sinh URL `https` đúng khi đi qua tunnel. Tin mọi proxy (`*`) chỉ phù hợp cho lúc demo trên máy cá nhân; khi deploy thật nên giới hạn lại theo IP proxy của máy chủ.
