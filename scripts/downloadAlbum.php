<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(22519);
require_once('../settings.inc');

$log = fopen("output.log", "w");

$symaudiosource = "/#z";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
}
symlink($audiosource, $symaudiosource);

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

$zip = new ZipArchive;
$tempfile = tempnam("../tmp", "");
$zip->open($tempfile, ZipArchive::CREATE);

$sql = "select * from albums where id=$albumid or boxsetid=$albumid order by boxsetindex";
$result = mysqli_query($link, $sql);

$isboxset = false;
while ($row = mysqli_fetch_assoc($result)) {

    $id = $row["Id"];
    $artistid = $row["ArtistId"];
    $released = $row["Released"];
    $title = $row["Title"];
    $folder = $row["Folder"];
    if ($row["IsBoxset"] == true)
        $isboxset = true;
    $boxsetid = $row["BoxsetId"];

    if ($folder == "") {
        continue;
    }

    $len = mb_strlen($folder);
    $pos = mb_strrpos($folder, "/");
    if ($albumfolder == "") {
        $albumfolder = mb_substr($folder, $pos + 1);
    }
    $offset = -1 * ($len - $pos + 1);
    $pos = mb_strrpos($folder, "/", $offset);
    $zipfolder = mb_substr($folder, $pos + 1) . "/";
    if ($boxsetid > 0 or mb_substr($zipfolder, 0, 7) == "Singles") {
        $offset = -1 * ($len - $pos + 1);
        $pos = mb_strrpos($folder, "/", $offset);
    }
    $zipfolder = mb_substr($folder, $pos + 1) . "/";

    if ($zipfile == "") {
        $zipfile = mb_substr($folder, $pos + 1);
        if ($isboxset == true) {
            $len = mb_strrpos($zipfile, "/");
            $zipfile = mb_substr($zipfile, 0, $len);
        }
        $zipfile = str_replace("/", " - ", $zipfile) . ".zip";
    }

    $imagefilename = $symaudiosource . $folder . "/Folder.jpg";
    $zipfilename = "Folder.jpg";
    $zip->addFile($imagefilename, $zipfolder . $zipfilename);

    $sql = "select * from tracks where albumid=$id";
    $result2 = mysqli_query($link, $sql);

    while ($row2 = mysqli_fetch_assoc($result2)) {
        $filename = $symaudiosource . $folder . "/" . $row2["FileName"];
        fwrite($log, $filename . "\r\n");
        $zipfilename = $row2["FileName"];
        $zip->addFile($filename, $zipfolder . $zipfilename);
    }
}

if ($boxsetid > 0) {
    $len = mb_strlen($folder);
    $pos = mb_strrpos($folder, "/");
    $offset = $len - $pos + 1;

    $folder = mb_substr($folder, 0, $len - $offset + 1);
    $imagefilename = $symaudiosource . $folder . "/Folder.jpg";

    $len = mb_strlen($zipfolder);
    $zipfolder = mb_substr($zipfolder, 0, $len - $offset + 1);

    $zipfilename = "Folder.jpg";
    $zip->addFile($imagefilename, $zipfolder . $zipfilename);
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

mysqli_close($link);
?>
