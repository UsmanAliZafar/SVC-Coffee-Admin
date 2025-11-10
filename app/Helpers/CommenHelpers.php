<?php

/**
 * Store Settings Helper Functions
 *
 * Comprehensive helper functions for managing store settings
 * including currencies, timezones, date formats, time formats, etc.
 */

if (!function_exists('get_all_currencies')) {
    /**
     * Get all available currencies
     *
     * @return array
     */
    function get_all_currencies(): array
    {
        return [
            // 'USD' => 'US Dollar ($)',
            // 'EUR' => 'Euro (€)',
            // 'GBP' => 'British Pound (£)',
            // 'PKR' => 'Pakistani Rupee (₨)',
            'SAR' => 'Saudi Riyal (﷼)',
            // 'AED' => 'UAE Dirham (د.إ)',
            // 'CAD' => 'Canadian Dollar (C$)',
            // 'AUD' => 'Australian Dollar (A$)',
            // 'JPY' => 'Japanese Yen (¥)',
            // 'CNY' => 'Chinese Yuan (¥)',
            // 'INR' => 'Indian Rupee (₹)',
            // 'CHF' => 'Swiss Franc (Fr)',
            // 'SEK' => 'Swedish Krona (kr)',
            // 'NOK' => 'Norwegian Krone (kr)',
            // 'DKK' => 'Danish Krone (kr)',
            // 'NZD' => 'New Zealand Dollar (NZ$)',
            // 'KWD' => 'Kuwaiti Dinar (د.ك)',
            // 'BHD' => 'Bahraini Dinar (د.ب)',
            // 'OMR' => 'Omani Rial (ر.ع.)',
            // 'QAR' => 'Qatari Riyal (ر.ق)',
            // 'MYR' => 'Malaysian Ringgit (RM)',
            // 'SGD' => 'Singapore Dollar (S$)',
            // 'HKD' => 'Hong Kong Dollar (HK$)',
            // 'THB' => 'Thai Baht (฿)',
            // 'ZAR' => 'South African Rand (R)',
            // 'BRL' => 'Brazilian Real (R$)',
            // 'MXN' => 'Mexican Peso (Mex$)',
            // 'TRY' => 'Turkish Lira (₺)',
            // 'RUB' => 'Russian Ruble (₽)',
            // 'KRW' => 'South Korean Won (₩)',
            // 'IDR' => 'Indonesian Rupiah (Rp)',
            // 'PLN' => 'Polish Zloty (zł)',
            // 'CZK' => 'Czech Koruna (Kč)',
            // 'HUF' => 'Hungarian Forint (Ft)',
            // 'ILS' => 'Israeli Shekel (₪)',
            // 'EGP' => 'Egyptian Pound (E£)',
            // 'PHP' => 'Philippine Peso (₱)',
            // 'VND' => 'Vietnamese Dong (₫)',
            // 'NGN' => 'Nigerian Naira (₦)',
            // 'MAD' => 'Moroccan Dirham (د.م.)',
            // 'JOD' => 'Jordanian Dinar (د.ا)',
            // 'LBP' => 'Lebanese Pound (ل.ل)',
            // 'IQD' => 'Iraqi Dinar (ع.د)',
        ];
    }
}

if (!function_exists('get_currencies')) {
    /**
     * Alias for get_all_currencies
     *
     * @return array
     */
    function get_currencies(): array
    {
        return get_all_currencies();
    }
}

if (!function_exists('get_active_currencies')) {
    /**
     * Get only active/enabled currencies
     * You can customize this based on your business needs
     *
     * @return array
     */
    function get_active_currencies(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'SAR' => 'Saudi Riyal (﷼)',
            'PKR' => 'Pakistani Rupee (₨)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'AED' => 'UAE Dirham (د.إ)',
        ];
    }
}

if (!function_exists('get_currency_symbol')) {
    /**
     * Get currency symbol by currency code
     *
     * @param string $code
     * @return string
     */
    function get_currency_symbol(string $code): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'PKR' => '₨',
            'SAR' => '﷼',
            'AED' => 'د.إ',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'CHF' => 'Fr',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'NZD' => 'NZ$',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'OMR' => 'ر.ع.',
            'QAR' => 'ر.ق',
        ];

        return $symbols[$code] ?? $code;
    }
}

if (!function_exists('get_all_timezones')) {
    /**
     * Get all available timezones grouped by region
     *
     * @return array
     */
    function get_all_timezones(): array
    {
        return [
            // Asia
            'Asia/Karachi' => 'Asia/Karachi (PKT - UTC+5)',
            'Asia/Dubai' => 'Asia/Dubai (GST - UTC+4)',
            'Asia/Riyadh' => 'Asia/Riyadh (AST - UTC+3)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST - UTC+5:30)',
            'Asia/Dhaka' => 'Asia/Dhaka (BST - UTC+6)',
            'Asia/Bangkok' => 'Asia/Bangkok (ICT - UTC+7)',
            'Asia/Singapore' => 'Asia/Singapore (SGT - UTC+8)',
            'Asia/Hong_Kong' => 'Asia/Hong Kong (HKT - UTC+8)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST - UTC+8)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST - UTC+9)',
            'Asia/Seoul' => 'Asia/Seoul (KST - UTC+9)',
            'Asia/Jerusalem' => 'Asia/Jerusalem (IST - UTC+2)',
            'Asia/Tehran' => 'Asia/Tehran (IRST - UTC+3:30)',
            'Asia/Kabul' => 'Asia/Kabul (AFT - UTC+4:30)',
            'Asia/Kathmandu' => 'Asia/Kathmandu (NPT - UTC+5:45)',
            'Asia/Yangon' => 'Asia/Yangon (MMT - UTC+6:30)',
            'Asia/Jakarta' => 'Asia/Jakarta (WIB - UTC+7)',
            'Asia/Manila' => 'Asia/Manila (PHT - UTC+8)',

            // // Europe
            // 'Europe/London' => 'Europe/London (GMT - UTC+0)',
            // 'Europe/Paris' => 'Europe/Paris (CET - UTC+1)',
            // 'Europe/Berlin' => 'Europe/Berlin (CET - UTC+1)',
            // 'Europe/Rome' => 'Europe/Rome (CET - UTC+1)',
            // 'Europe/Madrid' => 'Europe/Madrid (CET - UTC+1)',
            // 'Europe/Amsterdam' => 'Europe/Amsterdam (CET - UTC+1)',
            // 'Europe/Brussels' => 'Europe/Brussels (CET - UTC+1)',
            // 'Europe/Vienna' => 'Europe/Vienna (CET - UTC+1)',
            // 'Europe/Stockholm' => 'Europe/Stockholm (CET - UTC+1)',
            // 'Europe/Oslo' => 'Europe/Oslo (CET - UTC+1)',
            // 'Europe/Copenhagen' => 'Europe/Copenhagen (CET - UTC+1)',
            // 'Europe/Athens' => 'Europe/Athens (EET - UTC+2)',
            // 'Europe/Istanbul' => 'Europe/Istanbul (TRT - UTC+3)',
            // 'Europe/Moscow' => 'Europe/Moscow (MSK - UTC+3)',
            // 'Europe/Zurich' => 'Europe/Zurich (CET - UTC+1)',
            // 'Europe/Warsaw' => 'Europe/Warsaw (CET - UTC+1)',
            // 'Europe/Prague' => 'Europe/Prague (CET - UTC+1)',
            // 'Europe/Budapest' => 'Europe/Budapest (CET - UTC+1)',

            // // America - North
            // 'America/New_York' => 'America/New York (EST - UTC-5)',
            // 'America/Chicago' => 'America/Chicago (CST - UTC-6)',
            // 'America/Denver' => 'America/Denver (MST - UTC-7)',
            // 'America/Los_Angeles' => 'America/Los Angeles (PST - UTC-8)',
            // 'America/Anchorage' => 'America/Anchorage (AKST - UTC-9)',
            // 'America/Toronto' => 'America/Toronto (EST - UTC-5)',
            // 'America/Vancouver' => 'America/Vancouver (PST - UTC-8)',
            // 'America/Mexico_City' => 'America/Mexico City (CST - UTC-6)',

            // // America - South
            // 'America/Sao_Paulo' => 'America/São Paulo (BRT - UTC-3)',
            // 'America/Buenos_Aires' => 'America/Buenos Aires (ART - UTC-3)',
            // 'America/Santiago' => 'America/Santiago (CLT - UTC-4)',
            // 'America/Bogota' => 'America/Bogota (COT - UTC-5)',
            // 'America/Lima' => 'America/Lima (PET - UTC-5)',
            // 'America/Caracas' => 'America/Caracas (VET - UTC-4)',

            // // Africa
            // 'Africa/Cairo' => 'Africa/Cairo (EET - UTC+2)',
            // 'Africa/Johannesburg' => 'Africa/Johannesburg (SAST - UTC+2)',
            // 'Africa/Lagos' => 'Africa/Lagos (WAT - UTC+1)',
            // 'Africa/Nairobi' => 'Africa/Nairobi (EAT - UTC+3)',
            // 'Africa/Casablanca' => 'Africa/Casablanca (WET - UTC+0)',
            // 'Africa/Algiers' => 'Africa/Algiers (CET - UTC+1)',

            // // Pacific & Oceania
            // 'Pacific/Auckland' => 'Pacific/Auckland (NZST - UTC+12)',
            // 'Pacific/Sydney' => 'Australia/Sydney (AEDT - UTC+11)',
            // 'Pacific/Melbourne' => 'Australia/Melbourne (AEDT - UTC+11)',
            // 'Australia/Perth' => 'Australia/Perth (AWST - UTC+8)',
            // 'Pacific/Fiji' => 'Pacific/Fiji (FJT - UTC+12)',
            // 'Pacific/Honolulu' => 'Pacific/Honolulu (HST - UTC-10)',

            // UTC
            'UTC' => 'UTC (Coordinated Universal Time)',
        ];
    }
}

if (!function_exists('get_common_timezones')) {
    /**
     * Get commonly used timezones
     *
     * @return array
     */
    function get_common_timezones(): array
    {
        return [
            'Asia/Karachi' => 'Asia/Karachi (PKT - UTC+5)',
            'Asia/Dubai' => 'Asia/Dubai (GST - UTC+4)',
            'Asia/Riyadh' => 'Asia/Riyadh (AST - UTC+3)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST - UTC+5:30)',
            'Europe/London' => 'Europe/London (GMT - UTC+0)',
            'Europe/Paris' => 'Europe/Paris (CET - UTC+1)',
            'America/New_York' => 'America/New York (EST - UTC-5)',
            'America/Chicago' => 'America/Chicago (CST - UTC-6)',
            'America/Los_Angeles' => 'America/Los Angeles (PST - UTC-8)',
            'UTC' => 'UTC (Coordinated Universal Time)',
        ];
    }
}

if (!function_exists('get_all_date_formats')) {
    /**
     * Get all available date formats
     *
     * @return array
     */
    function get_all_date_formats(): array
    {
        return [
            'Y-m-d' => 'YYYY-MM-DD (2025-01-15)',
            'd-m-Y' => 'DD-MM-YYYY (15-01-2025)',
            'm-d-Y' => 'MM-DD-YYYY (01-15-2025)',
            'd/m/Y' => 'DD/MM/YYYY (15/01/2025)',
            'm/d/Y' => 'MM/DD/YYYY (01/15/2025)',
            'Y/m/d' => 'YYYY/MM/DD (2025/01/15)',
            'd.m.Y' => 'DD.MM.YYYY (15.01.2025)',
            'Y.m.d' => 'YYYY.MM.DD (2025.01.15)',
            'F j, Y' => 'Month D, YYYY (January 15, 2025)',
            'D, M j, Y' => 'Day, Mon D, YYYY (Wed, Jan 15, 2025)',
            'j F Y' => 'D Month YYYY (15 January 2025)',
            'l, F j, Y' => 'Day, Month D, YYYY (Wednesday, January 15, 2025)',
        ];
    }
}

if (!function_exists('get_common_date_formats')) {
    /**
     * Get commonly used date formats
     *
     * @return array
     */
    function get_common_date_formats(): array
    {
        return [
            'Y-m-d' => 'YYYY-MM-DD (2025-01-15)',
            'd-m-Y' => 'DD-MM-YYYY (15-01-2025)',
            'm-d-Y' => 'MM-DD-YYYY (01-15-2025)',
            'd/m/Y' => 'DD/MM/YYYY (15/01/2025)',
            'm/d/Y' => 'MM/DD/YYYY (01/15/2025)',
            'F j, Y' => 'Month D, YYYY (January 15, 2025)',
        ];
    }
}

if (!function_exists('get_all_time_formats')) {
    /**
     * Get all available time formats
     *
     * @return array
     */
    function get_all_time_formats(): array
    {
        return [
            'H:i:s' => '24-hour with seconds (23:45:30)',
            'H:i' => '24-hour without seconds (23:45)',
            'h:i:s A' => '12-hour with seconds (11:45:30 PM)',
            'h:i A' => '12-hour without seconds (11:45 PM)',
            'h:i a' => '12-hour lowercase (11:45 pm)',
            'g:i A' => '12-hour no leading zero (11:45 PM)',
            'g:i a' => '12-hour no leading zero lowercase (11:45 pm)',
        ];
    }
}

if (!function_exists('get_common_time_formats')) {
    /**
     * Get commonly used time formats
     *
     * @return array
     */
    function get_common_time_formats(): array
    {
        return [
            'H:i' => '24-hour (23:45)',
            'h:i A' => '12-hour (11:45 PM)',
            'h:i a' => '12-hour lowercase (11:45 pm)',
            'H:i:s' => '24-hour with seconds (23:45:30)',
        ];
    }
}

if (!function_exists('get_currency_positions')) {
    /**
     * Get available currency symbol positions
     *
     * @return array
     */
    function get_currency_positions(): array
    {
        return [
            'left' => 'Left ($100.00)',
            'right' => 'Right (100.00$)',
        ];
    }
}

if (!function_exists('get_decimal_separators')) {
    /**
     * Get available decimal separators
     *
     * @return array
     */
    function get_decimal_separators(): array
    {
        return [
            '.' => 'Dot (100.50)',
            ',' => 'Comma (100,50)',
        ];
    }
}

if (!function_exists('get_thousand_separators')) {
    /**
     * Get available thousand separators
     *
     * @return array
     */
    function get_thousand_separators(): array
    {
        return [
            ',' => 'Comma (1,000)',
            '.' => 'Dot (1.000)',
            ' ' => 'Space (1 000)',
            '' => 'None (1000)',
        ];
    }
}

if (!function_exists('get_countries')) {
    /**
     * Get list of countries
     *
     * @return array
     */
    function get_countries(): array
    {
        return [
            'AF' => 'Afghanistan',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'PK' => 'Pakistan',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'IN' => 'India',
            'CN' => 'China',
            'JP' => 'Japan',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'RU' => 'Russia',
            'TR' => 'Turkey',
            'KR' => 'South Korea',
            'ID' => 'Indonesia',
            'TH' => 'Thailand',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
            'NG' => 'Nigeria',
            'BD' => 'Bangladesh',
            'IQ' => 'Iraq',
            'JO' => 'Jordan',
            'KW' => 'Kuwait',
            'OM' => 'Oman',
            'QA' => 'Qatar',
            'BH' => 'Bahrain',
            'LB' => 'Lebanon',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'PL' => 'Poland',
            'AT' => 'Austria',
            'CZ' => 'Czech Republic',
            'HU' => 'Hungary',
            'GR' => 'Greece',
            'PT' => 'Portugal',
            'IE' => 'Ireland',
            'NZ' => 'New Zealand',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'PE' => 'Peru',
            'VE' => 'Venezuela',
        ];
    }
}

if (!function_exists('get_order_statuses')) {
    /**
     * Get available order statuses
     *
     * @return array
     */
    function get_order_statuses(): array
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
            'on_hold' => 'On Hold',
        ];
    }
}

if (!function_exists('get_payment_methods')) {
    /**
     * Get available payment methods
     *
     * @return array
     */
    function get_payment_methods(): array
    {
        return [
            'stripe' => 'Stripe (Credit/Debit Card)',
            'paypal' => 'PayPal',
            'cash_on_delivery' => 'Cash on Delivery',
            'bank_transfer' => 'Bank Transfer',
            'razorpay' => 'Razorpay',
            'paytm' => 'Paytm',
            'instamojo' => 'Instamojo',
        ];
    }
}

if (!function_exists('get_shipping_methods')) {
    /**
     * Get available shipping methods
     *
     * @return array
     */
    function get_shipping_methods(): array
    {
        return [
            'standard' => 'Standard Shipping',
            'express' => 'Express Shipping',
            'overnight' => 'Overnight Shipping',
            'international' => 'International Shipping',
            'pickup' => 'Store Pickup',
            'free' => 'Free Shipping',
        ];
    }
}

if (!function_exists('get_stock_statuses')) {
    /**
     * Get available stock statuses
     *
     * @return array
     */
    function get_stock_statuses(): array
    {
        return [
            'in_stock' => 'In Stock',
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'on_backorder' => 'On Backorder',
            'discontinued' => 'Discontinued',
        ];
    }
}

if (!function_exists('get_product_statuses')) {
    /**
     * Get available product statuses
     *
     * @return array
     */
    function get_product_statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'draft' => 'Draft',
            'archived' => 'Archived',
        ];
    }
}

if (!function_exists('format_price')) {
    /**
     * Format price based on store settings
     *
     * @param float $amount
     * @param string|null $currencyCode
     * @param string|null $currencySymbol
     * @param string $position
     * @param int $decimals
     * @param string $decimalSep
     * @param string $thousandSep
     * @return string
     */
    function format_price(
        float $amount,
        ?string $currencyCode = null,
        ?string $currencySymbol = null,
        string $position = 'left',
        int $decimals = 2,
        string $decimalSep = '.',
        string $thousandSep = ','
    ): string {
        $symbol = $currencySymbol ?? get_currency_symbol($currencyCode ?? 'USD');

        $formatted = number_format($amount, $decimals, $decimalSep, $thousandSep);

        return match($position) {
            'left' => $symbol . $formatted,
            'right' => $formatted . $symbol,
            'left_space' => $symbol . ' ' . $formatted,
            'right_space' => $formatted . ' ' . $symbol,
            default => $symbol . $formatted,
        };
    }
}

if (!function_exists('get_weekdays')) {
    /**
     * Get weekdays for business hours
     *
     * @return array
     */
    function get_weekdays(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }
}

if (!function_exists('get_admin_roles')) {
    /**
     * Get available admin roles
     *
     * @return array
     */
    function get_admin_roles(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'viewer' => 'Viewer',
        ];
    }
}

if (!function_exists('get_customer_groups')) {
    /**
     * Get available customer groups
     *
     * @return array
     */
    function get_customer_groups(): array
    {
        return [
            'retail' => 'Retail Customer',
            'wholesale' => 'Wholesale Customer',
            'vip' => 'VIP Customer',
            'business' => 'Business Customer',
            'guest' => 'Guest',
        ];
    }
}

if (!function_exists('get_tax_classes')) {
    /**
     * Get available tax classes
     *
     * @return array
     */
    function get_tax_classes(): array
    {
        return [
            'standard' => 'Standard Rate',
            'reduced' => 'Reduced Rate',
            'zero' => 'Zero Rate',
            'exempt' => 'Tax Exempt',
        ];
    }
}
