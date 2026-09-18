@extends('layouts.app')

@section('title', $hospital->name_bn . ' — চিকিৎসা সেবা, খরচ ও অপেক্ষার সময় | CancerCare Bangladesh')
@section('meta_description', $hospital->name_bn . '-এর রেডিওথেরাপি, কেমোথেরাপি, সার্জারি, অপেক্ষার সময় ও খরচের বিস্তারিত তথ্য। ' . Str::limit(strip_tags($hospital->description_bn), 140))

@push('meta')
    {{-- Schema.org Hospital Structured Data --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Hospital",
        "name": "{{ $hospital->name_bn }}",
        "alternateName": "{{ $hospital->name_en }}",
        "url": "{{ route('hospitals.show', $hospital) }}",
        "telephone": "{{ $hospital->phone }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ $hospital->address_bn }}",
            "addressLocality": "{{ $hospital->district?->name_en ?? 'Dhaka' }}",
            "addressCountry": "BD"
        },
        @if ($hospital->latitude && $hospital->longitude)
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": {{ $hospital->latitude }},
            "longitude": {{ $hospital->longitude }}
        },
        @endif
        "description": "{{ Str::limit(strip_tags($hospital->description_bn), 250) }}"
    }
    </script>
@endpush

@section('content')
<div class="py-6 pb-20 bg-mist">
    <div class="max-w-[1240px] mx-auto px-4 md:px-10">

        {{-- Hero Image --}}
        <div class="w-full h-[220px] md:h-[280px] rounded-[20px] overflow-hidden bg-slate-800 shadow-sm">
            @if ($hospital->cover_photo_path)
                <img src="{{ Str::startsWith($hospital->cover_photo_path, ['http://', 'https://']) ? $hospital->cover_photo_path : asset('storage/' . $hospital->cover_photo_path) }}"
                     alt="{{ $hospital->name_bn }}"
                     fetchpriority="high"
                     decoding="async"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-500 bg-slate-800">
                    <i class="ti ti-building-hospital text-6xl text-slate-400"></i>
                </div>
            @endif
        </div>

        {{-- Hero Floating Card --}}
        <div class="bg-white border border-line rounded-[20px] p-6 md:p-[28px_32px] mx-2 md:mx-8 -mt-16 md:-mt-[70px] mb-6 relative shadow-sm z-10">
            <div class="flex flex-col md:flex-row items-start justify-between gap-3 mb-2.5">
                <div>
                    <h1 class="font-serif text-2xl md:text-[32px] font-semibold tracking-tight text-ink leading-tight">
                        {{ $hospital->name_bn }}
                    </h1>
                    @if ($hospital->name_en)
                        <div class="text-sm text-slate-500 font-medium mt-0.5">{{ $hospital->name_en }}</div>
                    @endif
                </div>

                @php
                    $typeClass = match ($hospital->type?->value ?? $hospital->type) {
                        'govt' => 'bg-teal-100 text-teal-700',
                        'private' => 'bg-[#E6F1FB] text-[#0C447C]',
                        'npo' => 'bg-gold-soft text-[#7A5410]',
                        default => 'bg-mist text-slate-500',
                    };
                @endphp
                <span class="text-xs px-3 py-1 rounded-full font-semibold whitespace-nowrap {{ $typeClass }}">
                    {{ $hospital->type?->labelBn() ?? $hospital->type }}
                </span>
            </div>

            <div class="text-[14.5px] text-slate-600 leading-relaxed mb-4 font-bn flex items-center gap-1.5 flex-wrap">
                <i class="ti ti-map-pin text-[16px] text-slate-400"></i>
                <span>{{ $hospital->address_bn }}</span>
                @if ($hospital->district)
                    <span class="text-slate-300">·</span>
                    <span>{{ $hospital->district->name_bn }}</span>
                @endif
                @if ($hospital->established_year)
                    <span class="text-slate-300">·</span>
                    <span>{{ $hospital->established_year }} সালে প্রতিষ্ঠিত</span>
                @endif
            </div>

            {{-- 4 Stat Boxes --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 pt-4 border-t border-line">
                <div class="bg-mist rounded-xl p-3.5 md:p-[14px_16px]">
                    <div class="font-serif text-xl md:text-[21px] font-semibold text-ink">
                        {{ $hospital->bed_count ? $hospital->bed_count : '—' }}
                    </div>
                    <div class="text-[12px] text-slate-500 font-bn mt-0.5">শয্যা সংখ্যা</div>
                </div>

                <div class="bg-mist rounded-xl p-3.5 md:p-[14px_16px]">
                    <div class="font-serif text-xl md:text-[21px] font-semibold text-ink">
                        {{ $hospital->oncologist_count ? "{$hospital->oncologist_count} জন" : '—' }}
                    </div>
                    <div class="text-[12px] text-slate-500 font-bn mt-0.5">অনকোলজিস্ট</div>
                </div>

                <div class="bg-mist rounded-xl p-3.5 md:p-[14px_16px]">
                    <div class="font-serif text-xl md:text-[21px] font-semibold text-ink">
                        {{ $hospital->outdoor_fee !== null ? '৳' . number_format($hospital->outdoor_fee) : '—' }}
                    </div>
                    <div class="text-[12px] text-slate-500 font-bn mt-0.5">
                        {{ $hospital->type?->value === 'private' ? 'ডাক্তারের ফি' : 'আউটডোর টিকিট' }}
                    </div>
                </div>

                <div class="bg-mist rounded-xl p-3.5 md:p-[14px_16px]">
                    <div class="font-serif text-xl md:text-[21px] font-semibold text-ink">
                        {{ $hospital->emergency_24h ? '২৪ ঘণ্টা' : 'নিয়মিত সময়' }}
                    </div>
                    <div class="text-[12px] text-slate-500 font-bn mt-0.5">জরুরি বিভাগ</div>
                </div>
            </div>
        </div>

        {{-- 2-Column Content Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 px-2 md:px-8 items-start">

            {{-- Main Content Column (Left) --}}
            <div class="space-y-4">

                {{-- 1. কী কী চিকিৎসা পাওয়া যায় (Capability Matrix) --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                        কী কী চিকিৎসা পাওয়া যায়
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-5 font-bn">
                        যা আছে এবং যা নেই — দুটোই দেওয়া আছে যাতে অপ্রয়োজনে রোগীদের দূরদূরান্ত থেকে যাতায়াত না করতে হয়।
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($hospital->capabilities as $cap)
                            @php
                                $capStatus = $cap->status?->value ?? $cap->status;
                                $isNo = $capStatus === 'not_available';
                                $isLimited = $capStatus === 'limited';

                                $cardBg = $isNo ? 'bg-mist border-line-soft' : 'bg-white border-line';
                                $icBg = $isNo ? 'bg-mist text-slate-300' : ($isLimited ? 'bg-gold-soft text-gold' : 'bg-teal-100 text-teal-700');
                                $icClass = match ($cap->capability?->icon) {
                                    'radioactive' => 'ti-radioactive',
                                    'droplet' => 'ti-vaccine',
                                    'scalpel' => 'ti-cut',
                                    'bone' => 'ti-bone',
                                    'stroller' => 'ti-mood-kid',
                                    'heart-handshake' => 'ti-heart-handshake',
                                    'microscope' => 'ti-microscope',
                                    'scan' => 'ti-scan',
                                    'target' => 'ti-target',
                                    'gender-female' => 'ti-woman',
                                    'droplet-filled' => 'ti-droplet',
                                    default => 'ti-medical-cross',
                                };
                            @endphp
                            <div class="flex items-start gap-3 p-4 border rounded-[13px] {{ $cardBg }}">
                                <div class="w-[34px] h-[34px] rounded-[9px] flex items-center justify-center shrink-0 {{ $icBg }}">
                                    <i class="ti {{ $isNo ? 'ti-x' : ($isLimited ? 'ti-alert-triangle' : $icClass) }} text-[18px]"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[14.5px] font-semibold font-bn mb-1 {{ $isNo ? 'text-slate-400 line-through' : 'text-ink' }}">
                                        {{ $cap->capability?->label_bn }}
                                        @if ($isNo)
                                            <span class="text-xs font-normal text-slate-400">(নেই)</span>
                                        @elseif ($isLimited)
                                            <span class="text-xs font-normal text-gold">(সীমিত)</span>
                                        @endif
                                    </div>
                                    <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                        @if ($cap->detail_bn)
                                            {{ $cap->detail_bn }}
                                        @elseif ($isNo)
                                            এখানে এই সেবার সুবিধা নেই।
                                        @else
                                            নিয়মিত ও পূর্ণাঙ্গভাবে সেবাটি চালু আছে।
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 2. হাসপাতাল চেনার ভিডিও (Orientation Video) --}}
                @if ($hospital->videos->isNotEmpty())
                    @php
                        $video = $hospital->videos->first();
                    @endphp
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            হাসপাতাল চেনার ভিডিও
                        </div>
                        <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                            প্রথমবার আসার আগে দেখে নিন — কোথায় টিকিট কাটবেন, কোন ব্লকে কী আছে, কীভাবে এগোবেন। ভিডিওটি CCB নিজে ধারণ করেছে, হাসপাতালের প্রচারণা নয়।
                        </div>

                        <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer"
                           class="border border-line rounded-[14px] overflow-hidden hover:border-slate-300 transition flex flex-col sm:flex-row group block">
                            <div class="w-full sm:w-[210px] h-[132px] relative shrink-0 bg-slate-800 flex items-center justify-center overflow-hidden">
                                @if ($hospital->cover_photo_path)
                                    <img src="{{ Str::startsWith($hospital->cover_photo_path, ['http://', 'https://']) ? $hospital->cover_photo_path : asset('storage/' . $hospital->cover_photo_path) }}"
                                         alt="{{ $video->title_bn }}"
                                         class="w-full h-full object-cover opacity-60 group-hover:scale-105 transition duration-300">
                                @endif
                                <div class="absolute w-12 h-12 rounded-full bg-white/95 flex items-center justify-center text-slate-900 shadow group-hover:scale-110 transition">
                                    <i class="ti ti-player-play-filled text-lg ml-0.5 text-pink-700"></i>
                                </div>
                                @if ($video->duration_seconds > 0)
                                    <span class="absolute bottom-2 right-2 text-[11px] bg-black/75 text-white px-2 py-0.5 rounded font-sans">
                                        {{ gmdate('i:s', $video->duration_seconds) }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-4 md:p-[18px_20px] flex-1 flex flex-col justify-center">
                                <div class="text-[16px] font-semibold text-ink group-hover:text-pink-700 transition mb-1.5 leading-snug font-bn">
                                    {{ $video->title_bn }}
                                </div>
                                @if ($video->description_bn)
                                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-2.5 font-bn">
                                        {{ $video->description_bn }}
                                    </div>
                                @endif
                                <span class="text-[11.5px] text-teal-700 bg-teal-100 px-2.5 py-0.5 rounded-full font-semibold inline-flex items-center gap-1 self-start font-bn">
                                    <i class="ti ti-video text-[13px]"></i>
                                    {{ $video->produced_by ?? 'CancerCare Bangladesh' }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- 3. অপেক্ষার সময় (Wait Times) --}}
                @if ($hospital->waitTimes->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            অপেক্ষার সময়
                        </div>
                        <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                            {{ $hospital->type?->value === 'govt' ? 'সরকারি হাসপাতালে খরচ কম হলেও সেবা ভেদে সিরিয়ালে অপেক্ষা দীর্ঘ হতে পারে। পরিকল্পনার সময় এটা হিসাবে রাখুন।' : 'হাসপাতাল প্রদত্ত গড় অপেক্ষার সময়সূচি।' }}
                        </div>

                        <div class="bg-mist rounded-[14px] p-5 md:p-[20px_22px]">
                            @foreach ($hospital->waitTimes as $wt)
                                @php
                                    $sev = $wt->severity?->value ?? $wt->severity;
                                    $sevClass = match ($sev) {
                                        'long' => 'text-red-700',
                                        'medium' => 'text-gold',
                                        'short' => 'text-teal-700',
                                        default => 'text-slate-700',
                                    };
                                @endphp
                                <div class="flex justify-between items-center py-2.5 border-b border-line last:border-b-0 text-sm font-bn">
                                    <span class="text-ink">{{ $wt->label_bn }}</span>
                                    <span class="font-semibold text-right {{ $sevClass }}">
                                        {{ $wt->min_weeks == $wt->max_weeks ? "{$wt->min_weeks} সপ্তাহ" : "{$wt->min_weeks}–{$wt->max_weeks} সপ্তাহ" }}
                                    </span>
                                </div>
                            @endforeach
                            <div class="text-[12.5px] text-slate-500 leading-relaxed mt-3.5 pt-3.5 border-t border-line font-bn">
                                এই সময়গুলো রোগীদের অভিজ্ঞতা ও হাসপাতাল থেকে প্রাপ্ত তথ্যের আনুমানিক হিসাব — কোনো সুনির্দিষ্ট নিশ্চয়তা নয়। জরুরি প্রয়োজনে ডাক্তার অগ্রাধিকার দিতে পারেন।
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 4. আনুমানিক খরচ (Hospital Costs Table) --}}
                @if ($hospital->costs->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            আনুমানিক খরচ
                        </div>
                        <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                            হাসপাতালের সেবামূল্যের বর্তমান কাঠামো। ওষুধ বা বিশেষ কিছু পরীক্ষা বাইরে থেকে কিনতে হলে তা এর বাইরে।
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm font-bn">
                                <thead>
                                    <tr class="border-b border-line">
                                        <th class="text-left text-[11.5px] text-slate-400 uppercase tracking-wider font-semibold pb-2.5">সেবা</th>
                                        <th class="text-right text-[11.5px] text-slate-400 uppercase tracking-wider font-semibold pb-2.5">আনুমানিক খরচ</th>
                                        <th class="text-left text-[11.5px] text-slate-400 uppercase tracking-wider font-semibold pb-2.5 pl-6">নোট</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hospital->costs as $cost)
                                        <tr class="border-b border-line-soft last:border-b-0">
                                            <td class="py-3 text-ink font-medium">{{ $cost->label_bn ?? $cost->service_key }}</td>
                                            <td class="py-3 text-right font-semibold text-ink whitespace-nowrap">
                                                ৳{{ number_format($cost->min_amount) }}@if($cost->min_amount != $cost->max_amount)–{{ number_format($cost->max_amount) }}@endif
                                            </td>
                                            <td class="py-3 pl-6 text-slate-500 text-xs leading-relaxed">
                                                {{ $cost->note_bn ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- 5. যেসব বিষয়ে আগে থেকে প্রস্তুতি লাগে (Preparation 4 Pillars) --}}
                @if ($hospital->prepInfos->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            যেসব বিষয়ে আগে থেকে প্রস্তুতি লাগে
                        </div>
                        <div class="text-[13.5px] text-slate-600 leading-relaxed mb-4 font-bn">
                            এই গুরুত্বপূর্ণ বিষয়গুলো না জেনে হাসপাতালে এলে মাঝপথে ভোগান্তি বা জটিলতায় পড়তে হতে পারে।
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($hospital->prepInfos as $prep)
                                @php
                                    $flagClass = match ($prep->flag_type?->value ?? $prep->flag_type) {
                                        'positive' => 'bg-teal-100 text-teal-700',
                                        'warning' => 'bg-gold-soft text-[#7A5410]',
                                        'negative' => 'bg-red-100 text-red-700',
                                        default => 'bg-mist text-slate-600',
                                    };
                                    $pIcon = match ($prep->key?->value ?? $prep->key) {
                                        'blood_bank' => 'ti-droplet',
                                        'medicine_supply' => 'ti-pill',
                                        'attendant_policy' => 'ti-users',
                                        'records_return' => 'ti-files',
                                        default => 'ti-info-circle',
                                    };
                                @endphp
                                <div class="border border-line rounded-[13px] p-4 md:p-[16px_18px] flex flex-col justify-between">
                                    <div>
                                        <div class="text-[13.5px] font-semibold text-ink mb-1.5 flex items-center gap-2 font-bn">
                                            <i class="ti {{ $pIcon }} text-slate-500 text-[16px]"></i>
                                            <span>{{ $prep->title_bn }}</span>
                                        </div>
                                        <div class="text-[12.5px] text-slate-600 leading-relaxed font-bn">
                                            {{ $prep->description_bn }}
                                        </div>
                                    </div>
                                    @if ($prep->flag_text_bn)
                                        <div class="mt-2.5">
                                            <span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold inline-block font-bn {{ $flagClass }}">
                                                {{ $prep->flag_text_bn }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 6. রোগীদের বাস্তব অভিজ্ঞতা (Patient Experience — STRICT NO STAR RATING) --}}
                <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                        রোগীদের অভিজ্ঞতা
                    </div>
                    <div class="text-[13.5px] text-slate-600 leading-relaxed mb-5 font-bn">
                        আমরা কোনো স্টার রেটিং দিই না — কারণ ৳২০-র সরকারি হাসপাতাল আর ৳১,০০০-এর বেসরকারি হাসপাতালকে এক সংখ্যায় তুলনা করা যায় না। বদলে নির্দিষ্ট প্রশ্নের উত্তর দেওয়া হলো, যা মাঠপর্যায়ে নিরপেক্ষভাবে যাচাই করা সম্ভব।
                    </div>

                    @if ($hospital->experienceSummaries->isNotEmpty())
                        <div class="space-y-3.5">
                            @foreach ($hospital->experienceSummaries as $exp)
                                @php
                                    $pct = $exp->percentage;
                                    $barColor = $pct >= 75 ? 'bg-teal-500' : ($pct >= 50 ? 'bg-gold' : 'bg-red-700');
                                @endphp
                                <div>
                                    <div class="flex justify-between items-baseline gap-3 mb-1.5 font-bn">
                                        <span class="text-[14px] text-ink leading-snug">{{ $exp->question?->label_bn }}</span>
                                        <span class="text-[13.5px] font-semibold text-ink whitespace-nowrap">{{ round($pct) }}% বলেছেন হ্যাঁ</span>
                                    </div>
                                    <div class="h-1.5 bg-mist rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-[12.5px] text-slate-500 leading-relaxed mt-4 pt-3.5 border-t border-line font-bn">
                            যাচাইকৃত রোগী ও পরিবারের সংগৃহীত উত্তরের ভিত্তিতে প্রস্তুত। কোনো হাসপাতালকে ভালো বা খারাপ প্রমাণের জন্য নয় — শুধু হাসপাতালে যাওয়ার আগে কী আশা করবেন তা স্পষ্টভাবে জানানোর জন্য।
                        </div>
                    @else
                        <div class="bg-mist rounded-xl p-4 text-center text-xs text-slate-500 font-bn">
                            এই হাসপাতালের জন্য জরিপের তথ্য সংগ্রহ ও পর্যালোচনা চলছে।
                        </div>
                    @endif
                </div>

                {{-- 7. এখানে যেসব ডাক্তার বসেন (Associated Doctors) --}}
                @if ($hospital->doctors->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            এখানে যেসব ডাক্তার বসেন
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($hospital->doctors as $doctor)
                                <a href="{{ route('doctors.show', $doctor) }}" class="border border-line rounded-[13px] p-4 hover:border-slate-300 transition flex flex-col justify-between group">
                                    <div>
                                        <div class="w-11 h-11 rounded-xl overflow-hidden mb-2.5 bg-mist shrink-0">
                                            @if ($doctor->photo_path)
                                                <img src="{{ Str::startsWith($doctor->photo_path, ['http://', 'https://']) ? $doctor->photo_path : asset('storage/' . $doctor->photo_path) }}"
                                                     alt="{{ $doctor->name_bn }}"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <i class="ti ti-user text-xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-[14px] font-semibold text-ink group-hover:text-pink-700 transition leading-snug font-bn">
                                            {{ $doctor->name_bn }}
                                        </div>
                                        <div class="text-[12px] text-slate-500 mt-0.5 leading-snug font-bn">
                                            {{ $doctor->current_position_bn }}
                                        </div>
                                    </div>
                                    @if ($doctor->pivot?->schedule_note_bn)
                                        <div class="text-[12px] text-teal-700 mt-2 flex items-center gap-1 font-medium font-bn">
                                            <i class="ti ti-calendar text-[13px]"></i>
                                            <span>{{ $doctor->pivot->schedule_note_bn }}</span>
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 8. বাইরের জেলা থেকে এলে যা জানা দরকার (Practical Info) --}}
                @if ($hospital->practicalInfos->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-6 md:p-[28px_30px] shadow-sm">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-widest mb-2 font-bn">
                            বাইরের জেলা থেকে এলে যা জানা দরকার
                        </div>
                        <div class="space-y-4">
                            @foreach ($hospital->practicalInfos as $prac)
                                @php
                                    $prIcon = match ($prac->key?->value ?? $prac->key) {
                                        'documents' => 'ti-file-text',
                                        'timing' => 'ti-clock-hour-8',
                                        'accommodation' => 'ti-home',
                                        'transport' => 'ti-bus',
                                        'financial_aid' => 'ti-heart-handshake',
                                        default => 'ti-info-circle',
                                    };
                                @endphp
                                <div class="flex items-start gap-3.5 font-bn">
                                    <div class="w-9 h-9 rounded-xl bg-mist flex items-center justify-center shrink-0 text-slate-600">
                                        <i class="ti {{ $prac->icon ? 'ti-' . $prac->icon : $prIcon }} text-[18px]"></i>
                                    </div>
                                    <div>
                                        <div class="text-[14.5px] font-semibold text-ink mb-1">{{ $prac->title_bn }}</div>
                                        <div class="text-[13.5px] text-slate-600 leading-relaxed">{{ $prac->description_bn }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- Sidebar Column (Right) --}}
            <div class="space-y-4 sticky top-[90px]">

                {{-- Card 1: যোগাযোগ ও অবস্থান --}}
                <div class="bg-white border border-line rounded-2xl p-5 md:p-[20px_22px] shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3.5 font-bn">
                        যোগাযোগ ও অবস্থান
                    </div>

                    {{-- Map Box --}}
                    @if ($hospital->latitude && $hospital->longitude)
                        <a href="https://maps.google.com/?q={{ $hospital->latitude }},{{ $hospital->longitude }}"
                           target="_blank" rel="noopener noreferrer"
                           class="h-[140px] bg-mist border border-line-soft rounded-xl flex flex-col items-center justify-center gap-1.5 text-slate-500 hover:bg-slate-100 hover:text-ink transition mb-3.5 group">
                            <i class="ti ti-map-2 text-2xl text-slate-400 group-hover:text-pink-700 transition"></i>
                            <span class="text-xs font-bn font-medium">গুগল মানচিত্রে অবস্থান দেখুন</span>
                        </a>
                    @endif

                    <div class="space-y-2 text-sm font-bn">
                        <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                            <i class="ti ti-map-pin text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                            <div>
                                <div class="text-[11.5px] text-slate-400">ঠিকানা</div>
                                <div class="text-[13.5px] font-medium text-ink leading-snug">{{ $hospital->address_bn }}</div>
                            </div>
                        </div>

                        <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                            <i class="ti ti-phone text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                            <div>
                                <div class="text-[11.5px] text-slate-400">ফোন / হেল্পলাইন</div>
                                <a href="tel:{{ $hospital->phone }}" class="text-[13.5px] font-medium text-ink hover:text-pink-700 leading-snug font-sans">
                                    {{ $hospital->phone }}
                                </a>
                            </div>
                        </div>

                        <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                            <i class="ti ti-ambulance text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                            <div>
                                <div class="text-[11.5px] text-slate-400">জরুরি বিভাগ</div>
                                <div class="text-[13.5px] font-medium text-ink leading-snug">
                                    {{ $hospital->emergency_24h ? '২৪ ঘণ্টা খোলা' : 'সাধারণ সময়সূচি' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($hospital->latitude && $hospital->longitude)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $hospital->latitude }},{{ $hospital->longitude }}"
                           target="_blank" rel="noopener noreferrer"
                           class="w-full text-[13.5px] py-2.5 rounded-lg bg-teal-700 text-white font-semibold hover:bg-teal-800 border border-teal-600 text-center block mt-3 font-bn transition shadow-sm">
                            দিকনির্দেশনা নিন (Directions)
                        </a>
                    @endif
                </div>

                {{-- Card 2: এক নজরে --}}
                <div class="bg-white border border-line rounded-2xl p-5 md:p-[20px_22px] shadow-sm font-bn">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3.5">
                        এক নজরে
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                            <i class="ti ti-building-hospital text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                            <div>
                                <div class="text-[11.5px] text-slate-400">হাসপাতালের ধরন</div>
                                <div class="text-[13.5px] font-medium text-ink">{{ $hospital->type?->labelBn() }}</div>
                            </div>
                        </div>

                        @if ($hospital->established_year)
                            <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                                <i class="ti ti-calendar text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                                <div>
                                    <div class="text-[11.5px] text-slate-400">প্রতিষ্ঠার সাল</div>
                                    <div class="text-[13.5px] font-medium text-ink">{{ $hospital->established_year }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($hospital->annual_patients)
                            <div class="flex gap-2.5 items-start py-2 border-b border-line-soft">
                                <i class="ti ti-users text-slate-400 text-[17px] shrink-0 mt-0.5"></i>
                                <div>
                                    <div class="text-[11.5px] text-slate-400">বাৎসরিক সেবাগ্রহীতা</div>
                                    <div class="text-[13.5px] font-medium text-ink">{{ $hospital->annual_patients }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Verification Notice --}}
                <div class="text-xs text-slate-500 leading-relaxed flex gap-2 items-start p-3.5 bg-white border border-line rounded-xl shadow-sm font-bn">
                    <i class="ti ti-refresh text-[15px] shrink-0 mt-0.5 text-slate-400"></i>
                    <span>
                        তথ্য সর্বশেষ যাচাই: {{ $hospital->last_verified_at ? $hospital->last_verified_at->format('d M Y') : 'সম্প্রতি' }}। কোনো তথ্য পরিবর্তন বা ভুল মনে হলে আমাদের জানান।
                    </span>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
