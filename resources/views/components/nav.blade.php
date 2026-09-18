{{-- docs/prototypes/homepage.html-এর .topbar ও .nav হুবহু অনুসরণ করে --}}
<div class="bg-slate-900 text-white text-[12.5px] py-2">
    <div class="max-w-[1240px] mx-auto px-10 flex items-center gap-[22px]">
        <span class="font-bn flex items-center gap-1.5 text-white/72">
            <i class="ti ti-phone text-teal-500 text-sm"></i>
            হেল্পলাইন ০৯৬১১-৭৭৭৮৮৮
        </span>
        <span class="font-bn flex items-center gap-1.5 text-white/72">
            <i class="ti ti-clock text-teal-500 text-sm"></i>
            সকাল ৯টা – রাত ৯টা
        </span>
        <div class="ml-auto flex items-center gap-4">
            <span class="font-bn flex items-center gap-1.5 text-white/72">
                <i class="ti ti-shield-check text-teal-500 text-sm"></i>
                কেউ টাকা দিয়ে তালিকায় ওঠে না
            </span>
            <div class="flex border border-white/20 rounded-full overflow-hidden">
                <span class="px-2.5 py-0.5 text-[11.5px] bg-white text-slate-900 font-semibold cursor-pointer">EN</span>
                <span class="font-bn px-2.5 py-0.5 text-[11.5px] text-white/60 cursor-pointer">বাং</span>
            </div>
        </div>
    </div>
</div>

<nav class="bg-white border-b border-line sticky top-0 z-40">
    <div class="max-w-[1240px] mx-auto px-10 h-[76px] flex items-center gap-[34px]">
        <a href="{{ url('/') }}" class="block shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="CancerCare Bangladesh লোগো" class="h-[38px] w-auto">
        </a>

        <div class="flex gap-0.5">
            <a href="{{ route('doctors.index') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('doctors.*') && !request()->routeIs('doctors.apply') ? 'bg-mist text-ink font-semibold' : '' }}">ডাক্তার</a>
            <a href="{{ route('hospitals.index') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('hospitals.*') ? 'bg-mist text-ink font-semibold' : '' }}">হাসপাতাল</a>
            <a href="{{ route('cost-estimator.index') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('cost-estimator.*') ? 'bg-mist text-ink font-semibold' : '' }}">খরচের হিসাব</a>
            <a href="{{ route('patients.index') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('patients.*') ? 'bg-mist text-ink font-semibold' : '' }}">রোগীদের সহায়তা</a>
            <a href="{{ route('guides.index') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('guides.*') ? 'bg-mist text-ink font-semibold' : '' }}">ক্যান্সার গাইড</a>
            <a href="{{ route('doctors.apply') }}" class="font-bn text-sm text-slate-500 px-3.5 py-2.5 rounded-lg cursor-pointer font-medium hover:bg-mist hover:text-ink {{ request()->routeIs('doctors.apply') ? 'bg-mist text-ink font-semibold' : '' }}">ডাক্তারদের জন্য</a>
        </div>

        <div class="ml-auto flex gap-3 items-center">
            <a href="{{ url('/admin') }}" class="font-bn text-sm px-5 py-2.5 rounded-[9px] font-medium bg-white text-ink border border-slate-300 hover:border-teal-500 hover:bg-mist inline-flex items-center transition">সাইন ইন</a>
            <a href="{{ route('doctors.index') }}" class="font-bn text-sm px-5 py-2.5 rounded-[9px] font-semibold bg-teal-700 text-white hover:bg-teal-800 shadow-sm border border-teal-600 inline-flex items-center transition">শুরু করুন</a>
        </div>
    </div>
</nav>
