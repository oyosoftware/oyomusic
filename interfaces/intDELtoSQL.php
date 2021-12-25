<?php

error_reporting(E_ERROR);
require_once('../settings.inc');

function delete_removed_tracks($dir) {
    global $link, $symaudiosource, $log;

    $pos = mb_strlen($symaudiosource);
    $dir = mb_substr($dir, $pos);
    $dir = mysqli_real_escape_string($link, $dir);
    $dir = mysqli_real_escape_string($link, $dir); // double escape for like

    echo "\r\ndelete begins " . date('H:i:s') . "\r\n\r\n";

    $sql = "select * from tracks inner join albums on albumid = id where folder like '$dir%'";
    $result = mysqli_query($link, $sql);

    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {

        $counter++;

        if (($counter % 10000) == 0) {
            echo "counter: " . $counter . " " . date('H:i:s') . "\r\n\r\n";
        }

        $file = $symaudiosource . $row["Folder"] . "/" . $row["FileName"];
        $linkinfo = linkinfo($file);

        if ($linkinfo == -1) {
            $albumid = $row["AlbumId"];
            $discno = $row["DiscNo"];
            $track = $row["Track"];
            $sqld = "delete from tracks where albumid = $albumid and discno = $discno and track = $track";
            mysqli_query($link, $sqld);
            fwrite($log, "track " . $file . " doesn't exist anymore and will be deleted\r\n");
            $logged = true;
        }
    }
}

function clean_files() {
    global $link;

    echo "\r\nclean begins " . date('H:i:s') . "\r\n";

    // discs
    echo "\r\nclean discs\r\n\r\n";

    $sql = "select * from discs";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $albumid = $row["AlbumId"];
        $discno = $row["DiscNo"];
        $sql = "select count(*) as trackcount from tracks where albumid = $albumid and discno = $discno";
        $result2 = mysqli_query($link, $sql);
        $trackcount = mysqli_fetch_assoc($result2)["trackcount"];
        if ($trackcount == 0) {
            $sqld = "delete from discs where albumid = $albumid and discno = $discno";
            mysqli_query($link, $sqld);
        }
    }

    // albums
    echo "\r\nclean albums\r\n\r\n";

    $sql = "select * from albums where isboxset = false";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["Id"];
        $artistid = $row["ArtistId"];
        $released = $row["Released"];
        $albumtitle = $row["Title"];
        $sql = "select count(*) as disccount from discs where albumid = $id";
        $result2 = mysqli_query($link, $sql);
        $disccount = mysqli_fetch_assoc($result2)["disccount"];
        if ($disccount == 0) {
            $sqld = "delete from albums where id = $id";
            mysqli_query($link, $sqld);
            $sql = "select * from artists where id = $artistid";
            $result2 = mysqli_query($link, $sql);
            $albumartist = mysqli_fetch_assoc($result2)["Name"];
            echo "delete album " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
        }
    }

    // boxsets
    echo "\r\nclean boxsets\r\n\r\n";

    $sql = "select * from albums where isboxset = true";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["Id"];
        $artistid = $row["ArtistId"];
        $released = $row["Released"];
        $boxsettitle = $row["Title"];
        $sql = "select count(*) as albumcount from albums where boxsetid = $id";
        $result2 = mysqli_query($link, $sql);
        $albumcount = mysqli_fetch_assoc($result2)["albumcount"];
        if ($albumcount == 0) {
            $sqld = "delete from albums where id = $id";
            mysqli_query($link, $sqld);
            $sql = "select * from artists where id = $artistid";
            $result2 = mysqli_query($link, $sql);
            $boxsetartist = mysqli_fetch_assoc($result2)["Name"];
            echo "delete boxset " . $boxsetartist . " - " . $released . " - " . $boxsettitle . "\r\n";
        }
    }

    // artists
    echo "\r\nclean artists\r\n\r\n";

    $sql = "select * from artists";
    $result = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["Id"];
        $name = $row["Name"];

        $sql = "select count(*) as albumcount from albums where artistid = $id";
        $result2 = mysqli_query($link, $sql);
        $albumcount = mysqli_fetch_assoc($result2)["albumcount"];

        $sql = "select count(*) as trackcount from tracks where artistid = $id";
        $result2 = mysqli_query($link, $sql);
        $trackcount = mysqli_fetch_assoc($result2)["trackcount"];

        if ($albumcount == 0 and $trackcount == 0) {
            $sqld = "delete from artists where id = $id";
            mysqli_query($link, $sqld);
            echo "delete artist " . $id . " " . $name . "\r\n";
        }
    }
}

$log = fopen("error.log", "w");
$link = mysqli_connect($server, $username, $password, $database);

$symaudiosource = "/#d";
if (file_exists($symaudiosource)) {
    rmdir($symaudiosource);
}
symlink($audiosource, $symaudiosource);
delete_removed_tracks($symaudiosource);
rmdir($symaudiosource);
clean_files();

mysqli_close($link);
fclose($log);
?>
