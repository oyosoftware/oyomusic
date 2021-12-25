<?php

error_reporting(22519);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");

$sql = "select released from albums group by released order by released desc";
$result = mysqli_query($link, $sql);

$data = 'getYears([';

while ($row = mysqli_fetch_assoc($result)) {
  $data .= '{year: ' . $row["released"] . '}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>
