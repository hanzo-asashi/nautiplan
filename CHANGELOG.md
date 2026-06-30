# Changelog

Seluruh perubahan penting pada proyek ini didokumentasikan dalam file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
dan proyek ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v0.2.0] - 2026-06-30

### 🚀 Features

- **Manajemen Unit Kerja** — CRUD unit kerja dengan struktur hierarkis parent-child
- **Manajemen Pengguna** — CRUD pengguna dengan RBAC multi-role dan filter unit/role
- **Tahun Anggaran** — Kelola periode fiskal dengan status aktif/arsip
- **Renstra** — Rencana Strategis jangka menengah dengan indikator capaian
- **Renja** — Rencana Kerja Tahunan terhubung ke Renstra
- **Program & Kegiatan** — CRUD program, kegiatan, dan sub-kegiatan
- **Anggaran & Realisasi** — Tracking pagu anggaran dan realisasi belanja real-time
- **Audit Log** — Jejak audit lengkap (create/update/delete) untuk akuntabilitas
- **Dashboard** — Ringkasan statistik real-time dengan chart interaktif (bar & donut)
- **Autentikasi** — Laravel Fortify (2FA/TOTP, Passkeys/WebAuthn, Email Verification)
- **Role-Based Access Control** — Middleware `CheckRole` dan multi-role assignment

### 🎨 UI/UX

- Neonmorphic card design dengan border glow dan dual-layered shadow
- Dynamic mesh gradient background dengan drifting ambient glows
- Premium gradient text pada section header "Modul Unggulan"
- Neon badge berdenyut dan modern divider dekoratif
- Animasi hover interaktif pada widget statistik
- Landing page responsif dengan dark mode support

### 🧪 Tests

- 43 Pest test cases (183 assertions) — autentikasi, dashboard, profil, audit log

### 🔧 Maintenance

- GitHub Actions CI workflow (lint, static analysis, test matrix)
- Automatic changelog generation pada tag release
- Dependabot untuk pembaruan dependencies otomatis
- Laravel Pint code formatting

## [v0.1.0] - 2026-06-27

### 🚀 Features

- Initial Laravel 13 + Svelte 5 + Inertia.js v3 project scaffold
- Tailwind CSS v4 integration
- Laravel Fortify authentication (login, register, password reset)
- Basic landing page dan dashboard layout

[v0.2.0]: https://github.com/hanzo-asashi/nautiplan/compare/v0.1.0...v0.2.0
[v0.1.0]: https://github.com/hanzo-asashi/nautiplan/releases/tag/v0.1.0
