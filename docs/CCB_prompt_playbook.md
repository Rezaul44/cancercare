# CCB — Claude Code Prompt Playbook

প্রতিটি প্রম্পট কপি করে Claude Code-এ পেস্ট করো। ক্রম অনুসরণ করো — পরেরটা আগেরটার উপর নির্ভরশীল।

---

## ব্যবহারের নিয়ম

**১. এক প্রম্পটে এক কাজ।** "পুরো ডাক্তার মডিউল বানাও" বললে Claude অনেক কিছু একসাথে করবে, ভুল ধরা কঠিন হবে। ছোট ছোট ধাপে যাও।

**২. বড় কাজে Plan mode ব্যবহার করো।** প্রম্পটের শুরুতে লেখো "প্ল্যান মোডে" — Claude আগে পরিকল্পনা দেখাবে, তুমি সংশোধন করে অনুমোদন দেবে।

**৩. Prototype ফাইল @-mention করো।** UI বানানোর সময় `@docs/prototypes/homepage.html` লিখলে Claude হুবহু ঐ ডিজাইন অনুসরণ করবে।

**৪. প্রতিটি Phase আলাদা branch-এ।**
```bash
git checkout -b phase-2-doctors
```

**৫. Phase শেষে সবসময় বলো:** "এই Phase-এর জন্য টেস্ট লেখো এবং চালাও।"

---

## Prototype ফাইলের তালিকা

সব ফাইল `docs/prototypes/` ফোল্ডারে রাখো।

| ফাইল | ভেতরে যা আছে |
|---|---|
| `homepage.html` | হোমপেজ (search-first) |
| `doctor_directory.html` | ডাক্তার ডিরেক্টরি (দ্বিতীয় ট্যাবের রোগীর তালিকা পুরনো — ব্যবহার করবে না) |
| `doctor_profile.html` | ডাক্তারের প্রোফাইল |
| `hospitals.html` | হাসপাতালের তালিকা + প্রোফাইল (উপরে toggle) |
| `cost_estimator.html` | খরচের হিসাব |
| `guide_index.html` | ক্যান্সার গাইডের তালিকা |
| `cancer_guide.html` | গাইডের বিস্তারিত পাতা (স্তন ক্যান্সার) |
| `patient_support.html` | রোগীর তালিকা + বিস্তারিত পাতা (**এটাই সঠিক ভার্সন**) |
| `intake_flow.html` | "সবে ধরা পড়েছে" প্রস্তুতির ফ্লো |
| `onboarding.html` | ডাক্তারের আবেদন ফর্ম + রোগীর আবেদনের তথ্যপাতা |
| `trust_pages.html` | যাচাইকরণ পদ্ধতি + আমাদের সম্পর্কে + গোপনীয়তা নীতি |

⚠️ `doctor_directory.html`-এর দ্বিতীয় ট্যাবে রোগীর তালিকার পুরনো ভার্সন আছে
(progress bar সহ)। Phase 5-এ সবসময় `patient_support.html` ব্যবহার করো।

---

# Phase 1 — ভিত্তি

## 1.1 প্রজেক্ট সেটআপ

```
docs/CCB_database_schema.md এবং docs/CCB_system_documentation.md পড়ো।

Laravel 11 প্রজেক্টে নিচের প্যাকেজগুলো ইনস্টল ও কনফিগার করো:
- spatie/laravel-permission
- filament/filament (v3)
- laravel/horizon

তারপর:
1. .env.example-এ MySQL, Redis, S3 (public ও private দুটো disk) কনফিগার করো
2. config/filesystems.php-এ 's3_public' ও 's3_private' নামে দুটো disk যোগ করো
3. database charset utf8mb4_unicode_ci নিশ্চিত করো — বাংলা টেক্সটের জন্য বাধ্যতামূলক
4. Horizon ও Filament publish করো

কোনো migration এখনো লিখো না, শুধু setup।
```

## 1.2 Tailwind ও ফন্ট

```
CLAUDE.md-এ দেওয়া ডিজাইন টোকেন অনুযায়ী tailwind.config.js সেটআপ করো।

- সব রঙ (pink, slate, teal, gold, mist, line, ink)
- fontFamily: serif = Fraunces, sans = Inter, bn = Hind Siliguri
- Google Fonts থেকে তিনটি ফন্ট লোড করার জন্য layout-এ preconnect + link যোগ করো
- Alpine.js যোগ করো (npm অথবা CDN)

resources/css/app.css-এ .font-bn ইউটিলিটি ক্লাস রাখো যাতে বাংলা টেক্সটে সহজে ব্যবহার করা যায়।
```

## 1.3 রোল ও পারমিশন

```
docs/CCB_system_documentation.md-এর ধারা ৬-এর পারমিশন ম্যাট্রিক্স দেখো।

RolePermissionSeeder বানাও যাতে থাকবে:
- ৯টি রোল: super_admin, verification_officer, content_editor, medical_reviewer,
  field_agent, hospital_manager, support_agent, finance, doctor
- ম্যাট্রিক্স অনুযায়ী প্রতিটি রোলের পারমিশন
- একটি ডিফল্ট super_admin ইউজার (.env থেকে ইমেইল ও পাসওয়ার্ড)

পারমিশনের নাম এই ফরম্যাটে: 'doctors.view', 'doctors.publish', 'cases.verify' ইত্যাদি।
```

## 1.4 Lookup টেবিল ও seed data

```
docs/CCB_database_schema.md-এর ধারা ২ অনুযায়ী migration লেখো:
divisions, districts, cancer_types, doctor_types, capabilities, rating_criteria, settings

তারপর seeder বানাও:
- বাংলাদেশের ৮টি বিভাগ ও ৬৪টি জেলা (বাংলা ও ইংরেজি নাম সহ)
- districts.distance_tier ঠিক করো: ঢাকা=local, গাজীপুর/নারায়ণগঞ্জ/মানিকগঞ্জ=near, বাকি=far
- ১৮টি cancer_type (স্তন, ফুসফুস, মুখ, জরায়ু মুখ, রক্ত, পাকস্থলী, কোলন, প্রোস্টেট,
  শিশু, লিভার, খাদ্যনালী, থাইরয়েড, ডিম্বাশয়, মূত্রথলি, অগ্ন্যাশয়, মস্তিষ্ক, হাড়, ত্বক)
  — প্রথম ৯টি is_common=true
- ৬টি doctor_type, ১১টি capability, ৬টি rating_criteria
- settings-এ ranking weights ও helpline নম্বরের ডিফল্ট মান
```

## 1.5 Layout ও মূল কম্পোনেন্ট

```
@docs/prototypes/homepage.html দেখো।

layouts/app.blade.php বানাও — ঐ prototype-এর topbar, nav ও footer হুবহু অনুসরণ করে।
তারপর এই কম্পোনেন্টগুলো আলাদা করো:

- x-nav
- x-footer
- x-search-box (রোটেটিং placeholder + dropdown, Alpine.js দিয়ে)
- x-medical-disclaimer
- x-ranking-notice (props: basis array)

সব ক্লাস Tailwind-এ রূপান্তর করো, inline <style> রাখবে না।
বাংলা টেক্সটে font-bn ক্লাস দাও।
```

---

# Phase 2 — ডাক্তার

## 2.1 Migration ও Model

```
প্ল্যান মোডে।

docs/CCB_database_schema.md-এর ধারা ৩ ও ৪ অনুযায়ী migration ও Eloquent model বানাও:

doctor_applications, doctors, doctor_documents, doctor_timeline,
doctor_doctor_type, doctor_cancer_type, doctor_services, doctor_philosophy_points,
doctor_videos, chambers, rating_criteria, doctor_rating_submissions,
doctor_rating_summaries

সব relation, cast, ও এই scope গুলো যোগ করো: published(), verified()
Enum ক্লাস বানাও: DoctorStatus, ChamberType

DoctorObserver বানাও — creating-এ slug ও rotation_seed সেট করবে।

স্কিমার কলাম হুবহু অনুসরণ করো, নিজে থেকে কলাম যোগ করবে না।
```

## 2.2 ডাক্তারের আবেদন ফর্ম

```
@docs/prototypes/onboarding.html — "ডাক্তার যুক্ত হওয়া" অংশটা দেখো।

৪ ধাপের আবেদন ফর্ম বানাও:
- route: GET/POST /for-doctors
- ধাপগুলো Alpine.js দিয়ে (পেজ রিলোড ছাড়া), progress bar সহ
- ফাইল আপলোড: ছবি, BMDC সনদ, ডিগ্রির সনদ → private disk-এ
- FormRequest দিয়ে সম্পূর্ণ ভ্যালিডেশন
- জমা হলে doctor_applications-এ status='submitted'
- ধন্যবাদ পাতায় বলো: ৫–৭ কর্মদিবসে যাচাই, তারপর ফোন করা হবে

prototype-এর ডিজাইন হুবহু অনুসরণ করো — বিশেষ করে ধাপ ৪-এর "ফর্ম জমা দেওয়ার পর যা হবে"
অংশটা, কারণ ওখানে প্রত্যাশা ঠিক করা হয়েছে।
```

## 2.3 Filament — আবেদন যাচাই

```
DoctorApplicationResource বানাও Filament-এ:

- তালিকায়: নাম, BMDC নম্বর, জমার তারিখ, status badge
- ফিল্টার: status অনুযায়ী
- View পাতায়: সব তথ্য + আপলোড করা সনদ দেখার ব্যবস্থা (signed URL)
- Action: "যাচাই সম্পন্ন" — BMDC ও ডিগ্রি চেক করার চেকলিস্ট
- Action: "অনুমোদন" → doctors টেবিলে রেকর্ড তৈরি করবে, timeline ও chamber সহ
- Action: "বাতিল" — কারণ লিখতে হবে

শুধু super_admin ও verification_officer দেখতে পাবে।
প্রতিটি action activity log-এ লিখবে।
```

## 2.4 DoctorRankingService

```
প্ল্যান মোডে।

app/Services/DoctorRankingService.php বানাও।

স্কোর = (বিশেষত্বের মিল × W1) + (জেলার মিল × W2) + (রেটিং × W3, শুধু is_published হলে)
     + (অ্যাপয়েন্টমেন্ট সহজলভ্যতা × W4)
সমান স্কোরে: ORDER BY MOD(rotation_seed + weekOfYear, 1000)

সব weight settings টেবিল থেকে আসবে, হার্ডকোড নয়।

⚠️ CLAUDE.md-এর নীতি ১ কঠোরভাবে মানো — এই সার্ভিসে doctor_videos, payments,
is_paid_production, বা হাসপাতালের খ্যাতি কোনোভাবেই ব্যবহার হবে না।

সাথে একটি টেস্ট লেখো যা প্রমাণ করবে ranking query-তে এই টেবিলগুলো join হয় না।
```

## 2.5 ডাক্তার ডিরেক্টরি

```
@docs/prototypes/doctor_directory.html — "ডাক্তার খুঁজছি" অংশ দেখো।

/doctors পাতা বানাও:
- উপরে দুই dropdown (ক্যান্সারের ধরন + জেলা) — এটা Blade ফর্ম, AJAX নয়
- বাম sidebar-এ ফিল্টার: ডাক্তারের ধরন, হাসপাতালের ধরন, ফি, লিঙ্গ, সুবিধা
- ফিল্টার পরিবর্তনে AJAX দিয়ে ফলাফল আপডেট, কিন্তু history.pushState() দিয়ে
  URL অবশ্যই বদলাবে: /doctors?cancer=breast&district=dhaka
- sort options: প্রাসঙ্গিকতা, নিকটতম, কম ফি, রেটিং, দ্রুত সময়
- x-ranking-notice কম্পোনেন্ট বাধ্যতামূলক
- প্রতিটি কার্ডে কেন এসেছে তার কারণ ট্যাগ
- নিচে rotation নোট

DoctorRankingService ব্যবহার করো। N+1 এড়াতে eager load করো।
```

## 2.6 ডাক্তার প্রোফাইল

```
@docs/prototypes/doctor_profile.html

/doctors/{slug} পাতা বানাও — prototype-এর সব section:
হিরো, পরিচিতি ভিডিও, ম্যাচ ইঞ্জিন, যেসব চিকিৎসা করেন (tab), পেশাগত জীবন,
শিক্ষামূলক ভিডিও, রোগীদের ভিডিও, রোগীদের গল্প, রেটিং, চিকিৎসা দর্শন,
sidebar (অ্যাপয়েন্টমেন্ট, চেম্বার, WhatsApp, বিশেষত্ব)

গুরুত্বপূর্ণ:
- রেটিং section শুধু তখনই দেখাবে যখন rating_summary.is_published = true
- ভিডিওর নিচে "Doctor Pro Media প্রযোজিত · তালিকার ক্রমে প্রভাব ফেলে না" লেখা থাকবে
- পাতার নিচে তথ্যের উৎস নোট (কোনটা ডাক্তার দিয়েছেন, কোনটা CCB লিখেছে)
- schema.org Physician markup যোগ করো
```

## 2.7 ম্যাচ ইঞ্জিন

```
@docs/prototypes/doctor_profile.html-এর ম্যাচ ইঞ্জিন অংশ ও তার JavaScript দেখো।

resources/js/doctor-match.js বানাও — prototype-এর লজিক প্রায় হুবহু।

পার্থক্য: ডেটা hardcode নয়, AJAX দিয়ে আসবে:
GET /ajax/doctors/{doctor}/match?cancer_type=&stage=&treatment=

সার্ভার সাইডে DoctorMatchService বানাও যা doctor_cancer_type ও doctor_services
টেবিল দেখে verdict ঠিক করবে:
- ক্যান্সার type মিলে না → "উপযুক্ত নন" + directory-তে redirect লিংক
- মিলে কিন্তু treatment আংশিক → "ভালো মিল, কিছু বিবেচনা" + অন্য বিশেষজ্ঞের পরামর্শ
- সব মিলে → "উপযুক্ত"

কোনো ভুয়া success rate দেখাবে না — শুধু case count যদি DB-তে থাকে।
```

## 2.8 Phase 2 টেস্ট

```
Phase 2-এর জন্য Feature test লেখো:

1. ranking query কখনো doctor_videos বা payments টেবিল join করে না
2. অপ্রকাশিত (draft) ডাক্তার directory-তে আসে না
3. rating_summary.is_published=false হলে প্রোফাইলে রেটিং render হয় না
4. doctor_approved_at null হলে publish করা যায় না
5. ফিল্টার query string URL-এ প্রতিফলিত হয়
6. BMDC সনদের ফাইল signed URL ছাড়া অ্যাক্সেস করা যায় না

সব টেস্ট চালাও এবং যেগুলো fail করে সেগুলো ঠিক করো।
```

---

# Phase 3 — গাইড ও SEO

## 3.1 Migration ও Model

```
docs/CCB_database_schema.md-এর ধারা ৬ অনুযায়ী migration ও model:
guides, guide_videos, guide_terms, guide_stages, guide_steps, guide_myths, guide_faqs

guide_terms-এ FULLTEXT index দাও ngram parser সহ:
FULLTEXT KEY ft_terms (code, search_keywords, plain_explanation_bn) WITH PARSER ngram

Guide model-এ নিয়ম: reviewed_by_doctor_id null হলে status='published' করা যাবে না
(saving event-এ throw করবে)।
```

## 3.2 গাইড index পাতা

```
@docs/prototypes/guide_index.html

/guide পাতা বানাও:
- উপরে সার্চ (ক্যান্সারের নাম ও রিপোর্টের শব্দ দুটোই খুঁজবে)
- তিনটি টুল শর্টকাট কার্ড
- ক্যান্সারের কার্ড গ্রিড — শুধু guide_published=true যেগুলো
- যেগুলো এখনো হয়নি তার জন্য "আরও X টি আসছে" কার্ড
- নিচে হেল্পলাইন ব্যান্ড
- ফিল্টার ট্যাব (নারী/পুরুষ/শিশু/ভিডিও আছে) — Alpine.js দিয়ে
```

## 3.3 গাইড detail পাতা

```
@docs/prototypes/cancer_guide.html

/guide/{slug} পাতা — prototype-এর সব section:
disclaimer, ডাক্তারের ভিডিও, রিপোর্ট ডিকোডার, স্টেজ সিলেক্টর,
এখন কী করবেন, ভুল ধারণা, প্রশ্নোত্তর, পরবর্তী পদক্ষেপ, sidebar TOC

রিপোর্ট ডিকোডার:
- প্রতিটি term accordion, Alpine.js দিয়ে
- সার্চ বক্সে টাইপ করলে ফিল্টার (client-side, কারণ term সংখ্যা কম)
- প্রতিটি term-এর নিজস্ব anchor id: #her2-positive

স্টেজ সিলেক্টর: ৪টি কার্ড, ক্লিকে নিচের detail বদলাবে (Alpine)
```

## 3.4 SEO সম্পূর্ণ করা

```
সব পাবলিক পাতায় SEO যোগ করো:

1. partials/seo/meta.blade.php — title, description, canonical, og tags
2. partials/seo/schema.blade.php — পাতাভেদে schema.org JSON-LD:
   - ডাক্তার → Physician
   - হাসপাতাল → Hospital
   - গাইড → MedicalWebPage (reviewedBy অবশ্যই)
   - গাইডের FAQ → FAQPage
   - guide_terms → DefinedTerm
3. sitemap.xml জেনারেট করার job (nightly) — সব guide, doctor, hospital পাতা
4. robots.txt — /admin, /field, /ajax ব্লক
5. সব ছবিতে বাংলা alt টেক্সট

spatie/laravel-sitemap ব্যবহার করতে পারো।
```

## 3.5 Filament — গাইড CMS

```
GuideResource বানাও:
- guide-এর সব শিশু টেবিল (terms, stages, steps, myths, faqs) Repeater বা
  Relation Manager দিয়ে সম্পাদনাযোগ্য
- content_editor লিখবে, কিন্তু publish করতে পারবে না
- medical_reviewer-এর জন্য আলাদা action: "চিকিৎসা তথ্য অনুমোদন"
  → reviewed_by_doctor_id ও reviewed_at সেট করবে
- super_admin-এর "প্রকাশ" action — শুধু reviewed_at থাকলে সক্রিয়

তালিকায় দেখাও কোনটা কোন পর্যায়ে আছে।
```

---

# Phase 4 — হাসপাতাল ও খরচ

## 4.1 হাসপাতাল migration ও Filament

```
docs/CCB_database_schema.md-এর ধারা ৫ অনুযায়ী migration ও model:
hospitals, hospital_capabilities, hospital_wait_times, hospital_costs,
hospital_prep_info, hospital_practical_info, hospital_videos,
hospital_experience_questions, hospital_experience_responses,
hospital_experience_summaries, hospital_doctor

HospitalResource বানাও Filament-এ। সবচেয়ে গুরুত্বপূর্ণ:
capability ম্যাট্রিক্স যেন সহজে পূরণ করা যায় — ১১টি capability-র প্রতিটির জন্য
available/limited/not_available + বিস্তারিত নোট।

hospital_manager রোল সম্পাদনা করতে পারবে।
```

## 4.2 হাসপাতাল তালিকা ও প্রোফাইল

```
@docs/prototypes/hospitals.html

দুটো পাতা বানাও:

/hospitals — ফিল্টারের প্রথম গ্রুপ "যে চিকিৎসা দরকার" (capability অনুযায়ী),
তারপর ধরন, বিভাগ, সুবিধা। প্রতিটি কার্ডে যা আছে ও যা নেই দুটোই দেখাবে।

/hospitals/{slug} — সক্ষমতার গ্রিড, চেনার ভিডিও, অপেক্ষার সময়, খরচের তুলনা,
প্রস্তুতির ৪ বিষয়, রোগীদের অভিজ্ঞতা (স্টার নয়, শতাংশ), এখানে বসা ডাক্তার,
বাইরের জেলা থেকে এলে যা জানা দরকার, sidebar-এ যোগাযোগ

⚠️ স্টার রেটিং দেবে না — শুধু structured প্রশ্নের শতাংশ, আর উপরে ব্যাখ্যা
কেন স্টার রেটিং দেওয়া হয় না।
```

## 4.3 Cost Estimator

```
@docs/prototypes/cost_estimator.html — বিশেষ করে <script> অংশটা।

১. Migration: cost_base_rates, cost_multipliers, cost_indirect_rates, cost_phase_templates
২. Seeder: prototype-এর JS-এ যে সংখ্যাগুলো আছে সেগুলো DB-তে ঢোকাও
৩. app/Services/CostEstimatorService.php — prototype-এর গণনার লজিক, কিন্তু
   সব সংখ্যা DB থেকে
৪. POST /ajax/cost-estimate — stateless, কিছু সংরক্ষণ করবে না
   (গোপনীয়তা নীতিতে এই প্রতিশ্রুতি দেওয়া আছে)
   তিন ধরনের হাসপাতালের হিসাব একসাথে ফেরত দেবে
৫. resources/js/cost-estimator.js — UI, prototype হুবহু
৬. /cost-estimator পাতা

Filament-এ CostRateResource যাতে super_admin সংখ্যা হালনাগাদ করতে পারে।
```

---

# Phase 5 — রোগীর সহায়তা

## 5.1 Migration ও Observer

```
docs/CCB_database_schema.md-এর ধারা ৮ অনুযায়ী migration:
patient_cases, patient_case_verifications, patient_case_documents,
patient_case_costs, patient_case_accounts, patient_case_updates, patient_case_reports

⚠️ patient_cases-এ amount_raised, donation_count, progress_percent কলাম
কখনো যোগ করবে না — CCB টাকা ধরে না, তাই জানার উপায়ও নেই।

PatientCaseObserver বানাও:
- saving: age < 18 হলে show_photo জোর করে false
- publishing: expires_at = now()->addDays(30)
- case_code জেনারেট: CCB-2026-0001 ফরম্যাটে
```

## 5.2 Filament — কেস যাচাই

```
PatientCaseResource বানাও।

সবচেয়ে গুরুত্বপূর্ণ: ৪ ধাপের যাচাই ওয়ার্কফ্লো
- documents, hospital_confirm, identity, field_meeting
- প্রতিটি ধাপে: নোট, কে করল, কবে করল
- অগ্রগতি বার দেখাবে কত ধাপ শেষ

VerificationService::canPublishCase() বানাও:
৪ ধাপ + consent_form_path + name_verified account — সব থাকলে তবেই true

"প্রকাশ" action শুধু তখনই সক্রিয় হবে। Controller-এও চেক করবে, শুধু UI-তে নয়।

field_agent ও support_agent কেস তৈরি করতে পারবে, কিন্তু প্রকাশ করতে পারবে না।
```

## 5.3 পাবলিক পাতা

```
@docs/prototypes/patient_support.html

/patients — তালিকা:
- উপরে "CCB-র ভূমিকা কী" দুই কলাম (যা করি / যা করি না)
- প্রতারণা সতর্কতা
- কার্ডে: প্রয়োজনীয় টাকা (progress bar নয়), যাচাইয়ের ৩ প্রমাণ, যাচাইয়ের তারিখ
- sort: সাম্প্রতিক যাচাই, জরুরি, প্রয়োজন বেশি/কম

/patients/{case_code} — বিস্তারিত:
- রোগীর কথা, যাচাইকরণের timeline, যাচাইকৃত নথি (redacted), খরচের হিসাব
- ডান sidebar-এ bKash/Nagad/ব্যাংক নম্বর, কপি বাটন সহ
- প্রতারণা সতর্কতা ও দায়সীমার নোট

/apply-for-support — শুধু তথ্যের পাতা, কোনো ফর্ম নয় (manual প্রক্রিয়া)
```

## 5.4 মেয়াদ ব্যবস্থাপনা

```
তিনটি job বানাও:

1. ExpirePatientCases — প্রতিদিন ০১:০০, expires_at পার হলে status='expired'
2. NotifyCaseExpiring — প্রতিদিন ০৯:০০, ৫ দিন বাকি থাকলে staff-কে জানাবে
3. Filament-এ "মেয়াদ বাড়াও" action — নতুন আপডেট যোগ করলে ৩০ দিন বাড়বে

Filament তালিকায় "আর কত দিন" কলাম দেখাও, ৫ দিনের কম হলে লাল।
```

---

# Phase 6 — দ্বিতীয় মতামত

## 6.1 Migration ও ফাইল ভল্ট

```
Migration: second_opinion_requests, second_opinion_files,
second_opinion_responses, payments, doctor_payouts

app/Services/FileVaultService.php বানাও:
- private disk-এ আপলোড
- signed URL জেনারেট (১৫ মিনিট মেয়াদ)
- অ্যাক্সেস চেক: শুধু নির্বাচিত ডাক্তার ও super_admin

⚠️ doctor_payouts-এ commission কলাম নেই — CCB কমিশন নেয় না,
শুধু gateway fee বাদ যায়।
```

## 6.2 ৪ ধাপের ফর্ম

```
দ্বিতীয় মতামতের ডিজাইন এখনো prototype-এ নেই। নিচের স্পেসিফিকেশন অনুসরণ করো,
আর স্টাইলিং @docs/prototypes/onboarding.html-এর ফর্ম ডিজাইন থেকে নাও।

/second-opinion — ৪ ধাপ:
1. রিপোর্ট আপলোড (drag-drop, একাধিক ফাইল, private disk)
2. রোগীর পরিস্থিতি ও প্রশ্ন
3. ডাক্তার বাছাই (যাঁরা offers_second_opinion=true)
4. সারাংশ + পেমেন্ট

উপরে "কেন দ্বিতীয় মতামত" তিনটি কার্ড।
ধাপ ৪-এ জরুরি অবস্থার সতর্কতা রাখো — "অপেক্ষা করবেন না, চিকিৎসা শুরু করুন"।
```

## 6.3 পেমেন্ট গেটওয়ে

```
bKash, Nagad ও SSLCommerz ইন্টিগ্রেশন করো।

- app/Services/Payment/ ফোল্ডারে প্রতিটির আলাদা driver, একটি common interface
- payments টেবিল polymorphic (payable_type, payable_id)
- সফল হলে second_opinion_request.status = 'submitted'
- ব্যর্থ হলে retry-র সুযোগ
- webhook handling ও signature verification

স্যান্ডবক্স credentials .env-এ রাখো।
```

## 6.4 ডাক্তারের পোর্টাল

```
/doctor/* রুট বানাও (auth + role:doctor):

- ড্যাশবোর্ড: অপেক্ষমাণ দ্বিতীয় মতামত, নিজের প্রোফাইলের অবস্থা
- দ্বিতীয় মতামতের তালিকা ও উত্তর দেওয়ার ফর্ম
- নিজের প্রোফাইল দেখা + শুধু চেম্বার/ফি/সময়সূচি সম্পাদনা
- প্রোফাইল অনুমোদনের বাটন (doctor_approved_at)
- পে-আউটের হিসাব

⚠️ ডাক্তার doctor_services, philosophy, patients_treated সম্পাদনা করতে পারবেন না —
এগুলো CCB লেখে। শুধু দেখতে ও আপত্তি জানাতে পারবেন।
```

---

# Phase 7 — রেটিং ও হেল্পলাইন

## 7.1 মাঠকর্মীর মোবাইল ফর্ম

```
/field/rating/new পাতা — মোবাইল-first, Filament-এর বাইরে, Blade + Alpine।

লক্ষ্য: হাসপাতালের লাইনে দাঁড়িয়ে ৯০ সেকেন্ডে একটি জমা।

- ডাক্তার বাছাই: সার্চ + সাম্প্রতিক ব্যবহৃত উপরে
- ৩টি প্রশ্ন (is_active=true যেগুলো), বড় হ্যাঁ/না বাটন
- প্রেসক্রিপশনের ছবি: <input capture="environment">
- ফোন নম্বর → hash করে সংরক্ষণ (ডুপ্লিকেট ঠেকাতে), প্লেইন নয়
- অফলাইন হলে localStorage-এ রেখে নেটওয়ার্ক ফিরলে সিঙ্ক

শুধু field_agent রোল অ্যাক্সেস করতে পারবে।
```

## 7.2 রেটিং যাচাই ও aggregation

```
1. Filament-এ RatingSubmissionResource — প্রমাণ দেখে verify করার জন্য
2. RecalculateRatingSummaries job (nightly):
   - প্রতিটি criteria-র শতাংশ
   - overall_score
   - is_published = (total_count >= 10)
3. Filament ড্যাশবোর্ডে উইজেট: কোন ডাক্তারের কত/১০ হয়েছে

⚠️ ১০টির কম হলে প্রোফাইলে রেটিং section পুরো লুকানো থাকবে —
"০টি রেটিং" দেখানোও যাবে না।
```

## 7.3 হেল্পলাইন

```
helpline_logs migration + Filament resource।

- support_agent দ্রুত কল লগ করতে পারবে
- topic, outcome, follow_up_at
- কল থেকে সরাসরি patient_case তৈরির action
- ড্যাশবোর্ডে: আজকের কল, ফলো-আপ বাকি
- সাপ্তাহিক প্রতিবেদন: কোন বিষয়ে সবচেয়ে বেশি কল আসছে

এই ডেটাই বলে দেবে সাইটে কী তথ্যের ঘাটতি আছে।
```

---

# Phase 8 — সার্চ, মোবাইল, পালিশ

## 8.1 সার্চ

```
app/Services/SearchService.php বানাও।

এক কোয়েরিতে ৫ ধরনের ফলাফল, আলাদা weight:
guide_terms 1.0, guides 0.9, doctors 0.8, hospitals 0.7, patient_cases 0.5

- MySQL FULLTEXT + ngram parser (বাংলার জন্য)
- /search পাতা: ট্যাব দিয়ে ভাগ, প্রতিটি গ্রুপে "সব দেখুন"
- /ajax/search/suggest — হোমপেজের dropdown-এর জন্য
- ফলাফল না পেলে search_logs-এ লিখবে
- ফলাফল শূন্য হলে হেল্পলাইনের কার্ড দেখাবে

@docs/prototypes-এর search results অংশ অনুসরণ করো।
```

## 8.2 মোবাইল

```
সব পাবলিক পাতা মোবাইলে ঠিক করো। ৭০%+ ট্রাফিক মোবাইল থেকে আসবে।

অগ্রাধিকার:
1. হোমপেজ
2. গাইড (index + detail)
3. ডাক্তার (directory + profile)
4. হাসপাতাল
5. খরচের হিসাব
6. বাকি সব

- nav → hamburger + drawer
- সব grid → single column
- sidebar → উপরে বা নিচে সরাও
- টেবিল → কার্ড লেআউটে
- ফন্ট সাইজ ও tap target সামঞ্জস্য করো (কমপক্ষে 44px)

Chrome DevTools-এ 375px ও 414px-এ পরীক্ষা করো।
```

## 8.3 Performance

```
1. সব লিস্ট পাতায় N+1 চেক করো (laravel-debugbar দিয়ে)
2. Redis cache: cancer_types, districts, settings, guide পাতা
3. ছবি: responsive srcset + lazy loading + WebP
4. স্কিমার ধারা ১২-এর সব index আছে কিনা যাচাই করো
5. Lighthouse চালাও — লক্ষ্য: Performance 90+, SEO 100
```

## 8.4 নিরাপত্তা অডিট

```
নিরাপত্তা যাচাই করো এবং সমস্যা ঠিক করো:

1. NID নম্বর কোনো Blade view বা JSON response-এ যায় কিনা — grep করো
2. private disk-এর ফাইল signed URL ছাড়া অ্যাক্সেস হয় কিনা
3. প্রতিটি Filament resource-এ সঠিক permission চেক আছে কিনা
4. Rate limit: পাবলিক ৬০/মিনিট, সার্চ ২০/মিনিট, AJAX ১২০/মিনিট
5. ফাইল আপলোডে MIME যাচাই ও সাইজ সীমা
6. staff অ্যাকাউন্টে 2FA বাধ্যতামূলক
7. সব form-এ CSRF

CLAUDE.md-এর ৮টি critical টেস্ট চালাও।
```

---

# সাধারণ কাজের প্রম্পট

## বাগ ঠিক করা

```
[এরর মেসেজ পেস্ট করো]

এই এররটা আসছে [কোন পাতা/কাজে]। কারণ খুঁজে বের করো এবং ঠিক করো।
একই ধরনের সমস্যা অন্য কোথাও আছে কিনা দেখো।
```

## কোড রিভিউ

```
সদ্য লেখা কোডটা রিভিউ করো এই দিক থেকে:
1. CLAUDE.md-এর কোনো নীতি ভাঙছে কিনা
2. N+1 query আছে কিনা
3. ভ্যালিডেশন সম্পূর্ণ কিনা
4. permission চেক আছে কিনা
5. বাংলা টেক্সটে font-bn ক্লাস আছে কিনা
```

## নতুন ফিচার যোগের আগে

```
আমি [ফিচারের নাম] যোগ করতে চাই।

আগে বলো: এটা CLAUDE.md-এর কোনো নীতির সাথে সংঘর্ষে যায় কিনা?
সংঘর্ষ হলে ফিচারটা বাদ, নীতি নয়।

সংঘর্ষ না হলে প্ল্যান দাও — কোন টেবিল, কোন সার্ভিস, কোন পাতা লাগবে।
```

## রিফ্যাক্টর

```
[ফাইলের নাম] অনেক বড় হয়ে গেছে। এটাকে ভাগ করো —
লজিক Service-এ, ভ্যালিডেশন FormRequest-এ, view কম্পোনেন্টে।

কোনো আচরণ বদলাবে না। রিফ্যাক্টরের পর টেস্ট চালিয়ে নিশ্চিত করো।
```

---

## যেসব ভুল হতে পারে — খেয়াল রাখো

| লক্ষণ | কী হয়েছে |
|---|---|
| `patient_cases`-এ progress bar এসেছে | Claude "সুবিধাজনক" ফিচার যোগ করেছে — বাদ দাও |
| ranking-এ featured/priority কলাম | নীতি ১ ভাঙা হয়েছে |
| ডাক্তার নিজের case count সম্পাদনা করতে পারছে | CCB-লিখিত ফিল্ডে অ্যাক্সেস দেওয়া হয়েছে |
| AJAX ফিল্টারে URL বদলাচ্ছে না | SEO ভাঙবে, pushState যোগ করাও |
| খরচের সংখ্যা কোডে হার্ডকোড | DB-তে সরাও |
| বাংলা সার্চে ফলাফল আসছে না | ngram parser নেই |

প্রতি ৫–৬ সেশন পর একবার বলো:
```
CLAUDE.md আবার পড়ো এবং যাচাই করো কোনো নীতি ভাঙা হয়েছে কিনা।
```
