<?php
require_once 'db.php';

define('VENDOR_AUTOLOAD', __DIR__ . '/vendor/autoload.php');
if (file_exists(VENDOR_AUTOLOAD)) {
    require VENDOR_AUTOLOAD;
} else {
    die('PhpSpreadsheet belum terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="project_list.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'ID', 'Project ID', 'PIC', 'Assignment', 'Project Info', 'Req PIC', 'Hotel Name', 'Project Name', 'Start', 'End', 'Total Day(s)', 'Type', 'Status', 'Handover Report', 'Handover Day(s)', 'Ketertiban Admin', 'Point Ach', 'Point Req', '% Point', 'Month', 'Quarter', 'Week #'
], NULL, 'A1');

// Data
$sql = "SELECT p.*, u.display_name as pic_name, c.name as hotel_name_disp FROM projects p LEFT JOIN users u ON p.pic=u.id LEFT JOIN customers c ON p.hotel_name=c.id ORDER BY p.id DESC";
$stmt = $pdo->query($sql);
$row = 2;
while ($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray([
        $p['id'],
        $p['project_id'],
        $p['pic_name'],
        $p['assignment'],
        $p['project_information'],
        $p['req_pic'],
        $p['hotel_name_disp'],
        $p['project_name'],
        $p['start_date'],
        $p['end_date'],
        $p['total_days'],
        $p['type'],
        $p['status'],
        $p['handover_official_report'],
        $p['handover_days'],
        $p['ketertiban_admin'],
        $p['point_ach'],
        $p['point_req'],
        $p['percent_point'],
        $p['month'],
        $p['quarter'],
        $p['week_no'],
    ], NULL, 'A'.$row);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
