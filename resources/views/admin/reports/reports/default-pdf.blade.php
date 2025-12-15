<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Export</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            padding: 20px;
        }
        .header {
            background-color: #5B914C;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px;
        }
        .error {
            color: #dc3545;
            padding: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Report Export</h1>
        <p>Generated: {{ now()->format('M d, Y H:i:s') }}</p>
    </div>
    <div class="content">
        <div class="error">
            <h3>Template Not Found</h3>
            <p>The specific PDF template for this report type is not available.</p>
            <p>Please contact support for assistance.</p>
        </div>

        @if(isset($data))
        <h3>Available Data:</h3>
        <pre>{{ print_r(array_keys($data), true) }}</pre>
        @endif
    </div>
</body>
</html>
