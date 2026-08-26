# CancerCare Bangladesh — System Architecture ও Backend Documentation

**Version:** 2.0 · আগস্ট ২০২৬
**Stack:** Laravel 11 + Blade · Alpine.js · Tailwind CSS · MySQL 8 · Redis

> **v1.0 থেকে পরিবর্তন:** React SPA বাদ, Laravel Blade গৃহীত। কারণ ধারা ১-এ।

---

## ১. স্ট্যাক সিদ্ধান্ত ও যুক্তি

### কেন Blade, React নয়

| বিষয় | Blade | React SPA |
|---|---|---|
| SEO | ডিফল্টেই server-rendered | SSR সেটআপ লাগে, জটিল |
| গাইড পাতা Google-এ | প্রথম দিন থেকে | Next.js ছাড়া সমস্যা |
| ডেভেলপার (ঢাকায়) | সহজলভ্য, কম খরচ | কম, বেশি খরচ |
| Prototype পুনঃব্যবহার | প্রায় সরাসরি বসে যায় | পুরো আবার লিখতে হয় |
| কোডবেস | এক প্রজেক্ট | দুই প্রজেক্ট (API + SPA) |
| আনুমানিক সময় | ~১৯ সপ্তাহ | ~২৬ সপ্তাহ |

**CCB-র জন্য Blade-ই সঠিক** — এটি মূলত কনটেন্ট ও তথ্যভিত্তিক প্ল্যাটফর্ম, রিয়েল-টাইম অ্যাপ নয়।

### চূড়ান্ত স্ট্যাক

| স্তর | প্রযুক্তি | নোট |
|---|---|---|
| Framework | Laravel 11 | |
| View | Blade + component | `<x-doctor-card>` ইত্যাদি |
| Interactivity | **Alpine.js** (১৫ KB) | dropdown, tab, accordion, filter |
| ভারী লজিক | Vanilla JS module | cost estimator, match engine |
| Server call | AJAX (fetch) | শুধু যেখানে দরকার |
| CSS | Tailwind CSS | prototype-এর ডিজাইন টোকেন config-এ |
| Admin | **Filament 3** (সুপারিশ) | নিচে আলোচনা |
| DB | MySQL 8 | |
| Cache/Queue | Redis + Horizon | |
| Storage | S3 / DO Spaces | public + private bucket |
| Search | MySQL FULLTEXT (ngram) | পরে Meilisearch |

### Alpine.js কেন, খালি vanilla JS নয়

Prototype-এ যেসব `onclick` আর `classList.toggle` আছে, Alpine-এ সেগুলো অনেক পরিষ্কার:

```html
<!-- Vanilla (prototype-এ যা আছে) -->
<div class="term" onclick="tg(this)">...</div>

<!-- Alpine -->
<div x-data="{open:false}" @click="open=!open" :class="open && 'open'">...</div>
```

Alpine ১৫ KB, build step লাগে না, CDN থেকেই চলে। **শেখার সময় প্রায় শূন্য** — Blade ডেভেলপার এক দিনেই ধরে ফেলবেন।

তবে **cost estimator ও match engine** আলাদা JS module-এ রাখা ভালো, কারণ ওগুলোতে গণিতের লজিক আছে:

```
resources/js/
├── cost-estimator.js      ← prototype থেকে প্রায় সরাসরি
├── doctor-match.js        ← প্রায় সরাসরি
└── report-decoder.js      ← ফিল্টার লজিক
```

### অ্যাডমিন প্যানেল — তিনটি বিকল্প

| বিকল্প | সুবিধা | অসুবিধা |
|---|---|---|
| Blade + Alpine | এক ধরনের কোড | ফর্ম বেশি হলে কোড বাড়ে |
| Livewire 3 | CRUD দ্রুত | নতুন জিনিস শিখতে হবে |
| **Filament 3** | পুরো অ্যাডমিন প্রায় রেডি | কাস্টমাইজ কঠিন হতে পারে |

**সুপারিশ: Filament 3।** CCB-র অ্যাডমিনে ২০+ টেবিলের CRUD, রোল-পারমিশন, ফাইল আপলোড, ফিল্টার — সবই বিল্ট-ইন। অ্যাডমিনের কাজ ৬ সপ্তাহ থেকে ২ সপ্তাহে নামবে।

শুধু মাঠকর্মীর রেটিং ফর্ম আলাদা করে মোবাইলের জন্য Blade+Alpine-এ বানাতে হবে।

---

## ২. রুট কাঠামো

### পাবলিক রুট (web.php) — সব server-rendered

```php
Route::get('/',                             HomeController::class)->name('home');

// ডাক্তার
Route::get('/doctors',                      [DoctorController::class,'index']);
Route::get('/doctors/{doctor:slug}',        [DoctorController::class,'show']);

// হাসপাতাল
Route::get('/hospitals',                    [HospitalController::class,'index']);
Route::get('/hospitals/{hospital:slug}',    [HospitalController::class,'show']);

// ক্যান্সার গাইড — SEO-র প্রধান পাতা
Route::get('/guide',                        [GuideController::class,'index']);
Route::get('/guide/{cancerType:slug}',      [GuideController::class,'show']);

// টুল
Route::get('/cost-estimator',               [CostController::class,'index']);
Route::get('/start',                        [IntakeController::class,'index']);

// রোগীর সহায়তা
Route::get('/patients',                     [PatientCaseController::class,'index']);
Route::get('/patients/{case:case_code}',    [PatientCaseController::class,'show']);
Route::get('/apply-for-support',            [PatientCaseController::class,'howToApply']);

// দ্বিতীয় মতামত
Route::get('/second-opinion',               [SecondOpinionController::class,'create']);
Route::post('/second-opinion',              [SecondOpinionController::class,'store']);

// অন্যান্য
Route::get('/search',                       SearchController::class);
Route::get('/for-doctors',                  [DoctorApplicationController::class,'create']);
Route::post('/for-doctors',                 [DoctorApplicationController::class,'store']);
Route::get('/contact',                      ContactController::class);
Route::get('/{page:slug}',                  PageController::class); // about, privacy, terms, methodology
```

### AJAX রুট — শুধু যেখানে দরকার

```php
Route::prefix('ajax')->group(function () {
    Route::get('/search/suggest',           [SearchController::class,'suggest']);
    Route::post('/cost-estimate',           [CostController::class,'calculate']);   // স্টেটলেস
    Route::get('/doctors/{doctor}/match',   [DoctorController::class,'match']);
    Route::get('/doctors/filter',           [DoctorController::class,'filter']);
    Route::get('/hospitals/filter',         [HospitalController::class,'filter']);
    Route::get('/guide/{guide}/terms',      [GuideController::class,'terms']);
});
```

> **গুরুত্বপূর্ণ নীতি:** ফিল্টার AJAX দিয়ে হলেও URL-এ প্রতিফলিত হতে হবে (`/doctors?cancer=breast&district=dhaka`) — `history.pushState()` দিয়ে। নইলে শেয়ার ও Google index দুটোই ভাঙবে।

---

## ৩. Blade কম্পোনেন্ট কাঠামো

```
resources/views/
├── layouts/
│   ├── app.blade.php              পাবলিক লেআউট
│   └── admin.blade.php
├── components/
│   ├── nav.blade.php
│   ├── footer.blade.php
│   ├── search-box.blade.php       ← রোটেটিং placeholder + dropdown
│   ├── doctor-card.blade.php
│   ├── doctor-match.blade.php     ← ৩ dropdown ম্যাচ ইঞ্জিন
│   ├── hospital-card.blade.php
│   ├── capability-grid.blade.php  ← আছে/নেই/সীমিত
│   ├── patient-case-card.blade.php
│   ├── guide-term.blade.php       ← accordion
│   ├── stage-selector.blade.php
│   ├── ranking-notice.blade.php   ← "টাকা দিয়ে কেউ উপরে ওঠে না"
│   ├── verification-steps.blade.php
│   └── medical-disclaimer.blade.php
├── pages/
│   ├── home.blade.php
│   ├── doctors/{index,show}.blade.php
│   ├── hospitals/{index,show}.blade.php
│   ├── guide/{index,show}.blade.php
│   ├── patients/{index,show,apply}.blade.php
│   ├── cost-estimator.blade.php
│   ├── intake.blade.php
│   └── second-opinion.blade.php
└── partials/seo/{meta,schema}.blade.php
```

**নীতিগতভাবে গুরুত্বপূর্ণ কম্পোনেন্ট — বাদ দেওয়া যাবে না:**

```blade
{{-- ডাক্তার ও হাসপাতালের তালিকায় বাধ্যতামূলক --}}
<x-ranking-notice :basis="['বিশেষত্ব','দূরত্ব','রোগীর মতামত']" />

{{-- গাইড ও intake-এ বাধ্যতামূলক --}}
<x-medical-disclaimer />
```

---

## ৪. SEO — Blade-এর সবচেয়ে বড় সুবিধা

### প্রতিটি পাতায়

```blade
@section('seo')
    <title>{{ $guide->meta_title }} — CancerCare Bangladesh</title>
    <meta name="description" content="{{ $guide->meta_description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="bn_BD">
@endsection
```

### Structured Data (schema.org)

| পাতা | Schema type |
|---|---|
| ডাক্তার প্রোফাইল | `Physician` + `MedicalSpecialty` |
| হাসপাতাল | `Hospital` / `MedicalClinic` |
| গাইড | `MedicalWebPage` + `MedicalCondition` |
| গাইডের FAQ | `FAQPage` ← ফিচার্ড স্নিপেটের সম্ভাবনা |
| রিপোর্টের শব্দ | `DefinedTerm` |
| সারভাইভার স্টোরি | `Article` |

`MedicalWebPage`-এ `reviewedBy` ফিল্ডে অনকোলজিস্টের নাম দিলে Google মেডিকেল কনটেন্টকে বেশি গুরুত্ব দেয় (E-E-A-T সিগন্যাল)।

### রিপোর্টের শব্দের আলাদা anchor

```
/guide/breast-cancer#her2-positive
/guide/breast-cancer#grade
```
কেউ Google-এ "HER2 positive মানে কি" লিখলে সরাসরি ঐ অংশে নামবে।

> পরে বিবেচনা: টার্মগুলোর আলাদা পাতা (`/terms/her2-positive`) আরও ভালো র‍্যাঙ্ক করবে। শুরুতে anchor যথেষ্ট।

### বাধ্যতামূলক

- `sitemap.xml` — nightly job, সব guide/doctor/hospital পাতা
- `robots.txt` — admin ও private রুট ব্লক
- URL slug ইংরেজিতে (`/guide/breast-cancer`) — বাংলা URL-encode হয়ে কুৎসিত দেখায়
- সব ছবিতে বাংলা `alt`
- Core Web Vitals — Blade + Tailwind-এ এমনিতেই ভালো

---

## ৫. Tailwind Config — prototype-এর ডিজাইন টোকেন

```js
theme: {
  extend: {
    colors: {
      pink: { 50:'#FFF4F8', 100:'#FFE1EC', 200:'#FFC2D8',
              500:'#F20162', 600:'#DE0159', 700:'#C90154', 800:'#A80B4C' },
      slate:{ 300:'#A8B1B7', 500:'#5A656D', 700:'#333B41', 900:'#141719' },
      teal: { 50:'#F0FAF7', 100:'#DCF2ED', 500:'#12A085', 700:'#0B6E5C' },
      gold: { soft:'#FDF4E3', line:'#F0DFBC', DEFAULT:'#C98A1E' },
      mist:'#F7F7F5', line:'#E8E8E4', ink:'#141719',
    },
    fontFamily: {
      serif: ['Fraunces','Georgia','serif'],
      sans:  ['Inter','Hind Siliguri','sans-serif'],
      bn:    ['Hind Siliguri','Inter','sans-serif'],
    },
  }
}
```

Prototype-এর CSS variable সরাসরি ম্যাপ করা — ডিজাইন হুবহু থাকবে।

---

## ৬. ইউজার রোল ও পারমিশন

`spatie/laravel-permission` — Filament-এর সাথে সরাসরি কাজ করে।

| Role | মূল কাজ |
|---|---|
| `super_admin` | সবকিছু, রোল বণ্টন, settings, র‍্যাঙ্কিং ওয়েট |
| `verification_officer` | ডাক্তার ও রোগীর কেস যাচাই ও অনুমোদন |
| `content_editor` | গাইড, পেজ, সারভাইভার স্টোরি |
| `medical_reviewer` | গাইডের চিকিৎসা তথ্য অনুমোদন |
| `field_agent` | রেটিং সংগ্রহ, মাঠ পরিদর্শন |
| `hospital_manager` | হাসপাতালের তথ্য ও সক্ষমতা |
| `support_agent` | হেল্পলাইন, অভিযোগ, প্রাথমিক কেস |
| `finance` | পেমেন্ট ও ডাক্তার পে-আউট |
| `doctor` | নিজের প্রোফাইল, দ্বিতীয় মতামতের উত্তর |

### পারমিশন ম্যাট্রিক্স

`✓` পূর্ণ · `R` শুধু দেখা · `—` নেই

| Module | super | verif | content | medical | field | hospital | support | finance | doctor |
|---|---|---|---|---|---|---|---|---|---|
| Doctor applications | ✓ | ✓ | — | — | — | — | R | — | — |
| Doctor profiles | ✓ | ✓ | R | — | R | — | R | — | own R |
| Doctor publish | ✓ | ✓ | — | — | — | — | — | — | approve own |
| Ratings collect | ✓ | R | — | — | ✓ | — | — | — | — |
| Ratings verify | ✓ | ✓ | — | — | — | — | — | — | — |
| Hospitals | ✓ | R | — | — | R | ✓ | R | — | — |
| Cancer guides | ✓ | — | ✓ | R | — | — | R | — | — |
| Guide medical approve | ✓ | — | — | ✓ | — | — | — | — | — |
| Guide publish | ✓ | — | — | — | — | — | — | — | — |
| Patient cases | ✓ | ✓ | — | — | ✓ create | — | ✓ create | — | — |
| Patient case publish | ✓ | ✓ | — | — | — | — | — | — | — |
| Cost rates | ✓ | — | R | R | — | — | — | — | — |
| Second opinion | ✓ | R | — | — | — | — | R | R | own ✓ |
| Payments / payouts | ✓ | — | — | — | — | — | — | ✓ | own R |
| Helpline logs | ✓ | R | — | — | R | — | ✓ | — | — |
| Settings / ranking | ✓ | — | — | — | — | — | — | — | — |
| Users & roles | ✓ | — | — | — | — | — | — | — | — |
| Activity logs | ✓ | R own | — | — | — | — | — | R own | — |

### দুই-ধাপের অনুমোদন

| কাজ | কে করে | কে অনুমোদন করে |
|---|---|---|
| ডাক্তার প্রোফাইল প্রকাশ | verification_officer | **ডাক্তার নিজে** |
| গাইড প্রকাশ | content_editor | medical_reviewer → super_admin |
| রোগীর কেস প্রকাশ | field_agent / support | verification_officer (৪ ধাপ পূর্ণ হলে) |

---

## ৭. Laravel ফোল্ডার কাঠামো

```
app/
├── Enums/
│   DoctorStatus, CaseStatus, CapabilityStatus, HospitalType,
│   AnonymityLevel, VerificationStep
├── Models/
│   Doctor, DoctorApplication, Chamber, Hospital, HospitalCapability,
│   CancerType, Guide, GuideTerm, PatientCase, SecondOpinionRequest,
│   Payment, HelplineLog
├── Http/
│   ├── Controllers/
│   │   ├── HomeController, DoctorController, HospitalController,
│   │   │   GuideController, CostController, PatientCaseController,
│   │   │   SearchController, SecondOpinionController, PageController
│   │   └── Doctor/                    ← ডাক্তারের নিজস্ব পোর্টাল
│   ├── Requests/
│   ├── ViewComposers/
│   └── Middleware/LogAdminActivity
├── View/Components/                   ← Blade কম্পোনেন্ট ক্লাস
├── Filament/
│   ├── Resources/
│   │   DoctorResource, DoctorApplicationResource, HospitalResource,
│   │   GuideResource, PatientCaseResource, HelplineLogResource
│   ├── Pages/                         ← ড্যাশবোর্ড, settings
│   └── Widgets/                       ← রোলভেদে
├── Services/
│   ├── DoctorRankingService.php       ★ সবচেয়ে সংবেদনশীল
│   ├── CostEstimatorService.php
│   ├── SearchService.php
│   ├── VerificationService.php
│   ├── RatingAggregationService.php
│   └── FileVaultService.php
├── Policies/
├── Jobs/
│   ExpirePatientCases, RecalculateRatingSummaries,
│   RotateDoctorSeeds, RefreshCountCaches, GenerateSitemap
└── Observers/
    PatientCaseObserver   ← শিশুর ছবি ব্লক, expires_at
    DoctorObserver        ← slug, rotation_seed
```

---

## ৮. Core Services

### ৮.১ DoctorRankingService

```php
public function rank(array $filters): LengthAwarePaginator
{
    $w = Setting::group('ranking');   // DB থেকে, হার্ডকোড নয়

    return Doctor::query()
        ->published()
        ->with(['chambers.district','cancerTypes','ratingSummary'])
        ->when($filters['cancer_type_id'] ?? null, fn($q,$id) =>
            $q->whereHas('cancerTypes', fn($s) => $s->where('cancer_type_id',$id)))
        ->when($filters['district_id'] ?? null, fn($q,$id) =>
            $q->whereHas('chambers', fn($s) => $s->where('district_id',$id)))
        ->selectRaw("doctors.*, (
              (CASE WHEN dct.is_primary THEN ? ELSE ? END)
            + (CASE WHEN ch.district_id = ? THEN ? ELSE 0 END)
            + (COALESCE(drs.overall_score,0) * ? * drs.is_published)
        ) AS score", [$w['primary'],$w['secondary'],$filters['district_id'],$w['district'],$w['rating']])
        ->orderByDesc('score')
        ->orderByRaw('MOD(rotation_seed + ?, 1000)', [now()->weekOfYear])
        ->paginate(20);
}
```

**কোড রিভিউ চেকলিস্ট — এই সার্ভিসে যা কখনো থাকবে না:**
- `doctor_videos` টেবিলের join
- `payments` বা `is_paid_production` রেফারেন্স
- হাসপাতালের নাম বা খ্যাতি ভিত্তিক স্কোর
- ডাক্তারের নিজের ঘোষিত success rate
- ম্যানুয়াল `priority` বা `featured` ফ্ল্যাগ

**Rating ব্যবহারের শর্ত:** `is_published = false` হলে rating component ০ ধরা হবে — ডাক্তারকে শাস্তি দেওয়া হবে না।

### ৮.২ CostEstimatorService

সম্পূর্ণ stateless — গোপনীয়তা নীতিতে এই প্রতিশ্রুতি দেওয়া আছে।

```
direct   = Σ(base_rate[service] × stage_mult × hospital_mult)
indirect = travel + stay + food + outside_medicine + income_loss + misc
total    = direct + indirect
range    = [total × 0.85, total × 1.35]
monthly  = total / months
```

তিন ধরনের হাসপাতালের হিসাব একসাথে ফেরত যাবে — toggle-এ নতুন রিকোয়েস্ট লাগবে না।

### ৮.৩ VerificationService

```php
public function canPublishCase(PatientCase $case): bool
{
    return $case->verifications()->where('status','done')->count() === 4
        && $case->consent_form_path !== null
        && $case->accounts()->where('name_verified', true)->exists();
}
```

চারটি ধাপের একটিও বাকি থাকলে Filament-এ publish অ্যাকশন disabled, কন্ট্রোলারেও ব্লক।

### ৮.৪ SearchService

এক কোয়েরিতে চার ধরনের ফলাফল, আলাদা weight:

```
guide_terms   → 1.0   (সবচেয়ে নির্দিষ্ট — "HER2 মানে কী")
guides        → 0.9
doctors       → 0.8
hospitals     → 0.7
patient_cases → 0.5
```

ফলাফল না পেলে `search_logs`-এ লেখা — কোন প্রশ্নের উত্তর সাইটে নেই তা বোঝার একমাত্র উপায়।

---

## ৯. অ্যাডমিন প্যানেল (Filament)

### নেভিগেশন গ্রুপ

```
📊 ড্যাশবোর্ড                     [সবাই — রোলভেদে ভিন্ন উইজেট]

👨‍⚕️ ডাক্তার                        [super, verification]
   নতুন আবেদন · প্রোফাইল · যাচাই অপেক্ষমাণ · ৬ মাস পার

⭐ রেটিং                          [super, verification, field]
   সংগ্রহ · যাচাই · ডাক্তারভিত্তিক অগ্রগতি (কার কত/১০)

🏥 হাসপাতাল                       [super, hospital_manager]
   তালিকা · সক্ষমতা · ৩ মাস পার

📖 ক্যান্সার গাইড                  [super, content, medical]
   গাইড (৯/১৮ প্রকাশিত) · চিকিৎসা অনুমোদন · রিপোর্টের শব্দ

❤️ রোগীর কেস                      [super, verification, field, support]
   নতুন · যাচাই চলছে · প্রকাশিত (মেয়াদ) · অভিযোগ

📞 হেল্পলাইন                       [super, support]
💬 দ্বিতীয় মতামত                  [super, finance]
💰 পেমেন্ট ও পে-আউট                [super, finance]
🧮 খরচের হার                       [super]
🔎 সার্চ লগ (ফল পাওয়া যায়নি)      [super, content]
⚙️ সেটিংস ও র‍্যাঙ্কিং              [super]
👥 ইউজার ও রোল                     [super]
📋 অ্যাক্টিভিটি লগ                  [super]
```

### রোলভেদে ড্যাশবোর্ড

| Role | উইজেট |
|---|---|
| super_admin | সব সূচক, সাপ্তাহিক যাচাই, মেয়াদোত্তীর্ণ কেস, ট্রাফিক |
| verification_officer | অপেক্ষমাণ আবেদন, অসম্পূর্ণ ধাপ, মেয়াদ শেষ হচ্ছে |
| field_agent | আজকের ভিজিট, রেটিং লক্ষ্যমাত্রা, প্রমাণ বাকি |
| content_editor | খসড়া গাইড, অনুমোদন অপেক্ষমাণ, সার্চে না পাওয়া প্রশ্ন |
| support_agent | আজকের কল, ফলো-আপ, নতুন অভিযোগ |
| finance | অপরিশোধিত পে-আউট, ব্যর্থ পেমেন্ট |

### মাঠকর্মীর রেটিং ফর্ম — Filament-এর বাইরে

হাসপাতালের লাইনে দাঁড়িয়ে ব্যবহারের জন্য আলাদা মোবাইল পাতা (Blade + Alpine):

```
/field/rating/new
├── ডাক্তার বাছাই (সাম্প্রতিক উপরে)
├── ৩টি প্রশ্ন — বড় হ্যাঁ/না বাটন
├── প্রেসক্রিপশনের ছবি (camera capture)
└── জমা → অফলাইন হলে localStorage, পরে সিঙ্ক
```

লক্ষ্য: **৯০ সেকেন্ডে একটি জমা**। এক সকালে NICRH-এ ৩০–৫০টি সম্ভব।

---

## ১০. নিরাপত্তা

| বিষয় | ব্যবস্থা |
|---|---|
| স্টাফ অ্যাকাউন্ট | 2FA বাধ্যতামূলক, ৩০ মিনিট সেশন টাইমআউট |
| রোগীর নথি | private bucket, encryption at rest, signed URL ১৫ মিনিট |
| NID | সংরক্ষিত, **কখনো view-এ render হয় না** |
| দ্বিতীয় মতামতের ফাইল | শুধু নির্বাচিত ডাক্তার ও super_admin |
| অ্যাক্টিভিটি লগ | প্রতিটি verify/publish/delete-এ |
| ব্যাকআপ | দৈনিক DB + সাপ্তাহিক ফাইল, ৩০ দিন |
| Rate limit | পাবলিক ৬০/মিনিট, সার্চ ২০/মিনিট, AJAX ১২০/মিনিট |
| ফাইল আপলোড | MIME যাচাই, ১০ MB, ভাইরাস স্ক্যান |
| CSRF | Blade-এ `@csrf` — SPA-র তুলনায় অনেক সহজ |

### গোপনীয়তা নীতির সাথে কোডের মিল

| প্রতিশ্রুতি | কোডে |
|---|---|
| "খরচের হিসাব ব্রাউজারেই থাকে" | `/ajax/cost-estimate` কিছু সংরক্ষণ করে না |
| "শিশুদের ছবি প্রকাশ করি না" | `PatientCaseObserver` — age<18 → show_photo=false |
| "২৪ ঘণ্টায় তথ্য মুছে ফেলা হয়" | soft delete + ২৪ ঘণ্টা পর hard delete job |
| "সম্মতি ছাড়া প্রকাশ নয়" | consent null হলে publish ব্লক |

---

## ১১. ডেভেলপমেন্ট পর্যায়

| Phase | কাজ | সময় |
|---|---|---|
| **১. ভিত্তি** | Laravel setup, auth, roles, Filament, seed (district/cancer type), Tailwind config, layout ও কম্পোনেন্ট | ২ সপ্তাহ |
| **২. ডাক্তার** | আবেদন ফর্ম → যাচাই → প্রোফাইল → directory + ranking → ম্যাচ ইঞ্জিন | ৩.৫ সপ্তাহ |
| **৩. গাইড ও SEO** | Guide CMS, রিপোর্ট ডিকোডার, stage/myth/FAQ, schema.org, sitemap | ৩ সপ্তাহ |
| **৪. হাসপাতাল ও খরচ** | Hospital CRUD, capability ম্যাট্রিক্স, wait time, cost estimator | ২.৫ সপ্তাহ |
| **৫. রোগীর সহায়তা** | কেস, ৪ ধাপ যাচাই, প্রকাশ, মেয়াদ, অভিযোগ | ২ সপ্তাহ |
| **৬. দ্বিতীয় মতামত** | bKash/Nagad/SSLCommerz, ফাইল ভল্ট, ডাক্তার রেসপন্স, পে-আউট | ২.৫ সপ্তাহ |
| **৭. রেটিং ও হেল্পলাইন** | মাঠকর্মীর মোবাইল ফর্ম, aggregation, কল লগ | ১.৫ সপ্তাহ |
| **৮. পালিশ** | সার্চ, মোবাইল, performance, নিরাপত্তা অডিট | ২ সপ্তাহ |

**মোট ~১৯ সপ্তাহ** (React-এ ছিল ~২৬)। একজন সিনিয়র Laravel ডেভেলপার + একজন জুনিয়র।

### লঞ্চ কৌশল

**Phase ২ ও ৩ শেষ হলেই আংশিক লঞ্চ সম্ভব** — ডাক্তার ডিরেক্টরি + ক্যান্সার গাইড দিয়ে (প্রায় ৯ সপ্তাহে)। SEO-তে ফল আসতে ৪–৮ মাস লাগে, তাই ঐ পাতাগুলো যত আগে লাইভ হয় তত ভালো। বাকি ফিচার পরে যোগ হতে থাকবে।

---

## ১২. ডেভেলপারকে প্রথম দিনেই যা বলতে হবে

১. **সব পাবলিক পাতা Blade — কোনো SPA নয়।** SEO-ই প্রকল্পের প্রধান চালিকাশক্তি
২. **AJAX ফিল্টারেও URL বদলাতে হবে** (`history.pushState`), নইলে শেয়ার ও index ভাঙবে
৩. **র‍্যাঙ্কিং সার্ভিসে স্পনসরশিপ বা ভিডিওর প্রভাব থাকবে না** — স্থায়ী কোড রিভিউ আইটেম
৪. **রোগীর কেসে "কত টাকা উঠেছে" ফিল্ড নেই** — ইচ্ছাকৃত, ভুলে বাদ পড়েনি
৫. **সব সংখ্যা DB-তে** — খরচের হার, র‍্যাঙ্কিং ওয়েট, গুণক
৬. **বাংলা ফুলটেক্সট সার্চে ngram parser** — ডিফল্ট parser বাংলায় কাজ করে না
৭. **প্রাইভেট ফাইল কখনো public bucket-এ নয়**
৮. **prototype HTML গুলো রেফারেন্স** — ডিজাইন ও interaction হুবহু ঐভাবে

---

## ১৩. এখনো সিদ্ধান্ত বাকি

| বিষয় | কেন জরুরি |
|---|---|
| দ্বিতীয় মতামতের টাকা কার অ্যাকাউন্টে | CCB এখানে টাকা হ্যান্ডেল করছে — আইনি কাঠামো লাগবে |
| ডাক্তারের সাথে চুক্তি | প্রোফাইলের তথ্য CCB লিখছে — লিখিত সম্মতি ও দায়মুক্তি |
| Medical reviewer নিয়োগ | গাইড প্রকাশের পূর্বশর্ত |
| হোস্টিং | রোগীর স্বাস্থ্যতথ্য দেশে নাকি বিদেশে |
| আইনজীবী পর্যালোচনা | গোপনীয়তা নীতি ও সম্মতিপত্র |
| Filament নাকি কাস্টম অ্যাডমিন | ডেভেলপারের অভিজ্ঞতার উপর নির্ভর করে |
