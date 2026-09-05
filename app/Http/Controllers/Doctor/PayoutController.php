<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index(): View
    {
        $payouts = Auth::user()->doctor
            ->payouts()
            ->orderByDesc('period_start')
            ->paginate(15);

        return view('doctor.payouts.index', ['payouts' => $payouts]);
    }
}
