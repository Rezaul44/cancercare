@extends('layouts.app')

@section('title', 'ডাক্তার হিসেবে যুক্ত হোন — CancerCare Bangladesh')

@section('content')

@if ($submitted)
    <div class="bg-white border-b border-line py-16">
        <div class="max-w-[640px] mx-auto px-6 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                <i class="ti ti-circle-check text-3xl"></i>
            </div>
            <h1 class="font-serif text-[32px] font-medium leading-[1.2] mb-3">আবেদন জমা হয়েছে</h1>
            <p class="font-bn text-[15.5px] text-slate-500 leading-[1.7] mb-8">
                ধন্যবাদ। আপনার আবেদন আমরা পেয়েছি — <b class="text-ink">৫–৭ কর্মদিবসের মধ্যে</b> BMDC নিবন্ধন ও সনদ যাচাই করে
                আমরা আপনাকে <b class="text-ink">ফোন করব</b>।
            </p>

            <div class="text-left border border-line rounded-[14px] overflow-hidden">
                <div class="bg-mist px-5 py-3.5 text-[13px] font-semibold flex items-center gap-2">
                    <i class="ti ti-route text-[17px] text-slate-500"></i>
                    <span class="font-bn">এরপর যা হবে</span>
                </div>
                <div class="px-5 py-4">
                    <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                        <i class="ti ti-shield-check text-base text-slate-500 shrink-0 mt-0.5"></i>
                        <span><b class="text-ink">যাচাই (৫–৭ কর্মদিবস)</b> — BMDC নিবন্ধন ও ডিগ্রির সনদ আমরা মিলিয়ে দেখব।</span>
                    </div>
                    <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                        <i class="ti ti-phone text-base text-slate-500 shrink-0 mt-0.5"></i>
                        <span><b class="text-ink">ফোনে আলোচনা</b> — আপনার প্রোফাইলের বাকি অংশ আমরা কথা বলে সাজাব: কোন ক্যান্সারে কী কী সেবা দেন, আপনার চিকিৎসা দর্শন, এবং রোগীর সংখ্যা ও ফলাফল-সংক্রান্ত তথ্য।</span>
                    </div>
                    <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                        <i class="ti ti-video text-base text-slate-500 shrink-0 mt-0.5"></i>
                        <span><b class="text-ink">ভিডিও (ঐচ্ছিক)</b> — চাইলে আমরা আপনার ২ মিনিটের পরিচিতি ভিডিও ধারণ করে দিতে পারি। এটি সম্পূর্ণ ঐচ্ছিক এবং <b class="text-ink">তালিকায় আপনার ক্রমে কোনো প্রভাব ফেলে না</b>।</span>
                    </div>
                    <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                        <i class="ti ti-check text-base text-slate-500 shrink-0 mt-0.5"></i>
                        <span><b class="text-ink">প্রকাশের আগে অনুমোদন</b> — সম্পূর্ণ প্রোফাইল আপনাকে দেখানো হবে। আপনি অনুমোদন দিলে তবেই প্রকাশ করা হবে।</span>
                    </div>
                </div>
            </div>

            <a href="{{ url('/') }}" class="font-bn inline-block mt-8 text-sm px-6 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">
                হোমপেজে ফিরে যান
            </a>
        </div>
    </div>
@else

    {{-- docs/prototypes/onboarding.html-এর "ডাক্তার যুক্ত হওয়া" অংশ হুবহু অনুসরণ করে --}}
    <div class="bg-white border-b border-line pt-12 pb-11">
        <div class="max-w-[1240px] mx-auto px-10">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-[60px] items-center">
                <div>
                    <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">অনকোলজিস্টদের জন্য</div>
                    <h1 class="font-serif text-[40px] font-medium tracking-[-0.02em] leading-[1.16] mb-3">
                        <span class="font-bn">আবেদন করুন,</span><br>
                        <span class="font-bn">সারা দেশের রোগীরা আপনাকে খুঁজে পাবেন</span>
                    </h1>
                    <p class="font-bn text-base text-slate-500 leading-[1.7] max-w-[580px]">
                        মৌলিক তথ্য দিয়ে আবেদন করুন — বাকি প্রোফাইল আমরা আপনার সাথে কথা বলে সাজিয়ে দেব। তালিকাভুক্তি ও যাচাইকরণ সম্পূর্ণ বিনামূল্যে।
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-7">
                        <div class="border border-line rounded-[14px] px-5 py-4">
                            <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                                <i class="ti ti-coin-off text-[19px]"></i>
                            </div>
                            <div class="font-bn text-[14.5px] font-semibold mb-1">সম্পূর্ণ বিনামূল্যে</div>
                            <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">কোনো ফি নেই, কমিশন নেই। টাকা দিয়ে তালিকায় উপরে ওঠারও সুযোগ নেই।</div>
                        </div>
                        <div class="border border-line rounded-[14px] px-5 py-4">
                            <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                                <i class="ti ti-users text-[19px]"></i>
                            </div>
                            <div class="font-bn text-[14.5px] font-semibold mb-1">সঠিক রোগী</div>
                            <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">রোগীরা ক্যান্সারের ধরন ও জেলা দিয়ে খোঁজেন — আপনার বিশেষত্বের রোগীই আসবেন।</div>
                        </div>
                        <div class="border border-line rounded-[14px] px-5 py-4">
                            <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                                <i class="ti ti-shield-check text-[19px]"></i>
                            </div>
                            <div class="font-bn text-[14.5px] font-semibold mb-1">যাচাইকৃত পরিচয়</div>
                            <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">BMDC যাচাইয়ের ব্যাজ — রোগীরা নিশ্চিত হয়ে যোগাযোগ করতে পারেন।</div>
                        </div>
                    </div>
                </div>
                <img class="hidden lg:block h-[250px] w-full object-cover rounded-[18px]" src="https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=700&q=80" alt="অনকোলজিস্ট ও ক্যান্সার বিশেষজ্ঞ" loading="lazy" decoding="async" width="400" height="250">
            </div>
        </div>
    </div>

    <div
        x-data="{
            step: {{ (int) $initialStep }},
            timelineRows: {{ \Illuminate\Support\Js::from(old('timeline', [
                ['year_label' => '', 'title_bn' => '', 'institution_bn' => ''],
                ['year_label' => '', 'title_bn' => '', 'institution_bn' => ''],
                ['year_label' => '', 'title_bn' => '', 'institution_bn' => ''],
            ])) }},
            chambersRows: {{ \Illuminate\Support\Js::from(old('chambers', [
                ['name_bn' => '', 'address_bn' => '', 'fee' => '', 'type' => 'private', 'days_bn' => ''],
            ])) }},
            addTimelineRow() { this.timelineRows.push({ year_label: '', title_bn: '', institution_bn: '' }); },
            removeTimelineRow(i) { if (this.timelineRows.length > 1) this.timelineRows.splice(i, 1); },
            addChamberRow() { this.chambersRows.push({ name_bn: '', address_bn: '', fee: '', type: 'private', days_bn: '' }); },
            removeChamberRow(i) { if (this.chambersRows.length > 1) this.chambersRows.splice(i, 1); },
            next() { if (this.step < 4) { this.step++; window.scrollTo({ top: 220, behavior: 'smooth' }); } },
            back() { if (this.step > 1) { this.step--; window.scrollTo({ top: 220, behavior: 'smooth' }); } },
        }"
        class="pt-8 pb-16"
    >
        <div class="max-w-[820px] mx-auto px-6">

            <div class="mb-6">
                <div class="flex justify-between items-baseline mb-2.5">
                    <span class="font-bn text-[12.5px] text-[#8E979D]" x-text="'ধাপ ' + ['১','২','৩','৪'][step-1] + ' / ৪'"></span>
                    <span class="font-bn text-[12.5px] text-teal-700 flex items-center gap-1.5">
                        <i class="ti ti-lock text-sm"></i> আপনার তথ্য সুরক্ষিত
                    </span>
                </div>
                <div class="h-1 bg-line rounded-full overflow-hidden">
                    <div class="h-full bg-slate-900 rounded-full transition-all duration-300" :style="'width: ' + (step * 25) + '%'"></div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-[13px] border border-red-200 bg-red-50 px-5 py-4">
                    <div class="font-bn text-sm font-semibold text-red-700 mb-2">ফর্মে কিছু তথ্য ঠিক নেই — নিচে দেখুন</div>
                    <ul class="font-bn list-disc pl-5 text-[13px] text-red-700 space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('doctors.apply.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- ধাপ ১ — পরিচয় ও যোগাযোগ --}}
                <div x-show="step === 1" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                    <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ১</div>
                    <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                        <span class="font-bn">পরিচয় ও যোগাযোগ</span>
                    </div>
                    <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                        BMDC নম্বর দিয়ে আমরা নিবন্ধন যাচাই করব। ফোন ও ইমেইল শুধু আমাদের যোগাযোগের জন্য — প্রোফাইলে প্রকাশ করা হবে না।
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পূর্ণ নাম <span class="text-pink-600">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="ডা. সাদিয়া রহমান"
                                class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">BMDC নিবন্ধন নম্বর <span class="text-pink-600">*</span></label>
                            <input type="text" name="bmdc_number" value="{{ old('bmdc_number') }}" placeholder="A-34821"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('bmdc_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">মোবাইল নম্বর <span class="text-pink-600">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01712345678"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ইমেইল <span class="text-pink-600">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="doctor@example.com"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4" x-data="{ fileName: '' }">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">প্রোফাইল ছবি <span class="text-pink-600">*</span></label>
                        <label class="relative flex flex-col items-center gap-1 rounded-[13px] border-2 border-dashed border-line bg-[#FCFCFB] p-5 text-center cursor-pointer hover:border-slate-300 hover:bg-mist">
                            <input type="file" name="photo" accept="image/png,image/jpeg" class="absolute inset-0 opacity-0 cursor-pointer" @change="fileName = $event.target.files[0]?.name ?? ''">
                            <div class="flex h-[42px] w-[42px] items-center justify-center rounded-[11px] bg-mist text-slate-500">
                                <i class="ti ti-camera text-xl"></i>
                            </div>
                            <div class="font-bn text-sm font-semibold" x-text="fileName || 'ছবি আপলোড করুন'"></div>
                            <div class="font-bn text-[12.5px] leading-[1.55] text-[#8E979D]">পেশাদার ছবি, মুখ স্পষ্ট দেখা যায় এমন · JPG বা PNG · সর্বোচ্চ ৫ MB</div>
                        </label>
                        @error('photo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4" x-data="{ fileName: '' }">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">BMDC সনদের ছবি বা স্ক্যান <span class="text-pink-600">*</span></label>
                        <label class="relative flex flex-col items-center gap-1 rounded-[13px] border-2 border-dashed border-line bg-[#FCFCFB] p-5 text-center cursor-pointer hover:border-slate-300 hover:bg-mist">
                            <input type="file" name="bmdc_certificate" accept="image/png,image/jpeg,application/pdf" class="absolute inset-0 opacity-0 cursor-pointer" @change="fileName = $event.target.files[0]?.name ?? ''">
                            <div class="flex h-[42px] w-[42px] items-center justify-center rounded-[11px] bg-mist text-slate-500">
                                <i class="ti ti-file-certificate text-xl"></i>
                            </div>
                            <div class="font-bn text-sm font-semibold" x-text="fileName || 'সনদ আপলোড করুন'"></div>
                            <div class="font-bn text-[12.5px] leading-[1.55] text-[#8E979D]">যাচাইয়ের জন্য প্রয়োজন · এটি কখনো প্রকাশ করা হবে না</div>
                        </label>
                        @error('bmdc_certificate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                        <button type="button" @click="next()" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">পরবর্তী ধাপ</button>
                    </div>
                </div>

                {{-- ধাপ ২ — যোগ্যতা ও পেশাগত জীবন --}}
                <div x-show="step === 2" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                    <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ২</div>
                    <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                        <span class="font-bn">যোগ্যতা ও পেশাগত জীবন</span>
                    </div>
                    <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                        প্রোফাইলে যা দেখানো হবে। ডিগ্রির সনদ যাচাইয়ের জন্য লাগবে, প্রকাশ করা হবে না।
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">মূল ডিগ্রি <span class="text-pink-600">*</span></label>
                            <input type="text" name="primary_degree" value="{{ old('primary_degree') }}" placeholder="MBBS — ঢাকা মেডিকেল কলেজ, ২০০১"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('primary_degree') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">বিশেষায়িত ডিগ্রি <span class="text-pink-600">*</span></label>
                            <input type="text" name="specialized_degree" value="{{ old('specialized_degree') }}" placeholder="FCPS (Oncology) — BCPS, ২০০৭"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('specialized_degree') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ফেলোশিপ বা অতিরিক্ত প্রশিক্ষণ</label>
                        <input type="text" name="fellowship" value="{{ old('fellowship') }}" placeholder="Fellowship in Breast Oncology — Tata Memorial, Mumbai, ২০০৯"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                        <p class="font-bn text-xs text-[#8E979D] mt-1.5 leading-[1.55]">না থাকলে খালি রাখুন</p>
                        @error('fellowship') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">মোট অভিজ্ঞতা (বছর) <span class="text-pink-600">*</span></label>
                            <input type="number" min="0" max="60" name="experience_years" value="{{ old('experience_years') }}" placeholder="১৬"
                                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('experience_years') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">বর্তমান প্রধান পদ <span class="text-pink-600">*</span></label>
                            <input type="text" name="current_position" value="{{ old('current_position') }}" placeholder="সিনিয়র কনসালট্যান্ট, স্কয়ার হাসপাতাল"
                                class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                            @error('current_position') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পেশাগত জীবনের ধাপ <span class="text-pink-600">*</span></label>
                        <p class="font-bn text-xs text-[#8E979D] mb-2.5 leading-[1.55]">পুরনো থেকে নতুন ক্রমে দিন — প্রোফাইলে এটি একটি টাইমলাইন হিসেবে দেখানো হবে।</p>

                        <template x-for="(row, i) in timelineRows" :key="i">
                            <div class="grid grid-cols-[90px_1fr_1fr_auto] sm:grid-cols-[110px_1fr_1fr_auto] gap-2.5 items-end mb-2.5">
                                <input type="text" :name="'timeline[' + i + '][year_label]'" x-model="row.year_label" placeholder="২০০১"
                                    class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                <input type="text" :name="'timeline[' + i + '][title_bn]'" x-model="row.title_bn" placeholder="MBBS"
                                    class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                <input type="text" :name="'timeline[' + i + '][institution_bn]'" x-model="row.institution_bn" placeholder="ঢাকা মেডিকেল কলেজ"
                                    class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                <button type="button" @click="removeTimelineRow(i)" class="w-[38px] h-11 border border-line rounded-[10px] bg-white text-[#8E979D] flex items-center justify-center hover:border-red-700 hover:text-red-700">
                                    <i class="ti ti-x text-base"></i>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addTimelineRow()" class="font-bn w-full p-3.5 border-2 border-dashed border-line rounded-xl bg-transparent text-[13.5px] text-slate-500 font-medium flex items-center justify-center gap-2 hover:border-slate-300 hover:text-ink">
                            <i class="ti ti-plus text-base"></i> আরেকটি ধাপ যোগ করুন
                        </button>
                        @error('timeline') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4" x-data="{ fileNames: [] }">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ডিগ্রির সনদ আপলোড করুন <span class="text-pink-600">*</span></label>
                        <label class="relative flex flex-col items-center gap-1 rounded-[13px] border-2 border-dashed border-line bg-[#FCFCFB] p-5 text-center cursor-pointer hover:border-slate-300 hover:bg-mist">
                            <input type="file" name="degree_certificates[]" multiple accept="image/png,image/jpeg,application/pdf" class="absolute inset-0 opacity-0 cursor-pointer" @change="fileNames = Array.from($event.target.files).map(f => f.name)">
                            <div class="flex h-[42px] w-[42px] items-center justify-center rounded-[11px] bg-mist text-slate-500">
                                <i class="ti ti-certificate text-xl"></i>
                            </div>
                            <div class="font-bn text-sm font-semibold" x-text="fileNames.length ? fileNames.length + 'টি ফাইল নির্বাচিত' : 'সনদ আপলোড করুন'"></div>
                            <div class="font-bn text-[12.5px] leading-[1.55] text-[#8E979D]">একাধিক ফাইল দিতে পারেন · PDF, JPG বা PNG</div>
                        </label>
                        @error('degree_certificates') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                        <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                        <button type="button" @click="next()" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">পরবর্তী ধাপ</button>
                    </div>
                </div>

                {{-- ধাপ ৩ — বিশেষত্ব ও চেম্বার --}}
                <div x-show="step === 3" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                    <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ৩</div>
                    <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                        <span class="font-bn">বিশেষত্ব ও চেম্বার</span>
                    </div>
                    <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                        রোগীরা এই তথ্য দিয়েই আপনাকে খুঁজে পাবেন — তাই যতটা সম্ভব নির্দিষ্ট করে দিন।
                    </p>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">আপনি কোন ধরনের অনকোলজিস্ট <span class="text-pink-600">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($doctorTypes as $type)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="doctor_types[]" value="{{ $type->id }}" class="peer sr-only" @checked(in_array($type->id, old('doctor_types', [])))>
                                    <span class="font-bn inline-block rounded-full border border-line bg-white px-4 py-2 text-[13px] font-medium text-slate-500 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white">
                                        {{ $type->label_bn }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('doctor_types') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">যেসব ক্যান্সারে কাজ করেন <span class="text-pink-600">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($cancerTypes as $type)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="cancer_types[]" value="{{ $type->id }}" class="peer sr-only" @checked(in_array($type->id, old('cancer_types', [])))>
                                    <span class="font-bn inline-block rounded-full border border-line bg-white px-4 py-2 text-[13px] font-medium text-slate-500 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white">
                                        {{ $type->name_bn }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="font-bn text-xs text-[#8E979D] mt-1.5 leading-[1.55]">শুধু যেগুলোতে নিয়মিত রোগী দেখেন সেগুলো বাছুন — বেশি বাছলে ভুল রোগী আসবে</p>
                        @error('cancer_types') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">চেম্বারের তথ্য <span class="text-pink-600">*</span></label>

                        <template x-for="(chamber, i) in chambersRows" :key="i">
                            <div class="border border-line rounded-[14px] px-5 py-4 mb-3">
                                <div class="font-bn text-[11.5px] font-semibold text-[#8E979D] uppercase tracking-[0.07em] mb-3" x-text="'চেম্বার ' + (i + 1)"></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">হাসপাতাল বা চেম্বারের নাম</label>
                                        <input type="text" :name="'chambers[' + i + '][name_bn]'" x-model="chamber.name_bn" placeholder="স্কয়ার হাসপাতাল"
                                            class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                    </div>
                                    <div>
                                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">এলাকা</label>
                                        <input type="text" :name="'chambers[' + i + '][address_bn]'" x-model="chamber.address_bn" placeholder="পান্থপথ, ঢাকা"
                                            class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ভিজিট ফি (৳)</label>
                                        <input type="number" min="0" :name="'chambers[' + i + '][fee]'" x-model="chamber.fee" placeholder="১০০০"
                                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                    </div>
                                    <div>
                                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ধরন</label>
                                        <select :name="'chambers[' + i + '][type]'" x-model="chamber.type"
                                            class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                                            <option value="private">বেসরকারি</option>
                                            <option value="govt">সরকারি</option>
                                            <option value="npo">অলাভজনক</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">যেসব দিন ও সময়</label>
                                    <input type="text" :name="'chambers[' + i + '][days_bn]'" x-model="chamber.days_bn" placeholder="রবি, মঙ্গল, বৃহস্পতি · বিকেল ৪টা – রাত ৮টা"
                                        class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                                </div>
                                <button type="button" x-show="chambersRows.length > 1" @click="removeChamberRow(i)" class="font-bn mt-3 text-xs text-[#8E979D] hover:text-red-700">
                                    <i class="ti ti-trash text-sm"></i> এই চেম্বার সরান
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="addChamberRow()" class="font-bn w-full p-3.5 border-2 border-dashed border-line rounded-xl bg-transparent text-[13.5px] text-slate-500 font-medium flex items-center justify-center gap-2 hover:border-slate-300 hover:text-ink">
                            <i class="ti ti-plus text-base"></i> আরেকটি চেম্বার যোগ করুন
                        </button>
                        <p class="font-bn text-xs text-[#8E979D] mt-2.5 leading-[1.55]">সরকারি হাসপাতালেও বসলে অবশ্যই যোগ করুন — অনেক রোগীর জন্য সেটাই একমাত্র সাধ্যের মধ্যে</p>
                        @error('chambers') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">অতিরিক্ত সেবা</label>
                        <div class="flex flex-wrap gap-2">
                            @php $extraServiceOptions = ['whatsapp' => 'WhatsApp পরামর্শ দিই', 'second_opinion' => 'দ্বিতীয় মতামত দিই', 'telemedicine' => 'টেলিমেডিসিন']; @endphp
                            @foreach ($extraServiceOptions as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="extra_services[]" value="{{ $value }}" class="peer sr-only" @checked(in_array($value, old('extra_services', [])))>
                                    <span class="font-bn inline-block rounded-full border border-line bg-white px-4 py-2 text-[13px] font-medium text-slate-500 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                        <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                        <button type="button" @click="next()" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">পরবর্তী ধাপ</button>
                    </div>
                </div>

                {{-- ধাপ ৪ — ঘোষণা ও সম্মতি --}}
                <div x-show="step === 4" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                    <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ৪</div>
                    <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                        <span class="font-bn">ঘোষণা ও সম্মতি</span>
                    </div>
                    <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                        প্রায় শেষ। এই বিষয়গুলো নিশ্চিত করলেই আবেদন জমা দিতে পারবেন।
                    </p>

                    <div class="bg-mist rounded-[14px] px-6 py-5">
                        <div class="font-bn text-sm font-semibold mb-3.5">আমি নিশ্চিত করছি যে —</div>

                        @php
                            $declarationItems = [
                                'information_accurate' => 'উপরের সব তথ্য সত্য ও সঠিক। ভুল তথ্য দিলে প্রোফাইল সরিয়ে ফেলা হতে পারে।',
                                'bmdc_valid' => 'আমার BMDC নিবন্ধন বর্তমানে বৈধ এবং কোনো শাস্তিমূলক ব্যবস্থার অধীনে নেই।',
                                'no_payment_for_ranking' => 'আমি জানি যে CCB-তে টাকা দিয়ে তালিকায় উপরে ওঠা যায় না, এবং আমি এমন কোনো অনুরোধ করব না।',
                                'will_notify_changes' => 'চেম্বার, ফি বা সময়সূচি বদলালে আমি CCB-কে জানাব।',
                                'consent_patient_feedback' => 'রোগীরা আমার প্রোফাইলে নাম প্রকাশ ছাড়া মতামত দিতে পারবেন — এতে আমার সম্মতি আছে।',
                            ];
                        @endphp

                        @foreach ($declarationItems as $key => $label)
                            <label class="flex cursor-pointer items-start gap-3 py-2">
                                <input type="checkbox" name="declarations[{{ $key }}]" value="1" class="peer sr-only" @checked(old("declarations.$key", true))>
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-[5px] border border-line bg-white peer-checked:border-slate-900 peer-checked:bg-slate-900">
                                    <i class="ti ti-check text-white text-xs"></i>
                                </span>
                                <span class="font-bn text-[13.5px] leading-[1.65] text-slate-500 peer-checked:text-ink">{{ $label }}</span>
                            </label>
                            @error("declarations.$key") <p class="text-xs text-red-600 -mt-1 mb-1 ml-[30px]">{{ $message }}</p> @enderror
                        @endforeach
                    </div>

                    <div class="bg-mist rounded-[13px] px-5 py-4 mt-2">
                        <div class="font-bn text-[13.5px] font-semibold mb-3">আমরা কখন ফোন করলে সুবিধা হবে?</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পছন্দের সময়</label>
                                <select name="preferred_call_time" class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                                    @foreach (['সকাল ৯টা – দুপুর ১২টা', 'দুপুর ১২টা – বিকেল ৪টা', 'বিকেল ৪টা – রাত ৮টা', 'যেকোনো সময়'] as $option)
                                        <option value="{{ $option }}" @selected(old('preferred_call_time') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পছন্দের দিন</label>
                                <select name="preferred_call_day" class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                                    @foreach (['যেকোনো কর্মদিবস', 'শনি ও রবি', 'শুক্রবার'] as $option)
                                        <option value="{{ $option }}" @selected(old('preferred_call_day') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border border-line rounded-[14px] overflow-hidden mt-5">
                        <div class="bg-mist px-5 py-3.5 text-[13px] font-semibold flex items-center gap-2">
                            <i class="ti ti-route text-[17px] text-slate-500"></i>
                            <span class="font-bn">ফর্ম জমা দেওয়ার পর যা হবে</span>
                        </div>
                        <div class="px-5 py-4">
                            <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                                <i class="ti ti-shield-check text-base text-slate-500 shrink-0 mt-0.5"></i>
                                <span><b class="text-ink">যাচাই (৫–৭ কর্মদিবস)</b> — BMDC নিবন্ধন ও ডিগ্রির সনদ আমরা মিলিয়ে দেখব।</span>
                            </div>
                            <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                                <i class="ti ti-phone text-base text-slate-500 shrink-0 mt-0.5"></i>
                                <span><b class="text-ink">ফোনে আলোচনা</b> — আপনার প্রোফাইলের বাকি অংশ আমরা কথা বলে সাজাব: কোন ক্যান্সারে কী কী সেবা দেন, আপনার চিকিৎসা দর্শন, এবং রোগীর সংখ্যা ও ফলাফল-সংক্রান্ত তথ্য। এই অংশগুলো আমরা নিজে লিখি যাতে সব প্রোফাইলে তথ্যের মান ও ভাষা এক রকম থাকে।</span>
                            </div>
                            <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                                <i class="ti ti-video text-base text-slate-500 shrink-0 mt-0.5"></i>
                                <span><b class="text-ink">ভিডিও (ঐচ্ছিক)</b> — চাইলে আমরা আপনার ২ মিনিটের পরিচিতি ভিডিও ধারণ করে দিতে পারি। এটি সম্পূর্ণ ঐচ্ছিক এবং <b class="text-ink">তালিকায় আপনার ক্রমে কোনো প্রভাব ফেলে না</b>।</span>
                            </div>
                            <div class="font-bn flex gap-2.5 text-[13.5px] text-slate-500 leading-[1.6] py-1.5">
                                <i class="ti ti-check text-base text-slate-500 shrink-0 mt-0.5"></i>
                                <span><b class="text-ink">প্রকাশের আগে অনুমোদন</b> — সম্পূর্ণ প্রোফাইল আপনাকে দেখানো হবে। আপনি অনুমোদন দিলে তবেই প্রকাশ করা হবে।</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 bg-teal-50 border border-[#BFE5DC] rounded-[13px] px-5 py-4 mt-5">
                        <i class="ti ti-scale text-teal-700"></i>
                        <div class="font-bn text-[13.5px] text-[#166B5C] leading-[1.68]">
                            <b class="text-teal-700 font-semibold">ক্রম নির্ধারণের পদ্ধতি:</b> রোগীর খোঁজের সাথে বিশেষত্বের মিল, দূরত্ব, এবং যাচাইকৃত রোগীর মতামত — এই তিনটি দিয়ে ক্রম ঠিক হয়। ভিডিও থাকা বা না থাকা, অথবা CCB-র সাথে কোনো ব্যবসায়িক সম্পর্ক — কোনোটিই ক্রমে প্রভাব ফেলে না। সমান যোগ্যতার ডাক্তারদের ক্রম নিয়মিত ঘোরানো হয়।
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                        <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                        <button type="submit" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">আবেদন জমা দিন</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection
