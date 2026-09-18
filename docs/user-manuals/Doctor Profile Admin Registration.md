# Doctor Profile Admin Registration & YouTube Video Support Walkthrough

## Summary of Completed Work

We have implemented a complete admin management system for doctor profiles, mirroring the comprehensive capabilities of `HospitalResource`. Admins can now register, verify, configure, and publish every single section of a doctor profile directly from Filament, with rich support for direct and embedded YouTube links.

---

### 1. YouTube Helper & Media Integration

- **[`app/Support/YouTubeHelper.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Support/YouTubeHelper.php)**:
  - Supports all common YouTube formats:
    - Standard watch URL: `https://www.youtube.com/watch?v=VIDEO_ID`
    - Shortened URL: `https://youtu.be/VIDEO_ID`
    - Embed URL: `https://www.youtube.com/embed/VIDEO_ID`
    - YouTube Shorts: `https://www.youtube.com/shorts/VIDEO_ID`
    - Raw `<iframe>` embed codes: `<iframe ... src="https://www.youtube.com/embed/VIDEO_ID" ...></iframe>`
  - Utility methods:
    - `extractVideoId(string $urlOrIframe): ?string`
    - `toWatchUrl(?string $videoId): ?string`
    - `toEmbedUrl(?string $videoId): ?string`
    - `getThumbnailUrl(?string $videoId, string $quality = 'hqdefault'): ?string`
    - `cleanUrl(?string $input): ?string` (strips HTML iframe tags and normalizes to standard watch URL)

- **Model Accessors & Fallback Thumbnails**:
  - [`app/Models/DoctorVideo.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Models/DoctorVideo.php):
    - Added `watch_url` attribute
    - Added `embed_url` attribute
    - Fallback `thumbnail_url` attribute (automatically fetches YouTube `hqdefault` image when no custom thumbnail is uploaded)
  - [`app/Models/DoctorPatientTestimonial.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Models/DoctorPatientTestimonial.php):
    - Added `watch_url`, `embed_url`, and auto-fallback `thumbnail_url` attributes

- **Blade Profile Page Refactoring**:
  - [`resources/views/pages/doctors/show.blade.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/resources/views/pages/doctors/show.blade.php):
    - Intro video modal and link now use `$introVideo->watch_url` and fallback to `$introVideo->thumbnail_url`.
    - Educational video cards and links use `$video->watch_url` and `$video->thumbnail_url`.
    - Patient video testimonials use `$testimonial->watch_url` and `$testimonial->thumbnail_url`.

---

### 2. Filament Doctor Resource & Pages

- **[`app/Filament/Resources/DoctorResource.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource.php)**:
  - Form organized into 3 clear cards with clean Bengali labels:
    1. **মৌলিক তথ্য ও পরিচিতি:**
       - নাম (বাংলা ও ইংরেজি), SEO স্লাগ, BMDC রেজিস্ট্রেশন নম্বর
       - ডিগ্রিসমূহ (বাংলা), বর্তমান পদবি ও কর্মস্থল (বাংলা)
       - লিঙ্গ (পুরুষ / নারী / অন্যান্য), অভিজ্ঞতার বছর, চিকিৎসা করা মোট রোগীর সংখ্যা
       - অনকোলজিস্টের ধরন / স্পেশালিটি (Checkbox list relation)
       - ডাক্তারের ছবি আপলোড (Public disk)
    2. **পরামর্শ সেবা ও ফি:**
       - দ্বিতীয় মতামত সেবা (Second opinion) টগল ও ফি (৳)
       - জরুরি WhatsApp পরামর্শ টগল, ফি (৳) ও রেসপন্স টাইম (ঘণ্টা)
       - চিকিৎসা দর্শনের সংক্ষিপ্ত ভূমিকা (Intro)
    3. **যাচাই ও প্রকাশনা:**
       - প্রোফাইল স্ট্যাটাস (Draft / Published / Archived)
       - ডাক্তার অনুমোদন তারিখ (`doctor_approved_at`)
       - BMDC ভেরিফিকেশন তারিখ ও কর্মকর্তা
       - প্রকাশনার তারিখ (`published_at`)
       - সর্বশেষ পর্যালোচনা তারিখ (`last_verified_at`)
  - **Table View**:
    - Displays doctor photo, name, BMDC number, specialties badges, status badge, chamber count badge, and creation date.
    - Actions: Edit, View, and "পাবলিক প্রোফাইল দেখুন" direct link.
  - **Pages**:
    - `ListDoctors.php`
    - `CreateDoctor.php` (automatically redirects to `EditDoctor` upon creation so Relation Managers can be immediately populated)
    - `EditDoctor.php` (includes direct header button to view the public profile at `/doctors/{slug}`)
    - `ViewDoctor.php`

---

### 3. Relation Managers for Every Profile Section

Each section of the doctor profile can now be entered, reviewed, and updated via dedicated RelationManagers:

1. **[`ChambersRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/ChambersRelationManager.php)**:
   - Hospital selection (optional), chamber name, district, address, phone number, consultation fee (new / old), available days (CheckboxList), visiting hours, average wait time, next available date note, active toggle.
2. **[`VideosRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/VideosRelationManager.php)**:
   - Video category (Doctor Intro / Educational), title, **YouTube direct/embed link input** (automatically sanitized with `YouTubeHelper::cleanUrl`), optional custom thumbnail upload, duration in minutes, total views, and production badge.
3. **[`PatientTestimonialsRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/PatientTestimonialsRelationManager.php)**:
   - Patient / relative name, cancer stage, treatment outcome, **YouTube video link** (direct/embed), anonymous display checkbox, and thumbnail color theme.
4. **[`ServicesRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/ServicesRelationManager.php)**:
   - Cancer type association, service title, detailed description, icon picker (Tabler icons), badges, and badge color theme.
5. **[`TimelineRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/TimelineRelationManager.php)**:
   - Career timeline: Year/period, designation/degree, institution or hospital name, sort order.
6. **[`PatientStoriesRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/PatientStoriesRelationManager.php)**:
   - Written inspirational stories: patient quote, condition before vs. after treatment, treatment duration, and privacy toggles.
7. **[`PhilosophyPointsRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/PhilosophyPointsRelationManager.php)**:
   - Core treatment philosophy bullets with icon picker and descriptions.
8. **[`CancerTypesRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/CancerTypesRelationManager.php)**:
   - Attach / detach cancer specializations with `is_primary` flag.
9. **[`HospitalsRelationManager.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/DoctorResource/RelationManagers/HospitalsRelationManager.php)**:
   - Attach / detach affiliated hospitals with doctor's schedule note.

---

### 4. Documentation Updates

- **[`public/docs/module-2-doctors.html`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/public/docs/module-2-doctors.html)**:
  - Added Section 4 detailing the complete admin doctor registration process, all 9 relation managers, and YouTube link formatting.
- **[`public/docs/admin-practical-guide.html`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/public/docs/admin-practical-guide.html)**:
  - Updated Section 2 to explain how admins create doctors, fill each sub-section, handle YouTube links, and preview the resulting public page at `/doctors/{slug}`.
