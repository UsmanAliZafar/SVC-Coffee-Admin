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

            Log::info('========================================');
            Log::info('📋 ARB PAYMENT - TRANDATA PREPARED');
            Log::info('========================================');
            Log::info('Track ID: ' . ($trandata['trackId'] ?? 'N/A'));
            Log::info('Amount: ' . ($trandata['amt'] ?? 'N/A'));
            Log::info('Currency: ' . ($trandata['currencyCode'] ?? 'N/A'));
            Log::info('Customer Email: ' . ($trandata['cust_email'] ?? 'N/A'));
            Log::info('----------------------------------------');

            // Convert to JSON array format as per ARB specifications
            $trandataArray = [$trandata];
            $trandataString = json_encode($trandataArray, JSON_PRETTY_PRINT);

            Log::info('📄 PLAIN TRANDATA (JSON ARRAY FORMAT)');
            Log::info('========================================');
            Log::info('COPY THIS FOR TESTING:');
            Log::info('========================================');
            Log::info($trandataString);
            Log::info('========================================');
            Log::info('Length: ' . strlen($trandataString) . ' characters');
            Log::info('----------------------------------------');

            // Encrypt trandata (returns HEX string)
            $encryptedTrandata = $this->encryptAES($trandataString, $this->config['resource_key']);

            Log::info('🔐 ENCRYPTED TRANDATA');
            Log::info('========================================');
            Log::info('COPY THIS FOR POSTMAN:');
            Log::info('========================================');
            Log::info($encryptedTrandata);
            Log::info('========================================');
            Log::info('Length: ' . strlen($encryptedTrandata) . ' characters');
            Log::info('Is Valid HEX: ' . (ctype_xdigit(urldecode($encryptedTrandata)) ? 'YES' : 'NO'));
            Log::info('----------------------------------------');

            // Test decryption
            $decryptedTest = $this->decryptAES($encryptedTrandata, $this->config['resource_key']);
            $encryptionValid = ($decryptedTest === $trandataString);

            Log::info('✅ ENCRYPTION VERIFICATION');
            Log::info('========================================');
            Log::info('Encryption Valid: ' . ($encryptionValid ? 'YES ✅' : 'NO ❌'));
            if (!$encryptionValid) {
                Log::error('Original Length: ' . strlen($trandataString));
                Log::error('Decrypted Length: ' . strlen($decryptedTest));
                Log::error('Decrypted Sample: ' . substr($decryptedTest, 0, 200));
            }
            Log::info('----------------------------------------');

            // Build request payload
            $requestPayload = [
                [
                    'id' => $this->config['tranportal_id'],
                    'trandata' => $encryptedTrandata,
                    'responseURL' => config('arb-payment.response_url'),
                    'errorURL' => config('arb-payment.error_url')
                ]
            ];

            Log::info('📦 REQUEST PAYLOAD FOR ARB GATEWAY');
            Log::info('========================================');
            Log::info('COPY THIS COMPLETE JSON FOR POSTMAN:');
            Log::info('========================================');
            Log::info(json_encode($requestPayload, JSON_PRETTY_PRINT));
            Log::info('========================================');
            Log::info('Endpoint: ' . $this->config['payment_url']);
            Log::info('Method: POST');
            Log::info('Content-Type: application/json');
            Log::info('----------------------------------------');

            // cURL command for quick testing
            $curlCommand = $this->generateCurlCommand(
                $this->config['payment_url'],
                $requestPayload
            );

            Log::info('🔧 CURL COMMAND FOR TESTING');
            Log::info('========================================');
            Log::info('COPY THIS TO TEST IN TERMINAL:');
            Log::info('========================================');
            Log::info($curlCommand);
            Log::info('========================================');

            Log::info('✅ Payment Token Generation Complete');
            Log::info('========================================');

            return [
                'request_payload' => $requestPayload,
                'payment_url' => $this->config['payment_url'],
                'trandata' => $encryptedTrandata,
                'plain_trandata' => $trandataString,
                'curl_command' => $curlCommand
            ];

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ ARB PAYMENT - TOKEN GENERATION ERROR');
            Log::error('========================================');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile());
            Log::error('Error Line: ' . $e->getLine());
            Log::error('Stack Trace:');
            Log::error($e->getTraceAsString());
            Log::error('========================================');
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

        return [
            // Amount and Action
            'amt' => number_format($data['amount'], 2, '.', ''),
            'action' => '1', // 1 = Purchase

            // Portal Credentials - INSIDE trandata
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
     * Encrypt data using AES-256-CBC
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
     * Decrypt data using AES-256-CBC
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

    public function handleCallback(array $callbackData): array
    {
        try {
            Log::info('========================================');
            Log::info('📥 ARB CALLBACK RECEIVED');
            Log::info('========================================');
            Log::info('Raw Callback Data:');
            Log::info(json_encode($callbackData, JSON_PRETTY_PRINT));
            Log::info('----------------------------------------');

            $result = [
                'success' => false,
                'message' => '',
                'data' => [],
            ];

            // Check if trandata exists
            if (!empty($callbackData['trandata'])) {
                Log::info('🔓 DECRYPTING CALLBACK TRANDATA');
                Log::info('----------------------------------------');
                Log::info('Encrypted Trandata (first 100 chars):');
                Log::info(substr($callbackData['trandata'], 0, 100));
                Log::info('----------------------------------------');

                // Decrypt trandata
                $decryptedData = $this->decryptAES(
                    $callbackData['trandata'],
                    $this->config['resource_key']
                );

                // URL decode the decrypted data
                $decryptedData = urldecode($decryptedData);

                Log::info('📄 DECRYPTED CALLBACK DATA');
                Log::info('========================================');
                Log::info('COPY THIS DECRYPTED DATA:');
                Log::info('========================================');
                Log::info($decryptedData);
                Log::info('========================================');

                // Parse JSON array format
                $transactionDataArray = json_decode($decryptedData, true);

                // Handle both array and object format
                if (is_array($transactionDataArray) && isset($transactionDataArray[0])) {
                    // Array format: [{...}]
                    $transactionData = $transactionDataArray[0];
                    Log::info('✅ Parsed as JSON Array');
                } elseif (is_array($transactionDataArray)) {
                    // Object format: {...}
                    $transactionData = $transactionDataArray;
                    Log::info('✅ Parsed as JSON Object');
                } else {
                    // Fallback: try query string format
                    parse_str($decryptedData, $transactionData);
                    Log::info('✅ Parsed as Query String');
                }

                Log::info('----------------------------------------');
                Log::info('📋 PARSED TRANSACTION DATA');
                Log::info('========================================');
                Log::info(json_encode($transactionData, JSON_PRETTY_PRINT));
                Log::info('========================================');

                $result['data'] = $transactionData;

                // Check transaction result
                if (isset($transactionData['result'])) {
                    $resultLower = strtolower($transactionData['result']);
                    if ($resultLower === 'captured' || $resultLower === 'successful' || $resultLower === 'success') {
                        $result['success'] = true;
                        $result['message'] = 'Transaction successful';
                        Log::info('✅ Transaction Status: SUCCESS');
                    } else {
                        $result['message'] = $transactionData['result'];
                        Log::warning('⚠️ Transaction Status: ' . $transactionData['result']);
                    }
                } else {
                    $result['message'] = $transactionData['errorText'] ?? 'Transaction failed';
                    Log::error('❌ Transaction Failed: ' . $result['message']);
                }
            } else {
                // Handle error response (no trandata)
                Log::warning('⚠️ NO TRANDATA IN CALLBACK');
                Log::warning('Error Response Received:');
                Log::warning(json_encode($callbackData, JSON_PRETTY_PRINT));

                $result['message'] = $callbackData['ErrorText'] ?? 'Transaction failed';
                $result['data'] = [
                    'error_code' => $callbackData['Error'] ?? null,
                    'transaction_id' => $callbackData['tranid'] ?? null,
                    'payment_id' => $callbackData['paymentid'] ?? null,
                ];
            }

            Log::info('========================================');
            Log::info('📊 CALLBACK PROCESSING RESULT');
            Log::info('========================================');
            Log::info('Success: ' . ($result['success'] ? 'YES ✅' : 'NO ❌'));
            Log::info('Message: ' . $result['message']);
            Log::info('========================================');

            return $result;

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ ARB CALLBACK HANDLING ERROR');
            Log::error('========================================');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Callback Data:');
            Log::error(json_encode($callbackData, JSON_PRETTY_PRINT));
            Log::error('Stack Trace:');
            Log::error($e->getTraceAsString());
            Log::error('========================================');
            throw $e;
        }
    }

    /**
     * Parse payment response from initial token generation
     */
    public function parsePaymentResponse(array $response): array
    {
        try {
            Log::info('========================================');
            Log::info('🔍 PARSING ARB GATEWAY RESPONSE');
            Log::info('========================================');
            Log::info('Raw Response:');
            Log::info(json_encode($response, JSON_PRETTY_PRINT));
            Log::info('----------------------------------------');

            if (isset($response[0])) {
                $responseData = $response[0];

                // Status "1" means success
                if (isset($responseData['status']) && $responseData['status'] == '1') {
                    // Parse result: "paymentId:paymentPageUrl"
                    if (isset($responseData['result'])) {
                        $parts = explode(':', $responseData['result'], 2);
                        $paymentId = $parts[0] ?? null;
                        $baseUrl = $parts[1] ?? null;

                        // Frame payment URL with PaymentID parameter
                        $paymentPageUrl = $baseUrl . '?PaymentID=' . $paymentId;

                        return [
                            'success' => true,
                            'payment_id' => $paymentId,
                            'payment_page_url' => $paymentPageUrl,
                            'status' => $responseData['status']
                        ];
                    }
                }

                // Error response
                $errorResult = [
                    'success' => false,
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'error_text' => $responseData['errorText'] ?? 'Payment initiation failed',
                    'status' => $responseData['status'] ?? null
                ];

                Log::error('❌ PAYMENT INITIATION FAILED');
                Log::error('========================================');
                Log::error('Error Code: ' . $errorResult['error']);
                Log::error('Error Text: ' . $errorResult['error_text']);
                Log::error('Status: ' . $errorResult['status']);
                Log::error('========================================');

                return $errorResult;
            }

            throw new Exception('Invalid response format');

        } catch (Exception $e) {
            Log::error('========================================');
            Log::error('❌ RESPONSE PARSING ERROR');
            Log::error('========================================');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Response Data:');
            Log::error(json_encode($response, JSON_PRETTY_PRINT));
            Log::error('========================================');
            throw $e;
        }
    }

    /**
     * Generate cURL command for testing
     */
    protected function generateCurlCommand(string $url, array $payload): string
    {
        $json = json_encode($payload);
        $json = str_replace("'", "'\\''", $json); // Escape single quotes

        return "curl --location '$url' \\\n" .
               "--header 'Content-Type: application/json' \\\n" .
               "--header 'X-FORWARDED-FOR: 203.0.113.195' \\\n" .
               "--data '$json'";
    }

    // ==========================================
    // Helper Functions
    // ==========================================

    private function pkcs5_pad(string $text): string
    {
        $blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

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

    private function byteArray2Hex(array $byteArray): string
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }

    private function hex2ByteArray(string $hexString): array
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    private function byteArray2String(array $byteArray): string
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }
}
