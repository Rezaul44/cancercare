<?php

use App\Http\Controllers\CostEstimatorController;
use App\Http\Controllers\DoctorApplicationController;
use App\Http\Controllers\DoctorDirectoryController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorAuthController;
use App\Http\Controllers\Doctor\PayoutController as DoctorPayoutController;
use App\Http\Controllers\Doctor\ProfileController as DoctorPortalProfileController;
use App\Http\Controllers\Doctor\SecondOpinionController as DoctorSecondOpinionController;
use App\Http\Controllers\Field\FieldAuthController;
use App\Http\Controllers\Field\RatingSubmissionController;
use App\Http\Controllers\HospitalDirectoryController;
use App\Http\Controllers\HospitalProfileController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SecondOpinionController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('filament:optimize');
    Artisan::call('optimize');

    return response()->json([
        'status' => 'success',
        'message' => 'All caches cleared and re-optimized successfully!',
        'output' => trim(Artisan::output()),
    ]);
})->name('cache.clear');

Route::get('/clear-all-cache', function () {
    return redirect()->route('cache.clear');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/for-doctors', [DoctorApplicationController::class, 'create'])->name('doctors.apply');
Route::post('/for-doctors', [DoctorApplicationController::class, 'store'])->name('doctors.apply.store');

Route::get('/doctors', [DoctorDirectoryController::class, 'index'])->name('doctors.index');
Route::get('/doctors/{doctor:slug}', [DoctorProfileController::class, 'show'])->name('doctors.show');

Route::get('/hospitals', [HospitalDirectoryController::class, 'index'])->name('hospitals.index');
Route::get('/hospitals/{hospital:slug}', [HospitalProfileController::class, 'show'])->name('hospitals.show');

Route::get('/cost-estimator', [CostEstimatorController::class, 'index'])->name('cost-estimator.index');

Route::get('/patients', [\App\Http\Controllers\PatientCaseController::class, 'index'])->name('patients.index');
Route::get('/patients/{case_code}', [\App\Http\Controllers\PatientCaseController::class, 'show'])->name('patients.show');
Route::get('/apply-for-support', [\App\Http\Controllers\PatientCaseController::class, 'apply'])->name('patients.apply');

Route::get('/guide', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guide/{cancerType:slug}', [GuideController::class, 'show'])->name('guides.show');

Route::get('/second-opinion', [SecondOpinionController::class, 'create'])->name('second-opinion.request');
Route::post('/second-opinion', [SecondOpinionController::class, 'store'])->name('second-opinion.request.store');

Route::prefix('second-opinion/payments')->name('payments.')->group(function () {
    // SSLCommerz auto-submit করা POST ফর্ম দিয়ে ফেরত পাঠায়, bKash/Nagad GET redirect দিয়ে — তাই দুটোই রাখা হলো।
    Route::match(['get', 'post'], '/{payment}/success', [PaymentCallbackController::class, 'success'])->name('success');
    Route::match(['get', 'post'], '/{payment}/fail', [PaymentCallbackController::class, 'fail'])->name('fail');
    Route::match(['get', 'post'], '/{payment}/cancel', [PaymentCallbackController::class, 'cancel'])->name('cancel');
    Route::post('/{gateway}/webhook', [PaymentCallbackController::class, 'webhook'])->name('webhook');
});

Route::get('/search', [SearchController::class, 'index'])->name('search.index');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('/doctors/{doctor:slug}/match', [DoctorProfileController::class, 'match'])->name('doctors.match');
    Route::post('/cost-estimate', [CostEstimatorController::class, 'calculate'])->name('cost.estimate');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
});

Route::get('/private-document/{path}', function (\Illuminate\Http\Request $request, string $path) {
    abort_unless($request->hasValidRelativeSignature() || $request->hasValidSignature(), 403);
    abort_unless(\Illuminate\Support\Facades\Storage::disk('s3_private')->exists($path), 404);

    return \Illuminate\Support\Facades\Storage::disk('s3_private')->response($path);
})->where('path', '.*')->name('storage.s3_private');

// ডাক্তার পোর্টাল (Phase 6.4) — একই 'web' guard, Filament /admin থেকে সম্পূর্ণ আলাদা।
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [DoctorAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [DoctorAuthController::class, 'login'])->name('login.store');
        Route::get('/forgot-password', [DoctorAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [DoctorAuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [DoctorAuthController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [DoctorAuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::post('/logout', [DoctorAuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'role:doctor', 'doctor.profile'])->group(function () {
        Route::get('/', [DoctorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/second-opinions', [DoctorSecondOpinionController::class, 'index'])->name('second-opinions.index');
        Route::get('/second-opinions/{secondOpinionRequest}', [DoctorSecondOpinionController::class, 'show'])->name('second-opinions.show');
        Route::post('/second-opinions/{secondOpinionRequest}/respond', [DoctorSecondOpinionController::class, 'respond'])->name('second-opinions.respond');

        Route::get('/profile', [DoctorPortalProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [DoctorPortalProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/approve', [DoctorPortalProfileController::class, 'approve'])->name('profile.approve');

        Route::get('/payouts', [DoctorPayoutController::class, 'index'])->name('payouts.index');
    });
});

// মাঠকর্মী পোর্টাল (Phase 7.1) — একই 'web' guard, ডাক্তার পোর্টাল থেকে সম্পূর্ণ আলাদা লগইন
// (মাঠকর্মীর কোনো Doctor রেকর্ড লিঙ্ক থাকে না)।
Route::prefix('field')->name('field.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [FieldAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [FieldAuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [FieldAuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'role:field_agent'])->group(function () {
        Route::get('/rating/new', [RatingSubmissionController::class, 'create'])->name('rating.create');
        Route::post('/rating', [RatingSubmissionController::class, 'store'])->name('rating.store');
        Route::get('/ajax/doctors/search', [RatingSubmissionController::class, 'searchDoctors'])->name('rating.doctors.search');
    });
});