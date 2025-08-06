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
header('Content-Disposition: attachment;filename="activity_list.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'ID', 'Project ID', 'No', 'Information Date', 'User & Position', 'Department', 'Application', 'Type', 'Description', 'Action/Solution', 'Due Date', 'Status', 'CNC Number'
], NULL, 'A1');

// Data
$sql = "SELECT * FROM activities ORDER BY id DESC";
$stmt = $pdo->query($sql);
$row = 2;
while ($a = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray([
        $a['id'],
        $a['project_id'],
        $a['no'],
        $a['information_date'],
        $a['user_position'],
        $a['department'],
        $a['application'],
        $a['type'],
        $a['description'],
        $a['action_solution'],
        $a['due_date'],
        $a['status'],
        $a['cnc_number'],
    ], NULL, 'A'.$row);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
