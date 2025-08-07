# 🔧 Email Setup Fix - Ultimate Website

## 🚨 Masalah yang Ditemukan

Berdasarkan test yang dilakukan, ada beberapa masalah:

1. **❌ Authentication Failed**: Password `Pass@998877` bukan App Password Gmail
2. **❌ SSL Certificate Error**: Masalah dengan SSL verification
3. **❌ Domain Mismatch**: `pms@ppsolution.com` vs `pms@powerpro.id`
4. **❌ HTTP_HOST Undefined**: Error di CLI environment

## ✅ Solusi Lengkap

### **Option 1: Gunakan Gmail yang Benar (Recommended)**

#### **1️⃣ Setup Gmail Account**
1. Buka [Google Account Security](https://myaccount.google.com/security)
2. Aktifkan **2-Step Verification**
3. Buka [Google App Passwords](https://myaccount.google.com/apppasswords)
4. Pilih "Mail" dan "Other (Custom name)"
5. Ketik "Ultimate Website" dan klik "Generate"
6. **Copy App Password** (format: `abcd efgh ijkl mnop`)

#### **2️⃣ Update email_config.php**
```php
// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'rudiantoap@gmail.com'); // Email Gmail Anda
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // App Password dari Google
define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website'); // Nama pengirim
```

#### **3️⃣ Test Konfigurasi**
```bash
php test_email_fixed.php
```

### **Option 2: Fix Email Domain yang Ada**

Jika ingin tetap menggunakan `pms@ppsolution.com`:

#### **1️⃣ Pastikan Domain Support SMTP**
- Cek apakah domain `ppsolution.com` support SMTP
- Hubungi provider email untuk konfirmasi SMTP settings

#### **2️⃣ Update Konfigurasi**
```php
define('SMTP_HOST', 'smtp.ppsolution.com'); // Sesuaikan dengan provider
define('SMTP_PORT', 587); // Atau 465 untuk SSL
define('SMTP_USERNAME', 'pms@ppsolution.com');
define('SMTP_PASSWORD', 'Pass@998877'); // Password yang benar
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');
```

## 🔧 Fixes yang Sudah Diterapkan

### **1️⃣ SSL Bypass untuk Development**
```php
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```

### **2️⃣ HTTP_HOST Fix**
```php
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'ultimate-website';
$base_path = dirname($script_name);
$reset_link = "http://" . $host . $base_path . "/reset-password.php?token=" . $reset_token;
```

## 🧪 Testing Steps

### **1️⃣ Test Konfigurasi**
```bash
php test_email_fixed.php
```

### **2️⃣ Test Forgot Password**
1. Buka `http://localhost/ultimate-website/forgot-password.php`
2. Masukkan email: `rudiantoap@gmail.com`
3. Klik "Kirim Link Reset"
4. Cek inbox email

### **3️⃣ Test Reset Password**
1. Klik link di email
2. Masukkan password baru
3. Konfirmasi password
4. Login dengan password baru

## 🎯 Expected Results

Setelah fix berhasil:
- ✅ **Email terkirim** ke `rudiantoap@gmail.com`
- ✅ **Template email profesional** dengan branding
- ✅ **Link reset yang valid** selama 1 jam
- ✅ **Sistem keamanan yang aman**
- ✅ **User experience yang smooth**

## 🚨 Troubleshooting

### **Error: "Authentication failed"**
- Pastikan menggunakan App Password, bukan password biasa
- Pastikan 2-Step Verification aktif
- Pastikan email Gmail valid

### **Error: "Connection failed"**
- Cek internet connection
- Cek firewall settings
- Cek port 587 tidak diblokir

### **Error: "SSL certificate failed"**
- Sudah di-fix dengan SSL bypass
- Untuk production, gunakan SSL certificate yang valid

## 📧 Template Email

Email yang akan dikirim berisi:
- **Header**: Ultimate Website dengan gradient
- **Greeting**: "Halo Rudianto!"
- **Link Reset**: Tombol dan link untuk reset password
- **Keamanan**: Informasi tentang masa berlaku link
- **Footer**: Informasi kontak

## 🎉 Next Steps

1. **Pilih option** (Gmail atau domain existing)
2. **Setup konfigurasi** sesuai option yang dipilih
3. **Test sistem** dengan script yang disediakan
4. **Gunakan forgot password** untuk test end-to-end
5. **Monitor email delivery** dan user experience

---

**💡 Recommendation**: Gunakan Option 1 (Gmail) karena lebih reliable dan mudah di-setup! 