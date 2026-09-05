{{--
    docs/prototypes/homepage.html-এর .sbox/.sdrop হুবহু অনুসরণ করে, vanilla JS (ot()/cl()/setInterval)
    এর বদলে Alpine.js দিয়ে। ড্রপডাউন এখন GET /ajax/search/suggest থেকে real ডেটা দেখায় (resources/js/search-suggest.js) —
    prototype-এর ৪টি ডেমো ক্যাটাগরির মধ্যে "খরচ" বাদ (কোনো searchable কনটেন্ট টেবিল নেই), বদলে
    "রোগীর সহায়তা" (patient_cases) — যা আসল ব্যাকএন্ড ডেটার সাথে মেলে।
--}}
<div
    x-data="searchSuggest()"
    @click.outside="open = false"
    class="relative max-w-[560px]"
>
    <i class="ti ti-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 text-[21px] pointer-events-none"></i>

    <input
        type="text"
        x-ref="input"
        x-model="query"
        :placeholder="placeholders[placeholderIndex]"
        @focus="focused = true; open = query.length > 0"
        @blur="focused = false"
        @input="onInput()"
        @keydown.enter="goToResults()"
        class="font-bn w-full py-[19px] pl-14 pr-14 border-2 border-line rounded-2xl text-[16.5px] text-ink bg-white shadow-[0_4px_20px_rgba(20,23,25,0.05)] focus:outline-none focus:border-slate-900 focus:shadow-[0_6px_28px_rgba(20,23,25,0.1)]"
    >

    <i
        x-show="query.length > 0"
        x-cloak
        @click="clear()"
        class="ti ti-x absolute right-[21px] top-1/2 -translate-y-1/2 text-[#8E979D] text-[19px] cursor-pointer"
    ></i>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute top-[calc(100%+10px)] left-0 w-full bg-white border border-line rounded-2xl overflow-hidden z-[60] shadow-[0_20px_52px_rgba(20,23,25,0.16)] text-left"
    >
        <div x-show="results.doctors.length > 0" class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">ডাক্তার</div>
            <div x-ref="doctorsList"></div>
        </div>

        <div x-show="results.hospitals.length > 0" class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">হাসপাতাল</div>
            <div x-ref="hospitalsList"></div>
        </div>

        <div x-show="results.guides.length > 0" class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">ক্যান্সার গাইড</div>
            <div x-ref="guidesList"></div>
        </div>

        <div x-show="results.patient_cases.length > 0" class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">রোগীর সহায়তা</div>
            <div x-ref="patient_casesList"></div>
        </div>

        <div x-show="query.trim().length >= 2 && totalResults === 0" class="px-5 py-6 text-center">
            <p class="font-bn text-[13.5px] text-slate-500">কোনো ফলাফল পাওয়া যায়নি।</p>
            <p class="font-bn text-[12.5px] text-[#8E979D] mt-1">হেল্পলাইনে কল করুন <b>০৯৬১১-৭৭৭৮৮৮</b> — আমরা সাহায্য করব।</p>
        </div>

        <div x-show="totalResults > 0" class="px-5 py-3">
            <button type="button" @click="goToResults()" class="font-bn text-[13px] font-semibold text-slate-900 hover:underline">
                "<span x-text="query"></span>"-এর সব ফলাফল দেখুন →
            </button>
        </div>
    </div>
</div>
