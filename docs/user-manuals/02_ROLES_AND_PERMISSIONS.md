# ২. ব্যবহারকারীর রোল ও অনুমতি

CCB-তে ৯টি স্টাফ রোল আছে (Spatie Permission দিয়ে বানানো), সাথে দুইজন "বাইরের" ব্যবহারকারী — ডাক্তার (নিজস্ব পোর্টাল দিয়ে ঢোকেন) ও সাধারণ দর্শক/রোগী (কোনো লগইন লাগে না)। এই তালিকা `database/seeders/RolePermissionSeeder.php`-এ কোডে লেখা আছে — এটাই একমাত্র জায়গা যেখান থেকে অনুমতি বদলাতে হবে।

## এক নজরে — কে কী করে

| রোল | মূল কাজ | কোন মডিউলে |
|---|---|---|
| `super_admin` | সব কিছুর সম্পূর্ণ অ্যাক্সেস | সবগুলো |
| `verification_officer` | ডাক্তার আবেদন, রোগীর কেস, রেটিং — সব যাচাই ও প্রকাশ | ডাক্তার, রোগীর সহায়তা, রেটিং |
| `content_editor` | ক্যান্সার গাইড লেখা ও সম্পাদনা | গাইড |
| `medical_reviewer` | গাইডের চিকিৎসা-তথ্য অনুমোদন | গাইড, খরচ (শুধু দেখা) |
| `hospital_manager` | হাসপাতালের তথ্য হালনাগাদ | হাসপাতাল |
| `support_agent` | হেল্পলাইন কল লগ, নতুন রোগীর কেস তৈরি | হেল্পলাইন, রোগীর সহায়তা |
| `field_agent` | সরাসরি মাঠে গিয়ে রেটিং সংগ্রহ, রোগীর কেসের তথ্য জমা | রেটিং (নিজস্ব `/field` পোর্টাল) |
| `finance` | পেমেন্ট ও দ্বিতীয় মতামতের হিসাব দেখা | দ্বিতীয় মতামত/পেমেন্ট |
| `doctor` | নিজের প্রোফাইলের ফি/সময়সূচি, দ্বিতীয় মতামতের উত্তর | নিজস্ব `/doctor` পোর্টাল |

## বিস্তারিত অনুমতির তালিকা (verbatim)

### super_admin
সব ২৭টি অনুমতিই পায় — নিচের কোনো তালিকায় থাকা প্রয়োজন নেই, কোডে আলাদাভাবে সব দেওয়া আছে।

### verification_officer
`doctor_applications.view`, `doctor_applications.manage`, `doctors.view`, `doctors.manage`, `doctors.publish`, `ratings.view`, `ratings.verify`, `hospitals.view`, `cases.view`, `cases.manage`, `cases.verify`, `cases.publish`, `second_opinions.view`, `helpline.view`, `activity_logs.view_own`

### content_editor
`doctors.view`, `guides.view`, `guides.manage`, `cost_rates.view`

### medical_reviewer
`guides.view`, `guides.medical_approve`, `cost_rates.view`

### field_agent
`doctors.view`, `ratings.collect`, `hospitals.view`, `cases.view`, `cases.create`, `helpline.view`

### hospital_manager
`hospitals.view`, `hospitals.manage`

### support_agent
`doctor_applications.view`, `doctors.view`, `hospitals.view`, `guides.view`, `cases.view`, `cases.create`, `second_opinions.view`, `helpline.view`, `helpline.manage`

### finance
`second_opinions.view`, `payments.manage`, `activity_logs.view_own`

### doctor
`doctors.view_own`, `doctors.approve_own`, `second_opinions.respond_own`, `payments.view_own`

> এই রোলটাই ডাক্তারদের `/doctor` পোর্টালে ঢোকার অনুমতি দেয় — Filament `/admin`-এ না।

## ⚠️ গুরুত্বপূর্ণ ফাঁক — যা কোনো রোলকে দেওয়া হয়নি

কিছু অনুমতি সংজ্ঞায়িত আছে কিন্তু **কোনো নির্দিষ্ট রোলকে বরাদ্দ করা হয়নি** — মানে শুধু `super_admin`-ই সেই কাজ করতে পারে (কেউ ভুল করে ভাববে না যে সংশ্লিষ্ট রোলটা পারে):

- **`guides.publish`** — কোনো রোল নেই, তাই গাইড শুধু super_admin প্রকাশ করতে পারেন (content_editor লিখতে পারেন, কিন্তু প্রকাশের বাটন তাঁর জন্য নিষ্ক্রিয় থাকবে)।
- **`cost_rates.manage`** — কোনো রোল নেই, তাই খরচের হার শুধু super_admin বদলাতে পারেন (`content_editor`/`medical_reviewer` শুধু দেখতে পারেন, `finance`-এরও এই অনুমতি নেই)।
- **`second_opinions.manage`, `payments.manage`** বাদে বাকি `settings.manage`, `users.manage`, `activity_logs.view`, `doctors.manage`(সরাসরি, `verification_officer` ছাড়া) — এগুলোও কোনো নির্দিষ্ট রোলে বরাদ্দ নেই, শুধু super_admin-এর কাছে।

এগুলো ইচ্ছাকৃত ডিজাইন — যে কাজগুলো সবচেয়ে বেশি ঝুঁকিপূর্ণ (টাকার হিসাব বদলানো, গাইড পাবলিশ করা), সেগুলোর জন্য "কেউ একজন" নয়, নির্দিষ্টভাবে super_admin-এর অনুমোদন লাগে।

## নতুন স্টাফের অ্যাকাউন্ট কীভাবে খুলবেন

অ্যাডমিন প্যানেলে এখনো কোনো "ইউজার তৈরি করুন" পাতা নেই। নতুন স্টাফ যোগ করতে ডেভেলপারকে বলুন:

```bash
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'নাম', 'email' => 'email@example.com', 'password' => bcrypt('পাসওয়ার্ড')]);
>>> $user->assignRole('verification_officer'); // যে রোল দরকার
```

## ডাক্তারের অ্যাকাউন্ট কীভাবে তৈরি হয়

ডাক্তারের `/doctor` পোর্টালের লগইন **স্বয়ংক্রিয়ভাবে** তৈরি হয় — কোনো ম্যানুয়াল ধাপ লাগে না। বিস্তারিত **[03_DOCTOR_MODULE.md](03_DOCTOR_MODULE.md)**-এর "ডাক্তারের অ্যাকাউন্ট কীভাবে তৈরি হয়" অংশে।
