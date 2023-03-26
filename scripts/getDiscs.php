<?php

error_reporting(E_ERROR);

$albumid = filter_input(INPUT_GET, "albumid");

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from discs where albumid=$albumid order by albumid, discno";
$result = mysqli_query($link, $sql);

$discs = array();

while ($row = mysqli_fetch_assoc($result)) {
    $disc = (object) [];
    $disc->discno = (int) $row["DiscNo"];
    $disc->title = $row["Title"];
    $time = formattime($row["PlayingTime"]);
    $disc->playingtime = $time;
    $discs[] = $disc;
}

$discs = 'getDiscs(' . json_encode($discs, JSON_PRETTY_PRINT) . ")";
echo $discs;

mysqli_close($link);
?>