<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FieldAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('field.auth.login');
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

        if (! Auth::user()->hasRole('field_agent')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'এই অ্যাকাউন্টের মাঠকর্মী পোর্টালে প্রবেশাধিকার নেই।',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('field.rating.create'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('field.login');
    }
}
