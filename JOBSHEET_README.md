# 📋 Jobsheet System - Ultimate Website

## 🎯 **Overview**
Jobsheet adalah sistem manajemen jadwal kerja interaktif yang terintegrasi dengan website Ultimate. Sistem ini dirancang untuk tracking aktivitas harian PIC (Person In Charge) dengan interface yang user-friendly dan fitur yang komprehensif.

## 🚀 **Quick Start**

### 1. **Access Jobsheet**
- Buka website Ultimate
- Login dengan akun yang valid
- Klik menu "Jobsheet" di navigation bar (antara Activities dan Logs)

### 2. **Basic Navigation**
- **Select Period**: Pilih bulan dan tahun yang diinginkan
- **View Data**: Lihat jadwal untuk semua PIC
- **Filter PIC**: Gunakan search box untuk mencari PIC tertentu

### 3. **Data Entry**
- **Single Cell**: Klik kanan pada sel untuk menu options
- **Multiple Cells**: Drag mouse untuk pilih range sel
- **Fill Data**: Pilih dari menu context (D, DT, E.D, dll)

## ✨ **Key Features**

### 📅 **Period Management**
- **21-20 System**: Periode dari tanggal 21 bulan ini sampai 20 bulan depan
- **Flexible Range**: Pilih periode dari 2 tahun lalu sampai 5 tahun ke depan
- **Auto Default**: Otomatis menampilkan periode bulan saat ini

### 👥 **PIC Management**
- **45 PICs**: Total 45 orang Person In Charge
- **Search Function**: Cari PIC berdasarkan nama
- **Sticky Column**: Kolom PIC tetap terlihat saat scroll horizontal

### 🎨 **Visual Indicators**
- **Weekend Colors**: Sabtu (pink), Minggu (merah)
- **Holiday Marking**: Hari libur nasional ditandai khusus
- **Status Colors**: Approved (hijau), Selected (biru), Special (kuning)

### 📊 **Data Entry Options**
```
D     - Duty
DT    - Duty + Training  
E.D   - Extra Duty
M.TLK - Maintenance - Talk
M.TCK - Maintenance - Check
M.TCD - Maintenance - Code
M.TLN - Maintenance - Line
I.TLK - Installation - Talk
I.TCK - Installation - Check
I.TCD - Installation - Code
I.TLN - Installation - Line
U.TLK - Update - Talk
U.TCK - Update - Check
U.TCD - Update - Code
U.TLN - Update - Line
```

## 🖱️ **User Interface**

### 🎯 **Cell Interaction**
- **Left Click**: Pilih sel
- **Right Click**: Buka context menu
- **Double Click**: Buka modal untuk notes
- **Drag & Drop**: Pilih multiple sel

### 📱 **Context Menu**
- **Fill Options**: Menu untuk mengisi data
- **Status Markers**: Mark as On Time/Late
- **Notes**: Add/Edit/Remove catatan
- **Approval**: Approve/Re-open sel
- **Clear**: Kosongkan sel

### ⌨️ **Keyboard Shortcuts**
- **Escape**: Tutup menu dan clear selection
- **Delete**: Clear selected cells
- **Ctrl+A**: Select all cells

## 🌓 **Theme Support**

### ☀️ **Light Mode**
- Background putih dengan border abu-abu
- Text hitam untuk kontras optimal
- Warna-warna cerah untuk indicators

### 🌙 **Dark Mode**
- Background gelap dengan border abu-abu terang
- Text putih untuk kontras optimal
- Warna-warna yang disesuaikan untuk tema gelap

### 🔄 **Auto Switch**
- Mengikuti tema sistem website
- Transisi smooth antar tema
- Konsisten dengan design system

## 📱 **Responsive Design**

### 🖥️ **Desktop (1200px+)**
- Tampilan penuh dengan semua fitur
- Context menu lengkap
- Hover effects aktif

### 📱 **Tablet (768px - 1199px)**
- Layout yang disesuaikan
- Menu yang dapat di-collapse
- Touch-friendly interactions

### 📱 **Mobile (< 768px)**
- Layout vertikal untuk sel
- Simplified context menu
- Optimized untuk touch

## 📤 **Export & Print**

### 📊 **Excel Export**
- **Format**: .xlsx (Excel 2007+)
- **Content**: Semua data termasuk notes dan indicators
- **Filename**: Otomatis sesuai periode
- **Structure**: Header dengan colspan, body dengan data lengkap

### 🖨️ **Print Function**
- **Layout**: Optimized untuk A4/Letter
- **Content**: Semua data terlihat jelas
- **Headers**: Sticky headers untuk navigasi
- **Colors**: Print-friendly colors

## 🔧 **Technical Details**

### 🛠️ **Technologies**
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Backend**: PHP 7.4+
- **Framework**: Bootstrap 5
- **Libraries**: XLSX.js, Iconify

### 📁 **File Structure**
```
jobsheet.php              # Main page
assets/css/jobsheet.css   # Styling
assets/js/jobsheet.js    # Functionality
```

### 🔌 **Dependencies**
- Bootstrap 5 (CSS & JS)
- XLSX.js library
- Iconify icons
- Custom CSS variables

## 🚀 **Performance Features**

### ⚡ **Optimization**
- **Lazy Loading**: CSS dan JS terpisah
- **Efficient DOM**: Minimal manipulation
- **Event Delegation**: Optimal event handling
- **Memory Management**: Proper cleanup

### 📈 **Scalability**
- **Modular Code**: JavaScript dalam class
- **CSS Organization**: Styling terstruktur
- **PHP Functions**: Reusable logic
- **Future Ready**: Easy to extend

## 🧪 **Browser Support**

### ✅ **Fully Supported**
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### ⚠️ **Limited Support**
- Internet Explorer 11 (basic functionality)
- Older mobile browsers (degraded experience)

## 🔮 **Future Roadmap**

### 🚧 **Phase 1 (Current)**
- ✅ Basic jobsheet functionality
- ✅ Period management
- ✅ PIC management
- ✅ Data entry
- ✅ Export/Print

### 🚧 **Phase 2 (Next)**
- 🔄 Database integration
- 🔄 User permissions
- 🔄 Audit trail
- 🔄 Real-time sync

### 🚧 **Phase 3 (Future)**
- 🔄 API integration
- 🔄 Mobile app
- 🔄 Advanced analytics
- 🔄 Multi-language support

## 🐛 **Troubleshooting**

### ❌ **Common Issues**

#### **Context Menu Tidak Muncul**
- Pastikan JavaScript enabled
- Cek console untuk error
- Refresh halaman

#### **Export Excel Gagal**
- Pastikan library XLSX.js ter-load
- Cek browser compatibility
- Coba browser berbeda

#### **Dark Mode Tidak Berfungsi**
- Pastikan CSS ter-load dengan benar
- Cek tema website
- Refresh halaman

### 🔧 **Solutions**

#### **JavaScript Errors**
```javascript
// Check if jobsheet manager is loaded
console.log(window.jobsheetManager);

// Check for XLSX library
console.log(typeof XLSX);
```

#### **CSS Issues**
```css
/* Force dark mode */
[data-bs-theme="dark"] .jobsheet-cell {
    background-color: #1f2937 !important;
}
```

#### **PHP Errors**
```php
// Check error log
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 **Support & Contact**

### 🆘 **Getting Help**
- **Documentation**: Lihat file ini dan docs/
- **Code Comments**: Semua kode memiliki komentar
- **Console Logs**: Check browser console untuk debug info

### 📧 **Contact Information**
- **Developer**: AI Assistant
- **Project**: Ultimate Website
- **Repository**: Local development

## 📝 **Changelog**

### **Version 1.0.0** (Current)
- ✅ Initial release
- ✅ Basic jobsheet functionality
- ✅ Period management
- ✅ PIC management
- ✅ Data entry system
- ✅ Export/Print features
- ✅ Dark mode support
- ✅ Responsive design

## 🎉 **Conclusion**

Jobsheet System adalah solusi komprehensif untuk manajemen jadwal kerja yang:
- **User-friendly** dengan interface intuitif
- **Feature-rich** dengan semua kebutuhan tracking
- **Responsive** untuk semua device
- **Scalable** untuk pengembangan masa depan
- **Maintainable** dengan code quality tinggi

Sistem ini siap digunakan untuk production dan dapat dikembangkan sesuai kebutuhan bisnis yang berkembang.



