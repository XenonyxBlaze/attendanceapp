<?php

include 'dbConn.php';

require_once '../vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();

function sqlTurnstile($reader, $tFile, $conn) {
    $sheet = $reader->load($tFile)->getActiveSheet();

    $records = array_slice($sheet->toArray(),7);

    $insertions = 0;

    $timestamp = $sheet->getCell('A5')->getValue();
    $timestamp = date('dmY',strtotime(substr($timestamp,-10)));

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

        $createQuery = "CREATE TABLE IF NOT EXISTS turnstile".$block.$timestamp."(ID varchar(255), Name varchar(255),Time varchar(255), Date varchar(255),Attendance_Check_Point varchar(255));";

        try {
            echo $createQuery."<br>";
            $conn->exec($createQuery);
        } catch(PDOException $e) {
            echo $createQuery."<br>";
            echo "Error : " . $e->getMessage()."<br>";
        }

        // Find if duplicate record exists

        $sqlQuerry = "SELECT * FROM turnstile".$block.$timestamp." WHERE ID = \"$id\" AND Date = \"$date\" AND Time = \"$time\" AND Attendance_Check_Point = \"$checkpoint\";";

        try {
            $result = $conn->query($sqlQuerry)->fetchAll();
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }

        if(count($result) > 0) {
            continue;
        }

        $sqlQuerry = "INSERT INTO turnstile".$block.$timestamp."(ID, Name, Date, Time, Attendance_Check_Point) VALUES (\"$id\",\"$name\",\"$date\",\"$time\",\"$checkpoint\");";

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
        return true;
    } else {
        echo "Error uploading ".$tFile."<br>";
        return false;
    }
}

function isExcelFile($file) {
    $allowedExtensions = array('xls', 'xlsx');
    $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

if(!isset($_POST['submit'])) {
    header('Location: index.html');
}

// Define the target folder to store the Excel files
$targetFolder = 'uploads/turnstile/';
$uploadedFiles = $_FILES['tFiles'];
$countFiles = count($uploadedFiles['name']);

$timestamp = date("dmY",time());
// Loop through all the uploaded files

foreach($uploadedFiles['name'] as $key => $fileName) {
    $file = $uploadedFiles['tmp_name'][$key];

    $targetFile = 'TurnstileData_' . $timestamp . '_'. uniqid() . '.' . strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if(isExcelFile($fileName)) {
        if(move_uploaded_file($file, $targetFolder.$targetFile)) {
            echo $fileName." uploaded to server successfully<br>";

            if(sqlTurnstile($reader, $targetFolder.$targetFile, $conn)) {
                echo $fileName." uploaded to database successfully<br>";
                // delete stored file

                unlink($targetFolder.$targetFile);

            } else {
                echo $fileName." upload to database failed<br>";
            }

        } else {
            echo $fileName." upload failed<br>";
        }
    } else {
        echo $fileName." is not an Excel file. Skipping<br>";
    }
}

header('Location: ../public_pages/uploadTurnstile.html');

