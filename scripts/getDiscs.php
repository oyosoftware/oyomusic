<?php

error_reporting(E_ERROR);

$albumid = filter_input(INPUT_GET, "albumid");

require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from discs where albumid=$albumid order by albumid, discno";
$result = mysqli_query($link, $sql);

$data = 'getDiscs([';

while ($row = mysqli_fetch_assoc($result)) {
    $time = formattime($row["PlayingTime"]);
    $title = $row["Title"];
    $title = str_replace('\\', '\\\\', $title);
    $title = str_replace('"', '\"', $title);
    $data .= '{'
            . 'discno: ' . $row["DiscNo"] . ', '
            . 'title: "' . $title . '", '
            . 'playingtime: "' . $time . '"'
            . '}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>