{{--
    হোমপেজের অনকোলজিস্ট অনুসন্ধান বক্স — resources/js/search-suggest.js থেকে Alpine.js ড্রপডাউন চালায়।
    GET /ajax/search/suggest?type=doctors&q=... কল করে ডাক্তারদের রিয়েল-টাইম তালিকা দেখায়।
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
        @focus="focused = true; open = query.trim().length > 0"
        @blur="focused = false"
        @input="onInput()"
        @keydown.enter="goToResults()"
        class="font-bn w-full py-[19px] pl-14 pr-14 border-2 border-line rounded-2xl text-[16.5px] text-ink bg-white shadow-[0_4px_20px_rgba(20,23,25,0.05)] focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 focus:shadow-[0_6px_28px_rgba(11,110,92,0.15)] transition"
    >

    <i
        x-show="query.length > 0"
        x-cloak
        @click="clear()"
        class="ti ti-x absolute right-[21px] top-1/2 -translate-y-1/2 text-[#8E979D] text-[19px] cursor-pointer hover:text-ink transition"
    ></i>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute top-[calc(100%+10px)] left-0 w-full bg-white border border-line rounded-2xl overflow-hidden z-[60] shadow-[0_20px_52px_rgba(20,23,25,0.16)] text-left max-h-[440px] flex flex-col"
    >
        {{-- Results List Header --}}
        <div x-show="doctors.length > 0" class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <div class="font-bn text-[11px] font-semibold text-slate-400 uppercase tracking-[0.08em] px-5 pt-3 pb-2 flex items-center justify-between bg-slate-50/70">
                <span>অনকোলজিস্ট ডাক্তারগণ</span>
                <span class="text-xs text-pink-600 font-medium" x-text="doctors.length + ' জন পাওয়া গেছে'"></span>
            </div>
            <div x-ref="doctorsList" class="divide-y divide-slate-100"></div>
        </div>

        {{-- No Results --}}
        <div x-show="query.trim().length >= 1 && !loading && doctors.length === 0" class="px-5 py-7 text-center">
            <div class="w-11 h-11 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2.5">
                <i class="ti ti-user-x text-2xl"></i>
            </div>
            <p class="font-bn text-[14.5px] font-semibold text-slate-700">কোনো ডাক্তার পাওয়া যায়নি</p>
            <p class="font-bn text-[12.5px] text-slate-400 mt-1">হেল্পলাইনে কল করুন <b class="text-ink font-semibold">০৯৬১১-৭৭৭৮৮৮</b> — আমরা সরাসরি ডাক্তারের সন্ধান দেব।</p>
        </div>

        {{-- Footer --}}
        <div x-show="doctors.length > 0" class="px-5 py-3 bg-slate-50 border-t border-line flex items-center justify-between shrink-0">
            <button type="button" @click="goToResults()" class="font-bn text-[13.5px] font-semibold text-pink-600 hover:text-pink-700 hover:underline flex items-center gap-1.5">
                <span>"<span x-text="query"></span>"-এর সব ডাক্তার দেখুন</span>
                <i class="ti ti-arrow-right text-xs"></i>
            </button>
            <span class="font-bn text-[11.5px] text-slate-400">ক্লিক করে প্রোফাইলে যান</span>
        </div>
    </div>
</div>
