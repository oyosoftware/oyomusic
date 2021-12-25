<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(E_ERROR);
require_once('../settings.inc');

$symaudiosource = "/#f";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
}
symlink($audiosource, $symaudiosource);

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

$sql = "select * from albums inner join tracks on id=albumid where id=$albumid or boxsetid=$albumid";
$result = mysqli_query($link, $sql);

$filesexist = false;
while ($row = mysqli_fetch_assoc($result)) {

    $file = $symaudiosource . $row["Folder"] . '/' . $row["FileName"];
    $linkinfo = linkinfo($file);

    fwrite($log, $file . " " . $linkinfo . "\r\n");

    if ($linkinfo <> -1) {
        $filesexist = true;
        break;
    }
}

echo $filesexist;

mysqli_close($link);

rmdir($symaudiosource);
?>