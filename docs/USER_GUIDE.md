# 📖 Panduan Pengguna — Fitur Lanjutan NautiPlan

Dokumen ini berisi panduan penggunaan fitur-fitur operasional tingkat lanjut yang dirancang untuk membantu pengelolaan kegiatan, sub-kegiatan, hirarki DIPA, pencairan anggaran (SPP, SPM, SPTJB, SSP Pajak), pelacakan revisi POK, serta monitoring evaluasi di Politeknik Pelayaran Barombong.

---

## 📋 Daftar Isi
1. [Papan Tugas Kanban](#-papan-tugas-kanban)
2. [Kalender & Penjadwalan](#-kalender--penjadwalan)
3. [Notifikasi Real-time (SSE)](#-notifikasi-real-time-sse)
4. [Manajemen Versi Dokumen (Drag-and-Drop)](#-manajemen-versi-dokumen-drag-and-drop)
5. [Riwayat Perubahan & Audit Trail](#-riwayat-perubahan--audit-trail)
6. [Struktur Hirarki DIPA & Item Budgeting (POK)](#-struktur-hirarki-dipa--item-budgeting-pok)
7. [Administrasi Pencairan (SPP, SPM, SPTJB, & SSP Pajak)](#-administrasi-pencairan-spp-spm-sptjb--ssp-pajak)
8. [Pelacakan Revisi POK (Semula vs Menjadi)](#-pelacakan-revisi-pok-semula-vs-menjadi)
9. [Tren Penyerapan & Early Warning System (EWS)](#-tren-penyerapan--early-warning-system-ews)
10. [Laporan Monev Struktur DIPA APBN](#-laporan-monev-struktur-dipa-apbn)
11. [Keamanan & Validasi Transaksi Lanjutan](#-keamanan--validasi-transaksi-lanjutan)
12. [Manajemen Otentikasi & Keamanan Akun (2FA & Passkeys)](#-manajemen-otentikasi--keamanan-akun-2fa--passkeys)
13. [Alur Persetujuan Berjenjang (Workflow & Approvals)](#-alur-persetujuan-berjenjang-workflow--approvals)
14. [Linimasa Gantt (Gantt Chart)](#-linimasa-gantt-gantt-chart)
15. [Pemantauan Indikator Kinerja Utama (KPI Dashboard)](#-pemantauan-indikator-kinerja-utama-kpi-dashboard)
16. [Manajemen Data Master & Renstra/Renja](#-manajemen-data-master--renstrarenja)

---

## 📋 Papan Tugas Kanban

Papan Kanban menyediakan antarmuka visual untuk melacak progres tugas operasional (sub-kegiatan) secara dinamis.

### Cara Mengakses:
1. Masuk ke modul **Daftar Kegiatan**.
2. Pilih salah satu kegiatan, klik tombol **Papan Kanban** di bagian kanan atas.

### Cara Penggunaan:
* **Pindah Status**: Anda dapat memindahkan sub-kegiatan dengan mengubah status langsung pada drop-down status di kartu tugas.
* **Progres Slider**: Tarik slider progres (0–100%) untuk mencatat perkembangan riil pekerjaan.
* **Sinkronisasi Otomatis**: Progres keseluruhan kegiatan utama (parent) akan dihitung secara otomatis sebagai rata-rata dari progres seluruh sub-kegiatannya.
* **Optimistic Update**: Sistem menerapkan transisi instan tanpa reload halaman, mempercepat alur kerja Anda.

---

## 📅 Kalender & Penjadwalan

Modul Kalender menyajikan garis waktu kegiatan bulanan yang interaktif dan dinamis.

### Cara Mengakses:
* Klik menu **Kalender & Penjadwalan** di sidebar utama.

### Cara Penggunaan:
* **Navigasi Bulanan**: Gunakan tombol panah kiri/kanan untuk berpindah bulan atau pilih tahun anggaran aktif dari dropdown filter.
* **Penyaringan Lanjutan**: Saring kegiatan berdasarkan **Unit Pelaksana** atau **Status** untuk melihat jadwal spesifik.
* **Detail Drawer**: Klik tanggal tertentu pada kalender untuk membuka panel samping (drawer) yang menampilkan daftar lengkap kegiatan dan sub-kegiatan yang sedang berjalan pada hari tersebut beserta penanggung jawabnya.

---

## 🔔 Notifikasi Real-time (SSE)

NautiPlan menggunakan protokol Server-Sent Events (SSE) untuk mengirimkan pemberitahuan sistem secara instan tanpa membebani server Anda.

### Cara Penggunaan:
* **Lonceng Notifikasi**: Lonceng di pojok kanan atas layar akan menyala dan menampilkan badge merah ketika ada notifikasi baru (misal: pengajuan persetujuan baru, tugas diperbarui, atau revisi diminta).
* **Tindakan Cepat**: Buka lonceng notifikasi lalu klik salah satu notifikasi untuk langsung pergi ke halaman terkait.
* **Tandai Dibaca**: Klik tombol **Tandai Semua Dibaca** atau klik ikon centang pada tiap notifikasi untuk merapikan kotak masuk.
* **Notifikasi Desktop**: Jika Anda mengizinkan notifikasi browser, pemberitahuan akan muncul sebagai notifikasi desktop secara instan.

---

## 📎 Manajemen Versi Dokumen (Drag-and-Drop)

Setiap kegiatan mendukung lampiran dokumen yang terintegrasi dengan pelacakan riwayat revisi berkas.

### Cara Penggunaan:
* **Unggah Seret & Lepas (Drag-and-Drop)**: Cukup seret berkas Anda dari Windows Explorer dan lepas (*drop*) di atas area bergaris putus-putus pada detail kegiatan.
* **Pendeteksian Versi Baru**: Jika Anda mengunggah berkas dengan nama yang sama persis, sistem akan secara otomatis menjadikannya sebagai **versi baru** (misal: `v2`, `v3`) dari berkas asli, bukan menimpanya.
* **Riwayat Versi Collapsible**: Klik tombol riwayat versi (`+X`) pada berkas utama untuk melihat daftar versi terdahulu beserta nama pengunggahnya, serta opsi untuk mengunduh atau menghapus versi spesifik tersebut.

---

## 📜 Riwayat Perubahan & Audit Trail

Modul Audit Trail mendokumentasikan setiap perubahan parameter kritis dalam kegiatan untuk transparansi total.

### Cara Mengakses:
1. Buka halaman detail kegiatan.
2. Klik tombol **Riwayat Perubahan** di baris tombol atas.

### Cara Penggunaan:
* **Garis Waktu Perubahan**: Riwayat diurutkan berdasarkan waktu terbaru (kronologis) lengkap dengan informasi pengguna yang melakukan perubahan.
* **Perbandingan Parameter (Diff)**: Setiap entri menampilkan perubahan detail berupa tabel pembanding parameter sebelum (Nilai Lama) dan sesudah (Nilai Baru) perubahan tersebut disimpan.

---

## 🏛️ Struktur Hirarki DIPA & Item Budgeting (POK)

NautiPlan mengadopsi struktur penganggaran terstandarisasi DIPA APBN untuk pelaporan belanja yang terperinci dan terstruktur.

### Alur Hirarki:
`Program` ➔ `Kegiatan` ➔ `Output` ➔ `Sub Output` ➔ `Komponen` ➔ `Sub Komponen` ➔ `Pagu Belanja (Akun)` ➔ `Rincian POK (BudgetItem)`

### Penggunaan Item Budgeting:
* **Rincian POK**: Setiap Akun belanja memiliki rincian item (nama item, volume kuantitas, satuan, harga satuan, dan total pagu).
* **Manajemen Rincian**: KPA atau Operator Keuangan dapat menambahkan rincian baru pada POK melalui menu kelola anggaran kegiatan.
* **Audit Preventif Otomatis**: Item rencana POK inilah yang menjadi acuan ketat seluruh belanja realisasi berikutnya.

---

## 📄 Administrasi Pencairan (SPP, SPM, SPTJB, & SSP Pajak)

Modul realisasi kini terintegrasi penuh dengan dokumen pertanggungjawaban keuangan Pejabat Pembuat Komitmen (PPK).

### Validasi Transaksi (Audit Preventif):
* **Markup Prevention**: Sistem akan secara otomatis menolak realisasi jika harga satuan yang dimasukkan melebihi harga satuan yang direncanakan pada POK.
* **Volume Control**: Sistem menolak transaksi secara preventif jika jumlah volume item yang dibelanjakan melebihi sisa volume rencana POK (`remaining_volume`).

### Perhitungan Pajak Presisi:
* Pengguna dapat memilih komponen pajak pada tiap item realisasi belanja:
  * **PPN**: Dihitung otomatis sebesar 11% dari nilai kena pajak.
  * **PPh 21**: Pajak penghasilan perorangan (mendukung opsi PPh 21 campur/mixed).
  * **PPh 22 & PPh 23**: Pemotongan pajak pembelian barang atau jasa.

### Cetak Dokumen PDF Siap Pakai:
Setelah realisasi dicatat, Anda dapat mengunduh dokumen administrasi formal berstandar negara berikut:
1. **Surat Permintaan Pembayaran (SPP)**: Berisi rincian tagihan penyerapan belanja.
2. **Surat Perintah Membayar (SPM)**: Surat perintah pencairan dana ke kas negara.
3. **Surat Pernyataan Tanggung Jawab Belanja (SPTJB)**: Surat pernyataan keabsahan belanja PPK.
4. **Surat Setoran Pajak (SSP)**: Lembar penyetoran potongan PPN/PPh ke kantor pajak (dicetak otomatis per jenis pajak yang dipungut).

---

## 🔄 Pelacakan Revisi POK (Semula vs Menjadi)

Setiap pergeseran atau perubahan volume/harga rincian POK akan didokumentasikan untuk keperluan audit keuangan internal.

### Alur Kerja Revisi:
1. Masuk ke halaman **Daftar Pagu Anggaran** lalu pilih Akun Belanja yang ingin disesuaikan.
2. Ubah rincian item belanja (misal: memindahkan volume kertas, mengubah harga satuan standard, atau menambah rincian baru).
3. Masukkan **Alasan Revisi** secara singkat dan jelas sebelum menekan tombol simpan.
4. Sistem akan menaikkan **Nomor Versi POK** dan merekam data snapshot lama sebagai "Semula" dan data perubahan baru sebagai "Menjadi".

### Visualisasi & Cetak Laporan Revisi:
* **Histori Revisi (Modal)**: Klik tombol **Histori Revisi** pada baris anggaran untuk membandingkan secara visual rincian sebelum vs sesudah perubahan (lengkap dengan penanda item baru/dihapus dan nilai selisih delta).
* **Cetak PDF Laporan Komparatif**: Anda dapat mengekspor laporan komparatif revisi POK ini dalam bentuk berkas PDF resmi untuk arsip pemeriksaan.

---

## ⚠️ Tren Penyerapan & Early Warning System (EWS)

Modul Analisis menyajikan pemantauan penyerapan anggaran berjalan secara visual dan memberikan peringatan proaktif jika terjadi kondisi kritis.

### Indikator Pemantauan:
1. **Tren Penyerapan Bulanan (Kumulatif)**: Menyajikan progres realisasi kumulatif dari bulan Januari hingga Desember untuk memantau kecepatan belanja sepanjang tahun anggaran berjalan.
2. **Pagu Kritis / Early Warning System (EWS)**:
   * Menampilkan daftar pagu belanja dengan persentase penyerapan di atas **85%** (dana hampir habis) atau memiliki sisa saldo di bawah **Rp 2.000.000**.
   * Kartu peringatan berkode warna: **Kuning (Warning)** untuk sisa dana terbatas dan **Merah (Sangat Kritis/Depleted)** jika pagu sudah hampir habis agar PPK/KPA segera menjadwalkan revisi POK.

---

## 📊 Laporan Monev Struktur DIPA APBN

Sistem menyediakan ekspor data yang komprehensif bagi pimpinan untuk melakukan pemantauan anggaran.

### Cara Akses & Ekspor:
* Masuk ke menu **Analisis & Realisasi**.
* Klik tab **Hub Impor & Ekspor** untuk mengunduh template rencana POK atau mengunggah data anggaran masal.
* Klik tombol **Cetak Laporan** untuk mengunduh komparasi realisasi anggaran per Unit Kerja, per Program, maupun perbandingan realisasi multi-tahun.
* Laporan realisasi per-hirarki (Output/Sub-Output/Komponen) dapat dicetak secara terstruktur guna melihat efisiensi penyerapan masing-masing pos anggaran.

---

## 🔒 Keamanan & Validasi Transaksi Lanjutan

Sistem dilengkapi dengan proteksi integritas data dan validasi otomatis untuk mencegah kesalahan pencatatan administrasi.

### 1. Penguncian Tahun Anggaran (Fiscal Year Lock):
* KPA dapat mengunci (lock) tahun anggaran yang telah berakhir atau ditutup bukunya.
* **Pencegahan Manipulasi**: Jika tahun anggaran terkunci, sistem akan memblokir seluruh upaya untuk merevisi POK, mencatat realisasi belanja baru, mengubah/menghapus anggaran, atau mengajukan persetujuan kegiatan pada tahun bersangkutan. Hal ini mengamankan laporan keuangan tahunan dari manipulasi retrospektif.

### 2. Validasi Batas Tanggal (Date Boundaries):
* **Rentang Tahun Anggaran**: Tanggal realisasi belanja yang diinputkan dijamin 100% harus berada di antara tanggal mulai (*start_date*) dan tanggal selesai (*end_date*) dari tahun anggaran tersebut. Mencegah kesalahan ketik (typo) tahun yang bisa mengacaukan perhitungan.
* **Kronologis Kontrak Pihak Ketiga**: Untuk realisasi menggunakan surat pesanan/kontrak, sistem memastikan bahwa tanggal pembayaran/realisasi tidak mendahului tanggal penerbitan kontrak (Procurement Date).

### 3. Rekaman Jejak Audit (Audit Trail) Lanjutan:
* Setiap perubahan data anggaran krusial (termasuk kegiatan, rincian POK, catatan realisasi, dan revisi) kini terekam secara komprehensif dalam log audit sistem untuk menjamin akuntabilitas 100%.

---

## 🔐 Manajemen Otentikasi & Keamanan Akun (2FA & Passkeys)

Keamanan akses pengguna diatur dengan standar tertinggi melalui fitur otentikasi multi-lapis.

* **Otentikasi Dua Langkah (2FA)**: Pengguna dapat mengamankan akun menggunakan aplikasi authenticator (Google Authenticator, Authy, dll.) berbasis TOTP.
* **Passkeys (WebAuthn)**: Login biometrik (sidik jari, FaceID, atau Windows Hello) tanpa perlu mengingat kata sandi. Cepat, aman, dan kebal terhadap pishing.
* **Manajemen Sesi & Perangkat**: Pantau dan cabut akses (logout jarak jauh) dari perangkat yang tidak dikenal melalui halaman Keamanan Akun.

---

## 📝 Alur Persetujuan Berjenjang (Workflow & Approvals)

Setiap pengajuan kegiatan atau revisi POK dapat melalui mekanisme persetujuan berjenjang sebelum dapat dieksekusi.

* **Pengajuan (*Submit*)**: Pengguna/KPA dapat mengajukan persetujuan untuk sebuah kegiatan.
* **Review & Verifikasi**: Pihak berwenang (misal PPK atau SPI) dapat melihat daftar usulan yang masuk.
* **Tindakan (Setuju / Tolak / Revisi)**: Verifikator dapat menyetujui, menolak, atau mengembalikan usulan dengan catatan perbaikan yang jelas.
* **Jejak Persetujuan**: Semua riwayat status dan catatan diverifikasi secara transparan pada riwayat kegiatan.

---

## 📈 Linimasa Gantt (Gantt Chart)

Modul Linimasa Gantt memberikan visualisasi proyek dan kegiatan lintas waktu yang sangat berguna untuk perencanaan makro.

* **Tampilan Interaktif**: Memvisualisasikan rentang waktu (*start_date* hingga *end_date*) dari setiap kegiatan.
* **Garis Waktu Geser (*Drag & Pan*)**: Geser dan zoom grafik rentang waktu untuk melihat perencanaan bulanan hingga tahunan secara mudah.
* **Identifikasi Tumpang Tindih (*Overlap*)**: Sangat membantu pimpinan untuk mengalokasikan sumber daya agar tidak ada kegiatan berat yang bertumpuk pada bulan yang sama.

---

## 🎯 Pemantauan Indikator Kinerja Utama (KPI Dashboard)

Dashboard khusus (Capaian KPI) mengukur kinerja nyata terhadap target strategis politeknik.

* **Metrik Kinerja (IKU)**: Melacak ketercapaian Indikator Kinerja Utama institusi (seperti persentase kelulusan, daya serap lulusan, dll).
* **Visualisasi Target vs Realisasi**: Grafik perbandingan yang mudah dipahami (Gauge / Bar) untuk menunjukkan seberapa dekat instansi dengan target Renstra yang telah ditetapkan.

---

## 🗄️ Manajemen Data Master & Renstra/Renja

Sistem NautiPlan juga mencakup modul administratif untuk mengatur seluruh data pondasi aplikasi.

* **Rencana Strategis (Renstra)**: Pengelolaan visi, misi, dan tujuan jangka panjang instansi.
* **Rencana Kerja (Renja)**: Pengelolaan sasaran program prioritas tahunan.
* **Unit Organisasi & Pengguna**: Manajemen unit pelaksana teknis (Bagian/Subbagian/Pusat) serta hak akses pengguna (Roles/Permissions) sesuai peran organisasinya.
