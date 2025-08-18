<?php
// Session check
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'db.php';
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Jobsheet</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">Jobsheet</li>
        </ul>
    </div>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Judul Utama -->
        <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">
            JOBSHEET
        </h1>
        <h2 id="period-title" class="text-lg sm:text-xl font-medium text-center text-gray-600 dark:text-gray-400 mb-8">
            <!-- Judul periode akan diisi oleh JavaScript -->
        </h2>

        <!-- Form untuk memilih periode dan filter -->
        <div id="control-panel" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-8 flex flex-wrap items-center justify-center gap-4">
            <div>
                <label for="month-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Month</label>
                <select id="month-select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"></select>
            </div>
            <div>
                <label for="year-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                <select id="year-select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"></select>
            </div>
            <button id="generate-btn" class="self-end bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Generate Jobsheet
            </button>
            <button id="export-btn" class="self-end bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Export to Excel
            </button>
            <div class="border-l border-gray-300 dark:border-gray-600 h-10 mx-2 hidden sm:block"></div>
            <div class="w-full sm:w-auto">
                <label for="pic-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search PIC</label>
                <input type="text" id="pic-search" class="mt-1 block w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Ketik nama...">
            </div>
        </div>

        <!-- Kontainer untuk tabel agar bisa scroll -->
        <div id="table-container" class="shadow-lg rounded-xl overflow-hidden">
            <div class="overflow-auto" style="max-height: 70vh;">
                <table id="jobsheet-table" class="w-full text-sm text-left">
                    <thead id="schedule-head" class="text-xs text-gray-700 uppercase">
                        <!-- Header akan digenerate oleh JavaScript -->
                    </thead>
                    <tbody id="schedule-body">
                        <!-- Body akan digenerate oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <footer class="text-center mt-6 text-xs text-gray-500">
        </footer>
    </div>

    <!-- Menu Kustom untuk Klik Kanan -->
    <div id="context-menu" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg py-1 w-48 max-h-96 overflow-y-auto">
        <div id="fill-options">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="D">D</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="DT">DT</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="E.D">E.D</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="M.TLK">M.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="M.TCK">M.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="M.TCD">M.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="M.TLN">M.TLN</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="I.TLK">I.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="I.TCK">I.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="I.TCD">I.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="I.TLN">I.TLN</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="U.TLK">U.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="U.TCK">U.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="U.TCD">U.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="U.TLN">U.TLN</a>
        </div>
        <div id="marker-separator" class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
        <a href="#" id="menu-ontime" class="block px-4 py-2 text-sm text-green-600 dark:text-green-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="ontime">Mark as On Time</a>
        <a href="#" id="menu-telat" class="block px-4 py-2 text-sm text-red-600 dark:text-red-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="telat">Mark as Late</a>
        <a href="#" id="menu-add-note" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="add_note">Add Note</a>
        <a href="#" id="menu-remove-markers" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="remove_markers">Remove Mark & Note</a>
        <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
        <a href="#" id="menu-approve" class="block px-4 py-2 text-sm text-blue-600 dark:text-blue-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="approve">Approve</a>
        <a href="#" id="menu-unlock" class="block px-4 py-2 text-sm text-yellow-600 dark:text-yellow-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="unlock">Re-Open</a>
        <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
        <a href="#" id="menu-clear" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-bold" data-action="">Kosongkan</a>
    </div>

    <!-- Modal untuk menambah catatan -->
    <div id="note-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">Tambah/Ubah Catatan</h3>
                <div class="mt-2 px-7 py-3">
                    <input type="text" id="note-input" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" placeholder="Catatan singkat...">
                </div>
                <div class="items-center px-4 py-3 space-y-2 sm:space-y-0 sm:flex sm:space-x-2 sm:flex-row-reverse">
                    <button id="save-note-btn" class="w-full sm:w-auto px-4 py-2 bg-indigo-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-600 focus:outline-none">
                        Simpan
                    </button>
                     <button id="cancel-note-btn" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Library untuk export ke Excel -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<style>
    /* Menggunakan font Inter */
    body {
        font-family: 'Inter', sans-serif;
        -webkit-user-select: none; /* Safari */
        -ms-user-select: none; /* IE 10+ */
        user-select: none; /* Standard syntax */
    }
    
    /* --- ATURAN FREEZE PANEL --- */

    /* 1. Panel Kontrol Atas */
    #control-panel {
        position: -webkit-sticky;
        position: sticky;
        top: 1rem; /* Menempel dengan sedikit jarak dari atas */
        z-index: 40;
    }

    /* 2. Semua Header Tabel */
    #jobsheet-table thead th {
        position: -webkit-sticky;
        position: sticky;
        background-color: #e5e7eb; /* bg-gray-200 */
        z-index: 15;
    }
    .dark #jobsheet-table thead th {
        background-color: #0f172a; /* dark:bg-slate-900 - lebih gelap */
    }

    /* 3. Baris Header Pertama (Bulan) */
    #jobsheet-table thead tr:first-child th {
        top: 0;
    }

    /* 4. Baris Header Kedua (Tanggal) */
    #jobsheet-table thead tr:last-child th {
        top: 40px; /* Sesuaikan jika tinggi baris pertama berubah */
    }

    /* 5. Kolom PIC (Body & Header) */
    .sticky-col {
        position: -webkit-sticky;
        position: sticky;
        left: 0;
        z-index: 20; /* Lebih tinggi dari sel biasa */
    }

    /* 6. Sel Pojok Kiri Atas (PIC di Header) */
    #jobsheet-table thead .sticky-col {
        z-index: 30; /* z-index tertinggi agar selalu di atas */
    }
    
    /* 7. Latar Belakang Solid untuk Kolom PIC di Body */
    #jobsheet-table tbody .sticky-col {
        background-color: #f9fafb; /* bg-gray-50 */
    }
    .dark #jobsheet-table tbody .sticky-col {
        background-color: #0f172a; /* dark:bg-slate-900 - lebih gelap */
    }

    /* PERBAIKAN: Memastikan warna teks PIC selalu terlihat */
    #jobsheet-table .sticky-col {
        color: #111827; /* text-gray-900 */
    }
    .dark #jobsheet-table .sticky-col {
        color: #f9fafb; /* text-gray-50 */
    }

    /* Styling untuk sel yang dipilih */
    .selected {
        background-color: #aadeff !important;
        border: 1px solid #007bff;
    }
    .dark .selected {
        background-color: #0f172a !important;
        border: 1px solid #1e40af;
    }
    /* Styling untuk sel yang sudah diapprove */
    .approved {
        background-color: #d1fae5 !important;
        color: #065f46;
    }
    .dark .approved {
        background-color: #022c22 !important;
        color: #34d399;
    }
    /* Override warna seleksi jika sel tersebut juga sudah diapprove */
    .selected.approved {
        background-color: #ffdd99 !important;
        border: 1px solid #f59e0b;
    }
    .dark .selected.approved {
        background-color: #451a03 !important;
        border: 1px solid #d97706;
        color: #f59e0b;
    }
    /* Warna untuk hari spesial */
    .saturday { background-color: #fecdd3 !important; }
    .sunday { background-color: #fca5a5 !important; }
    .holiday { background-color: #e5e7eb !important; }
    .dark .saturday { background-color: #2e1065 !important; }
    .dark .sunday { background-color: #450a0a !important; }
    .dark .holiday { background-color: #1f2937 !important; }

    /* Styling untuk menu klik kanan kustom */
    #context-menu {
        position: absolute;
        display: none;
        z-index: 1000;
    }
    /* Styling untuk sel data agar berada di tengah */
    #schedule-body td {
        position: relative;
        text-align: center;
        font-weight: 500;
        padding-bottom: 16px;
    }

    /* Styling untuk indikator On Time / Telat */
    .indicator {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.7);
    }
    .ontime-indicator { background-color: #10b981; }
    .telat-indicator { background-color: #ef4444; }
    .dark .ontime-indicator { background-color: #047857; }
    .dark .telat-indicator { background-color: #b91c1c; }

    /* Styling untuk note di dalam sel */
    .note-text {
        position: absolute;
        bottom: 2px;
        left: 0;
        right: 0;
        font-size: 9px;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0 2px;
    }
    .dark .note-text {
        color: #9ca3af;
    }

    /* Dark mode support for existing elements */
    .dark .bg-white {
        background-color: #1f2937 !important;
    }
    
    .dark .text-gray-700 {
        color: #d1d5db !important;
    }
    
    .dark .border-gray-300 {
        border-color: #4b5563 !important;
    }
    
    .dark .border-gray-200 {
        border-color: #4b5563 !important;
    }
</style>

<script>
    // --- PENGATURAN UTAMA & GENERASI TABEL ---
    const tableHead = document.getElementById('schedule-head');
    const tableBody = document.getElementById('schedule-body');
    const contextMenu = document.getElementById('context-menu');
    const monthSelect = document.getElementById('month-select');
    const yearSelect = document.getElementById('year-select');
    const generateBtn = document.getElementById('generate-btn');
    const exportBtn = document.getElementById('export-btn');
    const periodTitle = document.getElementById('period-title');
    const noteModal = document.getElementById('note-modal');
    const noteInput = document.getElementById('note-input');
    const saveNoteBtn = document.getElementById('save-note-btn');
    const cancelNoteBtn = document.getElementById('cancel-note-btn');
    const picSearch = document.getElementById('pic-search');

    const monthNames = ["JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER"];
    
    const picNames = [
        "AKBAR", "ALDI", "ANDREAS", "APIP", "APRI", "ARBI", "ARIS", "BASIR", "BOWO",
        "DANANG", "DHANI", "DHIKA", "FACHRI", "FARHAN", "HANIP", "HASBI", "ICHSAN",
        "ICHWAN", "ILHAM", "IMAM", "INDRA", "IQHTIAR", "JAJA", "KOMENG", "LIFIM",
        "MAMAT", "MULYA", "NAUFAL", "NUR", "PRAD", "RAFLY", "RAMA", "REY", "RIDHO",
        "RIDWAN", "RIZKY", "ROBI", "SAHRUL", "SODIK", "VINCENT", "WAHYUDI",
        "WIDI", "YOSA", "YUDI"
    ];

    const nationalHolidays = [
        '2025-01-01', '2025-01-27', '2025-01-29', '2025-03-29', '2025-03-31', 
        '2025-04-01', '2025-04-18', '2025-05-01', '2025-05-12', '2025-05-29', 
        '2025-06-01', '2025-06-06', '2025-06-27', '2025-08-17', '2025-09-05', '2025-12-25'
    ];

    const populateSelectors = () => {
        monthNames.forEach((name, index) => monthSelect.add(new Option(name, index)));
        const currentYear = new Date().getFullYear();
        for (let i = currentYear - 2; i <= currentYear + 5; i++) yearSelect.add(new Option(i, i));
        monthSelect.value = 7;
        yearSelect.value = 2025;
    };

    const getDatesAndClasses = (startYear, startMonth) => {
        const dates = [], columnClasses = [];
        const startDate = new Date(startYear, startMonth, 21);
        const endDate = new Date(startYear, startMonth + 1, 20);
        const firstMonthInfo = { name: monthNames[startDate.getMonth()], year: startDate.getFullYear(), dayCount: 0 };
        const secondMonthInfo = { name: monthNames[endDate.getMonth()], year: endDate.getFullYear(), dayCount: 0 };
        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            dates.push({ year: currentDate.getFullYear(), month: currentDate.getMonth(), day: currentDate.getDate() });
            if (currentDate.getMonth() === startDate.getMonth()) firstMonthInfo.dayCount++;
            else secondMonthInfo.dayCount++;
            const dayOfWeek = currentDate.getDay();
            const dateString = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;
            if (nationalHolidays.includes(dateString)) columnClasses.push('holiday');
            else if (dayOfWeek === 0) columnClasses.push('sunday');
            else if (dayOfWeek === 6) columnClasses.push('saturday');
            else columnClasses.push('');
            currentDate.setDate(currentDate.getDate() + 1);
        }
        return { dates, columnClasses, firstMonthInfo, secondMonthInfo };
    };

    const generateTable = () => {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        const { dates, columnClasses, firstMonthInfo, secondMonthInfo } = getDatesAndClasses(selectedYear, selectedMonth);
        periodTitle.textContent = `PERIODE ${firstMonthInfo.name} ${firstMonthInfo.year} - ${secondMonthInfo.name} ${secondMonthInfo.year}`;
        
        let headHTML = `<tr><th scope="col" class="px-6 py-4 text-center sticky-col" rowspan="2"></th><th scope="col" class="px-6 py-3 text-center border-l border-gray-300 dark:border-gray-600" colspan="${firstMonthInfo.dayCount}">${firstMonthInfo.name} ${firstMonthInfo.year}</th><th scope="col" class="px-6 py-3 text-center border-l border-gray-300 dark:border-gray-600" colspan="${secondMonthInfo.dayCount}">${secondMonthInfo.name} ${secondMonthInfo.year}</th></tr><tr>`;
        
        dates.forEach((d, index) => {
            headHTML += `<th scope="col" class="px-3 py-3 text-center border-l border-gray-300 dark:border-gray-600 ${columnClasses[index]}">${String(d.day).padStart(2, '0')}</th>`;
        });
        headHTML += `</tr>`;
        tableHead.innerHTML = headHTML;
        let bodyHTML = '';
        picNames.forEach((name, rowIndex) => {
            const rowClass = "bg-white dark:bg-slate-900";
            bodyHTML += `<tr class="${rowClass} border-b dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors duration-200"><th scope="row" class="px-6 py-3 font-medium whitespace-nowrap sticky-col">${name}</th>`;
            dates.forEach((d, colIndex) => {
                bodyHTML += `<td class="px-3 py-3 border-l border-gray-200 dark:border-gray-700 cursor-pointer ${columnClasses[colIndex]}" data-row="${rowIndex}" data-col="${colIndex}"></td>`;
            });
            bodyHTML += `</tr>`;
        });
        tableBody.innerHTML = bodyHTML;
        updateStickyOffsets();
    };

    const updateStickyOffsets = () => {
        const firstHeaderRow = tableHead.querySelector('tr:first-child');
        if (!firstHeaderRow) return;
        const firstRowHeight = firstHeaderRow.getBoundingClientRect().height;
        tableHead.querySelectorAll('tr:last-child th').forEach(th => {
            th.style.top = `${firstRowHeight}px`;
        });
    };
    
    const filterPic = () => {
        const searchTerm = picSearch.value.toUpperCase();
        const rows = tableBody.getElementsByTagName('tr');
        for (let row of rows) {
            const picName = row.getElementsByTagName('th')[0].textContent.toUpperCase();
            if (picName.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    };

    const exportToExcel = () => {
        const table = document.getElementById('jobsheet-table');
        const data = [];

        // Proses Header
        const headerRows = table.querySelectorAll('thead tr');
        const headerRow1 = [];
        headerRow1.push("PIC"); // Kolom A1
        headerRows[0].querySelectorAll('th[colspan]').forEach(th => {
            const colspan = parseInt(th.getAttribute('colspan'));
            headerRow1.push(th.textContent);
            for (let i = 1; i < colspan; i++) {
                headerRow1.push(""); // Isi sel merged
            }
        });
        data.push(headerRow1);

        const headerRow2 = [];
        headerRow2.push(""); // Kolom A2 kosong
        headerRows[1].querySelectorAll('th').forEach(th => {
            headerRow2.push(th.textContent);
        });
        data.push(headerRow2);

        // Proses Body
        table.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.style.display === 'none') return; // Skip baris yang terfilter
            
            const rowData = [];
            tr.querySelectorAll('th, td').forEach(cell => {
                const mainContent = (Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE)?.nodeValue || '').trim();
                rowData.push(mainContent);
            });
            data.push(rowData);
        });

        const worksheet = XLSX.utils.aoa_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Jobsheet");

        // Membuat nama file dinamis
        const fileName = `Jobsheet_${periodTitle.textContent.replace(/\s/g, '_')}.xlsx`;
        XLSX.writeFile(workbook, fileName);
    };

    generateBtn.addEventListener('click', generateTable);
    picSearch.addEventListener('input', filterPic);
    exportBtn.addEventListener('click', exportToExcel);
    document.addEventListener('DOMContentLoaded', () => {
        populateSelectors();
        generateTable();
        updateStickyOffsets();
        setTimeout(updateStickyOffsets, 0);
    });
    window.addEventListener('resize', updateStickyOffsets);
    window.addEventListener('load', updateStickyOffsets);

    // --- LOGIKA SELEKSI, MENU KONTEKS, DAN APPROVAL ---
    let isSelecting = false, isDragging = false, startCell = null, selectedCells = [];
    
    const clearSelection = () => {
        tableBody.querySelectorAll('td.selected').forEach(c => c.classList.remove('selected'));
        selectedCells = [];
    };
    
    const highlightCells = (start, end) => {
        clearSelection();
        if (!start || !end) return;
        const r1 = Math.min(parseInt(start.dataset.row), parseInt(end.dataset.row)), r2 = Math.max(parseInt(start.dataset.row), parseInt(end.dataset.row));
        const c1 = Math.min(parseInt(start.dataset.col), parseInt(end.dataset.col)), c2 = Math.max(parseInt(start.dataset.col), parseInt(end.dataset.col));
        for (let r = r1; r <= r2; r++) for (let c = c1; c <= c2; c++) tableBody.querySelector(`td[data-row='${r}'][data-col='${c}']`)?.classList.add('selected');
    };

    const openNoteModal = () => {
        noteInput.value = selectedCells.length > 0 && selectedCells[0].dataset.note ? selectedCells[0].dataset.note : '';
        noteModal.classList.remove('hidden');
        noteInput.focus();
    };
    const closeNoteModal = () => noteModal.classList.add('hidden');

    saveNoteBtn.addEventListener('click', () => {
        const noteText = noteInput.value.trim();
        selectedCells.forEach(cell => {
            cell.querySelector('.note-text')?.remove();
            if (noteText) {
                cell.dataset.note = noteText;
                const noteEl = document.createElement('div');
                noteEl.className = 'note-text';
                noteEl.textContent = noteText;
                noteEl.title = noteText;
                cell.appendChild(noteEl);
            } else delete cell.dataset.note;
        });
        closeNoteModal();
        clearSelection();
    });
    cancelNoteBtn.addEventListener('click', closeNoteModal);

    tableBody.addEventListener('mousedown', (e) => {
        const targetCell = e.target.closest('td');
        if (!targetCell || e.button !== 0) return;
        isSelecting = true;
        isDragging = false;
        startCell = targetCell;
        contextMenu.style.display = 'none';
    });

    tableBody.addEventListener('mouseover', (e) => {
        if (isSelecting) {
            isDragging = true;
            const targetCell = e.target.closest('td');
            if (targetCell) highlightCells(startCell, targetCell);
        }
    });

    document.addEventListener('mouseup', (e) => {
        if (isSelecting) {
            if (!isDragging) { // This was a click, not a drag
                const targetCell = startCell;
                if (!targetCell.classList.contains('selected')) {
                    clearSelection();
                    targetCell.classList.add('selected');
                }
            }
            selectedCells = Array.from(tableBody.querySelectorAll('td.selected'));
            isSelecting = false;
        }
    });

    tableBody.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        const targetCell = e.target.closest('td');
        if (!targetCell) return;

        if (!targetCell.classList.contains('selected')) {
            clearSelection();
            targetCell.classList.add('selected');
            selectedCells = [targetCell];
        }
        
        if (selectedCells.length > 0) {
            const hasApproved = selectedCells.some(c => c.dataset.approved === 'true');
            const hasUnapproved = selectedCells.some(c => c.dataset.approved !== 'true');
            const mainContentNode = Array.from(targetCell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
            const mainText = mainContentNode ? mainContentNode.nodeValue.trim() : '';
            const hasContent = mainText.length > 0 && !hasApproved;
            const hasMarkers = selectedCells.some(c => c.querySelector('.indicator') || c.dataset.note);

            // Show/hide menu items based on context
            document.querySelectorAll('#fill-options a').forEach(el => el.style.display = hasUnapproved ? 'block' : 'none');
            document.querySelectorAll('#fill-options div').forEach(el => el.style.display = hasUnapproved ? 'block' : 'none');
            document.getElementById('menu-approve').style.display = hasUnapproved ? 'block' : 'none';
            document.getElementById('menu-unlock').style.display = hasApproved ? 'block' : 'none';
            document.getElementById('menu-clear').style.display = hasUnapproved ? 'block' : 'none';
            document.getElementById('menu-ontime').style.display = hasContent ? 'block' : 'none';
            document.getElementById('menu-telat').style.display = hasContent ? 'block' : 'none';
            document.getElementById('menu-add-note').style.display = hasContent ? 'block' : 'none';
            document.getElementById('menu-remove-markers').style.display = hasMarkers && hasUnapproved ? 'block' : 'none';
            document.getElementById('marker-separator').style.display = hasContent || (hasMarkers && hasUnapproved) ? 'block' : 'none';

            contextMenu.style.top = `${e.pageY}px`;
            contextMenu.style.left = `${e.pageX}px`;
            contextMenu.style.display = 'block';
        }
    });

    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target) && !noteModal.contains(e.target)) {
            contextMenu.style.display = 'none';
            // Clear selection if clicking outside the table container and the top controls
            if (!e.target.closest('#table-container') && !e.target.closest('#control-panel')) {
                clearSelection();
            }
        }
    });

    contextMenu.addEventListener('click', (e) => {
        e.preventDefault();
        const action = e.target.dataset.action;
        if (action === undefined || selectedCells.length === 0) return;
        if (action === 'add_note') {
            openNoteModal();
            contextMenu.style.display = 'none';
            return;
        }
        selectedCells.forEach(cell => {
            if (action === 'approve') {
                if (cell.dataset.approved !== 'true') {
                    cell.dataset.approved = 'true';
                    cell.classList.add('approved');
                }
            } else if (action === 'unlock') {
                if (cell.dataset.approved === 'true') {
                    delete cell.dataset.approved;
                    cell.classList.remove('approved');
                }
            } else if (action === 'ontime' || action === 'telat') {
                const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                const mainText = mainContentNode ? mainContentNode.nodeValue.trim() : '';
                if (mainText.length > 0 && cell.dataset.approved !== 'true') {
                    cell.querySelector('.indicator')?.remove();
                    const indicator = document.createElement('span');
                    indicator.className = `indicator ${action === 'ontime' ? 'ontime-indicator' : 'telat-indicator'}`;
                    cell.appendChild(indicator);
                }
            } else if (action === 'remove_markers') {
                if (cell.dataset.approved !== 'true') {
                    cell.querySelector('.indicator')?.remove();
                    cell.querySelector('.note-text')?.remove();
                    delete cell.dataset.note;
                }
            } else { // Fill D, DT, or Clear
                if (cell.dataset.approved !== 'true') {
                    const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                    if (action === '') { // Clear action
                        if (mainContentNode) mainContentNode.nodeValue = '';
                        cell.querySelector('.indicator')?.remove();
                        cell.querySelector('.note-text')?.remove();
                        delete cell.dataset.note;
                    } else {
                        if (mainContentNode) {
                            mainContentNode.nodeValue = action;
                        } else {
                            cell.prepend(document.createTextNode(action));
                        }
                    }
                }
            }
        });
        contextMenu.style.display = 'none';
        clearSelection();
    });
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
