{{-- docs/prototypes/doctor_directory.html-এর .dcard হুবহু অনুসরণ করে। Alpine directive নেই — এই partial AJAX swap-এও ব্যবহার হয়। --}}
@php
    $activeChambers = $doctor->chambers->where('is_active', true)->values();
    $primaryChamber = $activeChambers->first();
    $minFee = $activeChambers->min('fee');
    $rating = $doctor->ratingSummary;
@endphp

<div class="dcard flex gap-[18px] p-[22px] rounded-2xl border border-line bg-white hover:border-teal-600 transition-colors shadow-xs">
    @if ($doctor->photo_path)
        <img class="w-[76px] h-[76px] rounded-[15px] object-cover shrink-0 bg-mist"
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->photo_path) }}"
            alt="{{ $doctor->name_bn }} — অনকোলজিস্ট"
            loading="lazy"
            decoding="async"
            width="76"
            height="76">
    @else
        <div class="w-[76px] h-[76px] rounded-[15px] shrink-0 bg-mist flex items-center justify-center text-slate-300">
            <i class="ti ti-user text-[32px]"></i>
        </div>
    @endif

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2.5 mb-1">
            <a href="{{ route('doctors.show', $doctor) }}" class="font-serif text-[20px] font-semibold text-ink hover:underline">{{ $doctor->name_bn }}</a>
            <span class="font-bn inline-flex items-center gap-1 text-[11.5px] font-medium text-teal-700 bg-teal-100 px-2.5 py-1 rounded-full">
                <i class="ti ti-rosette-discount-check"></i> BMDC যাচাইকৃত
            </span>
        </div>

        <div class="font-bn text-[13.5px] text-slate-500 mb-2.5">
            {{ $doctor->current_position_bn }} · {{ $doctor->degrees_line_bn }} · {{ $doctor->experience_years }} বছরের অভিজ্ঞতা
        </div>

        @if (! empty($doctor->reason_tags))
            <div class="flex gap-2 flex-wrap mb-3">
                @foreach ($doctor->reason_tags as $tag)
                    <span class="font-bn inline-flex items-center gap-[5px] text-[11.5px] px-2.5 py-1 rounded-full bg-mist text-ink/60">
                        <i class="ti ti-check text-teal-500 text-[13px]"></i> {{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif

        @foreach ($activeChambers->take(2) as $chamber)
            <div class="font-bn flex items-center gap-2 text-[13px] text-slate-500 py-0.5">
                <i class="ti ti-building-hospital text-slate-500"></i>
                <b class="text-ink font-medium">{{ $chamber->name_bn }}</b> — {{ match ($chamber->type->value ?? $chamber->type) {
                    'govt' => 'সরকারি', 'private' => 'বেসরকারি', 'npo' => 'অলাভজনক', default => '',
                } }} · {{ $chamber->days_bn }} · ৳{{ $chamber->fee }}
            </div>
            @if ($chamber->next_available_note)
                <div class="font-bn flex items-center gap-2 text-[13px] text-slate-500 py-0.5">
                    <i class="ti ti-clock text-slate-500"></i> পরের খালি সময়: {{ $chamber->next_available_note }}
                </div>
            @endif
        @endforeach
    </div>

    <div class="w-[150px] shrink-0 border-l border-line pl-[18px] flex flex-col">
        <div class="mb-3">
            <div class="font-serif text-[19px] font-semibold text-ink">
                @if ($rating && $rating->is_published)
                    {{ number_format((float) $rating->overall_score, 1) }} ★
                @else
                    —
                @endif
            </div>
            <div class="font-bn text-[12px] text-slate-500">
                @if ($rating && $rating->is_published)
                    {{ $rating->total_count }}টি রেটিং
                @else
                    নতুন — যথেষ্ট রেটিং নেই
                @endif
            </div>
        </div>
        <div class="mb-4">
            <div class="font-serif text-[19px] font-semibold text-ink">{{ $minFee !== null ? '৳'.$minFee : '—' }}</div>
            <div class="font-bn text-[12px] text-slate-500">চেম্বার ফি</div>
        </div>
        <a href="{{ route('doctors.show', $doctor) }}" class="font-bn mt-auto py-2.5 rounded-[9px] font-semibold bg-teal-700 text-white hover:bg-teal-800 border border-teal-600 shadow-sm text-[13.5px] text-center transition">প্রোফাইল দেখুন</a>
    </div>
</div>
