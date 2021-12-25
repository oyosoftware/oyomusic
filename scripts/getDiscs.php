<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $albumid = $_GET['albumid'];
}

error_reporting(22519);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql)) die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");
 
$sql = "select * from discs where albumid=$albumid order by albumid, discno";
$result = mysqli_query($link, $sql);

$data = 'getDiscs([';

while ($row = mysqli_fetch_assoc($result)) {
    $time = formattime($row["PlayingTime"]);
    $title = $row["Title"];
    $title = str_replace('\\', '\\\\', $title);
    $title = str_replace('"', '\"', $title); 
    $data .=    '{'
                    . 'discno: '        . $row["DiscNo"]    . ', '
                    . 'title: "'        . $title            . '", '
                    . 'playingtime: "'  . $time             . '"'
                . '}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>
