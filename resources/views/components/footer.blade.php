{{-- docs/prototypes/homepage.html-এর .footer হুবহু অনুসরণ করে --}}
<footer class="bg-slate-900 text-white pt-[58px] pb-7">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="grid grid-cols-[1.5fr_1fr_1fr_1fr] gap-12">
            <div>
                <div class="mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="CancerCare Bangladesh লোগো" class="h-[34px] brightness-0 invert opacity-95">
                </div>
                <p class="font-bn text-[13.5px] text-white/55 leading-[1.72] max-w-[290px] mb-5">
                    বাংলাদেশে প্রতি বছর হাজারো পরিবার ক্যান্সারের খবর পায় — আর জানে না কোথায় যাবে। আমরা সেই পথটা দেখানোর জন্য আছি।
                </p>
                <div class="flex gap-2.5">
                    <div class="w-9 h-9 rounded-[10px] border border-white/[0.18] flex items-center justify-center cursor-pointer text-white/70">
                        <i class="ti ti-brand-facebook text-lg"></i>
                    </div>
                    <div class="w-9 h-9 rounded-[10px] border border-white/[0.18] flex items-center justify-center cursor-pointer text-white/70">
                        <i class="ti ti-brand-youtube text-lg"></i>
                    </div>
                    <div class="w-9 h-9 rounded-[10px] border border-white/[0.18] flex items-center justify-center cursor-pointer text-white/70">
                        <i class="ti ti-brand-whatsapp text-lg"></i>
                    </div>
                </div>
            </div>

            <div>
                <div class="font-bn text-[11.5px] font-semibold text-white/40 uppercase tracking-[0.1em] mb-4">খুঁজুন</div>
                <div class="font-bn flex flex-col gap-[11px]">
                    <a href="{{ route('doctors.index') }}" class="text-[13.5px] text-white/70 hover:text-white">ডাক্তার</a>
                    <a href="{{ route('hospitals.index') }}" class="text-[13.5px] text-white/70 hover:text-white">হাসপাতাল</a>
                    <a href="{{ route('cost-estimator.index') }}" class="text-[13.5px] text-white/70 hover:text-white">খরচের হিসাব</a>
                    <a href="{{ route('doctors.index') }}" class="text-[13.5px] text-white/70 hover:text-white">দ্বিতীয় মতামত</a>
                </div>
            </div>

            <div>
                <div class="font-bn text-[11.5px] font-semibold text-white/40 uppercase tracking-[0.1em] mb-4">সহায়তা</div>
                <div class="font-bn flex flex-col gap-[11px]">
                    <a href="{{ route('guides.index') }}" class="text-[13.5px] text-white/70 hover:text-white">ক্যান্সার গাইড</a>
                    <a href="{{ route('patients.index') }}" class="text-[13.5px] text-white/70 hover:text-white">রোগীদের সহায়তা</a>
                    <a href="{{ route('patients.apply') }}" class="text-[13.5px] text-white/70 hover:text-white">সহায়তার আবেদন</a>
                    <a href="{{ url('/') }}" class="text-[13.5px] text-white/70 hover:text-white">যোগাযোগ</a>
                </div>
            </div>

            <div>
                <div class="font-bn text-[11.5px] font-semibold text-white/40 uppercase tracking-[0.1em] mb-4">আমাদের সম্পর্কে</div>
                <div class="font-bn flex flex-col gap-[11px]">
                    <a href="{{ url('/') }}" class="text-[13.5px] text-white/70 hover:text-white">আমাদের লক্ষ্য</a>
                    <a href="{{ url('/') }}" class="text-[13.5px] text-white/70 hover:text-white">যাচাইকরণের পদ্ধতি</a>
                    <a href="{{ url('/') }}" class="text-[13.5px] text-white/70 hover:text-white">গোপনীয়তা নীতি</a>
                    <a href="{{ route('doctors.apply') }}" class="text-[13.5px] text-white/70 hover:text-white">ডাক্তারদের জন্য</a>
                </div>
            </div>
        </div>

        <div class="mt-11 pt-6 border-t border-white/[0.12] flex justify-between text-[12.5px] text-white/45">
            <span class="font-bn">© ২০২৬ CancerCare Bangladesh</span>
            <span class="font-bn">ঢাকায় তৈরি · Doctor Pro Media-র উদ্যোগ</span>
        </div>
    </div>
</footer>
