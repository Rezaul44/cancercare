<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * role:doctor থাকলেও কখনো কখনো সংশ্লিষ্ট Doctor রেকর্ড লিঙ্ক না থাকতে পারে
 * (যেমন ডেমো ইউজার) — এই মিডলওয়্যার সেই এজ-কেস আটকে বার্তা দেখায়।
 */
class EnsureDoctorProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()?->doctor) {
            Auth::logout();

            return redirect()->route('doctor.login')
                ->withErrors(['email' => 'আপনার অ্যাকাউন্টের সাথে কোনো ডাক্তার প্রোফাইল যুক্ত নেই। CCB-এর সাথে যোগাযোগ করুন।']);
        }

        return $next($request);
    }
}
