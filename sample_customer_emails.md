# Contoh Penggunaan Field Email Customer

## Format Email yang Direkomendasikan

### 1. General Manager
- Format: `gm@hotelname.com` atau `general.manager@hotelname.com`
- Contoh: `gm@hotelmawar.com`

### 2. Executive Office
- Format: `executive@hotelname.com` atau `exo@hotelname.com`
- Contoh: `executive@hotelmawar.com`

### 3. Human Resource Department Head
- Format: `hr@hotelname.com` atau `hr.head@hotelname.com`
- Contoh: `hr@hotelmawar.com`

### 4. Accounting Department Head
- Format: `accounting@hotelname.com` atau `acc.head@hotelname.com`
- Contoh: `accounting@hotelmawar.com`

### 5. Chief Accounting
- Format: `chief.acc@hotelname.com` atau `ca@hotelname.com`
- Contoh: `chief.acc@hotelmawar.com`

### 6. Cost Control
- Format: `cost.control@hotelname.com` atau `cc@hotelname.com`
- Contoh: `cost.control@hotelmawar.com`

### 7. Accounting Payable
- Format: `ap@hotelname.com` atau `payable@hotelname.com`
- Contoh: `ap@hotelmawar.com`

### 8. Accounting Receivable
- Format: `ar@hotelname.com` atau `receivable@hotelname.com`
- Contoh: `ar@hotelmawar.com`

### 9. Food & Beverage Department Head
- Format: `f&b@hotelname.com` atau `fb.head@hotelname.com`
- Contoh: `f&b@hotelmawar.com`

### 10. Front Office Department Head
- Format: `front.office@hotelname.com` atau `fo.head@hotelname.com`
- Contoh: `front.office@hotelmawar.com`

### 11. Housekeeping Department Head
- Format: `housekeeping@hotelname.com` atau `hk.head@hotelname.com`
- Contoh: `housekeeping@hotelmawar.com`

### 12. Engineering Department Head
- Format: `engineering@hotelname.com` atau `eng.head@hotelname.com`
- Contoh: `engineering@hotelmawar.com`

## Contoh Data Customer dengan Email

```sql
-- Update existing customers with sample emails
UPDATE customers SET email = 'gm@hotelmawar.com' WHERE customer_id = 'CUST001';
UPDATE customers SET email = 'hr@restoranmelati.com' WHERE customer_id = 'CUST002';

-- Insert new customer with email
INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, status, email, created_by, created_at) 
VALUES ('CUST003', 'Hotel Melati', 5, '150', 'Restoran Melati', 'Hotel', 'Group C', 'Zone 3', 'Jl. Melati No.3', 'Contract Maintenance', 'Active', 'executive@hotelmelati.com', 1, NOW());
```

## Manfaat Field Email

1. **Komunikasi Langsung**: Dapat menghubungi departemen yang tepat
2. **Notifikasi Otomatis**: Untuk sistem notifikasi dan reminder
3. **Laporan**: Laporan dapat dikirim langsung ke email yang sesuai
4. **Support**: Tim support dapat menghubungi PIC yang tepat
5. **Audit Trail**: Tracking komunikasi dengan customer

## Validasi Email

Field email menggunakan validasi HTML5:
- Format: `type="email"`
- Placeholder: "Enter email address"
- Validasi otomatis format email
- Field opsional (nullable)
