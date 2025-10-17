<?php

/**
 * ============================================
 * CURRENCY HELPER FUNCTIONS
 * ============================================
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
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'PKR' => 'Pakistani Rupee (₨)',
            'SAR' => 'Saudi Riyal (﷼)',
            'AED' => 'UAE Dirham (د.إ)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'INR' => 'Indian Rupee (₹)',
            'CHF' => 'Swiss Franc (Fr)',
            'SEK' => 'Swedish Krona (kr)',
            'NZD' => 'New Zealand Dollar (NZ$)',
            'KWD' => 'Kuwaiti Dinar (د.ك)',
            'BHD' => 'Bahraini Dinar (د.ب)',
            'OMR' => 'Omani Rial (ر.ع.)',
            'QAR' => 'Qatari Riyal (ر.ق)',
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

if (!function_exists('get_popular_currencies')) {
    /**
     * Get popular/main currencies
     *
     * @return array
     */
    function get_popular_currencies(): array
    {
        $all = get_all_currencies();
        $popular = ['USD', 'EUR', 'GBP', 'PKR', 'SAR'];

        return array_intersect_key($all, array_flip($popular));
    }
}

if (!function_exists('get_currency_symbols')) {
    /**
     * Get all currency symbols
     *
     * @return array
     */
    function get_currency_symbols(): array
    {
        return [
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
            'NZD' => 'NZ$',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'OMR' => 'ر.ع.',
            'QAR' => 'ر.ق',
        ];
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol by code
     *
     * @param string $code
     * @return string
     */
    function currency_symbol(string $code): string
    {
        $symbols = get_currency_symbols();
        return $symbols[strtoupper($code)] ?? $code;
    }
}

if (!function_exists('currency_name')) {
    /**
     * Get currency name by code
     *
     * @param string $code
     * @return string
     */
    function currency_name(string $code): string
    {
        $currencies = get_all_currencies();
        return $currencies[strtoupper($code)] ?? $code;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount with currency symbol
     *
     * @param float|int $amount
     * @param string $currencyCode
     * @param bool $symbolFirst
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, string $currencyCode = 'USD', bool $symbolFirst = true, int $decimals = 2): string
    {
        $symbol = currency_symbol($currencyCode);
        $formattedAmount = number_format((float)$amount, $decimals);

        return $symbolFirst
            ? $symbol . ' ' . $formattedAmount
            : $formattedAmount . ' ' . $symbol;
    }
}

if (!function_exists('is_valid_currency')) {
    /**
     * Check if currency code is valid
     *
     * @param string $code
     * @return bool
     */
    function is_valid_currency(string $code): bool
    {
        return array_key_exists(strtoupper($code), get_all_currencies());
    }
}

if (!function_exists('get_default_currency')) {
    /**
     * Get default currency from config or env
     *
     * @return string
     */
    function get_default_currency(): string
    {
        return config('app.default_currency', env('DEFAULT_CURRENCY', 'USD'));
    }
}

if (!function_exists('convert_currency_format')) {
    /**
     * Convert currency format for different locales
     *
     * @param float $amount
     * @param string $currencyCode
     * @param string $locale
     * @return string
     */
    function convert_currency_format(float $amount, string $currencyCode, string $locale = 'en_US'): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($amount, strtoupper($currencyCode));
        }

        return format_currency($amount, $currencyCode);
    }
}

if (!function_exists('get_currency_dropdown')) {
    /**
     * Get currency dropdown options HTML
     *
     * @param string|null $selected
     * @param bool $popularOnly
     * @return string
     */
    function get_currency_dropdown(?string $selected = null, bool $popularOnly = false): string
    {
        $currencies = $popularOnly ? get_popular_currencies() : get_all_currencies();
        $html = '';

        foreach ($currencies as $code => $name) {
            $isSelected = ($selected && strtoupper($selected) === $code) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%s" %s>%s</option>',
                $code,
                $isSelected,
                $name
            );
        }

        return $html;
    }
}

if (!function_exists('parse_currency_amount')) {
    /**
     * Parse currency string to float amount
     *
     * @param string $currencyString
     * @return float
     */
    function parse_currency_amount(string $currencyString): float
    {
        // Remove currency symbols and non-numeric characters except decimal point
        $cleaned = preg_replace('/[^0-9.]/', '', $currencyString);
        return (float)$cleaned;
    }
}

if (!function_exists('get_currency_info')) {
    /**
     * Get detailed currency information
     *
     * @param string $code
     * @return array
     */
    function get_currency_info(string $code): array
    {
        $code = strtoupper($code);

        return [
            'code' => $code,
            'name' => currency_name($code),
            'symbol' => currency_symbol($code),
            'is_valid' => is_valid_currency($code),
        ];
    }
}

/**
 * ============================================
 * END CURRENCY HELPER FUNCTIONS
 * ============================================
 */
