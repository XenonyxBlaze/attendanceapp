<?php

include 'dbConn.php';

session_start();

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

$report = array();

if(reportExists($conn, $reportTable)) {
    $filterConstraints = returnFilterConstraints($filters);
    $reportQuery = "SELECT * FROM $reportTable $filterConstraints";

    try {
        $report = $conn->query($reportQuery)->fetchAll();
    } catch(PDOException $e) {
        echo "Error : " . $e->getMessage()."<br>";
    }
    
}

$_SESSION['report'] = $report;

$_SESSION['block'] = $block;

$_SESSION['date'] = DateTime::createFromFormat('dmY', $date)->format('Y-m-d');

if(isset($_SESSION['redir'])) {
    header('Location: ../public_pages/'.$_SESSION['redir']);
}

