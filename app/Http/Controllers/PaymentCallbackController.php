<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentWebhook;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentResultRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentCallbackController extends Controller
{
    public function success(Request $request, Payment $payment, PaymentManager $payments, PaymentResultRecorder $recorder): View
    {
        $result = $payments->driver($payment->gateway->value)->verify($request, $payment);

        if ($result['success']) {
            $recorder->recordSuccess($payment, $result);
        } else {
            $recorder->recordFailure($payment, $result);
        }

        return view('pages.second-opinion-payment-result', [
            'payment' => $payment->refresh(),
            'outcome' => $result['success'] ? 'success' : 'failed',
        ]);
    }

    public function fail(Request $request, Payment $payment, PaymentManager $payments, PaymentResultRecorder $recorder): View
    {
        $result = $payments->driver($payment->gateway->value)->verify($request, $payment);
        $recorder->recordFailure($payment, $result);

        return view('pages.second-opinion-payment-result', [
            'payment' => $payment->refresh(),
            'outcome' => 'failed',
        ]);
    }

    public function cancel(Payment $payment, PaymentResultRecorder $recorder): View
    {
        $recorder->recordFailure($payment, ['success' => false, 'transaction_id' => null, 'raw' => ['reason' => 'cancelled']]);

        return view('pages.second-opinion-payment-result', [
            'payment' => $payment->refresh(),
            'outcome' => 'cancelled',
        ]);
    }

    public function webhook(Request $request, string $gateway, PaymentManager $payments): Response
    {
        $driver = $payments->driver($gateway);

        abort_unless($driver->verifyWebhookSignature($request), 400, 'Invalid signature');

        ProcessPaymentWebhook::dispatch($gateway, $request->all());

        return response('OK');
    }
}
