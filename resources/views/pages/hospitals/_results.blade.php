<div x-ref="results">
    {{-- Verification Method Notice --}}
    <div class="bg-gold-soft border border-gold-line rounded-[14px] p-4 md:px-5 flex gap-3 mb-5 shadow-sm">
        <i class="ti ti-info-circle text-gold text-xl shrink-0 mt-0.5"></i>
        <div>
            <div class="text-[14px] font-semibold text-[#7A5410] mb-1 font-bn">তথ্য যাচাইয়ের পদ্ধতি</div>
            <div class="text-[13.5px] text-[#8A6218] leading-relaxed font-bn">
                যন্ত্রপাতি ও সেবার তথ্য হাসপাতাল থেকে সরাসরি নেওয়া এবং প্রতি তিন মাসে মাঠপর্যায়ে যাচাই করা হয়। তবু যাওয়ার আগে ফোন করে নিশ্চিত হয়ে নিন — যন্ত্র নষ্ট থাকা বা সেবা সাময়িক বন্ধ থাকা অস্বাভাবিক নয়।
            </div>
        </div>
    </div>

    {{-- Results Top Bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <div class="text-[14.5px] text-slate-600 font-bn">
            {!! $resultHeading !!}
        </div>

        <div class="flex gap-1.5 flex-wrap">
            @php
                $sortOptions = [
                    'relevance' => 'প্রাসঙ্গিকতা',
                    'cost_low' => 'কম খরচ',
                    'wait_low' => 'কম অপেক্ষা',
                    'name' => 'নাম অনুসারে',
                ];
            @endphp
            @foreach ($sortOptions as $sortKey => $sortLabel)
                <button type="button"
                        @click="setSort('{{ $sortKey }}')"
                        class="text-[12.5px] px-3.5 py-1.5 rounded-full border transition font-bn cursor-pointer {{ $sort === $sortKey ? 'bg-slate-900 border-slate-900 text-white font-medium shadow-sm' : 'bg-white border-line text-slate-600 hover:border-slate-300 hover:text-ink' }}">
                    {{ $sortLabel }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Hospitals List --}}
    @if ($hospitals->isNotEmpty())
        <div class="flex flex-col gap-3.5">
            @foreach ($hospitals as $hospital)
                @include('pages.hospitals._card', ['hospital' => $hospital])
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $hospitals->links() }}
        </div>
    @else
        <div class="bg-white border border-line rounded-2xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-mist text-slate-400 mx-auto flex items-center justify-center mb-4">
                <i class="ti ti-building-hospital text-3xl"></i>
            </div>
            <h3 class="font-serif text-xl font-semibold text-ink mb-2">কোনো হাসপাতাল পাওয়া যায়নি</h3>
            <p class="text-sm text-slate-500 font-bn max-w-md mx-auto mb-6">
                আপনার নির্বাচিত ফিল্টারের সাথে মিলে এমন কোনো হাসপাতাল এই মুহূর্তে নেই। ফিল্টার শিথিল করে পুনরায় চেষ্টা করুন।
            </p>
            <button type="button"
                    @click="clearFilters()"
                    class="font-bn text-sm px-5 py-2.5 rounded-lg font-medium bg-slate-900 text-white hover:bg-slate-700 transition">
                সব ফিল্টার মুছুন
            </button>
        </div>
    @endif
</div>
