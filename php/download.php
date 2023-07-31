<?php

session_start();

if(!isset($_GET['filetype']) && !isset($_SESSION['reportTable'])){
    header('Location: ../public_pages/report.php');
}

$filetype=$_GET['filetype'];

$file = '../uploads/reports/'.$_SESSION['reportTable'].'.'.$filetype;
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Content-Length: ' . filesize($file));
readfile($file);
// header('Location: ../public_pages/report.php');



