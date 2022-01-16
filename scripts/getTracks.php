<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(E_ERROR);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$log = fopen("output.log", "w");

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

if (file_exists($audiosource)) {
    $target = $audiosource;
} else {
    $target = $_SERVER["DOCUMENT_ROOT"] . $audiosource;
}

$sql = "select * from albums where id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$folder = $row["Folder"];
$target = $target . $folder;

$symaudiosource = "album";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
    unlink($symaudiosource);
}
symlink($target, $symaudiosource);

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

    $file = $symaudiosource . '/' . $row["FileName"];
    $linkinfo = linkinfo($file);
    fwrite($log, $file . " " . $linkinfo . "\r\n");

    if ($linkinfo <> -1) {
        $fileexists = 'true,';
    } else {
        $fileexists = 'false,';
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

rmdir($symaudiosource);
unlink($symaudiosource);
?>
