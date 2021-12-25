<?php

error_reporting(22519);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");

echo "\r\nscan of audio begins " . date('H:i:s') . "<br>\r\n<br>\r\n";
$link->query('START TRANSACTION;');

$sql = "select * from tracks";
$result = mysqli_query($link, $sql);
$counter = 0;

while ($row = mysqli_fetch_assoc($result)) {

  $counter++;
  
  if (($counter % 10000) === 0) {
    echo "counter: " . $counter . " " . date('H:i:s') . "<br>\r\n";
    $link->query('COMMIT AND CHAIN;');
  }

  $file = $audiosource . $row["Folder"] . $row["FileName"];

  if (!file_exists($file)) {
    echo $file . "<br>\r\n";
  }
}

$link->query('COMMIT;');
mysqli_close($link);
?>
