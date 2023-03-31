<?php

error_reporting(E_ERROR);

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from genres order by genre";
$result = mysqli_query($link, $sql);

$genres = array();

while ($row = mysqli_fetch_assoc($result)) {
    $genre = (object) [];
    $genre->id = (int) $row["Id"];
    $genre->genre = htmlspecialchars($row["Genre"]);
    $genres[] = $genre;
}

$genres = 'getGenres(' . json_encode($genres, JSON_PRETTY_PRINT) . ")";
echo $genres;

mysqli_close($link);
?>