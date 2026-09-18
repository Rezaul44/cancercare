<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ডাক্তার পোর্টাল — CancerCare Bangladesh')</title>

    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Tabler Icons: jsDelivr & cdnjs fallback --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.29.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-mist text-ink antialiased">
    @auth
        <header class="bg-white border-b border-line">
            <div class="max-w-[1000px] mx-auto px-6 h-16 flex items-center justify-between">
                <a href="{{ route('doctor.dashboard') }}" class="font-serif text-lg font-medium">CCB <span class="font-bn text-slate-500 text-sm">ডাক্তার পোর্টাল</span></a>
                <nav class="font-bn flex items-center gap-5 text-[13.5px] text-slate-500">
                    <a href="{{ route('doctor.dashboard') }}" class="hover:text-ink">ড্যাশবোর্ড</a>
                    <a href="{{ route('doctor.second-opinions.index') }}" class="hover:text-ink">দ্বিতীয় মতামত</a>
                    <a href="{{ route('doctor.profile.edit') }}" class="hover:text-ink">প্রোফাইল</a>
                    <a href="{{ route('doctor.payouts.index') }}" class="hover:text-ink">পে-আউট</a>
                    <form method="POST" action="{{ route('doctor.logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-red-600">লগআউট</button>
                    </form>
                </nav>
            </div>
        </header>
    @endauth

    <main class="max-w-[1000px] mx-auto px-6 py-10">
        @if (session('status'))
            <div class="font-bn mb-6 rounded-[13px] border border-[#BFE5DC] bg-teal-50 px-5 py-3.5 text-[13.5px] text-teal-700">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
