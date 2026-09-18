<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Models\PatientCase;
use App\Observers\DoctorObserver;
use App\Observers\PatientCaseObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Doctor::observe(DoctorObserver::class);
        PatientCase::observe(PatientCaseObserver::class);

        // অ্যাপে কোনো routes/auth.php নেই — 'password.reset' নামের route নেই, তাই
        // পাসওয়ার্ড-রিসেট নোটিফিকেশনের URL ডাক্তার পোর্টালের নিজস্ব route-এ পাঠানো হচ্ছে।
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url(route('doctor.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        \Illuminate\Support\Facades\Blade::directive('bnNum', function ($expression) {
            return "<?php echo \\App\\Support\\BanglaHelper::bnNumber($expression); ?>";
        });
    }
}
