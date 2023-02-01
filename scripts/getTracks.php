<?php

error_reporting(E_ERROR);

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'GET') {
    $albumid = filter_input(INPUT_GET, "albumid");
}

require_once('../settings.inc');
require_once('../helpers/functions.php');

$log = fopen("output.log", "w");
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
        $audiosource = filter_input(INPUT_SERVER, DOCUMENT_ROOT) . $audiosource;
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

$data = 'getTracks([';

while ($row = mysqli_fetch_assoc($result)) {
    $title = $row["Title"];
    $title = str_replace('\\', '\\\\', $title);
    $title = str_replace('"', '\"', $title);

    $artistid = $row["ArtistId"];
    $sql = "select * from artists where id='$artistid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);
    $name = $row2["Name"];
    $name = str_replace('\\', '\\\\', $name);
    $name = str_replace('"', '\"', $name);

    $time = formattime($row["PlayingTime"]);

    $file = $audiosource . $folder . '/' . $row["FileName"];
    fwrite($log, $file . "\r\n");

    if (file_exists($file)) {
        $fileexists = 'true';
    } else {
        $fileexists = 'false';
    }

    $data .= '{'
            . 'discno: ' . $row["DiscNo"] . ', '
            . 'track: ' . $row["Track"] . ', '
            . 'title: "' . $title . '", '
            . 'name: "' . $name . '", '
            . 'playingtime: "' . $time . '", '
            . 'audiobitrate: ' . $row["AudioBitrate"] . ', '
            . 'filename: "' . $row["FileName"] . '", '
            . 'fileexists: ' . $fileexists . ''
            . '}, ';
}

$data .= '])';
echo $data;

fclose($log);
mysqli_close($link);
?>