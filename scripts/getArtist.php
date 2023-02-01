<?php

error_reporting(E_ERROR);

$artistid = filter_input(INPUT_GET, "artistid");

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from artists where id=$artistid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);

$data = 'getArtist({'
        . 'name: "' . $row["Name"] . '", '
        . 'letter: "' . $row["Letter"] . '", '
        . 'albumcount: ' . $row["AlbumCount"]
        . '})';

echo $data;

mysqli_close($link);
?>