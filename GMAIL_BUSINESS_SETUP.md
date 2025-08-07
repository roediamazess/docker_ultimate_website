# 📧 Gmail for Business Setup - Ultimate Website

## 🎯 Konfigurasi Email yang Benar

### **📧 Email Administrator (Sender)**
- **Email**: `pms@ppsolution.com` (Gmail for Business)
- **Fungsi**: Super User / Administrator / Email Sender
- **Status**: Gmail for Business dengan domain custom

### **👤 Email User (Receiver)**
- **Email**: `rudiantoap@gmail.com`
- **Fungsi**: User yang akan reset password
- **Status**: Gmail account, bisa terima email

## 🔧 Setup Gmail for Business untuk pms@ppsolution.com

### **Step 1: Aktifkan 2-Step Verification**

1. **Buka Google Account Security**
   - Kunjungi: https://myaccount.google.com/security
   - Login dengan akun `pms@ppsolution.com`

2. **Aktifkan 2-Step Verification**
   - Klik "2-Step Verification"
   - Ikuti langkah-langkah untuk setup
   - Pilih metode verifikasi (SMS/App)

### **Step 2: Generate App Password**

1. **Buka Google App Passwords**
   - Kunjungi: https://myaccount.google.com/apppasswords
   - Login dengan akun `pms@ppsolution.com`

2. **Generate App Password**
   - Pilih "Mail" dari dropdown
   - Pilih "Other (Custom name)"
   - Ketik "Ultimate Website"
   - Klik "Generate"

3. **Copy App Password**
   - Copy password yang dihasilkan (format: `abcd efgh ijkl mnop`)
   - Simpan dengan aman

### **Step 3: Update Konfigurasi Email**

1. **Buka file `email_config.php`**
2. **Update konfigurasi**:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'pms@ppsolution.com');
   define('SMTP_PASSWORD', 'your-app-password-here'); // Ganti dengan App Password
   define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');
   define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution');
   ```

3. **Ganti `your-app-password-here`** dengan App Password yang baru di-generate

### **Step 4: Test Konfigurasi**

1. **Jalankan test script**:
   ```bash
   php test_gmail_business.php
   ```

2. **Cek hasil test**:
   - ✅ Connection: SUCCESS
   - ✅ Email sent successfully!

## 🧪 Testing Email Setup

### **1️⃣ Test Konfigurasi**
```bash
php test_gmail_business.php
```

### **2️⃣ Test Forgot Password**
1. Buka `http://localhost/ultimate-website/forgot-password.php`
2. Masukkan email: `rudiantoap@gmail.com`
3. Klik "Kirim Link Reset"
4. Email akan dikirim dari `pms@ppsolution.com` ke `rudiantoap@gmail.com`

### **3️⃣ Test Reset Password**
1. Buka email di `rudiantoap@gmail.com`
2. Klik link reset password
3. Masukkan password baru
4. Login dengan password baru

## 🎯 Expected Results

Setelah setup berhasil:
- ✅ **Email terkirim** dari `pms@ppsolution.com` (Gmail for Business)
- ✅ **Email diterima** di `rudiantoap@gmail.com` (User)
- ✅ **Branding profesional** dengan nama PPSolution
- ✅ **Link reset yang valid** selama 1 jam
- ✅ **Sistem keamanan yang aman**

## 📧 Template Email

Email yang akan dikirim berisi:
- **Header**: Ultimate Website - PPSolution dengan gradient
- **From**: `pms@ppsolution.com` (Gmail for Business)
- **To**: `rudiantoap@gmail.com` (User)
- **Greeting**: "Halo Rudianto!"
- **Link Reset**: Tombol dan link untuk reset password
- **Keamanan**: Informasi tentang masa berlaku link
- **Footer**: Informasi kontak PPSolution

## 🚨 Troubleshooting

### **Error: "Authentication failed"**
- Pastikan 2-Step Verification aktif di `pms@ppsolution.com`
- Pastikan App Password benar dan baru di-generate
- Pastikan menggunakan App Password, bukan password biasa

### **Error: "Connection failed"**
- Cek internet connection
- Cek firewall settings
- Cek port 587 tidak diblokir

### **Error: "App Password not working"**
- Generate ulang App Password
- Pastikan nama app "Ultimate Website"
- Pastikan 2-Step Verification aktif

## ✅ Setup Checklist

- [ ] **2-Step Verification aktif** di `pms@ppsolution.com`
- [ ] **App Password generated** untuk "Ultimate Website"
- [ ] **SMTP_PASSWORD diupdate** dengan App Password
- [ ] **Test email berhasil** dikirim
- [ ] **Forgot password berfungsi** end-to-end

## 🎉 Next Steps

1. **Setup 2-Step Verification** di `pms@ppsolution.com`
2. **Generate App Password** untuk "Ultimate Website"
3. **Update konfigurasi** di `email_config.php`
4. **Test sistem** dengan script yang disediakan
5. **Monitor email delivery** dan user experience

---

**💡 Tips**: App Password hanya bisa digunakan untuk aplikasi yang tidak mendukung 2-Step Verification. Jangan bagikan App Password kepada siapapun! 