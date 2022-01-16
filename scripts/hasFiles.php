<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(E_ERROR);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

if (file_exists($audiosource)) {
    $target = $audiosource;
} else {
    $target = $_SERVER["DOCUMENT_ROOT"] . $audiosource;
}

$sql = "select * from albums where id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$folder = $row["Folder"];
$target = $target . $folder;

$symaudiosource = "album";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
    unlink($symaudiosource);
}
symlink($target, $symaudiosource);

$sql = "select * from albums inner join tracks on id=albumid where id=$albumid or boxsetid=$albumid";
$result = mysqli_query($link, $sql);

$filesexist = false;
while ($row = mysqli_fetch_assoc($result)) {
    $file = $symaudiosource . '/' . $row["FileName"];
    $linkinfo = linkinfo($file);

    if ($linkinfo <> -1) {
        $filesexist = true;
        break;
    }

    $folder = $row["Folder"];
    $pos = mb_strrpos($folder, "/");
    $file = $symaudiosource . mb_substr($folder, $pos) . '/' . $row["FileName"];
    $linkinfo = linkinfo($file);

    if ($linkinfo <> -1) {
        $filesexist = true;
        break;
    }
}

echo $filesexist;

mysqli_close($link);

rmdir($symaudiosource);
unlink($symaudiosource);
?>