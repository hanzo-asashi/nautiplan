## [v0.18.2] - 2026-07-05

### 🐛 Bug Fixes
- fix: remove invalid :global() pseudo-class from app.css to resolve lightningcss build warnings (6676a03)

### 🔧 Maintenance
- chore: update changelog for v0.18.1 [skip ci] (b2b7404)

[v0.18.2]: https://github.com/hanzo-asashi/nautiplan/compare/v0.18.1...v0.18.2

## [v0.18.1] - 2026-07-04

### 🐛 Bug Fixes
- fix: restore GitHub Release creation and add version to CHANGELOG entries (89e6ec2)
- fix: use import statement for LengthAwarePaginator in GetActivityRevisionsAction (69a49fe)
- fix: report helper types and configure changelog generation on push to main (8fa3e04)

### 📝 Documentation
- docs: backfill CHANGELOG.md with all historical releases (v0.2.0 - v0.18.0) [skip ci] (08ccd12)

### 🔧 Maintenance
- chore: release v0.18.1 (9fc6da3)
- chore: update changelog [skip ci] (85ad056)

### 📦 Other
- Merge pull request #26 from hanzo-asashi/refactor/budget-action-pattern (b773575)

[v0.18.1]: https://github.com/hanzo-asashi/nautiplan/compare/v0.18.0...v0.18.1

﻿# Changelog

Seluruh perubahan penting pada proyek ini didokumentasikan dalam file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
dan proyek ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.18.0] - 2026-07-04

### 🚀 Features
- feat: implement Filterable trait and ReportHelper (37c2428)
- feat: migrate budget revision from modal to full-page form (7733b4b)

### 🐛 Bug Fixes
- fix(phpstan): add generic type for Scope interface (a7dacfc)
- fix(security): implement unit isolation and authorization rules (dd360e5)

### 📝 Documentation
- docs: fix missing item 10 in TOC (a0ad9c8)
- docs: tambahkan seluruh fitur core yang sudah dibangun ke dalam USER_GUIDE (e9a1de8)
- docs: hapus referensi perubahan UI dari panduan pengguna (68eb569)
- docs: update user guide with new security and UI features (a02c666)

### 📦 Other
- Merge pull request #25 from hanzo-asashi/fix/tenant-isolation-idor (0f12c80)
- Merge pull request #24 from hanzo-asashi/feature/budget-edit-fullpage (4b30ccf)
- Merge pull request #23 from hanzo-asashi/feature/deep-gaps-hardening (0d0eddc)

---

## [v0.17.0] - 2026-07-04

### 🚀 Features
- feat: deep gaps hardening - fiscal year lock, date validations, and audit trails (e0660d7)

### 🐛 Bug Fixes
- fix: resolve PHPStan date formatting warning in Dashboard and Report controllers (922e08b)

### 📦 Other
- Merge pull request #22 from hanzo-asashi/feature/pok-revisions-hardening (a6c36ec)

---

## [v0.16.0] - 2026-07-04

### 🚀 Features
- feat: POK revisions hardening, re-approval workflow, deletion guard, and tax helper (f487118)

### 📝 Documentation
- docs: update USER_GUIDE.md with all new features and enhancements (4cfcdcf)
- docs: update ROADMAP.md to mark extension phase 4 as complete (709c452)

### 📦 Other
- Merge pull request #21 from hanzo-asashi/feature/extension-phase-4 (dd27f31)

---

## [v0.15.0] - 2026-07-04

### 🚀 Features
- feat: implement item-level budgeting, pok revision tracking, and early warning system (dfb767d)
- feat: implement full-page realization form, collapsible realizations accordion, clean print dropdown layout, and fix POK monitoring accessibility warnings (6c4aed0)

### 🐛 Bug Fixes
- fix: resolve year select matching issue by matching numeric option value (40f1b7b)

### 🎨 UI/UX
- style: change PokMonitoring layout to full width (24b4758)
- style: abbreviate rupiah formats in detail pages for consistency (f2ebe6a)
- style: abbreviate rupiah formats in POK monitoring table to match other pages (5275f30)

### 📝 Documentation
- docs: update ROADMAP.md to mark extension phase 3 as complete and add extension phase 4 gaps (dfc8b12)

### 📦 Other
- Merge pull request #20 from hanzo-asashi/feat/abbreviate-rupiah-format (2685bdd)
- Merge pull request #19 from hanzo-asashi/feat/rekap-dipa-reports (e8e80aa)

---

## [v0.14.0] - 2026-07-03

### 🚀 Features
- feat: implement PDF rekapitulasi DIPA per Output and Komponen (a09d080)

### 📦 Other
- Merge pull request #18 from hanzo-asashi/feat/pok-monev-reports (5b67015)

---

## [v0.13.0] - 2026-07-03

### 🚀 Features
- feat: add POK monitoring page with tree view and Excel export (4e0d81d)

### 📦 Other
- Merge pull request #17 from hanzo-asashi/feat/pencairan-spp-spm (e96b840)

---

## [v0.12.0] - 2026-07-03

### 🚀 Features
- feat: implement pencairan SPP, SPM, SPTJB, and SSP tax document generation (Phase 2) (df2feb9)

### 🎨 UI/UX
- style: format svelte tax input fields with prettier (b147d80)

### 📦 Other
- Merge pull request #16 from hanzo-asashi/feat/pok-hierarchy-items (fc8c07b)

---

## [v0.11.0] - 2026-07-03

### 🚀 Features
- feat: implement POK hierarchy tables, models, and excel reader data seeder (5ca3bb0)
- feat: change PDF responses from download to stream for inline browser previewing (1b5a7ed)

### 🐛 Bug Fixes
- fix: resolve phpstan generics and string cast errors (2048aca)
- fix: position print documents dropdown upwards to prevent vertical clipping (631ab97)

### 📦 Other
- Merge pull request #15 from hanzo-asashi/feat/normalized-ppk-reports (19cdaa6)

---

## [v0.10.0] - 2026-07-03

### 🚀 Features
- feat: implement database normalization 3NF and full procurement PDF reports (SP, SPK, BAST, BAP, Kwitansi) (8c578fc)

### 🐛 Bug Fixes
- fix: make nested procurement references null-safe in all PDF templates (443bb13)

### 🎨 UI/UX
- style: polish kwitansi PDF design and fix terbilang word spacing & spelling (72fb31f)
- style: fix closed dialog displaying as white box in Audit Logs page (03a325f)

### 📦 Other
- Merge pull request #14 from hanzo-asashi/fix/audit-logs-layout (1c19795)
- Merge pull request #13 from hanzo-asashi/feature/sim-ppk (3f239b2)

---

## [v0.9.0] - 2026-07-02

### 🚀 Features
- feat: implement official APBN headers, account fields, and vendor realization types (SIM-PPK) (2cc0ac9)
- feat: implement Phase 5 Advanced Features (Kanban, Calendar, SSE Notifications, Document Versioning, and Audit Trail) (a69b413)

### 🎨 UI/UX
- style: apply compact abbreviation format for rupiah values in tables across index views (c317bf6)
- style: abbreviate large rupiah figures in dashboard and budget summaries for readability (d9a693c)
- style: add color legend for calendar events (dbecbd8)
- style: tidy up calendar day indicators using clean colored dots (2968f28)
- style: apply global compact scrollbar styling via Tailwind v4 scrollbar utilities (fc207a6)
- style: change sidebar menu font size to text-sm (1e0ac5e)
- style: fix padding lint rules in AppSidebar.svelte (7011be9)
- style: group and organize sidebar menu items into distinct sections (e62740b)
- style: apply prettier formatting to Calendar.svelte (1b3d653)
- style: enhance accessibility (a11y) with ARIA attributes and labels in Svelte components (0a05d8d)
- style: fix action buttons alignment and prevent text wrapping in page header (1603222)

### 📝 Documentation
- docs: add user guide for Phase 5 features and update README (5da2a44)

### 📦 Other
- Merge pull request #12 from hanzo-asashi/feature/security-hardening (4a46ccf)
- security: implement authorization checks and rate-limiting to activities and notifications (b50793d)
- Merge pull request #11 from hanzo-asashi/feature/accessibility-audit (8569703)
- Merge pull request #10 from hanzo-asashi/feature/user-documentation (c37adc4)
- Merge pull request #9 from hanzo-asashi/feature/performance-optimization (1025932)
- perf: optimize database queries, eager loading and add indexing to notifications (4e1bfd9)
- Merge pull request #8 from hanzo-asashi/feature/advanced-features (baefd16)

---

## [v0.8.0] - 2026-07-01

### 🚀 Features
- feat: implement Phase 5 Advanced Features (Kanban, Calendar, SSE Notifications, Document Versioning, and Audit Trail) (468a389)

### 📦 Other
- Merge pull request #7 from hanzo-asashi/feature/reporting-export (61ca1c7)

---

## [v0.7.0] - 2026-07-01

### 🚀 Features
- feat: adjust Pagu chart color to primary theme and keep Realisasi yellow (6e6ad4a)

### 📝 Documentation
- docs: mark Phase 4 as complete in ROADMAP.md (8ba1e5a)

### 📦 Other
- Merge pull request #6 from hanzo-asashi/feature/approval-workflow (fbedde8)

---

## [v0.6.0] - 2026-07-01

### 🚀 Features
- feat: implement multi-step approval workflow (Phase 3) (04c4a79)

### 📦 Other
- Merge pull request #5 from hanzo-asashi/feature/activity-reports (ec29787)

---

## [v0.5.0] - 2026-07-01

### 🚀 Features
- feat: quarterly reporting and monitoring & evaluation (M&E) workflow (997854b)

### 📦 Other
- Merge pull request #4 from hanzo-asashi/feature/ci-optimization (cf39f32)

---

## [v0.4.1] - 2026-07-01

### 🔧 Maintenance
- ci: optimize workflow speeds with caching, parallel jobs, and disabling xdebug (1ffc226)

### 📦 Other
- Merge pull request #3 from hanzo-asashi/feature/kpi-dashboard (4a8609d)

---

## [v0.4.0] - 2026-07-01

### 🚀 Features
- feat: progress tracking dashboard and kpi achievement visualization (5f6663c)

### 📦 Other
- Merge pull request #2 from hanzo-asashi/feature/activity-indicators (263556b)

---

## [v0.3.0] - 2026-07-01

### 🚀 Features
- feat: inline performance indicator management and read-only show page (ec9a0e0)

### 📦 Other
- Merge pull request #1 from hanzo-asashi/theme/nova-zinc (2827304)

---

## [v0.2.1] - 2026-07-01

### 🐛 Bug Fixes
- fix: cast route actions to any to bypass svelte-check form property limitations on CI (c5ff236)
- fix: force ESLint to treat alias imports as internal to prevent CI resolution issues (9d0b736)
- fix: enforce type-first import ordering globally to resolve GitHub Actions CI errors (688483f)
- fix: enforce unified type import sorting rules and resolve ESLint conflicts (9c81421)
- fix: suppress false-positive svelte-check warnings and configure onwarn for build (b23fa30)

### 🎨 UI/UX
- style: polish brand logo, unify card backgrounds and standardise tables/inputs (bd9eb32)

### 📝 Documentation
- docs: add MIT License and update README license section (fea921f)
- docs: add comprehensive README with badges, features, and installation guide (68f47a2)

### 🔧 Maintenance
- ci: run database migrations before generating Wayfinder files to provide correct type definitions (389aa0e)
- ci: generate Wayfinder actions and routes during lint job to resolve Svelte typecheck errors (ca8fe0a)
- ci: add unified CI workflow, auto-release changelog, and update README badges (dbac7d9)

---

## [v0.2.0] - 2026-06-30

### 🚀 Features
- feat: polish landing page with neonmorphic cards, mesh gradient background, and drifting glows (ff04cfe)

### 📦 Other
- Configure Boost post-update script (1d215ba)
- Install Laravel Boost (3f8419e)
- Install Pest (511a92b)
- Set up a fresh Laravel app (88fc435)

---


[v0.18.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.17.0...v0.18.0
[v0.17.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.16.0...v0.17.0
[v0.16.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.15.0...v0.16.0
[v0.15.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.14.0...v0.15.0
[v0.14.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.13.0...v0.14.0
[v0.13.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.12.0...v0.13.0
[v0.12.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.11.0...v0.12.0
[v0.11.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.10.0...v0.11.0
[v0.10.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.9.0...v0.10.0
[v0.9.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.8.0...v0.9.0
[v0.8.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.7.0...v0.8.0
[v0.7.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.6.0...v0.7.0
[v0.6.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.5.0...v0.6.0
[v0.5.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.4.1...v0.5.0
[v0.4.1]: https://github.com/hanzo-asashi/nautiplan/compare/v0.4.0...v0.4.1
[v0.4.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.3.0...v0.4.0
[v0.3.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.2.1...v0.3.0
[v0.2.1]: https://github.com/hanzo-asashi/nautiplan/compare/v0.2.0...v0.2.1
[v0.2.0]: https://github.com/hanzo-asashi/nautiplan/releases/tag/v0.2.0
