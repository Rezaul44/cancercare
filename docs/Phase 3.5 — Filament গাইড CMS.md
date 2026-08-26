# Implementation Plan: Phase 3.5 — Filament গাইড CMS

Build the complete **GuideResource** in Filament for managing cancer guides, their child tables (terms, stages, steps, myths, faqs, videos), role-based approval workflows (`medical_reviewer` vs `super_admin`), and audit trails.

---

## User Review Required

> [!IMPORTANT]
> **Workflow & Access Control Summary:**
> - `content_editor`: Can view, create, and edit guide contents and all child records, but **cannot approve or publish**.
> - `medical_reviewer`: Has the `"চিকিৎসা তথ্য অনুমোদন"` action to verify and link a licensed doctor (`reviewed_by_doctor_id` & `reviewed_at`), transitioning the guide to `in_review`.
> - `super_admin`: Has full access and the `"প্রকাশ করুন"` action, which is **disabled/blocked unless `reviewed_at` and `reviewed_by_doctor_id` are set**.
> - `support_agent`: View-only access to published/in-progress guides.

---

## Proposed Changes

### Domain & Service Layer

#### [NEW] [app/Services/GuideService.php](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Services/GuideService.php)
- Implement domain operations:
  - `medicalApprove(Guide $guide, int $doctorId, ?string $note = null, ?User $causer = null): Guide`:
    - Assigns `reviewed_by_doctor_id` & `reviewed_at = now()`.
    - Updates status to `in_review` if in `draft`.
    - Logs Spatie activity `guide.medical_approved`.
  - `publish(Guide $guide, ?User $causer = null): Guide`:
    - Asserts doctor review exists (`reviewed_by_doctor_id` & `reviewed_at`).
    - Updates status to `published`, `published_at = now()`, `last_updated_at = now()`.
    - Updates linked `cancerType->update(['guide_published' => true])`.
    - Logs Spatie activity `guide.published`.
  - `unpublish(Guide $guide, ?User $causer = null): Guide`:
    - Reverts status to `draft`.
    - Updates `cancerType->update(['guide_published' => false])`.
    - Logs Spatie activity `guide.unpublished`.

---

### Filament Admin CMS

#### [NEW] [app/Filament/Resources/GuideResource.php](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/app/Filament/Resources/GuideResource.php)
- Model: `Guide::class`
- Icon: `heroicon-o-book-open`, Navigation Group: `কন্টেন্ট ম্যানেজমেন্ট`, Label: `ক্যান্সার গাইড`.
- Authorization:
  - `canViewAny()` / `canView()`: `guides.view` or `guides.manage`
  - `canCreate()` / `canEdit()` / `canDelete()`: `guides.manage`
- Table:
  - Columns: `cancerType.name_bn`, `title_bn`, `reviewedByDoctor.name_bn`, `reviewed_at`, `status` (colored badge: `খসড়া` [gray], `পর্যালোচনায়` [amber], `প্রকাশিত` [green]), `read_minutes`, `updated_at`.
  - Filters: `status` filter, `cancer_type_id` filter.
  - Table Actions:
    - `medicalApprove` action (modal with doctor select & note).
    - `publish` action (disabled if unreviewed).
    - `unpublish` action.
    - Edit / View / Delete actions.
- Form:
  - `cancer_type_id` (Select, required, unique per guide)
  - `title_bn` (TextInput, required)
  - `intro_bn` (Textarea, required)
  - `meta_title`, `meta_description`
  - `sources_note_bn`
  - `read_minutes`
  - Status and Review badges / info sections.

#### [NEW] Relation Managers in `app/Filament/Resources/GuideResource/RelationManagers/`
1. **`TermsRelationManager.php`**: Manage `GuideTerm` (code, slug, hint_bn, plain_explanation_bn, why_matters_bn, search_keywords, sort_order).
2. **`StagesRelationManager.php`**: Manage `GuideStage` (stage, title_bn, description_bn, typical_treatment_bn, duration_bn, cost_min, cost_max, severity_color, sort_order).
3. **`StepsRelationManager.php`**: Manage `GuideStep` (step_no, title_bn, description_bn, when_label_bn, urgency, items tags, sort_order).
4. **`MythsRelationManager.php`**: Manage `GuideMyth` (myth_bn, truth_bn, sort_order).
5. **`FaqsRelationManager.php`**: Manage `GuideFaq` (question_bn, answer_bn, sort_order).
6. **`VideosRelationManager.php`**: Manage `GuideVideo` (title_bn, video_url, platform, doctor_id, duration_seconds, sort_order).

#### [NEW] Pages in `app/Filament/Resources/GuideResource/Pages/`
- **`ListGuides.php`**: Listing with header actions and status indicators.
- **`CreateGuide.php`**: Creation page.
- **`EditGuide.php`**: Edit page with top-bar actions (`medicalApprove`, `publish`, `delete`) and relation manager tabs.
- **`ViewGuide.php`**: Read-only view page.

---

## Verification Plan

### Automated Feature Tests
- **[NEW] [`tests/Feature/GuideResourceTest.php`](file:///d:/xampp-windows-x64-8.2.4-0/htdocs/cancercare/tests/Feature/GuideResourceTest.php)**:
  - Super admin and content editor can access list, create, and edit pages.
  - Content editor **cannot** see or execute `publish` or `medicalApprove` actions.
  - Medical reviewer can execute `medicalApprove` action and set `reviewed_by_doctor_id` + `reviewed_at`.
  - Super admin **cannot publish** if `reviewed_at` / `reviewed_by_doctor_id` is null (disabled / domain exception).
  - Super admin **can publish** once reviewed, which sets `status = published`, `cancerType.guide_published = true`, and logs activity.
  - Relation managers (`terms`, `stages`, `steps`, `myths`, `faqs`, `videos`) allow full CRUD under `GuideResource`.
  - Unauthorized roles (e.g. `field_agent`, `finance`, unauthenticated) are blocked with 403 Forbidden.

### Commands to Run:
```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/GuideResourceTest.php
php vendor/phpunit/phpunit/phpunit
```
