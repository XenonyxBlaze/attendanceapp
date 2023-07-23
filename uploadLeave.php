<?php

require_once 'vendor\autoload.php';

use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$exceptions_file = "uploads/leaveData.xlsx";

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

$sheet = $reader->load($exceptions_file)->getActiveSheet();

$records = array_slice($sheet->toArray(),1);

$insertions=0;

foreach($records as $row) {
    $sqlQuerry = null;

    $id = strtoupper($row[0]);

    $sqlQuerry = "INSERT INTO onleave(ID) VALUES (\"$id\")";

    try {
        $conn->exec($sqlQuerry);
        $insertions++;
    } catch(PDOException $e) {
        echo "Error : " . $e->getMessage()."<br>";
    }

}

$count = $conn->query("SELECT count(*) FROM onleave")->fetchColumn();
if ($insertions == (int) $count) {
    header('Location: uploadAttendance.php');
}
