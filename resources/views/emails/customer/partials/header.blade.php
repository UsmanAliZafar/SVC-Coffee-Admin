{{-- Path: resources/views/emails/customers/partials/header.blade.php --}}

{{--
    ============================================================
    EMAIL HEADER PARTIAL
    ============================================================
    Pass these variables from each email view:

    $headerIcon      (string)  Emoji icon shown at top.         Default: '✉️'
    $headerTitle     (string)  Big bold heading.                Default: app name
    $headerSubtitle  (string)  Smaller line below heading.      Default: ''  (hidden if empty)
    $headerStyle     (string)  One of the preset colour keys:
                               'green'  → #5B914C  (brand default — orders, shipping, payments)
                               'red'    → #dc3545  (errors, cancellations, payment failed)
                               'blue'   → #007bff  (processing, informational)
                               'teal'   → #17a2b8  (refunds, neutral positive)
                               'gold'   → #ffc107  (warnings, pending)
                               'dark'   → #343a40  (generic / neutral)
                               Default: 'green'
    ============================================================
--}}

@php
    $headerIcon     = $headerIcon     ?? '✉️';
    $headerTitle    = $headerTitle    ?? config('app.name');
    $headerSubtitle = $headerSubtitle ?? '';

    // Colour map — gradient start / end for each style
    $headerColors = [
        'green' => ['#5B914C', '#4a7a3d'],
        'red'   => ['#dc3545', '#c82333'],
        'blue'  => ['#007bff', '#0056b3'],
        'teal'  => ['#17a2b8', '#138496'],
        'gold'  => ['#e6a817', '#c98d00'],
        'dark'  => ['#343a40', '#1d2124'],
    ];

    $style   = $headerStyle ?? 'green';
    $colors  = $headerColors[$style] ?? $headerColors['green'];
    $gradCSS = "background: linear-gradient(135deg, {$colors[0]} 0%, {$colors[1]} 100%);";
@endphp

<tr>
    <td style="{{ $gradCSS }} color: #ffffff; padding: 40px 20px; text-align: center; border-radius: 8px 8px 0 0;">

        {{-- Icon --}}
        <div style="font-size: 50px; margin-bottom: 10px; line-height: 1;">
            {{ $headerIcon }}
        </div>

        {{-- Title --}}
        <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #ffffff; line-height: 1.3;">
            {{ $headerTitle }}
        </h1>

        {{-- Optional subtitle --}}
        @if(!empty($headerSubtitle))
        <p style="margin: 10px 0 0 0; font-size: 16px; color: #ffffff; opacity: 0.95;">
            {{ $headerSubtitle }}
        </p>
        @endif

    </td>
</tr>
