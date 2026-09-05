@props(['hospital'])

<div class="bg-white border border-line rounded-[18px] overflow-hidden hover:border-slate-300 transition duration-150 shadow-sm flex flex-col justify-between">
    <div class="p-[22px] pb-[18px] flex flex-col md:flex-row gap-[18px]">
        {{-- Hospital Photo --}}
        <div class="w-full md:w-[130px] h-[110px] md:h-[96px] rounded-xl overflow-hidden shrink-0 bg-mist">
            @if ($hospital->cover_photo_path)
                <img src="{{ Str::startsWith($hospital->cover_photo_path, ['http://', 'https://']) ? $hospital->cover_photo_path : asset('storage/' . $hospital->cover_photo_path) }}"
                     alt="{{ $hospital->name_bn }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-300 bg-mist">
                    <i class="ti ti-building-hospital text-4xl"></i>
                </div>
            @endif
        </div>

        {{-- Main Details --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start gap-2.5 mb-1.5 flex-wrap">
                <a href="{{ route('hospitals.show', $hospital) }}" class="font-serif text-[21px] font-semibold tracking-tight text-ink hover:text-pink-700 leading-snug">
                    {{ $hospital->name_bn }}
                </a>
                @php
                    $typeClass = match ($hospital->type?->value ?? $hospital->type) {
                        'govt' => 'bg-teal-100 text-teal-700',
                        'private' => 'bg-[#E6F1FB] text-[#0C447C]',
                        'npo' => 'bg-gold-soft text-[#7A5410]',
                        default => 'bg-mist text-slate-500',
                    };
                @endphp
                <span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold whitespace-nowrap {{ $typeClass }}">
                    {{ $hospital->type?->labelBn() ?? $hospital->type }}
                </span>
            </div>

            <div class="text-[13.5px] text-slate-500 flex items-center gap-1.5 mb-3 font-bn">
                <i class="ti ti-map-pin text-[15px] text-slate-300"></i>
                <span>{{ $hospital->address_bn }}</span>
                @if ($hospital->district)
                    <span class="text-slate-300">·</span>
                    <span>{{ $hospital->district->name_bn }}</span>
                @endif
            </div>

            {{-- Capability Badges (Shows both available and unavailable) --}}
            <div class="flex gap-1.5 flex-wrap">
                @php
                    $caps = $hospital->capabilities->take(6);
                @endphp
                @foreach ($caps as $cap)
                    @php
                        $capStatus = $cap->status?->value ?? $cap->status;
                        $badgeStyle = match ($capStatus) {
                            'available' => 'bg-teal-50 text-teal-700',
                            'limited' => 'bg-gold-soft text-[#7A5410]',
                            'not_available' => 'bg-mist text-slate-400',
                            default => 'bg-mist text-slate-400',
                        };
                        $icon = match ($capStatus) {
                            'available' => 'ti-check',
                            'limited' => 'ti-alert-triangle',
                            'not_available' => 'ti-x',
                            default => 'ti-minus',
                        };
                        $label = $cap->capability?->label_bn ?? '';
                        if ($capStatus === 'not_available') {
                            $label .= ' নেই';
                        } elseif ($capStatus === 'limited') {
                            $label .= ' সীমিত';
                        }
                    @endphp
                    <span class="text-[11.5px] px-2.5 py-1 rounded-lg flex items-center gap-1 font-medium font-bn {{ $badgeStyle }}">
                        <i class="ti {{ $icon }} text-[13px]"></i>
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Side stats --}}
        <div class="w-full md:w-[180px] shrink-0 md:border-l border-line-soft md:pl-[18px] flex md:flex-col justify-between md:justify-center gap-3">
            @if ($hospital->outdoor_fee !== null)
                <div>
                    <div class="text-[16px] font-semibold font-serif text-ink">৳{{ number_format($hospital->outdoor_fee) }}</div>
                    <div class="text-[11.5px] text-slate-500 font-bn">
                        {{ $hospital->type?->value === 'private' ? 'ডাক্তারের ফি' : 'আউটডোর টিকিট' }}
                    </div>
                </div>
            @endif

            @if ($hospital->oncologist_count)
                <div>
                    <div class="text-[16px] font-semibold font-serif text-ink">{{ $hospital->oncologist_count }} জন</div>
                    <div class="text-[11.5px] text-slate-500 font-bn">অনকোলজিস্ট</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Card Footer --}}
    <div class="border-t border-line bg-off px-6 py-3 flex items-center gap-4 flex-wrap text-sm">
        @php
            $firstWait = $hospital->waitTimes->first();
        @endphp
        @if ($firstWait)
            <span class="text-[12.5px] text-slate-600 flex items-center gap-1.5 font-bn">
                <i class="ti ti-clock text-[14px] text-slate-400"></i>
                {{ $firstWait->label_bn }}:
                {{ $firstWait->min_weeks == $firstWait->max_weeks ? "{$firstWait->min_weeks} সপ্তাহ" : "{$firstWait->min_weeks}–{$firstWait->max_weeks} সপ্তাহ" }}
            </span>
        @endif

        @if ($hospital->bed_count)
            <span class="text-[12.5px] text-slate-600 flex items-center gap-1.5 font-bn">
                <i class="ti ti-bed text-[14px] text-slate-400"></i>
                {{ $hospital->bed_count }} শয্যা
            </span>
        @endif

        @if ($hospital->videos->isNotEmpty())
            <span class="text-[12px] text-teal-700 bg-teal-100 px-2.5 py-0.5 rounded-full font-semibold flex items-center gap-1 font-bn">
                <i class="ti ti-video text-[13px]"></i>
                চেনার ভিডিও আছে
            </span>
        @endif

        @if ($hospital->emergency_24h)
            <span class="text-[12px] text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-full font-medium flex items-center gap-1 font-bn">
                <i class="ti ti-ambulance text-[13px]"></i>
                ২৪ঘণ্টা জরুরি
            </span>
        @endif

        <a href="{{ route('hospitals.show', $hospital) }}" class="ml-auto text-[13px] px-4 py-2 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-700 font-bn transition inline-block">
            বিস্তারিত
        </a>
    </div>
</div>
