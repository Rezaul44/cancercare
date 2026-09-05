<?php

namespace App\Services\Payment\Drivers;

use App\Models\Payment;
use App\Services\Payment\PaymentGatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Nagad Payment Gateway (sandbox) — RSA sign/encrypt ভিত্তিক চেকইন-আউট API।
 * ⚠️ এই ড্রাইভারের এনক্রিপশন/সিগনেচার রাউন্ড-ট্রিপ Nagad-এর প্রকাশ্য ডকুমেন্টেশন অনুযায়ী
 * লেখা হয়েছে কিন্তু আসল sandbox মার্চেন্ট কী দিয়ে যাচাই করা হয়নি — লাইভ যাচাইয়ের আগে
 * এই ফাইলটাই সবচেয়ে বেশি মনোযোগ দাবি করে।
 */
class NagadDriver implements PaymentGatewayContract
{
    /**
     * @param  array{merchant_id?: string, merchant_private_key?: string, pg_public_key?: string, sandbox?: bool}  $config
     */
    public function __construct(private readonly array $config) {}

    private function baseUrl(): string
    {
        return ($this->config['sandbox'] ?? true)
            ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs'
            : 'https://api.mynagad.com/remote-payment-gateway-1.0/api/dfs';
    }

    public function initiate(Payment $payment, string $successUrl, string $failUrl, string $cancelUrl): string
    {
        $merchantId = $this->config['merchant_id'] ?? '';
        $orderId = 'SO-'.$payment->id.'-'.Str::random(6);
        $challenge = Str::random(40);

        $initSensitive = [
            'merchantId' => $merchantId,
            'datetime' => now()->format('YmdHis'),
            'orderId' => $orderId,
            'challenge' => $challenge,
        ];

        $initResponse = Http::withHeaders($this->apiHeaders())
            ->post("{$this->baseUrl()}/check-out/initialize/{$merchantId}/{$orderId}", [
                'sensitiveData' => $this->encrypt(json_encode($initSensitive)),
                'signature' => $this->sign(json_encode($initSensitive)),
            ])->json();

        $paymentReferenceId = $initResponse['paymentReferenceId'] ?? null;
        $returnedChallenge = $initResponse['challenge'] ?? $challenge;

        if (empty($paymentReferenceId)) {
            Log::warning('Nagad initialize failed', ['payment_id' => $payment->id, 'response' => $initResponse]);

            return $failUrl;
        }

        $completeSensitive = [
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050',
            'amount' => (string) $payment->amount,
            'challenge' => $returnedChallenge,
        ];

        $completeResponse = Http::withHeaders($this->apiHeaders())
            ->post("{$this->baseUrl()}/check-out/complete/{$paymentReferenceId}", [
                'sensitiveData' => $this->encrypt(json_encode($completeSensitive)),
                'signature' => $this->sign(json_encode($completeSensitive)),
                'merchantCallbackURL' => $successUrl,
            ])->json();

        $payment->update([
            'transaction_id' => $orderId,
            'raw_response' => [
                'order_id' => $orderId,
                'payment_reference_id' => $paymentReferenceId,
                'init' => $initResponse,
                'complete' => $completeResponse,
            ],
        ]);

        return $completeResponse['callBackUrl'] ?? $failUrl;
    }

    public function verify(Request $request, Payment $payment): array
    {
        $paymentReferenceId = $payment->raw_response['payment_reference_id'] ?? $request->query('payment_ref_id');

        if (empty($paymentReferenceId)) {
            return ['success' => false, 'transaction_id' => $payment->transaction_id, 'raw' => $request->all()];
        }

        $response = Http::withHeaders($this->apiHeaders())
            ->get("{$this->baseUrl()}/check-out/status/{$paymentReferenceId}")
            ->json();

        $success = ($response['status'] ?? null) === 'Success';

        return [
            'success' => $success,
            'transaction_id' => $response['issuerPaymentRefNo'] ?? $payment->transaction_id,
            'raw' => $response ?? [],
        ];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        // Nagad মূলত redirect + status-lookup দিয়ে যাচাই হয়; সরাসরি IPN নেই।
        return true;
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(): array
    {
        return [
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => request()->ip() ?? '127.0.0.1',
            'X-KM-Client-Type' => 'PC_WEB',
            'Content-Type' => 'application/json',
        ];
    }

    private function encrypt(string $data): string
    {
        $publicKey = $this->config['pg_public_key'] ?? '';

        if (empty($publicKey) || ! openssl_public_encrypt($data, $encrypted, $publicKey)) {
            return '';
        }

        return base64_encode($encrypted);
    }

    private function sign(string $data): string
    {
        $privateKey = $this->config['merchant_private_key'] ?? '';

        if (empty($privateKey) || ! openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return '';
        }

        return base64_encode($signature);
    }
}
