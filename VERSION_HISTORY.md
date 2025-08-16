# VERSION HISTORY

## v2.4.8 (2025-01-XX)
- **Gantt Chart View Enhancement:**
  - Menambahkan header dan filter section yang lengkap seperti Activity List view
  - Header section dengan dropdown "Show" untuk memilih jumlah item (10, 15, 20, 50, 100)
  - Filter section lengkap: Search, Priority, Department, Application, Type, Status
  - Real-time search dengan debounce 300ms
  - Filter logic untuk semua kriteria dengan dynamic rendering
  - Meningkatkan tinggi kolom header timeline dari h-12 (48px) ke h-20 (80px)
  - Mengganti label "DESKRIPSI TUGAS" menjadi "Description"
  - Menyesuaikan tinggi cell tanggal dan task cell agar konsisten
  - Menghapus tombol "Create Activity" dari Gantt Chart view
  - Menambahkan CSS untuk wrap-toggle, quick-edit-btn, dan styling lainnya
  - Integrasi filter dengan Gantt chart rendering

- **Activity List View UI Improvement:**
  - Memindahkan tombol "Create Activity" dari header ke filter section
  - Posisi baru: Apply Filters → Reset → Add Activity
  - Menyamakan styling tombol Add Activity dengan Apply Filters (gradient ungu)
  - Menghapus icon plus (+) dari tombol Add Activity
  - Layout yang lebih terorganisir dan konsisten

- **Technical Improvements:**
  - Filter functionality menggunakan JavaScript dengan real-time updates
  - Responsive design untuk semua view (List, Kanban, Gantt)
  - Dark mode support yang konsisten
  - Performance optimization dengan debounced search
  - Clean code structure dengan proper separation of concerns

## v2.4.7 (2025-08-13)
- Gantt (dark/light):
  - Toggle tema sepenuhnya sinkron dengan global `html[data-theme]` (hapus atribusi `body`/cookie), dan re-render saat tema berubah.
  - Footer tone & border-top diseragamkan dengan Activity; hilangkan perbedaan garis antar halaman.
  - Wrap Description toggle di-scope (`#gantt-root`) agar tidak bertabrakan dengan toggle lain.
  - Quick Edit: kontras dark mode ditingkatkan (background pekat, border jelas, shadow kuat, focus ring biru), tombol update pakai gradient brand.
  - Warna bar status pakai gradient + shadow: Open, On Progress, Need Requirement, Done, Cancel. Khusus Done di dark diberi outline tipis agar lebih “angkat”.
- Kanban: mengikuti tone wrapper global agar konsisten saat dark.

## v2.4.6 (2025-08-13)
- Gantt: penyelarasan tampilan agar seragam dengan List/Kanban
  - Header: pindahkan keterangan bulan ke tengah; rapikan toolbar; hilangkan pinggiran terang (sinkronisasi background global dan card wrapper).
  - “Wrap Description” dipindah ke atas tombol “Hari Ini”; toggle diperkecil; tambah badge ON/OFF; preferensi disimpan (localStorage); aksesibilitas (role switch, aria-checked, keyboard).
  - Penanda “Hari Ini”: highlight pada header dan grid, plus garis vertikal tipis di kolom hari ini.
  - Warna bar mengikuti status seperti List View: Open (oranye), On Progress (biru), Need Requirement (ungu), Done (hijau), Cancel (merah).
  - Perbaikan: `Need Requirement` sebelumnya abu-abu → kini ungu sesuai desain.
- Kanban: header/kerangka diseragamkan dengan List; tone kolom diredupkan di dark mode agar konsisten.


## v2.4.5 (2025-08-12)
- Tambah `Gantt Chart` view (`activity_gantt.php`) dengan sumber data langsung dari tabel `activities` (no, description, status, type, priority, information_date → start, due_date → end) dan grouping per Type.
- Persist tanggal hasil drag/resize: optimasi only-diff + debounce (400ms) + batch ke endpoint baru `update_activity_dates_batch.php` (transaksi DB). Aturan tetap: `due_date >= information_date`.
- Quick Edit dari Gantt: modal kecil untuk ubah `status`, `priority`, `type` via endpoint baru `update_activity_fields.php` (whitelist & partial update).
- Integrasi notifikasi di bawah logo untuk sukses/gagal (helper `assets/js/activity-notifications.js` + guard anti double-load).
- Penyesuaian dark theme pada Gantt (background, border, teks, weekend, bar shadow).
- Navigasi: tombol “Gantt Chart” ditambahkan pada `activity.php` dan `activity_kanban.php`.
# Version History - Ultimate Website

## Version 2.4.3 - Auth UX Polish: Ripple Login, Favicon, Scenic Backgrounds
**Date:** August 2025

### ✨ UX Improvements
- Login sukses: animasi ripple overlay dengan redirect otomatis setelah animasi selesai (durasi saat ini: 1.5s, sinkron via `animationend`).
- Favicon/Tab icon ditambahkan pada halaman auth (`login.php`, `forgot-password.php`, `reset-password.php`).
- Background pemandangan dikembalikan dan dipoles: setiap waktu (pagi/siang/sore/malam) memakai foto landscape + overlay gradient agar teks tetap terbaca.
- Kompatibel dengan dark/light theme yang sudah ada; overlay mengikuti `data-theme`.

### 📄 Files Touched
- `login.php`
- `forgot-password.php`
- `reset-password.php`
- `assets/css/login-backgrounds.css`

---

## Version 2.4.2 - Password Reset Fixes, User Schema Alignment, UI Polish
**Date:** August 2025

### ✅ Fixes & Changes
- Forgot Password: tampilkan error eksplisit jika email tidak terdaftar.
- Reset Password: perbaikan query agar kompatibel schema baru (gunakan `user_id`, hilangkan ketergantungan `display_name`).
- Konsistensi `users`: ganti referensi `id` → `user_id` di beberapa file terkait reset/login & utility.
- UI: hilangkan ikon “mata” ganda pada input password (Edge/IE/varian webkit) di login dan reset password; hanya satu ikon toggle custom di kanan.
- Cleanup: hapus file test/debug yang tidak digunakan agar tidak mengganggu produksi.

### 📄 Files Touched
- `forgot-password.php`, `reset-password.php`, `login.php`
- Utility/debug yang dirapikan atau dihapus

---

## Version 2.4.1 - Kanban Edit Modal Parity & Bug Fixes
**Date:** August 2025

### 🎨 UI/UX Alignment
- Menyamakan modal Edit Activity pada Kanban dengan List View (lebar, grid 2 kolom, header gradient, dark mode).
- Menyeragamkan tombol footer: urutan di kanan (Update, Close), padding `10px 16px`, radius `8px`, dan warna sesuai tema.
- Menyamakan bentuk tombol agar identik dengan list (bukan kapsul; sudut 8px, bayangan lembut).

### 🐛 Bug Fixes
- Memperbaiki error update (HTTP 500) saat `Completed Date` kosong dengan menyimpan sebagai `NULL` di database.
- Meningkatkan validasi dan error handling pada submit (logging dan feedback UI).
- Memastikan pemetaan field edit sesuai activity detail (application, type, customer, project, due_date, cnc_number, action_solution).

### 📄 Files Touched
- `activity_kanban.php` (modal markup, JS handling, CSS modal & tombol)
- `get_activity.php` (penyesuaian key response `data`)
- `update_activity.php` (handling `due_date` kosong → `NULL`, update kolom terkait)
- `assets/css/theme-override.css` (menggunakan variabel tema yang sudah ada)
- File uji: `test_kanban_simple.html`, `test_kanban_edit.html`

---

## Version 2.4.0 (Current) - Complete UI/UX Standardization and Database Schema Refactoring
**Date:** December 2024  
**Commit:** e41e55d

### 🎯 Major Features
- **Complete UI/UX Standardization**: Standardized Project List and User List to match Activities List View
- **Database Schema Refactoring**: Major changes to users table structure and relationships
- **Modal-Based Interactions**: Implemented consistent modal system across all list views
- **File Consolidation**: Streamlined user and project management into single pages

### 🔄 File Changes
#### Renamed Files
- `project_crud.php` → `project.php`
- `users-grid.php` → `users.php`

#### Deleted Files
- `add-user-form.php` - Consolidated into users.php
- `user_crud.php` - Consolidated into users.php
- `project_crud.php` - Renamed to project.php

#### New Files
- `project.php` - New standardized project list view
- `users.php` - New standardized user list view
- Database sync check scripts for validation
- Database migration scripts for schema changes

### 🎨 UI/UX Improvements
#### Project List View
- Removed action buttons, implemented row-click editing
- Added custom modal for editing projects
- Standardized header styling with `.table-header` class
- Implemented consistent filter section (search, status, type)
- Added pagination with "Show per page" dropdown
- Applied gradient styling and hover effects

#### User List View
- Converted from grid to table layout
- Implemented row-click editing with custom modal
- Added "Add New User" modal directly on page
- Standardized column widths and styling
- Removed "Join Date" column
- Added consistent filter section (search, role, tier)

#### Modal System
- Custom modal implementation matching activity.php style
- Consistent visual design across all modals
- ESC key and backdrop click dismissal
- Form validation and error handling

### 🗄️ Database Schema Changes
#### Users Table
- **Primary Key Change**: `display_name` became primary key (renamed to `user_id`)
- **Column Removal**: `id` column completely removed
- **Data Type Updates**: Foreign key columns updated to VARCHAR for compatibility
- **Constraint Updates**: All foreign key relationships updated

#### Migration Process
1. Added temporary VARCHAR columns to dependent tables
2. Migrated data from old integer references
3. Dropped old foreign key constraints
4. Updated users table structure
5. Recreated foreign key constraints
6. Removed temporary columns

### 🔧 Technical Improvements
#### PHP Backend
- Updated all database queries to use `user_id` instead of `id`
- Implemented proper ENUM handling for PostgreSQL
- Added auto-migration for new database columns
- Enhanced error handling and validation

#### JavaScript
- Fixed syntax errors in activity-notifications.js
- Implemented robust modal event handling
- Added form submission handling
- Enhanced user interaction feedback

#### Navigation Updates
- Updated sidebar links to reflect file renames
- Fixed profile photo queries in layout files
- Updated navbar profile links

### 🚀 Performance & Security
- **CSRF Protection**: Maintained across all forms
- **Database Efficiency**: Optimized queries with proper indexing
- **Session Management**: Enhanced login and profile handling
- **Input Validation**: Improved form validation and sanitization

### 📱 Responsive Design
- Maintained dark mode compatibility
- Consistent styling across all viewports
- Enhanced mobile interaction patterns

### 🧪 Testing & Validation
- Created comprehensive database sync check scripts
- Validated all foreign key relationships
- Tested modal functionality across different scenarios
- Verified form submission and data persistence

### 🔗 Dependencies
- **Database**: PostgreSQL with ENUM support
- **Frontend**: Bootstrap 5, Custom CSS, Vanilla JavaScript
- **Backend**: PHP 8+, PDO with PostgreSQL driver

### 📋 Migration Notes
- **Backup Required**: Full database backup before migration
- **Downtime**: Minimal downtime during schema changes
- **Data Integrity**: All existing data preserved and migrated
- **Rollback**: Migration scripts include rollback procedures

### 🎉 What's New
1. **Consistent User Experience**: All list views now have the same look and feel
2. **Improved Workflow**: Modal-based editing eliminates page navigation
3. **Better Data Management**: Streamlined user and project operations
4. **Enhanced Security**: Improved validation and error handling
5. **Modern Interface**: Gradient styling and smooth interactions

### 🐛 Bug Fixes
- Fixed modal freezing and display issues
- Resolved database schema mismatches
- Corrected form submission problems
- Fixed JavaScript syntax errors
- Resolved foreign key constraint issues

### 📚 Documentation Updates
- Updated `FUNCTIONALITY_STATUS.md`
- Updated `REQUIRE_LOGIN_FIX.md`
- Created comprehensive version history
- Added database sync check documentation

---

## Previous Versions

### Version 2.3.0
- Kanban view improvements
- Activity management enhancements

### Version 2.2.2
- List view consistency improvements
- Header styling standardization

### Version 2.2.1
- Bug fixes and minor improvements

### Version 2.2.0
- Major UI consistency updates
- Dark mode improvements

### Version 2.1.0
- Activity management features
- User interface enhancements

### Version 2.0.0
- Foundation for modern UI
- Basic functionality implementation

### Version 1.1.0
- Initial project setup
- Basic website structure

---

## Next Steps
- Monitor database performance after schema changes
- Gather user feedback on new modal interactions
- Consider additional UI/UX enhancements
- Plan future feature development

## Support
For issues or questions related to this version, please refer to the commit history or contact the development team.