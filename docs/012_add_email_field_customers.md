# Penambahan Field Email pada Customer

## Overview
Dokumen ini menjelaskan implementasi penambahan field email pada tabel customers untuk menyimpan informasi kontak berbagai departemen.

## Perubahan yang Dibuat

### 1. Database Schema Updates
- **Menambahkan field `email` ke tabel `customers`:**
  - Tipe data: VARCHAR(255)
  - Nullable: Ya (opsional)
  - Index: Ya (untuk performa pencarian)

### 2. PHP Logic Updates
- **Modified INSERT query** untuk menangani field email:
  ```php
  INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, status, email, created_by, created_at)
  ```

- **Modified UPDATE query** untuk menangani field email:
  ```php
  UPDATE customers SET customer_id=?, name=?, star=?, room=?, outlet=?, type=?, "group"=?, zone=?, address=?, billing=?, status=?, email=? WHERE id=?
  ```

### 3. Form Updates

#### Create Customer Form
- **Field baru ditambahkan:**
  - Email (type="email", placeholder="Enter email address")
  - Posisi: Setelah field Address, sebelum field Billing

#### Edit Customer Form
- **Field email ditambahkan dengan ID:**
  - `edit_email` untuk form edit
  - Populated dengan data existing saat edit

### 4. Table Display Updates
- **Kolom baru ditambahkan:**
  - Header: "Email"
  - Data: Menampilkan email customer atau "-" jika kosong
  - Posisi: Setelah kolom Address, sebelum kolom Billing

### 5. JavaScript Updates
- **Fungsi `editCustomer()` diperbarui:**
  - Mengisi field email saat modal edit dibuka
  - Data email diambil dari `data-email` attribute

## Struktur Email yang Diharapkan

Field email ini dirancang untuk menyimpan kontak berbagai departemen seperti:
- General Manager
- Executive Office  
- Human Resource Department Head
- Accounting Department Head
- Chief Accounting
- Cost Control
- Accounting Payable
- Accounting Receivable
- Food & Beverage Department Head
- Front Office Department Head
- Housekeeping Department Head
- Engineering Department Head

## File yang Dimodifikasi
1. `customer.php` - Main customer management file
2. `add_email_to_customers.sql` - Database migration script
3. `docs/012_add_email_field_customers.md` - This documentation

## Cara Penggunaan
1. Jalankan script SQL untuk menambahkan field email
2. Field email akan tersedia di form create dan edit customer
3. Email akan ditampilkan di tabel customer list
4. Data email dapat diedit melalui modal edit

## Catatan Teknis
- Field email menggunakan validasi HTML5 (type="email")
- Email bersifat opsional (nullable)
- Index dibuat untuk performa pencarian yang lebih baik
- Backward compatible dengan data existing
