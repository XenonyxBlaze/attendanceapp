<?php

include 'dbConn.php';

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

session_start();

if(isset($_SESSION['redir'])){
    $redir = $_SESSION['redir'];
    unset($_SESSION['redir']);
} else {
    $redir = "../php/genView.php";
}

$dates = array();

if(isset($_SESSION['ts'])){
    $ts = $_SESSION['ts'];
    array_push($dates, $ts);
} else {
    $date = date("dmY",time());
    array_push($dates, $date);
}

$tables = array();

foreach($dates as $date){
    $t = $conn->query("SHOW TABLES LIKE 'turnstile__$date'")->fetchAll(PDO::FETCH_COLUMN);
    foreach($t as $table){
        array_push($tables, $table);
    }
}

if(count($tables) == 0) {
    // echo "TMOROW";
    // echo $date;
    header("Location: $redir");
    // die;
}

function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $result = $result->fetchAll(PDO::FETCH_COLUMN);
    return count($result) > 0;
}

foreach($tables as $table) {

    $reportTable = "report".substr($table,9);

    $block = substr($table,9,2);

    $reportExists = tableExists($conn, $reportTable);

    if($reportExists) {
        $conn->exec("DROP TABLE $reportTable");
    }

    $createReportQuery = "CREATE TABLE IF NOT EXISTS ".$reportTable."(ID VARCHAR(255) NOT NULL PRIMARY KEY, NAME VARCHAR(255), STATUS VARCHAR(255));";

    $conn->exec($createReportQuery);

    $reportGenQuery = "
    INSERT INTO $reportTable (
        ID,
        Name,
        Status
    )
    SELECT
        m.ID,
        m.Name,
        COALESCE(l.STATUS, CASE WHEN latest_turnstile.STATUS IS NOT NULL THEN latest_turnstile.STATUS ELSE NULL END, 'NEW ENTRY')
    FROM " . $block . "master AS m
    LEFT JOIN (
        SELECT t.ID,
               CASE
                   WHEN t.Attendance_Check_Point LIKE '%EXIT%' THEN 'ABSENT'
                   ELSE 'PRESENT'
               END AS STATUS
        FROM $table AS t
        INNER JOIN (
            SELECT ID, MAX(Time) AS LatestTime
            FROM $table
            GROUP BY ID
        ) AS latest_time
        ON t.ID = latest_time.ID AND t.Time = latest_time.LatestTime
    ) AS latest_turnstile
    ON m.ID = latest_turnstile.ID
    LEFT JOIN onleave AS l ON m.ID = l.ID
    ON DUPLICATE KEY UPDATE Status = VALUES(Status);
    ";

    try {
        $conn->exec($reportGenQuery);
    } catch(PDOException $e) {
        echo $reportGenQuery."<br>";
        echo "Error : " . $e->getMessage()."<br>";
    }

    $fetchReport = "SELECT * FROM $reportTable";

    $report = $conn->query($fetchReport)->fetchAll();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Status');

    for($i = 0; $i < count($report); $i++) {
        $sheet->setCellValue('A'.($i+2), $report[$i]['ID']);
        $sheet->setCellValue('B'.($i+2), $report[$i]['NAME']);
        $sheet->setCellValue('C'.($i+2), $report[$i]['STATUS']);
    }

    $filePath='../uploads/reports//'.$reportTable;

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    $writer->save($filePath.'.xlsx');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);

    $writer->save($filePath.'.csv');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Tcpdf($spreadsheet);

    $writer->save($filePath.'.pdf');

}

header("Location: $redir");

