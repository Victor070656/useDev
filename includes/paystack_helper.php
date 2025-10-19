<?php

/**
 * Paystack Payment Integration Helper
 * Documentation: https://paystack.com/docs/api/
 */

class PaystackHelper {

    private $secretKey;
    private $publicKey;
    private $baseUrl;

    public function __construct() {
        $this->secretKey = PAYSTACK_SECRET_KEY;
        $this->publicKey = PAYSTACK_PUBLIC_KEY;
        $this->baseUrl = 'https://api.paystack.co';
    }

    /**
     * Initialize a payment transaction
     *
     * @param string $email Customer email
     * @param int $amount Amount in kobo (smallest currency unit)
     * @param string $reference Unique transaction reference
     * @param array $metadata Additional transaction data
     * @return array Response from Paystack
     */
    public function initializeTransaction($email, $amount, $reference, $metadata = []) {
        $url = $this->baseUrl . '/transaction/initialize';

        $data = [
            'email' => $email,
            'amount' => $amount, // Amount in kobo (100 kobo = 1 Naira)
            'reference' => $reference,
            'metadata' => $metadata,
            'callback_url' => APP_URL . '/client/payment-callback.php'
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Verify a transaction
     *
     * @param string $reference Transaction reference
     * @return array Response from Paystack
     */
    public function verifyTransaction($reference) {
        $url = $this->baseUrl . '/transaction/verify/' . $reference;
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Get transaction details
     *
     * @param int $transactionId Transaction ID
     * @return array Response from Paystack
     */
    public function getTransaction($transactionId) {
        $url = $this->baseUrl . '/transaction/' . $transactionId;
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Create a transfer recipient (for payouts to creators)
     *
     * @param string $type Account type (nuban, mobile_money, etc.)
     * @param string $name Account name
     * @param string $accountNumber Account number
     * @param string $bankCode Bank code
     * @param string $currency Currency (NGN, GHS, ZAR, USD)
     * @return array Response from Paystack
     */
    public function createTransferRecipient($type, $name, $accountNumber, $bankCode, $currency = 'NGN') {
        $url = $this->baseUrl . '/transferrecipient';

        $data = [
            'type' => $type,
            'name' => $name,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => $currency
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Initiate a transfer (payout to creator)
     *
     * @param string $source Source of funds (balance)
     * @param int $amount Amount in kobo
     * @param string $recipient Recipient code
     * @param string $reason Reason for transfer
     * @param string $reference Unique reference
     * @return array Response from Paystack
     */
    public function initiateTransfer($source, $amount, $recipient, $reason, $reference) {
        $url = $this->baseUrl . '/transfer';

        $data = [
            'source' => $source,
            'amount' => $amount,
            'recipient' => $recipient,
            'reason' => $reason,
            'reference' => $reference
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Verify transfer status
     *
     * @param string $reference Transfer reference
     * @return array Response from Paystack
     */
    public function verifyTransfer($reference) {
        $url = $this->baseUrl . '/transfer/verify/' . $reference;
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Get list of banks
     *
     * @param string $country Country code (nigeria, ghana, south-africa)
     * @param bool $useCursor Use cursor for pagination
     * @return array Response from Paystack
     */
    public function getBanks($country = 'nigeria', $useCursor = false) {
        $url = $this->baseUrl . '/bank?country=' . $country . '&use_cursor=' . ($useCursor ? 'true' : 'false');
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Resolve account number to get account name
     *
     * @param string $accountNumber Account number
     * @param string $bankCode Bank code
     * @return array Response from Paystack
     */
    public function resolveAccountNumber($accountNumber, $bankCode) {
        $url = $this->baseUrl . '/bank/resolve?account_number=' . $accountNumber . '&bank_code=' . $bankCode;
        return $this->makeRequest($url, 'GET');
    }

    /**
     * Create a refund
     *
     * @param string $transaction Transaction reference or ID
     * @param int $amount Amount to refund in kobo (optional, full refund if not specified)
     * @return array Response from Paystack
     */
    public function createRefund($transaction, $amount = null) {
        $url = $this->baseUrl . '/refund';

        $data = ['transaction' => $transaction];
        if ($amount !== null) {
            $data['amount'] = $amount;
        }

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Make HTTP request to Paystack API
     *
     * @param string $url API endpoint URL
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $data Request data (for POST requests)
     * @return array Response array with 'success', 'data', and 'message'
     */
    private function makeRequest($url, $method = 'GET', $data = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Cache-Control: no-cache'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'data' => null
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['status']) && $responseData['status']) {
            return [
                'success' => true,
                'message' => $responseData['message'] ?? 'Success',
                'data' => $responseData['data'] ?? null
            ];
        }

        return [
            'success' => false,
            'message' => $responseData['message'] ?? 'Unknown error occurred',
            'data' => $responseData['data'] ?? null
        ];
    }

    /**
     * Generate unique transaction reference
     *
     * @param string $prefix Optional prefix
     * @return string Unique reference
     */
    public static function generateReference($prefix = 'TXN') {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Convert cents to kobo (or smallest currency unit)
     * For USD: 1 cent = 1 kobo equivalent
     * For NGN: Amount is already in kobo
     *
     * @param int $cents Amount in cents
     * @param string $fromCurrency Source currency
     * @param string $toCurrency Target currency
     * @return int Amount in target currency's smallest unit
     */
    public static function convertCurrency($cents, $fromCurrency = 'USD', $toCurrency = 'NGN') {
        // Simple conversion - in production, use real-time exchange rates
        if ($fromCurrency === 'USD' && $toCurrency === 'NGN') {
            // Example: 1 USD = 1500 NGN (update with real rate)
            $exchangeRate = 1500;
            return $cents * $exchangeRate;
        }

        // If same currency or no conversion needed
        return $cents;
    }

    /**
     * Validate webhook signature
     *
     * @param string $payload Webhook payload
     * @param string $signature Signature from header
     * @return bool True if valid
     */
    public function validateWebhookSignature($payload, $signature) {
        $hash = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($hash, $signature);
    }
}

/**
 * Helper function to get Paystack instance
 */
function get_paystack() {
    static $paystack = null;
    if ($paystack === null) {
        $paystack = new PaystackHelper();
    }
    return $paystack;
}
