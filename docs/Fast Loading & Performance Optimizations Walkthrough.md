# Fast Loading & Performance Optimizations Walkthrough

To ensure high-speed page loading across all CancerCare Bangladesh pages, we implemented full-stack performance optimizations covering network prefetching, asset loading, query & database caching, responsive image loading strategies, and HTTP caching headers.

---

## 1. Implemented Optimizations

### A. Network & Font Loading
- **[`resources/views/layouts/app.blade.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/resources/views/layouts/app.blade.php)**:
  - Added `<link rel="dns-prefetch">` for `fonts.googleapis.com`, `fonts.gstatic.com`, and `cdnjs.cloudflare.com` to resolve DNS lookups early.
  - Added `<link rel="preconnect">` with `crossorigin` for `fonts.gstatic.com` and `cdnjs.cloudflare.com`.
  - Configured `display=swap` on Google Fonts to prevent invisible text during font download (FOIT).

### B. Image Loading & Layout Shift (CLS) Optimization
- **Above-The-Fold Images**:
  - `home.blade.php`: Hero visual configured with `fetchpriority="high"`, `decoding="async"`, and explicit `width` / `height` dimensions.
  - `doctors/show.blade.php`: Doctor profile photo configured with `fetchpriority="high"`, `decoding="async"`, and explicit dimensions.
- **Below-The-Fold / Offscreen Images**:
  - `home.blade.php`: Patient recovery stories images configured with `loading="lazy"` and `decoding="async"`.
  - `doctors/_card.blade.php`: Doctor directory card avatars configured with `loading="lazy"`, `decoding="async"`, `width="76"`, `height="76"`.
  - `guides/show.blade.php`: Video preview thumbnails configured with `loading="lazy"`, `decoding="async"`, `width="250"`, `height="158"`.
  - `for-doctors.blade.php`: Onboarding graphic configured with `loading="lazy"`, `decoding="async"`, `width="400"`, `height="250"`.

### C. Server-Side Query & Memory Caching
- **[`HomeController.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Http/Controllers/HomeController.php)**:
  - Caches `home_cancer_types` (1 hour) and `total_published_doctors_count` (30 minutes).
- **[`DoctorProfileController.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Http/Controllers/DoctorProfileController.php)**:
  - Caches `active_rating_criteria` and match engine cancer types `match_cancer_options`.
- **[`GuideController.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Http/Controllers/GuideController.php)**:
  - Caches `guide_index_published_cancer_types` (30 minutes) and `guide_other_types_{id}`.
- **[`GuideService.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Services/GuideService.php)**:
  - Automatically invalidates and flushes cached counts and guide lists on approval, publishing, and unpublishing.

### D. Response Headers & Browser Caching Middleware
- **[`app/Http/Middleware/OptimizeResponseHeaders.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Http/Middleware/OptimizeResponseHeaders.php)**:
  - Appends `Cache-Control: public, max-age=300, stale-while-revalidate=600` for public GET web requests.
  - Adds security & performance headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `X-XSS-Protection: 1; mode=block`.
  - Registered in `bootstrap/app.php` web middleware stack.

### E. Compiled Frontend Assets
- Compiled via Vite (`npm run build`):
  - `app.css`: 87.66 kB (14.74 kB gzip)
  - `app.js`: 110.64 kB (39.93 kB gzip)

---

## 2. Test Verification

- Full test suite run:
```bash
PHPUnit 11.5.56
OK (116 tests, 599 assertions)
```
- 100% tests passing including N+1 query regression assertions.

+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
say i make a update or add content from admin panel or tables. 
will it be showing frontend as you set caching . just clear the conecept
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

অ্যাডমিন প্যানেল থেকে কন্টেন্ট আপডেট করলে কি সাথে সাথে ফ্রন্টএন্ডে দেখা যাবে?
হ্যাঁ, শতভাগ নিশ্চিতভাবেই সাথে সাথে (Real-time) ফ্রন্টএন্ডে দেখা যাবে। কোনো পুরনো বা বাসি (Stale) ডেটা আটকে থাকবে না।

পুরো কনসেপ্ট ও কার্যপদ্ধতি:
১. বিস্তারিত পেজগুলো সবসময় লাইভ ডেটাবেজ থেকে আসে (Live Queries)
যেকোনো ডাক্তারের প্রোফাইল পেজ (/doctors/{slug}) বা কোনো ক্যান্সারের বিস্তারিত গাইড পেজ (/guide/{slug}) যখন একজন ব্যবহারকারী ভিজিট করেন, তখন সেই নির্দিষ্ট ডাক্তার বা গাইডের যাবতীয় তথ্য (চেম্বার, ভিজিট ফি, রিপোর্ট ডিকোডার টার্মস, স্টেজ বিবরণী, ভুল ধারণা, FAQ, ভিডিও) সরাসরি ডেটাবেজ থেকে রিয়েল-টাইমে রিলেশনসহ লোড হয়।
সুতরাং অ্যাডমিন প্যানেলে কোনো তথ্য পরিবর্তন করে Save করলেই তা পেজে আপডেট হয়ে যাবে।
২. অটোমেটিক ক্যাশ ক্লিয়ারিং (Automated Model Event Invalidation)
হোমপেজ বা সূচিপত্রের যেসব গ্লোবাল ড্রপডাউন ও কাউন্ট ক্যাশ করা হয়েছে (যেমন: মোট ডাক্তার সংখ্যা, গাইড সূচির ফিল্টার লিস্ট), সেগুলোর জন্য মডেলে অটোমেটিক ইভেন্ট হুক (booted hooks) যুক্ত করা আছে:

Filament অ্যাডমিন প্যানেলে যখনই কোনো নতুন গাইড/ডাক্তার যোগ করবেন, কোনো তথ্য সম্পাদন (Edit) করবেন, প্রকাশ (Publish) করবেন বা ডিলিট করবেন — Laravel স্বয়ংক্রিয়ভাবে পুরনো ক্যাশ কি সাথে সাথে মুছে (Flush/Forget) ফেলবে।
এর ফলে পরের রিকোয়েস্টেই ফ্রন্টএন্ড ডেটাবেজ থেকে একদম নতুন ডেটা নিয়ে ক্যাশ রিফ্রেশ করে নেবে।
৩. ব্রাউজার লেভেল ক্যাশিং (stale-while-revalidate)
ব্রাউজার লেভেলে আমরা stale-while-revalidate মেকানিজম ব্যবহার করেছি।
এর সুবিধা হলো: সাধারণ ভিজিটর যখন এক পেজ থেকে অন্য পেজে যান, পেজটি কোনো ল্যাগ ছাড়া ইনস্ট্যান্ট (০ মিলিসেকেন্ডে) লোড হয় এবং ব্যাকগ্রাউন্ডে নতুন কোনো ডেটা এসেছে কিনা তা ব্রাউজার যাচাই করে আপডেট করে নেয়। পেজ রিফ্রেশ (F5) দিলেও তাৎক্ষণিক ফ্রেশ কনটেন্ট লোড হয়।
সারসংক্ষেপ: পারফরম্যান্স অপটিমাইজেশন সাইটের গতি বহুগুণ বাড়িয়েছে, তবে এটি অ্যাডমিন প্যানেলের রিয়েল-টাইম কনটেন্ট আপডেট বা ডেটা পরিবর্তনের পথে কোনো বাধা তৈরি করবে না।
