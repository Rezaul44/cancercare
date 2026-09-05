<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDoctorProfileRequest;
use App\Models\Chamber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $doctor = Auth::user()->doctor->load('chambers.district');

        return view('doctor.profile.edit', ['doctor' => $doctor]);
    }

    public function update(UpdateDoctorProfileRequest $request): RedirectResponse
    {
        $doctor = Auth::user()->doctor;
        $validated = $request->validated();

        DB::transaction(function () use ($doctor, $validated) {
            $doctor->update([
                'second_opinion_fee' => $validated['second_opinion_fee'] ?? null,
                'whatsapp_fee' => $validated['whatsapp_fee'] ?? null,
                'whatsapp_response_hours' => $validated['whatsapp_response_hours'] ?? null,
            ]);

            foreach ($validated['chambers'] ?? [] as $chamberId => $chamberData) {
                Chamber::where('id', $chamberId)
                    ->where('doctor_id', $doctor->id) // নিজের নয় এমন চেম্বার সম্পাদনা ঠেকাতে
                    ->update([
                        'fee' => $chamberData['fee'] ?? null,
                        'days_bn' => $chamberData['days_bn'],
                        'time_from' => $chamberData['time_from'] ?? null,
                        'time_to' => $chamberData['time_to'] ?? null,
                    ]);
            }
        });

        return back()->with('status', 'প্রোফাইল হালনাগাদ হয়েছে।');
    }

    public function approve(): RedirectResponse
    {
        Auth::user()->doctor->update(['doctor_approved_at' => now()]);

        return back()->with('status', 'আপনার প্রোফাইল অনুমোদিত হলো।');
    }
}
