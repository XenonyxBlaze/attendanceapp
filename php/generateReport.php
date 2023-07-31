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

    $blockTable = substr($table,9,2)."master";

    if(tableExists($conn, $reportTable)){
        $conn->exec("DROP TABLE $reportTable");
    }

    $createReportQuery = "CREATE TABLE IF NOT EXISTS ".$reportTable."(ID VARCHAR(255) NOT NULL PRIMARY KEY, NAME VARCHAR(255), STATUS VARCHAR(255));";

    $conn->exec($createReportQuery);

    $neTable = "newEntry".substr($table,9);

    if(tableExists($conn, $neTable)){
        $conn->exec("DROP TABLE $neTable");
    }

    $crNeTable = "CREATE TABLE IF NOT EXISTS ".$neTable."(
        ID varchar(255),
        NAME varchar(255),
        Time varchar(255),
        Date varchar(255),
        Attendance_Check_Point varchar(255)
    );";

    $conn->exec($crNeTable);

    $neQuery = "INSERT INTO $neTable(ID, NAME, Time, Date, Attendance_Check_Point)
    SELECT t.ID, t.NAME, t.Time, t.Date, t.Attendance_Check_Point
    FROM $table t
    LEFT JOIN $blockTable b ON t.ID = b.ID
    WHERE b.ID IS NULL;";

    $delNeQuery = "DELETE FROM $table WHERE ID NOT IN (SELECT ID FROM $blockTable);";

    $nneReportQuery = "INSERT INTO $reportTable (
        Name,
        ID,
        STATUS
    )
    SELECT
        m.Name,
        m.ID,
        COALESCE(l.STATUS, CASE WHEN latest_turnstile.STATUS IS NOT NULL THEN latest_turnstile.STATUS ELSE NULL END, 'PRESENT')
    FROM $blockTable AS m
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

    $finalReportQuery = "INSERT INTO $reportTable (
        ID,
        STATUS
    )
    SELECT t1.ID,
           CASE
               WHEN t2.ID IS NOT NULL THEN
                   CASE
                       WHEN t2.STATUS = 'LEAVE' THEN 'NE-LEAVE'
                       WHEN t2.STATUS = 'REPORTED' THEN 'NE-REPORTED'
                   END
               WHEN t1.Attendance_Check_Point LIKE '%ENTRY%' THEN 'NE-PRESENT'
               ELSE 'NE-ABSENT'
           END AS STATUS
    FROM $neTable t1
    LEFT JOIN hostel_attendance.onleave t2 ON t1.ID = t2.ID AND t2.STATUS IN ('LEAVE', 'REPORTED')
    INNER JOIN (
        SELECT ID, MAX(Time) AS MaxTime
        FROM $neTable
        GROUP BY ID
    ) t3 ON t1.ID = t3.ID AND t1.Time = t3.MaxTime;";

    try {
        $conn->exec($neQuery);
        $conn->exec($delNeQuery);
        $conn->exec($nneReportQuery);
        $conn->exec($finalReportQuery);
        $conn->exec("INSERT INTO $table (SELECT * FROM $neTable);");
        $conn->exec("DROP TABLE $neTable");
    } catch(PDOException $e) {
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

