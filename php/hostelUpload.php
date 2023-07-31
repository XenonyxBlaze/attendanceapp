<?php

include 'dbconn.php';

require_once '../vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();


session_start();

$_SESSION['redir']="../public_pages/uploadHostelers.html";


function isExcelFile($file) {
    $allowedExtensions = array('xls', 'xlsx');
    $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['roomnum']) && !empty($_POST['roomnum'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $block = $_POST['block'];
        // $roomnum = $_POST['roomnum'];

        // SQL query

        $sqlQuerry = "INSERT INTO ".$block."master(ID, Name) VALUES (\"$id\",\"$name\")";

        try {
            $conn->exec($sqlQuerry);
            // echo "Hosteler information uploaded successfully<br>";
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }
    }

    // Handle batch user IDs from an uploaded Excel file
    if (isset($_FILES['excelFile']) && isExcelFile($_FILES['excelFile']['name'])  ) {

        $conn->query("TRUNCATE b1master");
        $conn->query("TRUNCATE b2master");
        $conn->query("TRUNCATE b3master");
        $conn->query("TRUNCATE ghmaster");

        $excelFileTmpName = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = $reader->load($excelFileTmpName);

        // IF FILE HAS MULTIPLE SHEETS FOR INDIVIDUAL BLOCKS
        // Get all sheets
        $sheetNames = $spreadsheet->getSheetNames();

        if(count($sheetNames)>1){
            foreach ($sheetNames as $sheetName){
                switch ($sheetName){
                    case $sheetName[0] == "B":
                        $block = "b".$sheetName[-1];
                        break;
                    case $sheetName[0] == "G":
                        $block = "gh";
                        break;
                    default:
                        $block = "b1";
                        break;
                }
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $sheetData = array_slice($sheet->toArray(null, true, true, true),1);
                foreach ($sheetData as $row) {
                    $id = $row['A'];
                    $name = $row['B'];
                    // $roomnum = $row['C'];

                    if($id == "" || $name == ""){
                        continue;
                    }

                    // SQL query
                    $sqlQuerry = "INSERT INTO ".$block."master(ID, Name) VALUES (\"$id\",\"$name\")";
                    try {
                        $conn->exec($sqlQuerry);
                        // echo "Hosteler information uploaded successfully<br>";
                    } catch(PDOException $e) {
                        // echo $sqlQuerry."<br>";
                        echo "Error : " . $e->getMessage()."<br>";
                    }
                }

            }
        }
    }

}

header('Location: ../public_pages/uploadHostelers.html');