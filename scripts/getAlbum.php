<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $albumid = $_GET['albumid'];
}

error_reporting(22519);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
  die("Database doesn't exist.");

mysqli_set_charset($link,"utf8");

$sql = "select * from albums where id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

$time = formattime($row["PlayingTime"]);
$title = $row["Title"];
$title = str_replace('\\', '\\\\', $title);
$title = str_replace('"', '\"', $title);

$formatid       = $row["FormatId"];
$sql            = "select * from formats where id='$formatid'";
$result2        = mysqli_query($link, $sql);
$row2           = mysqli_fetch_assoc($result2);

$genreid        = $row["GenreId"];
$sql            = "select * from genres where id='$genreid'";
$result3        = mysqli_query($link, $sql);
$row3           = mysqli_fetch_assoc($result3);

$data = 'getAlbum({'
            . 'artistid: '          .   $row["ArtistId"]                . ', '
            . 'released: '          .   $row["Released"]                . ', '
            . 'title: "'            .   $title                          . '", '
            . 'disccount: '         .   $row["DiscCount"]               . ', '
            . 'format: "'           .   $row2["Format"]                 . '", '
            . 'playingtime: "'      .   $time                           . '", '
            . 'genre: "'            .   $row3["Genre"]                  . '", '
            . 'folder: "'           .   $audiosource . $row["Folder"]   . '", '
            . 'imagefilename: "'    .   $row["ImageFileName"]           . '", '
            . 'isboxset: '          .   $row["IsBoxset"]                . ', '
            . 'boxsetid: '          .   $row["BoxsetId"]
        . '})';

echo $data;

mysqli_close($link);
?>


