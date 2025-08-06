# Dokumentasi Ultimate Website

## 1. Setup & Instalasi
- Pastikan PHP, PostgreSQL, Composer, dan web server (Apache/Nginx) sudah terinstall.
- Clone/copy source code ke server.
- Import database: `database_schema_postgres.sql` dan `add_logs_table.sql`.
- Konfigurasi koneksi database di `db.php`.
- Install dependency: `composer install` (untuk PHPMailer, dsb).
- (Opsional) Konfigurasi SMTP di `send_email.php`.

## 2. Fitur Utama
- **User, Customer, Project, Activity Browser**: CRUD data, search/filter, export Excel, pagination, CSRF protection.
- **Dashboard Analitik**: Statistik, grafik tren, notifikasi aktivitas terbaru, statistik performa.
- **Audit Log**: Semua aktivitas penting tercatat di `log_view.php`.
- **Notifikasi Email**: Otomatis ke admin untuk event penting (user baru, project selesai, activity overdue).
- **API Endpoint**: Expose data user, project, activity (GET/POST) untuk integrasi n8n/otomasi.
- **Backup Database**: Script `backup_database.sh` untuk backup manual.
- **Multi-language**: Dukungan Indonesia/English (lihat `language.php`).
- **Mobile Friendly**: Layout responsif di semua device.
- **Monitoring & Health Check**: Endpoint `health.php` untuk monitoring uptime.

## 3. Hak Akses (Role)
- **Administrator**: Full access (CRUD semua modul, lihat log).
- **Management**: Hanya view semua modul, lihat log.
- **Admin Office**: CRUD customer/project/activity, view user.
- **User/Client**: Hanya view data sendiri.
- Mapping detail: lihat `access_control.php`.

## 4. Integrasi API/n8n
- Endpoint API: `api_user.php`, `api_project.php`, `api_activity.php`, `api_activity_post.php`.
- Gunakan HTTP Request node di n8n untuk GET/POST data.
- (Opsional) Aktifkan autentikasi token di endpoint API.

## 5. FAQ & Troubleshooting
- **Tidak bisa login**: Cek koneksi database, rate limiting login, dan error log.
- **Email tidak terkirim**: Cek konfigurasi SMTP di `send_email.php`.
- **Backup gagal**: Pastikan pg_dump sudah di PATH dan user/password benar.
- **Akses ditolak**: Cek role user dan mapping di `access_control.php`.

## 6. Bantuan Lain
- Lihat file `docs.md` ini untuk update terbaru.
- Hubungi admin IT untuk reset password atau masalah akses.
