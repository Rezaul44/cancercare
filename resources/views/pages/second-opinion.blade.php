@extends('layouts.app')

@section('title', 'দ্বিতীয় মতামত নিন — CancerCare Bangladesh')

@section('content')

{{-- ডিজাইনের কোনো prototype নেই — docs/prototypes/onboarding.html-এর ফর্ম-ডিজাইন (কার্ড, আপলোড বক্স, চিপ, ধাপ ইত্যাদি) থেকে স্টাইলিং নেওয়া হয়েছে --}}
<div class="bg-white border-b border-line pt-12 pb-11">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">দ্বিতীয় মতামত</div>
        <h1 class="font-serif text-[36px] font-medium tracking-[-0.02em] leading-[1.16] mb-3">
            <span class="font-bn">নিশ্চিত হয়ে সিদ্ধান্ত নিন —</span><br>
            <span class="font-bn">অভিজ্ঞ অনকোলজিস্টের কাছ থেকে দ্বিতীয় মতামত নিন</span>
        </h1>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-7 max-w-[900px]">
            <div class="border border-line rounded-[14px] px-5 py-4">
                <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                    <i class="ti ti-stethoscope text-[19px]"></i>
                </div>
                <div class="font-bn text-[14.5px] font-semibold mb-1">নিশ্চিত রোগ নির্ণয়</div>
                <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">চিকিৎসার পরিকল্পনা সঠিক পথে আছে কিনা আরেকজন বিশেষজ্ঞের চোখে যাচাই করুন।</div>
            </div>
            <div class="border border-line rounded-[14px] px-5 py-4">
                <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                    <i class="ti ti-clock text-[19px]"></i>
                </div>
                <div class="font-bn text-[14.5px] font-semibold mb-1">দ্রুত উত্তর</div>
                <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">নির্ধারিত সময়ের মধ্যে লিখিত উত্তর পাবেন — হাসপাতালে গিয়ে অপেক্ষা করতে হবে না।</div>
            </div>
            <div class="border border-line rounded-[14px] px-5 py-4">
                <div class="w-[38px] h-[38px] rounded-[10px] bg-mist flex items-center justify-center mb-3 text-slate-700">
                    <i class="ti ti-lock text-[19px]"></i>
                </div>
                <div class="font-bn text-[14.5px] font-semibold mb-1">নিরাপদ ও গোপনীয়</div>
                <div class="font-bn text-[13px] text-slate-500 leading-[1.6]">আপনার রিপোর্ট শুধু আপনার নির্বাচিত ডাক্তারই দেখতে পারবেন — এনক্রিপ্টেড ভল্টে সংরক্ষিত।</div>
            </div>
        </div>
    </div>
</div>

<div
    x-data="{
        step: {{ (int) $initialStep }},
        fileNames: [],
        doctorId: '{{ old('doctor_id', '') }}',
        gateway: '{{ old('gateway', '') }}',
        doctors: {{ \Illuminate\Support\Js::from($doctors->map(fn ($d) => [
            'id' => (string) $d->id,
            'name_bn' => $d->name_bn,
            'degrees_line_bn' => $d->degrees_line_bn,
            'fee' => (int) ($d->second_opinion_fee ?? 0),
        ])->values()) }},
        get selectedDoctor() {
            return this.doctors.find(d => d.id === this.doctorId) || null;
        },
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
                    <i class="ti ti-lock text-sm"></i> আপনার তথ্য ও রিপোর্ট সুরক্ষিত
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

        <form method="POST" action="{{ route('second-opinion.request.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- ধাপ ১ — রিপোর্ট আপলোড --}}
            <div x-show="step === 1" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ১</div>
                <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                    <span class="font-bn">আপনার রিপোর্ট আপলোড করুন</span>
                </div>
                <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                    বায়োপসি, ইমেজিং, প্যাথলজি বা চিকিৎসা পরিকল্পনার রিপোর্ট — যা আছে সব দিন। এগুলো এনক্রিপ্টেড ভল্টে থাকবে, শুধু আপনার নির্বাচিত ডাক্তার দেখতে পারবেন।
                </p>

                <div class="mb-4" x-data="{ names: [] }">
                    <label class="relative flex flex-col items-center gap-1 rounded-[13px] border-2 border-dashed border-line bg-[#FCFCFB] p-8 text-center cursor-pointer hover:border-slate-300 hover:bg-mist">
                        <input type="file" name="reports[]" multiple accept="image/png,image/jpeg,application/pdf" class="absolute inset-0 opacity-0 cursor-pointer" @change="names = Array.from($event.target.files).map(f => f.name)">
                        <div class="flex h-[42px] w-[42px] items-center justify-center rounded-[11px] bg-mist text-slate-500">
                            <i class="ti ti-cloud-upload text-xl"></i>
                        </div>
                        <div class="font-bn text-sm font-semibold" x-text="names.length ? names.length + 'টি ফাইল নির্বাচিত' : 'রিপোর্ট আপলোড করুন'"></div>
                        <div class="font-bn text-[12.5px] leading-[1.55] text-[#8E979D]">একাধিক ফাইল দিতে পারেন · PDF, JPG বা PNG · প্রতিটি সর্বোচ্চ ১০ MB</div>
                    </label>
                    @error('reports') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('reports.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                    <button type="button" @click="next()" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">পরবর্তী ধাপ</button>
                </div>
            </div>

            {{-- ধাপ ২ — রোগীর পরিস্থিতি ও প্রশ্ন --}}
            <div x-show="step === 2" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ২</div>
                <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                    <span class="font-bn">রোগীর পরিস্থিতি ও প্রশ্ন</span>
                </div>
                <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                    যত স্পষ্ট করে লিখবেন, উত্তর তত নির্দিষ্ট হবে।
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">রোগীর নাম <span class="text-pink-600">*</span></label>
                        <input type="text" name="patient_name" value="{{ old('patient_name') }}" placeholder="রোগীর পূর্ণ নাম"
                            class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                        @error('patient_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">বয়স <span class="text-pink-600">*</span></label>
                        <input type="number" min="0" max="120" name="age" value="{{ old('age') }}" placeholder="৪৫"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                        @error('age') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ক্যান্সারের ধরন <span class="text-pink-600">*</span></label>
                        <select name="cancer_type_id" class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($cancerTypes as $type)
                                <option value="{{ $type->id }}" @selected((string) old('cancer_type_id') === (string) $type->id)>{{ $type->name_bn }}</option>
                            @endforeach
                        </select>
                        @error('cancer_type_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">চিকিৎসার বর্তমান অবস্থা <span class="text-pink-600">*</span></label>
                        <select name="current_status" class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                            <option value="">নির্বাচন করুন</option>
                            @foreach (\App\Enums\SecondOpinionCurrentStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('current_status') === $status->value)>{{ $status->labelBn() }}</option>
                            @endforeach
                        </select>
                        @error('current_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">এ পর্যন্ত যা চিকিৎসা হয়েছে</label>
                    <textarea name="treatments_done_bn" rows="3" placeholder="যেমন: সার্জারি হয়েছে, কেমোথেরাপির ২টি চক্র চলছে..."
                        class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">{{ old('treatments_done_bn') }}</textarea>
                    @error('treatments_done_bn') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">আপনার প্রশ্ন <span class="text-pink-600">*</span></label>
                    <textarea name="question_bn" rows="4" placeholder="ডাক্তারকে নির্দিষ্ট করে কী জানতে চান লিখুন"
                        class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">{{ old('question_bn') }}</textarea>
                    @error('question_bn') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">মোবাইল নম্বর <span class="text-pink-600">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01712345678"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">জেলা <span class="text-pink-600">*</span></label>
                        <select name="district_id" class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 cursor-pointer">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}" @selected((string) old('district_id') === (string) $district->id)>{{ $district->name_bn }}</option>
                            @endforeach
                        </select>
                        @error('district_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                    <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                    <button type="button" @click="next()" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">পরবর্তী ধাপ</button>
                </div>
            </div>

            {{-- ধাপ ৩ — ডাক্তার বাছাই --}}
            <div x-show="step === 3" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ৩</div>
                <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                    <span class="font-bn">ডাক্তার বাছাই করুন</span>
                </div>
                <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] mb-7">
                    যেসব অনকোলজিস্ট দ্বিতীয় মতামত দেন তাঁদের মধ্য থেকে একজনকে বাছুন।
                </p>

                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                    @foreach ($doctors as $doctor)
                        <label class="relative flex items-center justify-between gap-4 rounded-[14px] border border-line px-5 py-4 cursor-pointer has-[:checked]:border-slate-900 has-[:checked]:bg-mist">
                            <input type="radio" name="doctor_id" value="{{ $doctor->id }}" x-model="doctorId" class="sr-only">
                            <div>
                                <div class="font-bn text-[14.5px] font-semibold text-ink">{{ $doctor->name_bn }}</div>
                                <div class="font-bn text-[12.5px] text-slate-500 mt-0.5">{{ $doctor->degrees_line_bn }}</div>
                            </div>
                            <div class="font-bn text-[13.5px] font-semibold text-slate-700 whitespace-nowrap">৳{{ number_format((int) ($doctor->second_opinion_fee ?? 0)) }}</div>
                        </label>
                    @endforeach

                    @if ($doctors->isEmpty())
                        <p class="font-bn text-sm text-slate-500">এই মুহূর্তে কোনো ডাক্তার দ্বিতীয় মতামত সেবা দিচ্ছেন না।</p>
                    @endif
                </div>
                @error('doctor_id') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                    <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                    <button type="button" @click="next()" :disabled="!doctorId" :class="doctorId ? 'bg-slate-900 hover:bg-slate-700' : 'bg-line cursor-not-allowed'" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium text-white">পরবর্তী ধাপ</button>
                </div>
            </div>

            {{-- ধাপ ৪ — সারাংশ + পেমেন্ট --}}
            <div x-show="step === 4" x-cloak class="bg-white border border-line rounded-[20px] px-10 py-9">
                <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-[#8E979D] font-semibold mb-3">ধাপ ৪</div>
                <div class="font-serif text-[28px] font-medium leading-[1.26] tracking-[-0.016em] mb-2">
                    <span class="font-bn">সারাংশ ও পেমেন্ট</span>
                </div>

                <div class="bg-mist rounded-[14px] px-6 py-5 mb-5" x-show="selectedDoctor">
                    <div class="font-bn text-[13px] text-slate-500 mb-1">নির্বাচিত ডাক্তার</div>
                    <div class="font-bn text-[15px] font-semibold text-ink" x-text="selectedDoctor?.name_bn"></div>
                    <div class="font-bn text-[13px] text-slate-500 mt-3 mb-1">প্রত্যাশিত উত্তর</div>
                    <div class="font-bn text-[14px] text-ink">{{ $expectedHours }} ঘণ্টার মধ্যে</div>
                    <div class="font-bn text-[13px] text-slate-500 mt-3 mb-1">ফি</div>
                    <div class="font-serif text-[24px] font-medium text-ink" x-text="'৳' + (selectedDoctor?.fee || 0)"></div>
                </div>

                <div class="flex gap-3 bg-[#FDF4E3] border border-[#F0DFBC] rounded-[13px] px-5 py-4 mb-5">
                    <i class="ti ti-alert-triangle text-[#C98A1E]"></i>
                    <div class="font-bn text-[13.5px] text-[#8A6416] leading-[1.68]">
                        <b class="font-semibold">জরুরি অবস্থায় অপেক্ষা করবেন না।</b> শ্বাসকষ্ট, তীব্র ব্যথা, বা হঠাৎ অবনতি হলে এখনই নিকটস্থ হাসপাতালে যান বা চিকিৎসা শুরু করুন — দ্বিতীয় মতামতের জন্য অপেক্ষা করবেন না।
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পেমেন্ট পদ্ধতি বাছুন <span class="text-pink-600">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach (\App\Enums\PaymentGateway::cases() as $gatewayOption)
                            <label class="relative flex flex-col items-center gap-1 rounded-[13px] border border-line bg-white px-4 py-4 text-center cursor-pointer has-[:checked]:border-slate-900 has-[:checked]:bg-mist">
                                <input type="radio" name="gateway" value="{{ $gatewayOption->value }}" x-model="gateway" class="sr-only">
                                <span class="font-bn text-[13.5px] font-semibold text-ink">{{ $gatewayOption->labelBn() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('gateway') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 mt-7 pt-6 border-t border-line">
                    <button type="button" @click="back()" class="font-bn bg-transparent text-slate-500 border border-line px-5 py-3 rounded-[9px] font-medium hover:bg-mist hover:text-ink">পেছনে</button>
                    <button type="submit" :disabled="!gateway" :class="gateway ? 'bg-slate-900 hover:bg-slate-700' : 'bg-line cursor-not-allowed'" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium text-white">
                        <span x-text="selectedDoctor ? 'পেমেন্ট করুন — ৳' + selectedDoctor.fee : 'পেমেন্ট করুন'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
