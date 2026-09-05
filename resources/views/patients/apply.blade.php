@extends('layouts.app')

@section('title', 'সহায়তার আবেদন — তথ্যের পাতা | Cancer Care Bangladesh')
@section('meta_description', 'ক্যান্সার রোগীর আর্থিক সহায়তার আবেদনের তথ্য নির্দেশিকা। কোনো অনলাইন ফর্ম পূরণ করতে হবে না — সরাসরি ফোনে বা WhatsApp-এ কথা বলুন।')

@section('content')
<div class="bg-white border-b border-line py-9">
    <div class="max-w-[1000px] mx-auto px-6 md:px-10">
        <div class="text-[11.5px] tracking-[0.12em] uppercase text-slate-400 font-semibold mb-2.5 font-bn">সহায়তার আবেদন</div>
        <h1 class="font-serif text-3xl md:text-4xl font-medium tracking-[-0.018em] leading-tight text-ink mb-2.5">সহায়তার জন্য কীভাবে আবেদন করবেন</h1>
        <p class="font-bn text-[15px] text-slate-500 leading-[1.68] max-w-[700px]">
            এটি কোনো অনলাইন আবেদন ফর্ম নয় — আমরা প্রতিটি পরিবার ও চিকিৎসাপত্র ব্যক্তিগতভাবে যাচাই করি। নিচে আবেদনের যোগ্যতা, প্রয়োজনীয় কাগজপত্র ও যোগাযোগ প্রক্রিয়া বিস্তারিত তুলে ধরা হলো।
        </p>
    </div>
</div>

<div class="bg-mist py-9 md:py-12">
    <div class="max-w-[1000px] mx-auto px-6 md:px-10 space-y-6 font-bn">

        {{-- SECTION 1: HOW IT WORKS --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">ভূমিকা ও প্রতিশ্রুতি</div>
            <h2 class="font-serif text-2xl font-semibold text-ink mb-2.5">CCB-র সহায়তা কীভাবে কাজ করে</h2>
            <p class="text-[14.5px] text-slate-600 leading-relaxed mb-6">
                আমরা সরাসরি কোনো অনুদান দিই না বা টাকা তুলি না। অসচ্ছল ক্যান্সার রোগীদের কাগজপত্র নিরপেক্ষভাবে যাচাই করে তথ্য ও রোগীর নিজস্ব বিকাশ/ব্যাংক হিসাব আমাদের ওয়েবসাইটে প্রকাশ করি — যাতে সমাজের যে কেউ কোনো মধ্যস্বত্বভোগী ছাড়া সরাসরি রোগীর কাছে সাহায্য পাঠাতে পারেন।
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-line/60">
                <div class="bg-teal-50/50 rounded-xl p-5 border border-teal-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-teal-700 mb-3.5">আমরা যা করি</div>
                    <div class="space-y-2.5 text-[13.5px] text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>হাসপাতাল ও চিকিৎসকের কাগজপত্র সরাসরি যাচাই করি</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>পরিবারের সাথে সরাসরি দেখা করে আর্থিক তথ্য যাচাই করি</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>ওয়েবসাইটে আপনার তথ্য ও ছবি (সম্মতি সাপেক্ষে) প্রকাশ করি</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>আপনার নিজের বিকাশ বা ব্যাংক অ্যাকাউন্ট নম্বর দেখাই</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-check text-teal-500 text-base shrink-0 mt-0.5"></i>
                            <span>সম্পূর্ণ বিনামূল্যে — কোনো ফি বা কমিশন নেই</span>
                        </div>
                    </div>
                </div>

                <div class="bg-rose-50/40 rounded-xl p-5 border border-rose-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-pink-700 mb-3.5">আমরা যা করি না</div>
                    <div class="space-y-2.5 text-[13.5px] text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>টাকা সংগ্রহ বা হেফাজত করি না</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>কত টাকা উঠবে তার কোনো নিশ্চয়তা দিই না</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>নিজে থেকে কোনো আর্থিক অনুদান দিই না</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>চিকিৎসা বা হাসপাতাল ঠিক করে দিই না</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ti ti-x text-pink-500 text-base shrink-0 mt-0.5"></i>
                            <span>আপনার কাছ থেকে কখনো কোনো টাকা নিই না</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: ELIGIBILITY --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">যোগ্যতার শর্তাবলি</div>
            <h2 class="font-serif text-2xl font-semibold text-ink mb-2.5">কারা আবেদন করতে পারবেন</h2>
            <p class="text-[14.5px] text-slate-600 leading-relaxed mb-5">
                সীমিত সামর্থ্যের কারণে আমরা সবার আবেদন গ্রহণ করতে পারি না। যাঁদের চিকিৎসা খরচের অভাবে সত্যিই বন্ধ হয়ে যাওয়ার ঝুঁকিতে রয়েছে, তাঁদের অগ্রাধিকার দেওয়া হয়।
            </p>

            <div class="bg-mist/70 rounded-xl p-5 border border-line space-y-2.5 text-[14px] text-slate-700">
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>ক্যান্সার নির্ণয় নিশ্চিত হয়েছে এবং নির্ভরযোগ্য বায়োপসি বা প্যাথলজি রিপোর্ট রয়েছে।</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>চিকিৎসা চলমান রয়েছে অথবা বিশেষজ্ঞ চিকিৎসক স্বাক্ষরিত চিকিৎসা পরিকল্পনা দিয়েছেন।</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>পরিবারের আয়ে বা সঞ্চয়ে চিকিৎসার খরচ বহন করা কোনোভাবেই সম্ভব হচ্ছে না।</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>বাংলাদেশের যেকোনো নিবন্ধিত হাসপাতালে চিকিৎসা গ্রহণ করছেন।</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>রোগী বা তাঁর পরিবারের সদস্যের নামে সক্রিয় ও যাচাইযোগ্য বিকাশ/নগদ বা ব্যাংক অ্যাকাউন্ট আছে।</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                    <span>ছবি ও চিকিৎসার তথ্য ওয়েবসাইটে প্রকাশে লিখিত সম্মতি দিতে রাজি আছেন।</span>
                </div>
            </div>
        </div>

        {{-- SECTION 3: REQUIRED DOCUMENTS --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">প্রয়োজনীয় কাগজপত্র</div>
            <h2 class="font-serif text-2xl font-semibold text-ink mb-2.5">যা যা সাথে রাখতে হবে</h2>
            <p class="text-[14.5px] text-slate-600 leading-relaxed mb-6">
                সব কাগজপত্র একসাথে না থাকলেও হেল্পলাইনে যোগাযোগ করুন — কোন নথি কীভাবে সংগ্রহ করবেন আমাদের টিম সাহায্য করবে।
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-microscope text-slate-500 text-lg"></i>
                        বায়োপসি বা হিস্টোপ্যাথলজি রিপোর্ট
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">ক্যান্সার নিশ্চিত হওয়ার মূল প্রমাণপত্র। ল্যাব বা হাসপাতাল থেকে প্রাপ্ত মূল রিপোর্ট।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">অবশ্যই লাগবে</span>
                </div>

                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-clipboard-text text-slate-500 text-lg"></i>
                        চিকিৎসকের চিকিৎসা পরিকল্পনা
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">কী চিকিৎসা লাগবে, কতদিন লাগবে — চিকিৎসকের স্বাক্ষর ও সিলসহ প্রোটোকল।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">অবশ্যই লাগবে</span>
                </div>

                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-receipt text-slate-500 text-lg"></i>
                        হাসপাতালের খরচের হিসাব
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">বাকি চিকিৎসায় কত টাকা লাগবে তার প্রাক্কলন বা এস্টিমেট সনদ।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">অবশ্যই লাগবে</span>
                </div>

                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-id text-slate-500 text-lg"></i>
                        জাতীয় পরিচয়পত্র (NID)
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">রোগীর জাতীয় পরিচয়পত্র। রোগী শিশু হলে জন্মসনদ ও পিতা/মাতার NID।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">অবশ্যই লাগবে</span>
                </div>

                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-wallet text-slate-500 text-lg"></i>
                        বিকাশ বা ব্যাংক অ্যাকাউন্টের তথ্য
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">রোগী বা অভিভাবকের নামে হতে হবে। NID কার্ডের সাথে নামের মিল থাকা বাধ্যতামূলক।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">অবশ্যই লাগবে</span>
                </div>

                <div class="border border-line rounded-xl p-4 bg-off">
                    <div class="text-sm font-semibold text-ink flex items-center gap-2 mb-1.5">
                        <i class="ti ti-receipt-2 text-slate-500 text-lg"></i>
                        পূর্ববর্তী চিকিৎসার রসিদ
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-2.5">থাকলে সাথে দিন — এতে পরিবারের আর্থিক বোঝার চিত্র স্পষ্ট হয়।</p>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-mist text-slate-600 border border-line">থাকলে ভালো</span>
                </div>
            </div>
        </div>

        {{-- SECTION 4: 5-STEP WORKFLOW --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">আবেদন ও যাচাই প্রক্রিয়া</div>
            <h2 class="font-serif text-2xl font-semibold text-ink mb-2.5">যেভাবে যাচাই সম্পন্ন হয়</h2>
            <p class="text-[14.5px] text-slate-600 leading-relaxed mb-6">
                প্রাথমিক যোগাযোগ থেকে ওয়েবসাইটে প্রকাশ পর্যন্ত সাধারণত ৭ থেকে ১৪ দিন সময় লাগে।
            </p>

            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">১</div>
                    <div class="flex-1 pb-2">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-[15px] font-semibold text-ink">যোগাযোগ করুন</h3>
                            <span class="text-xs text-slate-400">প্রথম দিন</span>
                        </div>
                        <p class="text-[13.5px] text-slate-600 leading-relaxed">
                            নিচের নম্বরে সরাসরি ফোন বা WhatsApp করুন। কী কী কাগজপত্র আছে তা জানান। আমাদের প্রতিনিধি আপনার সাথে কথা বলে প্রাথমিক যোগ্যতা নির্ধারণ করবেন।
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">২</div>
                    <div class="flex-1 pb-2">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-[15px] font-semibold text-ink">কাগজপত্র পাঠান</h3>
                            <span class="text-xs text-slate-400">২–৩ দিন</span>
                        </div>
                        <p class="text-[13.5px] text-slate-600 leading-relaxed">
                            WhatsApp-এ নথিপত্রের স্পষ্ট ছবি পাঠান অথবা আমাদের অফিসে নিয়ে আসুন। আমাদের যাচাই টিম হাসপাতালে যোগাযোগ করে তথ্যের সত্যতা যাচাই করবেন।
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">৩</div>
                    <div class="flex-1 pb-2">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-[15px] font-semibold text-ink">মাঠপর্যায়ে সরাসরি সাক্ষাৎ</h3>
                            <span class="text-xs text-slate-400">৫–১০ দিন</span>
                        </div>
                        <p class="text-[13.5px] text-slate-600 leading-relaxed">
                            আমাদের প্রতিনিধি রোগী ও পরিবারের সাথে হাসপাতালে বা বাসায় সরাসরি দেখা করবেন। দাতাদের আস্থার স্বার্থে এটি বাধ্যতামূলক।
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">৪</div>
                    <div class="flex-1 pb-2">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-[15px] font-semibold text-ink">লিখিত সম্মতিপত্রে স্বাক্ষর</h3>
                            <span class="text-xs text-slate-400">সাক্ষাতের দিন</span>
                        </div>
                        <p class="text-[13.5px] text-slate-600 leading-relaxed">
                            রোগীর ছবি (১৮ বছরের নিচে হলে ছবি নেওয়া হয় না), চিকিৎসা তথ্য ও আর্থিক হিসাব প্রকাশের লিখিত সম্মতি নেওয়া হবে।
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">৫</div>
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-[15px] font-semibold text-ink">ওয়েবসাইটে প্রকাশ ও প্রচার</h3>
                            <span class="text-xs text-teal-600 font-semibold">৭–১৪ দিন</span>
                        </div>
                        <p class="text-[13.5px] text-slate-600 leading-relaxed">
                            সব শর্ত পূরণ হলে কেসটি ৩০ দিনের জন্য ওয়েবসাইটে প্রকাশিত হবে। নতুন আপডেট দিলে ৩০ দিন করে নবায়ন হবে। চিকিৎসা শেষে বা অনুরোধে যেকোনো সময় সরানো হবে।
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 5: CONTACT CHANNELS --}}
        <div class="bg-white border border-line rounded-2xl p-6 md:p-8 shadow-sm">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">যোগাযোগের ঠিকানা</div>
            <h2 class="font-serif text-2xl font-semibold text-ink mb-2.5">এখনই কথা বলুন</h2>
            <p class="text-[14.5px] text-slate-600 leading-relaxed mb-6">
                সকাল ৯টা থেকে রাত ৯টা, সপ্তাহে সাত দিন। বাংলায় কথা বলুন — কোনো জটিল ফর্ম পূরণ করতে হবে না।
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="tel:09611777888" class="border border-line rounded-xl p-5 bg-off hover:border-slate-300 transition-colors text-center block">
                    <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="ti ti-phone"></i>
                    </div>
                    <div class="text-sm font-semibold text-ink mb-1">হেল্পলাইনে ফোন করুন</div>
                    <div class="font-mono text-base font-bold text-slate-800 mb-1">০৯৬১১-৭৭৭৮৮৮</div>
                    <div class="text-xs text-slate-400">সকাল ৯টা – রাত ৯টা</div>
                </a>

                <div class="border border-line rounded-xl p-5 bg-off hover:border-slate-300 transition-colors text-center">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="ti ti-brand-whatsapp"></i>
                    </div>
                    <div class="text-sm font-semibold text-ink mb-1">WhatsApp বার্তা</div>
                    <div class="font-mono text-base font-bold text-slate-800 mb-1">০৯৬১১-৭৭৭৮৮৮</div>
                    <div class="text-xs text-slate-400">কাগজের ছবি পাঠাতে পারেন</div>
                </div>

                <div class="border border-line rounded-xl p-5 bg-off hover:border-slate-300 transition-colors text-center">
                    <div class="w-12 h-12 rounded-xl bg-mist text-slate-700 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="ti ti-map-pin"></i>
                    </div>
                    <div class="text-sm font-semibold text-ink mb-1">অফিসে আসুন</div>
                    <div class="text-sm font-medium text-slate-800 mb-1">ঢাকা অফিস</div>
                    <div class="text-xs text-slate-400">আগে ফোনে সময় ঠিক করে নিন</div>
                </div>
            </div>
        </div>

        {{-- SECTION 6: CAPACITY NOTICE --}}
        <div class="bg-white border border-line rounded-2xl p-6 shadow-sm flex items-start gap-4">
            <i class="ti ti-alert-circle text-slate-400 text-2xl shrink-0 mt-0.5"></i>
            <div>
                <div class="text-sm font-semibold text-ink mb-1.5">সীমিত সামর্থ্যের কথা খোলাখুলি বলি</div>
                <p class="text-[13px] text-slate-500 leading-relaxed">
                    প্রতিটি কেস আমরা নিজে গিয়ে সরেজমিনে যাচাই করি বলে একসাথে সীমিত সংখ্যক কেসের বেশি প্রক্রিয়া করা সম্ভব হয় না। আপনার আবেদন কোনো কারণে গ্রহণ না করা গেলে আমরা বিনীতভাবে জানিয়ে দেব এবং সরকারি সমাজসেবা অধিদপ্তর বা হাসপাতালের সমাজকল্যাণ তহবিলে আবেদনের পরামর্শ দেব।
                </p>
            </div>
        </div>

        {{-- SCAM WARNING --}}
        <div class="bg-gold-soft border border-gold-line rounded-xl p-4 md:p-5 flex gap-3.5">
            <i class="ti ti-shield-exclamation text-gold text-xl shrink-0 mt-0.5"></i>
            <div class="text-xs text-[#8A6218] leading-relaxed">
                <b class="text-[#7A5410] font-semibold">প্রতারণা থেকে সাবধান:</b> CCB কখনোই আবেদনের জন্য কোনো প্রকার ফি বা টাকা নেয় না। আমাদের সব সেবা সম্পূর্ণ বিনামূল্যে। কেউ টাকার বিনিময়ে সহায়তা তালিকাভুক্তির প্রস্তাব দিলে সাথে সাথে ০৯৬১১-৭৭৭৮৮৮ নম্বরে জানান।
            </div>
        </div>

    </div>
</div>
@endsection
