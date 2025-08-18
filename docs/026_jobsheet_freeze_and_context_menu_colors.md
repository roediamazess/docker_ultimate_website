## 026 - Jobsheet: Perbaikan Freeze Header Tanggal & Warna Menu Klik Kanan

- Perbaiki freeze baris tanggal agar konsisten saat scroll dengan menghitung tinggi header bulan secara dinamis dan menyetel `top` baris tanggal via JavaScript.
- Samakan warna teks item menu klik kanan dengan warna tanggal, namun warna khusus tetap dipertahankan untuk status penting:
  - On Time: hijau
  - Late: merah
  - Approve: biru
  - Re-Open: kuning
- Hapus zebra striping pada baris body; seluruh baris menggunakan latar putih (dark mode tetap didukung).
- Tambahkan pemanggilan pembaruan posisi sticky saat tabel dibuat, saat `resize`, dan saat halaman `load`.

Dampak: Header tanggal tidak lagi bergeser saat scroll, UI menu lebih konsisten, dan tampilan tabel lebih bersih.

