<?php

error_reporting(E_ERROR);

$albumid = filter_input(INPUT_GET, "albumid");

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from albums where id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

$formatid = $row["FormatId"];
$sql = "select * from formats where id='$formatid'";
$result2 = mysqli_query($link, $sql);
$row2 = mysqli_fetch_assoc($result2);

$genreid = $row["GenreId"];
$sql = "select * from genres where id='$genreid'";
$result3 = mysqli_query($link, $sql);
$row3 = mysqli_fetch_assoc($result3);

$album = (object) [];
$album->artistid = (int) $row["ArtistId"];
$album->released = (int) $row["Released"];
$album->title = $row["Title"];
$album->disccount = (int) $row["DiscCount"];
$album->format = $row2["Format"];
$time = formattime($row["PlayingTime"]);
$album->playingtime = $time;
$album->genre = $row3["Genre"];
$album->folder = $row["Folder"];
$album->imagefilename = $row["ImageFileName"];
$album->isboxset = (boolean) $row["IsBoxset"];
$album->boxsetid = (int) $row["BoxsetId"];

$album = 'getAlbum(' . json_encode($album, JSON_PRETTY_PRINT) . ")";
echo $album;

mysqli_close($link);
?>