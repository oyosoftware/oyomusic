<?php

error_reporting(22519);
require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);

echo "\r\njob begins " . date('H:i:s') . "\r\n";

// artists

echo "\r\nupdate artists\r\n\r\n";
sleep(5);

$sql = "select * from artists";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $albumartistid = $row["Id"];
    $sqlc = "select count(*) as albumcount from albums where artistid = $albumartistid and boxsetindex = -1";
    $resultc = mysqli_query($link, $sqlc);
    $rowc = mysqli_fetch_assoc($resultc);
    $albumcount = $rowc["albumcount"];
    if ($albumcount != $row["AlbumCount"]) {
        $sqlu = "update artists set albumcount = $albumcount where id = $albumartistid";
        mysqli_query($link, $sqlu);
    }
}

// discs

echo "update discs\r\n\r\n";
sleep(5);

$sql = "select * from discs";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $albumid = $row["AlbumId"];
    $discno = $row["DiscNo"];
    $title = $row["Title"];
    $sqlc = "select title, disccount from albums where id = $albumid";
    $resultc = mysqli_query($link, $sqlc);
    $rowc = mysqli_fetch_assoc($resultc);
    $disccount = $rowc["disccount"];
    if (is_null($title)) {
        $title = $rowc["title"];
        if ($disccount > 1) {
            $title = "$title (Disc $discno)";
        }
    }
    $title = mysqli_real_escape_string($link, $title);
    $sqlc = "select sum(playingtime) as playingtime from tracks where albumid = $albumid and discno = $discno";
    $resultc = mysqli_query($link, $sqlc);
    $rowc = mysqli_fetch_assoc($resultc);
    $playingtime = $rowc["playingtime"];
    if ($row["Title"] != $title or $row["PlayingTime"] != $playingtime) {
        $sqlu = "update discs set title = '$title', playingtime = $playingtime where albumid = $albumid and discno = $discno";
        mysqli_query($link, $sqlu);
    }
}

// albums

echo "update albums\r\n\r\n";
sleep(5);

$sql = "select * from albums where isboxset = 0";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $albumid = $row["Id"];
    $sqlc = "select count(*) as disccount, sum(playingtime) as playingtime from discs where albumid = $albumid";
    $resultc = mysqli_query($link, $sqlc);
    $rowc = mysqli_fetch_assoc($resultc);
    $disccount = $rowc["disccount"];
    $playingtime = $rowc["playingtime"];
    if ($disccount != $row["DiscCount"] or $playingtime != $row["PlayingTime"]) {
        $sqlu = "update albums set disccount = $disccount, playingtime = $playingtime where id = $albumid";
        mysqli_query($link, $sqlu);
    }
}

// boxsets

echo "update boxsets\r\n\r\n";
sleep(5);

$sql = "select * from albums where isboxset = 1";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $albumid = $row["Id"];
    $sqlc = "select count(*) as disccount, sum(playingtime) as playingtime from albums where boxsetid = $albumid";
    $resultc = mysqli_query($link, $sqlc);
    $rowc = mysqli_fetch_assoc($resultc);
    $disccount = $rowc["disccount"];
    $playingtime = $rowc["playingtime"];
    if ($disccount != $row["DiscCount"] or $playingtime != $row["PlayingTime"]) {
        $sqlu = "update albums set disccount = $disccount, playingtime = $playingtime where id = $albumid";
        mysqli_query($link, $sqlu);
    }
}

mysqli_close($link);

echo "\r\njob is ready " . date('H:i:s') . "\r\n";
?>