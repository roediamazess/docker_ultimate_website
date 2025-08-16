# Penambahan Unique Constraint pada Project ID

## Overview
Dokumen ini menjelaskan implementasi unique constraint pada field `project_id` di tabel `projects` untuk memastikan tidak ada project ID yang duplikat.

## Perubahan yang Dibuat

### 1. Database Schema Updates
- **Menambahkan unique constraint** pada field `project_id` di tabel `projects`
- **Constraint name**: `uk_projects_project_id`
- **Type**: UNIQUE
- **Index**: `idx_projects_project_id` untuk performa pencarian

### 2. Backend Validation
- **Validasi format project_id**: Tidak boleh kosong, maksimal 50 karakter
- **Validasi duplikasi**: Check database sebelum insert untuk memastikan tidak ada duplikasi
- **Error handling**: Pesan error yang informatif jika project ID sudah digunakan

### 3. Frontend Validation
- **Real-time validation**: Validasi saat user mengetik dan saat blur
- **Format validation**: Hanya huruf, angka, underscore (_), dan dash (-)
- **Visual feedback**: Bootstrap validation classes (is-valid, is-invalid)
- **User experience**: Feedback langsung tanpa perlu submit form

## Struktur Database

### Unique Constraint
```sql
-- Constraint yang ditambahkan
ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);

-- Index untuk performa
CREATE INDEX idx_projects_project_id ON projects(project_id);
```

### Validasi Format
- **Length**: Maksimal 50 karakter
- **Characters**: Hanya a-z, A-Z, 0-9, underscore (_), dash (-)
- **Required**: Tidak boleh kosong

## Implementasi Validasi

### 1. Backend Validation (PHP)
```php
// Validate project_id format
if (empty($project_id) || strlen($project_id) > 50) {
    throw new Exception('Project ID tidak boleh kosong dan maksimal 50 karakter.');
}

// Check for duplicate project_id
if (!$existing) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        throw new Exception('Project ID "' . htmlspecialchars($project_id) . '" sudah digunakan. Silakan gunakan Project ID yang berbeda.');
    }
}
```

### 2. Frontend Validation (JavaScript)
```javascript
function validateProjectId(projectId) {
    if (!projectId || projectId.trim() === '') {
        return { valid: false, message: 'Project ID tidak boleh kosong' };
    }
    
    if (projectId.length > 50) {
        return { valid: false, message: 'Project ID maksimal 50 karakter' };
    }
    
    if (!/^[a-zA-Z0-9_-]+$/.test(projectId)) {
        return { valid: false, message: 'Project ID hanya boleh berisi huruf, angka, underscore (_), dan dash (-)' };
    }
    
    return { valid: true, message: '' };
}
```

## Manfaat Implementasi

### 1. **Data Integrity**
- Tidak ada project ID yang duplikat
- Konsistensi data di database
- Referential integrity yang terjaga

### 2. **User Experience**
- Feedback real-time saat input
- Pesan error yang jelas dan informatif
- Validasi sebelum submit form

### 3. **System Reliability**
- Mencegah konflik data
- Performa query yang lebih baik dengan index
- Error handling yang robust

## Error Messages

### Backend Errors
- **Empty Project ID**: "Project ID tidak boleh kosong dan maksimal 50 karakter."
- **Duplicate Project ID**: "Project ID '[ID]' sudah digunakan. Silakan gunakan Project ID yang berbeda."

### Frontend Errors
- **Empty**: "Project ID tidak boleh kosong"
- **Too Long**: "Project ID maksimal 50 karakter"
- **Invalid Characters**: "Project ID hanya boleh berisi huruf, angka, underscore (_), dan dash (-)"

## Testing Scenarios

### 1. **Valid Project ID**
- Format: `PRJ001`, `Project_2024`, `IMP-001`
- Expected: Form dapat disubmit, data tersimpan

### 2. **Invalid Project ID**
- Empty: `""`
- Too long: `"A".repeat(51)`
- Special chars: `PRJ@001`, `PRJ#001`
- Expected: Error message, form tidak dapat disubmit

### 3. **Duplicate Project ID**
- Create project dengan ID `PRJ001`
- Try to create another project dengan ID `PRJ001`
- Expected: Error message "Project ID sudah digunakan"

## File yang Dimodifikasi

1. **`projects.php`** - Backend validation dan frontend JavaScript
2. **`add_unique_constraint_projects.sql`** - Script SQL migration
3. **`docs/015_add_unique_constraint_projects.md`** - This documentation

## Cara Penggunaan

### 1. **Membuat Project Baru**
- Masukkan Project ID yang unik
- Format yang valid: huruf, angka, underscore, dash
- Maksimal 50 karakter

### 2. **Edit Project**
- Project ID tidak dapat diubah (untuk menjaga referential integrity)
- Field lain dapat diedit sesuai kebutuhan

### 3. **Validasi Real-time**
- Input validation saat mengetik
- Format validation saat blur
- Visual feedback dengan Bootstrap classes

## Status Implementasi

**COMPLETED** ✅

Semua fitur telah berhasil diimplementasikan:
- ✅ Unique constraint pada database
- ✅ Backend validation untuk format dan duplikasi
- ✅ Frontend validation real-time
- ✅ Error handling yang informatif
- ✅ Visual feedback untuk user
- ✅ Index untuk performa optimal

## Cara Verifikasi

### 1. **Check Database**
- Unique constraint `uk_projects_project_id` sudah ada
- Index `idx_projects_project_id` sudah dibuat
- Tidak ada duplicate project_id

### 2. **Test Form Validation**
- Input project ID kosong
- Input project ID terlalu panjang
- Input project ID dengan karakter khusus
- Input project ID yang sudah ada

### 3. **Test Error Handling**
- Submit form dengan data invalid
- Check error messages yang muncul
- Verify data tidak tersimpan ke database
