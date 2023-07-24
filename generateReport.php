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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
</head>
<body>

    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
        }

        table {
            width: 100%;
        }

        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            margin: 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }


    </style>


    <form action="AttendanceReport.php" method="post">
        <button type="submit">Download</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Attendance Status</th>
        </tr>
        <?php foreach($sheet->toArray(  ) as $key => $value): ?>
            <?php if($key > 0): ?>
                <tr>
                    <td><?php echo $value[0]; ?></td>
                    <td><?php echo $value[1]; ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>

    </table>

</body>
</html>

