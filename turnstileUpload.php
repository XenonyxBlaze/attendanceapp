<?php

require_once 'vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();

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

function sqlTurnstile($reader, $tFile, $conn) {
    $sheet = $reader->load($tFile)->getActiveSheet();

    $records = array_slice($sheet->toArray(),7);

    $insertions = 0;
    $notStudent = 0;

    $timestamp = date('dmY',time());

    foreach($records as $row) {

        $sqlQuerry = null;

        // $name = $row[0];
        $id = strtoupper($row[1]);

        
        if (!preg_match("/^\d{2}[a-zA-Z]{3}\d{5}$/",$id)) {
            $notStudent++;
            continue;
        }

        $date = $row[4];
        $time = $row[5];
        $grp = substr($row[7],11);

        if(preg_match("/[a-zA-Z]{5}\s\d$/",$grp)) {
            $block = 'b'.substr($grp,-1);
        } else {
            $block = 'gh';
        }

        $checkpoint = $row[8];

        $createQuery = "CREATE TABLE IF NOT EXISTS turnstile_".$block."_".$timestamp."(ID varchar(255),Time varchar(255), Date varchar(255),Attendance_Check_Point varchar(255));";

        try {
            $conn->exec($createQuery);
        } catch(PDOException $e) {
            echo $sqlQuerry."<br>";
            echo "Error : " . $e->getMessage()."<br>";
        }
    
        $sqlQuerry = "INSERT INTO turnstile_".$block."_".$timestamp."(ID, Date, Time, Person_Group, Attendance_Check_Point) VALUES (\"$id\",\"$date\",\"$time\",\"$grp\",\"$checkpoint\");";

        try {
            $conn->exec($sqlQuerry);
            $insertions++;
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
            echo $sqlQuerry."<br>";
        }
    }

    if($insertions+$notStudent == count($records)) {
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

$conn = null;

$_POST['submit']=true;
$_POST['date']=$timestamp;
$_POST['block']='b1';
$_POST['status']='all';

// header('Location: generateReport.php');
