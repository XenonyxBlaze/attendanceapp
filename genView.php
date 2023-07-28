<?php

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

// if get method then set vars elseif post method set vars
$filters = array();

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $date = date("dmY",time());
    $block = "b1";
} elseif($_SERVER['REQUEST_METHOD'] == 'POST') {
    $block = $_POST['block'];
    
    if(isset($_POST['date']) && $_POST['date'] != "") {
        $date = $_POST['date'];
        $date = date('dmY',strtotime($date));
    } else {
        $date = date("dmY",time());
    }

    if(isset($_POST['name']) && $_POST['name'] != "") {
        $nameFilter = "Name LIKE \"".$_POST['name']."\"";
        array_push($filters, $nameFilter);
    }
    
    if(isset($_POST['id']) && $_POST['id'] != "") {
        $idFilter = "ID LIKE \"".$_POST['id']."\"";
        array_push($filters, $idFilter);
    }
    
    if(isset($_POST['status']) && $_POST['status'] != "" && $_POST['status'] != "All") {
        $statusFilter = "Status LIKE \"".$_POST['status']."\"";
        array_push($filters, $statusFilter);
    }
}

function reportExists($conn, $reportTable) {
    $result = $conn->query("SHOW TABLES LIKE '$reportTable'");
    $result = $result->fetchAll(PDO::FETCH_COLUMN);
    return count($result) > 0;
}

function returnFilterConstraints($filters) {
    $filterConstraints = "";
    if(count($filters) > 0) {
        $filterConstraints = "WHERE ";
        foreach($filters as $filter) {
            $filterConstraints .= $filter." AND ";
        }
        $filterConstraints = substr($filterConstraints, 0, -5);
    }
    return $filterConstraints;
}

$reportTable = "report".$block.$date;

if(!reportExists($conn, $reportTable)) {
    echo "No records found for the given date";
    die;
}

$filterConstraints = returnFilterConstraints($filters);

$reportQuery = "SELECT * FROM $reportTable $filterConstraints";


$report = $conn->query($reportQuery)->fetchAll();

session_start();

$_SESSION['report'] = $report;

// foreach($report as $row){
//     echo $row[0]."<br>";
// }

header("Location: report.php");

