<?php
include 'header.php';

$clockIn  = $_POST['clockIn'];
$clockOut = $_POST['clockOut'];
$break    = $_POST['break'] ?? '00:00';
$work     = $_POST['work'];
$date     = $_POST['date'] . ' 00:00:00';
$day      = $_POST['day'];

$query  = "SELECT clockid FROM clock WHERE clockdate = $1";
$params = [$date];
$result = pg_query_params($db, $query, $params);
$count  = pg_num_rows($result);

if ($count > 0) {
  exit;
}
else {
  $query = "
    INSERT INTO clock 
    (clockin, clockout, break, work, clockdate, day) 
    VALUES ($1, $2, $3, $4, $5, $6)
  ";
  $params = [$clockIn, $clockOut, $break, $work, $date, $day];
  $result = pg_query_params($db, $query, $params);
}

header('Location: ../index.php');