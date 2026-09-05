<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentResultRecorder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $gateway,
        private readonly array $payload,
    ) {}

    public function handle(PaymentManager $payments, PaymentResultRecorder $recorder): void
    {
        $transactionId = $this->payload['tran_id'] ?? $this->payload['order_id'] ?? null;

        $payment = $transactionId
            ? Payment::where('transaction_id', $transactionId)->first()
            : null;

        if (! $payment) {
            Log::warning('ProcessPaymentWebhook: no matching payment found', [
                'gateway' => $this->gateway,
                'payload' => $this->payload,
            ]);

            return;
        }

        try {
            $driver = $payments->driver($this->gateway);
            $fakeRequest = Request::create('', 'POST', $this->payload);
            $result = $driver->verify($fakeRequest, $payment);

            if ($result['success']) {
                $recorder->recordSuccess($payment, $result);
            } else {
                $recorder->recordFailure($payment, $result);
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessPaymentWebhook: failed to process webhook', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
