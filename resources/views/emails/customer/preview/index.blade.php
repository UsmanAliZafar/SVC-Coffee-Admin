{{-- Path: resources/views/emails/customer/preview/index.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template Previews</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 500px) {
            .grid { grid-template-columns: 1fr; }
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            border-left: 4px solid #5B914C;
            transition: box-shadow 0.15s, transform 0.15s;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }

        .card-label {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        .card-key {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }

        .arrow {
            font-size: 18px;
            color: #5B914C;
        }

        .badge {
            display: inline-block;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
            background: #eee;
            padding: 4px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container">

        <h1>📧 Email Template Previews</h1>
        <p class="subtitle">Click any card to preview the email in your browser with dummy data.</p>

        <div class="grid">
            @foreach($emails as $key => $meta)
            <a href="{{ route('admin.email-preview.show', $key) }}" class="card" target="_blank">
                <div>
                    <div class="card-label">{{ $meta['label'] }}</div>
                    <div class="card-key">{{ $key }}</div>
                </div>
                <div class="arrow">→</div>
            </a>
            @endforeach
        </div>

        <span class="badge">{{ count($emails) }} templates</span>

    </div>
</body>
</html>
