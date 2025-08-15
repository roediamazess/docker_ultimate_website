# 015 - Navbar Floating Capsule & Notification Positioning

Date: 2025-08-15

## Ringkasan
Mengubah navbar horizontal menjadi kapsul floating (fixed) dengan offset dari atas, menghilangkan efek glass di area luar, dan menyamakan background seluruh area dari logo hingga user menu. Logo disejajarkan vertikal simetris, dan sistem notifikasi diposisikan dinamis tepat di bawah logo kapsul.

## Perubahan Utama
- Navbar fixed dengan variabel `--nav-height` dan `--nav-offset`.
- Kapsul `.nav-surface` ber-radius besar, warna solid (light/dark), shadow hanya saat scroll.
- Area menu `.nav-menu` dibuat transparan agar menyatu dengan kapsul.
- Logo center vertikal; tinggi mengikuti `calc(var(--nav-height) - (var(--logo-vpad) * 2))`.
- Notifikasi (`#notification-container`, `.notification-stack`) diposisikan di bawah logo, update saat `scroll` dan `resize`.

## File yang Diubah
- `partials/layouts/layoutHorizontal.php`
- `README.md` (penyesuaian kecil deskripsi navbar)

## Detail Implementasi
- CSS inline di `layoutHorizontal.php`:
  - Menambah `:root { --nav-height: 72px; --nav-offset: 24px; --logo-vpad: 12px; }`
  - `.horizontal-navbar` transparan total, tanpa backdrop-filter, tanpa shadow.
  - `.nav-surface` sebagai kapsul dengan border halus dan background solid; backdrop-filter dimatikan.
  - `.nav-menu` di dalam kapsul dibuat transparan (tanpa background/border/shadow).
  - Padding top konten disesuaikan `calc(var(--nav-height)+var(--nav-offset)+12px)`.
- JS kecil di `layoutHorizontal.php`:
  - Menambah shadow pada kapsul saat scroll (class `.is-scrolled`).
  - Memposisikan ulang notifikasi berdasarkan posisi logo (`getBoundingClientRect`).

## Troubleshooting
- Jika masih terlihat garis/glass di bar luar: pastikan tidak ada CSS eksternal yang mengatur `.horizontal-navbar` (misal `assets/css/horizontal-layout.css`). Override sudah ditambahkan, tapi bila perlu, nonaktifkan import CSS terkait.
- Jika notifikasi tidak tepat berada di bawah logo pada layar kecil: periksa elemen `#companyLogo` dan pastikan tidak tersembunyi oleh transform lain; sesuaikan offset pada script (default 12px).

## Breaking Changes
Tidak ada. Struktur HTML navbar tetap; hanya wrapper kapsul yang ditambahkan di sekitar konten nav.

## Catatan Rilis
Tercatat di `VERSION_HISTORY.md` sebagai v2.4.9.


