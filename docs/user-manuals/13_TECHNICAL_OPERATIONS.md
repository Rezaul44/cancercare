# ১৩. টেকনিক্যাল অপারেশনস (ডেভেলপারদের জন্য)

এই ফাইলটা স্টাফদের জন্য নয় — যাঁরা সার্ভার সেটআপ, ডিপ্লয়মেন্ট বা কোড নিয়ে কাজ করেন তাঁদের জন্য।

## সিস্টেম রিকোয়ারমেন্টস

- **PHP:** `^8.2`
- **Laravel:** `^11.31`, **Filament:** `^3.0`
- **Database:** MySQL 8.0+ (`utf8mb4_unicode_ci` কোলেশন বাধ্যতামূলক — বাংলা টেক্সটের জন্য), FULLTEXT সার্চে **ngram parser** লাগে (ডিফল্ট MySQL parser বাংলায় কাজ করে না)
- **Queue/Cache:** Redis (Laravel Horizon দিয়ে কিউ ম্যানেজ হয়)
- **Storage:** `s3_public` (সাধারণ ছবি) ও `s3_private` (সংবেদনশীল ডকুমেন্ট — NID, BMDC সনদ, সম্মতিপত্র, প্রেসক্রিপশন) — dev-এ এই দুটোই লোকাল ডিস্কে ম্যাপ করা, প্রোডাকশনে আসল S3/Spaces-এ বদলাতে হবে

## স্থানীয় সেটআপ

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev   # অথবা প্রোডাকশনের জন্য npm run build
php artisan serve
```

## প্রোডাকশন — Cron ও কিউ ওয়ার্কার

```bash
# crontab -e
* * * * * cd /path-to-cancercare && php artisan schedule:run >> /dev/null 2>&1
```
কী কী স্বয়ংক্রিয়ভাবে চলবে তার সম্পূর্ণ তালিকা [12_SYSTEM_JOBS.md](12_SYSTEM_JOBS.md)-এ।

Horizon (কিউ ওয়ার্কার) Supervisor দিয়ে সবসময় চালু রাখতে হবে:
```ini
[program:cancercare-worker]
command=php /path-to-cancercare/artisan horizon
autostart=true
autorestart=true
user=www-data
```

## কোডবেস কাঠামো

```
app/
├── Enums/                 # স্ট্যাটাস, ধরন ইত্যাদি
├── Filament/Resources/    # অ্যাডমিন প্যানেলের প্রতিটি মডিউল
├── Filament/Widgets/      # ড্যাশবোর্ডের উইজেট (রেটিং অগ্রগতি, হেল্পলাইন পরিসংখ্যান)
├── Http/Controllers/      # পাবলিক পাতা, ডাক্তার পোর্টাল (Doctor/), মাঠকর্মী পোর্টাল (Field/)
├── Jobs/                  # রাতের স্বয়ংক্রিয় কাজ
├── Models/
├── Observers/             # PatientCaseObserver, DoctorObserver — নীতি কোডে জোর করে প্রয়োগ
├── Services/              # ব্যবসায়িক লজিক (Ranking, Verification, Payment/, CostEstimator, Search...)
└── Http/Middleware/       # role/permission মিডলওয়্যার, EnsureDoctorProfile
routes/
├── web.php                # পাবলিক + /doctor + /field রুট
└── console.php            # ক্রন শিডিউল
tests/Feature/             # ১৮০+ ফিচার টেস্ট
```

## টেস্ট চালানো

```bash
php artisan test                                    # পুরো স্যুট
php artisan test --filter=SearchTest                # নির্দিষ্ট ফাইল
```

## ট্রাবলশুটিং চিটশিট

| সমস্যা | সমাধান |
|---|---|
| নতুন ছবি/ফাইল দেখা যাচ্ছে না | `php artisan storage:link` |
| রোল/পারমিশন বদলের পরও অ্যাক্সেস আগের মতো | `php artisan permission:cache-reset` |
| মেয়াদ/রেটিং স্বয়ংক্রিয়ভাবে আপডেট হচ্ছে না | crontab-এ `schedule:run` ঠিকমতো চলছে কিনা দেখুন |
| CSS/JS পুরনো দেখাচ্ছে | `npm run build` |
| সব ধরনের ক্যাশ সমস্যা | `php artisan optimize:clear` |
| স্টাফ ইউজার/রোল অ্যাডমিন প্যানেলে যোগ করতে হবে | কোনো UI নেই — `php artisan tinker` দিয়ে ম্যানুয়ালি (দেখুন [02_ROLES_AND_PERMISSIONS.md](02_ROLES_AND_PERMISSIONS.md)) |
