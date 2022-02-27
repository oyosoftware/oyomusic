<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $albumid = $_GET['albumid'];
}

error_reporting(E_ERROR);
require_once('../settings.inc');
require_once('../helpers/functions.php');

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

$isboxset = $row["IsBoxset"];
$boxsetid = (int) $row["BoxsetId"];
$target = $target . $folder;

$symlink = "#d";
for ($i = 0; $i < 4; $i++) {
    $random = rand(0, 15);
    if ($random < 10) {
        $symlink .= $random;
    } else {
        $symlink .= chr(96 + $random - 9);
    }
}
if (file_exists($symlink)) {
    rmdir($symlink);
    unlink($symlink);
}
symlink($target, $symlink);

$len = mb_strlen($folder);
$pos = mb_strrpos($folder, "/");
$zipfolderpos = $pos;
$offset = -1 * ($len - $pos + 1);
$pos = mb_strrpos($folder, "/", $offset);
if ($boxsetid != -1) {
    $zipfolderpos = $pos;
}
$singlefolder = mb_substr($folder, $pos + 1);
$singlelen = mb_strrpos($singlefolder, "/");
$singlefolder = mb_substr($singlefolder, 0, $singlelen);
if ($boxsetid != -1 or $singlefolder == "Singles") {
    $offset = -1 * ($len - $pos + 1);
    $pos = mb_strrpos($folder, "/", $offset);
}
$symlinkpos = mb_strlen($folder);

$zip = new ZipArchive;
$tempfile = tempnam("../tmp", "");
$zip->open($tempfile, ZipArchive::CREATE);
$zipfilefolder = mb_substr($folder, $zipfolderpos + 1);
$zipfile = "$name - $zipfilefolder.zip";
$zipfile = utf8_decode($zipfile);

$zipfolder = mb_substr($folder, $pos + 1) . "/";
$filename = "../plugins/oyoplayer/oyoplayer.js";
$zipfilename = "oyoplayer.js";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "../plugins/oyoplayer/oyoplayer.css";
$zipfilename = "oyoplayer.css";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "album.html";
$zipfilename = "album.html";
$zip->addFile($filename, $zipfolder . $zipfilename);
if ($isboxset == true) {
    $filename = "../plugins/oyonavigator/oyonavigator.js";
    $zipfilename = "oyonavigator.js";
    $zip->addFile($filename, $zipfolder . $zipfilename);
    $filename = "albumlist.html";
    $zipfilename = "albumlist.html";
    $zip->addFile($filename, $zipfolder . $zipfilename);
}

makeJSON($albumid);

$sql = "select * from albums where id=$albumid or boxsetid=$albumid order by boxsetindex";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $counter++;
    $id = $row["Id"];
    $folder = $row["Folder"];

    $zipfolder = mb_substr($folder, $pos + 1) . "/";
    $folder = mb_substr($folder, $symlinkpos);

    $imagefilename = $symlink . $folder . "/" . "Folder.jpg";
    $zipfilename = "Folder.jpg";
    $zip->addFile($imagefilename, $zipfolder . $zipfilename);

    $sql = "select * from tracks where albumid=$id";
    $result2 = mysqli_query($link, $sql);

    while ($row2 = mysqli_fetch_assoc($result2)) {
        $filename = $symlink . $folder . "/" . $row2["FileName"];
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

rmdir($symlink);
unlink($symlink);

function makeJSON($albumid) {
    global $link, $symlink, $zip, $zipfolder;

    $album = new stdClass();

    $sql = "select * from artists inner join albums on artists.id=artistid where albums.id=$albumid";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);

    $album->artist = $row["Name"];
    $album->released = (int) $row["Released"];
    $album->title = $row["Title"];
    $album->disccount = (int) $row["DiscCount"];

    $formatid = (int) $row["FormatId"];
    $sql = "select * from formats where id='$formatid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);
    $album->format = $row2["Format"];

    $album->playingtime = formattime($row["PlayingTime"]);

    $genreid = (int) $row["GenreId"];
    $sql = "select * from genres where id='$genreid'";
    $result2 = mysqli_query($link, $sql);
    $row2 = mysqli_fetch_assoc($result2);
    $album->genre = $row2["Genre"];

    $album->imagefilename = "Folder.jpg";
    $album->isboxset = (boolean) $row["IsBoxset"];

    if (!$album->isboxset) {
        $album->discs = array();
        $sql = "select * from discs where albumid=$albumid order by albumid, discno";
        $result = mysqli_query($link, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $discno = (int) $row["DiscNo"];
            $album->discs[$discno] = new stdClass();
            $album->discs[$discno]->discno = (int) $row["DiscNo"];
            $album->discs[$discno]->title = $row["Title"];
            $album->discs[$discno]->playingtime = formattime($row["PlayingTime"]);
            $album->discs[$discno]->tracks = array();
            $sql = "select * from tracks where albumid=$albumid and discno=$discno order by albumid, discno, track";
            $result2 = mysqli_query($link, $sql);
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $track = (int) $row2["Track"];
                $album->discs[$discno]->tracks[$track] = new stdClass();
                $album->discs[$discno]->tracks[$track]->track = (int) $row2["Track"];
                $album->discs[$discno]->tracks[$track]->title = $row2["Title"];
                $artistid = (int) $row2["ArtistId"];
                $sql = "select * from artists where id='$artistid'";
                $result3 = mysqli_query($link, $sql);
                $row3 = mysqli_fetch_assoc($result3);
                $album->discs[$discno]->tracks[$track]->artist = $row3["Name"];
                $album->discs[$discno]->tracks[$track]->playingtime = formattime($row2["PlayingTime"]);
                $album->discs[$discno]->tracks[$track]->filename = $row2["FileName"];
                $file = $symlink . '/' . $row2["FileName"];
                $linkinfo = linkinfo($file);
                if ($linkinfo <> -1) {
                    $album->discs[$discno]->tracks[$track]->fileexists = true;
                } else {
                    $album->discs[$discno]->tracks[$track]->fileexists = false;
                }
            }
        }
    } else {
        $album->albums = array();
        $sql = "select * from artists inner join albums on artists.id=artistid where boxsetid=$albumid order by boxsetid, boxsetindex";
        $result = mysqli_query($link, $sql);
        $counter = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $counter += 1;
            $albumid = (int) $row["Id"];
            $album->albums[$counter] = new stdClass();
            $album->albums[$counter]->artist = $row["Name"];
            $album->albums[$counter]->released = (int) $row["Released"];
            $album->albums[$counter]->title = $row["Title"];
            $album->albums[$counter]->disccount = (int) $row["DiscCount"];

            $formatid = (int) $row["FormatId"];
            $sql = "select * from formats where id='$formatid'";
            $result2 = mysqli_query($link, $sql);
            $row2 = mysqli_fetch_assoc($result2);
            $album->albums[$counter]->format = $row2["Format"];

            $album->albums[$counter]->playingtime = formattime($row["PlayingTime"]);

            $genreid = (int) $row["GenreId"];
            $sql = "select * from genres where id='$genreid'";
            $result2 = mysqli_query($link, $sql);
            $row2 = mysqli_fetch_assoc($result2);
            $album->albums[$counter]->genre = $row2["Genre"];

            $folder = $row["Folder"];
            $len = mb_strlen($folder);
            $pos = mb_strrpos($folder, "/");
            $folder = mb_substr($folder, $pos + 1);
            $album->albums[$counter]->folder = $folder;

            $album->albums[$counter]->imagefilename = "Folder.jpg";
            $album->albums[$counter]->isboxset = (boolean) $row["IsBoxset"];
            $album->albums[$counter]->boxsetindex = (int) $row["BoxsetIndex"];
            $album->albums[$counter]->discs = array();
            $sql = "select * from discs where albumid=$albumid order by albumid, discno";
            $result2 = mysqli_query($link, $sql);
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $discno = (int) $row2["DiscNo"];
                $album->albums[$counter]->discs[$discno] = new stdClass();
                $album->albums[$counter]->discs[$discno]->discno = (int) $row2["DiscNo"];
                $album->albums[$counter]->discs[$discno]->title = $row2["Title"];
                $album->albums[$counter]->discs[$discno]->playingtime = formattime($row2["PlayingTime"]);
                $album->albums[$counter]->discs[$discno]->tracks = array();
                $sql = "select * from tracks where albumid=$albumid and discno=$discno order by albumid, discno, track";
                $result3 = mysqli_query($link, $sql);
                while ($row3 = mysqli_fetch_assoc($result3)) {
                    $track = (int) $row3["Track"];
                    $album->albums[$counter]->discs[$discno]->tracks[$track] = new stdClass();
                    $album->albums[$counter]->discs[$discno]->tracks[$track]->track = (int) $row3["Track"];
                    $album->albums[$counter]->discs[$discno]->tracks[$track]->title = $row3["Title"];
                    $artistid = (int) $row3["ArtistId"];
                    $sql = "select * from artists where id='$artistid'";
                    $result4 = mysqli_query($link, $sql);
                    $row4 = mysqli_fetch_assoc($result4);
                    $album->albums[$counter]->discs[$discno]->tracks[$track]->artist = $row4["Name"];
                    $album->albums[$counter]->discs[$discno]->tracks[$track]->playingtime = formattime($row3["PlayingTime"]);
                    $album->albums[$counter]->discs[$discno]->tracks[$track]->filename = $row3["FileName"];
                    $file = $symlink . "/" . $folder . "/" . $row3["FileName"];
                    $linkinfo = linkinfo($file);
                    if ($linkinfo <> -1) {
                        $album->albums[$counter]->discs[$discno]->tracks[$track]->fileexists = true;
                    } else {
                        $album->albums[$counter]->discs[$discno]->tracks[$track]->fileexists = false;
                    }
                }
            }
        }
    }

    $tempfile = tempnam("../tmp", "");
    $jsonfile = fopen($tempfile, "w");

    $json = json_encode($album, JSON_PRETTY_PRINT);
    fwrite($jsonfile, $json);
    $filename = $tempfile;
    $zipfilename = "album.json";
    $zip->addFile($filename, $zipfolder . $zipfilename);

    fclose($jsonfile);
}

mysqli_close($link);
?>
