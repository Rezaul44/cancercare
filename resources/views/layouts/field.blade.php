<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'মাঠকর্মী পোর্টাল — CancerCare Bangladesh')</title>

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
            <div class="max-w-[600px] mx-auto px-5 h-14 flex items-center justify-between">
                <span class="font-serif text-base font-medium">CCB <span class="font-bn text-slate-500 text-sm">মাঠকর্মী</span></span>
                <form method="POST" action="{{ route('field.logout') }}">
                    @csrf
                    <button type="submit" class="font-bn text-[13px] text-slate-500 hover:text-red-600">লগআউট</button>
                </form>
            </div>
        </header>
    @endauth

    <main class="max-w-[600px] mx-auto px-5 py-6">
        @yield('content')
    </main>
</body>
</html>
