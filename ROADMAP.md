# NautiPlan — Roadmap

> Sistem Terintegrasi Pengelolaan Program & Kegiatan — Poltekpel Barombong Makassar

## Overview

NautiPlan is an extremely large application (~18 modules, 30+ database tables, 50+ pages). To keep progress manageable and verifiable, the roadmap is structured into phases that build on each other. Each phase produces a working, testable increment.

---

## Phase 1 — Foundation & Core Data ✅ Complete

**Goal**: Build the foundation — database schema, role system, landing page, dashboard, and core CRUD for all master data and planning modules.

### Deliverables

- 18 database migrations with full ERD
- 18 Eloquent models with relationships, factories, and seeders
- Role-based access control (8 roles)
- Redesigned landing page (Hero, Features, Workflow, Benefits, Statistics, Testimonials, CTA, Footer)
- Rich dashboard with stats cards, budget charts, activity timeline
- CRUD pages: Units, Fiscal Years, Renstra, Renja, Programs, Activities, Budgets, Users, Audit Logs
- Expanded sidebar navigation with collapsible groups
- Reusable UI components (DataTable, charts, status badges, etc.)
- Dark/light mode support

---

## Phase 2 — KPI & Monitoring ✅ Done

**Goal**: Implement performance indicator tracking and monitoring/evaluation workflows.

### Deliverables

- Activity Indicators CRUD (IKU/IKK)
- Quarterly reporting
- Progress tracking dashboard
- Monitoring & Evaluation forms
- KPI achievement visualization
- Target vs actual comparisons

---

## Phase 3 — Approval Workflow ✅ Complete

**Goal**: Build the configurable multi-step approval chain for activity proposals.

### Deliverables

- Activity proposal flow
- Configurable multi-step approval chain (Unit Operator → Head of Dept → Deputy Director → Director)
- Revision requests
- Email notifications on status changes
- Approval history and audit trail

---

## Phase 4 — Reporting & Export ✅ Complete

**Goal**: Add comprehensive reporting with PDF/Excel export and visual analysis tools.

### Deliverables

- PDF report generation (barryvdh/laravel-dompdf)
- Excel export/import (openspout/openspout)
- Gantt chart view
- Comparative analysis reports
- Multi-year planning comparisons
- Budget realization reports

---

## Phase 5 — Advanced Features ✅ Complete

**Goal**: Add power-user features for day-to-day operational management.

### Deliverables

- Kanban task board for sub-activities
- Calendar & scheduling
- Real-time notifications (broadcasting)
- Document versioning
- File upload management
- Planning change versioning

---

## Phase 6 — Polish & Production ✅ Complete

**Goal**: Optimize performance, complete test coverage, and prepare for production deployment.

### Deliverables

- Performance optimization ✅ Complete
- Full test coverage ✅ Complete
- Accessibility audit ✅ Complete
- Production deployment config (Laravel Cloud) ✅ Complete
- Security hardening ✅ Complete
- User documentation ✅ Complete

---

## SIM-PPK Extension — POK & Pencairan Anggaran (New)

Guna menyelaraskan sistem dengan kebutuhan administrasi Pejabat Pembuat Komitmen (PPK) Politeknik Pelayaran Barombong, dilakukan pengembangan fitur lanjutan yang terbagi dalam **3 Fase Baru**:

### Extension Phase 1 — Perluasan Skema Hirarki DIPA & Item Budgeting ✅ Complete

**Goal**: Membangun relasi hirarki terstruktur standar DIPA/POK dan rincian item rencana anggaran.

#### Deliverables

- **Database Migrations**:
    - Tabel `outputs` (Kode, Nama, Kegiatan ID)
    - Tabel `sub_outputs` (Kode, Nama, Output ID)
    - Tabel `components` (Kode, Nama, Sub Output ID)
    - Tabel `sub_components` (Kode, Nama, Komponen ID)
    - Tabel `budget_items` (Volume, Satuan, Harga Satuan, Total, Akun/ActivityBudget ID)
- **Eloquent Models & Relations**:
    - Model baru: `Output`, `SubOutput`, `Component`, `SubComponent`, `BudgetItem`
    - Relasi dari `Program` $\rightarrow$ `Activity` $\rightarrow$ `Output` $\rightarrow$ `SubOutput` $\rightarrow$ `Component` $\rightarrow$ `SubComponent` $\rightarrow$ `ActivityBudget` $\rightarrow$ `BudgetItem`
- **Seeder Integration**:
    - Parser/Seeder rill dari file `docs/MATRIKS REVSI - POK BLU 14 Mei 2025 (Saldo Awal).xlsx` sheet `BLU REV 5`.

### Extension Phase 2 — Administrasi Pencairan SPP, SPM, SPTJB, & SSP Pajak ✅ Complete

**Goal**: Membuat formulir pengajuan pembayaran dan pemotongan pajak PPh 22/23 lengkap dengan bukti setoran.

#### Deliverables

- Input opsi pajak PPh 22 dan PPh 23 pada form realisasi item.
- Template cetak PDF untuk:
    - Surat Permintaan Pembayaran (SPP)
    - Surat Perintah Membayar (SPM)
    - Surat Pernyataan Tanggung Jawab Belanja (SPTJB)
    - Surat Setoran Pajak (SSP)

### Extension Phase 3 — Laporan Monev Struktur DIPA APBN ✅ Complete

**Goal**: Menghasilkan cetakan rekapitulasi realisasi per Output, Sub Output, Komponen, dan Sub Komponen.

#### Deliverables

- Cetak PDF Realisasi per Output & Sub Output.
- Cetak PDF Realisasi per Komponen & Sub Komponen (baik rekapitulasi ringkas maupun detail rincian barang).

### Extension Phase 4 — Pengembangan Lanjutan & Perbaikan Celah (Gaps) ✅ Complete

**Goal**: Menyempurnakan sistem monitoring dengan audit preventif, pelacakan histori revisi anggaran, dan visualisasi analisis penyerapan.

#### Deliverables

- **Pelacakan Revisi POK (Semula vs Menjadi)**:
    - Membuat tabel `budget_revisions` dan `budget_revision_details` untuk merekam snapshot data pagu lama ("Semula") dan data setelah direvisi ("Menjadi").
    - Menghasilkan laporan komparatif revisi POK per Akun/Kegiatan untuk mempermudah audit pergeseran anggaran.
- **Validasi Item-Level Budgeting (Audit Preventif)**:
    - Menambahkan validasi ketat saat pembuatan realisasi baru agar membandingkan realisasi rincian belanja (`realization_items`) dengan rencana standar di POK (`budget_items`).
    - Menolak transaksi secara preventif jika harga satuan melebihi standard rencana (_mark-up_ prevention) atau jika volume kuantitas melebihi sisa rencana.
- **Visualisasi Tren Realisasi & Dashboard Monev**:
    - Menyediakan widget grafik interaktif (tren bulanan/triwulanan, diagram penyerapan per unit kerja).
    - Sistem peringatan dini (_early warning system_) untuk pagu kritis atau penyerapan di bawah target.

---

## Extension Phase 5 — Fitur Tambahan & Interaktivitas UI (In Progress)

**Goal**: Meningkatkan interaktivitas dan monitoring real-time pada sistem anggaran.

### Deliverables

- **Feature 1: Live Activity Feed (Notifikasi & Dashboard Real-time)**:
    - Integrasi EventStream/SSE `/notifications/stream` ke dalam Dashboard untuk umpan aktivitas real-time (realisasi masuk, verifikasi, perubahan pagu).
- **Feature 2: Interactive Gantt Chart / Timeline Refinement**:
    - Menyempurnakan halaman `/reports/gantt` agar lebih interaktif dengan tooltip detail, highlight progress, dan navigasi timeline.
- **Feature 3: Quick Budget Revision Wizard**:
    - Modal interaktif multi-step untuk memindahkan alokasi pagu antar kegiatan dengan validasi audit sisa pagu.

---

## Technical Refactoring & Improvements (To Do)

**Goal**: Standardize controller architecture using the Action Pattern and implement database transactions for complex operations.

### Deliverables

- Apply Action Pattern to all remaining controllers (`ProgramController`, `RenjaController`, etc.).
- Move non-CRUD specific actions (like `revisions`, `updateSubActivityStatus`) into dedicated Action classes.
- Implement Database Transactions (DB Transaction) for actions that modify multiple records or tables to ensure data integrity.

---

## Technical Decisions

| Decision           | Choice                  | Rationale                                    |
| ------------------ | ----------------------- | -------------------------------------------- |
| Fiscal Year        | Calendar year (Jan–Dec) | Follows Indonesian government fiscal year    |
| Approval Chain     | Configurable            | Flexibility for organizational changes       |
| Budget Currency    | IDR (Rupiah)            | Single-currency system                       |
| PDF Export         | barryvdh/laravel-dompdf | Mature, well-supported                       |
| Excel Export       | openspout/openspout     | PHP 8.5 compatible, memory-efficient         |
| Frontend Framework | Svelte 5 + Inertia v3   | SPA experience with server-side routing      |
| UI Components      | shadcn-svelte (bits-ui) | Accessible, customizable                     |
| CSS Framework      | TailwindCSS 4           | Utility-first, v4 with CSS custom properties |
