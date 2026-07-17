<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'AI đánh giá chất lượng') — AgriSenAi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --green: #58CC02;
      --green-dark: #46A302;
      --green-light: #E8F8D4;
      --green-pale: #F0FBDF;
      --yellow: #FFC800;
      --yellow-light: #FFF4CC;
      --orange: #FF9600;
      --orange-light: #FFEBD1;
      --red: #FF4B4B;
      --red-light: #FFE5E5;
      --ink: #3C3C3C;
      --muted: #777;
      --line: #E5E5E5;
      --bg: #FAFAFA;
    }

    body {
      font-family: 'Nunito', system-ui, sans-serif;
      background: var(--bg);
      color: var(--ink);
      line-height: 1.6;
    }

    .wrap { max-width: 860px; margin: 0 auto; padding: 24px 16px 64px; }

    header.top {
      background: #fff;
      border-bottom: 2px solid var(--line);
      padding: 14px 16px;
    }
    header.top .inner {
      max-width: 860px; margin: 0 auto;
      display: flex; align-items: center; gap: 10px;
    }
    header.top a.logo {
      font-weight: 900; font-size: 20px; color: var(--green-dark);
      text-decoration: none; letter-spacing: -.4px;
    }
    header.top .tag {
      font-size: 12px; font-weight: 700; color: var(--muted);
      border-left: 2px solid var(--line); padding-left: 10px;
    }

    h1 { font-size: 26px; font-weight: 900; letter-spacing: -.5px; margin-bottom: 6px; }
    .sub { color: var(--muted); font-size: 15px; margin-bottom: 24px; }

    .card {
      background: #fff; border: 2px solid var(--line);
      border-radius: 16px; padding: 22px; margin-bottom: 18px;
    }

    label.lbl { display: block; font-weight: 800; font-size: 14px; margin-bottom: 8px; }
    .hint { font-size: 13px; color: var(--muted); font-weight: 400; margin-top: 6px; }

    select, input[type=file] {
      width: 100%; padding: 12px 14px;
      border: 2px solid var(--line); border-radius: 12px;
      font-family: inherit; font-size: 15px; font-weight: 600;
      background: #fff; color: var(--ink);
    }
    select:focus, input:focus { outline: none; border-color: var(--green); }

    .field { margin-bottom: 20px; }

    button.go {
      width: 100%; padding: 15px;
      background: var(--green); color: #fff;
      border: none; border-bottom: 4px solid var(--green-dark);
      border-radius: 14px; cursor: pointer;
      font-family: inherit; font-weight: 900; font-size: 16px;
      text-transform: uppercase; letter-spacing: .5px;
    }
    button.go:hover { background: var(--green-dark); }
    button.go:active { border-bottom-width: 2px; transform: translateY(2px); }
    button.go:disabled { background: #ccc; border-color: #aaa; cursor: wait; }

    .alert {
      background: var(--red-light); border: 2px solid var(--red);
      color: #B32020; border-radius: 12px; padding: 14px;
      font-weight: 700; font-size: 14px; margin-bottom: 18px;
    }
    .alert ul { margin: 6px 0 0 18px; font-weight: 600; }

    a.back {
      display: inline-block; margin-top: 8px;
      color: var(--green-dark); font-weight: 800; text-decoration: none; font-size: 15px;
    }
    a.back:hover { text-decoration: underline; }

    .note {
      background: var(--yellow-light); border: 2px solid var(--yellow);
      border-radius: 12px; padding: 14px; font-size: 13px; font-weight: 600;
    }

    @yield('css')
  </style>
</head>

<body>
  <header class="top">
    <div class="inner">
      <a href="/" class="logo">AgriSenAi</a>
      <span class="tag">AI đánh giá chất lượng nông sản</span>
    </div>
  </header>

  <div class="wrap">
    @yield('body')
  </div>
</body>

</html>
