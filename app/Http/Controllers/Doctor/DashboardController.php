<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\SecondOpinionStatus;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $doctor = Auth::user()->doctor;

        $pendingCount = $doctor->secondOpinionRequests()
            ->whereIn('status', [SecondOpinionStatus::Submitted, SecondOpinionStatus::Accepted])
            ->count();

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'pendingCount' => $pendingCount,
        ]);
    }
}
