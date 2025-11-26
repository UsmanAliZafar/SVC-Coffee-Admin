<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', store_name() . ' - Admin Panel')</title>
    <link rel="icon" type="image/png" href="{{ store_favicon() }}">
    <link rel="shortcut icon" type="image/png" href="{{ store_favicon() }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @include('admin.layouts.partials.styles')
</head>
<body class="bg-light">
    @include('admin.layouts.partials.navbar')

    <div class="admin-wrapper">
        @include('admin.layouts.partials.sidebar')
        <!-- Main content -->
        <main class="main-content" id="main-content">
            <div class="content-wrapper">
                @include('admin.layouts.partials.flash-messages')
                @yield('content')
            </div>
        </main>

    </div>
    @include('admin.layouts.partials.scripts')
    @include('admin.layouts.partials.footer')
    {{-- Global JavaScript variables --}}
    <script>
        // Make store settings available globally
        window.storeSettings = {
            name: "{{ store_name() }}",
            email: "{{ store_email() }}",
            phone: "{{ store_phone() }}",
            currency: "{{ store_currency() }}",
            currencySymbol: "{{ store_currency_symbol() }}",
            timezone: "{{ store_timezone() }}",
            dateFormat: "{{ store_date_format() }}",
            timeFormat: "{{ store_time_format() }}"
        };
    </script>
</body>
</html>
