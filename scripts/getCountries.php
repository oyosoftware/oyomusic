<?php

error_reporting(22519);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");

$sql = "select * from countries order by country";
$result = mysqli_query($link, $sql);

$data = 'getCountries([';

while ($row = mysqli_fetch_assoc($result)) {
  $data .= '{id: ' . $row["Id"] . ', country: "'. $row["Country"] . '"}, '; 
}

$data .= '])';
echo $data;

mysqli_close($link);
?>
