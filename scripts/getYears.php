<?php

error_reporting(E_ERROR);

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select released from albums group by released order by released desc";
$result = mysqli_query($link, $sql);

$years = array();

while ($row = mysqli_fetch_assoc($result)) {
    $year = (object) [];
    $year->year = (int) $row["released"];
    $years[] = $year;
}

$years = 'getYears(' . json_encode($years, JSON_PRETTY_PRINT) . ")";
echo $years;

mysqli_close($link);
?>