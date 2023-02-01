<?php

error_reporting(E_ERROR);

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from genres order by genre";
$result = mysqli_query($link, $sql);

$data = 'getGenres([';

while ($row = mysqli_fetch_assoc($result)) {
    $data .= '{id: ' . $row["Id"] . ', genre: "' . $row["Genre"] . '"}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>