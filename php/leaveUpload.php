<?php

require_once '../vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();

// SQL config

$sqlServer = "localhost:3306";
$sqlUser = "root";
$sqlPass = "";

try {
    $conn = new PDO("mysql:host=$sqlServer;dbname=hostel_attendance", $sqlUser, $sqlPass);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    echo "Connection to SQL database failed: " . $e->getMessage();
    die;
}


function isExcelFile($file) {
    $allowedExtensions = array('xls', 'xlsx');
    $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['roomnum']) && !empty($_POST['roomnum'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];

        // SQL query

        $sqlQuerry = "INSERT INTO onleave(ID, Status) VALUES (\"$id\",\"$status\")";

        try {
            $conn->exec($sqlQuerry);
            echo "Hosteler information uploaded successfully<br>";
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }
    }

    // Handle batch user IDs from an uploaded Excel file
    if (isset($_FILES['excelFile']) && isExcelFile($_FILES['excelFile']['name'])) {
        $excelFileTmpName = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = $reader->load($excelFileTmpName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        foreach ($sheetData as $row) {
            $id = $row['A'];
            $status = $row['B'];

            // SQL query
            $sqlQuerry = "INSERT INTO onleave(ID, Status) VALUES (\"$id\",\"$status                                                             \")";

            try {
                $conn->exec($sqlQuerry);
                echo "Hosteler information uploaded successfully<br>";
            } catch(PDOException $e) {
                echo "Error : " . $e->getMessage()."<br>";
            }

        }
    }

    session_start();
    $_SESSION['redir']='leave';

    header("Location: generateReport.php");

}

