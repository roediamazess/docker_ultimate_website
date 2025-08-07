# 📧 Email Setup untuk pms@ppsolution.com - Ultimate Website

## 🎯 Informasi Penting

**Email Administrator**: `pms@ppsolution.com`  
**Fungsi**: Super User / Administrator + Email Sender  
**Status**: Domain `ppsolution.com` tidak memiliki SMTP server sendiri

## 🔍 Hasil Test SMTP

Berdasarkan test yang dilakukan:
- ❌ **`smtp.ppsolution.com`**: TIDAK ADA - Domain tidak memiliki SMTP server
- ✅ **`smtp.gmail.com`**: BERHASIL - Gmail SMTP berfungsi
- ✅ **`smtp.office365.com`**: BERHASIL - Microsoft 365 berfungsi
- ✅ **`smtp.zoho.com`**: BERHASIL - Zoho Mail berfungsi

## 🚀 Solusi yang Direkomendasikan

### **Option 1: Gmail for Business (Recommended)**

#### **1️⃣ Setup Google Workspace**
1. Buka [Google Workspace Admin Console](https://admin.google.com)
2. Tambahkan domain `ppsolution.com`
3. Setup email routing untuk `pms@ppsolution.com`
4. Aktifkan 2-Step Verification
5. Generate App Password

#### **2️⃣ Update email_config.php**
```php
// Gmail for Business SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com');
define('SMTP_PASSWORD', 'your-app-password-here'); // App Password dari Google
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution');
```

### **Option 2: Microsoft 365**

#### **1️⃣ Setup Microsoft 365**
1. Buka [Microsoft 365 Admin Center](https://admin.microsoft.com)
2. Tambahkan domain `ppsolution.com`
3. Setup email untuk `pms@ppsolution.com`
4. Gunakan password email biasa

#### **2️⃣ Update email_config.php**
```php
// Microsoft 365 SMTP Configuration
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com');
define('SMTP_PASSWORD', 'your-email-password');
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution');
```

### **Option 3: Zoho Mail**

#### **1️⃣ Setup Zoho Mail**
1. Buka [Zoho Mail Admin](https://mail.zoho.com)
2. Tambahkan domain `ppsolution.com`
3. Setup email untuk `pms@ppsolution.com`
4. Aktifkan App Password

#### **2️⃣ Update email_config.php**
```php
// Zoho Mail SMTP Configuration
define('SMTP_HOST', 'smtp.zoho.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution');
```

## 🔧 Langkah-langkah Setup

### **1️⃣ Pilih Provider Email**
- **Gmail for Business**: Paling mudah, terintegrasi dengan Google
- **Microsoft 365**: Profesional, terintegrasi dengan Office
- **Zoho Mail**: Ekonomis, fitur lengkap

### **2️⃣ Setup Domain Email**
1. Daftar ke provider yang dipilih
2. Verifikasi domain `ppsolution.com`
3. Setup DNS records (MX, SPF, DKIM)
4. Buat email `pms@ppsolution.com`

### **3️⃣ Update Konfigurasi**
1. Copy konfigurasi yang sesuai ke `email_config.php`
2. Update password dengan App Password atau password email
3. Test konfigurasi

### **4️⃣ Test Sistem**
```bash
php test_email_fixed.php
```

## 🧪 Testing Checklist

- [ ] **SMTP Connection**: Test koneksi ke server SMTP
- [ ] **Authentication**: Test login dengan kredensial
- [ ] **Email Sending**: Test kirim email reset password
- [ ] **Email Delivery**: Cek email sampai di inbox
- [ ] **Link Reset**: Test link reset password berfungsi
- [ ] **Password Reset**: Test reset password berhasil

## 🎯 Expected Results

Setelah setup berhasil:
- ✅ **Email terkirim** dari `pms@ppsolution.com`
- ✅ **Branding profesional** dengan nama PPSolution
- ✅ **Link reset yang valid** selama 1 jam
- ✅ **Sistem keamanan yang aman**
- ✅ **User experience yang smooth**

## 🚨 Troubleshooting

### **Error: "Domain not found"**
- Pastikan domain `ppsolution.com` sudah terdaftar di provider
- Cek DNS records sudah benar
- Hubungi provider untuk konfirmasi

### **Error: "Authentication failed"**
- Pastikan menggunakan App Password (untuk Gmail/Zoho)
- Pastikan password email benar (untuk Microsoft 365)
- Pastikan 2-Step Verification aktif (jika diperlukan)

### **Error: "Connection failed"**
- Cek internet connection
- Cek firewall settings
- Cek port 587 tidak diblokir

## 📧 Template Email

Email yang akan dikirim berisi:
- **Header**: Ultimate Website - PPSolution dengan gradient
- **Greeting**: "Halo [Nama]!"
- **Link Reset**: Tombol dan link untuk reset password
- **Keamanan**: Informasi tentang masa berlaku link
- **Footer**: Informasi kontak PPSolution

## 🎉 Next Steps

1. **Pilih provider email** (Gmail for Business recommended)
2. **Setup domain email** di provider yang dipilih
3. **Update konfigurasi** di `email_config.php`
4. **Test sistem** dengan script yang disediakan
5. **Monitor email delivery** dan user experience

---

**💡 Recommendation**: Gunakan **Gmail for Business** karena paling mudah di-setup dan terintegrasi dengan baik! 