<?php

$sqlServer = "localhost:3306";
$sqlUser = "root";
$sqlPass = "toor";

try {
    $conn = new PDO("mysql:host=$sqlServer;dbname=attendence_system_test", $sqlUser, $sqlPass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("TRUNCATE TABLE turnstile;");

} catch(PDOException $e) {
    echo "Connection to SQL database failed: " . $e->getMessage();
    die;
}


if(!isset($_POST['submit']) && !isset($_POST['date']) && !isset($_POST['block']) && !isset($_POST['status'])) {
    header('Location: report.html');
} else {
    $date = $_POST['date'];
    $block = $_POST['block'];
    $status = $_POST['status'];
}

$reportFilePath = "uploads/reports/".$block.'/';
$reportFile = 'AttendanceReport_'.$date.'_'.$status.'.xlsx';

if(file_exists($reportFilePath.$reportFile)) {

}
