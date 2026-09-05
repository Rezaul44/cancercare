<?php

namespace App\Services\Payment\Drivers;

use App\Models\Payment;
use App\Services\Payment\PaymentGatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * bKash Tokenized Checkout (PGW) sandbox — https://developer.bka.sh/
 * এখানে শুধু একটাই callbackURL থাকে (success/fail/cancel সবই এখানে ফেরত আসে,
 * ?status= প্যারামিটার দিয়ে পার্থক্য বোঝা যায়) — তাই initiate()-এ successUrl-ই callbackURL হিসেবে দেওয়া হয়।
 */
class BkashDriver implements PaymentGatewayContract
{
    /**
     * @param  array{app_key?: string, app_secret?: string, username?: string, password?: string, sandbox?: bool}  $config
     */
    public function __construct(private readonly array $config) {}

    private function baseUrl(): string
    {
        return ($this->config['sandbox'] ?? true)
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    private function grantToken(): string
    {
        return Cache::remember('bkash.id_token', 3300, function () {
            $response = Http::withHeaders([
                'username' => $this->config['username'] ?? '',
                'password' => $this->config['password'] ?? '',
            ])->post($this->baseUrl().'/tokenized/checkout/token/grant', [
                'app_key' => $this->config['app_key'] ?? '',
                'app_secret' => $this->config['app_secret'] ?? '',
            ])->json();

            return $response['id_token'] ?? '';
        });
    }

    public function initiate(Payment $payment, string $successUrl, string $failUrl, string $cancelUrl): string
    {
        $token = $this->grantToken();

        $response = Http::withToken($token)->withHeaders([
            'X-App-Key' => $this->config['app_key'] ?? '',
        ])->post($this->baseUrl().'/tokenized/checkout/create', [
            'mode' => '0011',
            'payerReference' => (string) $payment->payable_id,
            'callbackURL' => $successUrl,
            'amount' => (string) $payment->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => 'SO-'.$payment->id,
        ])->json();

        $payment->update(['raw_response' => $response]);

        if (empty($response['bkashURL'])) {
            Log::warning('bKash create payment failed', ['payment_id' => $payment->id, 'response' => $response]);

            return $failUrl;
        }

        return $response['bkashURL'];
    }

    public function verify(Request $request, Payment $payment): array
    {
        $status = $request->query('status');
        $paymentId = $request->query('paymentID', $payment->raw_response['paymentID'] ?? null);

        if ($status !== 'success' || empty($paymentId)) {
            return ['success' => false, 'transaction_id' => null, 'raw' => $request->all()];
        }

        $response = Http::withToken($this->grantToken())->withHeaders([
            'X-App-Key' => $this->config['app_key'] ?? '',
        ])->post($this->baseUrl().'/tokenized/checkout/execute', [
            'paymentID' => $paymentId,
        ])->json();

        $success = ($response['transactionStatus'] ?? null) === 'Completed';

        return [
            'success' => $success,
            'transaction_id' => $response['trxID'] ?? null,
            'raw' => $response ?? [],
        ];
    }

    /**
     * bKash-এর টোকেনাইজড চেকআউটে আলাদা সার্ভার-টু-সার্ভার IPN নেই — verify() নিজেই
     * execute কল করে সত্যতা যাচাই করে, তাই এখানে সবসময় true।
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        return true;
    }
}
