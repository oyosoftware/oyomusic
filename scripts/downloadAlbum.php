<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(22519);
require_once('../settings.inc');

$log = fopen("output.log", "w");

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

$sql = "select * from artists inner join albums on artists.id=artistid where albums.id=$albumid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$name = $row["Name"];
$released = $row["Released"];
$title = $row["Title"];
$folder = $row["Folder"];
$target = $target . $folder;

$symaudiosource = "download";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
    unlink($symaudiosource);
}
symlink($target, $symaudiosource);

$zip = new ZipArchive;
$tempfile = tempnam("../tmp", "");
$zip->open($tempfile, ZipArchive::CREATE);
$zipfile = "$name - $released - $title.zip";

$len = mb_strlen($folder);
$pos = mb_strrpos($folder, "/");
$offset = -1 * ($len - $pos + 1);
$pos = mb_strrpos($folder, "/", $offset);

$singlefolder = mb_substr($folder, $pos + 1);
$singlelen = mb_strrpos($singlefolder, "/");
$singlefolder = mb_substr($singlefolder, 0, $singlelen);
if ($singlefolder == "Singles") {
    $offset = -1 * ($len - $pos + 1);
    $pos = mb_strrpos($folder, "/", $offset);
}

$sympos = mb_strlen($folder);

$sql = "select * from albums where id=$albumid or boxsetid=$albumid order by boxsetindex";
$result = mysqli_query($link, $sql);

$counter = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $counter++;
    $id = $row["Id"];
    $folder = $row["Folder"];

    $zipfolder = mb_substr($folder, $pos + 1) . "/";
    $folder = mb_substr($folder, $sympos);

    if ($counter == 1) {
        $filename = "../album.html";
        $zipfilename = "album.html";
        $zip->addFile($filename, $zipfolder . $zipfilename);
        $filename = "../plugins/oyoplayer/oyoplayer.js";
        $zipfilename = "oyoplayer.js";
        $zip->addFile($filename, $zipfolder . $zipfilename);
        $filename = "../plugins/oyoplayer/oyoplayer.css";
        $zipfilename = "oyoplayer.css";
        $zip->addFile($filename, $zipfolder . $zipfilename);
    }

    $imagefilename = $symaudiosource . $folder . "/" . "Folder.jpg";
    $zipfilename = "Folder.jpg";
    $zip->addFile($imagefilename, $zipfolder . $zipfilename);

    $sql = "select * from tracks where albumid=$id";
    $result2 = mysqli_query($link, $sql);

    while ($row2 = mysqli_fetch_assoc($result2)) {
        $filename = $symaudiosource . $folder . "/" . $row2["FileName"];
        $zipfilename = $row2["FileName"];
        $zip->addFile($filename, $zipfolder . $zipfilename);
    }
}

$zip->close();

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipfile . '"');
header('Content-Length: ' . filesize($tempfile));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

ob_start();
$resource = fopen($tempfile, "rb");
while (!feof($resource)) {
    echo fread($resource, 8192);
    ob_flush();
    ob_clean();
}
fclose($resource);
ob_end_clean();

unlink($tempfile);

fclose($log);

rmdir($symaudiosource);
unlink($symaudiosource);

function makeJSON() {
    $tempfile = tempnam("../tmp", "");
    $jsonfile = fopen($tempfile, "w");
    $sql = "select * from artists inner join albums on artists.id=artistid where albums.id=$albumid";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);

    $name = $row["Name"];

    $released = $row["Released"];
    $title = $row["Title"];
    $disccount = $row["DiscCount"];
    $formatid = $row["FormatId"];
    $playingtime = $row["PlayingTime"];
    $genreid = $row["GenreId"];
    $imagefilename = $row["ImageFileName"];
    $isboxset = $row["IsBoxset"];
    $boxsetid = $row["BoxstId"];
    $boxsetindex = $row["BoxsetIndex"];
}

mysqli_close($link);
?>
