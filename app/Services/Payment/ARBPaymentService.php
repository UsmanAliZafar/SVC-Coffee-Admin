<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Exception;

class ARBPaymentService
{
    protected $config;
    protected $environment;

    // Fixed IV for AES encryption (standard for ARB)
    protected $AES_IV = "PGKEYENCDECIVSPC";
    protected $AES_METHOD = "AES-256-CBC";

    public function __construct()
    {
        $this->environment = config('arb-payment.environment');
        $this->config = config("arb-payment.{$this->environment}");
    }

    /**
     * Generate Payment Token (Bank Hosted)
     */
    public function generatePaymentToken(array $paymentData): array
    {
        try {
            // Prepare trandata
            $trandata = $this->prepareTrandata($paymentData);

            Log::info('ARB Payment - Trandata Prepared', [
                'trackid' => $trandata['trackid'] ?? 'N/A',
                'amount' => $trandata['amt'] ?? 'N/A',
                'terminal_id' => $trandata['terminalId'] ?? 'N/A'
            ]);

            // Convert to JSON array format as per ARB specifications
            $trandataArray = [$trandata]; // Wrap in array [{...}]
            $trandataString = json_encode($trandataArray);

            Log::info('ARB Payment - Trandata JSON String', [
                'length' => strlen($trandataString),
                'sample' => substr($trandataString, 0, 200),
                'format' => 'JSON Array'
            ]);

            // Encrypt trandata (returns HEX string)
            $encryptedTrandata = $this->encryptAES($trandataString, $this->config['resource_key']);

            Log::info('ARB Payment - Trandata Encrypted', [
                'encrypted_length' => strlen($encryptedTrandata),
                'encrypted_sample' => substr($encryptedTrandata, 0, 100),
                'is_hex' => ctype_xdigit(urldecode($encryptedTrandata))
            ]);

            // Build complete URL with query parameters (GET request)
            $queryParams = [
                'trandata' => $encryptedTrandata,
                'errorURL' => config('arb-payment.error_url'),
                'responseURL' => config('arb-payment.response_url'),
                'tranportalId' => $this->config['tranportal_id'],
                'tranportalPassword' => $this->config['tranportal_password']
            ];

            $paymentUrl = $this->config['payment_url'] . '?' . http_build_query($queryParams);

            Log::info('ARB Payment - Payment URL Generated', [
                'url_length' => strlen($paymentUrl),
                'base_url' => $this->config['payment_url']
            ]);

            return [
                'payment_url' => $paymentUrl,
                'trandata' => $encryptedTrandata,
                'query_params' => $queryParams
            ];

        } catch (Exception $e) {
            Log::error('ARB Payment - Token Generation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare transaction data
     */
    protected function prepareTrandata(array $data): array
    {
        // Generate track ID if not provided
        $trackId = $data['track_id'] ?? 'TRK-' . time() . '-' . uniqid();

        // IMPORTANT: According to ARB documentation, password and id
        // MUST be INSIDE the encrypted trandata (as JSON shows)

        return [
            // Amount and Action
            'amt' => number_format($data['amount'], 2, '.', ''),
            'action' => '1', // 1 = Purchase

            // Portal Credentials - INSIDE trandata (as per document)
            'password' => $this->config['tranportal_password'],
            'id' => $this->config['tranportal_id'],

            // Currency and Track ID
            'currencyCode' => '682', // SAR = 682
            'trackId' => $trackId,

            // URLs - MUST be in trandata
            'responseURL' => config('arb-payment.response_url'),
            'errorURL' => config('arb-payment.error_url'),

            // Optional: Language
            'langid' => 'USA',

            // Optional: Customer Information
            'cust_email' => $data['customer_email'] ?? '',

            // Optional: User Defined Fields
            'udf1' => $data['udf1'] ?? '',
            'udf2' => $data['udf2'] ?? '',
            'udf3' => $data['udf3'] ?? '',
            'udf4' => $data['udf4'] ?? '',
            'udf5' => $data['udf5'] ?? '',
        ];
    }

    /**
     * Encrypt data using AES-256-CBC (WordPress plugin method)
     */
    public function encryptAES(string $str, string $key): string
    {
        // Add PKCS5 padding
        $str = $this->pkcs5_pad($str);

        // Encrypt with ZERO padding
        $encrypted = openssl_encrypt(
            $str,
            $this->AES_METHOD,
            $key,
            OPENSSL_ZERO_PADDING,
            $this->AES_IV
        );

        // Convert to byte array then to HEX
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', $encrypted);
        $encrypted = $this->byteArray2Hex($encrypted);

        // URL encode
        $encrypted = urlencode($encrypted);

        return $encrypted;
    }

    /**
     * Decrypt data using AES-256-CBC (WordPress plugin method)
     */
    public function decryptAES(string $code, string $key): string
    {
        // Remove URL encoding and convert from HEX
        $code = urldecode($code);
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);

        // Base64 encode for openssl_decrypt
        $code = base64_encode($code);

        // Decrypt
        $decrypted = openssl_decrypt(
            $code,
            $this->AES_METHOD,
            $key,
            OPENSSL_ZERO_PADDING,
            $this->AES_IV
        );

        // Remove PKCS5 padding
        return $this->pkcs5_unpad($decrypted);
    }

    /**
     * Handle callback response from payment gateway
     */
    public function handleCallback(array $callbackData): array
    {
        try {
            Log::info('ARB Callback Received - Raw Data', $callbackData);

            $result = [
                'success' => false,
                'message' => '',
                'data' => [],
            ];

            // Check if trandata exists
            if (!empty($callbackData['trandata'])) {
                // Decrypt trandata
                $decryptedData = $this->decryptAES(
                    $callbackData['trandata'],
                    $this->config['resource_key']
                );

                Log::info('ARB Callback - Decrypted Data (Raw)', [
                    'decrypted' => substr($decryptedData, 0, 500)
                ]);

                // Parse JSON array format
                $transactionDataArray = json_decode($decryptedData, true);

                // Handle both array and object format
                if (is_array($transactionDataArray) && isset($transactionDataArray[0])) {
                    // Array format: [{...}]
                    $transactionData = $transactionDataArray[0];
                } elseif (is_array($transactionDataArray)) {
                    // Object format: {...}
                    $transactionData = $transactionDataArray;
                } else {
                    // Fallback: try query string format for backward compatibility
                    parse_str($decryptedData, $transactionData);
                }

                Log::info('ARB Callback - Parsed Transaction Data', $transactionData);

                $result['data'] = $transactionData;

                // Check transaction result
                if (isset($transactionData['result'])) {
                    $resultLower = strtolower($transactionData['result']);
                    if ($resultLower === 'captured' || $resultLower === 'successful' || $resultLower === 'success') {
                        $result['success'] = true;
                        $result['message'] = 'Transaction successful';
                    } else {
                        $result['message'] = $transactionData['result'];
                    }
                } else {
                    $result['message'] = $transactionData['errorText'] ?? 'Transaction failed';
                }
            } else {
                // Handle error response (no trandata)
                Log::warning('ARB Callback - No trandata found', $callbackData);

                $result['message'] = $callbackData['ErrorText'] ?? 'Transaction failed';
                $result['data'] = [
                    'error_code' => $callbackData['Error'] ?? null,
                    'transaction_id' => $callbackData['tranid'] ?? null,
                    'payment_id' => $callbackData['paymentid'] ?? null,
                ];
            }

            Log::info('ARB Callback Processed', [
                'success' => $result['success'],
                'message' => $result['message']
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('ARB Callback Handling Error', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // ==========================================
    // Helper Functions (from WordPress plugin)
    // ==========================================

    /**
     * PKCS5 Padding
     */
    private function pkcs5_pad(string $text): string
    {
        $blocksize = 16; // AES block size
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * PKCS5 Unpadding
     */
    private function pkcs5_unpad(string $text)
    {
        $pad = ord($text[strlen($text) - 1]);

        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }

    /**
     * Convert byte array to HEX string
     */
    private function byteArray2Hex(array $byteArray): string
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /**
     * Convert HEX string to byte array
     */
    private function hex2ByteArray(string $hexString): array
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    /**
     * Convert byte array to string
     */
    private function byteArray2String(array $byteArray): string
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }
}
