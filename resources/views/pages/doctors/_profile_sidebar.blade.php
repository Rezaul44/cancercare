{{--
    docs/prototypes/doctor_profile.html-এর .side — বিভিন্ন ধরনের অ্যাপয়েন্টমেন্ট তালিকা, চেম্বার পপআপ, WhatsApp
    পরামর্শ কার্ড ও বিশেষত্ব চিপ।
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

<div class="flex flex-col gap-4 sticky top-[90px]" x-data="{ 
    sheetOpen: true, 
    whatsappModal: false, 
    selectedChamber: null 
}">
    {{-- অ্যাপয়েন্টমেন্ট মূল বক্স --}}
    <div class="bg-white border-2 border-slate-900 rounded-2xl px-5 py-5 shadow-sm">
        <div class="flex items-center justify-between mb-3.5">
            <div class="font-bn text-[12px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                <i class="ti ti-calendar-event text-pink-600 text-base"></i>
                <span>অ্যাপয়েন্টমেন্ট সেবা সমূহ</span>
            </div>
            <span class="font-bn text-[11px] bg-teal-50 text-teal-700 font-semibold px-2 py-0.5 rounded-full border border-teal-200">সরাসরি বুকিং</span>
        </div>

        <p class="font-bn text-[13px] text-slate-500 leading-relaxed mb-4">
            আপনার সুবিধাজনক পদ্ধতিতে বিশেষজ্ঞ চিকিৎসকের সাথে অ্যাপয়েন্টমেন্ট বা পরামর্শ গ্রহণ করুন:
        </p>

        {{-- তালিকা: বিভিন্ন ধরনের অ্যাপয়েন্টমেন্ট --}}
        <div class="flex flex-col gap-3">
            {{-- ১. হাসপাতাল / চেম্বার ভিত্তিক সরাসরি অ্যাপয়েন্টমেন্ট --}}
            @if ($doctor->chambers->isNotEmpty())
                <div class="border border-line rounded-xl p-3.5 bg-mist/50">
                    <div class="font-bn text-[11.5px] font-bold text-slate-500 uppercase tracking-wide mb-2.5 flex items-center gap-1.5">
                        <i class="ti ti-building-hospital text-blue-600 text-sm"></i>
                        <span>হাসপাতাল ও চেম্বার সাক্ষাৎকার</span>
                    </div>

                    <div class="flex flex-col gap-2">
                        @foreach ($doctor->chambers as $chamber)
                            @php
                                $chamberPhone = $chamber->hospital?->phone ?: '০৯৬১১-৭৭৭৮৮৮';
                                $timeStr = \Illuminate\Support\Carbon::parse($chamber->time_from)->format('g:i A') . ' – ' . \Illuminate\Support\Carbon::parse($chamber->time_to)->format('g:i A');
                            @endphp
                            <div class="bg-white border border-line rounded-xl p-3 hover:border-slate-400 transition-all">
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <div>
                                        <div class="font-bn text-[14px] font-bold text-ink leading-snug">{{ $chamber->name_bn }}</div>
                                        <div class="font-bn text-[12px] text-slate-500 mt-0.5">
                                            <i class="ti ti-map-pin text-[12px] text-slate-400"></i> {{ $chamber->address_bn }} ({{ $chamber->district->name_bn }})
                                        </div>
                                    </div>
                                    <span class="font-bn text-[14px] font-bold text-ink shrink-0 bg-mist px-2.5 py-1 rounded-lg border border-line">৳{{ $chamber->fee }}</span>
                                </div>

                                <div class="font-bn text-[12px] text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1 mb-2.5">
                                    <span class="flex items-center gap-1 text-slate-500"><i class="ti ti-calendar text-xs"></i> {{ $chamber->days_bn }}</span>
                                    <span class="flex items-center gap-1 text-slate-500"><i class="ti ti-clock text-xs"></i> {{ $timeStr }}</span>
                                </div>

                                @if ($chamber->next_available_note)
                                    <div class="font-bn text-[11.5px] text-teal-700 font-semibold mb-2 bg-teal-50 px-2.5 py-1 rounded border border-teal-100 flex items-center gap-1">
                                        <i class="ti ti-clock-check text-xs"></i> পরের খালি সময়: {{ $chamber->next_available_note }}
                                    </div>
                                @endif

                                <button type="button"
                                    @click="selectedChamber = {
                                        id: {{ $chamber->id }},
                                        name: '{{ addslashes($chamber->name_bn) }}',
                                        hospital: '{{ addslashes($chamber->hospital?->name_bn ?? '') }}',
                                        phone: '{{ $chamberPhone }}',
                                        address: '{{ addslashes($chamber->address_bn) }}',
                                        district: '{{ addslashes($chamber->district->name_bn) }}',
                                        fee: '{{ $chamber->fee }}',
                                        days: '{{ addslashes($chamber->days_bn) }}',
                                        time: '{{ addslashes($timeStr) }}',
                                        wait: '{{ $chamber->avg_wait_minutes ?? '' }}',
                                        nextAvailable: '{{ addslashes($chamber->next_available_note ?? '') }}'
                                    }"
                                    class="font-bn w-full py-2 px-3 bg-slate-900 text-white rounded-lg text-[13px] font-semibold hover:bg-slate-800 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="ti ti-phone-call text-xs"></i>
                                    <span>সিরিয়াল বুকিং তথ্য ও ফোন নম্বর</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ২. WhatsApp অনলাইন পরামর্শ --}}
            @if ($doctor->offers_whatsapp)
                <div class="border border-teal-200 rounded-xl p-3.5 bg-teal-50/60">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#25D366] text-white flex items-center justify-center shrink-0">
                                <i class="ti ti-brand-whatsapp text-base"></i>
                            </div>
                            <div>
                                <div class="font-bn text-[14px] font-bold text-teal-900">WhatsApp অনলাইন পরামর্শ</div>
                                <div class="font-bn text-[11.5px] text-teal-700">রিপোর্ট পাঠিয়ে দ্রুত চিকিৎসকের মতামত নিন</div>
                            </div>
                        </div>
                        <span class="font-bn text-[13px] font-bold text-teal-800">৳{{ $doctor->whatsapp_fee }}</span>
                    </div>

                    <div class="font-bn text-[11.5px] text-teal-700 mb-2.5 flex items-center gap-1.5">
                        <i class="ti ti-clock-hour-4 text-xs"></i> সম্ভাব্য উত্তর: {{ $doctor->whatsapp_response_hours }}
                    </div>

                    <button type="button" @click="whatsappModal = true"
                        class="font-bn w-full py-2 px-3 bg-teal-700 text-white rounded-lg text-[13px] font-semibold hover:bg-teal-800 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="ti ti-message-circle text-xs"></i>
                        <span>WhatsApp পরামর্শ নির্দেশিকা</span>
                    </button>
                </div>
            @endif

            {{-- ৩. অনলাইন দ্বিতীয় মতামত --}}
            @if ($doctor->offers_second_opinion)
                <div class="border border-blue-200 rounded-xl p-3.5 bg-blue-50/60">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                                <i class="ti ti-stethoscope text-base"></i>
                            </div>
                            <div>
                                <div class="font-bn text-[14px] font-bold text-blue-950">দ্বিতীয় মতামত (Second Opinion)</div>
                                <div class="font-bn text-[11.5px] text-blue-700">পূর্বের রিপোর্ট ও প্ল্যানের নিরপেক্ষ পর্যালোচনা</div>
                            </div>
                        </div>
                        <span class="font-bn text-[13px] font-bold text-blue-900">৳{{ $doctor->second_opinion_fee }}</span>
                    </div>

                    <a href="{{ route('second-opinion.request', ['doctor_id' => $doctor->id]) }}"
                        class="font-bn w-full py-2 px-3 bg-blue-700 text-white rounded-lg text-[13px] font-semibold hover:bg-blue-800 transition flex items-center justify-center gap-1.5 text-center">
                        <i class="ti ti-file-certificate text-xs"></i>
                        <span>দ্বিতীয় মতামতের জন্য আবেদন করুন</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- চেম্বার অ্যাপয়েন্টমেন্ট মডাল (পপআপ) --}}
    <div x-show="selectedChamber" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="selectedChamber = null">
        <div class="bg-white rounded-2xl max-w-[490px] w-full p-6 shadow-2xl border border-line text-left" @click.stop>
            <div class="flex items-start justify-between pb-3.5 border-b border-line mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <i class="ti ti-building-hospital text-2xl"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[16.5px] font-bold text-ink" x-text="selectedChamber?.name"></div>
                        <div class="font-bn text-[12px] text-slate-500" x-text="selectedChamber?.district + ' জেলা'"></div>
                    </div>
                </div>
                <button type="button" @click="selectedChamber = null" class="text-slate-400 hover:text-ink text-2xl p-1 leading-none">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            {{-- চেম্বারের বিস্তারিত ডাটা গ্রিড --}}
            <div class="bg-mist rounded-xl p-4 mb-4 font-bn text-[13px] flex flex-col gap-2.5 border border-line">
                <div class="flex justify-between items-start">
                    <span class="text-slate-500 shrink-0">ঠিকানা:</span>
                    <span class="font-medium text-ink text-right ml-2" x-text="selectedChamber?.address"></span>
                </div>
                <div class="flex justify-between items-center border-t border-line pt-2">
                    <span class="text-slate-500">বসার দিনসমূহ:</span>
                    <span class="font-semibold text-ink" x-text="selectedChamber?.days"></span>
                </div>
                <div class="flex justify-between items-center border-t border-line pt-2">
                    <span class="text-slate-500">চেম্বারের সময়:</span>
                    <span class="font-semibold text-teal-700" x-text="selectedChamber?.time"></span>
                </div>
                <div class="flex justify-between items-center border-t border-line pt-2">
                    <span class="text-slate-500">কনসালটেশন ফি:</span>
                    <span class="font-bold text-ink text-[14px]">৳<span x-text="selectedChamber?.fee"></span></span>
                </div>
                <template x-if="selectedChamber?.nextAvailable">
                    <div class="flex justify-between items-center border-t border-line pt-2">
                        <span class="text-slate-500">পরের খালি সময়:</span>
                        <span class="font-semibold text-teal-700" x-text="selectedChamber?.nextAvailable"></span>
                    </div>
                </template>
                <template x-if="selectedChamber?.wait">
                    <div class="flex justify-between items-center border-t border-line pt-2">
                        <span class="text-slate-500">গড় অপেক্ষার সময়:</span>
                        <span class="font-medium text-slate-700"><span x-text="selectedChamber?.wait"></span> মিনিট</span>
                    </div>
                </template>
            </div>

            {{-- রোগীর জন্য সিরিয়াল নির্দেশিকা --}}
            <div class="font-bn text-[12.5px] text-slate-600 mb-5 leading-[1.65]">
                <div class="font-semibold text-ink mb-1 flex items-center gap-1.5">
                    <i class="ti ti-info-circle text-blue-600"></i>
                    <span>সিরিয়াল ও সাক্ষাতের নিয়মাবলী:</span>
                </div>
                <ol class="list-decimal list-inside space-y-1 text-slate-600">
                    <li>নিচের নম্বরে ফোন করে রোগীর নাম ও পূর্বের রোগীর ফাইল থাকলে তা জানিয়ে সিরিয়াল নিশ্চিত করুন।</li>
                    <li>চেম্বারে আসার সময় সাম্প্রতিক সকল বায়োপসি, স্ক্যান ও প্রেসক্রিপশন মূল ফাইলসহ সাথে রাখুন।</li>
                    <li>চেম্বার শুরুর অন্তত ৩০ মিনিট পূর্বে উপস্থিত হয়ে টিকিট সংগ্রহ করুন।</li>
                </ol>
            </div>

            {{-- সরাসরি কল করার বাটন --}}
            <div class="flex flex-col sm:flex-row gap-2.5">
                <a :href="'tel:' + (selectedChamber?.phone || '')"
                    class="font-bn flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-center font-bold text-[14.5px] flex items-center justify-center gap-2 shadow-md">
                    <i class="ti ti-phone-call text-lg text-teal-400"></i>
                    <span>সিরিয়ালের জন্য কল করুন (<span x-text="selectedChamber?.phone"></span>)</span>
                </a>
                <button type="button" @click="selectedChamber = null"
                    class="font-bn py-3 px-5 bg-mist hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-[14px]">
                    বন্ধ করুন
                </button>
            </div>
        </div>
    </div>

    {{-- WhatsApp Consultation Instructions Modal --}}
    @if ($doctor->offers_whatsapp)
        <div x-show="whatsappModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="whatsappModal = false">
            <div class="bg-white rounded-2xl max-w-[460px] w-full p-6 shadow-2xl border border-line text-left" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#25D366] text-white">
                            <i class="ti ti-brand-whatsapp text-2xl"></i>
                        </div>
                        <div class="font-bn text-[16.5px] font-bold text-ink">WhatsApp পরামর্শ সেবা</div>
                    </div>
                    <button type="button" @click="whatsappModal = false" class="text-slate-400 hover:text-ink text-2xl p-1 leading-none">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <div class="font-bn text-[13.5px] text-slate-600 mb-4 leading-[1.65]">
                    <b>{{ $doctor->name_bn }}</b>-কে আপনার মেডিকেল রিপোর্ট পাঠিয়ে অনলাইনে মতামত নিতে পারেন।
                </div>

                <div class="bg-mist rounded-xl p-3.5 mb-4 text-[13px] font-bn flex flex-col gap-2 border border-line">
                    <div class="flex justify-between">
                        <span class="text-slate-500">পরামর্শ ফি:</span>
                        <span class="font-bold text-ink">৳{{ $doctor->whatsapp_fee }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">সম্ভাব্য উত্তরের সময়:</span>
                        <span class="font-semibold text-teal-700">{{ $doctor->whatsapp_response_hours }}</span>
                    </div>
                </div>

                <div class="font-bn text-[12.5px] text-slate-600 mb-5 leading-[1.65]">
                    <div class="font-semibold text-ink mb-1.5 flex items-center gap-1.5">
                        <i class="ti ti-clipboard-list text-teal-600"></i>
                        <span>পরামর্শ নেওয়ার ধাপসমূহ:</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600">
                        <li>WhatsApp-এ রোগীর নাম, বয়স এবং সমস্যা সংক্ষেপে লিখুন।</li>
                        <li>সর্বশেষ বায়োপসি / হিস্টোপ্যাথলজি রিপোর্ট ও স্ক্যানের ছবি সংযুক্ত করুন।</li>
                        <li>নির্দেশনা অনুযায়ী ফি পরিশোধের পর ডাক্তার রিপোর্ট পর্যালোচনা করবেন।</li>
                    </ol>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5">
                    <a href="https://wa.me/?text={{ urlencode('আসসালামু আলাইকুম। আমি '.$doctor->name_bn.'-এর WhatsApp পরামর্শ সেবা নিতে আগ্রহী।') }}"
                        target="_blank" rel="noopener"
                        class="font-bn flex-1 py-3 px-4 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-center font-bold text-[14px] flex items-center justify-center gap-2">
                        <i class="ti ti-brand-whatsapp text-lg"></i>
                        <span>WhatsApp-এ বার্তা পাঠান</span>
                    </a>
                    <button type="button" @click="whatsappModal = false"
                        class="font-bn py-3 px-4 bg-mist hover:bg-slate-200 text-slate-700 rounded-xl font-semibold text-[14px]">
                        বন্ধ করুন
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- বিশেষত্ব চিপ --}}
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
