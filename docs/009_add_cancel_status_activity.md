# Penambahan Status Cancel pada Activity List

## Overview
Dokumen ini menjelaskan penambahan status "Cancel" pada sistem Activity List untuk memberikan opsi status yang lebih lengkap dalam manajemen aktivitas.

## Tanggal Implementasi
Juli 2025

## Deskripsi Perubahan
Status "Cancel" telah ditambahkan ke dalam sistem Activity List sebagai opsi status kelima, melengkapi status yang sudah ada:
- Open (default)
- On Progress  
- Need Requirement
- Done
- Cancel

## File yang Dimodifikasi

### 1. `activity_crud.php`
- **Filter Status Dropdown**: Menambahkan opsi "Cancel" pada filter status
- **Create Modal**: Menambahkan opsi "Cancel" pada dropdown status saat membuat aktivitas baru
- **Edit Modal**: Menambahkan opsi "Cancel" pada dropdown status saat mengedit aktivitas
- **Badge Styling**: Menambahkan styling badge `danger` untuk status "Cancel"
- **Filter Logic**: Memperbarui filter "not_done" untuk mengecualikan status "Done" dan "Cancel"

### 2. `database_schema.sql`
- **ENUM Update**: Memperbarui ENUM status pada tabel activities untuk menyertakan "Cancel"

### 3. `database_schema_postgres.sql`
- **ENUM Update**: Memperbarui ENUM activity_status untuk menyertakan "Cancel"

### 4. `ACTIVITY_TABLE_FEATURES.md`
- **Documentation Update**: Memperbarui dokumentasi fitur untuk menyertakan status "Cancel"

## Detail Implementasi

### Badge Styling
Status "Cancel" menggunakan badge dengan class `badge-danger` untuk memberikan indikasi visual yang jelas bahwa aktivitas telah dibatalkan.

### Filter Logic
Filter "Active (Default)" sekarang mengecualikan status "Done" dan "Cancel", sehingga hanya menampilkan aktivitas yang masih aktif:
```sql
WHERE a.status NOT IN ('Done', 'Cancel')
```

### Database Schema
Perubahan pada schema database memastikan bahwa status "Cancel" dapat disimpan dan diambil dengan benar dari database.

## Testing
- [ ] Status "Cancel" muncul di semua dropdown status
- [ ] Filter "Active (Default)" berfungsi dengan benar (tidak menampilkan status Done dan Cancel)
- [ ] Badge styling untuk status "Cancel" menggunakan warna yang sesuai
- [ ] Status "Cancel" dapat disimpan dan diambil dari database

## Breaking Changes
Tidak ada breaking changes. Perubahan ini bersifat additive dan tidak mempengaruhi fungsionalitas yang sudah ada.

## Dependencies
- Database schema harus diupdate untuk mendukung ENUM status yang baru
- Jika menggunakan PostgreSQL, ENUM type harus diupdate

## Migration Notes
Untuk database yang sudah ada, perlu menjalankan migration untuk menambahkan status "Cancel":

### Automated Migration (Recommended)
Gunakan script `run_migration.php` yang akan mendeteksi tipe database secara otomatis:
```bash
php run_migration.php
```

### Manual Migration
**MySQL:**
```sql
ALTER TABLE activities MODIFY COLUMN status ENUM('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel');
```

**PostgreSQL:**
```sql
ALTER TYPE activity_status ADD VALUE 'Cancel';
```

### Migration Files
- `migration_add_cancel_status.sql` - Script PostgreSQL
- `migration_add_cancel_status_mysql.sql` - Script MySQL
- `run_migration.php` - Script otomatis (deteksi tipe database)
- `README_MIGRATION.md` - Panduan lengkap migration

## Troubleshooting

### Issue: Status "Cancel" tidak muncul di dropdown
**Solution**: Pastikan database schema sudah diupdate dan refresh halaman

### Issue: Filter "Active (Default)" tidak berfungsi
**Solution**: Periksa apakah filter logic sudah diupdate untuk mengecualikan status "Cancel"

### Issue: Badge styling tidak sesuai
**Solution**: Pastikan CSS class `badge-danger` tersedia dan terdefinisi dengan benar

### Issue: Database Error - Invalid ENUM value
**Error**: `SQLSTATE[22P02]: Invalid text representation for enum activity_status: "Cancel"`
**Solution**: Jalankan migration script untuk menambahkan value "Cancel" ke ENUM database

### Issue: Migration Script Gagal
**Solution**: 
1. Periksa kredensial database di `run_migration.php`
2. Pastikan user database memiliki privilege ALTER
3. Jalankan migration manual sesuai tipe database yang digunakan

### Issue: PostgreSQL Version Compatibility
**Solution**: Script migration otomatis akan mencoba metode alternatif untuk versi PostgreSQL yang lebih lama

## Referensi
- Template dokumentasi: `docs/000_TEMPLATE.md`
- File utama: `activity_crud.php`
- Database schema: `database_schema.sql`, `database_schema_postgres.sql`
