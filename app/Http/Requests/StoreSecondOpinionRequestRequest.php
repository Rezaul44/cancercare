<?php

namespace App\Http\Requests;

use App\Enums\PaymentGateway;
use App\Enums\SecondOpinionCurrentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * /second-opinion-এর ৪ ধাপ একসাথে ভ্যালিডেট করে — docs/prototypes/onboarding.html-এর
 * ফর্ম-ডিজাইন অনুসরণ করে বানানো, কিন্তু নিজস্ব ফিল্ড সেট (স্পেসিফিকেশন অনুযায়ী)।
 */
class StoreSecondOpinionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ধাপ ১ — রিপোর্ট আপলোড
            'reports' => ['required', 'array', 'min:1', 'max:10'],
            'reports.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // ধাপ ২ — রোগীর পরিস্থিতি ও প্রশ্ন
            'patient_name' => ['required', 'string', 'max:150'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'cancer_type_id' => [Rule::exists('cancer_types', 'id')],
            'current_status' => ['required', Rule::in(array_column(SecondOpinionCurrentStatus::cases(), 'value'))],
            'treatments_done_bn' => ['nullable', 'string', 'max:2000'],
            'question_bn' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^01[0-9]{9}$/'],
            'district_id' => [Rule::exists('districts', 'id')],

            // ধাপ ৩ — ডাক্তার বাছাই
            'doctor_id' => [
                'required',
                Rule::exists('doctors', 'id')->where('offers_second_opinion', true),
            ],

            // ধাপ ৪ — সারাংশ + পেমেন্ট
            'gateway' => ['required', Rule::in(array_column(PaymentGateway::cases(), 'value'))],
        ];
    }

    public function attributes(): array
    {
        return [
            'reports' => 'মেডিকেল রিপোর্ট',
            'patient_name' => 'রোগীর নাম',
            'age' => 'বয়স',
            'cancer_type_id' => 'ক্যান্সারের ধরন',
            'current_status' => 'চিকিৎসার বর্তমান অবস্থা',
            'treatments_done_bn' => 'এ পর্যন্ত যা চিকিৎসা হয়েছে',
            'question_bn' => 'আপনার প্রশ্ন',
            'phone' => 'মোবাইল নম্বর',
            'district_id' => 'জেলা',
            'doctor_id' => 'ডাক্তার',
            'gateway' => 'পেমেন্ট পদ্ধতি',
        ];
    }
}
