<?php

namespace App\Services\Payment\Drivers;

use App\Models\Payment;
use App\Services\Payment\PaymentGatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SSLCommerz সেশন API (sandbox) — https://developer.sslcommerz.com/doc/v4/
 * নিজস্ব হোস্টেড পেজে redirect করে, তারপর success/fail/cancel URL-এ ফেরত পাঠায়
 * এবং আলাদাভাবে IPN (webhook) পাঠায়।
 */
class SslcommerzDriver implements PaymentGatewayContract
{
    /**
     * @param  array{store_id?: string, store_password?: string, sandbox?: bool}  $config
     */
    public function __construct(private readonly array $config) {}

    private function baseUrl(): string
    {
        return ($this->config['sandbox'] ?? true)
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initiate(Payment $payment, string $successUrl, string $failUrl, string $cancelUrl): string
    {
        $tranId = 'SO-'.$payment->id.'-'.$payment->created_at?->timestamp;

        $response = Http::asForm()->post($this->baseUrl().'/gwprocess/v4/api.php', [
            'store_id' => $this->config['store_id'] ?? '',
            'store_passwd' => $this->config['store_password'] ?? '',
            'total_amount' => $payment->amount,
            'currency' => 'BDT',
            'tran_id' => $tranId,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => route('payments.webhook', ['gateway' => 'sslcommerz']),
            'shipping_method' => 'NO',
            'product_name' => 'Second Opinion',
            'product_category' => 'Service',
            'product_profile' => 'general',
            'cus_name' => 'CCB Patient',
            'cus_email' => 'no-reply@cancercarebd.example',
            'cus_add1' => 'Bangladesh',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => '01700000000',
        ])->json();

        $payment->update([
            'transaction_id' => $tranId,
            'raw_response' => $response,
        ]);

        if (($response['status'] ?? null) !== 'SUCCESS' || empty($response['GatewayPageURL'])) {
            Log::warning('SSLCommerz session init failed', ['payment_id' => $payment->id, 'response' => $response]);

            return $failUrl;
        }

        return $response['GatewayPageURL'];
    }

    public function verify(Request $request, Payment $payment): array
    {
        $valId = $request->query('val_id', $request->input('val_id'));

        if (empty($valId)) {
            return ['success' => false, 'transaction_id' => $payment->transaction_id, 'raw' => $request->all()];
        }

        $response = Http::get($this->baseUrl().'/validator/api/validationserverAPI.php', [
            'val_id' => $valId,
            'store_id' => $this->config['store_id'] ?? '',
            'store_passwd' => $this->config['store_password'] ?? '',
            'format' => 'json',
        ])->json();

        $success = in_array($response['status'] ?? null, ['VALID', 'VALIDATED'], true);

        return [
            'success' => $success,
            'transaction_id' => $response['tran_id'] ?? $payment->transaction_id,
            'raw' => $response ?? [],
        ];
    }

    /**
     * SSLCommerz IPN-এ verify_key ফিল্ডে যেসব প্যারামিটার নাম দেওয়া থাকে সেগুলো ব্যবহার করে
     * store_passwd-এর md5 যোগ করে হ্যাশ মিলিয়ে verify_sign যাচাই হয়।
     * ⚠️ আসল sandbox ক্রেডেনশিয়াল দিয়ে যাচাই না করা পর্যন্ত এই লজিক নিশ্চিতভাবে সঠিক ধরে নেওয়া যাবে না।
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $verifyKey = $request->input('verify_key');
        $verifySign = $request->input('verify_sign');

        if (empty($verifyKey) || empty($verifySign)) {
            return false;
        }

        $fields = explode(',', (string) $verifyKey);
        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $request->input($field, '');
        }

        $data['store_passwd'] = md5((string) ($this->config['store_password'] ?? ''));
        ksort($data);

        $computed = md5(urldecode(http_build_query($data)));

        return hash_equals($computed, (string) $verifySign);
    }
}
