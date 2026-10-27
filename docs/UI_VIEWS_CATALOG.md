# Katalog & Spesifikasi Antarmuka Pengguna (UI Views Catalog) - PPDB Engine

Dokumen ini mendeskripsikan seluruh tampilan antarmuka (*UI views*), tata letak (*layout shells*), komponen interaktif (*reusable components*), dan dokumen cetak (*PDF templates*) yang ada pada sistem PPDB Multi-Tenant Monolith Laravel 12.

---

## 1. Ringkasan Arsitektur Antarmuka & Design System

Sistem UI PPDB dibangun dengan prinsip **No-SPA Monolith** menggunakan:
- **Rendering:** Laravel Blade (Server-side rendering)
- **Styling:** Tailwind CSS (Utility-first, responsive reflow)
- **Reaktivitas Interaktif:** Alpine.js (State modal, tab wizard, AJAX toggle status, drawer navigasi)
- **Aksesibilitas & Standar Touch:** Target sentuh &ge; 44×44px, kontras teks WCAG AA, skip-to-content links, aria labels.
- **Palet Warna:**
  - `emerald-600` / `emerald-700`: Brand utama sekolah & identitas publik
  - `slate-50` s.d. `slate-900`: Latar belakang netral & tipografi
  - `amber-500` / `amber-600`: Status *Perlu Perbaikan (need_revision)* & peringatan
  - `blue-500` / `blue-600`: Status *Terdaftar (submitted)*
  - `purple-600`: Status *Diterima (accepted)*
  - `teal-600`: Status *Selesai Daftar Ulang (completed)*
  - `rose-600`: Status *Dibatalkan / Gugur (cancelled)*

---

## 2. Layout Shells (Kerangka Tata Letak Utama)

### 2.1. Layout Publik (`resources/views/layouts/ppdb_public.blade.php`)
- **Tujuan:** Kerangka utama untuk seluruh halaman yang diakses oleh calon siswa dan orang tua.
- **Elemen UI:**
  - **Skip Link:** `<a href="#main-content">` tersembunyi, muncul saat diakses via tombol `Tab` keyboard (*a11y*).
  - **Navbar Sticky:** Header tetap menempel di atas (`sticky top-0 bg-white/90 backdrop-blur-md`).
    - Logo sekolah (gambar dari `logo_url` tenant atau avatar inisial berwarna emerald).
    - Nama sekolah (dengan `truncate min-w-0` agar tidak merusak navbar pada ponsel sempit).
    - Badge deskripsi *"Penerimaan Peserta Didik Baru"* (`hidden sm:block`).
    - Tombol navigasi *"Cek Status"* dengan border emerald dan ikon kaca pembesar.
  - **Kontainer Konten (`#main-content`):** Wadah dinamis via `@yield('content')`.
  - **Footer Publik:**
    - Logo & nama lengkap sekolah.
    - Hak cipta tahun ajaran resmi dan atribusi platform sekolah.
    - Link pintas ke Beranda PPDB, Cek Status, dan Login Panitia Sekolah.

### 2.2. Layout CMS Panitia PPDB (`resources/views/layouts/ppdb_cms.blade.php`)
- **Tujuan:** Kerangka kerja panel administrasi untuk panitia sekolah.
- **Elemen UI:**
  - **Drawer / Sidebar Navigasi Responsif:**
    - Pada Desktop (`lg:flex`): Sidebar statis di sisi kiri (lebar 64 / 256px).
    - Pada Mobile/Tablet (`lg:hidden`): Off-canvas drawer berbasis Alpine.js (`x-show="sidebarOpen"`, dismiss via overlay click atau tombol `Escape`).
    - Menu item aktif ditandai warna `bg-emerald-50 text-emerald-700` dengan ikon SVG:
      1. *Dashboard* (`cms.dashboard`)
      2. *Daftar di Loket / Walk-in* (`cms.walk-in.index`)
      3. *Data Pendaftar* (`cms.applicants.index`)
      4. *Gelombang Pendaftaran* (`cms.waves.index`)
      5. *Jalur & Kuota* (`cms.tracks.index`)
      6. *Formulir Builder* (`cms.form-builder.index`)
      7. *Google Drive Sekolah* (`cms.gdrive.index`)
  - **Top Navigation Bar:**
    - Tombol Hamburger Menu (khusus layar ponsel & tablet).
    - Indikator sekolah & nama pengguna yang sedang login.
    - Tombol *"Lihat Portal Publik"* (buka portal calon siswa di tab baru).
    - Tombol *Logout / Keluar* panitia dengan konfirmasi.
  - **Flash Notification Toast:** Alert otomatis berwarna hijau (sukses) atau merah (gagal) jika controller mengirim `session('success')` atau `session('error')`.

### 2.3. Layout Superadmin Platform (`resources/views/layouts/ppdb_superadmin.blade.php`)
- **Tujuan:** Antarmuka khusus tim pengelola pusat multi-tenant.
- **Elemen UI:**
  - Tema gelap eksklusif (`bg-slate-950 text-slate-100`).
  - Topbar minimalis dengan logo Superadmin Cloud, status Engine Aktif, dan tombol logout.
  - Kontainer lebar (`max-w-7xl mx-auto`) untuk visualisasi metrik dan tabel multi-tenant.

---

## 3. Halaman Publik (Portal Calon Siswa & Orang Tua)

### 3.1. Halaman Beranda / Landing PPDB (`pages/public/landing.blade.php`)
- **Rute:** `/` (Domain tenant / prefix sekolah)
- **Elemen UI:**
  - **Hero Banner:** Latar belakang gradient hijau toska (`emerald-800 to teal-800`) berbayang modern.
    - Pill status pembukaan: *"Pendaftaran Sedang Dibuka"* (hijau) atau *"Pendaftaran Belum Dibuka"* (amber).
    - Heading utama ucapan selamat datang di portal resmi sekolah.
    - Tombol CTA: *"Daftar Sekarang"* (putih kontras) dan *"Cek Status Saya"* (transparan dengan border). Kedua tombol reflow menjadi `w-full` pada ponsel 320px–360px.
  - **Banner Gelombang Aktif:** Kotak khusus menampilkan nama gelombang yang sedang aktif (contoh: *Gelombang 1 - Reguler TA 2026/2027*), batas waktu penutupan dalam format bahasa Indonesia resmi, dan tombol *"Mulai Mengisi Formulir"*.
  - **Tahapan Registrasi (3 Langkah):** Kartu panduan ringkas calon siswa:
    1. *Pilih Jalur & Isi Data*
    2. *Unggah Foto Berkas*
    3. *Simpan Bukti Pendaftaran (PDF & Slip)*
  - **Katalog Jalur Masuk:** Grid kartu responsif (`1 kolom mobile -> 2 kolom tablet -> 3 kolom desktop`):
    - Nama jalur (Zonasi, Prestasi, Reguler, Afirmasi, Tahfidz).
    - Badge kuota siswa atau "Tanpa Batas Kuota".
    - Deskripsi & ketentuan seleksi.
    - Tombol *"Pilih Jalur Ini"* (otomatis memilih jalur saat masuk ke wizard).

### 3.2. Formulir Pendaftaran Wizard 4 Langkah (`pages/public/register_step_container.blade.php`)
- **Rute:** `/daftar` (`tenant_route('applicant.register.index')`)
- **Teknologi Interaksi:** Alpine.js (`registrationWizard()`) dengan persistensi draft otomatis ke `localStorage` (`saveDraft()`), transisi langkah tanpa refresh halaman, dan auto-scroll ke atas setiap pergantian step.
- **Elemen UI Per Langkah:**
  - **Stepper Bar (`<x-registration_stepper />`):** Indikator progres bernomor 1 s.d. 4 dengan label responsif yang tidak bertabrakan di mobile.
  - **Step 1 - Pilih Jalur Masuk:**
    - Menampilkan nama gelombang aktif.
    - Pilihan radio card interaktif dengan highlight border hijau emerald saat dipilih.
  - **Step 2 - Identitas Calon Siswa & Kontak:**
    - Nama Lengkap Calon Siswa (wajib, sesuai KK).
    - NIK 16 Digit (wajib, font monospace, validasi panjang karakter).
    - Jenis Kelamin (Laki-laki / Perempuan).
    - Tempat & Tanggal Lahir (native date picker responsif).
    - Nomor WhatsApp Aktif (wajib, disertai catatan kegunaan untuk pengiriman slip PDF).
  - **Step 3 - Informasi Tambahan & Isian Khusus:**
    - Merender kolom dinamis sekolah via komponen `<x-dynamic_field>` (NISN, Nama Ibu Kandung, Asal Sekolah, Pilihan Jurusan, dll.).
  - **Step 4 - Unggah Berkas Persyaratan (`<x-registration_step_documents>`):**
    - Kotak Opsi Penyerahan Fisik Langsung: *"Tunjukkan berkas fisik langsung ke sekolah (tanpa unggah file)"*. Jika dicentang, file upload menjadi opsional dan atribut `required` dicabut secara dinamis via Alpine.js (`togglePhysicalOption()`).
    - Tempat unggah dokumen persyaratan (KK, Akta Lahir, Ijazah, Rapor).
    - Input berkas kamera langsung (`accept="image/*,application/pdf"`).
    - Keterangan kompresi otomatis client-side untuk menghemat kuota pendaftar.
  - **Navigasi Wizard (`<x-registration_wizard_nav />`):**
    - Tombol *Sebelumnya* (dinonaktifkan pada Step 1).
    - Tombol *Lanjutkan* (Step 1-3 dengan validasi field wajib sebelum pindah).
    - Tombol *Kirim Pendaftaran* (Step 4, memicu state loading spinner dan submit form).

### 3.3. Halaman Sukses Registrasi & Slip Anti-Hilang (`pages/public/register_success.blade.php`)
- **Rute:** `/pendaftaran/sukses`
- **Konsep:** Penerapan *3-Layer Anti-Lost Slip Invariant* agar calon siswa tidak kehilangan nomor pendaftaran:
  1. *Layer 1 (Auto-Download):* Script memicu unduhan file PDF bukti pendaftaran secara otomatis saat halaman terbuka.
  2. *Layer 2 (WhatsApp Share):* Tombol hijau *"Kirim Bukti ke WhatsApp"* membuka tautan `https://wa.me/` dengan pesan teks terformat otomatis berisi nomor registrasi dan tautan unduh slip.
  3. *Layer 3 (Online Lookup):* Tautan menuju halaman pencarian status mandiri jika file fisik hilang.
- **Elemen UI:**
  - Ikon centang hijau besar penanda sukses.
  - Kotak nomor registrasi resmi (font monospace tebal berbingkai dashed emerald).
  - Kartu ringkasan calon siswa (Nama, Jalur, Gelombang, Waktu Pendaftaran).
  - Tombol aksi utama:
    - *"Unduh Bukti Pendaftaran (PDF)"* (berwarna emerald gelap).
    - *"Kirim Bukti ke WhatsApp"* (berwarna hijau WA).
    - *"Cek Status Pendaftaran"* (border netral).

### 3.4. Halaman Pendaftaran Sedang Ditutup (`pages/public/registration_closed.blade.php`)
- **Tujuan:** Muncul otomatis jika belum ada gelombang aktif atau masa pendaftaran telah berakhir.
- **Elemen UI:**
  - Ikon jam pasir / kalender berwarna amber.
  - Pesan ramah bahwa pendaftaran online sedang ditutup.
  - Kotak jadwal gelombang berikutnya (jika sudah dijadwalkan oleh panitia), lengkap dengan tanggal dan jam pembukaan WIB.
  - Tombol *"Kembali ke Beranda"* dan *"Cek Status Pendaftaran Sebelumnya"*.

### 3.5. Halaman Pencarian Status Mandiri (`pages/public/status_lookup.blade.php`)
- **Rute:** `/status` (`tenant_route('applicant.status.lookup')`)
- **Prinsip Bebas Password:** Tidak ada pembuatan akun pendaftar; autentikasi murni menggunakan kombinasi identitas resmi.
- **Elemen UI:**
  - Ikon pencarian.
  - Input field *Nomor Registrasi* (contoh: `REG-SMAN1-2026-0001`).
  - Input field *Tanggal Lahir Calon Siswa* (date picker).
  - Tombol *"Periksa Status Pendaftaran"* full-width `min-h-[44px]`.

### 3.6. Lembar Detail Status Siswa (`pages/public/status_detail.blade.php`)
- **Rute:** `/status/detail`
- **Elemen UI:**
  - **Header Pendaftar:** Nama lengkap siswa (dengan `break-words`), nomor registrasi (dengan `break-all`), dan pill status pendaftaran (Terdaftar, Berkas Lengkap, Perlu Perbaikan, Diterima, Selesai).
  - **Alert Catatan Revisi:** Kotak kuning amber yang tampil hanya jika status pendaftar adalah `need_revision`, memuat pesan instruksi perbaikan dari panitia dan tombol *"Perbaiki Berkas Sekarang"*.
  - **Timeline Progres (4 Tahap):**
    1. *Terdaftar* &rarr; 2. *Berkas Lengkap* &rarr; 3. *Diterima* &rarr; 4. *Selesai*.
  - **Rincian Jalur & Gelombang:** Kartu ringkasan jalur masuk yang dipilih.
  - **Daftar Berkas & Status Validasi:**
    - Baris setiap dokumen (Akta, KK, dll.).
    - Nama file terunggah atau label *"Ditunjukkan fisik langsung ke sekolah"* jika memilih opsi berkas fisik tanpa upload.
    - Catatan penolakan spesifik per dokumen jika ada.
    - Badge status per berkas (*Lengkap*, *Perlu Perbaikan*, *Menunggu Verifikasi*).
  - **Aksi Cetak Ulang:** Tombol unduh ulang PDF slip resmi dan tombol cek nomor pendaftar lain.

### 3.7. Halaman Perbaikan Berkas Pendaftar (`pages/public/status_reupload.blade.php`)
- **Rute:** `/status/perbaiki`
- **Elemen UI:**
  - Header peringatan perbaikan berkas dengan nama & nomor registrasi.
  - Kotak kutipan catatan panitia sekolah.
  - Form unggah ulang berkas:
    - Dokumen yang berstatus `invalid` otomatis ditandai dengan bingkai border amber dan label *"Perlu Perbaikan"*.
    - Alasan penolakan dari panitia ditampilkan di bawah nama berkas.
    - Input pemilih file pengganti (JPG, PNG, PDF maks 5MB).
  - Tombol aksi: *"Batal"* dan *"Kirim Berkas Perbaikan"* (dilengkapi indikator loading spinner Alpine.js saat proses unggah berlangsung).

---

## 4. Panel CMS Panitia Sekolah

### 4.1. Halaman Login Panitia (`pages/cms/auth/login.blade.php`)
- **Rute:** `/admin/login` (atau sub-path tenant)
- **Elemen UI:**
  - Kartu login terpusat dengan logo sekolah dan inisial sekolah.
  - Badge kode identitas sekolah (`school_code`).
  - Input field *Email Panitia* dan *Kata Sandi*.
  - Checkbox *"Ingat saya di perangkat ini"*.
  - Tombol submit *"Masuk ke Panel PPDB"* berwarna emerald.
  - Box pesan error validasi login jika kredensial tidak cocok.

### 4.2. Dashboard Ringkasan Panitia (`pages/cms/dashboard_overview.blade.php`)
- **Rute:** `/admin` (`tenant_route('cms.dashboard')`)
- **Elemen UI:**
  - **Welcome Banner:** Salam pembuka memuat nama panitia yang sedang login, nama sekolah, dan pill indikator status gelombang aktif saat ini.
  - **Grid 8 Indikator Statistik (Metrik Real-time):**
    1. *Total Pendaftar* (semua jalur)
    2. *Terdaftar / Antrean Baru* (warna biru, pendaftar menunggu verifikasi berkas)
    3. *Perlu Perbaikan* (warna amber, berkas tidak jelas/menunggu revisi siswa)
    4. *Berkas Lengkap* (warna emerald, dokumen terverifikasi siap dirangking)
    5. *Diterima* (warna ungu, dinyatakan lulus seleksi)
    6. *Selesai Daftar Ulang* (warna teal, resmi menjadi peserta didik)
    7. *Dibatalkan / Gugur* (warna rose, pendaftar mundur atau gugur syarat)
    8. *Daftar di Loket* (warna indigo, entri pendaftar via meja panitia)
  - **Aksi Cepat Panitia (Quick Action Cards):** Reflow responsif 3-tier:
    - Tombol *Buka Loket Pendaftaran* (pendaftaran offline pendaftar yang hadir ke sekolah).
    - Tombol *Verifikasi Berkas* (langsung menuju tabel filter pendaftar).
    - Tombol *Kelola Periode & Jalur* (pengaturan jadwal pembukaan dan kuota).

### 4.3. Tabel Data & Verifikasi Pendaftar (`pages/cms/applicants_index.blade.php`)
- **Rute:** `/admin/pendaftar` (`tenant_route('cms.applicants.index')`)
- **Elemen UI:**
  - **Header Bar:** Judul modul, counter total pendaftar, dan tombol *"Ekspor CSV"* dengan query filter aktif.
  - **Bilah Filter Pencarian Cepat:**
    - Input teks pencarian (mendukung Nama, Nomor Registrasi, atau NIK).
    - Dropdown filter *Jalur Pendaftaran*.
    - Dropdown filter *Status Pendaftaran*.
    - Tombol *Filter* dan tombol *Reset*.
  - **Tabel Pendaftar Responsif (`min-w-[640px]`):**
    - Kolom *Pendaftar:* Nama lengkap (link ke detail), No. Registrasi monospace, dan NIK.
    - Kolom *Jalur & Gelombang:* Nama jalur dan gelombang pendaftaran.
    - Kolom *Tipe:* Badge `Online` (mandiri) atau `Loket` (walk-in).
    - Kolom *Status:* Komponen toggle switch instan `<x-status_toggle_switch>` untuk mengubah status tanpa perlu reload halaman.
    - Kolom *Aksi:* Tombol *"Periksa"* berkas dan tombol ikon unduh slip PDF resmi.
  - **Pagination Bar:** Navigasi halaman Laravel jika pendaftar melebihi limit per halaman.

### 4.4. Lembar Rincian & Verifikasi Pendaftar (`pages/cms/applicant_detail.blade.php`)
- **Rute:** `/admin/pendaftar/{applicant}`
- **Elemen UI:**
  - **Top Bar:** Tombol kembali, nama pendaftar, badge status saat ini, nomor registrasi, tombol pintas hubungi via WhatsApp, dan tombol cetak bukti pendaftaran.
  - **Status Change Workflow Bar:** Deretan tombol cepat untuk menaikkan/menurunkan status pendaftar:
    - `Terdaftar (Reset)`
    - `✓ Berkas Lengkap`
    - `✓ Diterima`
    - `✓ Selesai Daftar Ulang`
    - `✕ Batalkan`
    - `⚠ Minta Perbaikan Berkas` (membuka modal formulir catatan revisi).
  - **Grid Konten 2 Bagian:**
    - *Bagian Kiri (Biodata):* Data pokok (NIK, Tempat Tanggal Lahir, Jenis Kelamin, No. WA, Asal Registrasi) dan seluruh field kustom tambahan dari formulir builder.
    - *Bagian Kanan (Pemeriksaan Dokumen):* Daftar seluruh berkas digital menggunakan `<x-applicant_doc_row>` yang dilengkapi link pratinjau Google Drive, toggle penerimaan dokumen fisik, tombol persetujuan cepat, dan form penolakan dokumen tertentu.
  - **Modal Catatan Revisi:** Popup Alpine.js untuk menuliskan instruksi yang akan langsung tampil pada halaman cek status calon siswa.

### 4.5. Loket Pendaftaran Langsung di Sekolah / Walk-in (`pages/cms/walk_in_form.blade.php`)
- **Rute:** `/admin/loket` (`tenant_route('cms.walk-in.index')`)
- **Fungsi:** Memudahkan panitia menginput calon siswa yang datang langsung membawa berkas fisik ke meja pendaftaran sekolah.
- **Elemen UI:**
  - **Header Mode Loket:** Indikator loket pendaftaran aktif.
  - **Banner Pendaftaran Terakhir Sukses:** Jika baru saja menginput siswa, kartu hijau sukses muncul dan secara otomatis memicu cetak slip PDF ke tab browser baru (`window.open(slip_url, '_blank')`).
  - **Formulir Entri Cepat:**
    - Pemilih Gelombang Aktif & Jalur Masuk.
    - Identitas calon siswa (Nama, NIK, Jenis Kelamin, Tempat & Tanggal Lahir, No. WA).
    - Isian dinamis kustom sekolah.
    - Checkbox *"Berkas fisik diterima langsung di loket"* (default tercentang, membuat status pendaftar langsung menjadi `verified` tanpa menunggu upload digital).
    - Input opsional foto/scan berkas fisik jika panitia ingin mengarsipkan salinan digital ke Google Drive.
    - Tombol submit *"Daftar di Loket & Cetak Slip"*.

### 4.6. Pengaturan Gelombang Pendaftaran (`pages/cms/period_settings.blade.php`)
- **Rute:** `/admin/gelombang` (`tenant_route('cms.waves.index')`)
- **Elemen UI:**
  - **Header:** Deskripsi pengaturan jadwal dan tombol *"Tambah Gelombang"*.
  - **Tabel Gelombang (`min-w-[640px]`):**
    - Nama gelombang, Tahun Ajaran, Rentang Tanggal Buka s.d. Tutup (WIB), Tanggal Pengumuman.
    - Status: Badge hijau *"Buka"*, badge amber *"Menunggu Jadwal"*, atau abu-abu *"Ditutup / Nonaktif"*.
    - Tombol Edit (membuka modal edit) dan tombol Toggle Aktif/Nonaktifkan.
  - **Modal Tambah/Edit Gelombang:**
    - Nama Gelombang.
    - Tahun Ajaran (contoh: `2026/2027`).
    - Input waktu `datetime-local` responsive: Tanggal & Jam Buka, Tanggal & Jam Tutup.
    - Tanggal Pengumuman (opsional).
    - Checkbox aktifkan gelombang.
    - Tombol Batal & Simpan.

### 4.7. Pengaturan Jalur Masuk & Kuota (`pages/cms/track_settings.blade.php`)
- **Rute:** `/admin/jalur` (`tenant_route('cms.tracks.index')`)
- **Elemen UI:**
  - **Header:** Tombol *"Tambah Jalur"*.
  - **Tabel Jalur Masuk (`min-w-[600px]`):**
    - Nama Jalur, Slug URL, Kuota Siswa, Ketentuan Berkas/Deskripsi, Status Buka/Tutup, dan Aksi.
  - **Modal Tambah/Edit Jalur:**
    - Nama Jalur (Zonasi, Prestasi, Reguler, dll.).
    - Kuota Siswa (angka, kosongkan jika tidak dibatasi).
    - Textarea Persyaratan & Ketentuan Berkas.
    - Checkbox Buka pendaftaran untuk jalur ini.
    - Tombol Batal & Simpan Jalur.

### 4.8. Formulir Builder Dinamis (`pages/cms/form_builder.blade.php`)
- **Rute:** `/admin/form-builder` (`tenant_route('cms.form-builder.index')`)
- **Tujuan:** Memberikan kemandirian penuh kepada sekolah untuk menambah pertanyaan formulir atau syarat berkas tanpa mengubah kode sumber.
- **Elemen UI:**
  - **Action Bar:** Tombol *"Terapkan Template Jenjang"* (membuka `<x-form_preset_modal>`) dan tombol *"Tambah Field"*.
  - **Kelompok Langkah / Bagian Formulir:**
    - Bagian 1: *Biodata Siswa*
    - Bagian 2: *Data Orang Tua / Wali*
    - Bagian 3: *Data Asal Sekolah*
    - Bagian 4: *Informasi Tambahan*
    - Bagian 5: *Unggah Berkas Persyaratan*
  - **Baris Field:** Menampilkan label pertanyaan, field key, badge status wajib/opsional, tipe input, tombol toggle tampil/sembunyi, tombol edit, dan tombol hapus.
  - **Modal Tambah/Edit Field:**
    - Pilihan kelompok langkah.
    - Pilihan tipe input: Teks Singkat, Angka, Pilihan Menu (Select), Pilihan Opsi (Radio), Centang (Checkbox), Teks Panjang (Textarea), Tanggal (Date), Unggah Berkas (File Upload).
    - Label Pertanyaan.
    - Kunci Identitas unik (*field key*).
    - Opsi pilihan (jika tipe select/radio/checkbox).
    - Checkbox Wajib Diisi.

### 4.9. Pengaturan Integrasi Google Drive Sekolah (`pages/cms/google_drive_settings.blade.php`)
- **Rute:** `/admin/google-drive` (`tenant_route('cms.gdrive.index')`)
- **Prinsip Non-SaaS Lock-in:** Seluruh file pendaftar (KK, Akta, Rapor) diarsipkan langsung ke Google Drive resmi milik sekolah (Google Workspace for Education atau akun Google sekolah) via OAuth2.
- **Elemen UI:**
  - **Status Koneksi:** Badge hijau *"Terhubung"* atau badge amber *"Belum Terhubung"*.
  - **Kondisi Terhubung:**
    - Alamat email Google sekolah yang terhubung (dengan `break-all`).
    - Status siap menerima berkas pendaftar baru.
    - Informasi struktur direktori otomatis: `/PPDB_{TAHUN}/{NO_REG}_{NAMA}/...`
    - Tombol *"Putus Hubungan Akun"* dengan konfirmasi keamanan.
  - **Kondisi Belum Terhubung:**
    - Peringatan bahwa berkas belum terarsip otomatis ke cloud sekolah.
    - Tombol hijau *"Hubungkan Akun Google Drive Sekolah"* (menginisiasi alur OAuth2 resmi Google).
  - **Catatan Keamanan & Privasi Data:** Penjelasan bahwa token akses dienkripsi ketat di database dan hak akses dibatasi hanya untuk folder PPDB sekolah.

---

## 5. Panel Superadmin Platform

### 5.1. Login Superadmin (`pages/superadmin/login.blade.php`)
- **Rute:** `/superadmin/login`
- **Elemen UI:** Desain gelap berlatar belakang glassmorphism (`bg-slate-950/70 backdrop-blur-xl`), input email superadmin & kata sandi, checkbox ingat perangkat, dan tombol masuk dengan aksen gradient indigo-violet.

### 5.2. Dashboard Superadmin Platform (`pages/superadmin/dashboard.blade.php`)
- **Rute:** `/superadmin`
- **Elemen UI:**
  - Metrik global: Total Sekolah Aktif, Total Gelombang Dibuka, Total Seluruh Pendaftar se-Platform, Total Berkas Terverifikasi, dan Rasio Sekolah dengan Google Drive Terhubung.
  - Tabel Monitoring Seluruh Tenant Sekolah:
    - Nama & kode unik sekolah.
    - Status gelombang pendaftaran yang sedang aktif.
    - Status integrasi Google Drive masing-masing sekolah.
    - Jumlah pendaftar per sekolah.
    - Tombol aksi superadmin: Akses langsung ke CMS sekolah terkait.

---

## 6. Pustaka Komponen UI Reusable (Blade Components)

| Komponen | File | Fungsi & Karakteristik UI |
| :--- | :--- | :--- |
| **`applicant_doc_row`** | `components/applicant_doc_row.blade.php` | Menampilkan baris berkas di panel verifikasi panitia. Dilengkapi link pratinjau Google Drive di tab baru, tombol toggle berkas fisik (`+ Terima Fisik` / `✓ Fisik Diterima`), tombol `Setujui`, tombol `Minta Perbaikan`, serta form input catatan penolakan spesifik. |
| **`applicant_status_badge`** | `components/applicant_status_badge.blade.php` | Badge pill visual status siswa dengan warna terstandardisasi: Terdaftar (biru), Perlu Perbaikan (kuning), Berkas Lengkap (hijau), Diterima (ungu), Selesai (teal), Dibatalkan (merah). |
| **`dynamic_field`** | `components/dynamic_field.blade.php` | Komponen polimorfik yang merender input HTML sesuai definisi field builder (text, number, select, radio, checkbox, textarea, date, file) lengkap dengan label, tanda bintang wajib, helper text, dan penanganan error validasi Laravel. |
| **`form_preset_modal`** | `components/form_preset_modal.blade.php` | Dialog modal untuk menyalin struktur formulir standar sesuai jenjang sekolah secara instan: PAUD/TK, SD/MI, SMP/MTs, dan SMA/SMK. |
| **`registration_stepper`** | `components/registration_stepper.blade.php` | Stepper visual 4 tahap pada formulir pendaftaran calon siswa dengan reflow mobile yang ramah layar kecil. |
| **`registration_wizard_nav`** | `components/registration_wizard_nav.blade.php` | Bilah navigasi tombol *Sebelumnya*, *Lanjutkan*, dan *Kirim Pendaftaran* dengan target sentuh aman (&ge; 44px). |
| **`status_toggle_switch`** | `components/status_toggle_switch.blade.php` | Dropdown pill berbasis AJAX yang memungkinkan panitia mengubah status pendaftar secara langsung dari tabel tanpa reload halaman, dilengkapi indikator loading spinner saat data disimpan. |

---

## 7. Desain Cetak Dokumen & PDF

### 7.1. Kartu Tanda Bukti Pendaftaran Resmi (`pdf/applicant_card_slip.blade.php`)
- **Teknologi Render:** DomPDF (Format A4 / Folio formal).
- **Elemen Dokumen:**
  - **Kop Surat Resmi:** Nama sekolah kapital tebal berwarna toska (`#0f766e`), sub-judul Panitia PPDB Online, tahun ajaran, dan logo/inisial sekolah.
  - **Judul Dokumen:** *"KARTU TANDA BUKTI PENDAFTARAN"*.
  - **Kotak Nomor Registrasi:** Kotak berbingkai garis putus-putus hijau (*dashed box*) memuat nomor registrasi resmi dengan ukuran 15pt font monospace tebal dan timestamp pencatatan sistem.
  - **Tabel Data Calon Siswa:**
    - Nama Lengkap Siswa
    - NIK (Nomor Kependudukan)
    - Tempat, Tanggal Lahir
    - Jenis Kelamin
    - Jalur Pendaftaran & Gelombang
    - Nomor WhatsApp Kontak
    - Sekolah Asal & Data Tambahan
  - **Catatan Penting Calon Siswa:** Panduan penyimpanan kartu, jadwal pengumuman seleksi, dan nomor kontak panitia.
  - **Footer & Keabsahan:**
    - **QR Code Resmi:** Barcode QR 2D yang memuat tautan verifikasi keaslian kartu secara online.
    - Kolom tanda tangan & stempel panitia penerimaan siswa baru.

---

## 8. Ringkasan Matriks Responsivitas Antarmuka

| Komponen / Tampilan | Small Phone (&le; 360px) | Mobile Phone (375px - 639px) | Tablet (640px - 1023px) | Desktop (&ge; 1024px) |
| :--- | :--- | :--- | :--- | :--- |
| **Navbar Header** | Logo + inisial + tombol Cek Status | Nama sekolah truncated | Nama sekolah utuh + subjudul | Tata letak leluasa + container max 5xl |
| **Formulir Wizard** | Stepper angka + label mini, 1 kolom | Label lengkap, input 1 kolom | Grid 2 kolom pada NIK/Gender/TTL | Kontainer 2xl terpusat |
| **Tabel Data CMS** | Scroll horizontal (`min-w-[640px]`) | Scroll horizontal (`min-w-[640px]`) | Terbaca lapang | Tampil penuh tanpa scroll |
| **Action Buttons** | `w-full` vertikal bertumpuk | `w-full sm:w-auto` | Berdampingan rapi | Berdampingan dengan gap nyaman |
| **Modal Dialog** | Lebar 100% layar (padding 4) | Lebar 100% layar | Terpusat `max-w-md` / `max-w-lg` | Terpusat berbayang elegan |

---
*Katalog ini disusun sebagai referensi resmi desain dan implementasi seluruh antarmuka pengguna pada sistem PPDB Multi-Tenant.*
