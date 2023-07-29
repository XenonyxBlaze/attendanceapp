<?php

session_start();

if(!isset($_SESSION['report'])) {
  header('Location: ../php/genView.php');
} elseif(empty($_SESSION['report'])) {
  $report = array();
} else {
  $report = $_SESSION['report'];
  session_reset();
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/root.css" />
    <link rel="stylesheet" href="../css/report.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
    <link rel="stylesheet" href="../css/header.css" />

    <title>Attendance Report</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  </head>
  <body>
    <!-- NAVBAR HTML: (Expandable by adding more anchor tags) -->
    <label>
      <input type="checkbox" />
      <span class="menu"> <span class="hamburger"></span> </span>
      <ul>
        <li id="h1"><a href="../index.html">Home</a></li>
        <li><a href="../public_pages/report.php">View Today's Attendance Report</a></li>
        <li><a href="../public_pages/uploadTurnstile.html">Upload Turnstile Data</a></li>
        <li><a href="../public_pages/previousReport.php">View Previous Reports</a></li>
        <li><a href="../public_pages/uploadHostelers.html">Upload hostel masterdata</a></li>
        <li><a href="../public_pages/uploadLeave.html">Upload hosteler leave data</a></li>
      </ul>
    </label>
    <div class="overlay"></div>
    <!-- NAVBAR HAMBURGER ENDS HERE. -->
    <header>
      <div>
        <img src="../img/logoblack.png" alt="VITLogo" />
      </div>
      <div id="center">
        <p font="akz">Hostel attendance management system</p>
        <p>[HAMS v0.3]</p>
      </div>
      <div></div>
    </header>

    <h2 id="title">View attendance report:</h2>
    <div id="form-data">
      <form action="../php/genView.php" method="post" enctype="multipart/form-data" id="viewform">
        <input type="text" name="name" id="filter-name" placeholder="Name" />
        <input
          type="text"
          name="regno"
          id="filter-regno"
          placeholder="Registration Number"
        />
        <!-- TODO: DELETE DATE BUTTON AND ADD BUTTON REFRESH / REGENERATE -->
        <input type="date" name="date" id="filter-date" />
        <select name="status" id="filter-status">
          <option value="All">All</option>
          <option value="present">Present</option>
          <option value="absent">Absent</option>
          <option value="onleave">On Leave</option>
          <option value="reported">Leave But Reported</option>
          <option value="new entry">New Entry</option>
        </select>
        <input type="submit" value="Go" />
      <!-- </form> -->
    </div>
    <div id="main-data">
      <div id="blockbtns">
        <!-- <form action="genView.php" method="POST" enctype="multipart/form-data"> -->
          <input type="radio" name="block" id="B1-radio" value="b1" checked />
          <label for="B1-radio">Boys Block-1</label>
          <input type="radio" name="block" id="B2-radio" value="b2" />
          <label for="B2-radio">Boys Block-2</label>
          <input type="radio" name="block" id="B3-radio" value="b3" />
          <label for="B3-radio">Boys Block-3</label>
          <input type="radio" name="block" id="GH-radio" value="gh" />
          <label for="GH-radio">Girls Block-1</label>
        </form>
      </div>
      <div id="downloadbtns">
        <p>Download as:</p>
        <button id="download-as-Excel">Excel</button>
        <button id="download-as-CSV">CSV</button>
        <button id="download-as-PDF">PDF</button>
      </div>
      <div id="tableview">
        <table>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
          </tr>
          <?php
          if (count($report) == 0) {
            echo "<tr><td colspan=\"3\">No data to display</td></tr>
            <tr><td colspan=\"3\">Try regenerating the report or upload relevant data.</td></tr>";
          }

          foreach($report as $row){
            echo "<tr>";
            echo "<td>".$row[0]."</td>";
            echo "<td>".$row[1]."</td>";
            echo "<td>".$row[2]."</td>";
            echo "</tr>";
          }
          ?>
        </table>
      </div>
    </div>
    <script>
      $('input[type="radio"]').click(function() {
        $("#viewform").submit();
      });

      // Download records
      $('#download-as-Excel').click(function() {
        // Send get request to download.php with filetype=excel
        window.location.href = "../php/download.php?filetype=xlsx";
      });

      $('#download-as-CSV').click(function() {
        // Send post request to download.php with filetype=csv
        window.location.href = "../php/download.php?filetype=csv";
      });

      $('#download-as-PDF').click(function() {
        // Send post request to download.php with filetype=pdf
        window.location.href = "../php/download.php?filetype=pdf";

      });

    </script>

    <?php

      if(isset($_SESSION['block'])) {
        echo "<script>$('#".strtoupper($_SESSION['block'])."-radio').prop('checked', true);</script>";
      }

      if(isset($_SESSION['status'])) {
        echo "<script>$('#filter-status').val('".$_SESSION['status']."');</script>";
      }

      if(isset($_SESSION['regno'])) {
        echo "<script>$('#filter-regno').val('".$_SESSION['regno']."');</script>";
      }

      if(isset($_SESSION['name'])) {
        echo "<script>$('#filter-name').val('".$_SESSION['name']."');</script>";
      }

    ?>
  </body>
</html>
