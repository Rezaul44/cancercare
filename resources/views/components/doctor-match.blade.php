{{--
    docs/prototypes/doctor_profile.html-এর .match ("এই ডাক্তার কি আমার জন্য") — ৩ dropdown ম্যাচ ইঞ্জিন
    (docs/CCB_system_documentation.md ধারা ৩)। রেন্ডারিং লজিক resources/js/doctor-match.js-এ, ডেটা
    আসে GET /ajax/doctors/{slug}/match থেকে (DoctorProfileController::match)। ব্যবহার:
    <x-doctor-match :doctor="$doctor" :cancer-options="$matchCancerOptions" />
--}}
@props(['doctor', 'cancerOptions'])

<div x-data="doctorMatch('{{ $doctor->slug }}')" class="border-2 border-pink-600 rounded-2xl overflow-hidden">
    <div class="bg-pink-50 p-5">
        <div class="font-bn text-[15px] font-semibold text-pink-800 mb-1">আপনার পরিস্থিতি বলুন</div>
        <div class="font-bn text-[13px] text-pink-700 leading-[1.6] mb-[15px]">তিনটি তথ্য দিলে বুঝতে পারবেন এই ডাক্তার আপনার ক্ষেত্রে উপযুক্ত কিনা।</div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="font-bn block text-[10.5px] font-semibold text-pink-700 uppercase tracking-wide mb-1.5">ক্যান্সারের ধরন</label>
                <select x-ref="cancer" @change="onChange()"
                    class="font-bn w-full px-[13px] py-[11px] border-[1.5px] border-pink-200 rounded-[10px] text-[13.5px] text-ink bg-white cursor-pointer focus:outline-none focus:border-pink-600">
                    <option value="">বেছে নিন</option>
                    @foreach ($cancerOptions as $option)
                        <option value="{{ $option->slug }}">{{ $option->name_bn }}</option>
                    @endforeach
                    <option value="other">অন্যান্য</option>
                </select>
            </div>
            <div>
                <label class="font-bn block text-[10.5px] font-semibold text-pink-700 uppercase tracking-wide mb-1.5">স্টেজ</label>
                <select x-ref="stage" @change="onChange()"
                    class="font-bn w-full px-[13px] py-[11px] border-[1.5px] border-pink-200 rounded-[10px] text-[13.5px] text-ink bg-white cursor-pointer focus:outline-none focus:border-pink-600">
                    <option value="">বেছে নিন</option>
                    <option value="1">স্টেজ ১</option>
                    <option value="2">স্টেজ ২</option>
                    <option value="3">স্টেজ ৩</option>
                    <option value="4">স্টেজ ৪</option>
                    <option value="unknown">জানি না</option>
                </select>
            </div>
            <div>
                <label class="font-bn block text-[10.5px] font-semibold text-pink-700 uppercase tracking-wide mb-1.5">যে চিকিৎসা লাগবে</label>
                <select x-ref="treatment" @change="onChange()"
                    class="font-bn w-full px-[13px] py-[11px] border-[1.5px] border-pink-200 rounded-[10px] text-[13.5px] text-ink bg-white cursor-pointer focus:outline-none focus:border-pink-600">
                    <option value="">বেছে নিন</option>
                    <option value="surgery">অপারেশন</option>
                    <option value="chemo">কেমোথেরাপি</option>
                    <option value="radiation">রেডিওথেরাপি</option>
                    <option value="hormone">হরমোন থেরাপি</option>
                    <option value="unknown">জানি না</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white p-5">
        <div x-ref="empty" class="text-center py-[26px] text-slate-500 text-[13.5px]">
            <i class="ti ti-arrow-up text-xl block mb-[9px]"></i>
            <span class="font-bn">উপরে তথ্য দিলে ফলাফল দেখাবে</span>
        </div>

        <div x-ref="result" style="display:none">
            <div class="flex gap-[14px] items-start mb-4">
                <div x-ref="iconWrap" class="w-[42px] h-[42px] rounded-xl flex items-center justify-center shrink-0">
                    <i x-ref="icon" class="ti" style="font-size:20px"></i>
                </div>
                <div class="flex-1">
                    <div x-ref="title" class="font-bn text-[16px] font-semibold mb-1"></div>
                    <div x-ref="desc" class="font-bn text-[13.5px] text-slate-500 leading-[1.7]"></div>
                </div>
            </div>
            <div x-ref="grid" class="grid grid-cols-2 gap-[10px]"></div>
            <div x-ref="cta" style="display:none" class="font-bn mt-[14px] pt-[14px] border-t border-line flex items-center gap-[9px] text-[13px] text-slate-500 leading-[1.6]"></div>
        </div>
    </div>
</div>
