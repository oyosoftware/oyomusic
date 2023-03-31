<?php

error_reporting(E_ERROR);

$albumid = filter_input(INPUT_GET, "albumid");

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$audiosource = str_replace("\\", "/", $audiosource);

switch (true) {
    case mb_substr($audiosource, 0, 2) === "//":
        break;
    case mb_substr($audiosource, 1, 2) === ":/":
        break;
    case mb_substr($audiosource, 0, 7) === "file://":
        break;
    case mb_substr($audiosource, 0, 1) === "/":
        $documentroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        if (is_null($documentroot)) {
            $documentroot = filter_input(INPUT_ENV, 'DOCUMENT_ROOT');
        }
        $audiosource = $documentroot . $audiosource;
        break;
    default:
        $audiosource = "../" . $audiosource;
        break;
}

$sql = "select * from albums where id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$folder = $row["Folder"];

$sql = "select * from tracks where albumid=$albumid order by albumid, discno, track";
$result = mysqli_query($link, $sql);

$tracks = array();

while ($row = mysqli_fetch_assoc($result)) {

    $artistid = $row["ArtistId"];
    $sql = "select * from artists where id='$artistid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);

    $track = (object) [];
    $track->albumid = (int) $row["AlbumId"];
    $track->discno = (int) $row["DiscNo"];
    $track->track = (int) $row["Track"];
    $track->title = htmlspecialchars($row["Title"]);
    $track->name = htmlspecialchars($row2["Name"]);
    $time = formattime($row["PlayingTime"]);
    $track->playingtime = $time;
    $track->audiobitrate = (int) $row["AudioBitrate"];
    $track->audiobitratemode = $row["AudioBitrateMode"];
    $track->filename = $row["FileName"];
    $file = $audiosource . $folder . '/' . $row["FileName"];
    if (file_exists($file)) {
        $track->fileexists = true;
    } else {
        $track->fileexists = false;
    }
    $tracks[] = $track;
}

$tracks = 'getTracks(' . json_encode($tracks, JSON_PRETTY_PRINT) . ")";
echo $tracks;

mysqli_close($link);
?>