<?php
require_once "db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$picNames = [];
try {
    $stmt = $pdo->query("SELECT id, display_name, full_name FROM users ORDER BY full_name");
    while ($row = $stmt->fetch()) {
        $picNames[] = [
            "id" => $row["id"],
            "display_name" => $row["display_name"],
            "name" => $row["full_name"]
        ];
    }
} catch (PDOException $e) {
    $picNames = [
        ["id" => 1, "display_name" => "AKBAR", "name" => "Akbar"],
        ["id" => 2, "display_name" => "ALDI", "name" => "Aldi"],
        ["id" => 3, "display_name" => "ANDREAS", "name" => "Andreas"],
        ["id" => 4, "display_name" => "APIP", "name" => "Apip"],
        ["id" => 5, "display_name" => "APRI", "name" => "Apri"],
        ["id" => 6, "display_name" => "ARBI", "name" => "Arbi"],
        ["id" => 7, "display_name" => "ARIS", "name" => "Aris"],
        ["id" => 8, "display_name" => "BASIR", "name" => "Basir"],
        ["id" => 9, "display_name" => "BOWO", "name" => "Bowo"],
        ["id" => 10, "display_name" => "DANANG", "name" => "Danang"],
        ["id" => 11, "display_name" => "DHANI", "name" => "Dhani"],
        ["id" => 12, "display_name" => "DHIKA", "name" => "Dhika"],
        ["id" => 13, "display_name" => "FACHRI", "name" => "Fachri"],
        ["id" => 14, "display_name" => "FARHAN", "name" => "Farhan"],
        ["id" => 15, "display_name" => "HANIP", "name" => "Hanip"],
        ["id" => 16, "display_name" => "HASBI", "name" => "Hasbi"],
        ["id" => 17, "display_name" => "ICHSAN", "name" => "Ichsan"],
        ["id" => 18, "display_name" => "ICHWAN", "name" => "Ichwan"],
        ["id" => 19, "display_name" => "ILHAM", "name" => "Ilham"],
        ["id" => 20, "display_name" => "IMAM", "name" => "Imam"],
        ["id" => 21, "display_name" => "INDRA", "name" => "Indra"],
        ["id" => 22, "display_name" => "IQHTIAR", "name" => "Iqhtiar"],
        ["id" => 23, "display_name" => "JAJA", "name" => "Jaja"],
        ["id" => 24, "display_name" => "KOMENG", "name" => "Komeng"],
        ["id" => 25, "display_name" => "LIFI", "name" => "Lifi"],
        ["id" => 26, "display_name" => "MAMAT", "name" => "Mamat"],
        ["id" => 27, "display_name" => "MULYA", "name" => "Mulya"],
        ["id" => 28, "display_name" => "NAUFAL", "name" => "Naufal"],
        ["id" => 29, "display_name" => "NUR", "name" => "Nur"],
        ["id" => 30, "display_name" => "PRAD", "name" => "Prad"],
        ["id" => 31, "display_name" => "RAFLY", "name" => "Rafly"],
        ["id" => 32, "display_name" => "RAMA", "name" => "Rama"],
        ["id" => 33, "display_name" => "REY", "name" => "Rey"],
        ["id" => 34, "display_name" => "RIDHO", "name" => "Ridho"],
        ["id" => 35, "display_name" => "RIDWAN", "name" => "Ridwan"],
        ["id" => 36, "display_name" => "RIZKY", "name" => "Rizky"],
        ["id" => 37, "display_name" => "ROBI", "name" => "Robi"],
        ["id" => 38, "display_name" => "SAHRUL", "name" => "Sahrul"],
        ["id" => 39, "display_name" => "SODIK", "name" => "Sodik"],
        ["id" => 40, "display_name" => "VINCENT", "name" => "Vincent"],
        ["id" => 41, "display_name" => "WAHYUDI", "name" => "Wahyudi"],
        ["id" => 42, "display_name" => "WIDI", "name" => "Widi"],
        ["id" => 43, "display_name" => "YOSA", "name" => "Yosa"],
        ["id" => 44, "display_name" => "YUDI", "name" => "Yudi"],
        ["id" => 45, "display_name" => "IVAN", "name" => "Ivan"],
        ["id" => 46, "display_name" => "TRI", "name" => "Tri"],
        ["id" => 47, "display_name" => "IAM", "name" => "Iam"]
    ];
}
?>

<?php include "./partials/layouts/layoutHorizontal.php"; ?>

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
        <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">JOBSHEET</h1>
        <h2 id="period-title" class="text-lg sm:text-xl font-medium text-center text-gray-600 dark:text-gray-400 mb-8"></h2>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-8 flex flex-wrap items-center justify-center gap-4">
            <div>
                <label for="month-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan Mulai</label>
                <select id="month-select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"></select>
            </div>
            <div>
                <label for="year-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                <select id="year-select" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"></select>
            </div>
            <button id="generate-btn" class="self-end bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out">Generate Jobsheet</button>
            <button id="export-btn" class="self-end bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out">Export to Excel</button>
            <div class="border-l border-gray-300 dark:border-gray-600 h-10 mx-2 hidden sm:block"></div>
            <div class="w-full sm:w-auto">
                <label for="pic-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari PIC</label>
                <input type="text" id="pic-search" class="mt-1 block w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Ketik nama...">
            </div>
        </div>

        <div id="table-container" class="shadow-lg rounded-xl overflow-hidden">
            <div class="overflow-auto" style="max-height: 70vh; overflow-x: auto;">
                <table id="jobsheet-table" class="w-full text-sm text-left">
                    <thead id="schedule-head" class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700"></thead>
                    <tbody id="schedule-body"></tbody>
                </table>
            </div>
        </div>
        <footer class="text-center mt-6 text-xs text-gray-500">Klik dan geser untuk memilih rentang tanggal. Klik kanan untuk opsi.</footer>
    </div>
</div>

<style>
html[data-theme="dark"] body,
html[data-theme="dark"] .dashboard-main-body {
    background-color: #0b1220 !important;
}

html[data-theme="light"] body,
html[data-theme="light"] .dashboard-main-body {
    background-color: #f8fafc !important;
}

body {
    -webkit-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Header freeze seperti group */
thead th {
    position: sticky;
    top: 0;
    background: #374151;
    color: #ffffff;
    border: 1px solid #6b7280;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.sticky-col {
    position: sticky;
    left: 0;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    border-right: 2px solid #6b7280;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    text-align: center;
    vertical-align: middle;
    padding: 8px;
    width: 100px;
    min-width: 100px;
    max-width: 100px;
    box-sizing: border-box;
    z-index: 100;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
}

thead .sticky-col {
    z-index: 100;
    height: auto;
    min-height: auto;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
    width: 100px;
    min-width: 100px;
    max-width: 100px;
    box-sizing: border-box;
}

[data-theme="light"] .sticky-col {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
    border-right: 2px solid #9ca3af !important;
    color: #374151 !important;
    text-shadow: none !important;
}

#schedule-body td, #schedule-body th {
    height: 60px !important;
    min-height: 60px !important;
    vertical-align: middle !important;
    text-align: center !important;
}

thead tr:last-child th {
    top: 40px;
    background: #374151;
    color: #ffffff;
    border: 1px solid #6b7280;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

thead tr:last-child th.sticky-col {
    top: 40px;
    left: 0;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: #ffffff;
    border-right: 2px solid #6b7280;
    z-index: 100;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
}

/* Simple table layout */
#jobsheet-table {
    border-collapse: collapse;
    width: 100%;
}

#jobsheet-table th,
#jobsheet-table td {
    border: 1px solid #e5e7eb;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
}

/* Simple table structure */
#schedule-head {
    background: #f3f4f6;
}

#schedule-body tr:nth-child(even) {
    background: #f9fafb;
}

#schedule-body tr:nth-child(odd) {
    background: #ffffff;
}







.selected {
    background-color: #aadeff !important;
    border: 1px solid #007bff;
}

.approved {
    background-color: #d1fae5 !important;
    color: #065f46;
}

[data-theme="dark"] .approved {
    background-color: #064e3b !important;
    color: #a7f3d0;
}

.selected.approved {
    background-color: #ffdd99 !important;
    border: 1px solid #f59e0b;
}

[data-theme="dark"] .selected.approved {
    background-color: #b45309 !important;
    border: 1px solid #f59e0b;
}

.saturday { background-color: #fecdd3 !important; }
.sunday { background-color: #fca5a5 !important; }
.holiday { background-color: #e5e7eb !important; }
[data-theme="dark"] .saturday { background-color: #881337 !important; }
[data-theme="dark"] .sunday { background-color: #991b1b !important; }
[data-theme="dark"] .holiday { background-color: #374151 !important; }

#context-menu {
    position: absolute;
    display: none;
    z-index: 1000;
}

#schedule-body td {
    position: relative;
    text-align: center;
    font-weight: 500;
    padding: 8px;
    border: 1px solid #e5e7eb;
}

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
[data-theme="dark"] .note-text {
    color: #9ca3af;
}

[data-theme="light"] thead th {
    color: #374151 !important;
}

[data-theme="dark"] #context-menu {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.3) !important;
}

[data-theme="dark"] #context-menu a {
    color: #e5e7eb !important;
}

[data-theme="dark"] #context-menu a:hover {
    background-color: #374151 !important;
}

[data-theme="dark"] #context-menu .border-t {
    border-color: #4b5563 !important;
}

[data-theme="light"] #context-menu {
    background-color: #ffffff !important;
    border-color: #e5e7eb !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.05) !important;
}

[data-theme="light"] #context-menu a {
    color: #374151 !important;
}

[data-theme="light"] #context-menu a:hover {
    background-color: #f3f4f6 !important;
}

[data-theme="light"] #context-menu .border-t {
    border-color: #d1d5db !important;
}

[data-theme="dark"] #note-modal .bg-white {
    background-color: #1f2937 !important;
}

[data-theme="dark"] #note-modal .text-gray-900 {
    color: #e5e7eb !important;
}

[data-theme="dark"] #note-modal .text-gray-200 {
    color: #e5e7eb !important;
}

[data-theme="dark"] #note-modal .border-gray-200 {
    border-color: #374151 !important;
}

[data-theme="dark"] #note-modal .border-gray-600 {
    border-color: #4b5563 !important;
}

[data-theme="dark"] #note-modal .bg-gray-700 {
    background-color: #111827 !important;
}

[data-theme="dark"] #note-modal .bg-gray-600 {
    background-color: #374151 !important;
}

[data-theme="dark"] #note-modal .bg-gray-500 {
    background-color: #6b7280 !important;
}

[data-theme="light"] #note-modal .bg-white {
    background-color: #ffffff !important;
}

[data-theme="light"] #note-modal .text-gray-900 {
    color: #111827 !important;
}

[data-theme="light"] #note-modal .text-gray-200 {
    color: #374151 !important;
}

[data-theme="light"] #note-modal .border-gray-200 {
    border-color: #e5e7eb !important;
}

[data-theme="light"] #note-modal .border-gray-600 {
    border-color: #4b5563 !important;
}

[data-theme="light"] #note-modal .bg-gray-700 {
    background-color: #374151 !important;
}

[data-theme="light"] #note-modal .bg-gray-600 {
    background-color: #4b5563 !important;
}

[data-theme="light"] #note-modal .bg-gray-500 {
    background-color: #6b7280 !important;
}
</style>

<!-- XLSX Library for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<?php include "./partials/layouts/layoutBottom.php"; ?>

    <!-- Menu Kustom untuk Klik Kanan -->
    <div id="context-menu" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg py-1 w-48 max-h-96 overflow-y-auto">
        <div id="fill-options">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="D">D</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="DT">DT</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="E.D">E.D</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="M.TLK">M.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="M.TCK">M.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="M.TCD">M.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="M.TLN">M.TLN</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="I.TLK">I.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="I.TCK">I.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="I.TCD">I.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="I.TLN">I.TLN</a>
            <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="U.TLK">U.TLK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="U.TCK">U.TCK</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="U.TCD">U.TCD</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="U.TLN">U.TLN</a>
        </div>
        <div id="marker-separator" class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
        <a href="#" id="menu-ontime" class="block px-4 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="ontime">Tandai On Time</a>
        <a href="#" id="menu-telat" class="block px-4 py-2 text-sm text-red-600 dark:text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="telat">Tandai Telat</a>
    <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="add_note">Tambah Catatan</a>
        <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
        <a href="#" id="menu-approve" class="block px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="approve">Approve</a>
        <a href="#" id="menu-unlock" class="block px-4 py-2 text-sm text-yellow-600 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="unlock">Re-Open</a>
        <div class="border-t my-1 border-gray-200 dark:border-gray-600"></div>
    <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" data-action="">Kosongkan</a>
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
                <button id="save-note-btn" class="w-full sm:w-auto px-4 py-2 bg-indigo-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-indigo-600 focus:outline-none">Simpan</button>
                 <button id="cancel-note-btn" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
const tableHead = document.getElementById("schedule-head");
const tableBody = document.getElementById("schedule-body");
const contextMenu = document.getElementById("context-menu");
const monthSelect = document.getElementById("month-select");
const yearSelect = document.getElementById("year-select");
const generateBtn = document.getElementById("generate-btn");
const exportBtn = document.getElementById("export-btn");
const periodTitle = document.getElementById("period-title");
const noteModal = document.getElementById("note-modal");
const noteInput = document.getElementById("note-input");
const saveNoteBtn = document.getElementById("save-note-btn");
const cancelNoteBtn = document.getElementById("cancel-note-btn");
const picSearch = document.getElementById("pic-search");

        const monthNames = ["JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER"];
        
const picNames = <?php echo json_encode(array_column($picNames, "name")); ?>;
        
        const nationalHolidays = [
    "2025-01-01", "2025-01-27", "2025-01-29", "2025-03-29", "2025-03-31", 
    "2025-04-01", "2025-04-18", "2025-05-01", "2025-05-12", "2025-05-29", 
    "2025-06-01", "2025-06-06", "2025-06-27", "2025-08-17", "2025-09-05", "2025-12-25"
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
        const dateString = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, "0")}-${String(currentDate.getDate()).padStart(2, "0")}`;
        if (nationalHolidays.includes(dateString)) columnClasses.push("holiday");
        else if (dayOfWeek === 0) columnClasses.push("sunday");
        else if (dayOfWeek === 6) columnClasses.push("saturday");
        else columnClasses.push("");
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return { dates, columnClasses, firstMonthInfo, secondMonthInfo };
        };

        const generateTable = () => {
            const selectedYear = parseInt(yearSelect.value);
            const selectedMonth = parseInt(monthSelect.value);
            const { dates, columnClasses, firstMonthInfo, secondMonthInfo } = getDatesAndClasses(selectedYear, selectedMonth);
            periodTitle.textContent = `PERIODE ${firstMonthInfo.name} ${firstMonthInfo.year} - ${secondMonthInfo.name} ${secondMonthInfo.year}`;
            
            let headHTML = `<tr><th scope="col" class="px-6 py-4 text-center sticky-col bg-gray-200 dark:bg-gray-700" rowspan="2">PIC</th><th scope="col" class="px-6 py-3 text-center border-l border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700" colspan="${firstMonthInfo.dayCount}">${firstMonthInfo.name} ${firstMonthInfo.year}</th><th scope="col" class="px-6 py-3 text-center border-l border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700" colspan="${secondMonthInfo.dayCount}">${secondMonthInfo.name} ${secondMonthInfo.year}</th></tr><tr>`;
            
            dates.forEach((d, index) => {
        headHTML += `<th scope="col" class="px-3 py-3 text-center border-l border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700 ${columnClasses[index]}">${String(d.day).padStart(2, "0")}</th>`;
            });
            headHTML += `</tr>`;
            tableHead.innerHTML = headHTML;
    let bodyHTML = "";
            picNames.forEach((name, rowIndex) => {
                const rowClass = rowIndex % 2 === 0 
                    ? "bg-white dark:bg-gray-800" 
                    : "bg-gray-50 dark:bg-gray-700";
                bodyHTML += `<tr class="${rowClass} border-b dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"><th scope="row" class="px-6 py-3 font-medium whitespace-nowrap sticky-col ${rowClass} text-gray-900 dark:text-white">${name}</th>`;
                dates.forEach((d, colIndex) => {
                    bodyHTML += `<td class="px-3 py-3 border-l border-gray-200 dark:border-gray-700 cursor-pointer ${columnClasses[colIndex]}" data-row="${rowIndex}" data-col="${colIndex}"></td>`;
                });
                bodyHTML += `</tr>`;
            });
            tableBody.innerHTML = bodyHTML;
        };
        
        const filterPic = () => {
            const searchTerm = picSearch.value.toUpperCase();
    const rows = tableBody.getElementsByTagName("tr");
            for (let row of rows) {
        const picName = row.getElementsByTagName("th")[0].textContent.toUpperCase();
                if (picName.includes(searchTerm)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        };

        const exportToExcel = () => {
    const table = document.getElementById("jobsheet-table");
            const data = [];

    const headerRows = table.querySelectorAll("thead tr");
            const headerRow1 = [];
    headerRow1.push("");
    headerRows[0].querySelectorAll("th").forEach(th => {
        if (th.hasAttribute("colspan")) {
            const colspan = parseInt(th.getAttribute("colspan"));
                    headerRow1.push(th.textContent);
                    for (let i = 1; i < colspan; i++) {
                headerRow1.push("");
                    }
                }
            });
            data.push(headerRow1);

            const headerRow2 = [];
    headerRows[1].querySelectorAll("th").forEach(th => {
                headerRow2.push(th.textContent);
            });
            data.push(headerRow2);

    table.querySelectorAll("tbody tr").forEach(tr => {
        if (tr.style.display === "none") return;
                
                const rowData = [];
        tr.querySelectorAll("th, td").forEach(cell => {
            const mainContent = (Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE)?.nodeValue || "").trim();
                    let cellText = mainContent;

            if (cell.querySelector(".ontime-indicator")) cellText += " (On Time)";
            if (cell.querySelector(".telat-indicator")) cellText += " (Telat)";
                    if (cell.dataset.note) cellText += ` [${cell.dataset.note}]`;
                    
                    rowData.push(cellText);
                });
                data.push(rowData);
            });

            const worksheet = XLSX.utils.aoa_to_sheet(data);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Jobsheet");

    const fileName = `Jobsheet_${periodTitle.textContent.replace(/\s/g, "_")}.xlsx`;
            XLSX.writeFile(workbook, fileName);
        };

generateBtn.addEventListener("click", generateTable);
picSearch.addEventListener("input", filterPic);
exportBtn.addEventListener("click", exportToExcel);
document.addEventListener("DOMContentLoaded", () => {
            populateSelectors();
            generateTable();
        });

        let isSelecting = false, isDragging = false, startCell = null, selectedCells = [];
        
        const clearSelection = () => {
    tableBody.querySelectorAll("td.selected").forEach(c => c.classList.remove("selected"));
            selectedCells = [];
        };
        
        const highlightCells = (start, end) => {
            clearSelection();
            if (!start || !end) return;
            const r1 = Math.min(parseInt(start.dataset.row), parseInt(end.dataset.row)), r2 = Math.max(parseInt(start.dataset.row), parseInt(end.dataset.row));
            const c1 = Math.min(parseInt(start.dataset.col), parseInt(end.dataset.col)), c2 = Math.max(parseInt(start.dataset.col), parseInt(end.dataset.col));
    for (let r = r1; r <= r2; r++) for (let c = c1; c <= c2; c++) tableBody.querySelector(`td[data-row="${r}"][data-col="${c}"]`)?.classList.add("selected");
        };

        const openNoteModal = () => {
    noteInput.value = selectedCells.length > 0 && selectedCells[0].dataset.note ? selectedCells[0].dataset.note : "";
    noteModal.classList.remove("hidden");
            noteInput.focus();
        };
const closeNoteModal = () => noteModal.classList.add("hidden");

saveNoteBtn.addEventListener("click", () => {
            const noteText = noteInput.value.trim();
            selectedCells.forEach(cell => {
        cell.querySelector(".note-text")?.remove();
                if (noteText) {
                    cell.dataset.note = noteText;
            const noteEl = document.createElement("div");
            noteEl.className = "note-text";
                    noteEl.textContent = noteText;
                    noteEl.title = noteText;
                    cell.appendChild(noteEl);
                } else delete cell.dataset.note;
            });
            closeNoteModal();
            clearSelection();
        });
cancelNoteBtn.addEventListener("click", closeNoteModal);

tableBody.addEventListener("mousedown", (e) => {
    const targetCell = e.target.closest("td");
            if (!targetCell || e.button !== 0) return;
            isSelecting = true;
            isDragging = false;
            startCell = targetCell;
    contextMenu.style.display = "none";
        });

tableBody.addEventListener("mouseover", (e) => {
            if (isSelecting) {
                isDragging = true;
        const targetCell = e.target.closest("td");
                if (targetCell) highlightCells(startCell, targetCell);
            }
        });

document.addEventListener("mouseup", (e) => {
            if (isSelecting) {
        if (!isDragging) {
                    const targetCell = startCell;
            if (!targetCell.classList.contains("selected")) {
                        clearSelection();
                targetCell.classList.add("selected");
                    }
                }
        selectedCells = Array.from(tableBody.querySelectorAll("td.selected"));
                isSelecting = false;
            }
        });

tableBody.addEventListener("contextmenu", (e) => {
            e.preventDefault();
    const targetCell = e.target.closest("td");
            if (!targetCell) return;

    if (!targetCell.classList.contains("selected")) {
                clearSelection();
        targetCell.classList.add("selected");
                selectedCells = [targetCell];
            }
            
            if (selectedCells.length > 0) {
        const hasApproved = selectedCells.some(c => c.dataset.approved === "true");
        const hasUnapproved = selectedCells.some(c => c.dataset.approved !== "true");
                const mainContentNode = Array.from(targetCell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
        const mainText = mainContentNode ? mainContentNode.nodeValue.trim() : "";
                const hasContent = mainText.length > 0 && !hasApproved;
                
        document.getElementById("fill-options").style.display = hasUnapproved ? "block" : "none";
        document.getElementById("menu-approve").style.display = hasUnapproved ? "block" : "none";
        document.getElementById("menu-unlock").style.display = hasApproved ? "block" : "none";
        document.getElementById("menu-clear").style.display = hasUnapproved ? "block" : "none";
        document.getElementById("menu-ontime").style.display = hasContent ? "block" : "none";
        document.getElementById("menu-telat").style.display = hasContent ? "block" : "none";
        document.getElementById("menu-add-note").style.display = hasContent ? "block" : "none";
        document.getElementById("marker-separator").style.display = hasContent ? "block" : "none";

                contextMenu.style.top = `${e.pageY}px`;
                contextMenu.style.left = `${e.pageX}px`;
        contextMenu.style.display = "block";
            }
        });

document.addEventListener("click", (e) => {
            if (!contextMenu.contains(e.target) && !noteModal.contains(e.target)) {
        contextMenu.style.display = "none";
        if (!e.target.closest("#table-container") && !e.target.closest(".bg-white.dark\\:bg-gray-800")) {
                    clearSelection();
                }
            }
        });

contextMenu.addEventListener("click", (e) => {
            e.preventDefault();
            const action = e.target.dataset.action;
            if (action === undefined || selectedCells.length === 0) return;
    if (action === "add_note") {
                openNoteModal();
        contextMenu.style.display = "none";
                return;
            }
            selectedCells.forEach(cell => {
        if (action === "approve") {
            if (cell.dataset.approved !== "true") {
                cell.dataset.approved = "true";
                cell.classList.add("approved");
            }
        } else if (action === "unlock") {
            if (cell.dataset.approved === "true") {
                        delete cell.dataset.approved;
                cell.classList.remove("approved");
                    }
        } else if (action === "ontime" || action === "telat") {
                    const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
            const mainText = mainContentNode ? mainContentNode.nodeValue.trim() : "";
            if (mainText.length > 0 && cell.dataset.approved !== "true") {
                cell.querySelector(".indicator")?.remove();
                const indicator = document.createElement("span");
                indicator.className = `indicator ${action === "ontime" ? "ontime-indicator" : "telat-indicator"}`;
                        cell.appendChild(indicator);
                    }
        } else {
            if (cell.dataset.approved !== "true") {
                        const mainContentNode = Array.from(cell.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                if (action === "") {
                    if (mainContentNode) mainContentNode.nodeValue = "";
                    cell.querySelector(".indicator")?.remove();
                    cell.querySelector(".note-text")?.remove();
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
    contextMenu.style.display = "none";
            clearSelection();
        });
    </script>
</body>
</html>
