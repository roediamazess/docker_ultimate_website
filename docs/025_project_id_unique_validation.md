# Project ID Unique Constraint dan Validasi

## Overview
Dokumen ini menjelaskan implementasi unique constraint pada kolom `project_id` dan sistem validasi yang mencegah duplikasi project ID saat menambah project baru.

## Fitur yang Diimplementasikan

### 1. Database Level Protection
- **Unique Constraint**: Kolom `project_id` sekarang memiliki unique constraint di level database
- **Automatic Rejection**: Database akan otomatis menolak INSERT/UPDATE yang menyebabkan duplikasi
- **Data Integrity**: Memastikan tidak ada project dengan ID yang sama

### 2. Backend Validation
- **Pre-insert Check**: Validasi duplikasi sebelum menyimpan ke database
- **Detailed Error Messages**: Pesan error yang informatif dengan detail project yang sudah ada
- **Transaction Safety**: Menggunakan database transaction untuk konsistensi data

### 3. Frontend Validation
- **Real-time Validation**: Validasi format dan uniqueness saat user mengetik
- **Visual Feedback**: Indikator visual (hijau/merah) untuk status validasi
- **Form Submission Guard**: Mencegah submit form jika project ID tidak valid

## File yang Dimodifikasi

### 1. `add_unique_constraint_project_id.php`
Script untuk menambahkan unique constraint pada kolom `project_id`.

**Fitur:**
- Deteksi database driver (MySQL/PostgreSQL)
- Pemeriksaan duplikasi existing data
- Penambahan unique constraint
- Verifikasi constraint berhasil ditambahkan

**Cara Penggunaan:**
```bash
# Jalankan script ini di browser atau command line
php add_unique_constraint_project_id.php
```

### 2. `check_project_id_uniqueness.php`
API endpoint untuk validasi uniqueness project ID secara real-time.

**Response Format:**
```json
{
  "success": true,
  "exists": true,
  "message": "❌ Project ID 'PRJ001' sudah digunakan! (Project: Implementasi PMS, Hotel: Hotel Mawar, Type: Implementation, Status: Done)",
  "count": 1,
  "project_info": {
    "project_name": "Implementasi PMS",
    "hotel_name_text": "Hotel Mawar",
    "type": "Implementation",
    "status": "Done",
    "created_at": "2023-01-10 10:00:00"
  }
}
```

### 3. `projects.php`
File utama project management dengan validasi yang ditingkatkan.

**Validasi yang Ditambahkan:**
- Format validation (alphanumeric, underscore, dash)
- Length validation (max 50 karakter)
- Real-time uniqueness check
- Detailed error messages
- Form submission prevention

## Cara Kerja Validasi

### 1. Format Validation
```javascript
function validateProjectId(projectId) {
    if (!projectId || projectId.trim() === '') {
        return { valid: false, message: 'Project ID tidak boleh kosong' };
    }
    
    if (projectId.length > 50) {
        return { valid: false, message: 'Project ID maksimal 50 karakter' };
    }
    
    if (!/^[a-zA-Z0-9_-]+$/.test(projectId)) {
        return { valid: false, message: 'Project ID hanya boleh berisi huruf, angka, underscore (_), dan dash (-). Karakter khusus tidak diizinkan.' };
    }
    
    return { valid: true, message: '' };
}
```

### 2. Uniqueness Check
```javascript
async function checkProjectIdUniqueness(projectId) {
    try {
        const response = await fetch('check_project_id_uniqueness.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'project_id=' + encodeURIComponent(projectId)
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error checking project ID uniqueness:', error);
        return { exists: false, message: 'Error checking uniqueness' };
    }
}
```

### 3. Real-time Validation
```javascript
projectIdInput.addEventListener('blur', async function() {
    const projectId = this.value.trim();
    
    if (!projectId) return;
    
    // Basic format validation
    const formatValidation = validateProjectId(projectId);
    if (!formatValidation.valid) {
        this.classList.add('is-invalid');
        // Show error message
        return;
    }
    
    // Check uniqueness in database
    const uniquenessCheck = await checkProjectIdUniqueness(projectId);
    if (uniquenessCheck.exists) {
        this.classList.add('is-invalid');
        // Show detailed error message
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        // Show success message
    }
});
```

## Pesan Error yang Ditampilkan

### 1. Format Error
- ❌ Project ID tidak boleh kosong
- ❌ Project ID maksimal 50 karakter
- ❌ Project ID hanya boleh berisi huruf, angka, underscore (_), dan dash (-). Karakter khusus tidak diizinkan.

### 2. Duplication Error
```
❌ Project ID "PRJ001" sudah digunakan!

Informasi Project yang sudah ada:
• Project Name: Implementasi PMS
• Hotel: Hotel Mawar
• Type: Implementation
• Status: Done
• Created: 2023-01-10 10:00:00

Silakan gunakan Project ID yang berbeda.
```

## Database Schema Changes

### Before (No Unique Constraint)
```sql
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,  -- No unique constraint
    -- other columns...
);
```

### After (With Unique Constraint)
```sql
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    -- other columns...
    UNIQUE KEY projects_project_id_unique (project_id)  -- Added unique constraint
);
```

## Testing

### 1. Test Unique Constraint
1. Jalankan `add_unique_constraint_project_id.php`
2. Verifikasi constraint berhasil ditambahkan
3. Coba insert project dengan ID yang sama

### 2. Test Frontend Validation
1. Buka form Add Project
2. Masukkan project ID yang sudah ada
3. Verifikasi pesan error muncul
4. Verifikasi form tidak bisa di-submit

### 3. Test Backend Validation
1. Coba bypass frontend validation
2. Verifikasi backend tetap menolak duplikasi
3. Verifikasi pesan error detail muncul

## Troubleshooting

### 1. Constraint Already Exists
Jika unique constraint sudah ada, script akan menampilkan informasi constraint yang ada.

### 2. Duplicate Data Found
Jika ada duplikasi existing data, script akan berhenti dan menampilkan daftar duplikasi yang harus diperbaiki terlebih dahulu.

### 3. Database Driver Not Supported
Script mendukung MySQL dan PostgreSQL. Untuk database lain, perlu modifikasi script.

## Security Considerations

### 1. SQL Injection Protection
- Menggunakan prepared statements
- Validasi input di frontend dan backend
- Sanitasi output

### 2. Access Control
- Validasi session user
- CSRF protection
- Role-based access control

### 3. Error Handling
- Tidak menampilkan informasi database internal
- Logging untuk debugging
- User-friendly error messages

## Future Enhancements

### 1. Auto-suggestion
- Suggest available project ID format
- Check availability in real-time

### 2. Bulk Validation
- Validate multiple project IDs at once
- Batch import validation

### 3. Advanced Format Rules
- Custom format validation rules
- Project ID pattern templates
- Industry-specific naming conventions

## Conclusion

Implementasi ini memberikan:
- **Data Integrity**: Unique constraint di level database
- **User Experience**: Real-time validation dengan feedback visual
- **Error Prevention**: Mencegah duplikasi sebelum data tersimpan
- **Maintainability**: Kode yang terstruktur dan terdokumentasi

Project ID sekarang benar-benar unique dan sistem validasi memberikan feedback yang jelas kepada user tentang status validasi dan error yang terjadi.

