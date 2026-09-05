<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ডাক্তার পোর্টাল থেকে শুধু ফি ও সময়সূচি সম্পাদনাযোগ্য — doctor_services, philosophy_points,
 * patients_treated ইত্যাদি CCB-লিখিত ফিল্ড ইচ্ছাকৃতভাবে এখানে নেই (docs/CCB_prompt_playbook.md 6.4)।
 */
class UpdateDoctorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'second_opinion_fee' => ['nullable', 'integer', 'min:0'],
            'whatsapp_fee' => ['nullable', 'integer', 'min:0'],
            'whatsapp_response_hours' => ['nullable', 'string', 'max:40'],
            'chambers' => ['nullable', 'array'],
            'chambers.*.fee' => ['nullable', 'integer', 'min:0'],
            'chambers.*.days_bn' => ['required', 'string', 'max:120'],
            'chambers.*.time_from' => ['nullable', 'date_format:H:i'],
            'chambers.*.time_to' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function attributes(): array
    {
        return [
            'second_opinion_fee' => 'দ্বিতীয় মতামতের ফি',
            'whatsapp_fee' => 'WhatsApp পরামর্শের ফি',
            'whatsapp_response_hours' => 'WhatsApp উত্তরের সময়',
        ];
    }
}
