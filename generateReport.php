<?php

if(!isset($_POST['submit']) && !isset($_POST['date'])) {
    header('Location: report.html');
} else {
    $date = $_POST['date'];
}

// SQL config
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

$tables = $conn->query("SHOW TABLES LIKE 'turnstile__$date'")->fetchAll(PDO::FETCH_COLUMN);

if(count($tables) == 0) {
    echo "No records found for the given date";
    die;
}

$records = array();

foreach($tables as $table) {

    $createReportQuery = "CREATE TABLE IF NOT EXISTS report".substr($table,8)."(ID VARCHAR(255) NOT NULL PRIMARY KEY, NAME VARCHAR(255), STATUS VARCHAR(255));";


}

