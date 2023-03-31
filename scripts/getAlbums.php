<?php

error_reporting(E_ERROR);

$artistid = filter_input(INPUT_GET, "artistid");
$isboxset = filter_input(INPUT_GET, "isboxset");
$boxsetid = filter_input(INPUT_GET, "boxsetid");
$pageno = filter_input(INPUT_GET, "pageno");
$ovrrecordspage = filter_input(INPUT_GET, "ovrrecordspage");
if (!$ovrrecordspage) {
    $ovrrecordspage = 0;
}

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

if ($ovrrecordspage != 0) {
    $recordspage = $ovrrecordspage;
}

$first = $recordspage * $pageno - $recordspage;
if ($isboxset == 0) {
    $sql = "select * from albums where artistid=$artistid and boxsetindex = -1 order by artistid, released, title limit $first, $recordspage";
} else {
    $sql = "select * from albums where boxsetid=$boxsetid order by boxsetid, boxsetindex limit $first, $recordspage";
}
$result = mysqli_query($link, $sql);

$albums = array();

while ($row = mysqli_fetch_assoc($result)) {
    $formatid = $row["FormatId"];
    $sql = "select * from formats where id='$formatid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);

    $genreid = $row["GenreId"];
    $sql = "select * from genres where id='$genreid'";
    $result3 = mysqli_query($link, $sql);
    $row3 = mysqli_fetch_assoc($result3);

    $album = (object) [];
    $album->albumid = (int) $row["Id"];
    $album->released = (int) $row["Released"];
    $album->title = htmlspecialchars($row["Title"]);
    $album->disccount = (int) $row["DiscCount"];
    $album->format = htmlspecialchars($row2["Format"]);
    $time = formattime($row["PlayingTime"]);
    $album->playingtime = $time;
    $album->genre = htmlspecialchars($row3["Genre"]);
    $album->imagefilename = $row["ImageFileName"];
    $album->isboxset = (boolean) $row["IsBoxset"];
    $albums[] = $album;
}

$albums = 'getAlbums(' . json_encode($albums, JSON_PRETTY_PRINT) . ")";
echo $albums;

mysqli_close($link);
?>