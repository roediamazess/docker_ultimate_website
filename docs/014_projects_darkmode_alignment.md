# 014_projects_darkmode_alignment

Date: 2025-08-14

## Ringkasan
Menyelaraskan tampilan halaman `projects.php` dengan referensi Activities List, terutama untuk dark mode agar tidak terlalu kontras, sekaligus merapikan letak tombol Add Project dan meningkatkan UX modal.

## Perubahan Utama
- Status tabs: urutan dan style seragam, default filter Running.
- Filter section: warna latar, border, input/select, focus state dan tombol Reset disesuaikan untuk dark mode.
- Table header chip (`.table-header`): gradient gelap sama dengan Activities.
- Detail baris ("PIC: …"): gradient lembut, border halus; dark mode parity.
- Hapus tombol Create Project di header/tab bar; tambahkan tombol "Add Project" di bar filter (sebelah Reset).
- Modal Add/Edit:
  - Dark mode alignment (background, border, footer, disabled input legible).
  - Dapat ditutup dengan ESC dan klik overlay.

## File Terdampak
- `projects.php`
- `VERSION_HISTORY.md` (tambahan entri v2.4.8)

## Catatan Implementasi
- CSS spesifik dark mode menggunakan selector `html[data-theme="dark"]` untuk konsistensi global.
- Tidak ada perubahan pada endpoint/backend; hanya UI/UX.

## Troubleshooting
- Jika dark mode tidak aktif, pastikan `data-theme="dark"` pada elemen `html` dan tidak ada override inline lain yang menimpa.
- Jika modal tidak bisa ditutup dengan ESC, pastikan tidak ada event listener lain yang `preventDefault()` pada `keydown`.

## Breaking Changes
Tidak ada.


