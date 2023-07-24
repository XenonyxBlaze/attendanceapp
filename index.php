<!DOCTYPE html>
<?php 

    require_once 'vendor\autoload.php';

    use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

    $reader = new Xlsx();

    if(isset($_POST['submit'])) {

        // Disable form input fields

        $hostelData = $_FILES['hostelData']['name'];
        $leaveData = $_FILES['leaveData']['name'];
        $turnstileData = $_FILES['turnstileData']['name'];

        // Check if the files are uploaded
        if($hostelData && $leaveData && $turnstileData) {
            // Check if the files are xlsx files
            foreach($_FILES as $file) {
                if(pathinfo($file['name'], PATHINFO_EXTENSION) != 'xlsx') {
                    echo 'Please upload excel files only';
                    exit();
                }
            }
            // Move the files to the uploads folder

            foreach($_FILES as $fileName=>$file) {
                move_uploaded_file($file['tmp_name'], 'uploads/'.$fileName.'.xlsx');
            }

            // Redirect to the attendance page
            // header('Location: uploadMain.php');
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

            function uploadMain($reader, $data_file, $conn) {

                $records = $reader->load($data_file)->getActiveSheet();

                $records = array_slice($records->toArray(),1);

                $insertions=0;

                foreach($records as $row) {
                    $sqlQuerry = null;

                    $id = strtoupper($row[0]);

                    if (!preg_match("/^\d{2}[A-Z]{3}\d{5}$/",$id)) {
                        continue;
                    }

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
                    return true;
                }
            }

            function uploadLeave($reader, $exceptions_file, $conn) {

                $records = $reader->load($exceptions_file)->getActiveSheet();

                $records = array_slice($records->toArray(),1);

                $insertions=0;

                foreach($records as $row) {
                    $sqlQuerry = null;

                    $id = strtoupper($row[0]);

                    if (!preg_match("/^\d{2}[A-Z]{3}\d{5}$/",$id)) {
                        continue;
                    }

                    $sqlQuerry = "INSERT INTO onleave(ID) VALUES (\"$id\")";

                    try {
                        $conn->exec($sqlQuerry);
                        $insertions++;
                    } catch(PDOException $e) {
                        echo "Error : " . $e->getMessage()."<br>";
                    }
                }
            
            
                $count = $conn->query("SELECT count(*) FROM onleave")->fetchColumn();
                
                if ($insertions == (int) $count) {
                    return true;
                }
            }

            function uploadAttendance($reader,$attendance_file,$conn) {
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
                    return true;
                }
            }

            if (uploadMain($reader, $data_file, $conn)) {
                echo "Master data uploaded successfully<br>";
                if (uploadLeave($reader, $exceptions_file, $conn)) {
                    echo "Leave data uploaded successfully<br>";
                    if (uploadAttendance($reader, $attendance_file, $conn)) {
                        echo "Attendance data uploaded successfully<br>";
                        header('Location: generateReport.php');
                    } else {
                        echo "Attendance data upload failed<br>";
                        die();
                    }
                } else {
                    echo "Leave data upload failed<br>";
                    die();
                }
            } else {
                echo "Master data upload failed<br>";
                die();
            }


            
        } else {
            echo 'Please upload all the files';
        }
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Attendance App</title>
</head>
<body>
    <style>

        body {
            font-family: sans-serif;
        }

        div {
            margin: 0 auto;
            width: 50%;
            text-align: center;
        }

        input[type="file"] {
            margin: 0 auto;
            width: 50%;
            text-align: center;
        }

        input[type="submit"] {
            margin: 0 auto;
            width: 50%;
            text-align: center;
        }

        h3 {
            margin: 0 auto;
            width: 50%;
            text-align: center;
        }

        h2 {
            margin: 0 auto;
            width: 50%;
            text-align: center;
        }

    </style>
    <div>
        <form action="" method="post" enctype="multipart/form-data">
            <h3>
                Upload hostel data:
            </h3>
            <input type="file" name="hostelData" id="hostelData" >
            <h3>Upload leave data: </h3>
            <input type="file" name="leaveData" id="leaveData" >
            <h3>Upload turnstile data: </h3>
            <input type="file" name="turnstileData" id="turnstileData" >
            <br>
            <h2>Get Attendance:</h2>
            <input type="submit" value="Fetch" name="submit" id="submit" >
        </form>
    </div>
</body>
</html>
