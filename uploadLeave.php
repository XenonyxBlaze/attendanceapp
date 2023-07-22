<?php

if (!isset($exceptions_file)) {
    header('Location: uploadAttendance.php');
}

$sheet = $reader->load($exceptions_file)->getActiveSheet();

$records = array_slice($sheet->toArray(),1);

$insertions=0;

foreach($records as $row) {
    $sqlQuerry = null;

    $id = strtoupper($row[0]);

    if (!preg_match("/^\d{2}[A-Z]{3}\d{5}$/",$id)) {
        continue;
    }

    $block = $row[1];

    $sqlQuerry = "INSERT INTO onleave(ID) VALUES (\"$id\")";

    try {
        $conn->exec($sqlQuerry);
        $insertions++;
    } catch(PDOException $e) {
        echo "Error : " . $e->getMessage()."<br>";
    }

}

echo "Uploaded hostel data".PHP_EOL;
echo "Insertions: ".$insertions.PHP_EOL;


$count = $pdo->query("SELECT count(*) FROM onleave")->fetchColumn();
if ($insertions == (int) $count) {
    header('Location: uploadAttendance.php');
}
