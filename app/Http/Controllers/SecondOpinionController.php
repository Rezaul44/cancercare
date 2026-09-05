<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreSecondOpinionRequestRequest;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Setting;
use App\Services\Payment\PaymentManager;
use App\Services\SecondOpinionRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SecondOpinionController extends Controller
{
    private const DEFAULT_EXPECTED_HOURS = 72;

    /**
     * ধাপ-অনুযায়ী ফিল্ড, ভ্যালিডেশন ব্যর্থ হলে কোন ধাপে ফিরিয়ে দেখাতে হবে তা বের করতে।
     */
    private const STEP_FIELDS = [
        1 => ['reports'],
        2 => ['patient_name', 'age', 'cancer_type_id', 'current_status', 'treatments_done_bn', 'question_bn', 'phone', 'district_id'],
        3 => ['doctor_id'],
        4 => ['gateway'],
    ];

    public function create(): View
    {
        return view('pages.second-opinion', [
            'doctors' => Doctor::query()
                ->published()
                ->where('offers_second_opinion', true)
                ->with('cancerTypes')
                ->orderBy('name_bn')
                ->get(),
            'cancerTypes' => CancerType::orderBy('sort_order')->get(),
            'districts' => District::orderBy('name_bn')->get(),
            'expectedHours' => (int) (Setting::group('second_opinion')['expected_hours'] ?? self::DEFAULT_EXPECTED_HOURS),
            'initialStep' => $this->initialStep(),
        ]);
    }

    private function initialStep(): int
    {
        $errors = session('errors');

        if (! $errors) {
            return 1;
        }

        foreach (self::STEP_FIELDS as $step => $fields) {
            foreach ($fields as $field) {
                if ($errors->has($field) || $errors->has($field.'.*')) {
                    return $step;
                }
            }
        }

        return 1;
    }

    public function store(
        StoreSecondOpinionRequestRequest $request,
        SecondOpinionRequestService $service,
        PaymentManager $payments,
    ): RedirectResponse {
        $validated = $request->validated();

        $secondOpinionRequest = $service->submit($validated, $request->file('reports', []));

        $payment = $secondOpinionRequest->paymentAttempts()->create([
            'gateway' => $validated['gateway'],
            'amount' => $secondOpinionRequest->fee,
            'status' => PaymentStatus::Initiated,
        ]);

        $redirectUrl = $payments->driver($validated['gateway'])->initiate(
            $payment,
            route('payments.success', $payment),
            route('payments.fail', $payment),
            route('payments.cancel', $payment),
        );

        return redirect()->away($redirectUrl);
    }
}
