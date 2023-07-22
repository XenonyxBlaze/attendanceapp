
<?php
    require_once 'vendor\autoload.php';

    use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;

    // Files
    $attendance_file = "uploads/turnstileData.xlsx";
    $data_file = "uploads/hostelData.xlsx";
    $exceptions_file = "uploads/leaveData.xlsx";

    // SQL config
    $sqlServer = "localhost:3306";
    $sqlUser = "root";
    $sqlPass = "toor";

    try {
        $conn = new PDO("mysql:host=$sqlServer;dbname=hostel_attendance", $sqlUser, $sqlPass);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->exec("TRUNCATE turnstile;");
        $conn->exec("TRUNCATE masterdata;");
        $conn->exec("TRUNCATE onleave;");
    } catch(PDOException $e) {
        echo "Connection to SQL database failed: " . $e->getMessage();
        die;
    }


    $reader = new Xlsx();

    // Process attendance file

    $sheet = $reader->load($attendance_file)->getActiveSheet();

    $records = array_slice($sheet->toArray(),7);
    foreach($records as $row) {

        $sqlQuerry = null;

        $name = $row[0];
        $id = strtoupper($row[1]);

        $date = $row[4];
        $time = $row[5];

        $sqlQuerry = "INSERT INTO turnstile(StuName, ID, _Date, _Time) VALUES (\"$name\",\"$id\",\"$date\",\"$time\")";

        try {
            $conn->exec($sqlQuerry);
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }
    }

    

    // Process Main data

