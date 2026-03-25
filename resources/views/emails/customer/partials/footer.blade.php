{{-- Path: resources/views/emails/customers/partials/footer.blade.php --}}

{{--
    ============================================================
    FOOTER PARTIAL
    ============================================================
    All store info is pulled automatically from store_settings().

    Optional overrides per email:

    $footerSupportText  (string)  Label above the support link.
                                  Default: "Need help? We're here for you!"
    $footerSupportColor (string)  Hex color for the support link.
                                  Default: '#5B914C'  (use '#dc3545' for red emails etc.)
    ============================================================
--}}

@php
    $footerSupportText  = $footerSupportText  ?? "Need help? We're here for you!";
    $footerSupportColor = $footerSupportColor ?? '#5B914C';

    // Pull store data via helper
    $storeName     = store_settings('store_name')    ?: config('app.name');
    $storeTagline  = store_settings('store_tagline') ?: null;
    $storeEmail    = store_settings('store_email')   ?: config('mail.from.address');
    $storePhone    = store_settings('store_phone')   ?: null;
    $storeAddress  = store_settings('store_address') ?: null;
    $storeCity     = store_settings('store_city')    ?: null;
    $storeCountry  = store_settings('store_country') ?: null;

    // Build a one-line location string if any address info exists
    $locationParts = array_filter([$storeCity, $storeCountry]);
    $storeLocation = !empty($locationParts) ? implode(', ', $locationParts) : null;
@endphp

{{-- Help / Support section (inside body, above the grey footer band) --}}
<tr>
    <td style="padding: 0 30px 30px 30px;">

        {{-- Divider --}}
        <div style="height: 1px; background-color: #e9ecef; margin-bottom: 30px;"></div>

        {{-- Support link --}}
        <div style="text-align: center;">
            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                {{ $footerSupportText }}
            </p>
            <p style="margin: 0;">
                <a href="mailto:{{ $storeEmail }}"
                   style="color: {{ $footerSupportColor }}; text-decoration: none; font-weight: 600;">
                    Contact Support
                </a>
            </p>
        </div>

    </td>
</tr>

{{-- Grey footer band --}}
<tr>
    <td style="background-color: #f8f9fa; padding: 30px 20px; text-align: center; border-top: 1px solid #e9ecef;">

        {{-- Store Name --}}
        <p style="margin: 0 0 6px 0; font-weight: 600; color: #333333; font-size: 16px;">
            {{ $storeName }}
        </p>

        {{-- Tagline (only if set in store settings) --}}
        @if($storeTagline)
        <p style="margin: 0 0 8px 0; color: #666666; font-size: 14px;">
            {{ $storeTagline }}
        </p>
        @endif

        {{-- Phone (only if set) --}}
        @if($storePhone)
        <p style="margin: 0 0 4px 0; color: #888888; font-size: 13px;">
            📞 {{ $storePhone }}
        </p>
        @endif

        {{-- Location (only if city or country set) --}}
        @if($storeLocation)
        <p style="margin: 0 0 15px 0; color: #888888; font-size: 13px;">
            📍 {{ $storeLocation }}
        </p>
        @endif

        {{-- Divider --}}
        <div style="height: 1px; background-color: #e9ecef; margin: 0 0 15px 0;"></div>

        {{-- Copyright --}}
        <p style="margin: 0; font-size: 12px; color: #999999;">
            &copy; {{ date('Y') }} {{ $storeName }}. All rights reserved.
        </p>

    </td>
</tr>
