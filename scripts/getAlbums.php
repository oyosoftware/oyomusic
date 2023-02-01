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
require_once('../helpers/functions.php');

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

$data = 'getAlbums([';

while ($row = mysqli_fetch_assoc($result)) {
    $title = $row["Title"];
    $title = str_replace('\\', '\\\\', $title);
    $title = str_replace('"', '\"', $title);
    $time = formattime($row["PlayingTime"]);

    $formatid = $row["FormatId"];
    $sql = "select * from formats where id='$formatid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);

    $genreid = $row["GenreId"];
    $sql = "select * from genres where id='$genreid'";
    $result3 = mysqli_query($link, $sql);
    $row3 = mysqli_fetch_assoc($result3);

    $data .= '{'
            . 'albumid: ' . $row["Id"] . ', '
            . 'released: ' . $row["Released"] . ', '
            . 'title: "' . $title . '", '
            . 'disccount: ' . $row["DiscCount"] . ', '
            . 'format: "' . $row2["Format"] . '", '
            . 'playingtime: "' . $time . '", '
            . 'genre: "' . $row3["Genre"] . '", '
            . 'imagefilename: "' . $row["ImageFileName"] . '", '
            . 'isboxset: ' . $row["IsBoxset"]
            . '}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>