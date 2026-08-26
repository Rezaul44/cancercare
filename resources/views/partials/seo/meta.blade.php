@php
    $pageTitle = trim($__env->yieldContent('title', 'CancerCare Bangladesh — ক্যান্সার চিকিৎসায় বিশ্বস্ত পথপ্রদর্শক'));
    $pageDescription = $seoDescription ?? 'বাংলাদেশে ক্যান্সার চিকিৎসার সঠিক তথ্য, বিশেষজ্ঞ অনকোলজিস্ট, হাসপাতাল ও খরচের নির্ভরযোগ্য তথ্য — সব এক জায়গায়, নিজের ভাষায়।';
    $pageCanonical = $canonicalUrl ?? url()->current();
    $pageType = $seoType ?? 'website';
    $pageImage = $seoImage ?? asset('images/logo.png');
    $siteName = 'CancerCare Bangladesh';
@endphp

{{-- Standard HTML Meta Tags --}}
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="{{ $seoKeywords ?? 'ক্যান্সার, অনকোলজিস্ট, ক্যান্সার চিকিৎসা বাংলাদেশ, স্তন ক্যান্সার, কেমোথেরাপি, ক্যান্সার ডাক্তার ঢাকা, ক্যান্সার হাসপাতাল' }}">
<link rel="canonical" href="{{ $pageCanonical }}">

{{-- Open Graph / Facebook --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $pageType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $pageCanonical }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:locale" content="bn_BD">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">
