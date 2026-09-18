@extends('layouts.app')

@section('title', $patientCase->display_name_bn . ' — রোগীর সহায়তা | Cancer Care Bangladesh')
@section('meta_description', $patientCase->display_name_bn . ' (' . ($patientCase->cancerType?->name_bn ?? '') . ')। প্রয়োজনীয় সহায়তা: ৳' . number_format($patientCase->amount_needed) . '। সরাসরি রোগীর অ্যাকাউন্টে সাহায্য পাঠান।')

@section('content')
<div class="bg-mist py-8 md:py-10">
    <div class="max-w-[1240px] mx-auto px-6 md:px-10">

        {{-- BACK LINK --}}
        <div class="mb-5">
            <a href="{{ route('patients.index') }}" class="font-bn text-sm text-slate-500 hover:text-ink inline-flex items-center gap-1.5 font-medium transition-colors">
                <i class="ti ti-arrow-left text-base"></i>
                সকল রোগীর তালিকায় ফিরুন
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-7 items-start">

            {{-- LEFT MAIN COLUMN --}}
            <div class="space-y-5">

                {{-- HERO PROFILE BOX --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col sm:flex-row gap-5 items-start pb-6 mb-6 border-b border-line">
                        @if($patientCase->show_photo && $patientCase->photo_path)
                            <img src="{{ Storage::disk('s3_public')->url($patientCase->photo_path) }}"
                                 alt="{{ $patientCase->display_name_bn }}"
                                 class="w-24 h-24 rounded-2xl object-cover shrink-0 bg-mist border border-line shadow-sm">
                        @else
                            <div class="w-24 h-24 rounded-2xl shrink-0 bg-slate-100 border border-line flex items-center justify-center font-bn font-semibold text-2xl text-slate-500 shadow-sm">
                                {{ mb_substr($patientCase->display_name_bn, 0, 2) }}
                            </div>
                        @endif

                        <div class="flex-1">
                            <h1 class="font-serif text-2xl md:text-3xl font-semibold tracking-[-0.015em] text-ink mb-1.5">
                                {{ $patientCase->display_name_bn }}
                            </h1>
                            <div class="font-bn text-[14.5px] text-slate-600 leading-relaxed mb-3.5">
                                <span>{{ $patientCase->age }} বছর</span> ·
                                <span>{{ $patientCase->cancerType?->name_bn }}@if($patientCase->stage), {{ $patientCase->stage }}@endif</span> ·
                                <span>{{ $patientCase->district?->name_bn }}</span>
                                @if($patientCase->hospital)
                                    <div class="text-slate-500 text-sm mt-0.5">
                                        চিকিৎসাধীন: {{ $patientCase->hospital->name_bn }}
                                        @if($patientCase->treating_doctor_name) ({{ $patientCase->treating_doctor_name }}) @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 font-bn">
                                <span class="text-[11.5px] px-3 py-1 rounded-full font-medium bg-teal-100 text-teal-700 flex items-center gap-1">
                                    <i class="ti ti-rosette-discount-check text-sm"></i>
                                    কাগজপত্র যাচাইকৃত
                                </span>
                                <span class="text-[11.5px] px-3 py-1 rounded-full font-medium bg-mist text-slate-600 border border-line">
                                    কেস: {{ $patientCase->case_code }}
                                </span>
                                @if($patientCase->expires_at)
                                    @php
                                        $daysLeft = (int) now()->diffInDays($patientCase->expires_at, false);
                                    @endphp
                                    <span class="text-[11.5px] px-3 py-1 rounded-full font-medium bg-mist text-slate-600 border border-line">
                                        সক্রিয় — আর {{ max(0, $daysLeft) }} দিন
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ESTIMATED NEED BLOCK --}}
                    <div class="bg-mist/80 rounded-2xl p-5 md:p-6 mb-7 border border-line/60">
                        <div class="font-bn text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">চিকিৎসার আনুমানিক প্রয়োজন</div>
                        <div class="font-serif text-3xl md:text-4xl font-semibold text-ink leading-none mb-3">৳{{ number_format($patientCase->amount_needed) }}</div>
                        <div class="font-bn text-[13.5px] text-slate-500 leading-relaxed">
                            এটি হাসপাতালের দেওয়া হিসাব অনুযায়ী আনুমানিক প্রয়োজন। <b class="text-ink font-semibold">CCB কোনো টাকা সংগ্রহ করে না</b> — আপনি সরাসরি {{ $patientCase->display_name_bn }}-এর নিজস্ব অ্যাকাউন্টে সাহায্য পাঠাবেন। কত অনুদান উঠেছে তা CCB সংরক্ষণ বা প্রকাশ করে না।
                        </div>
                    </div>

                    {{-- STORY SECTION --}}
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-3.5 font-bn">রোগীর কথা</div>
                    <div class="font-bn text-[15px] text-slate-600 leading-[1.85] space-y-3.5">
                        {!! nl2br(e($patientCase->story_bn)) !!}
                    </div>
                </div>

                {{-- VERIFICATION TIMELINE --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-8 shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-5 font-bn">যাচাইকরণের ধাপসমূহ</div>

                    <div class="space-y-4 font-bn">
                        @php
                            $stepNames = [
                                'documents' => 'হাসপাতালের কাগজপত্র যাচাই',
                                'hospital_confirm' => 'চিকিৎসক ও হাসপাতাল নিশ্চিতকরণ',
                                'identity' => 'জাতীয় পরিচয়পত্র ও একাউন্ট যাচাই',
                                'field_meeting' => 'সরাসরি সাক্ষাৎ ও সম্মতিপত্র',
                            ];
                        @endphp

                        @foreach($stepNames as $stepKey => $stepTitle)
                            @php
                                $v = $patientCase->verifications->first(fn($item) => (($item->step instanceof \App\Enums\PatientCaseVerificationStep) ? $item->step->value : $item->step) === $stepKey);
                                $isDone = $v && ((($v->status instanceof \App\Enums\PatientCaseVerificationStatus) ? $v->status->value : $v->status) === 'done');
                            @endphp

                            <div class="flex items-start gap-4">
                                <div class="flex flex-col items-center shrink-0">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold {{ $isDone ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-400' }}">
                                        @if($isDone)
                                            <i class="ti ti-check text-sm"></i>
                                        @else
                                            <i class="ti ti-clock text-sm"></i>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-0.5 bg-line flex-1 min-h-[22px] my-1"></div>
                                    @endif
                                </div>

                                <div class="flex-1 pb-2">
                                    <div class="text-[14.5px] font-medium text-ink flex items-center justify-between">
                                        <span>{{ $stepTitle }}</span>
                                        @if($isDone)
                                            <span class="text-xs text-teal-600 font-normal">সম্পন্ন</span>
                                        @else
                                            <span class="text-xs text-slate-400 font-normal">পেন্ডিং</span>
                                        @endif
                                    </div>
                                    <div class="text-[13.5px] text-slate-500 mt-1 leading-relaxed">
                                        {{ $v?->note_bn ?? 'এই ধাপের তথ্য যাচাই সম্পন্ন করা হয়েছে।' }}
                                    </div>
                                    @if($v?->completed_at)
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ $v->completed_at->translatedFormat('d F Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- VERIFIED DOCUMENTS --}}
                @if($patientCase->documents->count() > 0)
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-8 shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-4 font-bn">যাচাইকৃত নথি</div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 font-bn">
                            @foreach($patientCase->documents as $doc)
                                <div class="border border-line rounded-xl p-3.5 flex items-center gap-3 bg-off">
                                    <div class="w-9 h-9 rounded-lg bg-mist flex items-center justify-center shrink-0 text-slate-500">
                                        <i class="ti ti-file-text text-lg"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[13.5px] font-medium text-ink truncate">
                                            {{ $doc->type instanceof \App\Enums\PatientCaseDocumentType ? $doc->type->labelBn() : $doc->type }}
                                        </div>
                                        <div class="text-xs text-slate-400">যাচাইকৃত কপি (রেড্যাক্টেড)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="font-bn text-xs text-slate-400 mt-3.5 flex items-center gap-1.5">
                            <i class="ti ti-shield-lock text-sm"></i>
                            গোপনীয়তার স্বার্থে রোগীর ব্যক্তিগত তথ্য (এনআইডি নম্বর, ফোন, পূর্ণ ঠিকানা) রেড্যাক্ট / ঢেকে দেওয়া হয়েছে।
                        </div>
                    </div>
                @endif

                {{-- ITEMIZED COST BREAKDOWN --}}
                @if($patientCase->costs->count() > 0)
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-8 shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-4 font-bn">খরচের ভাঙা হিসাব</div>

                        <table class="w-full text-sm font-bn">
                            <tbody class="divide-y divide-line/60">
                                @foreach($patientCase->costs as $cost)
                                    <tr>
                                        <td class="py-2.5 text-slate-600">{{ $cost->item_bn }}</td>
                                        <td class="py-2.5 text-right font-medium text-ink">৳{{ number_format($cost->amount) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="font-semibold text-ink border-t-2 border-line">
                                    <td class="pt-3.5">মোট আনুমানিক প্রয়োজন</td>
                                    <td class="pt-3.5 text-right font-serif text-base">৳{{ number_format($patientCase->amount_needed) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="font-bn text-xs text-slate-400 mt-3.5">
                            হাসপাতালের প্রাক্কলন অনুযায়ী তৈরি। চিকিৎসার প্রতিক্রিয়াভেদে প্রকৃত খরচ কমবেশি হতে পারে।
                        </div>
                    </div>
                @endif

                {{-- PATIENT UPDATES --}}
                @if($patientCase->updates->count() > 0)
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-8 shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-4 font-bn">চিকিৎসার সর্বশেষ অগ্রগতি ও আপডেট</div>
                        <div class="space-y-3 font-bn">
                            @foreach($patientCase->updates as $update)
                                <div class="bg-mist/60 rounded-xl p-4 border border-line/60">
                                    <div class="text-xs text-slate-400 mb-1">
                                        {{ $update->update_date ? $update->update_date->translatedFormat('d F Y') : '' }}
                                    </div>
                                    <div class="text-[13.5px] text-slate-600 leading-relaxed">
                                        {{ $update->note_bn }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- RIGHT STICKY SIDEBAR --}}
            <div class="space-y-4">

                {{-- PAYBOX --}}
                <div class="bg-white border-2 border-pink-500 rounded-[18px] overflow-hidden shadow-md sticky top-24"
                     x-data="{ copiedNumber: null, copyText(text) { navigator.clipboard.writeText(text); this.copiedNumber = text; setTimeout(() => this.copiedNumber = null, 2000); } }">

                    <div class="p-6 border-b border-pink-600 bg-gradient-to-r from-pink-600 to-rose-600 text-white">
                        <div class="font-bn text-lg font-semibold mb-1">সরাসরি সাহায্য পাঠান</div>
                        <div class="font-bn text-[13px] text-white/70 leading-relaxed">
                            নিচের অ্যাকাউন্টগুলো {{ $patientCase->display_name_bn }}-এর নিজস্ব। CCB-র কোনো ভূমিকা নেই — টাকা সরাসরি তাঁর কাছে যাবে।
                        </div>
                    </div>

                    <div class="p-5 md:p-6 space-y-3.5">
                        @forelse($patientCase->accounts as $acc)
                            @php
                                $typeVal = ($acc->type instanceof \App\Enums\PatientCaseAccountType) ? $acc->type->value : (string) $acc->type;
                            @endphp

                            <div class="border border-line rounded-xl p-4 bg-off hover:border-slate-300 transition-colors">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($typeVal === 'bkash')
                                        <div class="w-9 h-9 rounded-lg bg-[#E2136E] text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            bKash
                                        </div>
                                        <div class="font-bn">
                                            <div class="text-sm font-semibold text-ink">বিকাশ (পার্সোনাল)</div>
                                            <div class="text-[11px] text-slate-400">Send Money</div>
                                        </div>
                                    @elseif($typeVal === 'nagad')
                                        <div class="w-9 h-9 rounded-lg bg-[#F6921E] text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            নগদ
                                        </div>
                                        <div class="font-bn">
                                            <div class="text-sm font-semibold text-ink">নগদ (পার্সোনাল)</div>
                                            <div class="text-[11px] text-slate-400">Send Money</div>
                                        </div>
                                    @elseif($typeVal === 'rocket')
                                        <div class="w-9 h-9 rounded-lg bg-[#8C3494] text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            Rocket
                                        </div>
                                        <div class="font-bn">
                                            <div class="text-sm font-semibold text-ink">রকেট (পার্সোনাল)</div>
                                            <div class="text-[11px] text-slate-400">Send Money</div>
                                        </div>
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-slate-700 text-white flex items-center justify-center shrink-0">
                                            <i class="ti ti-building-bank text-lg"></i>
                                        </div>
                                        <div class="font-bn">
                                            <div class="text-sm font-semibold text-ink">ব্যাংক অ্যাকাউন্ট</div>
                                            <div class="text-[11px] text-slate-400">বড় অঙ্কের অনুদানের জন্য</div>
                                        </div>
                                    @endif
                                </div>

                                {{-- NUMBER BOX --}}
                                <div class="bg-mist rounded-lg p-2.5 px-3 flex items-center justify-between gap-2 border border-line/60">
                                    <span class="font-mono text-sm md:text-base font-semibold text-ink tracking-wide select-all">
                                        {{ $acc->account_number }}
                                    </span>
                                    <button type="button"
                                            @click="copyText('{{ preg_replace('/[^0-9]/', '', $acc->account_number) }}')"
                                            class="font-bn text-xs px-2.5 py-1 rounded bg-white border border-line text-slate-700 hover:bg-pink-600 hover:text-white hover:border-pink-600 transition-colors font-medium">
                                        <span x-show="copiedNumber !== '{{ preg_replace('/[^0-9]/', '', $acc->account_number) }}'">কপি</span>
                                        <span x-show="copiedNumber === '{{ preg_replace('/[^0-9]/', '', $acc->account_number) }}'" class="text-teal-600 font-semibold" style="display:none;">কপি হয়েছে!</span>
                                    </button>
                                </div>

                                <div class="mt-2.5 font-bn text-xs text-slate-500 space-y-0.5">
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">অ্যাকাউন্টের নাম:</span>
                                        <span class="font-medium text-ink">{{ $acc->account_name }}</span>
                                    </div>
                                    @if($acc->bank_name)
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">ব্যাংক:</span>
                                            <span class="font-medium text-ink">{{ $acc->bank_name }}</span>
                                        </div>
                                    @endif
                                    @if($acc->branch)
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">শাখা:</span>
                                            <span class="font-medium text-ink">{{ $acc->branch }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="font-bn text-xs text-slate-500 text-center py-4">
                                অ্যাকাউন্ট নম্বর যাচাই প্রক্রিয়াধীন রয়েছে।
                            </div>
                        @endforelse
                    </div>

                    {{-- SCAM REMINDER --}}
                    <div class="bg-gold-soft border-t border-gold-line p-4 px-5 flex gap-2.5">
                        <i class="ti ti-shield-exclamation text-gold text-lg shrink-0 mt-0.5"></i>
                        <div class="font-bn text-xs text-[#8A6218] leading-relaxed">
                            CCB কখনো নিজের নম্বরে অনুদান চায় না। শুধু এই পাতায় দেখানো নম্বরেই পাঠাবেন। সন্দেহ হলে ০৯৬১১-৭৭৭৮৮৮ নম্বরে যাচাই করুন।
                        </div>
                    </div>

                    {{-- POST-DONATION HELPLINE --}}
                    <div class="p-4 px-5 bg-mist border-t border-line font-bn">
                        <div class="text-xs text-slate-500 leading-relaxed mb-3">
                            সাহায্য পাঠানোর পর জানাতে চাইলে হেল্পলাইনে বলতে পারেন — যাতে রোগীর প্রয়োজন পূরণ হলে আমরা দ্রুত তালিকা থেকে সরিয়ে নিতে পারি।
                        </div>
                        <a href="tel:09611777888"
                           class="w-full block text-center text-[13px] py-2.5 rounded-lg bg-white text-ink border border-line hover:border-slate-400 font-medium transition-colors">
                            <i class="ti ti-phone text-sm mr-1"></i>
                            হেল্পলাইনে জানান (০৯৬১১-৭৭৭৮৮৮)
                        </a>
                    </div>
                </div>

                {{-- SIDE CARD: DISCLAIMER --}}
                <div class="bg-white border border-line rounded-2xl p-5 shadow-sm font-bn">
                    <div class="text-xs font-semibold text-ink mb-2 flex items-center gap-1.5">
                        <i class="ti ti-info-circle text-slate-500 text-base"></i>
                        গুরুত্বপূর্ণ তথ্য ও দায়সীমা
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        CCB শুধু কাগজপত্র ও পারিবারিক অসচ্ছলতা যাচাই করে তথ্য প্রকাশ করেছে। অর্থ কীভাবে ব্যয় হবে বা চিকিৎসার ফলাফল কী হবে — সে বিষয়ে CCB কোনো নিশ্চয়তা বা দায় বহন করে না। অনুদানের সিদ্ধান্ত সম্পূর্ণ আপনার।
                    </p>
                </div>

                {{-- SIDE CARD: REPORT --}}
                <div class="bg-white border border-line rounded-2xl p-5 shadow-sm font-bn">
                    <div class="text-xs font-semibold text-ink mb-2 flex items-center gap-1.5">
                        <i class="ti ti-flag text-slate-500 text-base"></i>
                        কিছু অসঙ্গতি বা ভুল মনে হচ্ছে?
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">
                        এই কেস সম্পর্কে কোনো তথ্য অসত্য বা প্রতারণামূলক মনে হলে আমাদের জরুরি হেল্পলাইনে জানান। আমরা অবিলম্বে তদন্তপূর্বক ব্যবস্থা গ্রহণ করব।
                    </p>
                    <a href="tel:09611777888" class="text-xs text-pink-600 font-medium hover:underline inline-flex items-center gap-1">
                        রিপোর্ট করুন →
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
