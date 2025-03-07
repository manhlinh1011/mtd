<?php

require_once 'app/Libraries/PhpSpreadsheet/src/PhpSpreadsheet/Spreadsheet.php';
require_once 'app/Libraries/PhpSpreadsheet/src/PhpSpreadsheet/Writer/Xlsx.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Hello World!');
    $writer = new Xlsx($spreadsheet);

    $writer->save('test.xlsx');
    echo "File 'test.xlsx' đã được tạo thành công!";
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
