<?php

error_reporting(E_ERROR);

$letter = filter_input(INPUT_GET, "letter");

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from artists where letter like '$letter%' and albumcount > 0 order by name";
$result = mysqli_query($link, $sql);

$artists = array();

while ($row = mysqli_fetch_assoc($result)) {
    $artist = (object) [];
    $artist->id = (int) $row["Id"];
    $artist->name = $row["Name"];
    $artists[] = $artist;
}

$artists = 'getArtists(' . json_encode($artists, JSON_PRETTY_PRINT) . ")";
echo $artists;

mysqli_close($link);
?>