<?php

    require_once 'vendor\autoload.php';

    use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;

    $reader = new Xlsx();
    $attendance_file = "uploads/turnstileData.xlsx";

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
    // Process attendance file

    $sheet = $reader->load($attendance_file)->getActiveSheet();

    $records = array_slice($sheet->toArray(),1);

    $insertions = 0;

    foreach($records as $row) {

        $sqlQuerry = null;

        $name = $row[0];
        $id = strtoupper($row[1]);

        if (!preg_match("/^\d{2}[A-Z]{3}\d{5}$/",$id)) {
            continue;
        }

        $date = $row[4];
        $time = $row[5];

        $sqlQuerry = "INSERT INTO turnstile(StuName, ID, _Date, _Time) VALUES (\"$name\",\"$id\",\"$date\",\"$time\")";

        try {
            $conn->exec($sqlQuerry);
            $insertions++;
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }
    }


    $count = $conn->query("SELECT count(*) FROM turnstile")->fetchColumn();

    if ($count == $insertions) {
        header('Location: generateReport.php');
    }
