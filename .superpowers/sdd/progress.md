# Tien do: layout app.blade.php

Plan: docs/superpowers/plans/2026-07-17-layout-app-blade.md
Branch: feature/layout-chrome (tach tu main sau khi Task 1 da merge)

Task 1: complete (commit 8950cf8, da merge vao main qua 52b9ee9)
  - Bang mau doi sang tong demo #2e7d32; 14/14 ma khop; 0 ma cu sot; font Nunito OK
  - Ngoai pham vi: xoa ~290 dong @layer components chet trong app.css
    (class .hero/.btn-3d/.pcard... - home.blade.php tu dinh nghia inline, khong goi @vite)
    Lay lai neu can: git show d00b9d5:resources/css/app.css

Task 2: complete (commits cd90122..f788238, review clean sau 1 vong fix)
  - config/navigation.php + NavigationConfigTest (7/7 pass)
  - Fix: drawer_links -> drawer_groups (2 nhom, 8 link) theo home.html;
    nhan "AI danh gia chat luong" / "Khuyen nghi gia ban"; them account+settings; bo notif
  - Ke hoach Task 3 & 8 da vá theo (icon settings, render divider)

Task 3: complete (commits d28ab85..bc50c68, review clean sau 1 vong fix)
  - x-icon 20 icon, 8/8 test pass; path doi chieu tung ky tu voi chrome.js
  - Fix: tach scan khoi ai (scan net 2 theo I.scan); zalo dung prop strokeWidth

Task 4: complete (commit f70bb2e, review clean vong dau)
  - Role model + User::isFarmer()/accountHref(); 4/4 test, khong dung DB

Task 5: complete (commit df28da8, review clean vong dau)
  - layouts/app.blade.php + 6 component chrome rong; 3/3 test
  - Reviewer da doi chieu tung breakpoint cua <main> va <body> voi demo: khop

Task 6: complete (commit bd6cd4e, review clean vong dau)
  - bottom-nav doi bo theo $page; 7/7 test; reviewer doi chieu tung class voi demo: khop
  - MINOR ton dong (cho review cuoi triage):
    * .bnav thieu cursor:pointer va -webkit-tap-highlight-color:transparent
      (reviewer ghi nhan: khong component nao trong du an tai hien tap-highlight,
       co ve la quy uoc chung -> can quyet dinh mot the)

Task 7: complete (commit cea089b, review clean vong dau)
  - top-nav desktop, nhanh guest+auth; 4/4 test; full suite 35/35
  - Reviewer doi chieu tung class voi demo (dong 84-244 CSS): khop pixel

Task 8: complete (commits 3f55e31..d03e399, review clean sau 1 vong fix)
  - mob-header + drawer; 9/9 test
  - Fix: mob-header khop demo (1 nut chuong, brand lien AgriSenAi, sub "Nong san Dong Thap", logo div)
  - MINOR ton dong (review cuoi): nut icon dung <a href="#"> thay vi <button>
    (mau chung ca top-nav/mob-header cho cac nut chua co dich)

Task 9: complete (commits f4a7e77..375dd4b, review clean sau 1 vong fix)
  - footer 4 cot chi hien >=1024px; 4/4 test
  - Fix: bo xl:max-w-[1320px] xl:px-12 (demo khong mo rong footer o 1280px)

Task 10: complete (commit bb5f5c0, review clean vong dau)
  - vo chat FAB+panel, chatBody rong; 2/2 test
  - Reviewer canh: Tailwind v4 dung property `translate:` cho translate-x-full
    -> da sua ke hoach Task 11: rule .open dung `translate:0 0` khong phai transform
  - MINOR ton dong (review cuoi): FAB thieu transition box-shadow; input thieu autocomplete=off

Task 11: complete (commits 9068f52/ef202e8, review clean vong dau)
  - JS dong/mo drawer+chat; CSS rule .open dung translate:0 (da verify build)
  - Step 4 (kiem trinh duyet) CHUA lam - de dieu phoi/user xac nhan o Task 12
  - MINOR ton dong (review cuoi): scroll-lock body.overflow dung chung, khong ref-count
    (edge case ban phim khi ca 2 panel mo cung luc)

Task 12: complete (commit 13bf1d7, review approved)
  - /layout-demo + route + test; full suite 53/53 pass
  - PENDING: kiem trinh duyet (Step 7) - dieu phoi lam
  - Ghi nhan: #58CC02 con o home.blade.php + quality/layout.blade.php (file rieng, khong dung layouts.app - ngoai pham vi)

=== TAT CA 12 TASK DA XONG - con: kiem trinh duyet + review toan nhanh ===

=== KIEM TRINH DUYET (Step 7 Task 12) - DIEU PHOI DA LAM - PASS ===
- Chup Chrome headless + CDP o 390px va 1280px, ca ?page=home lan ?page=ai
- Desktop 1280: top-nav (logo sen, 5 link, Trang chu active xanh #2e7d32, search, guest Dang nhap/Dang ky), footer, chat FAB goc phai duoi. OK
- Mobile 390: mob-header (hamburger, brand "AgriSenAi" lien, sub "Nong san Dong Thap", khong nut icon vi guest), bottom-nav 5 muc + AI Scan tron xanh noi giua, chat FAB tren bottom-nav, KHONG footer. OK
- Do CDP: overflow=false (sw 485<=vw 500), khong tran ngang
- ?page=ai: bottom-nav doi bo dung (Gio hang -> Khuyen nghi), nut AI Scan sang. XAC NHAN TRUC QUAN OK
- Mau #2e7d32 dung (khong phai #58CC02), font Nunito. Screenshot o scratchpad.

=== REVIEW TOAN NHANH (opus) - XONG ===
- Khong Critical. Da fix: aria-label 3 nav + aria-hidden drawer (commit 54e9084, 55/55 test)
- Triage 3 minor cu: (a) <a href="#"> icon = trung demo, khong sua; (b) tap-highlight cosmetic; (c) scroll-lock ly thuyet (2 panel loai tru nhau qua z-index) -> khong sua
- IMPORTANT ton dong - QUYET DINH CUA USER:
  * Tai lieu du thi (.docx/.pdf) lot vao 2 commit 750677e & c232afa
    do toi chay `git add docs/` (ca thu muc) khi commit file plan.
    Noi dung con nguyen, chi la lich su nhanh khong sach.
- Minor follow-up: /layout-demo nen guard moi truong truoc khi deploy that;
  isFarmer()/accountHref() nhi phan chua cover admin (khi gan auth); app.css trung scroll-behavior

========================================================================
# Tien do: MARKETPLACE (san thi truong)
Plan: docs/superpowers/plans/2026-07-21-marketplace.md
Spec: docs/superpowers/specs/2026-07-21-marketplace.md
Branch: feature/marketplace (tach tu main @ 804f07d sau khi commit spec+plan)
Base truoc Task 1: 804f07d
PHP dev: E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe (khong trong PATH)
Test: sqlite :memory:, RefreshDatabase. Suite goc 55/55 truoc khi bat dau.

Task 1: complete (commits 804f07d..d6bc83e, review clean - Spec OK, Approved)
  MINOR (review cuoi): User.php import HasMany thua (dung FQCN, plan-mandated)

Task 2: complete (commits d6bc83e..107f70b, review clean - Spec OK, Approved)
  Seed dev DB: 10 certified. MINOR: import thua o DatabaseSeeder + test (plan-mandated)
  Note: subagent phai bat MySQL Laragon (chua chay)

Task 3: complete (commits 107f70b..a35b51d, review clean - Spec OK, Approved)
  MINOR: category eager-load chua render; CSS token trung (convention AI page); nav align cosmetic
  Note: demo thi-truong.html la bang gia, khong phai catalog -> card/filter dung theo he thiet ke

Task 4: complete (commits a35b51d..afa8935, review clean - Spec OK, Approved)
  Test-only, controller byte-identical. MINOR: if($other) guard co the giam coverage am tham; thieu assertOk truoc getContent

Task 5: complete (commits afa8935..e8c23fa, review clean - Spec OK, Approved)
  MINOR: VN label map inline trong view; index card van dung url() thay vi route(products.show)

Task 6: complete (commits e8c23fa..2191fcc, review clean - Spec OK, Approved)
  MINOR: input step=1 vs qty float; khong gioi han tren qty

Task 7: complete (commits 2191fcc..4e1cede, review clean - Spec OK, Approved)
  MINOR: undefined-key warning neu product bi xoa (vo hai, log noise); khong retry order_code collision
  Out-of-brief: sua 1 dong cart.blade url()->route(checkout.form) (finalization hop ly)

=== TAT CA 7 TASK XONG - suite 69/69 - chuan bi review toan nhanh ===

=== REVIEW TOAN NHANH (opus) - Ready to merge WITH FIXES ===
Khong Critical. 
IMPORTANT: buyer_id fallback (CheckoutController) -> null neu chua seed -> 500. Plan-mandated demo, nhung nen guard.
FIX (1 subagent): buyer_id guard; index card url()->route(products.show); xoa import chet (User/DatabaseSeeder/MarketplaceSeederTest); filter test them assertNotNull + assertOk.
DEFER (follow-up, ngoai pham vi mockup): route naming marketplace.index vs products.show; validate ton kho; retry order_code collision; ownership order-confirm; float qty UI.

FIX review cuoi: complete (commit 0811e21) - buyer_id guard + route() card + don import + siet filter test. Suite 69/69.
=== MARKETPLACE HOAN TAT - 7 task + fix review cuoi - nhanh feature/marketplace san sang merge ===

========================================================================
# Tien do: QR TRUY XUAT (Digital Product Passport)
Plan: docs/superpowers/plans/2026-07-21-qr-traceability.md
Spec: docs/superpowers/specs/2026-07-21-qr-traceability.md
Branch: feature/qr-traceability (tach tu main sau khi commit plan)
LUU Y: da `git stash push routes/web.php` (PWA offline route WIP cua user) -> POP LAI sau khi merge.
Base truoc Task 1: 58068a3
PHP: E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe

Task 1: complete (58068a3..cdbd440, review clean - Spec OK, Approved). Suite 70/70.
  MINOR: test goi fresh() 2 lan; fixture verbose

Task 2: complete (cdbd440..f7c109a, review clean - Spec OK, Approved). Suite 73/73.
  MINOR: composer "*" nen ^4.2; simple-qrcode require ext-gd (kiem tra cho Task 3)

Task 3: complete (f7c109a..9e71d4e, review clean - Spec OK, Approved). Suite 76/76.
  MINOR: route() dung positional param; khong co test chan write route

Task 4: complete (9e71d4e..a426fb8, review clean - Spec OK, Approved). Suite 77/77.
  MINOR: inline FQ Storage facade; chua co test cho banner verified/mismatch; app.blade doc-comment thieu truyxuat

Task 5: complete (a426fb8..e3c535c, review clean - Spec OK, Approved). Suite 78/78. Demo verified->mismatch chay that.
  MINOR: fallback "anh-demo" neu thieu file ref; khong assertExists truc tiep

Task 6: complete (e3c535c..29c96dc, review clean - Spec OK, Approved). Suite 79/79.
  MINOR: qr-title trung tieu de card (cosmetic)

=== TAT CA 6 TASK QR XONG - suite 79/79 - chuan bi review toan nhanh ===

=== REVIEW TOAN NHANH QR (opus) - Ready to merge WITH FIXES ===
Khong Critical. Test that (10 test moi) verified/mismatch chay dung.
IMPORTANT (deu la thiet ke spec -> user quyet):
 #1 verify() coi anh THIEU FILE = mismatch -> neu quen storage:link thi moi passport bao do oan. (plan bat buoc storage:link nen demo OK; that ra la strict AND theo spec)
 #2 "verified" khong chong sua that su (SHA-256 input cong khai, luu cung hang, thuat toan trong repo -> ai co quyen ghi DB deu tinh lai duoc). Copy chan trang noi qua. Spec design cho demo.
 #3 hash BO QUA defect_note + raw_ai_response (dang hien defect_note) -> sua defect_note khong lat bang. Dung thu tu canonical spec.
MINOR: composer "*" -> ^4.2; ext-gd deploy note; computeHash extra query; khong test chan write route; inline FQ Storage; app.blade doc thieu truyxuat; seeder fallback 'anh-demo'.

Pin composer ^4.2 (e5ab3b4). MERGED vao main (FF @ e5ab3b4). Stash PWA routes/web.php da pop (resolve conflict import: giu TraceController, offline route giu uncommitted). Nhanh feature/qr-traceability xoa.
=== QR TRUY XUAT HOAN TAT - 6 task + review cuoi + pin composer - suite 79/79 ===
