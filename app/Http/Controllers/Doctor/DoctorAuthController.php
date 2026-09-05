<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class DoctorAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('doctor.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'ইমেইল বা পাসওয়ার্ড সঠিক নয়।',
            ]);
        }

        if (! Auth::user()->hasRole('doctor')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'এই অ্যাকাউন্টের ডাক্তার পোর্টালে প্রবেশাধিকার নেই।',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('doctor.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login');
    }

    public function showForgotPassword(): View
    {
        return view('doctor.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'পাসওয়ার্ড সেট করার লিংক আপনার ইমেইলে পাঠানো হয়েছে (থাকলে)।');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('doctor.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($validated, function ($user, $password) {
            $user->forceFill(['password' => bcrypt($password)])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return redirect()->route('doctor.login')->with('status', 'পাসওয়ার্ড সেট হয়েছে — এখন লগইন করুন।');
    }
}
