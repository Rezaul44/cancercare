<?php

namespace App\Services;

use App\Enums\SecondOpinionStatus;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\SecondOpinionFile;
use App\Models\SecondOpinionRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecondOpinionRequestService
{
    private const DEFAULT_EXPECTED_HOURS = 72;

    public function __construct(private readonly FileVaultService $fileVault) {}

    /**
     * @param  array<string, mixed>  $data  FormRequest::validated() (গেটওয়ে ছাড়া — সেটা আলাদাভাবে পাঠানো হয়)
     * @param  list<UploadedFile>  $reports
     */
    public function submit(array $data, array $reports): SecondOpinionRequest
    {
        return DB::transaction(function () use ($data, $reports) {
            $doctor = Doctor::findOrFail($data['doctor_id']);

            $request = SecondOpinionRequest::create([
                'patient_name' => $data['patient_name'],
                'age' => $data['age'],
                'cancer_type_id' => $data['cancer_type_id'],
                'current_status' => $data['current_status'],
                'treatments_done_bn' => $data['treatments_done_bn'] ?? null,
                'question_bn' => $data['question_bn'],
                'phone' => $data['phone'],
                'district_id' => $data['district_id'],
                'doctor_id' => $doctor->id,
                'fee' => $doctor->second_opinion_fee ?? 0,
                'status' => SecondOpinionStatus::PendingPayment,
                'expected_hours' => (int) (Setting::group('second_opinion')['expected_hours'] ?? self::DEFAULT_EXPECTED_HOURS),
            ]);

            $directory = 'second-opinion-requests/'.Str::uuid();

            foreach ($reports as $file) {
                $path = $this->fileVault->store($file, $directory);

                SecondOpinionFile::create([
                    'request_id' => $request->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_at' => now(),
                ]);
            }

            return $request;
        });
    }
}
