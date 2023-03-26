<?php

error_reporting(E_ERROR);

$artistid = filter_input(INPUT_GET, "artistid");

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from artists where id=$artistid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

$artist = (object) [];
$artist->name = $row["Name"];
$artist->letter = $row["Letter"];
$artist->albumcount = (int) $row["AlbumCount"];

$artist = 'getArtist(' . json_encode($artist, JSON_PRETTY_PRINT) . ")";
echo $artist;

mysqli_close($link);
?>