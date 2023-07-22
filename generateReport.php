<?php

require_once 'vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$sqlServer = "localhost:3306";
$sqlUser = "root";
$sqlPass = "toor";

try {
    $conn = new PDO("mysql:host=$sqlServer;dbname=hostel_attendance", $sqlUser, $sqlPass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection to SQL database failed: " . $e->getMessage();
    die;
}

$reportQuery = <<<EOT
SELECT
    m.ID,
    CASE
        WHEN l.ID IS NOT NULL THEN 'ON LEAVE'
        WHEN t.entry_count IS NULL OR t.entry_count % 2 = 0 THEN 'Present'
        ELSE 'Absent'
    END AS Attendance_Status
FROM
    hostel_attendance.masterdata m
LEFT JOIN (
    SELECT
        ID,
        COUNT(*) AS entry_count
    FROM
        hostel_attendance.turnstile
    GROUP BY
        ID
) t ON m.ID = t.ID
LEFT JOIN hostel_attendance.onleave l ON m.ID = l.ID;
EOT;

$report = $conn->query($reportQuery)->fetchAll(PDO::FETCH_ASSOC);

$reportFile = "uploads/report.xlsx";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$writer = new Xlsx($spreadsheet);

$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Attendance Status');

$row = 2;

foreach($report as $record) {
    $sheet->setCellValue('A'.$row, $record['ID']);
    $sheet->setCellValue('B'.$row, $record['Attendance_Status']);
    $row++;
}

$writer->save($reportFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="report.xlsx"');
header('Cache-Control: max-age=0');
