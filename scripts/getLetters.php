<?php

header("Access-Control-Allow-Origin: *");
error_reporting(22519);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

$sql = "select letter from artists where albumcount > 0 group by letter order by letter";
$result = mysqli_query($link, $sql);

mysqli_set_charset($link,"utf8");

$data = 'getLetters([';

while ($row = mysqli_fetch_assoc($result)) {
  $data .= '{letter: "' . $row["letter"] . '"}, '; 
}

$data .= '])';
echo $data;

mysqli_close($link);
?>

