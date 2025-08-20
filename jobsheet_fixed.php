<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'db.php';
include './partials/layouts/layoutHorizontal.php'; 
?>

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

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">Jobsheet Period</span>
                <select id="month-select" class="form-select form-select-sm w-auto">
                    <option value="7">AGUSTUS</option>
                    <option value="8">SEPTEMBER</option>
                </select>
                <select id="year-select" class="form-select form-select-sm w-auto">
                    <option value="2025">2025</option>
                </select>
                <button id="generate-btn" class="btn btn-success btn-sm">Generate</button>
            </div>
            <div class="d-flex gap-2">
                <input type="text" id="pic-search" class="form-control form-control-sm" placeholder="Search PIC..." style="width: 150px;">
                <button id="export-btn" class="btn btn-warning btn-sm">Export Excel</button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div id="table-container" class="table-responsive">
                <table id="jobsheet-table" class="table table-bordered mb-0">
                    <thead id="schedule-head" class="table-light"></thead>
                    <tbody id="schedule-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="context-menu" class="bg-white border border-gray-200 rounded shadow-lg py-1 w-48" style="position: absolute; display: none; z-index: 1000;">
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-bold" data-action="D">D</a>
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-bold" data-action="DT">DT</a>
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-bold" data-action="E.D">E.D</a>
        <div class="border-t my-1 border-gray-200"></div>
        <a href="#" class="block px-4 py-2 text-sm text-green-600 hover:bg-gray-100 font-bold" data-action="ontime">Mark as On Time</a>
        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-bold" data-action="telat">Mark as Late</a>
        <div class="border-t my-1 border-gray-200"></div>
        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-bold" data-action="">Clear</a>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<script>
const tableHead = document.getElementById('schedule-head');
const tableBody = document.getElementById('schedule-body');
const contextMenu = document.getElementById('context-menu');
const monthSelect = document.getElementById('month-select');
const yearSelect = document.getElementById('year-select');
const generateBtn = document.getElementById('generate-btn');
const exportBtn = document.getElementById('export-btn');
const picSearch = document.getElementById('pic-search');

const monthNames = ["JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER"];

const picNames = [
    "Akbar", "Aldi", "Andreas", "Apip", "Apri", "Arbi", "Aris", "Basir", "Bowo",
    "Danang", "Dhani", "Dhika", "Fachri", "Farhan", "Hanip", "Hasbi", "Ichsan",
    "Ichwan", "Ilham", "Imam", "Indra", "Iqhtiar", "Jaja", "Komeng", "Lifi",
    "Mamat", "Mulya", "Naufal", "Nur", "Prad", "Rafly", "Rama", "Rey", "Ridho",
    "Ridwan", "Rizky", "Robi", "Sahrul", "Sodik", "Vincent", "Wahyudi",
    "Widi", "Yosa", "Yudi","Ivan", "Tri", "Iam"
];

let currentDates = [];

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
        if (dayOfWeek === 0) columnClasses.push('sunday');
        else if (dayOfWeek === 6) columnClasses.push('saturday');
        else columnClasses.push('');
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return { dates, columnClasses, firstMonthInfo, secondMonthInfo };
};

const generateTable = async () => {
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value);
    const { dates, columnClasses, firstMonthInfo, secondMonthInfo } = getDatesAndClasses(selectedYear, selectedMonth);
    currentDates = dates;
    
    let headHTML = `<tr><th scope="col" class="text-center sticky-col" rowspan="2">PIC</th><th scope="col" class="text-center border-start" colspan="${firstMonthInfo.dayCount}">${firstMonthInfo.name} ${firstMonthInfo.year}</th><th scope="col" class="text-center border-start" colspan="${secondMonthInfo.dayCount}">${secondMonthInfo.name} ${secondMonthInfo.year}</th></tr><tr>`;
    
    dates.forEach((d, index) => {
        headHTML += `<th scope="col" class="text-center border-start ${columnClasses[index]}">${String(d.day).padStart(2, '0')}</th>`;
    });
    headHTML += `</tr>`;
    tableHead.innerHTML = headHTML;
    
    let bodyHTML = '';
    picNames.forEach((name, rowIndex) => {
        bodyHTML += `<tr class="table-light"><th scope="row" class="sticky-col">${name}</th>`;
        dates.forEach((d, colIndex) => {
            bodyHTML += `<td class="text-center border-start ${columnClasses[colIndex]}" data-row="${rowIndex}" data-col="${colIndex}"></td>`;
        });
        bodyHTML += `</tr>`;
    });
    tableBody.innerHTML = bodyHTML;

    try {
        const start = `${String(dates[0].day).padStart(2,'0')}-${String(dates[0].month+1).padStart(2,'0')}-${String(dates[0].year).slice(-2)}`;
        const last = dates[dates.length-1];
        const end = `${String(last.day).padStart(2,'0')}-${String(last.month+1).padStart(2,'0')}-${String(last.year).slice(-2)}`;
        
        const res = await fetch('jobsheet_get_period.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ start, end }), 
            credentials: 'same-origin' 
        });
        
        if (res.ok) {
            const rows = await res.json();
            if (Array.isArray(rows)) {
                rows.forEach(r => {
                    applyRecord(r);
                });
            }
        }
    } catch (error) {
        console.log('Error loading data:', error);
    }
};

const applyRecord = (r) => {
    const pic = (r.pic_name || '').toString().trim();
    const picIndex = picNames.findIndex(name => name.toUpperCase() === pic.toUpperCase());
    if (picIndex === -1) return;
    
    const dayKey = String(r.day || '').trim();
    const dateIndex = currentDates.findIndex(d => {
        const k = `${String(d.day).padStart(2,'0')}-${String(d.month+1).padStart(2,'0')}-${String(d.year).slice(-2)}`;
        return k === dayKey;
    });
    if (dateIndex === -1) return;
    
    const row = tableBody.querySelectorAll('tr')[picIndex];
    if (!row) return;
    
    const cell = row.querySelectorAll('td')[dateIndex];
    if (!cell) return;
    
    cell.innerHTML = '';
    if (r.value) cell.appendChild(document.createTextNode(r.value));
    
    if (r.ontime === true || r.ontime === 1 || r.ontime === '1') {
        const i = document.createElement('span'); 
        i.className = 'badge bg-success'; 
        i.textContent = '✓'; 
        cell.appendChild(i);
    }
    if (r.late === true || r.late === 1 || r.late === '1') {
        const i = document.createElement('span'); 
        i.className = 'badge bg-danger'; 
        i.textContent = '✗'; 
        cell.appendChild(i);
    }
    
    if (r.note) { 
        const n = document.createElement('div'); 
        n.className = 'small text-muted'; 
        n.textContent = r.note; 
        n.title = r.note; 
        cell.appendChild(n); 
    }
};

const getCellMeta = (cell) => {
    const rowIndex = parseInt(cell.dataset.row);
    const colIndex = parseInt(cell.dataset.col);
    const userName = picNames[rowIndex];
    const dateInfo = currentDates[colIndex];
    if (!dateInfo) return null;
    const dd = String(dateInfo.day).padStart(2, '0');
    const mm = String(dateInfo.month + 1).padStart(2, '0');
    const yy = String(dateInfo.year).slice(-2);
    const day = `${dd}-${mm}-${yy}`;
    const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
    const value = (mainContentNode ? mainContentNode.nodeValue : '').trim();
    const ontime = !!cell.querySelector('.badge.bg-success');
    const late = !!cell.querySelector('.badge.bg-danger');
    const note = '';
    return { user_id: '', pic_name: userName, day, value, ontime, late, note };
};

const saveOrDeleteCell = (cell) => {
    const meta = getCellMeta(cell);
    if (!meta) return;
    const shouldDelete = (!meta.value || meta.value === '') && !meta.ontime && !meta.late;
    
    if (shouldDelete) {
        fetch('jobsheet_delete.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ pic_name: meta.pic_name, day: meta.day }), 
            credentials: 'same-origin' 
        });
    } else {
        fetch('jobsheet_save.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify(meta), 
            credentials: 'same-origin' 
        });
    }
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
    if (typeof XLSX === 'undefined') {
        alert('XLSX library belum loaded. Tunggu sebentar dan coba lagi.');
        return;
    }
    
    const table = document.getElementById('jobsheet-table');
    const data = [];

    const headerRows = table.querySelectorAll('thead tr');
    const headerRow1 = ["PIC"];
    headerRows[0].querySelectorAll('th[colspan]').forEach(th => {
        const colspan = parseInt(th.getAttribute('colspan'));
        headerRow1.push(th.textContent);
        for (let i = 1; i < colspan; i++) {
            headerRow1.push("");
        }
    });
    data.push(headerRow1);

    const headerRow2 = [""];
    headerRows[1].querySelectorAll('th').forEach(th => {
        headerRow2.push(th.textContent);
    });
    data.push(headerRow2);

    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        
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

    const fileName = `Jobsheet_${monthNames[monthSelect.value]}_${yearSelect.value}.xlsx`;
    XLSX.writeFile(workbook, fileName);
};

let selectedCells = [];

const clearSelection = () => {
    tableBody.querySelectorAll('td.selected').forEach(c => c.classList.remove('selected'));
    selectedCells = [];
};

tableBody.addEventListener('mousedown', (e) => {
    const targetCell = e.target.closest('td');
    if (!targetCell || e.button !== 0) return;
    
    if (!targetCell.classList.contains('selected')) {
        clearSelection();
        targetCell.classList.add('selected');
        selectedCells = [targetCell];
    }
    
    contextMenu.style.display = 'none';
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
        contextMenu.style.top = `${e.pageY}px`;
        contextMenu.style.left = `${e.pageX}px`;
        contextMenu.style.display = 'block';
    }
});

document.addEventListener('click', (e) => {
    if (!contextMenu.contains(e.target)) {
        contextMenu.style.display = 'none';
    }
});

contextMenu.addEventListener('click', (e) => {
    e.preventDefault();
    const action = e.target.dataset.action;
    if (action === undefined || selectedCells.length === 0) return;
    
    selectedCells.forEach(cell => {
        if (action === 'ontime' || action === 'telat') {
            const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
            const mainText = mainContentNode ? mainContentNode.nodeValue.trim() : '';
            if (mainText.length > 0) {
                cell.querySelector('.badge')?.remove();
                const indicator = document.createElement('span');
                indicator.className = `badge ${action === 'ontime' ? 'bg-success' : 'bg-danger'}`;
                indicator.textContent = action === 'ontime' ? '✓' : '✗';
                cell.appendChild(indicator);
            }
            saveOrDeleteCell(cell);
        } else if (action === '') { // Clear
            cell.innerHTML = '';
            saveOrDeleteCell(cell);
        } else { // Fill D, DT, etc.
            const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
            if (mainContentNode) {
                mainContentNode.nodeValue = action;
            } else {
                cell.prepend(document.createTextNode(action));
            }
            saveOrDeleteCell(cell);
        }
    });
    
    contextMenu.style.display = 'none';
    clearSelection();
});

generateBtn.addEventListener('click', generateTable);
picSearch.addEventListener('input', filterPic);
exportBtn.addEventListener('click', exportToExcel);

document.addEventListener('DOMContentLoaded', generateTable);
</script>

<style>
.sticky-col {
    position: sticky;
    left: 0;
    background-color: #f8f9fa;
    z-index: 1;
}

.selected {
    background-color: #e3f2fd !important;
}

.saturday { background-color: #fff3cd; }
.sunday { background-color: #f8d7da; }
</style>

<?php include './partials/layouts/layoutBottom.php'; ?>

