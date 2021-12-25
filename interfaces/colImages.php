<?php

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "select * from albums order by folder";
$result = mysqli_query($link, $sql);
$previousartistid = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $artistid = $row["ArtistId"];
    $folder = $row["Folder"];

    if ($artistid <> $previousartistid) {
        $sqla = "select * from artists where id = $artistid";
        $resulta = mysqli_query($link, $sqla);
        $rowa = mysqli_fetch_assoc($resulta);
        $symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
        $albumartist = str_replace($symbols, "_", $rowa["Name"]);

        $path = $audiosource . mb_substr($folder, 0, mb_strrpos($folder, '/')) . "/Folder.jpg";
        if (file_exists($path)) {
            $pathdate = date('YmdHis', filemtime($path));
            $newpath = "../" . $imagepathartists . "/" . $albumartist . ".jpg";

            if (file_exists($newpath)) {
                $newpathdate = date('YmdHis', filemtime($newpath));
            }
            if (!file_exists($newpath) || $pathdate > $newpathdate) {
                echo $newpath . "\r\n";
                $image1 = imagecreatefromjpeg($path);
                imagejpeg($image1, $newpath, 100);
            }
        }
    }

    $path = $audiosource . $folder . "/Folder.jpg";
    if (file_exists($path)) {
        $pathdate = date('YmdHis', filemtime($path));
        $lastfolder = mb_substr(mb_strrchr($folder, '/'), 1);

        $newpath = "../" . $imagepath . "/" . $albumartist . " - " . $lastfolder . ".jpg";
        if (file_exists($newpath)) {
            $newpathdate = date('YmdHis', filemtime($newpath));
        }
        if (!file_exists($newpath) || $pathdate > $newpathdate) {
            echo $newpath . "\r\n";
            $image1 = imagecreatefromjpeg($path);
            imagejpeg($image1, $newpath, 100);
        }

        $newpath = "../" . $imagepaththumbs . "/" . $albumartist . " - " . $lastfolder . ".jpg";
        if (file_exists($newpath)) {
            $newpathdate = date('YmdHis', filemtime($newpath));
        }
        if (!file_exists($newpath) || $pathdate > $newpathdate) {
            $image1 = imagecreatefromjpeg($path);
            $image2 = imagescale($image1, 50);
            imagejpeg($image2, $newpath, 100);
        }
    }
    $previousartistid = $artistid;
}