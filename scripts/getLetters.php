<?php

error_reporting(E_ERROR);

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select letter from artists where albumcount > 0 group by letter order by letter";
$result = mysqli_query($link, $sql);

$letters = array();

while ($row = mysqli_fetch_assoc($result)) {
    $letter = (object) [];
    $letter->letter = $row["letter"];
    $letters[] = $letter;
}

$letters = 'getLetters(' . json_encode($letters, JSON_PRETTY_PRINT) . ")";
echo $letters;

mysqli_close($link);
?>