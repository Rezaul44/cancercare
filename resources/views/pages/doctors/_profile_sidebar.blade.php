{{--
    docs/prototypes/doctor_profile.html-এর .side — অ্যাপয়েন্টমেন্ট শিট, চেম্বার তালিকা, WhatsApp
    পরামর্শ কার্ড ও বিশেষত্ব চিপ। $doctor-এ প্রয়োজনীয় সব relation আগে থেকেই eager-load করা
    (DoctorProfileController::show)।
--}}
@php
    $chipItems = collect();

    foreach ($doctor->services as $service) {
        $isPrimary = optional($doctor->cancerTypes->firstWhere('id', $service->cancer_type_id))->pivot?->is_primary ?? false;
        $chipItems->push(['label' => $service->title_bn, 'highlighted' => $isPrimary]);
    }

    foreach ($doctor->cancerTypes as $cancerType) {
        if (! $cancerType->pivot->is_primary) {
            $chipItems->push(['label' => $cancerType->name_bn, 'highlighted' => false]);
        }
    }
@endphp

<div class="flex flex-col gap-4 sticky top-[90px]" x-data="{ sheetOpen: false, whatsappModal: false }">
    <div class="bg-white border-2 border-slate-900 rounded-2xl px-5 py-4">
        <div class="font-bn text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide mb-3.5">অ্যাপয়েন্টমেন্ট</div>
        <button type="button" @click="sheetOpen = !sheetOpen"
            class="font-bn w-full p-3.5 bg-slate-900 text-white border-none rounded-[11px] text-[15px] font-semibold cursor-pointer hover:bg-slate-700 flex items-center justify-center gap-2">
            <span>অ্যাপয়েন্টমেন্ট নিন</span>
            <i class="ti ti-chevron-down text-sm transition-transform duration-200" :class="{ 'rotate-180': sheetOpen }"></i>
        </button>

        <div x-show="sheetOpen" x-cloak class="mt-3 flex flex-col gap-2">
            @foreach ($doctor->chambers as $chamber)
                <div @click="document.getElementById('chamber-{{ $chamber->id }}')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                    class="flex items-center gap-3 px-3.5 py-3 border border-line rounded-xl hover:border-slate-400 hover:bg-[#FCFCFB] cursor-pointer transition-colors">
                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0 bg-blue-100">
                        <i class="ti ti-building-hospital text-[17px] text-blue-700"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[13.5px] font-semibold text-ink">{{ $chamber->name_bn }}</div>
                        <div class="font-bn text-[12px] text-slate-400 mt-0.5">৳{{ $chamber->fee }} · {{ $chamber->days_bn }}</div>
                    </div>
                </div>
            @endforeach

            @if ($doctor->offers_whatsapp)
                <div @click="whatsappModal = true"
                    class="flex items-center gap-3 px-3.5 py-3 border border-line rounded-xl hover:border-teal-400 hover:bg-[#F0FAF7] cursor-pointer transition-colors">
                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0" style="background:#E8F8ED">
                        <i class="ti ti-brand-whatsapp text-[17px]" style="color:#1D9E75"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[13.5px] font-semibold text-teal-700">WhatsApp পরামর্শ</div>
                        <div class="font-bn text-[12px] text-slate-400 mt-0.5">৳{{ $doctor->whatsapp_fee }} · {{ $doctor->whatsapp_response_hours }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($doctor->chambers->isNotEmpty())
        <div class="bg-white border border-line rounded-2xl px-5 py-4">
            <div class="font-bn text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide mb-3.5">চেম্বার</div>

            @foreach ($doctor->chambers as $chamber)
                <div id="chamber-{{ $chamber->id }}" class="border border-line rounded-xl overflow-hidden transition-all duration-300 {{ ! $loop->last ? 'mb-2.5' : '' }}">
                    <div class="px-4 py-3 flex justify-between items-start gap-2.5">
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold">{{ $chamber->name_bn }}</div>
                            <div class="font-bn text-[12px] text-slate-400 mt-0.5">{{ $chamber->address_bn }}</div>
                        </div>
                        <div class="font-bn text-[15px] font-semibold whitespace-nowrap {{ ($chamber->type->value ?? $chamber->type) === 'govt' ? 'text-teal-700' : '' }}">৳{{ $chamber->fee }}</div>
                    </div>
                    <div class="bg-mist px-4 py-2.5 flex flex-col gap-1">
                        <div class="font-bn text-[12px] text-slate-500 flex items-center gap-1.5"><i class="ti ti-calendar text-slate-300"></i> {{ $chamber->days_bn }}</div>
                        <div class="font-bn text-[12px] text-slate-500 flex items-center gap-1.5"><i class="ti ti-clock text-slate-300"></i> {{ \Illuminate\Support\Carbon::parse($chamber->time_from)->format('g:i A') }} – {{ \Illuminate\Support\Carbon::parse($chamber->time_to)->format('g:i A') }}</div>
                    </div>
                    @if ($chamber->next_available_note)
                        <div class="font-bn px-4 py-2 border-t border-line text-[12px] text-teal-700 font-semibold">পরের খালি সময়: {{ $chamber->next_available_note }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($doctor->offers_whatsapp)
        <div id="whatsapp-card" class="bg-white border border-line rounded-2xl px-5 py-4">
            <div class="font-bn text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide mb-3.5">অনলাইন পরামর্শ</div>
            <div class="bg-teal-50 border rounded-[13px] p-4" style="border-color:#BFE5DC">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0" style="background:#25D366">
                        <i class="ti ti-brand-whatsapp text-[19px] text-white"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[13.5px] font-semibold text-teal-700">WhatsApp পরামর্শ</div>
                        <div class="font-bn text-[11.5px] mt-0.5" style="color:#166B5C">রিপোর্ট পাঠিয়ে মতামত নিন</div>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5 mb-3">
                    <div class="flex justify-between text-[12.5px] font-bn"><span style="color:#166B5C">ফি</span><span class="text-teal-700 font-semibold">৳{{ $doctor->whatsapp_fee }}</span></div>
                    <div class="flex justify-between text-[12.5px] font-bn"><span style="color:#166B5C">উত্তর</span><span class="text-teal-700 font-semibold">{{ $doctor->whatsapp_response_hours }}</span></div>
                </div>
                <button type="button" @click="whatsappModal = true"
                    class="font-bn w-full p-3 bg-teal-700 text-white border-none rounded-[10px] text-[13.5px] font-semibold cursor-pointer hover:bg-[#095546] transition-colors">WhatsApp-এ যোগাযোগ</button>
            </div>
        </div>

        {{-- WhatsApp Consultation Instructions Modal --}}
        <div x-show="whatsappModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click.self="whatsappModal = false">
            <div class="bg-white rounded-2xl max-w-[460px] w-full p-6 shadow-xl border border-line" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-teal-100 text-teal-700">
                            <i class="ti ti-brand-whatsapp text-xl"></i>
                        </div>
                        <div class="font-bn text-[16px] font-semibold text-ink">WhatsApp পরামর্শ সেবা</div>
                    </div>
                    <button type="button" @click="whatsappModal = false" class="text-slate-400 hover:text-ink text-xl">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <div class="font-bn text-[13.5px] text-slate-600 mb-4 leading-[1.65]">
                    <b>{{ $doctor->name_bn }}</b>-কে আপনার মেডিকেল রিপোর্ট পাঠিয়ে অনলাইনে মতামত নিতে পারেন।
                </div>

                <div class="bg-mist rounded-xl p-3.5 mb-4 text-[13px] font-bn flex flex-col gap-2 border border-line">
                    <div class="flex justify-between">
                        <span class="text-slate-500">পরামর্শ ফি:</span>
                        <span class="font-semibold text-ink">৳{{ $doctor->whatsapp_fee }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">সম্ভাব্য উত্তরের সময়:</span>
                        <span class="font-semibold text-teal-700">{{ $doctor->whatsapp_response_hours }}</span>
                    </div>
                </div>

                <div class="font-bn text-[12.5px] text-slate-600 mb-5 leading-[1.65]">
                    <div class="font-semibold text-ink mb-1.5">পরামর্শ নেওয়ার ধাপসমূহ:</div>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600">
                        <li>WhatsApp-এ রোগীর নাম, বয়স এবং সমস্যা সংক্ষেপে লিখুন।</li>
                        <li>সর্বশেষ বায়োপসি / হিস্টোপ্যাথলজি রিপোর্ট ও স্ক্যানের ছবি সংযুক্ত করুন।</li>
                        <li>নির্দেশনা অনুযায়ী ফি পরিশোধের পর ডাক্তার রিপোর্ট পর্যালোচনা করবেন।</li>
                    </ol>
                </div>

                <div class="flex gap-2.5">
                    <a href="https://wa.me/?text={{ urlencode('আসসালামু আলাইকুম। আমি '.$doctor->name_bn.'-এর WhatsApp পরামর্শ সেবা নিতে আগ্রহী।') }}"
                        target="_blank" rel="noopener"
                        class="font-bn flex-1 py-3 px-4 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-center font-medium text-[14px] flex items-center justify-center gap-2">
                        <i class="ti ti-brand-whatsapp text-lg"></i>
                        <span>WhatsApp-এ বার্তা পাঠান</span>
                    </a>
                    <button type="button" @click="whatsappModal = false"
                        class="font-bn py-3 px-4 bg-mist hover:bg-slate-200 text-slate-700 rounded-xl font-medium text-[14px]">
                        বন্ধ করুন
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($chipItems->isNotEmpty())
        <div class="bg-white border border-line rounded-2xl px-5 py-4">
            <div class="font-bn text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide mb-3.5">বিশেষত্ব</div>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($chipItems as $chip)
                    <span class="font-bn text-[11.5px] px-3 py-1 rounded-full border {{ $chip['highlighted'] ? 'bg-pink-100 border-pink-200 text-pink-800 font-semibold' : 'border-line text-slate-500' }}">{{ $chip['label'] }}</span>
                @endforeach
            </div>
        </div>
    @endif
</div>
