@extends('layouts.app')

@section('title', 'ক্যান্সার চিকিৎসার খরচের হিসাব — চিকিৎসায় আনুমানিক কত লাগতে পারে | CancerCare Bangladesh')
@section('meta_description', 'ক্যান্সার চিকিৎসায় সরকারি, অলাভজনক ও বেসরকারি হাসপাতালে আনুমানিক কত খরচ হয়। হাসপাতাল বিল ছাড়াও যাতায়াত, থাকা, ওষুধ ও পরিবারের অর্থনৈতিক প্রভাব সহ পূর্ণাঙ্গ খরচের হিসাবক।')

@push('styles')
<style>
@media print {
    nav, footer, .topbar, .inputs, .actions, .notinc, .helpgrid, .page-head {
        display: none !important;
    }
    body {
        background: #fff !important;
        min-width: 100% !important;
    }
    .layout {
        display: block !important;
    }
    .headline, .sec {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}
</style>
@endpush

@section('content')
{{-- Page Header --}}
<div class="bg-white border-b border-line py-9 pb-7">
    <div class="max-w-[1240px] mx-auto px-4 md:px-10">
        <div class="text-[11.5px] uppercase tracking-widest text-slate-400 font-semibold mb-2.5 font-bn">
            খরচের হিসাব
        </div>
        <h1 class="font-serif text-3xl md:text-[34px] font-medium tracking-tight text-ink leading-tight mb-2.5">
            চিকিৎসায় আনুমানিক কত লাগতে পারে
        </h1>
        <p class="text-[15px] text-slate-500 leading-relaxed max-w-[720px] font-bn">
            শুধু হাসপাতালের বিল নয় — যাতায়াত, থাকা, বাইরে থেকে ওষুধ কেনা আর সাথের লোকের আয় বন্ধ থাকা, সবকিছু ধরে হিসাব। বাংলাদেশে এই খরচগুলোই পরিবারকে সবচেয়ে বেশি চাপে ফেলে।
        </p>
    </div>
</div>

{{-- Main Estimator Body --}}
<div class="py-7 pb-20 bg-mist min-h-[700px]"
     x-data="costEstimator({
         initialInputs: @js($initialEstimate['inputs'] ?? []),
         initialEstimate: @js($initialEstimate ?? [])
     })">
    <div class="max-w-[1240px] mx-auto px-4 md:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-[330px_1fr] gap-6 items-start">

            {{-- 1. Input Controls (Left Column) --}}
            <div class="bg-white border border-line rounded-[18px] p-6 sticky top-[90px] shadow-sm">
                <div class="text-[13px] font-semibold mb-4 flex items-center gap-2 font-bn text-ink">
                    <i class="ti ti-adjustments text-[17px] text-slate-500"></i>
                    <span>আপনার তথ্য দিন</span>
                </div>

                {{-- Cancer Type --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">ক্যান্সারের ধরন</label>
                    <select class="w-full p-2.5 px-3.5 border border-line rounded-[11px] text-[14px] text-ink bg-white font-bn focus:outline-none focus:border-slate-700 cursor-pointer"
                            x-model="type"
                            @change="setCancerType($event.target.value)">
                        <option value="breast">স্তন ক্যান্সার</option>
                        <option value="lung">ফুসফুস ক্যান্সার</option>
                        <option value="cervical">জরায়ু মুখের ক্যান্সার</option>
                        <option value="blood">রক্তের ক্যান্সার (লিউকেমিয়া)</option>
                        <option value="stomach">পাকস্থলী ক্যান্সার</option>
                        <option value="oral">মুখের ক্যান্সার</option>
                        <option value="colon">কোলন ক্যান্সার</option>
                        <option value="prostate">প্রোস্টেট ক্যান্সার</option>
                    </select>
                </div>

                {{-- Stage --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">স্টেজ</label>
                    <div class="flex gap-1.5">
                        <button type="button" @click="setStage('1')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="stage === '1' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">১</button>
                        <button type="button" @click="setStage('2')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="stage === '2' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">২</button>
                        <button type="button" @click="setStage('3')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="stage === '3' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">৩</button>
                        <button type="button" @click="setStage('4')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="stage === '4' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">৪</button>
                    </div>
                </div>

                {{-- Treatments --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">যেসব চিকিৎসা লাগবে</label>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5 text-[13px] py-1.5 cursor-pointer font-bn select-none transition"
                             @click="toggleTreat('surgery')"
                             :class="treat.surgery ? 'text-ink font-semibold' : 'text-slate-500'">
                            <div class="w-[17px] h-[17px] rounded-[5px] border flex items-center justify-center shrink-0"
                                 :class="treat.surgery ? 'bg-slate-900 border-slate-900 text-white' : 'border-line text-transparent'">
                                <i class="ti ti-check text-[11px]"></i>
                            </div>
                            <span>অপারেশন</span>
                        </div>

                        <div class="flex items-center gap-2.5 text-[13px] py-1.5 cursor-pointer font-bn select-none transition"
                             @click="toggleTreat('chemo')"
                             :class="treat.chemo ? 'text-ink font-semibold' : 'text-slate-500'">
                            <div class="w-[17px] h-[17px] rounded-[5px] border flex items-center justify-center shrink-0"
                                 :class="treat.chemo ? 'bg-slate-900 border-slate-900 text-white' : 'border-line text-transparent'">
                                <i class="ti ti-check text-[11px]"></i>
                            </div>
                            <span>কেমোথেরাপি</span>
                        </div>

                        <div class="flex items-center gap-2.5 text-[13px] py-1.5 cursor-pointer font-bn select-none transition"
                             @click="toggleTreat('radiation')"
                             :class="treat.radiation ? 'text-ink font-semibold' : 'text-slate-500'">
                            <div class="w-[17px] h-[17px] rounded-[5px] border flex items-center justify-center shrink-0"
                                 :class="treat.radiation ? 'bg-slate-900 border-slate-900 text-white' : 'border-line text-transparent'">
                                <i class="ti ti-check text-[11px]"></i>
                            </div>
                            <span>রেডিওথেরাপি</span>
                        </div>

                        <div class="flex items-center gap-2.5 text-[13px] py-1.5 cursor-pointer font-bn select-none transition"
                             @click="toggleTreat('targeted')"
                             :class="treat.targeted ? 'text-ink font-semibold' : 'text-slate-500'">
                            <div class="w-[17px] h-[17px] rounded-[5px] border flex items-center justify-center shrink-0"
                                 :class="treat.targeted ? 'bg-slate-900 border-slate-900 text-white' : 'border-line text-transparent'">
                                <i class="ti ti-check text-[11px]"></i>
                            </div>
                            <span>টার্গেটেড থেরাপি</span>
                        </div>
                    </div>
                </div>

                {{-- District Distance --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">কোন জেলা থেকে আসবেন</label>
                    <select class="w-full p-2.5 px-3.5 border border-line rounded-[11px] text-[14px] text-ink bg-white font-bn focus:outline-none focus:border-slate-700 cursor-pointer"
                            x-model="dist"
                            @change="setDist($event.target.value)">
                        <option value="local">ঢাকার ভেতরে</option>
                        <option value="near">ঢাকার কাছাকাছি (৫০–১৫০ কিমি)</option>
                        <option value="far">দূরের জেলা (১৫০+ কিমি)</option>
                    </select>
                </div>

                {{-- Attendants --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">সাথে কতজন থাকবেন</label>
                    <div class="flex gap-1.5">
                        <button type="button" @click="setAtt(1)" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="att === 1 ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">১ জন</button>
                        <button type="button" @click="setAtt(2)" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="att === 2 ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">২ জন</button>
                    </div>
                </div>

                {{-- Income Loss --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-slate-500 mb-1.5 block font-bn">সাথের লোকের মাসিক আয় বন্ধ হবে?</label>
                    <div class="flex gap-1.5">
                        <button type="button" @click="setLoss('yes')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="loss === 'yes' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">হ্যাঁ</button>
                        <button type="button" @click="setLoss('no')" class="flex-1 text-[12.5px] py-2 rounded-[10px] border font-bn font-medium transition cursor-pointer"
                                :class="loss === 'no' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-600 hover:border-slate-300'">না</button>
                    </div>
                </div>

                {{-- Input Note --}}
                <div class="text-[11.5px] text-slate-400 leading-relaxed pt-3.5 border-t border-line-soft font-bn">
                    এই হিসাব প্রকাশ্য তথ্য ও রোগীদের বাস্তব অভিজ্ঞতার ভিত্তিতে আনুমানিক। প্রকৃত খরচ রোগীভেদে আলাদা হতে পারে — এটি কোনো কোটেশন নয়।
                </div>
            </div>

            {{-- 2. Results & Breakdowns (Right Column) --}}
            <div class="space-y-4">

                {{-- Top Headline Box --}}
                <div class="bg-white border-2 border-slate-900 rounded-[20px] p-6 md:p-[28px_32px] shadow-sm">
                    <div class="text-[12px] text-slate-400 uppercase tracking-widest font-semibold mb-2 font-bn">
                        আনুমানিক মোট খরচ — <span x-text="hosp === 'govt' ? 'সরকারি হাসপাতালে' : (hosp === 'npo' ? 'অলাভজনক হাসপাতালে' : 'বেসরকারি হাসপাতালে')">সরকারি হাসপাতালে</span>
                    </div>

                    <div class="font-serif text-3xl md:text-[44px] font-semibold tracking-tight text-ink leading-none mb-2"
                         x-text="fmt(estimate?.min_range) + ' – ' + fmt(estimate?.max_range)">
                        {{ '৳' . number_format($initialEstimate['min_range']) }} – {{ '৳' . number_format($initialEstimate['max_range']) }}
                    </div>

                    <div class="text-[14px] text-slate-600 leading-relaxed mb-5 font-bn">
                        <span x-text="estimate?.cancer_type_name_bn">{{ $initialEstimate['cancer_type_name_bn'] }}</span> ·
                        স্টেজ <span x-text="bnNum(stage)">২</span> ·
                        আনুমানিক <span x-text="bnNum(estimate?.months)">{{ $initialEstimate['months'] }}</span> মাসের চিকিৎসা
                    </div>

                    {{-- 3 Split Stats --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-4 border-t border-line">
                        <div class="bg-mist rounded-[13px] p-4">
                            <div class="text-[12px] text-slate-600 mb-1 flex items-center gap-1.5 font-bn">
                                <i class="ti ti-stethoscope text-teal-700 text-sm"></i>
                                <span>চিকিৎসার খরচ</span>
                            </div>
                            <div class="font-serif text-[21px] font-semibold text-ink" x-text="fmt(estimate?.direct_cost)">
                                {{ '৳' . number_format($initialEstimate['direct_cost']) }}
                            </div>
                            <div class="text-[11.5px] text-slate-400 font-bn mt-0.5" x-text="'মোটের ' + bnNum(estimate?.direct_percent) + '%'">
                                মোটের {{ $initialEstimate['direct_percent'] }}%
                            </div>
                        </div>

                        <div class="bg-mist rounded-[13px] p-4">
                            <div class="text-[12px] text-slate-600 mb-1 flex items-center gap-1.5 font-bn">
                                <i class="ti ti-bus text-gold text-sm"></i>
                                <span>আনুষঙ্গিক খরচ</span>
                            </div>
                            <div class="font-serif text-[21px] font-semibold text-ink" x-text="fmt(estimate?.indirect_cost)">
                                {{ '৳' . number_format($initialEstimate['indirect_cost']) }}
                            </div>
                            <div class="text-[11.5px] text-slate-400 font-bn mt-0.5" x-text="'মোটের ' + bnNum(estimate?.indirect_percent) + '%'">
                                মোটের {{ $initialEstimate['indirect_percent'] }}%
                            </div>
                        </div>

                        <div class="bg-mist rounded-[13px] p-4">
                            <div class="text-[12px] text-slate-600 mb-1 flex items-center gap-1.5 font-bn">
                                <i class="ti ti-calendar-month text-pink-600 text-sm"></i>
                                <span>মাসে গড়ে</span>
                            </div>
                            <div class="font-serif text-[21px] font-semibold text-ink" x-text="fmt(estimate?.monthly_avg)">
                                {{ '৳' . number_format($initialEstimate['monthly_avg']) }}
                            </div>
                            <div class="text-[11.5px] text-slate-400 font-bn mt-0.5" x-text="bnNum(estimate?.months) + ' মাস ধরে'">
                                {{ $initialEstimate['months'] }} মাস ধরে
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section: হাসপাতালের ধরন বদলে দেখুন --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[26px_28px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5 font-bn">
                        হাসপাতালের ধরন বদলে দেখুন
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                        একই চিকিৎসা, ভিন্ন জায়গায় — পার্থক্যটা বিশাল। সরকারি হাসপাতালে অপেক্ষা বেশি হলেও খরচ কয়েকগুণ কম।
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Govt --}}
                        <div class="border-1.5 rounded-[14px] p-4 md:p-[18px_20px] cursor-pointer transition"
                             @click="setHosp('govt')"
                             :class="hosp === 'govt' ? 'border-slate-900 bg-off shadow-sm' : 'border-line hover:border-slate-300'">
                            <div class="text-[14px] font-semibold font-bn flex items-center gap-1.5 mb-1 text-ink">
                                <span>সরকারি</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-teal-100 text-teal-700">সবচেয়ে সাশ্রয়ী</span>
                            </div>
                            <div class="font-serif text-[24px] font-semibold text-ink my-2" x-text="fmt(estimate?.comparisons?.govt?.amount) + '+'">
                                {{ '৳' . number_format($initialEstimate['comparisons']['govt']['amount']) }}+
                            </div>
                            <div class="text-[12px] text-slate-500 leading-relaxed font-bn">
                                NICRH, মেডিকেল কলেজ · অপেক্ষা ৮–১২ সপ্তাহ
                            </div>
                        </div>

                        {{-- NPO --}}
                        <div class="border-1.5 rounded-[14px] p-4 md:p-[18px_20px] cursor-pointer transition"
                             @click="setHosp('npo')"
                             :class="hosp === 'npo' ? 'border-slate-900 bg-off shadow-sm' : 'border-line hover:border-slate-300'">
                            <div class="text-[14px] font-semibold font-bn flex items-center gap-1.5 mb-1 text-ink">
                                <span>অলাভজনক</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-gold-soft text-[#7A5410]">মাঝামাঝি</span>
                            </div>
                            <div class="font-serif text-[24px] font-semibold text-ink my-2" x-text="fmt(estimate?.comparisons?.npo?.amount) + '+'">
                                {{ '৳' . number_format($initialEstimate['comparisons']['npo']['amount']) }}+
                            </div>
                            <div class="text-[12px] text-slate-500 leading-relaxed font-bn">
                                ট্রাস্ট ও দাতব্য হাসপাতাল · অপেক্ষা ৩–৫ সপ্তাহ
                            </div>
                        </div>

                        {{-- Private --}}
                        <div class="border-1.5 rounded-[14px] p-4 md:p-[18px_20px] cursor-pointer transition"
                             @click="setHosp('priv')"
                             :class="hosp === 'priv' || hosp === 'private' ? 'border-slate-900 bg-off shadow-sm' : 'border-line hover:border-slate-300'">
                            <div class="text-[14px] font-semibold font-bn flex items-center gap-1.5 mb-1 text-ink">
                                <span>বেসরকারি</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-[#E6F1FB] text-[#0C447C]">দ্রুততম</span>
                            </div>
                            <div class="font-serif text-[24px] font-semibold text-ink my-2" x-text="fmt(estimate?.comparisons?.priv?.amount) + '+'">
                                {{ '৳' . number_format($initialEstimate['comparisons']['priv']['amount']) }}+
                            </div>
                            <div class="text-[12px] text-slate-500 leading-relaxed font-bn">
                                স্কয়ার, এভারকেয়ার · অপেক্ষা ১–২ সপ্তাহ
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section: কোন ধাপে কত খরচ --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[26px_28px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5 font-bn">
                        কোন ধাপে কত খরচ
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-5 font-bn">
                        পুরো টাকা একসাথে লাগে না। ধাপে ধাপে কত লাগবে সেটা জানলে আর্থিক পরিকল্পনা সহজ হয়।
                    </div>

                    <div class="space-y-4">
                        <template x-for="(phase, idx) in estimate?.phases" :key="idx">
                            <div class="flex gap-4 items-start">
                                <div class="flex flex-col items-center w-6 shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-mist text-slate-600 flex items-center justify-center text-xs font-semibold"
                                         x-text="bnNum(idx + 1)"></div>
                                    <div class="w-[1.5px] bg-line flex-1 min-h-[16px] mt-1" x-show="idx < estimate?.phases?.length - 1"></div>
                                </div>
                                <div class="flex-1 pb-4">
                                    <div class="flex justify-between items-baseline gap-3 mb-0.5 font-bn">
                                        <span class="text-[15px] font-semibold text-ink" x-text="phase.t"></span>
                                        <span class="text-[15px] font-semibold text-ink whitespace-nowrap" x-text="fmt(phase.amt)"></span>
                                    </div>
                                    <div class="text-[12px] text-slate-400 mb-2 font-bn" x-text="phase.w"></div>
                                    <div class="space-y-1 bg-mist/60 rounded-xl p-3">
                                        <template x-for="(item, iIdx) in phase.items" :key="iIdx">
                                            <div class="flex justify-between text-[13px] text-slate-600 font-bn leading-relaxed">
                                                <span x-text="item[0]"></span>
                                                <span class="text-ink font-medium whitespace-nowrap ml-3" x-text="fmt(item[1])"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Section: মাসে কত টাকা লাগতে পারে --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[26px_28px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5 font-bn">
                        মাসে কত টাকা লাগতে পারে
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                        চিকিৎসার মাঝামাঝি সময়ে খরচ সবচেয়ে বেশি হয় — সেই মাসগুলোর জন্য আগে থেকে প্রস্তুতি নিন।
                    </div>

                    {{-- Chart Bars --}}
                    <div class="flex items-end gap-1.5 md:gap-2.5 h-[160px] pt-4 mb-3">
                        <template x-for="item in estimate?.monthly_flow" :key="item.month">
                            <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                <span class="text-[10px] text-slate-500 font-sans whitespace-nowrap opacity-80 group-hover:opacity-100 font-semibold"
                                      x-text="fmt(item.total).replace('৳','')"></span>
                                <div class="w-full flex flex-col justify-end transition duration-300 rounded-t-md overflow-hidden"
                                     :style="`height: ${item.height_percent}%;`">
                                    <div class="w-full bg-slate-300" :style="`height: ${100 - item.direct_percent}%;`"></div>
                                    <div class="w-full bg-slate-900" :style="`height: ${item.direct_percent}%;`"></div>
                                </div>
                                <span class="text-[11px] text-slate-400 font-bn" x-text="bnNum(item.month) + 'ম'"></span>
                            </div>
                        </template>
                    </div>

                    <div class="text-[12.5px] text-slate-400 leading-relaxed pt-3 border-t border-line font-bn flex items-center gap-4 flex-wrap">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-slate-900 inline-block"></span> গাঢ় অংশ: চিকিৎসার সরাসরি খরচ</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-slate-300 inline-block"></span> হালকা অংশ: যাতায়াত, থাকা ও ওষুধের খরচ</span>
                    </div>
                </div>

                {{-- Section: আনুষঙ্গিক খরচ — যা সবাই ভুলে যায় --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[26px_28px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5 font-bn">
                        আনুষঙ্গিক খরচ — যা সবাই ভুলে যায়
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                        বাংলাদেশে দূরের জেলা থেকে আসা পরিবারের ক্ষেত্রে এই খরচ প্রায়ই মোট আর্থিক বোঝার এক-তৃতীয়াংশ হয়ে দাঁড়ায়।
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="(item, idx) in estimate?.indirect_items" :key="idx">
                            <div class="border border-line rounded-[13px] p-4 flex flex-col justify-between">
                                <div class="flex justify-between items-baseline mb-1 font-bn">
                                    <span class="text-[13.5px] font-semibold text-ink flex items-center gap-1.5">
                                        <i class="ti text-slate-500 text-[16px]" :class="item.icon"></i>
                                        <span x-text="item.title_bn"></span>
                                    </span>
                                    <span class="text-[14px] font-semibold text-ink whitespace-nowrap" x-text="fmt(item.amount)"></span>
                                </div>
                                <div class="text-[12.5px] text-slate-500 font-bn leading-relaxed" x-text="item.desc_bn"></div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Section: এই হিসাবে যা ধরা হয়নি --}}
                <div class="bg-gold-soft border border-gold-line rounded-[14px] p-5 flex gap-3.5 shadow-sm">
                    <i class="ti ti-alert-triangle text-gold text-2xl shrink-0 mt-0.5"></i>
                    <div>
                        <div class="text-[14px] font-semibold text-[#7A5410] mb-1.5 font-bn">এই হিসাবে যা ধরা হয়নি</div>
                        <div class="text-[13.5px] text-[#8A6218] leading-relaxed font-bn">
                            জটিলতা দেখা দিলে অতিরিক্ত ভর্তি ও আইসিইউ খরচ, রক্ত সংগ্রহে সমস্যা হলে বাড়তি খরচ, চিকিৎসার পর বছরে ২–৩ বার ফলো-আপ স্ক্যান, এবং রোগ ফিরে এলে নতুন করে চিকিৎসা। এছাড়া টার্গেটেড থেরাপির ওষুধের দাম বছরে ওঠানামা করে। মোট বাজেটের ওপর অন্তত ২০% বাড়তি ইমার্জেন্সি তহবিল রাখার পরামর্শ দেওয়া হয়।
                        </div>
                    </div>
                </div>

                {{-- Section: খরচ কমানোর উপায় --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[26px_28px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-1.5 font-bn">
                        খরচ কমানোর উপায়
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                        বাংলাদেশে যেসব সহায়তা আসলে সাধারণ রোগীদের জন্য পাওয়া যায়।
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="border border-line rounded-[13px] p-4.5">
                            <div class="text-[14px] font-semibold text-ink mb-1 flex items-center gap-2 font-bn">
                                <i class="ti ti-building-bank text-pink-700 text-base"></i>
                                <span>সমাজসেবা অধিদপ্তরের তহবিল</span>
                            </div>
                            <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                দরিদ্র ক্যান্সার রোগীদের জন্য এককালীন ৫০,০০০ টাকা পর্যন্ত সরকারি সহায়তা। হাসপাতালের সমাজসেবা কর্মকর্তার কাছে আবেদন করতে হয় — আয়ের সনদ ও NID লাগে।
                            </div>
                        </div>

                        <div class="border border-line rounded-[13px] p-4.5">
                            <div class="text-[14px] font-semibold text-ink mb-1 flex items-center gap-2 font-bn">
                                <i class="ti ti-building-hospital text-pink-700 text-base"></i>
                                <span>সরকারি হাসপাতালে চিকিৎসা</span>
                            </div>
                            <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                একই মানের চিকিৎসা ৬০–৮০% কম খরচে। অপেক্ষা বেশি হলেও ডাক্তাররা প্রায়ই একই — অনেকে সরকারি ও বেসরকারি উভয় জায়গাতেই চিকিৎসা দেন।
                            </div>
                        </div>

                        <div class="border border-line rounded-[13px] p-4.5">
                            <div class="text-[14px] font-semibold text-ink mb-1 flex items-center gap-2 font-bn">
                                <i class="ti ti-pill text-pink-700 text-base"></i>
                                <span>জেনেরিক ওষুধ</span>
                            </div>
                            <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                বাংলাদেশে শীর্ষ কোম্পানিগুলোর তৈরি জেনেরিক কেমো ওষুধ আমদানি করা ব্রান্ডের চেয়ে অনেক সস্তা। ডাক্তারের সাথে দেশীয় বিকল্প নিয়ে কথা বলুন।
                            </div>
                        </div>

                        <div class="border border-line rounded-[13px] p-4.5">
                            <div class="text-[14px] font-semibold text-ink mb-1 flex items-center gap-2 font-bn">
                                <i class="ti ti-heart-handshake text-pink-700 text-base"></i>
                                <span>যাচাইকৃত সহায়তার তালিকা</span>
                            </div>
                            <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                CCB-তে যাচাই করা রোগীদের তালিকা আছে যেখানে সরাসরি বিকাশ/নগদে সাহায্য পাঠানো যায়। আপনার পরিবারের জরুরি প্রয়োজন থাকলে যোগাযোগ করুন।
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-2 flex-wrap">
                    <button type="button" @click="printEstimate()" class="text-sm px-5 py-3 rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-700 font-bn transition inline-flex items-center gap-2 shadow-sm cursor-pointer">
                        <i class="ti ti-printer text-base"></i>
                        <span>হিসাবটি প্রিন্ট করুন</span>
                    </button>
                    <a href="{{ route('hospitals.index') }}" class="text-sm px-5 py-3 rounded-xl bg-white border border-line text-ink font-medium hover:bg-mist font-bn transition inline-flex items-center gap-2">
                        <i class="ti ti-building-hospital text-base"></i>
                        <span>হাসপাতাল দেখুন</span>
                    </a>
                    <a href="{{ route('doctors.index') }}" class="text-sm px-5 py-3 rounded-xl bg-white border border-line text-ink font-medium hover:bg-mist font-bn transition inline-flex items-center gap-2">
                        <i class="ti ti-stethoscope text-base"></i>
                        <span>ডাক্তার খুঁজুন</span>
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
