<?php

error_reporting(E_ERROR);
require_once('../settings.inc');
require_once('../helpers/functions.php');

function calculate_totals() {
    global $link, $servername;

    // discs

    $message = "update discs " . date('H:i:s');
    if ($servername === null) {
        echo $message . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from discs";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select name, albumid, discno, discs.title, discs.playingtime from discs
                inner join albums on albums.id=albumid
                inner join artists on artists.id=artistid
                order by folder";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;
        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }
        $albumartist = $row["name"];
        $albumid = $row["albumid"];
        $discno = $row["discno"];
        $disctitle = $row["title"];
        $sqlc = "select title, disccount from albums where id = $albumid";
        $resultc = mysqli_query($link, $sqlc);
        $rowc = mysqli_fetch_assoc($resultc);
        $disccount = $rowc["disccount"];
        if (is_null($disctitle) or $disctitle == "") {
            $disctitle = $rowc["title"];
            if ($disccount > 1) {
                $disctitle = "$disctitle (Disc $discno)";
            }
        }
        $escdisctitle = mysqli_real_escape_string($link, $disctitle);
        $sqlc = "select sum(playingtime) as playingtime from tracks where albumid = $albumid and discno = $discno";
        $resultc = mysqli_query($link, $sqlc);
        $rowc = mysqli_fetch_assoc($resultc);
        $playingtime = $rowc["playingtime"];
        if ($row["title"] != $disctitle or $row["playingtime"] != $playingtime) {
            $sqlu = "update discs set title = '$escdisctitle', playingtime = $playingtime where albumid = $albumid and discno = $discno";
            mysqli_query($link, $sqlu);
            $message = "disc $disctitle of $albumartist has been updated";
            if ($servername === null) {
                echo $message . "\r\n";
            } else {
                $response = array('message' => $message);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
    }
    $message = "disc counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    // albums

    $message = "update albums " . date('H:i:s');
    if ($servername === null) {
        echo $message . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from albums";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select name, albums.id as id, title, disccount, playingtime from albums
                inner join artists on artists.id=artistid
                where isboxset = 0
                order by folder";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;
        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }
        $albumartist = $row["name"];
        $albumid = $row["id"];
        $title = $row["title"];
        $sqlc = "select count(*) as disccount, sum(playingtime) as playingtime from discs where albumid = $albumid";
        $resultc = mysqli_query($link, $sqlc);
        $rowc = mysqli_fetch_assoc($resultc);
        $disccount = $rowc["disccount"];
        $playingtime = $rowc["playingtime"];
        if ($disccount != $row["disccount"] or $playingtime != $row["playingtime"]) {
            $sqlu = "update albums set disccount = $disccount, playingtime = $playingtime where id = $albumid";
            mysqli_query($link, $sqlu);
            $message = "album $title of $albumartist has been updated";
            if ($servername === null) {
                echo $message . "\r\n";
            } else {
                $response = array('message' => $message);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
    }
    $message = "album counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    // boxsets

    $message = "update boxsets " . date('H:i:s');
    if ($servername === null) {
        echo $message . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from albums where isboxset = 1";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select name, albums.id as id, title, disccount, playingtime from albums
                inner join artists on artists.id=artistid
                where isboxset = 1
                order by folder";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;
        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }
        $albumartist = $row["name"];
        $albumid = $row["id"];
        $title = $row["title"];
        $sqlc = "select count(*) as disccount, sum(playingtime) as playingtime from albums where boxsetid = $albumid";
        $resultc = mysqli_query($link, $sqlc);
        $rowc = mysqli_fetch_assoc($resultc);
        $disccount = $rowc["disccount"];
        $playingtime = $rowc["playingtime"];
        if ($disccount != $row["disccount"] or $playingtime != $row["playingtime"]) {
            $sqlu = "update albums set disccount = $disccount, playingtime = $playingtime where id = $albumid";
            mysqli_query($link, $sqlu);
            $message = "boxset $title of $albumartist has been updated";
            if ($servername === null) {
                echo $message . "\r\n";
            } else {
                $response = array('message' => $message);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
    }
    $message = "boxset counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    // artists

    $message = "update artists " . date('H:i:s');
    if ($servername === null) {
        echo $message . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from artists";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select id, name, albumcount from artists
                order by name";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;
        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }
        $albumartistid = $row["id"];
        $albumartist = $row["name"];
        $sqlc = "select count(*) as albumcount from albums where artistid = $albumartistid and boxsetindex = -1";
        $resultc = mysqli_query($link, $sqlc);
        $rowc = mysqli_fetch_assoc($resultc);
        $albumcount = $rowc["albumcount"];
        if ($albumcount != $row["albumcount"]) {
            $sqlu = "update artists set albumcount = $albumcount where id = $albumartistid";
            mysqli_query($link, $sqlu);
            $message = "album artist $albumartist has been updated";
            if ($servername === null) {
                echo $message . "\r\n";
            } else {
                $response = array('message' => $message);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
    }
    $message = "artist counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
}

$servername = $argv[1];
$link = mysqli_connect($server, $username, $password, $database);

$jobstart = time();

$message = "Calculate Totals";
if ($servername === null) {
    echo "$message" . "\r\n";
}

$message = "job begins " . date('H:i:s');
if ($servername === null) {
    echo $message . "\r\n";
} else {
    $response = array('message' => $message);
    $json = json_encode($response);
    echo $json . ",\n";
}

calculate_totals();

$jobend = time();
$seconds = $jobend - $jobstart;
$duration = formattime($seconds);

$message = "job is ready " . date('H:i:s') . " and took $duration";
if ($servername === null) {
    echo $message . "\r\n";
} else {
    $response = array('message' => $message);
    $json = json_encode($response);
    echo $json . ",\n";
}

mysqli_close($link);
?>