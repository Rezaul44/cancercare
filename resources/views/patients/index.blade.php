@extends('layouts.app')

@section('title', 'রোগীদের সহায়তা — Cancer Care Bangladesh')
@section('meta_description', 'যাচাই করা রোগী, সরাসরি সহায়তা। কোনো কমিশন বা মধ্যস্বত্বভোগী নেই। সরাসরি রোগীর নিজস্ব বিকাশ বা ব্যাংক অ্যাকাউন্টে অনুদান পাঠান।')

@section('content')
<div class="bg-white border-b border-line py-9">
    <div class="max-w-[1240px] mx-auto px-6 md:px-10">
        <div class="text-[11.5px] tracking-[0.12em] uppercase text-slate-400 font-semibold mb-2.5 font-bn">রোগীদের সহায়তা</div>
        <h1 class="font-serif text-3xl md:text-[34px] font-medium tracking-[-0.018em] leading-tight text-ink mb-2.5">যাচাই করা রোগী, সরাসরি সহায়তা</h1>
        <p class="font-bn text-[15px] text-slate-500 leading-[1.68] max-w-[720px]">
            এই রোগীদের কাগজপত্র আমরা যাচাই করেছি। আপনি চাইলে সরাসরি তাঁদের bKash বা ব্যাংক অ্যাকাউন্টে সাহায্য পাঠাতে পারেন — CCB-র মাধ্যমে নয়, কোনো চার্জ বা মধ্যস্বত্বভোগী ছাড়াই।
        </p>
    </div>
</div>

<div class="bg-mist py-8 md:py-10">
    <div class="max-w-[1240px] mx-auto px-6 md:px-10">

        {{-- CCB ROLE BAND --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-7 mb-6 shadow-sm">
            <div class="font-bn text-[15px] font-semibold text-ink mb-2.5 flex items-center gap-2">
                <i class="ti ti-info-circle text-slate-500 text-lg"></i>
                CCB-র ভূমিকা কী
            </div>
            <p class="font-bn text-sm text-slate-500 leading-[1.75] mb-5">
                আমরা রোগীদের কাগজপত্র যাচাই করে তথ্য প্রকাশ করি — এটুকুই। <b class="text-ink font-semibold">CCB কোনো টাকা সংগ্রহ করে না, ধরে রাখে না, বিতরণও করে না।</b> আপনার সাহায্য সরাসরি রোগীর কাছে যায়, মাঝখানে আমরা থাকি না।
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-line/60">
                <div class="bg-teal-50/50 rounded-xl p-4 border border-teal-100">
                    <div class="font-bn text-xs font-semibold uppercase tracking-wider text-teal-700 mb-3">CCB যা করে</div>
                    <div class="flex flex-col gap-2 font-bn text-[13.5px] text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>হাসপাতালের কাগজ ও রিপোর্ট যাচাই</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>জাতীয় পরিচয়পত্র মিলিয়ে দেখা</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>পরিবারের সাথে সরাসরি কথা বলা</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>যাচাইকৃত তথ্য প্রকাশ করা</span>
                        </div>
                    </div>
                </div>

                <div class="bg-rose-50/40 rounded-xl p-4 border border-rose-100">
                    <div class="font-bn text-xs font-semibold uppercase tracking-wider text-pink-700 mb-3">CCB যা করে না</div>
                    <div class="flex flex-col gap-2 font-bn text-[13.5px] text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>টাকা সংগ্রহ বা হেফাজত</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>কোনো কমিশন বা চার্জ</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>টাকা কীভাবে খরচ হলো তার নিশ্চয়তা</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>চিকিৎসার ফলাফলের দায়</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SCAM WARNING --}}
        <div class="bg-gold-soft border border-gold-line rounded-xl p-4 md:p-5 flex gap-3.5 mb-7">
            <i class="ti ti-shield-exclamation text-gold text-2xl shrink-0 mt-0.5"></i>
            <div>
                <div class="font-bn text-sm font-semibold text-[#7A5410] mb-1">প্রতারণা থেকে সাবধান</div>
                <div class="font-bn text-[13.5px] text-[#8A6218] leading-[1.65]">
                    CCB কখনো নিজের নম্বরে টাকা চায় না, ফোন করে অনুদানও চায় না। শুধু এই ওয়েবসাইটে প্রকাশিত নম্বরেই পাঠাবেন। কেউ CCB-র নাম করে টাকা চাইলে <b>০৯৬১১-৭৭৭৮৮৮</b> নম্বরে জানান।
                </div>
            </div>
        </div>

        {{-- FILTER & SORT BAR --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="font-bn text-[14.5px] text-slate-500">
                <b class="text-ink font-semibold">{{ $cases->total() }} জন</b> যাচাইকৃত রোগী
            </div>

            <div class="flex flex-wrap items-center gap-2 font-bn">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'recent']) }}"
                   class="text-[12.5px] px-3.5 py-1.5 rounded-full border transition-colors {{ $sort === 'recent' ? 'bg-pink-600 border-pink-600 text-white font-medium shadow-xs' : 'bg-white border-line text-slate-600 hover:border-pink-300' }}">
                    সাম্প্রতিক যাচাই
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'urgent']) }}"
                   class="text-[12.5px] px-3.5 py-1.5 rounded-full border transition-colors {{ $sort === 'urgent' ? 'bg-pink-600 border-pink-600 text-white font-medium shadow-xs' : 'bg-white border-line text-slate-600 hover:border-pink-300' }}">
                    সবচেয়ে জরুরি
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount_desc']) }}"
                   class="text-[12.5px] px-3.5 py-1.5 rounded-full border transition-colors {{ $sort === 'amount_desc' ? 'bg-pink-600 border-pink-600 text-white font-medium shadow-xs' : 'bg-white border-line text-slate-600 hover:border-pink-300' }}">
                    প্রয়োজন — বেশি
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount_asc']) }}"
                   class="text-[12.5px] px-3.5 py-1.5 rounded-full border transition-colors {{ $sort === 'amount_asc' ? 'bg-pink-600 border-pink-600 text-white font-medium shadow-xs' : 'bg-white border-line text-slate-600 hover:border-pink-300' }}">
                    প্রয়োজন — কম
                </a>
            </div>
        </div>

        {{-- PATIENT CARDS LIST --}}
        @if($cases->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-9">
                @foreach($cases as $case)
                    <div class="bg-white border border-line rounded-[18px] overflow-hidden hover:border-pink-300 transition-all flex flex-col justify-between shadow-sm">
                        <div>
                            {{-- TOP SECTION --}}
                            <div class="p-5 pb-3.5 flex items-start gap-3.5">
                                @if($case->show_photo && $case->photo_path)
                                    <img src="{{ Storage::disk('s3_public')->url($case->photo_path) }}"
                                         alt="{{ $case->display_name_bn }}"
                                         class="w-14 h-14 rounded-full object-cover shrink-0 bg-mist border border-line">
                                @else
                                    <div class="w-14 h-14 rounded-full shrink-0 bg-slate-100 border border-line flex items-center justify-center font-bn font-semibold text-lg text-slate-500">
                                        {{ mb_substr($case->display_name_bn, 0, 2) }}
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="font-bn text-base font-semibold text-ink leading-tight truncate">
                                        {{ $case->display_name_bn }}, {{ $case->age }}
                                    </div>
                                    <div class="font-bn text-[13px] text-slate-500 mt-1 leading-snug">
                                        {{ $case->cancerType?->name_bn }} @if($case->stage) · {{ $case->stage }} @endif · {{ $case->district?->name_bn }}
                                    </div>
                                </div>

                                <span class="font-bn text-[11px] px-2.5 py-1 rounded-full font-semibold bg-teal-100 text-teal-700 shrink-0">
                                    সক্রিয়
                                </span>
                            </div>

                            {{-- AMOUNT NEEDED (NO PROGRESS BAR) --}}
                            <div class="px-5 pb-3.5 flex items-baseline gap-2">
                                <span class="font-serif text-2xl font-semibold text-ink">৳{{ number_format($case->amount_needed) }}</span>
                                <span class="font-bn text-[13px] text-slate-500">আনুমানিক প্রয়োজন</span>
                            </div>

                            {{-- STORY EXCERPT --}}
                            <div class="font-bn text-[13.5px] text-slate-600 leading-relaxed px-5 pb-4 line-clamp-2">
                                {{ $case->story_bn }}
                            </div>

                            {{-- VERIFICATION PROOFS CHECKLIST --}}
                            <div class="px-5 py-3.5 bg-mist/70 border-t border-b border-line/70 flex flex-col gap-1.5 font-bn text-[12.5px] text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-check text-teal-500 text-sm shrink-0"></i>
                                    <span>{{ $case->hospital?->name_bn ?? 'হাসপাতাল' }}-এর ভর্তি ও খরচের কাগজ</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ti ti-check text-teal-500 text-sm shrink-0"></i>
                                    <span>ডাক্তারের স্বাক্ষরিত চিকিৎসা পরিকল্পনা</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($case->age < 18)
                                        <i class="ti ti-lock text-slate-400 text-sm shrink-0"></i>
                                        <span class="text-slate-500">শিশু — সুরক্ষা নীতিতে ছবি লুকানো</span>
                                    @else
                                        <i class="ti ti-check text-teal-500 text-sm shrink-0"></i>
                                        <span>NID ও পরিবারের সাথে সরাসরি কথা</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER --}}
                        <div class="p-4 px-5 bg-white flex items-center justify-between gap-3">
                            <span class="font-bn text-xs text-slate-400">
                                যাচাই: {{ $case->verified_at ? $case->verified_at->translatedFormat('d F Y') : 'সম্প্রতি' }}
                            </span>
                            <a href="{{ route('patients.show', $case->case_code) }}"
                               class="font-bn text-[13.5px] px-4 py-2 rounded-lg bg-pink-600 text-white hover:bg-pink-700 border border-pink-500 shadow-sm transition-all font-semibold">
                                বিস্তারিত ও যোগাযোগ
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            <div class="mb-9">
                {{ $cases->links() }}
            </div>
        @else
            <div class="bg-white border border-line rounded-2xl p-12 text-center my-8 shadow-sm">
                <i class="ti ti-user-search text-slate-400 text-4xl mb-3 block"></i>
                <h3 class="font-bn text-lg font-semibold text-ink mb-1">বর্তমানে কোনো সক্রিয় রোগী কেস পাওয়া যায়নি</h3>
                <p class="font-bn text-sm text-slate-500 max-w-[420px] mx-auto">
                    নতুন কেসের যাচাই প্রক্রিয়া চলমান রয়েছে। যাচাই শেষ হওয়া মাত্রই তালিকাটি হালনাগাদ করা হবে।
                </p>
            </div>
        @endif

        {{-- DURATION POLICY CARD --}}
        <div class="bg-white border border-line rounded-2xl p-5 md:p-6 shadow-sm">
            <div class="font-bn text-sm font-semibold text-ink mb-2.5 flex items-center gap-2">
                <i class="ti ti-clock text-slate-500 text-base"></i>
                তালিকা কতদিন থাকে
            </div>
            <p class="font-bn text-[13.5px] text-slate-500 leading-relaxed">
                প্রতিটি কেস যাচাইয়ের তারিখ থেকে ৩০ দিন সক্রিয় থাকে। এর মধ্যে রোগী বা পরিবার নতুন আপডেট না দিলে কেসটি স্বয়ংক্রিয়ভাবে তালিকা থেকে সরে যায় — যাতে পুরনো বা অপ্রাসঙ্গিক তথ্য থেকে না যায়। চিকিৎসা শেষ হলে বা রোগী চাইলে যেকোনো সময় সরানো হয়।
            </p>
        </div>

    </div>
</div>
@endsection
