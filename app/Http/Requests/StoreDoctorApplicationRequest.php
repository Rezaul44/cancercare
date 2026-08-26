<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * docs/prototypes/onboarding.html-এর ৪ ধাপ অনুযায়ী পুরো doctor application ফর্মের ভ্যালিডেশন।
 */
class StoreDoctorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ধাপ ১ — পরিচয় ও যোগাযোগ
            'full_name' => ['required', 'string', 'max:150'],
            'bmdc_number' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^01[0-9]{9}$/'],
            'email' => ['required', 'email', 'max:191'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'bmdc_certificate' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // ধাপ ২ — যোগ্যতা ও পেশাগত জীবন
            'primary_degree' => ['required', 'string', 'max:200'],
            'specialized_degree' => ['required', 'string', 'max:200'],
            'fellowship' => ['nullable', 'string', 'max:200'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'current_position' => ['required', 'string', 'max:200'],
            'timeline' => ['required', 'array', 'min:1'],
            'timeline.*.year_label' => ['required', 'string', 'max:40'],
            'timeline.*.title_bn' => ['required', 'string', 'max:200'],
            'timeline.*.institution_bn' => ['required', 'string', 'max:200'],
            'degree_certificates' => ['required', 'array', 'min:1'],
            'degree_certificates.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // ধাপ ৩ — বিশেষত্ব ও চেম্বার
            'doctor_types' => ['required', 'array', 'min:1'],
            'doctor_types.*' => [Rule::exists('doctor_types', 'id')],
            'cancer_types' => ['required', 'array', 'min:1'],
            'cancer_types.*' => [Rule::exists('cancer_types', 'id')],
            'chambers' => ['required', 'array', 'min:1'],
            'chambers.*.name_bn' => ['required', 'string', 'max:160'],
            'chambers.*.address_bn' => ['required', 'string', 'max:255'],
            'chambers.*.fee' => ['nullable', 'integer', 'min:0'],
            'chambers.*.type' => ['required', Rule::in(['govt', 'private', 'npo'])],
            'chambers.*.days_bn' => ['required', 'string', 'max:120'],
            'extra_services' => ['nullable', 'array'],
            'extra_services.*' => [Rule::in(['whatsapp', 'second_opinion', 'telemedicine'])],

            // ধাপ ৪ — ঘোষণা ও সম্মতি
            'declarations' => ['required', 'array'],
            'declarations.information_accurate' => ['required', 'accepted'],
            'declarations.bmdc_valid' => ['required', 'accepted'],
            'declarations.no_payment_for_ranking' => ['required', 'accepted'],
            'declarations.will_notify_changes' => ['required', 'accepted'],
            'declarations.consent_patient_feedback' => ['required', 'accepted'],
            'preferred_call_time' => ['required', 'string', 'max:40'],
            'preferred_call_day' => ['required', 'string', 'max:40'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'পূর্ণ নাম',
            'bmdc_number' => 'BMDC নিবন্ধন নম্বর',
            'phone' => 'মোবাইল নম্বর',
            'email' => 'ইমেইল',
            'photo' => 'প্রোফাইল ছবি',
            'bmdc_certificate' => 'BMDC সনদ',
            'primary_degree' => 'মূল ডিগ্রি',
            'specialized_degree' => 'বিশেষায়িত ডিগ্রি',
            'experience_years' => 'মোট অভিজ্ঞতা',
            'current_position' => 'বর্তমান প্রধান পদ',
            'timeline' => 'পেশাগত জীবনের ধাপ',
            'degree_certificates' => 'ডিগ্রির সনদ',
            'doctor_types' => 'অনকোলজিস্টের ধরন',
            'cancer_types' => 'যেসব ক্যান্সারে কাজ করেন',
            'chambers' => 'চেম্বারের তথ্য',
            'preferred_call_time' => 'পছন্দের সময়',
            'preferred_call_day' => 'পছন্দের দিন',
        ];
    }
}
