<?php

use App\Http\Controllers\DoctorApplicationController;
use App\Http\Controllers\DoctorDirectoryController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/for-doctors', [DoctorApplicationController::class, 'create'])->name('doctors.apply');
Route::post('/for-doctors', [DoctorApplicationController::class, 'store'])->name('doctors.apply.store');

Route::get('/doctors', [DoctorDirectoryController::class, 'index'])->name('doctors.index');
Route::get('/doctors/{doctor:slug}', [DoctorProfileController::class, 'show'])->name('doctors.show');

Route::get('/guide', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guide/{cancerType:slug}', [GuideController::class, 'show'])->name('guides.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('/doctors/{doctor:slug}/match', [DoctorProfileController::class, 'match'])->name('doctors.match');
});

Route::get('/private-document', function (\Illuminate\Http\Request $request) {
    $path = (string) $request->query('path', '');
    abort_unless(\Illuminate\Support\Facades\Storage::disk('s3_private')->exists($path), 404);

    return \Illuminate\Support\Facades\Storage::disk('s3_private')->response($path);
})->middleware('signed')->name('storage.s3_private');