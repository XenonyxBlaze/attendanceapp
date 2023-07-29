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

        header('Location: AttendanceReport.php');

    }
