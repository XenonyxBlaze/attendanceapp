<?php

session_start();

if(isset($_SESSION['report'])) {
  $report = $_SESSION['report'];
} else {
  $report = array();
}

// foreach($report as $row){
//   echo $row[0]."<br>";
// }

// if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER["REQUEST_METHOD"] == 'GET') {
//   header('Location: genView.php');
// }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/root.css" />
    <link rel="stylesheet" href="css/report.css" />
    <link rel="stylesheet" href="css/navbar.css" />
    <link rel="stylesheet" href="css/header.css" />

    <title>Attendance Report</title>
  </head>
  <body>
    <!-- NAVBAR HTML: (Expandable by adding more anchor tags) -->
    <label>
      <input type="checkbox" />
      <span class="menu"> <span class="hamburger"></span> </span>
      <ul>
        <li id="h1"><a href="index.html">Home</a></li>
        <li><a href="report.php">View Attendance Report</a></li>
        <li><a href="updateHostelers.html">Update hostel masterdata</a></li>
        <li><a href="uploadLeave.html">Update hosteler leave data</a></li>
      </ul>
    </label>
    <div class="overlay"></div>
    <!-- NAVBAR HAMBURGER ENDS HERE. -->
    <header>
      <div>
        <img src="./img/logoblack.png" alt="VITLogo" />
      </div>
      <div id="center">
        <p font="akz">Hostel attendance management system</p>
        <p>[HAMS v0.3]</p>
      </div>
      <div></div>
    </header>

    <h2 id="title">View attendance report:</h2>
    <div id="form-data">
      <form action="genView.php" method="post" enctype="multipart/form-data" id="viewform">
        <input type="text" name="name" id="filter-name" placeholder="Name" />
        <input
          type="text"
          name="regno"
          id="filter-regno"
          placeholder="Registration Number"
        />
        <input type="date" name="date" id="filter-date" />
        <select name="status" id="filter-status">
          <option value="All">All</option>
          <option value="present">Present</option>
          <option value="absent">Absent</option>
          <option value="onleave">On Leave</option>
          <option value="leavebutreported">Leave But Reported</option>
        </select>
        <input type="submit" value="Go" />
      <!-- </form> -->
    </div>
    <div id="main-data">
      <div id="blockbtns">
        <!-- <form action="genView.php" method="POST" enctype="multipart/form-data"> -->
          <input type="radio" name="block" id="BHB1-radio" value="b1" checked />
          <label for="BHB1-radio">Boys Block-1</label>
          <input type="radio" name="block" id="BHB2-radio" value="b2" />
          <label for="BHB2-radio">Boys Block-2</label>
          <input type="radio" name="block" id="BHB3-radio" value="b3" />
          <label for="BHB3-radio">Boys Block-3</label>
          <input type="radio" name="block" id="GHB1-radio" value="gh" />
          <label for="GHB1-radio">Girls Block-1</label>
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
    <script src="https://code.jquery.com/jquery-3.7.0.slim.min.js" integrity="sha256-tG5mcZUtJsZvyKAxYLVXrmjKBVLd6VpVccqz/r4ypFE=" crossorigin="anonymous"></script>
    <script>
      $('input[type="radio"]').click(function() {
        $("#viewform").submit();
      });
      $('#filter-date').val(new Date().toISOString().slice(0,10));
    </script>
  </body>
</html>
