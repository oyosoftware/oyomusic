<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $artistid = $_GET['artistid'];
}

error_reporting(22519);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");

$sql = "select * from artists where id=$artistid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

$data = 'getArtist({'
            . 'name: "'         . $row["Name"]      . '", '
            . 'letter: "'       . $row["Letter"]    . '", '
            . 'albumcount: '    . $row["AlbumCount"]
        . '})';

echo $data;

mysqli_close($link);
?>


