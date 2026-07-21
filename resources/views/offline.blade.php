<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không có kết nối — AgriSenAi</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f7f8f6;
            color: #1a2e1a;
            padding: 24px;
            text-align: center
        }

        .box {
            max-width: 340px
        }

        h1 {
            font-size: 20px;
            margin: 0 0 8px
        }

        p {
            font-size: 15px;
            line-height: 1.6;
            color: #5a6b5a;
            margin: 0 0 20px
        }

        button {
            background: #2E7D32;
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600
        }
    </style>
</head>

<body>
    <div class="box">
        <div style="font-size:48px">🌾</div>
        <h1>Mất kết nối mạng</h1>
        <p>Không tải được nội dung mới. Kiểm tra lại kết nối rồi thử lại.</p>
        <button onclick="location.reload()">Thử lại</button>
    </div>
</body>

</html>