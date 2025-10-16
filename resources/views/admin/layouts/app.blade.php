<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coffee Admin Panel')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @include('admin.layouts.partials.styles')
    @stack('styles')
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
    {{-- SPA Implementation Script --}}
    {{-- <script>
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script> --}}

    {{-- Include the SPA script (from artifact spa_implementation) --}}
    {{-- <script src="{{ asset('admin/js/spa.js') }}"></script> --}}
    @stack('scripts')
</body>
</html>
