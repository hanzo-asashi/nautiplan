<p align="center">
  <img src="https://img.shields.io/badge/Laravel-v13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Svelte-5-FF3E00?style=for-the-badge&logo=svelte&logoColor=white" alt="Svelte">
  <img src="https://img.shields.io/badge/Inertia.js-v3-6C47FF?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
</p>

# ⚓ NautiPlan

**Sistem Terintegrasi Pengelolaan Program & Kegiatan**  
*Politeknik Pelayaran Barombong Makassar*

---

## 📋 Tentang

NautiPlan adalah platform manajemen perencanaan strategis yang dirancang khusus untuk Politeknik Pelayaran Barombong. Sistem ini mencakup pengelolaan rencana strategis (Renstra), rencana kerja tahunan (Renja), anggaran, indikator kinerja utama, hingga evaluasi program kerja secara digital, transparan, dan akuntabel.

## ✨ Fitur Utama

| Modul | Deskripsi |
|---|---|
| 📊 **Dashboard** | Ringkasan statistik real-time dengan chart interaktif |
| 🏢 **Manajemen Unit** | Kelola unit kerja dengan struktur hierarkis |
| 👥 **Manajemen Pengguna** | RBAC (Role-Based Access Control) dengan multi-role |
| 📅 **Tahun Anggaran** | Kelola periode fiskal beserta status aktif/arsip |
| 📝 **Renstra** | Rencana Strategis jangka menengah dengan indikator capaian |
| 📋 **Renja** | Rencana Kerja Tahunan yang terhubung ke Renstra |
| 🎯 **Program & Kegiatan** | Manajemen program, kegiatan, dan sub-kegiatan |
| 💰 **Anggaran & Realisasi** | Tracking pagu anggaran dan realisasi belanja |
| 📜 **Audit Log** | Jejak audit lengkap untuk akuntabilitas |

## 🛠️ Tech Stack

- **Backend:** Laravel 13, PHP 8.5
- **Frontend:** Svelte 5, Inertia.js v3, Tailwind CSS v4
- **Database:** MySQL / PostgreSQL
- **Auth:** Laravel Fortify (2FA, Passkeys, Email Verification)
- **Testing:** Pest v4, PHPStan / Larastan
- **Code Style:** Laravel Pint, ESLint, Prettier

## 🚀 Instalasi

### Prasyarat

- PHP 8.5+
- Composer
- Node.js 20+
- MySQL 8+ / PostgreSQL 15+

### Langkah-langkah

```bash
# Clone repository
git clone https://github.com/hanzo-asashi/nautiplan.git
cd nautiplan

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env lalu jalankan migrasi
php artisan migrate --seed

# Build assets
npm run build

# Jalankan server (atau gunakan Laravel Herd)
php artisan serve
```

### Development

```bash
# Jalankan dev server dengan HMR
npm run dev

# Atau gunakan composer script
composer run dev
```

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test dengan output compact
php artisan test --compact

# Jalankan test spesifik
php artisan test --filter=NamaTest

# Jalankan linting & static analysis
vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## 📁 Struktur Proyek

```
nautiplan/
├── app/
│   ├── Concerns/          # Traits (HasAuditTrail)
│   ├── Http/
│   │   ├── Controllers/   # Resource controllers
│   │   └── Middleware/     # CheckRole, HandleInertiaRequests
│   └── Models/            # Eloquent models
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── css/               # Tailwind CSS
│   └── js/
│       ├── components/    # Reusable Svelte components
│       ├── layouts/       # Auth & app layouts
│       └── pages/         # Inertia page components
├── routes/                # Web & API routes
└── tests/                 # Pest test suites
```

## 📄 Lisensi

Hak Cipta © 2026 Politeknik Pelayaran Barombong Makassar. All rights reserved.
