# Cancer Care Bangladesh (CCB)
## অ্যাডমিনিস্ট্রেটর ও ডেভেলপার গাইডলাইন এবং অপারেশনাল ম্যানুয়াল (Admin & Developer Manual)

---

## 📑 সূচিপত্র
1. [ডেভেলপার ও ডেভঅপ্স নির্দেশিকা (Developer & DevOps Guide)](#১-ডেভেলপার-ও-ডেভঅপ্স-নির্দেশিকা)
   - ১.১ [সিস্টেম রিকোয়ারমেন্টস (System Requirements)](#১১-সিস্টেম-রিকোয়ারমেন্টস)
   - ১.২ [স্থানীয় ডেভেলপমেন্ট সেটআপ (Local Setup)](#১২-স্থানীয়-ডেভেলপমেন্ট-সেটআপ)
   - ১.৩ [প্রোডাকশন ডিপ্লয়মেন্ট ও শিডিউলার (Production Deployment & Cron)](#১৩-প্রোডাকশন-ডিপ্লয়মেন্ট-ও-শিডিউলার)
   - ১.৪ [কোডবেস আর্কিটেকচার (Codebase Structure)](#১৪-কোডবেস-আর্কিটেকচার)
   - ১.৫ [টেস্টিং গাইডলাইন (Automated Testing)](#১৫-টেস্টিং-গাইডলাইন)
2. [অ্যাডমিনিস্ট্রেটর নির্দেশিকা (Administrator Operations Manual)](#২-অ্যাডমিনিস্ট্রেটর-নির্দেশিকা)
   - ২.১ [অ্যাডমিন প্যানেলে লগইন ও নিরাপত্তা](#২১-অ্যাডমিন-প্যানেলে-লগইন-ও-নিরাপত্তা)
   - ২.২ [ইউজার ও পারমিশন ম্যানেজমেন্ট (RBAC Roles)](#২২-ইউজার-ও-পারমিশন-ম্যানেজমেন্ট)
   - ২.৩ [ডাক্তার আবেদন অনুমোদন ওয়ার্কফ্লো](#২৩-ডাক্তার-আবেদন-অনুমোদন-ওয়ার্কফ্লো)
   - ২.৪ [রোগীর সহায়তা ও ৪-ধাপের ভেরিফিকেশন পরিচালনা](#২৪-রোগীর-সহায়তা-ও-৪-ধাপের-ভেরিফিকেশন-পরিচালনা)
   - ২.৫ [ক্যান্সার গাইড ও খরচের হারের সিএমএস](#২৫-ক্যান্সার-গাইড-ও-খরচের-হারের-সিএমএস)
   - ২.৬ [ক্যাশ ও অপ্টিমাইজেশন পরিচালনা](#২৬-ক্যাশ-ও-অপ্টিমাইজেশন-পরিচালনা)
3. [জরুরি ট্রাবলশুটিং ও কমান্ড চিটশিট (Troubleshooting Cheat Sheet)](#৩-জরুরি-ট্রাবলশুটিং-ও-কমান্ড-চিটশিট)

---

## ১. ডেভেলপার ও ডেভঅপ্স নির্দেশিকা

### ১.১ সিস্টেম রিকোয়ারমেন্টস
- **PHP:** `^8.2` (Extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `intl`, `gd`, `redis`)
- **Database:** MySQL `8.0+` (Default Collation: `utf8mb4_unicode_ci`)
- **Queue/Cache:** Redis `6.0+`
- **Dependency Managers:** Composer `2.6+`, Node.js `18.0+` & NPM
- **Storage:** AWS S3 বা S3 Compatible Storage (MinIO / Wasabi / DigitalOcean Spaces)

---

### ১.২ স্থানীয় ডেভেলপমেন্ট সেটআপ (Local Setup)

```bash
# ১. রিপোজিটরি ক্লোন করুন
git clone https://github.com/Rezaul44/cancercare.git
cd cancercare

# ২. পিএইচপি ও নোড ডিপেন্ডেন্সি ইনস্টল করুন
composer install
npm install

# ৩. এনভায়রনমেন্ট কনফিগারেশন তৈরি করুন
cp .env.example .env
php artisan key:generate

# ৪. ডাটাবেজ মাইগ্রেশন ও ডেমো সিডার চালান
php artisan migrate --seed

# ৫. স্টোরেজ লিংক তৈরি করুন
php artisan storage:link

# ৬. ফ্রন্টএন্ড অ্যাসেট কম্পাইল করুন
npm run build # অথবা ডেভেলপমেন্টের জন্য npm run dev

# ৭. লোকাল ডেভেলপমেন্ট সার্ভার চালু করুন
php artisan serve
```

---

### ১.৩ প্রোডাকশন ডিপ্লয়মেন্ট ও শিডিউলার

#### ক. `.env` প্রোডাকশন কনফিগারেশন
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cancercarebd.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cancercare_prod
DB_USERNAME=cancercare_user
DB_PASSWORD=SecurePasswordHere

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

# ডুয়েল স্টোরেজ ডিস্ক
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET_PUBLIC=cancercare-public
AWS_BUCKET_PRIVATE=cancercare-private
```

#### খ. প্রোডাকশন ক্রন জব শিডিউলার (Crontab)
সার্ভারে প্রতি মিনিটে লারাভেলের শিডিউলার রান করাতে crontab-এ নিচের লাইনটি যুক্ত করুন:
```bash
* * * * * cd /path-to-cancercare && php artisan schedule:run >> /dev/null 2>&1
```
> **শিডিউল অনুযায়ী যা যা স্বয়ংক্রিয়ভাবে চলবে:**
> - **রাত ০১:০০ টা:** `ExpirePatientCases` (মেয়াদোত্তীর্ণ কেস `expired` করা)
> - **রাত ০২:০০ টা:** `sitemap:generate` (সার্চ ইঞ্জিন ইনডেক্সিং সাইটম্যাপ)
> - **সকাল ০৯:০০ টা:** `NotifyCaseExpiring` (৫ দিন বাকি থাকা কেসগুলোর জন্য স্টাফ নোটিফিকেশন)

#### গ. কিউ ওয়ার্কার (Supervisor Configuration)
Horizon বা কিউ প্রসেস চালু রাখতে `/etc/supervisor/conf.d/cancercare-worker.conf` তৈরি করুন:
```ini
[program:cancercare-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-cancercare/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path-to-cancercare/storage/logs/horizon.log
```

---

### ১.৪ কোডবেস আর্কিটেকচার ও মডিউল ডিরেক্টরি

```
cancercare/
├── app/
│   ├── Enums/                 # স্ট্যাটাস, জেন্ডার, ভেরিফিকেশন স্টেপ ইত্যাদি Enums
│   ├── Filament/Resources/    # ফিলামেন্ট অ্যাডমিন রিসোর্স ও ফর্ম/টেবিল
│   ├── Http/Controllers/      # পাবলিক ও AJAX কন্ট্রোলার
│   ├── Jobs/                  # ব্যাকগ্রাউন্ড শিডিউলড জবস (ExpirePatientCases, NotifyCaseExpiring)
│   ├── Models/                # Eloquent মডেল ও রিলেশনশিপ
│   ├── Observers/             # PatientCaseObserver (শিশু সুরক্ষা ও কেস কোড লজিক)
│   └── Services/              # VerificationService, CostEstimatorService
├── config/                    # ফাইলসিস্টেম, ফিলামেন্ট, পারমিশন কনফিগ
├── database/
│   ├── migrations/            # মাইগ্রেশন ফাইলসমূহ
│   └── seeders/               # ডেমো ও মাস্টার ডাটা সিডার
├── resources/
│   ├── views/                 # ব্লেড টেমপ্লেট ও পাবলিক পোর্টাল
│   └── js/ & css/             # টেলউইন্ড ও কাস্টম স্ক্রিপ্ট
├── routes/
│   ├── web.php                # পাবলিক ও অথেনটিকেটেড ওয়েব রাউট
│   └── console.php            # আর্টিস্যান কমান্ড ও ক্রন শিডিউল
└── tests/Feature/             # ফিচার টেস্ট স্যুট (167+ টেস্ট)
```

---

### ১.৫ টেস্টিং গাইডলাইন (Automated Testing)
কোডে কোনো পরিবর্তন আনার পর টেস্ট রান করে শতভাগ সুস্থতা নিশ্চিত করুন:
```bash
# সম্পূর্ণ টেস্ট স্যুট চালান
php vendor/phpunit/phpunit/phpunit

# নির্দিষ্ট মডিউল টেস্ট চালান
php vendor/phpunit/phpunit/phpunit tests/Feature/PatientSupportPublicPagesTest.php
php vendor/phpunit/phpunit/phpunit tests/Feature/PatientCaseExpirationTest.php
```

---

## ২. অ্যাডমিনিস্ট্রেটর নির্দেশিকা

### ২.১ অ্যাডমিন প্যানেলে লগইন ও নিরাপত্তা
- **অ্যাডমিন ইউআরএল:** `https://your-domain.com/admin` (অথবা লোকাল `http://localhost/admin`)
- **ডিফল্ট সুপার অ্যাডমিন:** `admin@cancercare.test` (পাসওয়ার্ড `.env` বা সিডারে নির্ধারিত)।
- **নিরাপত্তা সুপারিশ:** প্রোডাকশনে ওঠার সাথে সাথে পাসওয়ার্ড পরিবর্তন করুন এবং শক্তিশালী ক্রেডেনশিয়াল নিশ্চিত করুন।

---

### ২.২ ইউজার ও পারমিশন ম্যানেজমেন্ট (RBAC Roles)
1. **নতুন স্টাফ নিয়োগ:** `Admin Panel ➔ Users ➔ New User` থেকে নাম, ইমেইল ও পাসওয়ার্ড দিয়ে একাউন্ট খুলুন।
2. **রোল প্রদান:** কর্মীর দায়িত্ব অনুযায়ী সঠিক রোল সিলেক্ট করুন:
   - **`verification_officer`:** কাগজপত্র যাচাই ও ফিল্ড রিপোর্ট পর্যালোচনার জন্য।
   - **`support_agent`:** হেল্পলাইন রোগীর তথ্য ও বিকাশ অ্যাকাউন্ট এন্ট্রির জন্য।
   - **`field_agent`:** সরাসরি রোগীর বাড়ি গিয়ে সাক্ষাৎ ও সম্মতি সংগ্রহের জন্য।
   - **`medical_reviewer`:** ডাক্তার ও মেডিকেল গাইড পর্যালোচনার জন্য।

---

### ২.৩ ডাক্তার আবেদন অনুমোদন ওয়ার্কফ্লো
1. ওয়েবসাইটে `/for-doctors` ফর্ম থেকে কোনো চিকিৎসক আবেদন জমা দিলে তা `Doctor Applications` তালিকায় পেন্ডিং অবস্থায় জমা হয়।
2. **যাচাইকরণ:**
   - চিকিৎসকের নাম ও BMDC রেজিস্ট্রেশন নম্বর BMDC সরকারি ডাটাবেজে যাচাই করুন।
   - চিকিৎসকের ডিগ্রি ও বর্তমান কর্মস্থলের সত্যতা নিশ্চিত করুন।
3. **অনুমোদন:** `Approve` বাটনে ক্লিক করলে তা সরাসরি পাবলিশড ডাক্তার প্রোফাইলে যুক্ত হয় এবং পাবলিক ডিরেক্টরিতে লাইভ হয়ে যায়।

---

### ২.৪ রোগীর সহায়তা ও ৪-ধাপের ভেরিফিকেশন পরিচালনা
1. **নতুন কেস তৈরি:** `Patient Cases ➔ Create` থেকে তথ্য পূরণ করুন।
2. **কঠোর শিশু সুরক্ষা নিশ্চিতকরণ:** রোগীর বয়স ১৮-এর নিচে (`age < 18`) দিলে ছবি স্বয়ংক্রিয়ভাবে ব্লক থাকবে। এটি কখনো ম্যানুয়ালি এডিট করে ছবি দেখানো যাবে না।
3. **৪-ধাপের যাচাইকরণ:**
   - **হাসপাতাল পেপার:** বায়োপসি ও খরচের প্রাক্কলন নিশ্চিত করুন।
   - **ডাক্তার নিশ্চিতকরণ:** চিকিৎসকের প্রেসক্রিপশন ও প্রোটোকল নিশ্চিত করুন।
   - **আইডেন্টিটি:** রোগীর NID এবং বিকাশ/ব্যাংক অ্যাকাউন্টের নামের মিল নিশ্চিত করে `Name Verified` চেক দিন।
   - **ফিল্ড ভিজিট:** পরিবারের সাথে সরাসরি দেখা করে স্বাক্ষরিত লিখিত সম্মতিপত্র (`consent_form_path`) আপলোড করুন।
4. **কেস প্রকাশনা:** ৪টি শর্তই সম্পন্ন হলে তবেই **"প্রকাশ করুন" (Publish)** বাটন কার্যকর হবে। প্রকাশের পর কেসটি পরবর্তী ৩০ দিনের জন্য ওয়েবসাইটে লাইভ থাকবে।
5. **মেয়াদ বৃদ্ধি ("মেয়াদ বাড়াও"):** কেসের ৩০ দিন পার হওয়ার আগে রোগীর পরিবারের সাথে যোগাযোগ করে নতুন চিকিৎসার অগ্রগতি নোট (`note_bn`) লিখে কেসের মেয়াদ আরও **৩০ দিন বৃদ্ধি** করা যাবে।

---

### ২.৫ ক্যান্সার গাইড ও খরচের হারের সিএমএস
- **ক্যান্সার গাইড:** `Cancer Types` রিসোর্সে গিয়ে লক্ষণ, পরীক্ষা, চিকিৎসার ধাপ ও এফএকিউ বাংলায় সম্পাদনা করা যাবে।
- **খরচের বেইজ রেট:** `Cost Rates` রিসোর্স থেকে বিভিন্ন সার্জারি, কেমোথেরাপি ও রেডিওথেরাপির সরকারি ও বেসরকারি প্রাক্কলিত হারের প্যারামিটার পরিবর্তন করা যাবে।

---

### ২.৬ ক্যাশ ও অপ্টিমাইজেশন পরিচালনা
সাইট আপডেট বা সিডার চালানোর পর ক্যাশ ক্লিয়ার করতে:
- ব্রাউজার থেকে: `https://your-domain.com/clear-cache`
- টার্মিনাল থেকে: `php artisan optimize:clear`

---

## ৩. জরুরি ট্রাবলশুটিং ও কমান্ড চিটশিট

| সমস্যা | সম্ভাব্য কারণ | সমাধান কমান্ড |
| :--- | :--- | :--- |
| **নতুন ছবি বা ফাইল দেখা যাচ্ছে না** | স্টোরেজ সিমলিংক তৈরি নেই | `php artisan storage:link` |
| **পারমিশন বা রোলে এক্সেস ডিনায়েড** | পারমিশন ক্যাশ হয়ে আছে | `php artisan permission:cache-reset` |
| **ক্রন জব বা মেয়াদ স্বয়ংক্রিয়ভাবে আপডেট হচ্ছে না** | ক্রন শিডিউলার বন্ধ আছে | `crontab -e` চেক করুন এবং `php artisan schedule:run` টেস্ট করুন |
| **সিএসএস বা জাভাস্ক্রিপ্ট স্টাইল পাচ্ছে না** | Vite বিল্ড হয়নি | `npm run build` |
| **সম্পূর্ণ ক্যাশ রিসেট করতে চাইলে** | ভিউ/রুট/কনফিগ ক্যাশ জ্যাম | `php artisan optimize:clear` |

---
*Cancer Care Bangladesh — একটি উন্মুক্ত, স্বচ্ছ ও পেশাদার অলাভজনক স্বাস্থ্য উদ্যোগ।*
