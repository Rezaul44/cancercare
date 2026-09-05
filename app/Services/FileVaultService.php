<?php

namespace App\Services;

use App\Models\SecondOpinionFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * BMDC সনদ, NID, বায়োপসি রিপোর্ট, দ্বিতীয় মতামতের ফাইল ইত্যাদি প্রাইভেট
 * ফাইলের জন্য একক জায়গা — private ডিস্কে আপলোড ও সাময়িক (signed) URL তৈরি করে।
 */
class FileVaultService
{
    private const DISK = 's3_private';

    private const DEFAULT_EXPIRY_MINUTES = 15;

    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    public function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk(self::DISK)->delete($path);
    }

    /**
     * প্রাইভেট ফাইলের জন্য সাময়িক signed URL — ডিফল্ট মেয়াদ ১৫ মিনিট।
     */
    public function temporaryUrl(string $path, int $minutes = self::DEFAULT_EXPIRY_MINUTES): string
    {
        return URL::temporarySignedRoute(
            'storage.'.self::DISK,
            now()->addMinutes($minutes),
            ['path' => $path],
            absolute: false,
        );
    }

    /**
     * দ্বিতীয় মতামতের ফাইল শুধু অনুরোধে নির্বাচিত ডাক্তার ও super_admin দেখতে পারবেন।
     */
    public function canAccessSecondOpinionFile(User $user, SecondOpinionFile $file): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $request = $file->relationLoaded('request') ? $file->request : $file->request()->first();
        $selectedDoctorUserId = $request?->doctor?->user_id;

        return $selectedDoctorUserId !== null && $selectedDoctorUserId === $user->id;
    }

    /**
     * অ্যাক্সেস চেক পাস হলেই signed URL দেয়, নাহলে 403।
     */
    public function secondOpinionFileUrl(User $user, SecondOpinionFile $file, int $minutes = self::DEFAULT_EXPIRY_MINUTES): string
    {
        abort_unless($this->canAccessSecondOpinionFile($user, $file), 403);

        return $this->temporaryUrl($file->file_path, $minutes);
    }
}
