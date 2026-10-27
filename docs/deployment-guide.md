# Panduan Deployment & Operasional Produksi PPDB Monolith

Dokumen ini memandu proses rilis dan operasional sistem monolit PPDB multi-tenant pada server produksi (Fase P12).

---

## 1. Kebutuhan Sistem Minimum

- **Sistem Operasi**: Ubuntu 22.04 / 24.04 LTS (atau Debian 12)
- **Web Server**: Nginx 1.22+ (mendukung SNI / Wildcard SSL)
- **PHP**: PHP 8.2 atau 8.3 dengan ekstensi:
  - `ext-pdo_mysql`
  - `ext-redis`
  - `ext-gd` (untuk kompresi dokumen Intervention Image)
  - `ext-bcmath`, `ext-ctype`, `ext-fileinfo`, `ext-mbstring`, `ext-openssl`, `ext-tokenizer`, `ext-xml`
- **Redis Server**: Redis 6.0+ (digunakan untuk antrean background `documents`, session, dan caching)
- **Process Manager**: Supervisor
- **Database**: MariaDB 10.6+ / MySQL 8.0+ (`saas_core`)

---

## 2. Konfigurasi Environment (`.env`)

Salin template `.env.example` ke `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Parameter produksi penting:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ppdb.bitserver.cloud

# Database terhubung ke saas_core
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_core
DB_USERNAME=ppdb_db_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

# Antrean & Cache berbasis Redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Multi-Tenancy & Custom Domain
PPDB_CENTRAL_DOMAIN=ppdb.bitserver.cloud

# Google Drive OAuth Platform Credentials (OAuth Client App)
GOOGLE_DRIVE_CLIENT_ID=your-google-oauth-client-id
GOOGLE_DRIVE_CLIENT_SECRET=your-google-oauth-client-secret
# (Opsional) Fallback global jika ada sekolah yang belum connect
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_ROOT_FOLDER_ID=root
```

> [!NOTE]
> **Model BYOD (Bring Your Own Drive):** Nilai `GOOGLE_DRIVE_CLIENT_ID` dan `GOOGLE_DRIVE_CLIENT_SECRET` berlaku global sebagai OAuth Client App. Setiap sekolah menghubungkan akun Google Workspace for Education / Gmail sekolah masing-masing secara mandiri melalui menu **Pengaturan Google Drive** (`/cms/settings/google-drive`) di panel CMS. Token disimpan terenkripsi di tabel `schools`.

---

## 3. Eksekusi Migrasi Database & Seeding Pilot

Jalankan migrasi pada database `saas_core`. Tabel migrasi sistem PPDB diisolasi pada `ppdb_migrations` sehingga aman dari benturan dengan CMS sekolah utama:

```bash
# Jalankan migrasi
php artisan migrate --force

# (Opsional) Inisialisasi sekolah percontohan pertama
php artisan db:seed --class=PpdbDemoSeeder --force
```

---

## 4. Konfigurasi Worker (Supervisor)

Buat file `/etc/supervisor/conf.d/ppdb-worker.conf`:

```ini
[program:ppdb-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ppdb/artisan queue:work redis --queue=documents,default --sleep=3 --tries=3 --timeout=120 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/ppdb/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan konfigurasi worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ppdb-worker:*
```

---

## 5. Konfigurasi Crontab (Pembersihan File Orphan)

Jadwalkan cron job Laravel untuk membersihkan file staging sementara (`temp_ppdb`) yang berusia lebih dari 7 hari:

```bash
crontab -e -u www-data
```

Tambahkan baris berikut:

```cron
* * * * * cd /var/www/ppdb && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Konfigurasi Web Server (Nginx)

Nginx menangani domain pusat (`ppdb.bitserver.cloud`) dan domain kustom sekolah (misal: `ppdb.sman1jkt.sch.id`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name ppdb.bitserver.cloud *.sch.id;

    root /var/www/ppdb/public;
    index index.php index.html;

    client_max_body_size 15M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 7. Prosedur Uji Asap (Smoke Testing)

Setelah proses rilis selesai, verifikasi 4 alur kunci berikut:
1. **Pendaftaran Mandiri Online**: Akses `https://ppdb.bitserver.cloud/{school_code}` $\rightarrow$ isi wizard $\rightarrow$ pastikan berkas tersimpan dan slip PDF terunduh otomatis.
2. **Pengecekan Status Mandiri**: Buka `/cek-status` $\rightarrow$ masukkan nomor registrasi dan tanggal lahir $\rightarrow$ verifikasi tampilan status pendaftar.
3. **Pendaftaran di Loket**: Login panitia `/cms/login` $\rightarrow$ buka menu *Daftar di Loket* $\rightarrow$ simpan pendaftar walk-in $\rightarrow$ pastikan slip PDF loket tercetak.
4. **Verifikasi & Ekspor Data**: Ubah status pendaftar menjadi *Berkas Lengkap* $\rightarrow$ klik tombol *Ekspor CSV* $\rightarrow$ pastikan file terunduh rapi.
