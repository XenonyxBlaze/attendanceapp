<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$reader = new Xlsx();
$report = $reader->load('uploads/report.xlsx');

if(!isset($_POST['submit'])) {
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($report, 'Xlsx');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');

}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>


    <form action="" method="post">
        <button type="submit">Download</button>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Attendance Status</th>
        </tr>
        <?php foreach($report->getActiveSheet()->toArray() as $key => $value): ?>
            <?php if($key > 0): ?>
                <tr>
                    <td><?php echo $value[0]; ?></td>
                    <td><?php echo $value[1]; ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>

    </table>

</body>
</html>
