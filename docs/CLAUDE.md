# CancerCare Bangladesh (CCB) — Project Context

এই ফাইলটি প্রতিটি সেশনে স্বয়ংক্রিয়ভাবে পড়া হয়। নতুন সিদ্ধান্ত হলে এখানে যোগ করো।

---

## প্রকল্প কী

বাংলাদেশের প্রথম সমন্বিত ক্যান্সার কেয়ার প্ল্যাটফর্ম। রোগী ও পরিবার যেন জানতে পারে —
কোন ডাক্তার দেখাব, কোথায় চিকিৎসা হয়, কত খরচ লাগবে, আর টাকা না থাকলে কী করব।

**ভাষা:** পুরো UI বাংলায়। কোড, কমেন্ট, ভেরিয়েবল ইংরেজিতে।

---

## Stack

- Laravel 11 + **Blade** (কোনো SPA নয় — SEO-ই প্রকল্পের প্রধান চালিকাশক্তি)
- Alpine.js (dropdown, tab, accordion) + vanilla JS module (cost estimator, match engine)
- Tailwind CSS
- MySQL 8 (`utf8mb4_unicode_ci` — বাংলার জন্য বাধ্যতামূলক)
- Filament 3 (অ্যাডমিন প্যানেল)
- Redis (cache + queue), S3/Spaces (public + private bucket)

---

## অপরিবর্তনীয় নীতি — এগুলো কখনো ভাঙা যাবে না

এই নিয়মগুলো প্ল্যাটফর্মের বিশ্বাসযোগ্যতার ভিত্তি। কোনো ফিচার এগুলোর সাথে সংঘর্ষে গেলে ফিচারটি বাদ যাবে, নীতি নয়।

### ১. র‍্যাঙ্কিং কেনা যায় না
`DoctorRankingService`-এ **কখনো** এগুলো ব্যবহার হবে না:
- `doctor_videos` টেবিলের join
- `payments`, `is_paid_production`, বা যেকোনো স্পনসরশিপ ফিল্ড
- হাসপাতালের নাম বা "খ্যাতি" ভিত্তিক স্কোর
- ডাক্তারের নিজের ঘোষিত success rate
- ম্যানুয়াল `priority` / `featured` ফ্ল্যাগ

স্কোরে শুধু: বিশেষত্বের মিল, জেলার দূরত্ব, যাচাইকৃত রেটিং, অ্যাপয়েন্টমেন্টের সহজলভ্যতা।
সমান স্কোরে `rotation_seed` দিয়ে ঘোরানো হয়।

### ২. CCB অনুদানের টাকা ধরে না
- দাতা সরাসরি রোগীর bKash/ব্যাংকে পাঠান
- তাই `patient_cases`-এ `amount_raised`, `donation_count`, `progress_percent` **নেই** — ইচ্ছাকৃত
- কোনো wallet/escrow টেবিল বানানো যাবে না
- ব্যতিক্রম: দ্বিতীয় মতামতের ফি প্ল্যাটফর্মের মাধ্যমে আসে (`payments` + `doctor_payouts`), তবে সেখানেও CCB কমিশন নেয় না

### ৩. শিশুর ছবি কখনো প্রকাশ নয়
`PatientCaseObserver`-এ `age < 18` হলে `show_photo` জোর করে `false`।

### ৪. যাচাই ছাড়া প্রকাশ নয়
- রোগীর কেস: ৪ ধাপ (documents, hospital_confirm, identity, field_meeting) + লিখিত সম্মতি
- ডাক্তার: BMDC যাচাই + ডাক্তারের নিজের অনুমোদন (`doctor_approved_at`)
- গাইড: `reviewed_by_doctor_id` খালি থাকলে publish করা যাবে না

### ৫. রেটিং ১০টির কম হলে দেখানো হয় না
`doctor_rating_summaries.is_published` — `total_count >= 10` না হলে false।

### ৬. আমরা ডাক্তার নই
কোনো পাতায় রোগ নির্ণয়, পরীক্ষা প্রেসক্রাইব, বা চিকিৎসার নির্দেশ দেওয়া হবে না।
গাইড ও intake-এ `<x-medical-disclaimer />` বাধ্যতামূলক।

---

## SEO — আলোচনার অযোগ্য

- সব পাবলিক পাতা server-rendered Blade
- AJAX ফিল্টার করলেও `history.pushState()` দিয়ে URL বদলাতে হবে
- schema.org: Physician, Hospital, MedicalWebPage, FAQPage, DefinedTerm
- `MedicalWebPage`-এ `reviewedBy` অবশ্যই দিতে হবে (E-E-A-T)
- বাংলা ফুলটেক্সট সার্চে **ngram parser** লাগবে, ডিফল্ট parser বাংলায় কাজ করে না
- sitemap.xml nightly regenerate

---

## যা DB-তে থাকবে, কোডে হার্ডকোড নয়

- র‍্যাঙ্কিং ওয়েট → `settings` টেবিল
- খরচের হার ও গুণক → `cost_base_rates`, `cost_multipliers`, `cost_indirect_rates`
- হেল্পলাইন নম্বর, মেয়াদের দিন সংখ্যা → `settings`

কারণ: প্রতি ৬ মাসে হালনাগাদ লাগবে, তখন ডেভেলপার ছাড়াই বদলাতে হবে।

---

## ফাইল স্টোরেজ

```
public bucket   → ডাক্তারের ছবি, হাসপাতালের ছবি, রোগীর প্রকাশযোগ্য ছবি
private bucket  → BMDC সনদ, ডিগ্রি, NID, বায়োপসি রিপোর্ট, সম্মতিপত্র,
                  রেটিং প্রমাণ, দ্বিতীয় মতামতের ফাইল
```

Private ফাইল শুধু signed URL (১৫ মিনিট মেয়াদ) দিয়ে সার্ভ হবে।
**NID নম্বর কখনো কোনো Blade view-তে render হবে না।**

---

## রেফারেন্স ফাইল

```
docs/
├── CCB_database_schema.md          ← সম্পূর্ণ স্কিমা, এটাই সোর্স অব ট্রুথ
├── CCB_system_documentation.md     ← আর্কিটেকচার, রোল, সার্ভিস
└── prototypes/                     ← ডিজাইন রেফারেন্স, HTML+CSS+JS
    ├── homepage.html
    ├── doctor_profile.html
    ├── doctor_directory.html
    ├── hospitals.html
    ├── cost_estimator.html
    ├── cancer_guide.html
    ├── guide_index.html
    ├── patient_support.html
    ├── intake_flow.html
    ├── onboarding.html
    └── trust_pages.html
```

**Blade বানানোর সময় prototype-এর HTML/CSS হুবহু অনুসরণ করো।** ডিজাইন ইতিমধ্যে চূড়ান্ত —
নতুন করে ডিজাইন করার দরকার নেই, শুধু Blade কম্পোনেন্টে ভাগ করে Tailwind-এ রূপান্তর করো।

---

## Tailwind ডিজাইন টোকেন

```js
colors: {
  pink: { 50:'#FFF4F8', 100:'#FFE1EC', 200:'#FFC2D8',
          500:'#F20162', 600:'#DE0159', 700:'#C90154', 800:'#A80B4C' },
  slate:{ 300:'#A8B1B7', 500:'#5A656D', 700:'#333B41', 900:'#141719' },
  teal: { 50:'#F0FAF7', 100:'#DCF2ED', 500:'#12A085', 700:'#0B6E5C' },
  gold: { soft:'#FDF4E3', line:'#F0DFBC', DEFAULT:'#C98A1E' },
  mist:'#F7F7F5', line:'#E8E8E4', ink:'#141719',
}
fontFamily: {
  serif: ['Fraunces','Georgia','serif'],      // সব headline
  sans:  ['Inter','Hind Siliguri','sans-serif'],
  bn:    ['Hind Siliguri','Inter','sans-serif'],  // বাংলা টেক্সটে .font-bn
}
```

**রঙের নিয়ম:** pink হলো ব্র্যান্ডের স্বাক্ষর, সাইটের রঙ নয়। শুধু logo, donation CTA,
progress bar, স্তন ক্যান্সারের আইকন, hero-র একটি শব্দে। বাকি সব charcoal/neutral।

---

## কাজের ক্রম

| Phase | কাজ |
|---|---|
| ১ | Setup, auth, roles, Filament, seed data, Tailwind config, layout + কম্পোনেন্ট |
| ২ | ডাক্তার — আবেদন → যাচাই → প্রোফাইল → directory + ranking + ম্যাচ ইঞ্জিন |
| ৩ | গাইড ও SEO — CMS, রিপোর্ট ডিকোডার, schema.org, sitemap |
| ৪ | হাসপাতাল ও খরচ — capability ম্যাট্রিক্স, wait time, cost estimator |
| ৫ | রোগীর সহায়তা — কেস, ৪ ধাপ যাচাই, মেয়াদ |
| ৬ | দ্বিতীয় মতামত — পেমেন্ট, ফাইল ভল্ট, পে-আউট |
| ৭ | রেটিং ও হেল্পলাইন — মাঠকর্মীর মোবাইল ফর্ম |
| ৮ | সার্চ, মোবাইল পালিশ, নিরাপত্তা অডিট |

**Phase ২ ও ৩ শেষেই আংশিক লঞ্চ** — ডাক্তার ডিরেক্টরি + গাইড দিয়ে।
SEO-তে ফল আসতে ৪–৮ মাস, তাই ঐ পাতা যত আগে লাইভ তত ভালো।

---

## কোড কনভেনশন

- Controller পাতলা রাখো, লজিক `app/Services/`-এ
- সব ভ্যালিডেশন `FormRequest`-এ
- Enum ব্যবহার করো, magic string নয় (`DoctorStatus::Published`)
- N+1 এড়াতে সবসময় eager load
- Model-এ query scope: `->published()`, `->verified()`
- Blade কম্পোনেন্ট পুনর্ব্যবহারযোগ্য রাখো
- প্রতিটি verify/publish/delete-এ activity log

---

## টেস্ট যেগুলো অবশ্যই লিখতে হবে

```php
// এই টেস্টগুলো নীতি রক্ষার শেষ প্রহরী
test('ranking never uses video or payment data');
test('child patient photo is never published');
test('case cannot publish without 4 verification steps');
test('case cannot publish without consent form');
test('rating summary hidden below 10 submissions');
test('guide cannot publish without medical reviewer');
test('NID never appears in any response');
test('private files not accessible without signed url');
```

---

## যা এখনো সিদ্ধান্ত হয়নি

- দ্বিতীয় মতামতের টাকা কার অ্যাকাউন্টে আসবে (আইনি কাঠামো)
- Medical reviewer নিয়োগ — গাইড প্রকাশের পূর্বশর্ত
- হোস্টিং দেশে নাকি বিদেশে (রোগীর স্বাস্থ্যতথ্য)
- আইনজীবী দিয়ে গোপনীয়তা নীতি পর্যালোচনা

এগুলোর সমাধান না হওয়া পর্যন্ত সংশ্লিষ্ট ফিচার প্রোডাকশনে যাবে না।
