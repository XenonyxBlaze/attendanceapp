<?php

$date = date("dmY",time());

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

$tables = $conn->query("SHOW TABLES LIKE 'turnstile__$date'")->fetchAll(PDO::FETCH_COLUMN);

if(count($tables) == 0) {
    echo "No records found for the given date";
    die;
}

foreach($tables as $table) {

    $reportTable = "report".substr($table,9);

    $block = substr($table,9,2);

    $createReportQuery = "CREATE TABLE IF NOT EXISTS ".$reportTable."(ID VARCHAR(255) NOT NULL PRIMARY KEY, NAME VARCHAR(255), STATUS VARCHAR(255));";

    $conn->exec($createReportQuery);

    $reportGenQuery = "
    INSERT INTO $reportTable (
        ID,
        Name,
        Status
    )
    SELECT
        m.ID,
        m.Name,
        COALESCE(l.STATUS, CASE WHEN latest_turnstile.STATUS IS NOT NULL THEN latest_turnstile.STATUS ELSE NULL END, 'NEW ENTRY')
    FROM " . $block . "master AS m
    LEFT JOIN (
        SELECT t.ID,
               CASE
                   WHEN t.Attendance_Check_Point LIKE '%EXIT%' THEN 'ABSENT'
                   ELSE 'PRESENT'
               END AS STATUS
        FROM $table AS t
        INNER JOIN (
            SELECT ID, MAX(Time) AS LatestTime
            FROM $table
            GROUP BY ID
        ) AS latest_time
        ON t.ID = latest_time.ID AND t.Time = latest_time.LatestTime
    ) AS latest_turnstile
    ON m.ID = latest_turnstile.ID
    LEFT JOIN onleave AS l ON m.ID = l.ID
    ON DUPLICATE KEY UPDATE Status = VALUES(Status);
    ";

    echo $reportGenQuery."<br>";
}

