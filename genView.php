<?php
session_start();

$sqlServer = "localhost:3306";
$sqlUser = "root";
<<<<<<< HEAD
$sqlPass = "";
=======
$sqlPass = "toor";
>>>>>>> 4bf8fc4dc5dc9dd7fa6aca50dbe0537ae657f5b0

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
        $nameFilter = "Name LIKE \"%".$_POST['name']."%\"";
        $_SESSION['name']=$_POST['name'];
        array_push($filters, $nameFilter);
    } else {
        $_SESSION['name']='';
    }
    
    if(isset($_POST['regno']) && $_POST['regno'] != "") {
        $idFilter = "ID LIKE \"".$_POST['regno']."\"";
        $_SESSION['regno']=$_POST['regno'];
        array_push($filters, $idFilter);
    } else {
        $_SESSION['regno']='';
    }
    
    if(isset($_POST['status']) && $_POST['status'] != "" && $_POST['status'] != "All") {
        $statusFilter = "Status LIKE \"".$_POST['status']."\"";
        $_SESSION['status']=$_POST['status'];
        array_push($filters, $statusFilter);
    } else {
        $_SESSION['status']='All';
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
    $_SESSION['report'] = array();
    
    $_SESSION['block'] = $block;

    $_SESSION['date'] = DateTime::createFromFormat('dmY', $date)->format('Y-m-d');
    header("Location: report.php");
    die;
}

$filterConstraints = returnFilterConstraints($filters);

$reportQuery = "SELECT * FROM $reportTable $filterConstraints";

$report = $conn->query($reportQuery)->fetchAll();


$_SESSION['report'] = $report;

$_SESSION['block'] = $block;

$_SESSION['date'] = DateTime::createFromFormat('dmY', $date)->format('Y-m-d');

$_SESSION['reportTable'] = $reportTable;


// foreach($report as $row){
//     echo $row[0]."<br>";
// }

header("Location: report.php");

