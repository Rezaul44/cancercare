{{--
    docs/prototypes/doctor_directory.html-এর .side ফিল্টার — আসল <input type="checkbox"> ব্যবহার
    করা হয়েছে (prototype-এর fake div/onclick নয়), যাতে JS ছাড়াও ফর্ম সাবমিট করে ফিল্টার করা যায়।
--}}
<div class="side sticky top-[90px] bg-white border border-line rounded-2xl p-[22px]">
    <div class="flex items-center justify-between mb-4">
        <span class="font-bn text-[13px] font-semibold text-ink">ফিল্টার</span>
        <a href="{{ route('doctors.index') }}" @click.prevent="clearFilters()" class="font-bn text-[12.5px] text-slate-500 hover:text-ink underline">সব মুছুন</a>
    </div>

    <div class="mb-6">
        <div class="font-bn text-[13px] font-semibold text-ink mb-2.5">ডাক্তারের ধরন</div>
        @foreach ($doctorTypes as $doctorType)
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13.5px] text-slate-700 font-bn">
                <input type="checkbox" name="doctor_type[]" value="{{ $doctorType->key }}"
                    class="w-[17px] h-[17px] rounded accent-slate-900"
                    @checked(in_array($doctorType->key, $filters['doctor_type_keys'], true))>
                {{ $doctorType->label_bn }}
            </label>
        @endforeach
    </div>

    <div class="mb-6">
        <div class="font-bn text-[13px] font-semibold text-ink mb-2.5">হাসপাতালের ধরন</div>
        @foreach (['govt' => 'সরকারি', 'private' => 'বেসরকারি', 'npo' => 'অলাভজনক'] as $value => $label)
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13.5px] text-slate-700 font-bn">
                <input type="checkbox" name="hospital_type[]" value="{{ $value }}"
                    class="w-[17px] h-[17px] rounded accent-slate-900"
                    @checked(in_array($value, $filters['chamber_types'], true))>
                {{ $label }}
            </label>
        @endforeach
    </div>

    <div class="mb-6">
        <div class="font-bn text-[13px] font-semibold text-ink mb-2.5">ভিজিট ফি</div>
        @foreach (['upto_500' => '৳৫০০ পর্যন্ত', '500_1000' => '৳৫০০ – ১,০০০', '1000_plus' => '৳১,০০০+'] as $value => $label)
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13.5px] text-slate-700 font-bn">
                <input type="checkbox" name="fee[]" value="{{ $value }}"
                    class="w-[17px] h-[17px] rounded accent-slate-900"
                    @checked(in_array($value, $filters['fee_buckets'], true))>
                {{ $label }}
            </label>
        @endforeach
    </div>

    <div class="mb-6">
        <div class="font-bn text-[13px] font-semibold text-ink mb-2.5">ডাক্তারের লিঙ্গ</div>
        @foreach (['female' => 'নারী ডাক্তার', 'male' => 'পুরুষ ডাক্তার'] as $value => $label)
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13.5px] text-slate-700 font-bn">
                <input type="checkbox" name="gender[]" value="{{ $value }}"
                    class="w-[17px] h-[17px] rounded accent-slate-900"
                    @checked(in_array($value, $filters['gender'], true))>
                {{ $label }}
            </label>
        @endforeach
    </div>

    <div>
        <div class="font-bn text-[13px] font-semibold text-ink mb-2.5">সুবিধা</div>
        @foreach (['whatsapp' => 'WhatsApp পরামর্শ', 'second_opinion' => 'দ্বিতীয় মতামত দেন'] as $value => $label)
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13.5px] text-slate-700 font-bn">
                <input type="checkbox" name="facility[]" value="{{ $value }}"
                    class="w-[17px] h-[17px] rounded accent-slate-900"
                    @checked(in_array($value, $filters['facilities'], true))>
                {{ $label }}
            </label>
        @endforeach
    </div>

    <noscript>
        <button type="submit" class="font-bn w-full mt-5 py-2.5 rounded-[9px] font-medium bg-slate-900 text-white">ফিল্টার প্রয়োগ করুন</button>
    </noscript>
</div>
