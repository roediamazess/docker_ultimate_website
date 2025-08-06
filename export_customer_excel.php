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
header('Content-Disposition: attachment;filename="customer_list.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'ID', 'Customer ID', 'Name', 'Star', 'Room', 'Outlet', 'Type', 'Group', 'Zone', 'Address', 'Billing'
], NULL, 'A1');

// Data
$sql = "SELECT id, customer_id, name, star, room, outlet, type, \"group\", zone, address, billing FROM customers ORDER BY id DESC";
$stmt = $pdo->query($sql);
$row = 2;
while ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray([
        $c['id'],
        $c['customer_id'],
        $c['name'],
        $c['star'],
        $c['room'],
        $c['outlet'],
        $c['type'],
        $c['group'],
        $c['zone'],
        $c['address'],
        $c['billing'],
    ], NULL, 'A'.$row);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
