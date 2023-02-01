<?php

error_reporting(E_ERROR);

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'GET') {
    $albumid = filter_input(INPUT_GET, "albumid");
}

require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$audiosource = str_ireplace("\\", "/", $audiosource);

switch (true) {
    case mb_substr($audiosource, 0, 2) === "//":
        break;
    case mb_substr($audiosource, 1, 2) === ":/":
        break;
    case mb_substr($audiosource, 0, 7) === "file://":
        break;
    case mb_substr($audiosource, 0, 1) === "/":
        $audiosource = filter_input(INPUT_SERVER, DOCUMENT_ROOT) . $audiosource;
        break;
    default:
        $audiosource = "../" . $audiosource;
        break;
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

$zip = new ZipArchive;
$tempfile = tempnam("../tmp", "");
$zip->open($tempfile, ZipArchive::CREATE);
$zipfilefolder = mb_substr($folder, $zipfolderpos + 1);
$zipfile = "$name - $zipfilefolder.zip";
$zipfile = utf8_decode($zipfile);

$zipfolder = mb_substr($folder, $pos + 1) . "/";
$filename = "../plugins/oyoplayer/oyoplayer.js";
$zipfilename = "include/oyoplayer.js";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "../plugins/oyoplayer/oyoplayer.css";
$zipfilename = "include/oyoplayer.css";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "../plugins/oyographics/oyographics.js";
$zipfilename = "include/oyographics.js";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "../plugins/oyomirror/oyomirror.js";
$zipfilename = "include/oyomirror.js";
$zip->addFile($filename, $zipfolder . $zipfilename);
$filename = "album.html";
$zipfilename = "album.html";
$zip->addFile($filename, $zipfolder . $zipfilename);
if ($isboxset == true) {
    $filename = "../plugins/oyonavigator/oyonavigator.js";
    $zipfilename = "include/oyonavigator.js";
    $zip->addFile($filename, $zipfolder . $zipfilename);
    $filename = "../plugins/oyotableheader/oyotableheader.js";
    $zipfilename = "include/oyotableheader.js";
    $zip->addFile($filename, $zipfolder . $zipfilename);
    $filename = "../plugins/oyopaddingbox/oyopaddingbox.js";
    $zipfilename = "include/oyopaddingbox.js";
    $zip->addFile($filename, $zipfolder . $zipfilename);
    $filename = "albumlist.html";
    $zipfilename = "albumlist.html";
    $zip->addFile($filename, $zipfolder . $zipfilename);
}

$sql = "select * from albums where id=$albumid or boxsetid=$albumid order by boxsetindex";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row["Id"];
    $folder = $row["Folder"];

    $zipalbumfolder = mb_substr($folder, $pos + 1) . "/";
    $imagefilename = $audiosource . $folder . "/" . "Folder.jpg";
    if (mb_substr($audiosource, 0, 7) === "file://") {
        $imagefilename = mb_substr($imagefilename, 7);
    }
    $zipfilename = "Folder.jpg";
    $zip->addFile($imagefilename, $zipalbumfolder . $zipfilename);

    $sql = "select * from tracks where albumid=$id";
    $result2 = mysqli_query($link, $sql);

    while ($row2 = mysqli_fetch_assoc($result2)) {
        $filename = $audiosource . $folder . "/" . $row2["FileName"];
        if (mb_substr($audiosource, 0, 7) === "file://") {
            $filename = mb_substr($filename, 7);
        }
        if (file_exists($filename)) {
            $zipfilename = $row2["FileName"];
            $zip->addFile($filename, $zipalbumfolder . $zipfilename);
        }
    }
}

$album = makeJSON($albumid);
$jsontempfile = tempnam("../tmp", "");
$jsonfile = fopen($jsontempfile, "w");
$json = "album = " . json_encode($album, JSON_PRETTY_PRINT);
fwrite($jsonfile, $json);
fclose($jsonfile);
$filename = $jsontempfile;
$zipfilename = "album.json";
$zip->addFile($filename, $zipfolder . $zipfilename);

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
unlink($jsontempfile);

function makeJSON($albumid) {
    global $link, $audiosource;

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
        $result2 = mysqli_query($link, $sql);
        while ($row2 = mysqli_fetch_assoc($result2)) {
            $discno = (int) $row2["DiscNo"];
            $album->discs[$discno] = new stdClass();
            $album->discs[$discno]->discno = (int) $row2["DiscNo"];
            $album->discs[$discno]->title = $row2["Title"];
            $album->discs[$discno]->playingtime = formattime($row2["PlayingTime"]);
            $album->discs[$discno]->tracks = array();
            $sql = "select * from tracks where albumid=$albumid and discno=$discno order by albumid, discno, track";
            $result3 = mysqli_query($link, $sql);
            while ($row3 = mysqli_fetch_assoc($result3)) {
                $track = (int) $row3["Track"];
                $album->discs[$discno]->tracks[$track] = new stdClass();
                $album->discs[$discno]->tracks[$track]->track = (int) $row3["Track"];
                $album->discs[$discno]->tracks[$track]->title = $row3["Title"];
                $artistid = (int) $row3["ArtistId"];
                $sql = "select * from artists where id='$artistid'";
                $result4 = mysqli_query($link, $sql);
                $row4 = mysqli_fetch_assoc($result4);
                $album->discs[$discno]->tracks[$track]->artist = $row4["Name"];
                $album->discs[$discno]->tracks[$track]->playingtime = formattime($row3["PlayingTime"]);
                $album->discs[$discno]->tracks[$track]->filename = $row3["FileName"];
                $file = $audiosource . $row["Folder"] . '/' . $row3["FileName"];
                if (file_exists($file)) {
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
                    $file = $audiosource . "/" . $row["Folder"] . "/" . $row3["FileName"];
                    if (file_exists($file)) {
                        $album->albums[$counter]->discs[$discno]->tracks[$track]->fileexists = true;
                    } else {
                        $album->albums[$counter]->discs[$discno]->tracks[$track]->fileexists = false;
                    }
                }
            }
        }
    }
    return $album;
}

mysqli_close($link);
?>