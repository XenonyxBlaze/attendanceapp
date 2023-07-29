<?php

session_start();

if(!isset($_GET['filetype'])){
    header('Location: report.php');
}

$filetype = $_GET['filetype'];

if(!isset($_SESSION['reportTable'])){
    header('Location: report.php');
}

$file = 'uploads/reports/'.$_SESSION['reportTable'];

if($filetype == 'excel') {
    $file .= '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
} elseif($filetype == 'csv') {
    $file .= '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
} elseif($filetype == 'pdf') {
    $file .= '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
} else {
    header('Location: report.php');
}


