# 📧 Email Setup Guide - Ultimate Website

## 🔧 Setup Email untuk Password Reset

### **Masalah Saat Ini:**
Sistem forgot password saat ini hanya simulasi dan tidak benar-benar mengirim email ke `rudiantoap@gmail.com`.

### **Solusi:**
Setup sistem email yang benar-benar berfungsi menggunakan Gmail SMTP.

---

## 📋 Langkah-langkah Setup Email

### **1️⃣ Aktifkan 2-Step Verification**
1. Buka [Google Account Security](https://myaccount.google.com/security)
2. Klik "2-Step Verification"
3. Aktifkan 2-Step Verification untuk akun Gmail Anda

### **2️⃣ Generate App Password**
1. Buka [Google App Passwords](https://myaccount.google.com/apppasswords)
2. Pilih "Mail" dari dropdown
3. Pilih "Other (Custom name)" dan ketik "Ultimate Website"
4. Klik "Generate"
5. **Copy password yang dihasilkan** (format: `abcd efgh ijkl mnop`)

### **3️⃣ Update Konfigurasi Email**
1. Buka file `email_config.php`
2. Update baris berikut:

```php
// Ganti dengan kredensial Anda
define('SMTP_USERNAME', 'rudiantoap@gmail.com'); // Email Gmail Anda
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // App Password dari Google
define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com'); // Email pengirim
```

### **4️⃣ Test Konfigurasi**
Jalankan script test:
```bash
php test_email_config.php
```

---

## 🎯 Hasil yang Diharapkan

Setelah setup berhasil:
- ✅ Email reset password akan dikirim ke `rudiantoap@gmail.com`
- ✅ Email berisi link reset yang valid selama 1 jam
- ✅ Template email yang profesional dan responsif
- ✅ Sistem keamanan yang aman

---

## 📧 Template Email yang Akan Dikirim

Email akan berisi:
- **Header**: Ultimate Website dengan gradient
- **Greeting**: "Halo Rudianto!"
- **Link Reset**: Tombol dan link untuk reset password
- **Keamanan**: Informasi tentang masa berlaku link
- **Footer**: Informasi kontak

---

## 🔗 File yang Terlibat

- `email_config.php` - Konfigurasi email
- `send_email.php` - Fungsi kirim email
- `forgot-password.php` - Halaman request reset
- `reset-password.php` - Halaman set password baru
- `test_email_config.php` - Test konfigurasi

---

## 🚨 Troubleshooting

### **Error: "Authentication failed"**
- Pastikan 2-Step Verification aktif
- Pastikan App Password benar
- Pastikan email Gmail valid

### **Error: "Connection failed"**
- Pastikan internet terhubung
- Pastikan port 587 tidak diblokir
- Cek firewall settings

### **Email tidak terkirim**
- Cek spam folder
- Pastikan email tujuan benar
- Cek error logs di XAMPP

---

## ✅ Checklist Setup

- [ ] 2-Step Verification aktif
- [ ] App Password generated
- [ ] `email_config.php` diupdate
- [ ] Test konfigurasi berhasil
- [ ] Email test terkirim
- [ ] Forgot password berfungsi

---

## 🎉 Setelah Setup Berhasil

1. **Request Reset Password**: Klik "Forgot Password? Click here"
2. **Masukkan Email**: `rudiantoap@gmail.com`
3. **Klik Submit**: Sistem akan kirim email
4. **Cek Email**: Buka inbox `rudiantoap@gmail.com`
5. **Klik Link**: Reset password melalui email
6. **Set Password Baru**: Masukkan password baru

---

**💡 Tips:** Simpan App Password dengan aman dan jangan bagikan kepada siapapun! 