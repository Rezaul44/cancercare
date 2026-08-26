{{--
    docs/prototypes/homepage.html-এর .sbox/.sdrop হুবহু অনুসরণ করে, vanilla JS (ot()/cl()/setInterval)
    এর বদলে Alpine.js দিয়ে। ড্রপডাউনের ভেতরের ৪টি ক্যাটাগরি এখনো prototype-এর ডেমো ডেটা —
    পরের ফেজে /ajax/search/suggest দিয়ে ডাইনামিক হবে।
--}}
<div
    x-data="{
        query: '',
        focused: false,
        open: false,
        placeholders: [
            'যা জানতে চান লিখুন — ডাক্তার, হাসপাতাল, খরচ',
            'স্তন ক্যান্সারের ভালো ডাক্তার কে',
            'NICRH-এ কী কী চিকিৎসা হয়',
            'কেমোথেরাপিতে আসলে কত টাকা লাগে',
            'সরকারি হাসপাতালে রেডিওথেরাপি কোথায়',
        ],
        placeholderIndex: 0,
        init() {
            setInterval(() => {
                if (!this.focused && this.query === '') {
                    this.placeholderIndex = (this.placeholderIndex + 1) % this.placeholders.length;
                }
            }, 3400);
        },
        clear() {
            this.query = '';
            this.open = false;
            $refs.input.focus();
        },
    }"
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
        @input="open = query.length > 0"
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
        <div class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">ডাক্তার</div>
            <div class="flex items-center gap-[13px] px-5 py-[11px] cursor-pointer hover:bg-mist">
                <div class="w-[34px] h-[34px] rounded-[10px] bg-blue-100 flex items-center justify-center shrink-0">
                    <i class="ti ti-stethoscope text-blue-700 text-lg"></i>
                </div>
                <div>
                    <div class="font-bn text-[14.5px] font-semibold">স্তন ক্যান্সার বিশেষজ্ঞ</div>
                    <div class="font-bn text-[12.5px] text-[#8E979D] mt-0.5">৪৮ জন — প্রত্যেকের নিবন্ধন যাচাই করা</div>
                </div>
                <i class="ti ti-arrow-right ml-auto text-[#8E979D] text-base"></i>
            </div>
        </div>

        <div class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">হাসপাতাল</div>
            <div class="flex items-center gap-[13px] px-5 py-[11px] cursor-pointer hover:bg-mist">
                <div class="w-[34px] h-[34px] rounded-[10px] bg-teal-100 flex items-center justify-center shrink-0">
                    <i class="ti ti-building-hospital text-teal-700 text-lg"></i>
                </div>
                <div>
                    <div class="font-bn text-[14.5px] font-semibold">স্তন ক্যান্সার চিকিৎসা কেন্দ্র</div>
                    <div class="font-bn text-[12.5px] text-[#8E979D] mt-0.5">১২টি কেন্দ্র — কোথায় কী আছে দেখুন</div>
                </div>
                <i class="ti ti-arrow-right ml-auto text-[#8E979D] text-base"></i>
            </div>
        </div>

        <div class="py-[7px] border-b border-[#F1F1EE]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">ক্যান্সার গাইড</div>
            <div class="flex items-center gap-[13px] px-5 py-[11px] cursor-pointer hover:bg-mist">
                <div class="w-[34px] h-[34px] rounded-[10px] bg-purple-100 flex items-center justify-center shrink-0">
                    <i class="ti ti-book-2 text-purple-700 text-lg"></i>
                </div>
                <div>
                    <div class="font-bn text-[14.5px] font-semibold">স্তন ক্যান্সার — লক্ষণ ও চিকিৎসা</div>
                    <div class="font-bn text-[12.5px] text-[#8E979D] mt-0.5">সহজ বাংলায়, WHO নির্দেশনা অনুযায়ী</div>
                </div>
                <i class="ti ti-arrow-right ml-auto text-[#8E979D] text-base"></i>
            </div>
        </div>

        <div class="py-[7px]">
            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.09em] px-5 pt-2.5 pb-1.5">খরচ</div>
            <div class="flex items-center gap-[13px] px-5 py-[11px] cursor-pointer hover:bg-mist">
                <div class="w-[34px] h-[34px] rounded-[10px] bg-gold-soft flex items-center justify-center shrink-0">
                    <i class="ti ti-calculator text-gold text-lg"></i>
                </div>
                <div>
                    <div class="font-bn text-[14.5px] font-semibold">স্তন ক্যান্সারের চিকিৎসার খরচ</div>
                    <div class="font-bn text-[12.5px] text-[#8E979D] mt-0.5">সরকারিতে কত কম, পাশাপাশি দেখুন</div>
                </div>
                <i class="ti ti-arrow-right ml-auto text-[#8E979D] text-base"></i>
            </div>
        </div>
    </div>
</div>
