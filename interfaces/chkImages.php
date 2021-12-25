<?php

error_reporting(E_ERROR);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$log = fopen("error.log", "w");
$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

echo "\r\nscan of images begins " . date('H:i:s') . "\r\n\r\n";

$sql = "select * from albums";
$result = mysqli_query($link, $sql);
$counter = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $counter++;

    if (($counter % 1000) === 0) {
        echo "counter: " . $counter . " " . date('H:i:s') . "\r\n";
    }

    $folder = "../" . $imagepath;
    //$file = $folder . "/" . str_replace("\'", "'", $row["ImageFileName"]);
    $file = $folder . "/" . $row["ImageFileName"];

    if (!file_exists($file)) {
        echo $file . "\r\n";
        fwrite($log, str_replace("\'", "'", $file) . "\r\n");
        $folder = null;
        $file = null;
    }
}

echo "counter: " . $counter . " " . date('H:i:s') . "\r\n";

mysqli_close($link);
fclose($log);
?>
