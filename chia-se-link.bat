@echo off
rem Tao link https tam thoi (Cloudflare Tunnel) tro ve http://agrisenai.test
rem Chi tiet: docs\files-ke-hoach\huong-dan-tao-link-tam-thoi.md
chcp 65001 >nul
cd /d "%~dp0"

set "PHP=E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

rem 1. Tim cloudflared: PATH -> ban di kem Laragon
set "CF="
for /f "delims=" %%i in ('where cloudflared 2^>nul') do if not defined CF set "CF=%%i"
if not defined CF if exist "E:\laragon\bin\cloudflared\cloudflared.exe" set "CF=E:\laragon\bin\cloudflared\cloudflared.exe"
if not defined CF (
    echo [LOI] Khong tim thay cloudflared. Cai bang: winget install Cloudflare.cloudflared
    pause & exit /b 1
)

rem 2. Laragon phai dang chay
curl -s -o nul -m 5 http://agrisenai.test/ || (
    echo [LOI] Khong vao duoc http://agrisenai.test - hay bat Laragon ^(Start All^) roi chay lai.
    pause & exit /b 1
)

rem 3. Bo Vite dev (public\hot) va build giao dien, nguoi ngoai khong vao duoc localhost:5173
if exist public\hot (
    echo Dang tat che do Vite dev: xoa public\hot. Neu dang chay "npm run dev" hay tat cua so do.
    del public\hot
)
echo Dang build giao dien...
call npm run build >nul || (
    echo [LOI] npm run build that bai. Chay tay "npm run build" de xem loi.
    pause & exit /b 1
)

rem 4. Cache config: chan Apache doc nham .env cua du an khac cung may
"%PHP%" artisan config:cache >nul

echo.
echo ============================================================
echo  Link https://....trycloudflare.com se hien ben duoi sau vai giay.
echo  Gui link do cho nguoi can xem. Camera dien thoai chay duoc qua link nay.
echo  Tai khoan demo: 0911111111 / agrisen123  (admin: 0900000000)
echo  Dong cua so nay hoac bam Ctrl+C de TAT link.
echo ============================================================
echo.

"%CF%" tunnel --url http://agrisenai.test --http-host-header agrisenai.test
pause
