<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function getTitle(): string|Htmlable
    {
        return 'Cancer Care — সাইন ইন';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Cancer Care';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'ক্যান্সার চিকিৎসায় সঠিক সিদ্ধান্তের প্ল্যাটফর্ম';
    }
}
