<aside class="bg-white border border-line rounded-2xl p-[22px] sticky top-[90px] shadow-sm">
    <div class="text-[13px] font-semibold mb-4 flex items-center justify-between font-bn">
        <span>ফিল্টার</span>
        <button type="button"
                @click="clearFilters()"
                class="text-[12px] text-slate-400 hover:text-pink-700 font-normal cursor-pointer transition">
            সব মুছুন
        </button>
    </div>

    {{-- 1. Capability Filter (যে চিকিৎসা দরকার) --}}
    <div class="pb-4 mb-4 border-b border-line-soft">
        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 font-bn">
            যে চিকিৎসা দরকার
        </div>
        <div class="space-y-1">
            @foreach ($allCapabilities as $cap)
                @php
                    $isSelected = in_array($cap->key, $filters['capabilities'] ?? []);
                @endphp
                <label class="flex items-center gap-2.5 text-[13.5px] py-1 cursor-pointer select-none font-bn transition {{ $isSelected ? 'text-ink font-semibold' : 'text-slate-600 hover:text-ink' }}">
                    <input type="checkbox"
                           value="{{ $cap->key }}"
                           x-model="selectedCapabilities"
                           @change="updateFilters()"
                           class="hidden">
                    <div class="w-[17px] h-[17px] rounded-[5px] border-1.5 flex items-center justify-center shrink-0 transition {{ $isSelected ? 'bg-teal-700 border-teal-700 text-white' : 'border-line text-transparent' }}">
                        <i class="ti ti-check text-[11px]"></i>
                    </div>
                    <span>{{ $cap->label_bn }}</span>
                    <span class="ml-auto text-[12px] text-slate-400 font-sans">
                        {{ $capabilityCounts[$cap->key] ?? 0 }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- 2. Hospital Type Filter (হাসপাতালের ধরন) --}}
    <div class="pb-4 mb-4 border-b border-line-soft">
        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 font-bn">
            হাসপাতালের ধরন
        </div>
        <div class="space-y-1">
            @php
                $types = [
                    'govt' => 'সরকারি',
                    'private' => 'বেসরকারি',
                    'npo' => 'অলাভজনক',
                ];
            @endphp
            @foreach ($types as $typeKey => $typeLabel)
                @php
                    $isSelected = in_array($typeKey, $filters['types'] ?? []);
                @endphp
                <label class="flex items-center gap-2.5 text-[13.5px] py-1 cursor-pointer select-none font-bn transition {{ $isSelected ? 'text-ink font-semibold' : 'text-slate-600 hover:text-ink' }}">
                    <input type="checkbox"
                           value="{{ $typeKey }}"
                           x-model="selectedTypes"
                           @change="updateFilters()"
                           class="hidden">
                    <div class="w-[17px] h-[17px] rounded-[5px] border-1.5 flex items-center justify-center shrink-0 transition {{ $isSelected ? 'bg-teal-700 border-teal-700 text-white' : 'border-line text-transparent' }}">
                        <i class="ti ti-check text-[11px]"></i>
                    </div>
                    <span>{{ $typeLabel }}</span>
                    <span class="ml-auto text-[12px] text-slate-400 font-sans">
                        {{ $typeCounts[$typeKey] ?? 0 }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- 3. Division Filter (বিভাগ) --}}
    <div class="pb-4 mb-4 border-b border-line-soft">
        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 font-bn">
            বিভাগ
        </div>
        <div class="space-y-1">
            @foreach ($divisions as $division)
                @php
                    $isSelected = in_array($division->id, $filters['division_ids'] ?? []);
                @endphp
                <label class="flex items-center gap-2.5 text-[13.5px] py-1 cursor-pointer select-none font-bn transition {{ $isSelected ? 'text-ink font-semibold' : 'text-slate-600 hover:text-ink' }}">
                    <input type="checkbox"
                           value="{{ $division->id }}"
                           x-model="selectedDivisions"
                           @change="updateFilters()"
                           class="hidden">
                    <div class="w-[17px] h-[17px] rounded-[5px] border-1.5 flex items-center justify-center shrink-0 transition {{ $isSelected ? 'bg-teal-700 border-teal-700 text-white' : 'border-line text-transparent' }}">
                        <i class="ti ti-check text-[11px]"></i>
                    </div>
                    <span>{{ $division->name_bn }}</span>
                    <span class="ml-auto text-[12px] text-slate-400 font-sans">
                        {{ $divisionCounts[$division->id] ?? 0 }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- 4. Facilities Filter (সুবিধা) --}}
    <div>
        <div class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 font-bn">
            সুবিধা
        </div>
        <div class="space-y-1">
            @php
                $facilityOptions = [
                    'emergency_24h' => '২৪ ঘণ্টা জরুরি সেবা',
                    'has_video' => 'চেনার ভিডিও আছে',
                    'has_financial_aid' => 'আর্থিক সহায়তা তহবিল',
                    'has_accommodation' => 'রোগীর থাকার ব্যবস্থা',
                    'has_blood_bank' => 'নিজস্ব ব্লাড ব্যাংক',
                    'has_female_oncologist' => 'নারী অনকোলজিস্ট আছেন',
                ];
            @endphp
            @foreach ($facilityOptions as $fKey => $fLabel)
                @php
                    $isSelected = in_array($fKey, $filters['facilities'] ?? []);
                @endphp
                <label class="flex items-center gap-2.5 text-[13.5px] py-1 cursor-pointer select-none font-bn transition {{ $isSelected ? 'text-ink font-semibold' : 'text-slate-600 hover:text-ink' }}">
                    <input type="checkbox"
                           value="{{ $fKey }}"
                           x-model="selectedFacilities"
                           @change="updateFilters()"
                           class="hidden">
                    <div class="w-[17px] h-[17px] rounded-[5px] border-1.5 flex items-center justify-center shrink-0 transition {{ $isSelected ? 'bg-teal-700 border-teal-700 text-white' : 'border-line text-transparent' }}">
                        <i class="ti ti-check text-[11px]"></i>
                    </div>
                    <span>{{ $fLabel }}</span>
                    <span class="ml-auto text-[12px] text-slate-400 font-sans">
                        {{ $facilityCounts[$fKey] ?? 0 }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</aside>
