# 📖 Panduan Pengguna — Fitur Lanjutan NautiPlan

Dokumen ini berisi panduan penggunaan fitur-fitur operasional tingkat lanjut yang dirancang untuk membantu pengelolaan kegiatan dan sub-kegiatan sehari-hari secara digital dan real-time di Politeknik Pelayaran Barombong.

---

## 📋 Daftar Isi
1. [Papan Tugas Kanban](#-papan-tugas-kanban)
2. [Kalender & Penjadwalan](#-kalender--penjadwalan)
3. [Notifikasi Real-time (SSE)](#-notifikasi-real-time-sse)
4. [Manajemen Versi Dokumen (Drag-and-Drop)](#-manajemen-versi-dokumen-drag-and-drop)
5. [Riwayat Perubahan & Audit Trail](#-riwayat-perubahan--audit-trail)

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
* **Notifikasi Desktop**: Jika Anda mengizinkan notifikasi browser, pemberitahuan akan muncul sebagai notifikasi desktop windows secara instan.

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
