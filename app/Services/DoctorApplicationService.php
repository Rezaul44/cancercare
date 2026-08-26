<?php

namespace App\Services;

use App\Enums\DoctorApplicationStatus;
use App\Enums\DoctorStatus;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\DoctorApplication;
use App\Models\DoctorTimeline;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoctorApplicationService
{
    private const DISK = 's3_private';

    /**
     * @param  array<string, mixed>  $data  FormRequest::validated()
     * @param  array{photo: UploadedFile, bmdc_certificate: UploadedFile, degree_certificates: list<UploadedFile>}  $files
     */
    public function submit(array $data, array $files): DoctorApplication
    {
        $directory = 'doctor-applications/'.Str::uuid();

        $photoPath = $files['photo']->store($directory, self::DISK);
        $bmdcCertificatePath = $files['bmdc_certificate']->store($directory.'/bmdc', self::DISK);
        $degreeCertificatePaths = collect($files['degree_certificates'])
            ->map(fn (UploadedFile $file) => $file->store($directory.'/degrees', self::DISK))
            ->values()
            ->all();

        return DoctorApplication::create([
            'full_name' => $data['full_name'],
            'bmdc_number' => $data['bmdc_number'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'photo_path' => $photoPath,
            'bmdc_certificate_path' => $bmdcCertificatePath,
            'experience_years' => $data['experience_years'],
            'current_position' => $data['current_position'],
            'degrees' => [
                'primary' => $data['primary_degree'],
                'specialized' => $data['specialized_degree'],
                'fellowship' => $data['fellowship'] ?? null,
                'certificate_paths' => $degreeCertificatePaths,
            ],
            'timeline' => array_values($data['timeline']),
            'doctor_type_ids' => array_values($data['doctor_types']),
            'cancer_type_ids' => array_values($data['cancer_types']),
            'chambers' => array_values($data['chambers']),
            'extra_services' => [
                'whatsapp' => in_array('whatsapp', $data['extra_services'] ?? [], true),
                'second_opinion' => in_array('second_opinion', $data['extra_services'] ?? [], true),
                'telemedicine' => in_array('telemedicine', $data['extra_services'] ?? [], true),
            ],
            'preferred_call_time' => $data['preferred_call_time'],
            'preferred_call_day' => $data['preferred_call_day'],
            'declarations' => array_merge(
                array_map(fn ($accepted) => (bool) $accepted, $data['declarations']),
                ['agreed_at' => now()->toDateTimeString()],
            ),
            'status' => DoctorApplicationStatus::Submitted->value,
        ]);
    }

    /**
     * BMDC ও ডিগ্রি সনদ যাচাইয়ের checklist সংরক্ষণ করে, status "under_review"-এ নেয়।
     *
     * @param  array{bmdc_verified: bool, degree_verified: bool, note: ?string}  $checklist
     */
    public function markVerified(DoctorApplication $application, array $checklist, User $reviewer): DoctorApplication
    {
        $application->update([
            'verification_checklist' => [
                'bmdc_verified' => (bool) $checklist['bmdc_verified'],
                'degree_verified' => (bool) $checklist['degree_verified'],
                'note' => $checklist['note'] ?? null,
                'verified_by' => $reviewer->id,
                'verified_at' => now()->toDateTimeString(),
            ],
            'status' => DoctorApplicationStatus::UnderReview->value,
        ]);

        activity()
            ->causedBy($reviewer)
            ->performedOn($application)
            ->withProperties(['checklist' => $application->verification_checklist])
            ->log('doctor_application.verified');

        return $application->refresh();
    }

    /**
     * আবেদন অনুমোদন করে doctors টেবিলে রেকর্ড তৈরি করে — timeline ও chamber সহ।
     *
     * @param  array{
     *     gender: string,
     *     experience_years: int,
     *     current_position: string,
     *     chambers: list<array{name_bn: string, address_bn: string, district_id: int, type: string, fee: ?int, days_bn: string, time_from: string, time_to: string}>,
     * }  $doctorData
     */
    public function approve(DoctorApplication $application, array $doctorData, User $reviewer): Doctor
    {
        return DB::transaction(function () use ($application, $doctorData, $reviewer) {
            $doctor = Doctor::create([
                'application_id' => $application->id,
                'name_bn' => $application->full_name,
                'name_en' => $application->full_name,
                'bmdc_number' => $application->bmdc_number,
                'bmdc_verified_at' => now(),
                'bmdc_verified_by' => $reviewer->id,
                'photo_path' => $application->photo_path,
                'degrees_line_bn' => $this->degreesLine($application),
                'experience_years' => $doctorData['experience_years'],
                'current_position_bn' => $doctorData['current_position'],
                'gender' => $doctorData['gender'],
                'status' => DoctorStatus::PendingApproval,
                'doctor_approved_at' => now(),
            ]);

            foreach ($application->timeline as $index => $step) {
                DoctorTimeline::create([
                    'doctor_id' => $doctor->id,
                    'year_label' => $step['year_label'],
                    'title_bn' => $step['title_bn'],
                    'institution_bn' => $step['institution_bn'],
                    'sort_order' => $index,
                ]);
            }

            foreach ($doctorData['chambers'] as $index => $chamber) {
                Chamber::create([
                    'doctor_id' => $doctor->id,
                    'name_bn' => $chamber['name_bn'],
                    'address_bn' => $chamber['address_bn'],
                    'district_id' => $chamber['district_id'],
                    'type' => $chamber['type'],
                    'fee' => $chamber['fee'] ?? 0,
                    'days_bn' => $chamber['days_bn'],
                    'time_from' => $chamber['time_from'],
                    'time_to' => $chamber['time_to'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }

            if (! empty($application->doctor_type_ids)) {
                $doctor->doctorTypes()->sync($application->doctor_type_ids);
            }

            if (! empty($application->cancer_type_ids)) {
                $doctor->cancerTypes()->sync(
                    collect($application->cancer_type_ids)
                        ->mapWithKeys(fn ($id, $index) => [$id => ['is_primary' => $index === 0]])
                        ->all()
                );
            }

            $application->update([
                'status' => DoctorApplicationStatus::Approved->value,
                'doctor_id' => $doctor->id,
                'reviewed_by' => $reviewer->id,
            ]);

            activity()
                ->causedBy($reviewer)
                ->performedOn($application)
                ->withProperties(['doctor_id' => $doctor->id])
                ->log('doctor_application.approved');

            return $doctor;
        });
    }

    public function reject(DoctorApplication $application, string $reason, User $reviewer): DoctorApplication
    {
        $application->update([
            'status' => DoctorApplicationStatus::Rejected->value,
            'review_note' => $reason,
            'reviewed_by' => $reviewer->id,
        ]);

        activity()
            ->causedBy($reviewer)
            ->performedOn($application)
            ->withProperties(['reason' => $reason])
            ->log('doctor_application.rejected');

        return $application->refresh();
    }

    private function degreesLine(DoctorApplication $application): string
    {
        return collect([
            $application->degrees['primary'] ?? null,
            $application->degrees['specialized'] ?? null,
            $application->degrees['fellowship'] ?? null,
        ])->filter()->implode(', ');
    }
}
