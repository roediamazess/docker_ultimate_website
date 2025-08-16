# 🔧 Troubleshooting Project ID Validation

## Masalah yang Ditemukan
Berdasarkan console error yang muncul, ada masalah dengan AJAX request yang tidak mendapatkan response dari API endpoint `check_project_id_uniqueness.php`.

## File yang Telah Dibuat untuk Troubleshooting

### 1. `debug_project_id_validation.php`
File ini akan membantu mendiagnosis masalah:
- ✅ Session check
- ✅ Database connection check
- ✅ Project PRJ999 existence check
- ✅ API endpoint test
- ✅ PHP error check
- ✅ Access control check

**Cara Penggunaan:**
```bash
# Jalankan di browser
php debug_project_id_validation.php
```

### 2. `simple_test.php`
File test sederhana untuk memverifikasi validasi:
- ✅ Form input untuk project ID
- ✅ Real-time validation
- ✅ Console log untuk debugging
- ✅ Auto-test dengan PRJ999

**Cara Penggunaan:**
```bash
# Buka di browser
simple_test.php
```

### 3. `simple_test_no_session.php`
File test yang lebih advanced:
- ✅ Pilihan endpoint (dengan/s tanpa session)
- ✅ Test endpoint functionality
- ✅ Detailed logging
- ✅ Comparison between endpoints

**Cara Penggunaan:**
```bash
# Buka di browser
simple_test_no_session.php
```

### 4. `test_without_session.php`
API endpoint tanpa session requirement:
- ✅ Tidak memerlukan login
- ✅ Langsung check database
- ✅ Response JSON yang sama

**Cara Penggunaan:**
```bash
# Gunakan sebagai endpoint alternatif
test_without_session.php
```

## Langkah Troubleshooting

### **Step 1: Debug Session dan Database**
```bash
# Jalankan file debug
php debug_project_id_validation.php
```

**Yang Harus Dicek:**
- ✅ Session user_id dan email ada
- ✅ Database connection berhasil
- ✅ Project PRJ999 ada di database
- ✅ API endpoint berfungsi

### **Step 2: Test API Endpoint**
```bash
# Buka file test sederhana
simple_test.php
```

**Yang Harus Dicek:**
- ✅ Console log menampilkan request
- ✅ Response dari server
- ✅ JSON parsing berhasil
- ✅ Error message muncul untuk PRJ999

### **Step 3: Compare Endpoints**
```bash
# Buka file test advanced
simple_test_no_session.php
```

**Yang Harus Dicek:**
- ✅ Endpoint tanpa session berfungsi
- ✅ Endpoint dengan session berfungsi
- ✅ Response yang sama dari kedua endpoint

### **Step 4: Fix Issues**

#### **Issue 1: Session Problem**
Jika ada masalah dengan session:
```php
// Di check_project_id_uniqueness.php
// Tambahkan error handling yang lebih baik
if (file_exists('access_control.php')) {
    require_once 'access_control.php';
    
    if (function_exists('require_login')) {
        try {
            require_login();
        } catch (Exception $e) {
            // Log error atau handle gracefully
            error_log("Login failed: " . $e->getMessage());
        }
    }
}
```

#### **Issue 2: Database Connection**
Jika ada masalah dengan database:
```php
// Test database connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "Database OK";
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
```

#### **Issue 3: JavaScript Error**
Jika ada masalah dengan JavaScript:
```javascript
// Tambahkan error handling yang lebih baik
async function checkProjectIdUniqueness(projectId) {
    try {
        console.log('🔍 Checking uniqueness for project ID:', projectId);
        
        const response = await fetch('check_project_id_uniqueness.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'project_id=' + encodeURIComponent(projectId)
        });
        
        console.log('📡 Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('📡 Raw response:', responseText);
        
        const result = JSON.parse(responseText);
        console.log('✅ Parsed result:', result);
        
        return result;
        
    } catch (error) {
        console.error('❌ Error checking project ID uniqueness:', error);
        return { 
            exists: false, 
            message: 'Error checking uniqueness: ' + error.message,
            error: error.message 
        };
    }
}
```

## Expected Results

### **Untuk Project ID yang Sudah Ada (PRJ999):**
```json
{
  "success": true,
  "exists": true,
  "message": "❌ Project ID 'PRJ999' sudah digunakan! (Project: TEST, Hotel: Hotel Mawar, Type: Retraining, Status: Scheduled)",
  "count": 1,
  "project_info": {
    "project_name": "TEST",
    "hotel_name_text": "Hotel Mawar",
    "type": "Retraining",
    "status": "Scheduled",
    "created_at": "2025-01-14 10:00:00"
  }
}
```

### **Untuk Project ID yang Baru:**
```json
{
  "success": true,
  "exists": false,
  "message": "✅ Project ID 'PRJ_NEW_001' tersedia dan dapat digunakan",
  "count": 0
}
```

## Common Issues dan Solutions

### **Issue 1: "The message port closed before a response was received"**
**Solution:**
- Periksa apakah API endpoint berfungsi
- Pastikan tidak ada PHP error
- Check browser console untuk detail error

### **Issue 2: "Failed to fetch"**
**Solution:**
- Periksa file path API endpoint
- Pastikan server berjalan
- Check network tab di DevTools

### **Issue 3: "Invalid JSON response"**
**Solution:**
- Periksa apakah ada PHP error sebelum JSON
- Pastikan header Content-Type: application/json
- Check apakah ada whitespace sebelum <?php

### **Issue 4: "Session expired"**
**Solution:**
- Refresh halaman dan login ulang
- Periksa session timeout
- Gunakan endpoint tanpa session untuk testing

## Testing Checklist

- [ ] Session user valid
- [ ] Database connection OK
- [ ] Project PRJ999 ada di database
- [ ] API endpoint berfungsi
- [ ] Response JSON valid
- [ ] Frontend validation berfungsi
- [ ] Error message muncul untuk duplikasi
- [ ] Success message muncul untuk ID baru

## Next Steps

1. **Jalankan debug file** untuk identifikasi masalah
2. **Test API endpoint** secara terpisah
3. **Compare endpoints** dengan/s tanpa session
4. **Fix issues** berdasarkan debug info
5. **Test kembali** di form Add Project
6. **Verifikasi** error message muncul untuk PRJ999

## File yang Perlu Diperiksa

- `debug_project_id_validation.php` - Debug utama
- `simple_test.php` - Test sederhana
- `simple_test_no_session.php` - Test advanced
- `test_without_session.php` - Endpoint alternatif
- `check_project_id_uniqueness.php` - API utama
- `projects.php` - JavaScript validation

Dengan file-file ini, kita bisa mengidentifikasi dan memperbaiki masalah validasi project ID dengan cepat! 🚀

