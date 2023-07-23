
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
    // Process Main data

    $sheet = $reader->load($data_file)->getActiveSheet();

    $records = array_slice($sheet->toArray(),1);

    $insertions=0;

    foreach($records as $row) {
        $sqlQuerry = null;

        $id = strtoupper($row[0]);

        if (!preg_match("/^\d{2}[A-Z]{3}\d{5}$/",$id)) {
            continue;
        }

        // $block = $row[1];

        $sqlQuerry = "INSERT INTO masterdata(ID) VALUES (\"$id\")";

        try {
            $conn->exec($sqlQuerry);
            $insertions++;
        } catch(PDOException $e) {
            echo "Error : " . $e->getMessage()."<br>";
        }

    }
    
    $count = $conn->query("SELECT count(*) FROM masterdata")->fetchColumn();
    
    if ($insertions == (int) $count) {
        header('Location: uploadLeave.php');
    }
