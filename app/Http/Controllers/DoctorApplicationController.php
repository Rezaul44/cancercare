<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorApplicationRequest;
use App\Models\CancerType;
use App\Models\DoctorType;
use App\Services\DoctorApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DoctorApplicationController extends Controller
{
    /**
     * ধাপ-অনুযায়ী ফিল্ড, ভ্যালিডেশন ব্যর্থ হলে কোন ধাপে ফিরিয়ে দেখাতে হবে তা বের করতে।
     */
    private const STEP_FIELDS = [
        1 => ['full_name', 'bmdc_number', 'phone', 'email', 'photo', 'bmdc_certificate'],
        2 => ['primary_degree', 'specialized_degree', 'fellowship', 'experience_years', 'current_position', 'timeline', 'degree_certificates'],
        3 => ['doctor_types', 'cancer_types', 'chambers', 'extra_services'],
        4 => ['declarations', 'preferred_call_time', 'preferred_call_day'],
    ];

    public function create(): View
    {
        return view('pages.for-doctors', [
            'doctorTypes' => DoctorType::orderBy('label_bn')->get(),
            'cancerTypes' => CancerType::orderBy('sort_order')->get(),
            'submitted' => (bool) session('application_submitted'),
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

    public function store(StoreDoctorApplicationRequest $request, DoctorApplicationService $service): RedirectResponse
    {
        $service->submit($request->validated(), [
            'photo' => $request->file('photo'),
            'bmdc_certificate' => $request->file('bmdc_certificate'),
            'degree_certificates' => $request->file('degree_certificates'),
        ]);

        return redirect()
            ->route('doctors.apply')
            ->with('application_submitted', true);
    }
}
