<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Enums\SecondOpinionStatus;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * পেমেন্ট verify/webhook-এর ফলাফল অনুযায়ী payments ও second_opinion_requests টেবিল
 * হালনাগাদ করে। gateway_fee শতাংশ settings টেবিল থেকে আসে, হার্ডকোড নয়।
 *
 * @see docs/CLAUDE.md "যা DB-তে থাকবে, কোডে হার্ডকোড নয়"
 */
class PaymentResultRecorder
{
    /**
     * @param  array{success: bool, transaction_id: ?string, raw: array<string, mixed>}  $result
     */
    public function recordSuccess(Payment $payment, array $result): void
    {
        if ($payment->status === PaymentStatus::Success) {
            return; // ইতিমধ্যে প্রসেস হয়ে গেছে — webhook ও return-leg দুটোই আসতে পারে
        }

        $feePercent = (float) (Setting::group('second_opinion')['gateway_fee_percent'][$payment->gateway->value] ?? 0);
        $gatewayFee = (int) round($payment->amount * $feePercent / 100);

        $payment->update([
            'status' => PaymentStatus::Success,
            'transaction_id' => $result['transaction_id'] ?? $payment->transaction_id,
            'gateway_fee' => $gatewayFee,
            'raw_response' => array_merge($payment->raw_response ?? [], ['verify' => $result['raw']]),
            'paid_at' => Carbon::now(),
        ]);

        $request = $payment->payable;

        if ($request && $request->status !== SecondOpinionStatus::Submitted) {
            $request->update([
                'payment_id' => $payment->id,
                'status' => SecondOpinionStatus::Submitted,
            ]);
        }
    }

    /**
     * @param  array{success: bool, transaction_id: ?string, raw: array<string, mixed>}  $result
     */
    public function recordFailure(Payment $payment, array $result): void
    {
        if ($payment->status === PaymentStatus::Success) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'raw_response' => array_merge($payment->raw_response ?? [], ['verify' => $result['raw']]),
        ]);

        // second_opinion_requests.status pending_payment-এই থাকে — ব্যবহারকারী retry করতে পারবেন।
    }
}
