{{--
    docs/prototypes/doctor_profile.html — /doctors/{slug}। প্রতিটি সেকশন prototype-এর ক্রম মেনে চলে়।
    রেটিং সেকশন শুধু $doctor->ratingSummary->is_published হলে দেখানো হয় (CLAUDE.md নীতি ৫)।
--}}
@extends('layouts.app')

@section('title', $doctor->name_bn.' — যাচাই করা অনকোলজিস্ট | CancerCare Bangladesh')

@php
    $primaryCancerType = $doctor->cancerTypes->firstWhere('pivot.is_primary', true) ?? $doctor->cancerTypes->first();
    $primaryDistrict = optional($doctor->chambers->first())->district;
    $metaDescription = trim(($doctor->current_position_bn ?? '').' · '.($doctor->degrees_line_bn ?? ''), ' ·');

    $doctorTypeIcons = [
        'surgical_oncologist' => ['icon' => 'ti-cut', 'bg' => 'bg-pink-100', 'fg' => 'text-pink-800'],
        'medical_oncologist' => ['icon' => 'ti-vaccine', 'bg' => 'bg-blue-100', 'fg' => 'text-blue-700'],
        'radiation_oncologist' => ['icon' => 'ti-radioactive', 'bg' => 'bg-purple-100', 'fg' => 'text-purple-700'],
        'hemato_oncologist' => ['icon' => 'ti-droplet', 'bg' => 'bg-red-100', 'fg' => 'text-red-700'],
        'gynecologic_oncologist' => ['icon' => 'ti-gender-female', 'bg' => 'bg-pink-100', 'fg' => 'text-pink-800'],
        'pediatric_oncologist' => ['icon' => 'ti-baby-carriage', 'bg' => 'bg-teal-100', 'fg' => 'text-teal-700'],
    ];

    $badgeColors = [
        'teal' => 'bg-teal-100 text-teal-700',
        'pink' => 'bg-pink-100 text-pink-800',
        'blue' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'gold' => 'bg-gold-soft text-[#7A5410]',
        'mist' => 'bg-mist text-slate-500',
    ];

    $serviceIcons = [
        'stethoscope' => 'ti-stethoscope', 'cut' => 'ti-cut', 'vaccine' => 'ti-vaccine',
        'dna' => 'ti-dna', 'microscope' => 'ti-microscope', 'pill' => 'ti-pill', 'refresh' => 'ti-refresh',
        'file-description' => 'ti-file-description', 'clipboard-check' => 'ti-clipboard-check', 'shield-check' => 'ti-shield-check',
    ];

    $stageLabels = ['1' => 'স্টেজ ১', '2' => 'স্টেজ ২', '3' => 'স্টেজ ৩', '4' => 'স্টেজ ৪', 'unknown' => ''];
    $storyHighlightColors = [
        'teal' => 'bg-teal-100 text-teal-700', 'blue' => 'bg-blue-100 text-blue-700', 'purple' => 'bg-purple-100 text-purple-700',
    ];

    $servicesByCancerType = $doctor->services->groupBy('cancer_type_id');
    $tabCancerTypes = $doctor->cancerTypes->filter(fn ($cancerType) => $servicesByCancerType->has($cancerType->id));
    $firstTabKey = $tabCancerTypes->isNotEmpty() ? (string) $tabCancerTypes->first()->id : ($doctor->offers_second_opinion ? 'second-opinion' : null);

    $seoDescription = $metaDescription ?: 'ডা. '.$doctor->name_bn.' — বিশেষজ্ঞ অনকোলজিস্ট। চেম্বার, অ্যাপয়েন্টমেন্ট ও চিকিৎসার তথ্যাবলী।';
    $seoType = 'profile';
    $seoImage = $doctor->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->photo_path) : asset('images/logo.png');
@endphp

@section('content')

<div class="max-w-[1240px] mx-auto px-10">
    <div class="font-bn py-3 text-[12.5px] text-slate-400 flex items-center gap-[7px]">
        <a href="{{ route('doctors.index') }}" class="text-slate-500 hover:text-ink">ডাক্তার</a>
        <i class="ti ti-chevron-right" style="font-size:13px"></i>
        <span>
            {{ $doctor->doctorTypes->pluck('label_bn')->implode(', ') }}
            @if ($primaryDistrict)
                · {{ $primaryDistrict->name_bn }}
            @endif
        </span>
        <i class="ti ti-chevron-right" style="font-size:13px"></i>
        <span class="text-ink">{{ $doctor->name_bn }}</span>
    </div>
</div>

<div class="pt-3 pb-[70px]">
    <div class="max-w-[1240px] mx-auto px-10">

        <div class="bg-white border border-line rounded-[20px] px-8 py-7 mb-4">
            <div class="flex gap-6 items-start pb-5 border-b border-line">
                @if ($doctor->photo_path)
                    <img class="w-[118px] h-[118px] rounded-[20px] object-cover shrink-0 bg-mist"
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->photo_path) }}"
                        alt="{{ $doctor->name_bn }}"
                        fetchpriority="high"
                        decoding="async"
                        width="118"
                        height="118">
                @else
                    <div class="w-[118px] h-[118px] rounded-[20px] shrink-0 bg-mist flex items-center justify-center text-slate-300">
                        <i class="ti ti-user text-[44px]"></i>
                    </div>
                @endif

                <div class="flex-1">
                    <div class="font-serif text-[32px] font-semibold tracking-[-.018em] leading-[1.2] mb-[7px]">{{ $doctor->name_bn }}</div>
                    <div class="font-bn text-[14.5px] text-slate-500 leading-[1.65] mb-[13px]">{{ $doctor->degrees_line_bn }}</div>

                    <div class="flex gap-[7px] flex-wrap mb-[13px]">
                        @if ($doctor->bmdc_verified_at)
                            <span class="font-bn inline-flex items-center gap-[5px] text-[11.5px] px-[11px] py-1 rounded-full font-semibold bg-teal-100 text-teal-700">
                                <i class="ti ti-rosette-discount-check" style="font-size:13px"></i> BMDC যাচাইকৃত
                            </span>
                        @endif
                        <span class="text-[11.5px] px-[11px] py-1 rounded-full font-semibold bg-mist text-slate-500">BMDC #{{ $doctor->bmdc_number }}</span>
                        <span class="font-bn text-[11.5px] px-[11px] py-1 rounded-full font-semibold bg-mist text-slate-500">{{ $doctor->experience_years }} বছরের অভিজ্ঞতা</span>
                    </div>

                    <div class="flex gap-2 flex-wrap">
                        @foreach ($doctor->doctorTypes as $doctorType)
                            @php $style = $doctorTypeIcons[$doctorType->key] ?? ['icon' => 'ti-stethoscope', 'bg' => 'bg-mist', 'fg' => 'text-slate-700']; @endphp
                            <div class="font-bn flex items-center gap-[7px] px-[14px] py-[7px] rounded-full text-[13px] font-semibold {{ $style['bg'] }} {{ $style['fg'] }}">
                                <i class="ti {{ $style['icon'] }}" style="font-size:15px"></i> {{ $doctorType->label_bn }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-3 pt-5">
                <div class="bg-mist rounded-[13px] px-4 py-3.5">
                    <div class="font-serif text-[23px] font-semibold leading-none">
                        @if ($doctor->ratingSummary && $doctor->ratingSummary->is_published)
                            {{ number_format((float) $doctor->ratingSummary->overall_score, 1) }} ★
                        @else
                            —
                        @endif
                    </div>
                    <div class="font-bn text-[12px] text-slate-500 mt-[5px] leading-[1.4]">
                        {{ $doctor->ratingSummary && $doctor->ratingSummary->is_published ? $doctor->ratingSummary->total_count.'টি রেটিং' : 'নতুন — যথেষ্ট রেটিং নেই' }}
                    </div>
                </div>
                <div class="bg-mist rounded-[13px] px-4 py-3.5">
                    <div class="font-serif text-[23px] font-semibold leading-none">{{ $doctor->patients_treated ? number_format($doctor->patients_treated).'+' : '—' }}</div>
                    <div class="font-bn text-[12px] text-slate-500 mt-[5px] leading-[1.4]">চিকিৎসা করা রোগী</div>
                </div>
                <div class="bg-mist rounded-[13px] px-4 py-3.5">
                    <div class="font-serif text-[23px] font-semibold leading-none">{{ $doctor->cancerTypes->count() }}</div>
                    <div class="font-bn text-[12px] text-slate-500 mt-[5px] leading-[1.4]">ক্যান্সারে বিশেষত্ব</div>
                </div>
                <div class="bg-mist rounded-[13px] px-4 py-3.5">
                    <div class="font-serif text-[23px] font-semibold leading-none">{{ $doctor->chambers->count() }}</div>
                    <div class="font-bn text-[12px] text-slate-500 mt-[5px] leading-[1.4]">চেম্বার</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-[1fr_340px] gap-6 items-start">
            <div>

                {{-- পরিচিতি ভিডিও --}}
                @php $introVideo = $doctor->videos->firstWhere('type', 'intro'); @endphp
                @if ($introVideo)
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">পরিচিতি</div>
                        <a href="{{ $introVideo->video_url }}" target="_blank" rel="noopener" class="flex border border-line rounded-[14px] overflow-hidden hover:border-slate-300">
                            <div class="w-[230px] h-[145px] relative shrink-0 bg-slate-700 flex items-center justify-center">
                                @if ($introVideo->thumbnail_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($introVideo->thumbnail_path) }}" class="w-full h-full object-cover opacity-65" alt="ডা. {{ $doctor->name_bn }}-এর পরিচিতি ভিডিও">
                                @endif
                                <div class="absolute w-[50px] h-[50px] rounded-full bg-white/95 flex items-center justify-center text-slate-900">
                                    <i class="ti ti-player-play-filled ml-0.5" style="font-size:21px"></i>
                                </div>
                                <span class="absolute bottom-[9px] right-[10px] text-[11px] bg-black/65 text-white px-[7px] py-0.5 rounded">{{ gmdate('i:s', $introVideo->duration_seconds) }}</span>
                            </div>
                            <div class="px-5 py-4 flex-1 flex flex-col justify-center">
                                <div class="font-bn text-[17px] font-semibold mb-[7px] leading-[1.35]">{{ $introVideo->title_bn }}</div>
                                @if ($introVideo->description_bn)
                                    <div class="font-bn text-[13.5px] text-slate-500 leading-[1.65] mb-[11px]">{{ $introVideo->description_bn }}</div>
                                @endif
                                <div class="font-bn text-[11.5px] text-slate-400 flex items-center gap-1.5">
                                    <i class="ti ti-video" style="font-size:13px"></i>
                                    ভিডিওটি {{ $introVideo->produced_by }} প্রযোজিত · তালিকার ক্রমে প্রভাব ফেলে না
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- ম্যাচ ইঞ্জিন --}}
                <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                    <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">এই ডাক্তার কি আমার জন্য</div>
                    <x-doctor-match :doctor="$doctor" :cancer-options="$matchCancerOptions" />
                </div>

                {{-- যেসব চিকিৎসা করেন --}}
                @if ($firstTabKey)
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4" x-data="{ tab: '{{ $firstTabKey }}' }">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">যেসব চিকিৎসা করেন</div>
                        <div class="flex gap-[7px] flex-wrap mb-4">
                            @foreach ($tabCancerTypes as $cancerType)
                                <button type="button" @click="tab = '{{ $cancerType->id }}'"
                                    :class="tab === '{{ $cancerType->id }}' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-500'"
                                    class="font-bn text-[13px] px-4 py-2 rounded-full border-[1.5px] font-medium">{{ $cancerType->name_bn }}</button>
                            @endforeach
                            @if ($doctor->offers_second_opinion)
                                <button type="button" @click="tab = 'second-opinion'"
                                    :class="tab === 'second-opinion' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-line text-slate-500'"
                                    class="font-bn text-[13px] px-4 py-2 rounded-full border-[1.5px] font-medium">দ্বিতীয় মতামত</button>
                            @endif
                        </div>

                        @foreach ($tabCancerTypes as $cancerType)
                            <div x-show="tab === '{{ $cancerType->id }}'" class="grid grid-cols-2 gap-3">
                                @foreach ($servicesByCancerType->get($cancerType->id) as $service)
                                    @php $badgeClass = $badgeColors[$service->badge_color] ?? $badgeColors['mist']; @endphp
                                    <div class="flex gap-3 items-start px-4 py-3.5 border border-line rounded-[13px]">
                                        <div class="w-[38px] h-[38px] rounded-[11px] flex items-center justify-center shrink-0 {{ $badgeClass }}">
                                            <i class="ti {{ $serviceIcons[$service->icon] ?? 'ti-stethoscope' }}" style="font-size:17px"></i>
                                        </div>
                                        <div>
                                            <div class="font-bn text-[14px] font-semibold mb-1">{{ $service->title_bn }}</div>
                                            <div class="font-bn text-[12.5px] text-slate-500 leading-[1.6] mb-2">{{ $service->description_bn }}</div>
                                            @if ($service->badge_text_bn)
                                                <span class="font-bn text-[11px] px-[9px] py-[3px] rounded-full font-semibold inline-block {{ $badgeClass }}">{{ $service->badge_text_bn }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        @if ($doctor->offers_second_opinion)
                            <div x-show="tab === 'second-opinion'" class="grid grid-cols-2 gap-3">
                                <div class="flex gap-3 items-start px-4 py-3.5 border border-line rounded-[13px]">
                                    <div class="w-[38px] h-[38px] rounded-[11px] flex items-center justify-center shrink-0 bg-blue-100">
                                        <i class="ti ti-file-description text-blue-700" style="font-size:17px"></i>
                                    </div>
                                    <div>
                                        <div class="font-bn text-[14px] font-semibold mb-1">রিপোর্ট ও বায়োপসি পর্যালোচনা</div>
                                        <div class="font-bn text-[12.5px] text-slate-500 leading-[1.6] mb-2">আপনার রিপোর্ট দেখে নিরপেক্ষ মতামত দেন।</div>
                                        <span class="font-bn text-[11px] px-[9px] py-[3px] rounded-full font-semibold inline-block bg-gold-soft text-[#7A5410]">ফি ৳{{ $doctor->second_opinion_fee }}</span>
                                    </div>
                                </div>
                                <div class="flex gap-3 items-start px-4 py-3.5 border border-line rounded-[13px]">
                                    <div class="w-[38px] h-[38px] rounded-[11px] flex items-center justify-center shrink-0 bg-teal-100">
                                        <i class="ti ti-clipboard-check text-teal-700" style="font-size:17px"></i>
                                    </div>
                                    <div>
                                        <div class="font-bn text-[14px] font-semibold mb-1">চিকিৎসা পরিকল্পনা যাচাই</div>
                                        <div class="font-bn text-[12.5px] text-slate-500 leading-[1.6]">অন্য ডাক্তারের দেওয়া পরিকল্পনা নিরপেক্ষভাবে মূল্যায়ন।</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- পেশাগত জীবন --}}
                @if ($doctor->timeline->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">পেশাগত জীবন</div>
                        <div class="flex flex-col">
                            @foreach ($doctor->timeline as $entry)
                                <div class="flex gap-4 items-start">
                                    <div class="flex flex-col items-center w-3 shrink-0">
                                        <div class="w-3 h-3 rounded-full bg-pink-600 shrink-0 mt-[5px]"></div>
                                        @if (! $loop->last)
                                            <div class="w-[1.5px] bg-line flex-1" style="min-height:18px;margin-top:4px"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 {{ ! $loop->last ? 'pb-5' : '' }}">
                                        <div class="text-[15px] font-semibold font-bn">{{ $entry->title_bn }}</div>
                                        <div class="font-bn text-[13.5px] text-slate-500 mt-0.5">{{ $entry->institution_bn }}</div>
                                        <div class="font-bn text-[12.5px] text-pink-700 mt-1 font-semibold">{{ $entry->year_label }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- শিক্ষামূলক ভিডিও --}}
                @php $educationalVideos = $doctor->videos->where('type', 'educational'); @endphp
                @if ($educationalVideos->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">শিক্ষামূলক ভিডিও</div>
                        <div class="flex flex-col gap-2.5">
                            @foreach ($educationalVideos as $video)
                                <a href="{{ $video->video_url }}" target="_blank" rel="noopener" class="border border-line rounded-[13px] overflow-hidden flex items-center hover:border-slate-300">
                                    <div class="w-[100px] h-[72px] flex items-center justify-center shrink-0 relative bg-mist">
                                        <div class="w-8 h-8 rounded-full bg-white/92 flex items-center justify-center">
                                            <i class="ti ti-player-play-filled ml-0.5" style="font-size:14px"></i>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3 flex-1">
                                        <div class="font-bn text-[14px] font-semibold mb-1 leading-[1.45]">
                                            {{ $video->title_bn }}
                                            <span class="text-[10.5px] px-[7px] py-0.5 rounded font-semibold ml-1.5 {{ $video->platform === 'youtube' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($video->platform) }}</span>
                                        </div>
                                        <div class="font-bn text-[12.5px] text-slate-400">
                                            {{ sprintf('%d:%02d', intdiv($video->duration_seconds, 60), $video->duration_seconds % 60) }}
                                            @if ($video->view_count) · {{ number_format($video->view_count) }} বার দেখা হয়েছে @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- রোগীদের ভিডিও অভিজ্ঞতা --}}
                @if ($doctor->patientTestimonials->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">রোগীদের ভিডিও অভিজ্ঞতা</div>
                        <div class="font-bn text-[13.5px] text-slate-500 leading-[1.7] mb-[18px]">সব ভিডিও রোগীর লিখিত সম্মতিতে ধারণ করা। যাঁরা মুখ দেখাতে চাননি, তাঁদের পরিচয় গোপন রাখা হয়েছে।</div>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($doctor->patientTestimonials as $testimonial)
                                @php $tBadge = $badgeColors[$testimonial->thumbnail_color_key] ?? $badgeColors['teal']; @endphp
                                <a href="{{ $testimonial->video_url }}" target="_blank" rel="noopener" class="border border-line rounded-[13px] overflow-hidden hover:border-slate-300">
                                    <div class="h-[110px] flex items-center justify-center relative {{ explode(' ', $tBadge)[0] }}">
                                        <div class="w-[38px] h-[38px] rounded-full bg-white/92 flex items-center justify-center text-slate-900">
                                            <i class="ti ti-player-play-filled ml-0.5" style="font-size:16px"></i>
                                        </div>
                                        <span class="absolute bottom-[7px] right-[9px] text-[10.5px] bg-black/60 text-white px-[6px] py-[1px] rounded">{{ sprintf('%d:%02d', intdiv($testimonial->duration_seconds, 60), $testimonial->duration_seconds % 60) }}</span>
                                    </div>
                                    <div class="px-3.5 py-3">
                                        <span class="font-bn text-[10.5px] px-[9px] py-[3px] rounded-full font-semibold inline-block mb-1.5 bg-teal-100 text-teal-700">{{ $testimonial->outcome_bn }}</span>
                                        <div class="font-bn text-[13px] font-semibold mb-0.5">
                                            {{ $testimonial->cancerType?->name_bn }}
                                            @if ($testimonial->stage) · {{ $stageLabels[$testimonial->stage] }} @endif
                                        </div>
                                        <div class="font-bn text-[12px] text-slate-400 flex items-center gap-1">
                                            <i class="ti ti-lock" style="font-size:12px"></i> {{ $testimonial->anonymized_label_bn }} · {{ $testimonial->year }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- রোগীদের গল্প --}}
                @if ($doctor->patientStories->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">রোগীদের গল্প</div>

                        @if ($doctor->storyHighlights->isNotEmpty())
                            <div class="flex gap-2 flex-wrap mb-4">
                                @foreach ($doctor->storyHighlights as $highlight)
                                    <span class="font-bn text-[12px] px-3 py-[5px] rounded-full font-semibold {{ $storyHighlightColors[$highlight->color_key] ?? $storyHighlightColors['teal'] }}">{{ $highlight->label_bn }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-col gap-3">
                            @foreach ($doctor->patientStories as $story)
                                <div class="border border-line rounded-[15px] overflow-hidden">
                                    <div class="flex gap-3.5 items-start px-5 pt-4 pb-3">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-[15px] font-semibold shrink-0 bg-pink-100 text-pink-800 font-bn">{{ mb_substr($story->patient_label_bn, 0, 1) }}</div>
                                        <div class="flex-1">
                                            <div class="font-bn text-[14.5px] font-semibold">{{ $story->patient_label_bn }}</div>
                                            <div class="font-bn text-[12.5px] text-slate-500 mt-0.5">
                                                {{ $story->cancerType?->name_bn }}
                                                @if ($story->stage) · {{ $stageLabels[$story->stage] }} @endif
                                                @if ($story->district) · {{ $story->district->name_bn }} @endif
                                                · {{ $story->year }}
                                            </div>
                                        </div>
                                        <span class="font-bn text-[11px] px-[10px] py-[3px] rounded-full font-semibold whitespace-nowrap bg-teal-100 text-teal-700">{{ $story->outcome_duration_bn }}</span>
                                    </div>
                                    <div class="font-bn text-[14px] text-slate-500 leading-[1.75] px-5 pb-3.5 italic">&ldquo;{{ $story->quote_bn }}&rdquo;</div>
                                    <div class="bg-mist px-5 py-2.5 flex items-center gap-2.5 text-[12.5px] font-bn flex-wrap">
                                        <span class="text-red-700">তখন: {{ $story->then_bn }}</span>
                                        <span class="text-slate-300">→</span>
                                        <span class="text-teal-700 font-semibold">এখন: {{ $story->now_bn }}</span>
                                        @if ($story->is_family_told || $story->is_name_changed)
                                            <span class="ml-auto text-slate-400 flex items-center gap-1 text-[11.5px]">
                                                <i class="ti {{ $story->is_family_told ? 'ti-users' : 'ti-lock' }}" style="font-size:12px"></i>
                                                {{ $story->is_family_told ? 'পরিবারের বলা গল্প' : 'নাম পরিবর্তিত' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- রোগীদের মতামত (রেটিং) --}}
                @if ($doctor->ratingSummary && $doctor->ratingSummary->is_published)
                    @php
                        $overall = (float) $doctor->ratingSummary->overall_score;
                        $fullStars = (int) floor($overall);
                        $hasHalfStar = ($overall - $fullStars) >= 0.5;
                    @endphp
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">রোগীদের মতামত</div>
                        <div class="flex gap-6 items-start">
                            <div class="text-center shrink-0 w-[90px]">
                                <div class="font-serif text-[44px] font-semibold leading-none">{{ number_format($overall, 1) }}</div>
                                <div class="flex gap-0.5 justify-center my-[7px]" style="color:#C98A1E">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="ti {{ $i < $fullStars ? 'ti-star-filled' : ($i === $fullStars && $hasHalfStar ? 'ti-star-half-filled' : 'ti-star') }}" style="font-size:14px"></i>
                                    @endfor
                                </div>
                                <div class="font-bn text-[12px] text-slate-400">{{ $doctor->ratingSummary->total_count }}টি মতামত</div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 flex-1">
                                @foreach ($ratingCriteria as $criterion)
                                    @php $score = $doctor->ratingSummary->criteria_scores[$criterion->key] ?? null; @endphp
                                    @if ($score !== null)
                                        <div class="flex items-center gap-2.5">
                                            <span class="font-bn text-[13px] text-slate-500 w-[110px] shrink-0">{{ $criterion->label_bn }}</span>
                                            <div class="flex-1 h-1.5 bg-mist rounded overflow-hidden">
                                                <div class="h-full rounded bg-slate-900" style="width:{{ $score }}%"></div>
                                            </div>
                                            <span class="font-bn text-[12.5px] w-[34px] text-right shrink-0 font-medium">{{ $score }}%</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="font-bn flex items-center gap-2.5 bg-mist rounded-[11px] px-4 py-3 mt-4 text-[12.5px] text-slate-500 leading-[1.6]">
                            <i class="ti ti-lock text-slate-500" style="font-size:16px"></i>
                            <span>সব মতামত নাম প্রকাশ ছাড়া নেওয়া। শুধু যাচাইকৃত রোগীরাই মতামত দিতে পারেন — ডাক্তার কে মতামত দিলেন তা জানতে পারেন না।</span>
                        </div>
                    </div>
                @endif

                {{-- চিকিৎসা দর্শন --}}
                @if ($doctor->philosophyPoints->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] px-7 py-6 mb-4">
                        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide mb-[9px]">চিকিৎসা দর্শন</div>
                        <div class="flex flex-col gap-3.5">
                            @foreach ($doctor->philosophyPoints as $point)
                                <div class="flex gap-3.5 items-start">
                                    <div class="w-9 h-9 rounded-[11px] bg-pink-100 flex items-center justify-center shrink-0 text-pink-700">
                                        <i class="ti ti-{{ $point->icon }}" style="font-size:17px"></i>
                                    </div>
                                    <div>
                                        <div class="font-bn text-[14.5px] font-semibold mb-1">{{ $point->title_bn }}</div>
                                        <div class="font-bn text-[13.5px] text-slate-500 leading-[1.75]">{{ $point->description_bn }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- তথ্যের উৎস নোট --}}
                <div class="font-bn flex gap-2 items-start text-[11.5px] text-slate-400 leading-[1.65] px-4 py-3 bg-white border border-line rounded-xl">
                    <i class="ti ti-info-circle" style="font-size:14px"></i>
                    <span>
                        পরিচয়, ডিগ্রি ও চেম্বারের তথ্য ডাক্তার নিজে দিয়েছেন এবং CCB যাচাই করেছে। সেবার বিবরণ, রোগীর সংখ্যা ও গল্পগুলো ডাক্তারের সাথে আলোচনা করে CCB সাজিয়েছে। রোগীদের মতামত সরাসরি রোগীদের কাছ থেকে নেওয়া।
                        @if ($doctor->last_verified_at)
                            সর্বশেষ হালনাগাদ: {{ $doctor->last_verified_at->translatedFormat('j F Y') }}।
                        @endif
                    </span>
                </div>

            </div>

            @include('pages.doctors._profile_sidebar')
        </div>
    </div>
</div>

@endsection
