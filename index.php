<?php

    // Check if the user has uploaded the files

    if(isset($_POST['submit'])) {
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
            header('Location: uploadMain.php');
            
        } else {
            echo 'Please upload all the files';
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance App</title>
</head>
<body>
    <div>
        <form action="" method="post" enctype="multipart/form-data">
            <h3>
                Upload hostel data:
            </h3>
            <input type="file" name="hostelData" id="hostelData">
            <h3>Upload leave data: </h3>
            <input type="file" name="leaveData" id="leaveData">
            <h3>Upload turnstile data: </h3>
            <input type="file" name="turnstileData" id="turnstileData">
            <br>
            <h2>Get Attendance:</h2>
            <input type="submit" value="Fetch" name="submit">
        </form>
    </div>

</body>
</html>

