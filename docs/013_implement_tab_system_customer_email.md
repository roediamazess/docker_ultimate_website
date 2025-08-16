# Implementasi Tab System untuk Customer Email Management

## Overview
Dokumen ini menjelaskan implementasi tab system pada modal customer untuk mengelola multiple email fields dengan lebih rapi dan terorganisir.

## Perubahan yang Dibuat

### 1. Database Schema Updates
- **Menambahkan 12 field email terpisah ke tabel `customers`:**
  - `email_gm` - General Manager
  - `email_executive` - Executive Office
  - `email_hr` - HR Department Head
  - `email_acc_head` - Accounting Department Head
  - `email_chief_acc` - Chief Accounting
  - `email_cost_control` - Cost Control
  - `email_ap` - Accounting Payable
  - `email_ar` - Accounting Receivable
  - `email_fb` - F&B Department Head
  - `email_fo` - Front Office Department Head
  - `email_hk` - Housekeeping Department Head
  - `email_engineering` - Engineering Department Head

### 2. Tab System Implementation

#### Tab Navigation
- **General Info Tab**: Berisi semua field customer yang sudah ada
- **Email Contacts Tab**: Berisi semua field email departemen

#### Tab Styling
- CSS untuk tab buttons dan content
- Active state styling
- Hover effects
- Responsive design untuk mobile

### 3. Form Updates

#### Create Customer Form
- **Tab 1: General Info**
  - Customer ID, Name, Star, Room, Outlet, Type, Group, Zone, Address, Billing, Status
  
- **Tab 2: Email Contacts**
  - 12 field email untuk berbagai departemen
  - Grid layout 2 kolom
  - Placeholder text yang informatif

#### Edit Customer Form
- **Tab 1: General Info**
  - Semua field general info dengan data existing
  
- **Tab 2: Email Contacts**
  - 12 field email dengan data existing
  - Dapat diedit sesuai kebutuhan

### 4. JavaScript Functions

#### Tab Switching
```javascript
function switchTab(tabName, buttonElement) {
    // Remove active class from all tabs and buttons
    // Add active class to clicked button and corresponding content
}
```

#### Edit Customer Function
- Populate semua field general info
- Populate semua field email dari data attributes
- Handle data yang tidak ada dengan value kosong

### 5. Database Operations

#### INSERT Query
```sql
INSERT INTO customers (
    customer_id, name, star, room, outlet, type, "group", zone, address, billing, status,
    email_gm, email_executive, email_hr, email_acc_head, email_chief_acc, email_cost_control,
    email_ap, email_ar, email_fb, email_fo, email_hk, email_engineering,
    created_by, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

#### UPDATE Query
```sql
UPDATE customers SET 
    customer_id=?, name=?, star=?, room=?, outlet=?, type=?, "group"=?, zone=?, address=?, billing=?, status=?,
    email_gm=?, email_executive=?, email_hr=?, email_acc_head=?, email_chief_acc=?, email_cost_control=?,
    email_ap=?, email_ar=?, email_fb=?, email_fo=?, email_hk=?, email_engineering=?
WHERE id=?
```

## Struktur Tab System

### Tab 1: General Info
- Layout: 2 kolom dengan custom-modal-row
- Field: Semua field customer yang sudah ada
- Styling: Menggunakan CSS yang sudah ada

### Tab 2: Email Contacts
- Layout: Grid 2 kolom dengan email-fields-grid
- Field: 12 field email departemen
- Styling: CSS khusus untuk email fields

## CSS Classes

### Tab System
- `.tab-container` - Container utama tab
- `.tab-buttons` - Container button tab
- `.tab-button` - Style button tab
- `.tab-button.active` - Style button tab aktif
- `.tab-content` - Container content tab
- `.tab-content.active` - Content tab yang aktif

### Email Fields
- `.email-fields-grid` - Grid layout untuk email fields
- `.email-field-group` - Group untuk setiap field email
- `.email-field-label` - Label untuk field email
- `.email-field-input` - Input field email

## Responsive Design

### Desktop
- Tab buttons horizontal
- Email fields grid 2 kolom
- Modal width normal

### Mobile
- Tab buttons vertical
- Email fields grid 1 kolom
- Modal width 95%

## JavaScript Events

### Tab Switching
- Click event pada tab button
- Remove active class dari semua tab
- Add active class ke tab yang dipilih

### Modal Management
- Show/hide modal
- Populate form fields
- Handle form submission

## File yang Dimodifikasi

1. **`customer.php`** - File utama dengan tab system
2. **`add_multiple_email_fields.sql`** - Script SQL migration
3. **`docs/013_implement_tab_system_customer_email.md`** - This documentation

## Cara Penggunaan

### 1. Membuat Customer Baru
1. Klik "Create Customer"
2. Tab "General Info": Isi informasi dasar customer
3. Tab "Email Contacts": Isi email berbagai departemen
4. Klik "Add Customer"

### 2. Mengedit Customer
1. Klik baris customer yang ingin diedit
2. Tab "General Info": Edit informasi dasar
3. Tab "Email Contacts": Edit email departemen
4. Klik "Update Customer"

### 3. Navigasi Tab
- Klik tab button untuk switch antara tab
- Tab aktif akan memiliki styling berbeda
- Content tab akan berubah sesuai tab yang dipilih

## Manfaat Implementasi

1. **UI yang Lebih Rapi**: Form tidak terlalu panjang
2. **Organisasi yang Lebih Baik**: Email dikelompokkan terpisah
3. **User Experience**: Navigasi yang mudah dan intuitif
4. **Maintainability**: Kode yang lebih terorganisir
5. **Scalability**: Mudah menambah field email baru

## Status Implementasi

**COMPLETED** ✅

Semua fitur telah berhasil diimplementasikan:
- Tab system untuk create dan edit customer
- Multiple email fields untuk berbagai departemen
- Responsive design untuk desktop dan mobile
- JavaScript functions untuk tab switching
- Database schema dengan 12 email fields
- Form handling untuk semua field email
