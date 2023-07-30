<?php

include 'dbConn.php';

require_once '../vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();

function sqlTurnstile($reader, $tFile, $conn) {
    $sheet = $reader->load($tFile)->getActiveSheet();

    $records = array_slice($sheet->toArray(),7);

    $insertions = 0;

    $timeStamp = $sheet->getCell('A5')->getValue();
    $timeStamp = date('dmY',strtotime(str_replace("/","-",substr($timeStamp,-10))));

    foreach($records as $row) {

        $sqlQuerry = null;

        // $name = $row[0];
        $id = strtoupper($row[1]);

        $name = $row[0];

        $date = $row[4];
        $time = $row[5];
        $grp = substr($row[7],12);

        if ($grp=="GHBLOCK1"){
            $block = "gh";
        } else {
            $block = "b".$grp[-1];
        }

        $checkpoint = $row[9];

        $createQuery = "CREATE TABLE IF NOT EXISTS turnstile".$block.$timeStamp."(ID varchar(255), Name varchar(255),Time varchar(255), Date varchar(255),Attendance_Check_Point varchar(255));";

        try {
            $conn->exec($createQuery);
        } catch(PDOException $e) {
            echo $createQuery."<br>";
            echo "Error : " . $e->getMessage()."<br>";
        }

        // Find if duplicate record exists

        // $sqlQuerry = "SELECT * FROM turnstile".$block.$timestamp." WHERE ID = \"$id\" AND Date = \"$date\" AND Time = \"$time\" AND Attendance_Check_Point = \"$checkpoint\";";

        // try {
        //     $result = $conn->query($sqlQuerry)->fetchAll();
        // } catch(PDOException $e) {
        //     echo "Error : " . $e->getMessage()."<br>";
        // }

        // if(count($result) > 0) {
        //     continue;
        // }

        $sqlQuerry = "INSERT INTO turnstile".$block.$timeStamp."(ID, Name, Date, Time, Attendance_Check_Point) VALUES (\"$id\",\"$name\",\"$date\",\"$time\",\"$checkpoint\");";

        try {
            $conn->exec($sqlQuerry);
            $insertions++;
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
            echo $sqlQuerry."<br>";
        }
    }

    if($insertions == count($records)) {
        echo $tFile." uploaded successfully<br>";
    } else {
        echo "Possible faults: $insertions out of ".count($records)." records uploaded<br>";
    }

    return $timeStamp;
}

function isExcelFile($file) {
    $allowedExtensions = array('xls', 'xlsx');
    $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../index.html');
}

// Define the target folder to store the Excel files
$targetFolder = '../uploads/turnstile/';
$uploadedFiles = $_FILES['tFiles'];
$countFiles = count($uploadedFiles['name']);

$timestamp = date("dmY",time());
// Loop through all the uploaded files

$reportsGen = array();

foreach($uploadedFiles['name'] as $key => $fileName) {
    $file = $uploadedFiles['tmp_name'][$key];

    $targetFile = 'TurnstileData_' . $timestamp . '_'. uniqid() . '.' . strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if(isExcelFile($fileName)) {
        if(move_uploaded_file($file, $targetFolder.$targetFile)) {
            echo $fileName." uploaded to server successfully<br>";

            $ts = sqlTurnstile($reader, $targetFolder.$targetFile, $conn);
            unlink($targetFolder.$targetFile);

            if (!in_array($ts, $reportsGen)){
                array_push($reportsGen, $ts);
            }

        } else {
            echo $fileName." upload failed<br>";
        }
    } else {
        echo $fileName." is not an Excel file. Skipping<br>";
    }
}

session_start();
$_SESSION['redir']='../public_pages/uploadTurnstile.html';
$_SESSION['ts']=$ts;

header('Location: ../php/generateReport.php');
