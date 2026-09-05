<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorRatingSubmission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * মাঠকর্মীর মোবাইল ফর্ম থেকে জমা হওয়া রেটিং সংরক্ষণ করে।
 */
class RatingSubmissionService
{
    private const DISK_DIRECTORY = 'rating-proofs';

    public function __construct(private readonly FileVaultService $fileVault) {}

    /**
     * রোগীর ফোন নম্বর কখনো প্লেইন সংরক্ষণ হয় না — শুধু ডুপ্লিকেট ঠেকাতে HMAC হ্যাশ রাখা হয়।
     * APP_KEY দিয়ে কী করা হয়েছে যাতে এই হ্যাশ ব্রুট-ফোর্স করে ফোন নম্বর ফিরে পাওয়া না যায়
     * (প্লেইন sha256 হলে বাংলাদেশি ফোন নম্বরের ছোট keyspace-এর কারণে সহজেই উল্টানো যেত)।
     */
    public function hashPhone(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.key'));
    }

    /**
     * @param  array{phone: string, answers: array<string, bool>, free_comment_bn: ?string}  $data
     *
     * @throws \DomainException এই ডাক্তারের জন্য একই রোগীর ফোন নম্বর দিয়ে আগেই জমা হয়ে থাকলে
     */
    public function submit(array $data, Doctor $doctor, User $collector, ?UploadedFile $photo): DoctorRatingSubmission
    {
        $phoneHash = $this->hashPhone($data['phone']);

        $alreadySubmitted = DoctorRatingSubmission::where('doctor_id', $doctor->id)
            ->where('patient_phone_hash', $phoneHash)
            ->exists();

        if ($alreadySubmitted) {
            throw new \DomainException('এই রোগীর জন্য এই ডাক্তারের রেটিং ইতিমধ্যে জমা হয়েছে।');
        }

        $proofPath = $photo ? $this->fileVault->store($photo, self::DISK_DIRECTORY.'/'.Str::uuid()) : null;

        return DoctorRatingSubmission::create([
            'doctor_id' => $doctor->id,
            'collected_by' => $collector->id,
            'source' => 'field_hospital',
            'patient_phone_hash' => $phoneHash,
            'proof_type' => $proofPath ? 'prescription' : 'none',
            'proof_path' => $proofPath,
            'answers' => $data['answers'],
            'free_comment_bn' => $data['free_comment_bn'] ?? null,
            'is_verified' => false,
            'collected_at' => now()->toDateString(),
        ]);
    }
}
