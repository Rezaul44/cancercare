# CancerCare Bangladesh — Database Schema

**Stack:** Laravel 11 · MySQL 8 · React (SPA)
**Convention:** snake_case, plural table names, Laravel migrations
**Charset:** `utf8mb4_unicode_ci` (বাংলা টেক্সটের জন্য বাধ্যতামূলক)

---

## ০. স্কিমা লেখার আগে — যে নীতিগুলো ডাটাবেসেই এনফোর্স হবে

এই নীতিগুলো আলোচনায় ঠিক হয়েছে এবং কোডে ভাঙা যাবে না। তাই স্কিমাতেই এমনভাবে রাখা হয়েছে যাতে ভাঙা কঠিন হয়।

| নীতি | ডাটাবেসে কীভাবে |
|---|---|
| টাকা দিয়ে র‍্যাঙ্কিং কেনা যায় না | `doctors` টেবিলে কোনো `is_sponsored`, `priority`, `boost` কলাম **নেই** এবং রাখা হবে না |
| CCB অনুদানের টাকা ধরে না | donation-এর জন্য কোনো wallet/escrow/transaction টেবিল নেই। শুধু রোগীর অ্যাকাউন্ট নম্বর প্রকাশ করা হয় |
| শিশুর ছবি প্রকাশ হয় না | `patient_cases.age < 18` হলে `show_photo` model boot-এ জোর করে `false` |
| ১০টি যাচাইকৃত রেটিং না হলে প্রকাশ নয় | `doctor_rating_summaries.is_published` ফ্ল্যাগ, `total_count >= 10` না হলে false |
| রোগীর কেস ৩০ দিন পর নিষ্ক্রিয় | `patient_cases.expires_at`, scheduled job |
| ভিডিও থাকা র‍্যাঙ্কিং বাড়ায় না | ranking query-তে `doctor_videos` join **নিষিদ্ধ** |

---

## ১. Auth, Roles ও Admin

`spatie/laravel-permission` ব্যবহার করা হবে।

### users
সব ধরনের লগইনকারী — অ্যাডমিন স্টাফ ও ডাক্তার। সাধারণ রোগী/দর্শকের অ্যাকাউন্ট লাগে না।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| name | varchar(150) | |
| email | varchar(191) unique nullable | |
| phone | varchar(20) unique nullable | ডাক্তারদের জন্য প্রধান |
| password | varchar(255) | |
| user_type | enum | `staff`, `doctor` |
| status | enum | `active`, `suspended`, `pending` |
| last_login_at | timestamp nullable | |
| two_factor_secret | text nullable | staff-দের জন্য বাধ্যতামূলক |
| created_by | bigint FK users nullable | |
| timestamps, soft_deletes | | |

### roles / permissions / model_has_roles
Spatie প্যাকেজের ডিফল্ট টেবিল।

### admin_activity_logs
কে কী বদলাল তার পূর্ণ রেকর্ড। যাচাইভিত্তিক প্ল্যাটফর্মে এটি বাধ্যতামূলক।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | |
| action | varchar(80) | `doctor.verified`, `case.published` ইত্যাদি |
| subject_type | varchar(120) | polymorphic |
| subject_id | bigint | |
| before | json nullable | পরিবর্তনের আগের মান |
| after | json nullable | |
| ip_address | varchar(45) | |
| user_agent | varchar(255) | |
| created_at | timestamp | |

**Index:** `(subject_type, subject_id)`, `(user_id, created_at)`

---

## ২. Reference / Lookup টেবিল

### divisions
`id, name_bn, name_en, slug`

### districts
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| division_id | FK | |
| name_bn, name_en | varchar(80) | |
| slug | varchar(80) unique | |
| distance_tier | enum | `local`, `near`, `far` — ঢাকা থেকে দূরত্ব, cost estimator-এ ব্যবহৃত |
| has_cancer_center | boolean | |

### cancer_types
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| name_bn | varchar(120) | স্তন ক্যান্সার |
| name_en | varchar(120) | Breast cancer |
| slug | varchar(120) unique | SEO URL |
| icon | varchar(60) | tabler icon key |
| color_key | varchar(30) | pink/blue/teal ইত্যাদি |
| short_description_bn | text | |
| gender_bias | enum nullable | `female`, `male`, `child`, `all` — ফিল্টারে ব্যবহৃত |
| is_common | boolean | "সবচেয়ে বেশি" ট্যাগ |
| doctor_count_cache | int | nightly job |
| hospital_count_cache | int | |
| guide_published | boolean | গাইড লেখা শেষ কিনা |
| sort_order | int | |
| timestamps | | |

### doctor_types
`id, key, label_bn, label_en` — medical / surgical / radiation / hemato / gynecologic / pediatric oncologist

### capabilities
হাসপাতালের সক্ষমতার তালিকা।
`id, key, label_bn, icon, sort_order`
মান: `radiotherapy`, `chemotherapy`, `cancer_surgery`, `bmt`, `pediatric_unit`, `palliative_care`, `pathology_lab`, `pet_ct`, `targeted_therapy`, `female_oncologist`, `blood_bank`

### settings
`id, key, value (json), group, updated_by, updated_at`
র‍্যাঙ্কিং ওয়েট, হেল্পলাইন নম্বর, খরচের গুণক — সব এখানে, কোডে হার্ডকোড নয়।

---

## ৩. Doctors

### doctor_applications
ফর্ম জমা দেওয়ার কাঁচা তথ্য। অনুমোদনের আগে `doctors` টেবিলে যায় না।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| full_name | varchar(150) | |
| bmdc_number | varchar(40) | |
| phone, email | varchar | |
| photo_path | varchar(255) | |
| degrees | json | ধাপ ২-এর ইনপুট |
| timeline | json | career timeline সারিগুলো |
| doctor_type_ids | json | |
| cancer_type_ids | json | |
| chambers | json | |
| extra_services | json | whatsapp / second opinion / telemedicine |
| preferred_call_time | varchar(40) | |
| preferred_call_day | varchar(40) | |
| declarations | json | ৫টি চেকবক্স + timestamp |
| status | enum | `submitted`, `under_review`, `call_scheduled`, `approved`, `rejected` |
| reviewed_by | FK users nullable | |
| review_note | text nullable | |
| doctor_id | FK doctors nullable | অনুমোদনের পর লিঙ্ক |
| timestamps | | |

### doctors
প্রকাশযোগ্য প্রোফাইল। **ডাক্তার নিজে দেন** কিছু ফিল্ড, **CCB লেখে** কিছু ফিল্ড — কলামে `source` মন্তব্যে চিহ্নিত।

| Column | Type | Source | Note |
|---|---|---|---|
| id | bigint PK | | |
| user_id | FK users nullable | | ডাক্তার লগইন করলে |
| application_id | FK nullable | | |
| name_bn | varchar(150) | doctor | ডা. সাদিয়া রহমান |
| name_en | varchar(150) | doctor | |
| slug | varchar(160) unique | system | SEO |
| bmdc_number | varchar(40) unique | doctor | |
| bmdc_verified_at | timestamp nullable | CCB | যাচাইয়ের প্রমাণ |
| bmdc_verified_by | FK users nullable | CCB | |
| photo_path | varchar(255) | doctor | |
| degrees_line_bn | varchar(400) | doctor | হিরোতে এক লাইনে |
| experience_years | tinyint | doctor | |
| current_position_bn | varchar(200) | doctor | |
| gender | enum | doctor | `male`, `female` — ফিল্টারে দরকার |
| philosophy_intro_bn | text nullable | **CCB** | কথা বলে লেখা |
| patients_treated | int nullable | **CCB** | আলোচনা করে বসানো |
| offers_second_opinion | boolean | doctor | |
| offers_whatsapp | boolean | doctor | |
| whatsapp_fee | int nullable | doctor | |
| whatsapp_response_hours | varchar(40) nullable | doctor | |
| second_opinion_fee | int nullable | doctor | |
| status | enum | | `draft`, `pending_approval`, `published`, `suspended` |
| doctor_approved_at | timestamp nullable | | প্রকাশের আগে ডাক্তারের সম্মতি |
| published_at | timestamp nullable | | |
| last_verified_at | date nullable | | ৬ মাস পরপর |
| rotation_seed | int | system | সমান স্কোরে ঘোরানোর জন্য |
| timestamps, soft_deletes | | | |

**Index:** `slug`, `status`, `bmdc_number`

### doctor_documents
যাচাইয়ের কাগজ — **কখনো পাবলিক নয়**।
`id, doctor_id FK, type (bmdc_certificate|degree|other), file_path, uploaded_at, is_private (default true)`

### doctor_timeline
ডাক্তার নিজে বসান।
`id, doctor_id FK, year_label varchar(40), title_bn, institution_bn, sort_order`

### doctor_doctor_type (pivot)
`doctor_id, doctor_type_id`

### doctor_cancer_type (pivot)
`doctor_id, cancer_type_id, is_primary boolean`
র‍্যাঙ্কিংয়ের প্রধান ম্যাচিং টেবিল।

### doctor_services
"যেসব চিকিৎসা করেন" ট্যাব — **CCB লেখে**।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| cancer_type_id | FK | কোন ট্যাবে |
| title_bn | varchar(160) | স্তন রক্ষা করে অপারেশন |
| description_bn | text | |
| badge_text_bn | varchar(80) nullable | "২৮০+ অপারেশন" |
| badge_color | varchar(30) | |
| icon | varchar(60) | |
| sort_order | int | |

### doctor_philosophy_points
`id, doctor_id, icon, title_bn, description_bn, sort_order` — CCB লেখে

### doctor_videos
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| type | enum | `intro`, `educational` |
| platform | enum | `youtube`, `facebook` |
| video_url | varchar(255) | |
| thumbnail_path | varchar(255) nullable | |
| title_bn | varchar(200) | |
| description_bn | text nullable | |
| duration_seconds | int | |
| view_count | int nullable | |
| produced_by | varchar(80) | ডিফল্ট "Doctor Pro Media" — প্রোফাইলে প্রকাশ্যে দেখানো হয় |
| is_paid_production | boolean | **শুধু হিসাবের জন্য, র‍্যাঙ্কিংয়ে কখনো ব্যবহার নয়** |
| sort_order | int | |

### chambers
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| hospital_id | FK nullable | তালিকাভুক্ত হাসপাতাল হলে |
| name_bn | varchar(160) | |
| address_bn | varchar(255) | |
| district_id | FK | |
| type | enum | `govt`, `private`, `npo` |
| fee | int | |
| days_bn | varchar(120) | রবি, মঙ্গল, বৃহস্পতি |
| time_from, time_to | time | |
| avg_wait_minutes | int nullable | |
| next_available_note | varchar(120) nullable | |
| is_active | boolean | |
| sort_order | int | |

**Index:** `(district_id, is_active)`, `(doctor_id)`

### doctor_cancer_type_stages
"এই ডাক্তার কি আমার জন্য" ম্যাচ ইঞ্জিনের (docs/prototypes/doctor_profile.html, `/doctors/{slug}`) স্টেজ-ভিত্তিক অংশ —
**CCB লেখে**। শুধু `doctor_cancer_type`-এ যুক্ত ক্যান্সারের জন্যই সারি থাকে।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| cancer_type_id | FK | |
| stage | enum | `1`, `2`, `3`, `4`, `unknown` |
| case_count | unsigned int | |
| success_rate_percent | unsigned tinyint | |
| note_bn | text | |

**Unique:** `(doctor_id, cancer_type_id, stage)`

### doctor_treatment_specialties
একই ম্যাচ ইঞ্জিনের চিকিৎসা-ধরন অংশ — স্টেজ-নিরপেক্ষ। **CCB লেখে**।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| cancer_type_id | FK | |
| treatment_key | enum | `surgery`, `chemo`, `radiation`, `hormone`, `unknown` |
| role | enum | `provides` (নিজে দেন), `refers` (অন্য বিশেষজ্ঞের কাছে পাঠান) |
| note_bn | text | |

**Unique:** `(doctor_id, cancer_type_id, treatment_key)`

### doctor_patient_testimonials
"রোগীদের ভিডিও অভিজ্ঞতা" — `doctor_videos` (ডাক্তারের নিজের ভিডিও) থেকে ইচ্ছাকৃতভাবে আলাদা টেবিল,
কারণ বিষয় রোগী। রোগীর লিখিত সম্মতি ছাড়া সারি তৈরি হয় না।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| cancer_type_id | FK nullable | |
| stage | enum nullable | `1`–`4`, `unknown` |
| outcome_bn | varchar(80) | "ক্যান্সার-মুক্ত" |
| anonymized_label_bn | varchar(120) | "নারী, ৪০-এর কোঠায়" |
| year | smallint | |
| video_url | varchar(255) | |
| duration_seconds | unsigned int | |
| thumbnail_color_key | varchar(30) | |
| sort_order | int | |

### doctor_patient_stories
"রোগীদের গল্প" — লিখিত গল্প। `patient_cases` (ধারা ৫, অনুদান-কেন্দ্রিক, ৩০ দিন পর নিষ্ক্রিয়) থেকে সম্পূর্ণ
আলাদা: এটি ডাক্তার প্রোফাইলের স্থায়ী বিশ্বাসযোগ্যতা-কন্টেন্ট, মেয়াদ ফুরায় না।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| cancer_type_id | FK nullable | |
| stage | enum nullable | `1`–`4`, `unknown` |
| district_id | FK nullable | |
| patient_label_bn | varchar(120) | নাম, বা "সালমা (নাম পরিবর্তিত)" |
| year | smallint | |
| outcome_duration_bn | varchar(60) | "২ বছর ক্যান্সার-মুক্ত" |
| quote_bn | text | |
| then_bn, now_bn | varchar(160) | |
| is_name_changed | boolean | |
| is_family_told | boolean | রোগী নিজে নয়, পরিবার বলেছে |
| sort_order | int | |

### doctor_story_highlights
"রোগীদের গল্প" সেকশনের উপরের পিল পরিসংখ্যান ("১২১ জন স্টেজ ৩ থেকে সুস্থ")। **CCB লেখে**, স্বয়ংক্রিয় হিসাব নয়।
`id, doctor_id FK, label_bn varchar(120), color_key varchar(30), sort_order int`

---

## ৪. Doctor Ratings (মাঠকর্মী সংগ্রহ করে)

### rating_criteria
`id, key, label_bn, sort_order, is_active`
মান: `explains_clearly`, `listens_well`, `not_rushed`, `follow_up_care`, `on_time`, `affordable`

> আলোচনায় ঠিক হয়েছে — মাঠে ৩টি প্রশ্ন জিজ্ঞেস করা হবে (৯০ সেকেন্ড), বাকিগুলো `is_active=false` রেখে পরে চালু করা যাবে।

### doctor_rating_submissions
প্রতিটি রোগীর একটি জমা।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| doctor_id | FK | |
| collected_by | FK users | মাঠকর্মী |
| source | enum | `field_hospital`, `phone`, `whatsapp` |
| collection_location | varchar(120) nullable | NICRH আউটডোর |
| patient_phone_hash | varchar(64) nullable | ডুপ্লিকেট ঠেকাতে, প্লেইন নম্বর নয় |
| proof_type | enum | `prescription`, `receipt`, `none` |
| proof_path | varchar(255) nullable | প্রাইভেট |
| answers | json | `{"explains_clearly": true, ...}` |
| free_comment_bn | text nullable | |
| is_verified | boolean | proof দেখে অনুমোদন |
| verified_by | FK users nullable | |
| collected_at | date | |
| timestamps | | |

**Unique:** `(doctor_id, patient_phone_hash)` — একই রোগী দুবার নয়

### doctor_rating_summaries
প্রোফাইলে যা দেখানো হয়। Nightly job রিক্যালকুলেট করে।

| Column | Type | Note |
|---|---|---|
| doctor_id | FK PK | |
| total_count | int | |
| criteria_scores | json | `{"explains_clearly": 96, ...}` শতাংশ |
| overall_score | decimal(2,1) nullable | ৪.৮ |
| is_published | boolean | **`total_count >= 10` না হলে false** |
| last_calculated_at | timestamp | |

---

## ৫. Hospitals

### hospitals
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| name_bn, name_en | varchar(200) | |
| slug | varchar(200) unique | |
| type | enum | `govt`, `private`, `npo` |
| district_id | FK | |
| address_bn | varchar(255) | |
| latitude, longitude | decimal(10,7) nullable | |
| phone | varchar(60) | |
| established_year | year nullable | |
| bed_count | int nullable | |
| oncologist_count | int nullable | |
| outdoor_fee | int nullable | |
| emergency_24h | boolean | |
| annual_patients | varchar(80) nullable | |
| cover_photo_path | varchar(255) | |
| description_bn | text | |
| last_verified_at | date | **প্রতি ৩ মাসে** |
| verified_by | FK users nullable | |
| status | enum | `draft`, `published`, `suspended` |
| timestamps | | |

### hospital_capabilities
পাতার সবচেয়ে গুরুত্বপূর্ণ অংশ — **যা নেই সেটাও রেকর্ড হয়**।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| hospital_id | FK | |
| capability_id | FK | |
| status | enum | `available`, `limited`, `not_available` |
| detail_bn | text | "৩টি লিনিয়ার অ্যাক্সিলারেটর সচল" |
| machine_count | tinyint nullable | |
| last_checked_at | date | |

**Unique:** `(hospital_id, capability_id)`

### hospital_wait_times
`id, hospital_id, service_key, min_weeks, max_weeks, label_bn, severity (short|medium|long), updated_at`
service_key: `first_visit`, `biopsy_report`, `chemo_start`, `radiotherapy_start`, `surgery_date`

### hospital_costs
`id, hospital_id, service_key, min_amount, max_amount, note_bn`
প্রোফাইলে সরকারি বনাম বেসরকারি তুলনা দেখানোর জন্য।

### hospital_prep_info
"যেসব বিষয়ে আগে থেকে প্রস্তুতি লাগে" — ৪টি কার্ড।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| hospital_id | FK | |
| key | enum | `blood_bank`, `medicine_supply`, `attendant_policy`, `records_return` |
| title_bn | varchar(120) | |
| description_bn | text | |
| flag_text_bn | varchar(60) | "ডোনার লাগতে পারে" |
| flag_type | enum | `positive`, `warning`, `negative` |

### hospital_practical_info
"বাইরের জেলা থেকে এলে যা জানা দরকার"।
`id, hospital_id, key (documents|timing|accommodation|transport|financial_aid), title_bn, description_bn, icon, sort_order`

### hospital_videos
`id, hospital_id, video_url, platform, title_bn, description_bn, duration_seconds, produced_by`
> শুধু **CCB-র নিজস্ব দিকনির্দেশনামূলক ভিডিও**। হাসপাতালের প্রচারণামূলক ভিডিও এখানে রাখা হবে না।

### hospital_experience_questions
`id, key, label_bn, sort_order, is_active`
মান: `wait_time_accurate`, `medicine_available`, `machine_working`, `doctor_explained`, `female_doctor_available`, `cost_as_told`

### hospital_experience_responses
`id, hospital_id, question_id, answer boolean, collected_by, source, collected_at`

### hospital_experience_summaries
`hospital_id, question_id, yes_count, total_count, percentage, is_published (total_count >= 30), last_calculated_at`

### hospital_doctor (pivot)
`hospital_id, doctor_id, schedule_note_bn` — প্রোফাইলে "এখানে যেসব ডাক্তার বসেন"

---

## ৬. Cancer Guide (SEO-র প্রধান ভিত্তি)

### guides
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| cancer_type_id | FK unique | এক ক্যান্সারে এক গাইড |
| title_bn | varchar(200) | |
| intro_bn | text | |
| meta_title, meta_description | varchar | SEO |
| reviewed_by_doctor_id | FK doctors nullable | **ব্যাজে দেখানো হয়** |
| reviewed_at | date nullable | |
| sources_note_bn | varchar(200) | "WHO ও NCCN নির্দেশনা" |
| read_minutes | tinyint | |
| status | enum | `draft`, `in_review`, `published` |
| published_at | timestamp nullable | |
| last_updated_at | date | |

> **নিয়ম:** `reviewed_by_doctor_id` খালি থাকলে `status = published` করা যাবে না। মডেল ভ্যালিডেশনে আটকাতে হবে।

### guide_videos
`id, guide_id, video_url, platform, title_bn, description_bn, duration_seconds, doctor_id`

### guide_terms — রিপোর্ট ডিকোডার
এই টেবিলটাই SEO-র আসল অস্ত্র। প্রতিটি শব্দের আলাদা anchor URL হবে।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| guide_id | FK | |
| code | varchar(120) | `HER2 Positive`, `Grade 1 / 2 / 3` |
| slug | varchar(140) | `/guide/breast-cancer#her2` |
| hint_bn | varchar(120) | "টার্গেটেড ওষুধ লাগবে কিনা" |
| plain_explanation_bn | text | |
| why_matters_bn | text | |
| scale | json nullable | Grade-এর মতো ৩ ধাপের স্কেল |
| search_keywords | varchar(255) | ফিল্টারের জন্য |
| sort_order | int | |

### guide_stages
`id, guide_id, stage (1-4), title_bn, description_bn, typical_treatment_bn, duration_bn, cost_min, cost_max, severity_color`

### guide_steps
"এখন কী করবেন" — ধাপ ১ সবসময় "বিশেষজ্ঞ দেখান"।
`id, guide_id, step_no, title_bn, description_bn, when_label_bn, urgency (urgent|normal), items json, sort_order`

### guide_myths
`id, guide_id, myth_bn, truth_bn, sort_order`

### guide_faqs
`id, guide_id, question_bn, answer_bn, sort_order`

---

## ৭. Cost Estimator

সব সংখ্যা টেবিলে — কোডে হার্ডকোড নয়, কারণ প্রতি ৬ মাসে হালনাগাদ হবে।

### cost_base_rates
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| cancer_type_id | FK | |
| service_key | enum | `diagnosis`, `surgery`, `chemo`, `radiation`, `targeted` |
| govt_amount | int | ভিত্তি হার (সরকারি) |
| default_months | tinyint | চিকিৎসার সময়কাল |
| is_applicable | boolean | যেমন রক্তের ক্যান্সারে surgery = false |
| updated_at | | |

### cost_multipliers
`id, group (stage|hospital_type|distance), key, multiplier decimal(4,2), label_bn`
- stage: 1→0.72, 2→1.00, 3→1.35, 4→1.55
- hospital_type: govt→1.0, npo→1.9, private→4.4
- distance: local→0.35, near→0.70, far→1.00

### cost_indirect_rates
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| key | enum | `travel`, `stay`, `food`, `outside_medicine`, `income_loss`, `misc` |
| label_bn | varchar(80) | |
| base_amount | int | প্রতি ইউনিট |
| unit | enum | `per_trip`, `per_month`, `percent_of_direct` |
| percent_value | decimal(4,2) nullable | ওষুধের জন্য direct-এর ১৮% |

### cost_phase_templates
"কোন ধাপে কত খরচ" ভাঙার জন্য।
`id, cancer_type_id, service_key, phase_title_bn, when_bn, breakdown json, sort_order`

### cost_estimate_logs (ঐচ্ছিক, analytics)
`id, cancer_type_id, stage, district_id, hospital_type, total_estimated, created_at`
**কোনো PII নয়** — শুধু কোন ক্যান্সারে কতজন হিসাব করছে তা বোঝার জন্য।

---

## ৮. Patient Support (অনুদান — CCB টাকা ধরে না)

### patient_cases
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| case_code | varchar(20) unique | CCB-2026-0042 |
| real_name | varchar(150) | **কখনো প্রকাশ নয় যদি anonymity সেট থাকে** |
| display_name_bn | varchar(150) | পাতায় যা দেখাবে |
| age | tinyint | |
| gender | enum | |
| cancer_type_id | FK | |
| stage | varchar(20) nullable | |
| district_id | FK | |
| hospital_id | FK nullable | |
| treating_doctor_name | varchar(150) nullable | |
| story_bn | text | রোগীর নিজের কথা |
| amount_needed | int | হাসপাতালের হিসাব অনুযায়ী |
| photo_path | varchar(255) nullable | |
| show_photo | boolean | **age < 18 হলে জোর করে false** |
| anonymity_level | enum | `full_name`, `partial`, `changed_name`, `initials_only` |
| consent_form_path | varchar(255) | লিখিত সম্মতির স্ক্যান — বাধ্যতামূলক |
| consent_signed_at | date | |
| status | enum | `draft`, `verifying`, `published`, `expired`, `fulfilled`, `withdrawn` |
| verified_at | timestamp nullable | |
| published_at | timestamp nullable | |
| expires_at | timestamp nullable | **published_at + 30 দিন** |
| created_by | FK users | |
| verified_by | FK users nullable | |
| timestamps, soft_deletes | | |

**Index:** `(status, expires_at)`, `(cancer_type_id)`, `(district_id)`

> **স্কিমায় ইচ্ছাকৃতভাবে যা নেই:** `amount_raised`, `donation_count`, `progress_percent`। CCB টাকা ধরে না, তাই কত উঠেছে তা জানার উপায়ও নেই — মিথ্যা সংখ্যা দেখানোর সুযোগ রাখা হয়নি।

### patient_case_verifications
৪ ধাপের যাচাই, প্রতিটির তারিখ ও দায়িত্বপ্রাপ্ত ব্যক্তি।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| patient_case_id | FK | |
| step | enum | `documents`, `hospital_confirm`, `identity`, `field_meeting` |
| status | enum | `pending`, `done`, `failed` |
| note_bn | text | পাতায় দেখানো হয় |
| completed_by | FK users | |
| completed_at | date | |

**Unique:** `(patient_case_id, step)`

### patient_case_documents
`id, patient_case_id, type (biopsy|treatment_plan|cost_estimate|nid|receipt), file_path, is_public boolean, redacted boolean, uploaded_by, uploaded_at`
> পাবলিক ফাইলে NID নম্বর, ঠিকানা, ফোন **redact করা বাধ্যতামূলক**।

### patient_case_costs
`id, patient_case_id, item_bn, amount, sort_order` — খরচের ভাঙা হিসাব

### patient_case_accounts
রোগীর নিজের অ্যাকাউন্ট — পাতায় প্রকাশ্যে দেখানো হয়।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| patient_case_id | FK | |
| type | enum | `bkash`, `nagad`, `bank` |
| account_number | varchar(60) | |
| account_name | varchar(150) | NID-র নামের সাথে মিলতে হবে |
| bank_name, branch | varchar(120) nullable | |
| name_verified | boolean | যাচাই হয়েছে কিনা |
| is_active | boolean | |

### patient_case_updates
`id, patient_case_id, note_bn, update_date, created_by, is_public`
৩০ দিনের নবায়ন ও অগ্রগতির খবর।

### patient_case_reports
জনসাধারণের অভিযোগ।
`id, patient_case_id, reporter_phone, reason, details, status (new|reviewing|resolved|dismissed), handled_by, resolved_at`

---

## ৯. Second Opinion (এখানে CCB টাকা হ্যান্ডেল করে)

> **গুরুত্বপূর্ণ পার্থক্য:** অনুদানে CCB টাকা ধরে না, কিন্তু দ্বিতীয় মতামতের ফি প্ল্যাটফর্মের মাধ্যমে আসে এবং ডাক্তারকে পে-আউট করতে হয়। এর জন্য আলাদা আইনি ও হিসাবরক্ষণ ব্যবস্থা লাগবে।

### second_opinion_requests
| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| request_code | varchar(20) unique | |
| patient_name | varchar(150) | |
| age | tinyint | |
| cancer_type_id | FK | |
| current_status | enum | `not_started`, `ongoing`, `completed`, `recurrence` |
| treatments_done_bn | text nullable | |
| question_bn | text | মূল প্রশ্ন |
| phone | varchar(20) | |
| district_id | FK | |
| doctor_id | FK doctors | |
| fee | int | |
| payment_id | FK payments nullable | |
| status | enum | `pending_payment`, `submitted`, `accepted`, `answered`, `refunded`, `cancelled` |
| expected_hours | smallint | |
| answered_at | timestamp nullable | |
| timestamps | | |

### second_opinion_files
`id, request_id, file_path, original_name, mime, size_bytes, uploaded_at`
> ফাইল S3-এ এনক্রিপ্টেড, signed URL দিয়ে শুধু নির্বাচিত ডাক্তার দেখতে পাবেন।

### second_opinion_responses
`id, request_id, doctor_id, response_bn, call_made boolean, call_note, answered_at`

### payments
পলিমরফিক — দ্বিতীয় মতামত ও ভবিষ্যতের অন্য সেবার জন্য।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| payable_type, payable_id | polymorphic | |
| gateway | enum | `bkash`, `nagad`, `sslcommerz` |
| amount | int | |
| gateway_fee | int nullable | |
| transaction_id | varchar(100) nullable | |
| status | enum | `initiated`, `success`, `failed`, `refunded` |
| raw_response | json | |
| paid_at | timestamp nullable | |

### doctor_payouts
`id, doctor_id, period_start, period_end, request_count, gross_amount, gateway_fee, net_amount, status (pending|paid), paid_at, reference`
> **CCB কমিশন নেয় না** — তাই `commission` কলাম নেই। শুধু গেটওয়ে চার্জ বাদ যায়।

---

## ১০. Helpline, Search, Content

### helpline_logs
হেল্পলাইনই CCB-র সবচেয়ে বড় ডেটা উৎস হবে।

| Column | Type | Note |
|---|---|---|
| id | bigint PK | |
| channel | enum | `phone`, `whatsapp` |
| caller_name | varchar(150) nullable | |
| caller_phone | varchar(20) | |
| district_id | FK nullable | |
| cancer_type_id | FK nullable | |
| topic | enum | `report_help`, `find_doctor`, `cost_query`, `financial_aid`, `case_application`, `complaint`, `other` |
| summary_bn | text | |
| outcome | enum | `resolved`, `referred`, `follow_up_needed`, `case_created` |
| linked_case_id | FK nullable | |
| handled_by | FK users | |
| follow_up_at | date nullable | |
| call_duration_minutes | smallint nullable | |
| created_at | | |

### search_logs
`id, query, results_count, clicked_type, clicked_id, session_hash, created_at`
কী খুঁজে মানুষ কিছু পায় না — সেটা জানার একমাত্র উপায়।

### pages
`id, slug, title_bn, content_bn (longtext), meta_title, meta_description, updated_by, published_at`
স্লাগ: `about`, `privacy`, `terms`, `verification-methodology`

### survivor_stories
`id, display_name_bn, age, cancer_type_id, district_id, years_free, quote_bn, photo_path, show_photo, consent_path, is_featured, status, published_at`

### media
`id, disk, path, mime, size_bytes, uploadable_type, uploadable_id, is_private, uploaded_by, created_at`

---

## ১১. Ranking (কোনো টেবিল নয় — সার্ভিস)

র‍্যাঙ্কিং রানটাইমে হিসাব হয়, কিন্তু ওয়েট `settings` টেবিলে থাকে যাতে প্রকাশ্য methodology পাতার সাথে সবসময় মেলে।

```
score = (specialty_match × W1)
      + (district_match × W2)
      + (rating_score × W3, শুধু is_published হলে)
      + (availability × W4)
      + rotation_jitter(doctor.rotation_seed, current_week)
```

**নিষিদ্ধ:** `doctor_videos`, `is_paid_production`, `payments`, কোনো স্পনসরশিপ ফিল্ড এই কোয়েরিতে join করা যাবে না। কোড রিভিউতে এটি চেকলিস্ট আইটেম।

`rotation_seed` প্রতি সপ্তাহে job দিয়ে পুনর্নির্ধারণ হয় — সমান স্কোরের ডাক্তাররা ঘুরে ঘুরে উপরে আসেন।

---

## ১২. প্রয়োজনীয় Index সারাংশ

| টেবিল | Index |
|---|---|
| doctors | `slug`, `status`, `(status, published_at)` |
| doctor_cancer_type | `(cancer_type_id, doctor_id)` |
| chambers | `(district_id, is_active)`, `(hospital_id)` |
| hospitals | `slug`, `(district_id, status)`, `(type, status)` |
| hospital_capabilities | `(capability_id, status)` — "রেডিওথেরাপি আছে এমন হাসপাতাল" ফিল্টার |
| patient_cases | `(status, expires_at)`, `(cancer_type_id, status)` |
| guide_terms | FULLTEXT `(code, search_keywords, plain_explanation_bn)` |
| guides | `cancer_type_id`, `status` |
| helpline_logs | `(created_at)`, `(topic)`, `(follow_up_at)` |

বাংলা ফুলটেক্সট সার্চের জন্য MySQL-এর ngram parser ব্যবহার করতে হবে:
`FULLTEXT KEY ft_bn (title_bn, description_bn) WITH PARSER ngram`

---

## ১৩. Scheduled Jobs

| Job | সময় | কাজ |
|---|---|---|
| `ExpirePatientCases` | প্রতিদিন ০১:০০ | `expires_at` পার হলে status → expired |
| `NotifyCaseExpiring` | প্রতিদিন ০৯:০০ | ৫ দিন বাকি থাকলে স্টাফকে জানানো |
| `RecalculateRatingSummaries` | প্রতিদিন ০২:০০ | doctor ও hospital উভয়ের |
| `RotateDoctorSeeds` | প্রতি সোমবার | rotation_seed নতুন করে |
| `RefreshCountCaches` | প্রতিদিন ০৩:০০ | cancer_types-এর doctor/hospital count |
| `FlagStaleVerifications` | সাপ্তাহিক | ডাক্তার ৬ মাস, হাসপাতাল ৩ মাস পার হলে |
| `GenerateSitemap` | প্রতিদিন | SEO |

---

## ১৪. ডেটা মাইগ্রেশনের ক্রম

Foreign key নির্ভরতা অনুযায়ী:

```
1. divisions → districts
2. cancer_types, doctor_types, capabilities, rating_criteria
3. users → roles → permissions
4. hospitals → hospital_* (সব শিশু টেবিল)
5. doctor_applications → doctors → doctor_* → chambers
6. guides → guide_* (terms, stages, steps, myths, faqs)
7. cost_base_rates, cost_multipliers, cost_indirect_rates
8. patient_cases → patient_case_*
9. second_opinion_requests → payments → doctor_payouts
10. helpline_logs, search_logs, pages, survivor_stories
```
