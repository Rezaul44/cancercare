<x-filament-panels::page.simple>
    @push('styles')
    <style>
        /* ==========================================================
           HTML BODY / FULL-SCREEN CANCER ANIMATED BACKGROUND
           ========================================================== */
        html, body, .fi-body {
            background-color: #0b1120 !important;
        }

        .fi-simple-layout {
            position: relative;
            min-height: 100vh;
            background: radial-gradient(circle at 50% 15%, #1e293b 0%, #0f172a 50%, #020617 100%) !important;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* Fixed Background Canvas Layer for Full Body Animations */
        .cancer-body-animation-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        }

        /* Subtle Background Dot Grid */
        .cancer-grid-bg {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1.2px, transparent 1.2px);
            background-size: 30px 30px;
            opacity: 0.55;
        }

        /* Ambient Cellular Glowing Orbs across HTML Body */
        .cancer-glow-orb-1 {
            position: absolute;
            top: -120px;
            left: -100px;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(222, 1, 89, 0.35) 0%, rgba(222, 1, 89, 0.08) 45%, transparent 70%);
            filter: blur(55px);
            animation: body-pulse-glow-1 12s ease-in-out infinite alternate;
        }

        .cancer-glow-orb-2 {
            position: absolute;
            bottom: -140px;
            right: -100px;
            width: 580px;
            height: 580px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(18, 160, 133, 0.38) 0%, rgba(18, 160, 133, 0.08) 45%, transparent 70%);
            filter: blur(60px);
            animation: body-pulse-glow-2 14s ease-in-out infinite alternate;
        }

        .cancer-glow-orb-3 {
            position: absolute;
            top: 42%;
            right: 10%;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(234, 179, 8, 0.24) 0%, rgba(234, 179, 8, 0.04) 50%, transparent 70%);
            filter: blur(45px);
            animation: body-pulse-glow-3 16s ease-in-out infinite alternate;
        }

        .cancer-glow-orb-4 {
            position: absolute;
            bottom: 22%;
            left: 7%;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(147, 51, 234, 0.28) 0%, rgba(147, 51, 234, 0.04) 50%, transparent 70%);
            filter: blur(40px);
            animation: body-pulse-glow-1 15s ease-in-out infinite alternate-reverse;
        }

        @keyframes body-pulse-glow-1 {
            0% { transform: scale(1) translate(0, 0); opacity: 0.7; }
            50% { transform: scale(1.16) translate(35px, 40px); opacity: 1; }
            100% { transform: scale(0.92) translate(-25px, 20px); opacity: 0.75; }
        }

        @keyframes body-pulse-glow-2 {
            0% { transform: scale(1) translate(0, 0); opacity: 0.75; }
            50% { transform: scale(1.2) translate(-40px, -35px); opacity: 1; }
            100% { transform: scale(0.88) translate(25px, -15px); opacity: 0.7; }
        }

        @keyframes body-pulse-glow-3 {
            0% { transform: scale(0.9) translate(0, 0); opacity: 0.55; }
            50% { transform: scale(1.14) translate(-25px, 35px); opacity: 0.95; }
            100% { transform: scale(1.02) translate(30px, -20px); opacity: 0.6; }
        }

        /* Floating Cancer Awareness Ribbons across HTML Body Canvas */
        .cancer-ribbon {
            position: absolute;
            opacity: 0.85;
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.5));
            transition: opacity 0.3s ease;
        }

        .ribbon-pink {
            top: 9%;
            left: 6%;
            width: 82px;
            height: 120px;
            animation: float-ribbon-1 9s ease-in-out infinite;
        }

        .ribbon-teal {
            bottom: 10%;
            left: 9%;
            width: 74px;
            height: 108px;
            animation: float-ribbon-2 11s ease-in-out infinite 1s;
        }

        .ribbon-lavender {
            top: 12%;
            right: 7%;
            width: 88px;
            height: 128px;
            animation: float-ribbon-3 10s ease-in-out infinite 2s;
        }

        .ribbon-gold {
            bottom: 12%;
            right: 9%;
            width: 70px;
            height: 102px;
            animation: float-ribbon-4 12s ease-in-out infinite 0.5s;
        }

        .ribbon-mini-pink {
            top: 68%;
            right: 24%;
            width: 46px;
            height: 68px;
            opacity: 0.5;
            animation: float-ribbon-1 14s ease-in-out infinite 3s;
        }

        .ribbon-mini-teal {
            top: 28%;
            left: 20%;
            width: 50px;
            height: 74px;
            opacity: 0.55;
            animation: float-ribbon-2 13s ease-in-out infinite 2.5s;
        }

        @keyframes float-ribbon-1 {
            0% { transform: translateY(0px) rotate(-6deg) scale(1); }
            50% { transform: translateY(-26px) rotate(4deg) scale(1.05); }
            100% { transform: translateY(0px) rotate(-6deg) scale(1); }
        }

        @keyframes float-ribbon-2 {
            0% { transform: translateY(0px) rotate(8deg) scale(1); }
            50% { transform: translateY(-30px) rotate(-3deg) scale(1.06); }
            100% { transform: translateY(0px) rotate(8deg) scale(1); }
        }

        @keyframes float-ribbon-3 {
            0% { transform: translateY(0px) rotate(5deg) scale(1); }
            50% { transform: translateY(-24px) rotate(-7deg) scale(1.04); }
            100% { transform: translateY(0px) rotate(5deg) scale(1); }
        }

        @keyframes float-ribbon-4 {
            0% { transform: translateY(0px) rotate(-10deg) scale(1); }
            50% { transform: translateY(-28px) rotate(2deg) scale(1.07); }
            100% { transform: translateY(0px) rotate(-10deg) scale(1); }
        }

        /* Floating Cellular Particles in Background */
        .cell-particle {
            position: absolute;
            border-radius: 50%;
        }

        .cell-1 {
            width: 10px;
            height: 10px;
            top: 22%;
            left: 15%;
            background: rgba(222, 1, 89, 0.75);
            box-shadow: 0 0 16px rgba(222, 1, 89, 0.95);
            animation: float-particle 8s ease-in-out infinite alternate;
        }

        .cell-2 {
            width: 14px;
            height: 14px;
            top: 65%;
            left: 18%;
            background: rgba(18, 160, 133, 0.75);
            box-shadow: 0 0 18px rgba(18, 160, 133, 0.95);
            animation: float-particle 10s ease-in-out infinite alternate 1.5s;
        }

        .cell-3 {
            width: 11px;
            height: 11px;
            top: 30%;
            right: 18%;
            background: rgba(234, 179, 8, 0.7);
            box-shadow: 0 0 16px rgba(234, 179, 8, 0.85);
            animation: float-particle 9s ease-in-out infinite alternate 0.8s;
        }

        .cell-4 {
            width: 8px;
            height: 8px;
            bottom: 26%;
            right: 16%;
            background: rgba(168, 85, 247, 0.75);
            box-shadow: 0 0 14px rgba(168, 85, 247, 0.9);
            animation: float-particle 7s ease-in-out infinite alternate 2s;
        }

        .cell-ring-1 {
            width: 48px;
            height: 48px;
            top: 40%;
            left: 5%;
            border: 1.5px solid rgba(222, 1, 89, 0.35);
            border-radius: 50%;
            animation: pulse-ring 6s ease-in-out infinite;
        }

        .cell-ring-2 {
            width: 60px;
            height: 60px;
            bottom: 30%;
            right: 4%;
            border: 1.5px solid rgba(18, 160, 133, 0.35);
            border-radius: 50%;
            animation: pulse-ring 7s ease-in-out infinite 1s;
        }

        @keyframes float-particle {
            0% { transform: translateY(0) translateX(0); opacity: 0.4; }
            50% { transform: translateY(-34px) translateX(18px); opacity: 0.95; }
            100% { transform: translateY(-64px) translateX(-12px); opacity: 0.4; }
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9) rotate(0deg); opacity: 0.3; }
            50% { transform: scale(1.15) rotate(180deg); opacity: 0.8; }
            100% { transform: scale(0.9) rotate(360deg); opacity: 0.3; }
        }

        /* ==========================================================
           MODERN, COMPACT & RESPONSIVE LOGIN BOX
           ========================================================== */
        .fi-simple-main-ctn {
            padding: 12px !important;
        }

        .fi-simple-main {
            position: relative;
            z-index: 20;
            /* 100% Solid, Crisp, Modern White Background */
            background-color: #ffffff !important;
            border-radius: 20px !important;
            padding: 32px 34px !important;
            max-width: 410px !important;
            margin-left: auto !important;
            margin-right: auto !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
            border: 1px solid #e2e8f0 !important;
            /* Sleek modern elevated drop shadow */
            box-shadow:
                0 20px 40px -15px rgba(15, 23, 42, 0.4),
                0 0 30px -6px rgba(222, 1, 89, 0.16),
                0 0 20px -6px rgba(18, 160, 133, 0.14) !important;
            animation: none !important;
            transform: none !important;
        }

        .dark .fi-simple-main {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            box-shadow:
                0 20px 40px -15px rgba(0, 0, 0, 0.65),
                0 0 30px -6px rgba(222, 1, 89, 0.22),
                0 0 20px -6px rgba(18, 160, 133, 0.18) !important;
        }

        /* Modern Brand Typography */
        .brand-title {
            font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
            letter-spacing: -0.02em;
            color: #0f172a;
            font-weight: 700;
        }
        .dark .brand-title {
            color: #f8fafc;
        }

        /* Modern Primary Sign-In Button */
        .fi-btn-primary {
            background: linear-gradient(135deg, #DE0159 0%, #B80148 100%) !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(222, 1, 89, 0.35) !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding-top: 9px !important;
            padding-bottom: 9px !important;
        }

        .fi-btn-primary:hover {
            box-shadow: 0 6px 18px rgba(222, 1, 89, 0.45) !important;
            background: linear-gradient(135deg, #F00B66 0%, #C90150 100%) !important;
        }

        /* Hide default simple header */
        .fi-simple-header {
            display: none !important;
        }

        @media (max-width: 640px) {
            .fi-simple-main {
                padding: 24px 20px !important;
                margin: 12px !important;
                border-radius: 16px !important;
                max-width: 100% !important;
            }
            .ribbon-pink, .ribbon-lavender {
                display: none;
            }
        }
    </style>
    @endpush

    {{-- HTML Body / Background Full-Screen Animated Canvas --}}
    <div class="cancer-body-animation-canvas">
        <div class="cancer-grid-bg"></div>
        <div class="cancer-glow-orb-1"></div>
        <div class="cancer-glow-orb-2"></div>
        <div class="cancer-glow-orb-3"></div>
        <div class="cancer-glow-orb-4"></div>

        {{-- Floating Cancer Awareness Ribbon 1: Pink (Breast Cancer Awareness) --}}
        <div class="cancer-ribbon ribbon-pink" title="Breast Cancer Awareness Ribbon">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <defs>
                    <linearGradient id="pinkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FF5C98" />
                        <stop offset="50%" stop-color="#DE0159" />
                        <stop offset="100%" stop-color="#9C003C" />
                    </linearGradient>
                    <filter id="ribbonShadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#DE0159" flood-opacity="0.35" />
                    </filter>
                </defs>
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="url(#pinkGrad)" stroke-width="15" stroke-linecap="round" filter="url(#ribbonShadow)"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="url(#pinkGrad)" stroke-width="15" stroke-linecap="round" filter="url(#ribbonShadow)"/>
                <ellipse cx="50" cy="22" rx="16" ry="12" fill="none" stroke="url(#pinkGrad)" stroke-width="14" />
            </svg>
        </div>

        {{-- Floating Cancer Awareness Ribbon 2: Teal (Ovarian / Cervical / GYN Awareness) --}}
        <div class="cancer-ribbon ribbon-teal" title="Ovarian / Cervical Cancer Awareness Ribbon">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <defs>
                    <linearGradient id="tealGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#2ED8B6" />
                        <stop offset="50%" stop-color="#12A085" />
                        <stop offset="100%" stop-color="#085B4B" />
                    </linearGradient>
                </defs>
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="url(#tealGrad)" stroke-width="15" stroke-linecap="round"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="url(#tealGrad)" stroke-width="15" stroke-linecap="round"/>
                <ellipse cx="50" cy="22" rx="16" ry="12" fill="none" stroke="url(#tealGrad)" stroke-width="14" />
            </svg>
        </div>

        {{-- Floating Cancer Awareness Ribbon 3: Lavender (All Cancer Awareness) --}}
        <div class="cancer-ribbon ribbon-lavender" title="All Cancers Awareness Ribbon">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <defs>
                    <linearGradient id="lavGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#C084FC" />
                        <stop offset="50%" stop-color="#9333EA" />
                        <stop offset="100%" stop-color="#581C87" />
                    </linearGradient>
                </defs>
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="url(#lavGrad)" stroke-width="15" stroke-linecap="round"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="url(#lavGrad)" stroke-width="15" stroke-linecap="round"/>
                <ellipse cx="50" cy="22" rx="16" ry="12" fill="none" stroke="url(#lavGrad)" stroke-width="14" />
            </svg>
        </div>

        {{-- Floating Cancer Awareness Ribbon 4: Gold (Childhood Cancer Awareness) --}}
        <div class="cancer-ribbon ribbon-gold" title="Childhood Cancer Awareness Ribbon">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <defs>
                    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FDE047" />
                        <stop offset="50%" stop-color="#EAB308" />
                        <stop offset="100%" stop-color="#A16207" />
                    </linearGradient>
                </defs>
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="url(#goldGrad)" stroke-width="15" stroke-linecap="round"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="url(#goldGrad)" stroke-width="15" stroke-linecap="round"/>
                <ellipse cx="50" cy="22" rx="16" ry="12" fill="none" stroke="url(#goldGrad)" stroke-width="14" />
            </svg>
        </div>

        {{-- Floating Mini Ribbons --}}
        <div class="cancer-ribbon ribbon-mini-pink">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="#FF5C98" stroke-width="15" stroke-linecap="round"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="#FF5C98" stroke-width="15" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="cancer-ribbon ribbon-mini-teal">
            <svg viewBox="0 0 100 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <path d="M 50 10 C 25 10, 10 35, 15 65 C 20 90, 45 110, 20 135 C 18 137, 28 138, 38 120 C 48 102, 50 82, 50 82" stroke="#2ED8B6" stroke-width="15" stroke-linecap="round"/>
                <path d="M 50 10 C 75 10, 90 35, 85 65 C 80 90, 55 110, 80 135 C 82 137, 72 138, 62 120 C 52 102, 50 82, 50 82" stroke="#2ED8B6" stroke-width="15" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- Cellular Vitality Particles --}}
        <div class="cell-particle cell-1"></div>
        <div class="cell-particle cell-2"></div>
        <div class="cell-particle cell-3"></div>
        <div class="cell-particle cell-4"></div>
        <div class="cell-particle cell-ring-1"></div>
        <div class="cell-particle cell-ring-2"></div>
    </div>

    {{-- Modern, Compact, Clean Login Card --}}
    <div class="w-full relative">
        {{-- Brand Header --}}
        <div class="text-center mb-5">
            {{-- Brand Logo --}}
            <div class="inline-flex items-center justify-center mb-2.5">
                <a href="{{ url('/') }}" class="inline-block transition hover:opacity-90">
                    <img src="{{ asset('images/logo.png') }}" alt="Cancer Care" class="h-9 w-auto mx-auto">
                </a>
            </div>

            {{-- Brand Name --}}
            <h1 class="brand-title text-2xl leading-tight">
                Cancer Care
            </h1>

            {{-- Bengali Subtitle --}}
            <p class="text-[12.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-bn leading-normal">
                চিকিৎসা ও তথ্য ব্যবস্থাপনা প্যানেল
            </p>

            {{-- Fresh Status Pills --}}
            <div class="flex items-center justify-center gap-1.5 mt-2">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-pink-50 text-[#DE0159] dark:bg-pink-950/40 dark:text-pink-300 border border-pink-200/60 dark:border-pink-900/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#DE0159]"></span>
                    সচেতনতা ও সেবা
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-teal-50 text-[#0B6E5C] dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200/60 dark:border-teal-900/50">
                    সুরক্ষিত প্যানেল
                </span>
            </div>
        </div>

        {{-- Authentication Form --}}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        <x-filament-panels::form id="form" wire:submit="authenticate" class="space-y-3.5">
            {{ $this->form }}

            <div class="pt-1.5">
                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </div>
        </x-filament-panels::form>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

        {{-- Back to Main Portal Link --}}
        <div class="text-center mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition font-bn font-medium">
                <i class="ti ti-arrow-left text-sm"></i>
                <span>মূল ওয়েবসাইটে ফিরে যান</span>
            </a>
        </div>
    </div>
</x-filament-panels::page.simple>
