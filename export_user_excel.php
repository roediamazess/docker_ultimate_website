<?php
require_once 'db.php';

// Autoload PhpSpreadsheet jika sudah diinstall via Composer
define('VENDOR_AUTOLOAD', __DIR__ . '/vendor/autoload.php');
if (file_exists(VENDOR_AUTOLOAD)) {
    require VENDOR_AUTOLOAD;
} else {
    die('PhpSpreadsheet belum terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="user_list.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->fromArray([
    'ID', 'Display Name', 'Full Name', 'Email', 'Tier', 'Role', 'Start Work'
], NULL, 'A1');

// Data
$sql = "SELECT id, display_name, full_name, email, tier, role, start_work FROM users ORDER BY id DESC";
$stmt = $pdo->query($sql);
$row = 2;
while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray([
        $u['id'],
        $u['display_name'],
        $u['full_name'],
        $u['email'],
        $u['tier'],
        $u['role'],
        $u['start_work'],
    ], NULL, 'A'.$row);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
