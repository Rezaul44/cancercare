<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Services\Payment\Drivers\BkashDriver;
use App\Services\Payment\Drivers\NagadDriver;
use App\Services\Payment\Drivers\SslcommerzDriver;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('services.payments.default', PaymentGateway::Sslcommerz->value);
    }

    public function createBkashDriver(): BkashDriver
    {
        return new BkashDriver((array) config('services.bkash', []));
    }

    public function createNagadDriver(): NagadDriver
    {
        return new NagadDriver((array) config('services.nagad', []));
    }

    public function createSslcommerzDriver(): SslcommerzDriver
    {
        return new SslcommerzDriver((array) config('services.sslcommerz', []));
    }
}
