<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayContract
{
    /**
     * পেমেন্ট শুরু করে, ব্যবহারকারীকে যেখানে পাঠাতে হবে সেই URL রিটার্ন করে।
     * গেটওয়ে-নির্দিষ্ট রেফারেন্স (paymentID, tran_id ইত্যাদি) $payment->raw_response-এ সংরক্ষণ করা হয়।
     */
    public function initiate(Payment $payment, string $successUrl, string $failUrl, string $cancelUrl): string;

    /**
     * ফেরত আসা রিকোয়েস্ট (success/fail/cancel) যাচাই করে গেটওয়ের প্রকৃত অবস্থা কনফার্ম করে।
     *
     * @return array{success: bool, transaction_id: ?string, raw: array<string, mixed>}
     */
    public function verify(Request $request, Payment $payment): array;

    /**
     * সার্ভার-টু-সার্ভার webhook/IPN-এর স্বাক্ষর/হ্যাশ যাচাই করে — জাল রিকোয়েস্ট ঠেকাতে।
     */
    public function verifyWebhookSignature(Request $request): bool;
}
