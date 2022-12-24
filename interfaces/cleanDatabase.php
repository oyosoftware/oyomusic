<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../helpers/functions.php');

function delete_removed_tracks($dir) {
    global $link, $audiosource, $symlink, $log, $servername;

    $pos = mb_strlen($symlink);
    $dir = mb_substr($dir, $pos);
    $dir = mysqli_real_escape_string($link, $dir);

    $message = "delete of tracks begins " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from tracks
            inner join albums on albumid = id
            where folder like '$dir%'";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select albumid, discno, track, folder, filename from tracks
                inner join albums on albumid = id
                where folder like '$dir%'
                order by folder, filename";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;

        if (($counter % 10000) == 0) {
            $message = "track counter: $counter " . date('H:i:s');
            if ($servername === null) {
                echo $message . "\r\n";
            } else {
                $response = array('message' => $message);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }

        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        $file = $symlink . $row["folder"] . "/" . $row["filename"];
        $linkinfo = linkinfo($file);

        if ($linkinfo === -1) {
            $albumid = $row["albumid"];
            $discno = $row["discno"];
            $track = $row["track"];
            $sqld = "delete from tracks where albumid = $albumid and discno = $discno and track = $track";
            mysqli_query($link, $sqld);
            $path = $audiosource . $row["folder"] . "/" . $row["filename"];
            $logtext = "track " . $path . " doesn't exist anymore and will be deleted";
            fwrite($log, $logtext . "\r\n");
            if ($servername !== null) {
                $response = array('logtext' => $logtext);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
    }
    $message = "track counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
}

function clean_files() {
    global $link, $log, $servername;

    $message = "clean begins " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    // discs

    $message = "clean discs " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
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

    $sql = "select albumid, discno from discs";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;

        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        $albumid = $row["albumid"];
        $discno = $row["discno"];
        $sql = "select count(*) as trackcount from tracks where albumid = $albumid and discno = $discno";
        $result2 = mysqli_query($link, $sql);
        $trackcount = mysqli_fetch_assoc($result2)["trackcount"];
        if ($trackcount == 0) {
            $sqld = "delete from discs where albumid = $albumid and discno = $discno";
            mysqli_query($link, $sqld);
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

    $message = "clean albums " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from albums where isboxset = false";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select id, artistid, released, title from albums where isboxset = false";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;

        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        $id = $row["id"];
        $artistid = $row["artistid"];
        $released = $row["released"];
        $albumtitle = $row["title"];
        $sql = "select count(*) as disccount from discs where albumid = $id";
        $result2 = mysqli_query($link, $sql);
        $disccount = mysqli_fetch_assoc($result2)["disccount"];
        if ($disccount == 0) {
            $sqld = "delete from albums where id = $id";
            mysqli_query($link, $sqld);
            $sql = "select name from artists where id = $artistid";
            $result2 = mysqli_query($link, $sql);
            $albumartist = mysqli_fetch_assoc($result2)["name"];
            $logtext = "delete album " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
            fwrite($log, $logtext . "\r\n");
            if ($servername !== null) {
                $response = array('logtext' => $logtext);
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

    $message = "clean boxsets " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select count(*) as records from albums where isboxset = true";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    $records = $row["records"];
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $sql = "select id, artistid, released, title from albums where isboxset = true";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;

        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        $id = $row["id"];
        $artistid = $row["artistid"];
        $released = $row["released"];
        $boxsettitle = $row["title"];
        $sql = "select count(*) as albumcount from albums where boxsetid = $id";
        $result2 = mysqli_query($link, $sql);
        $albumcount = mysqli_fetch_assoc($result2)["albumcount"];
        if ($albumcount == 0) {
            $sqld = "delete from albums where id = $id";
            mysqli_query($link, $sqld);
            $sql = "select name from artists where id = $artistid";
            $result2 = mysqli_query($link, $sql);
            $boxsetartist = mysqli_fetch_assoc($result2)["name"];
            $logtext = "delete boxset " . $boxsetartist . " - " . $released . " - " . $boxsettitle;
            fwrite($log, $logtext . "\r\n");
            if ($servername !== null) {
                $response = array('logtext' => $logtext);
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

    $message = "clean artists " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
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

    $sql = "select id, name from artists";
    $result = mysqli_query($link, $sql);
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $counter++;

        if ($servername !== null) {
            $response = array('counter' => $counter);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        $id = $row["id"];
        $name = $row["name"];

        $sql = "select count(*) as albumcount from albums where artistid = $id";
        $result2 = mysqli_query($link, $sql);
        $albumcount = mysqli_fetch_assoc($result2)["albumcount"];

        $sql = "select count(*) as trackcount from tracks where artistid = $id";
        $result2 = mysqli_query($link, $sql);
        $trackcount = mysqli_fetch_assoc($result2)["trackcount"];

        if ($albumcount == 0 and $trackcount == 0) {
            $sqld = "delete from artists where id = $id";
            mysqli_query($link, $sqld);
            $logtext = "delete artist " . $id . " " . $name;
            fwrite($log, $logtext . "\r\n");
            if ($servername === null) {
                $response = array('logtext' => $logtext);
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

function shutdown() {
    global $symlink;
    rmdir($symlink);
}

register_shutdown_function('shutdown');

$log = fopen("error.log", "w");
$servername = $argv[1];
$link = mysqli_connect($server, $username, $password, $database);

$base = "";
//$base = "/Populair/MNO/Modern Eon";

$audiosource = str_ireplace("\\", "/", $audiosource);
$base = str_ireplace("\\", "/", $base);
$path = $audiosource . $base;

$symlink = "/#cd";
if (file_exists($symlink)) {
    rmdir($symlink);
}

if (file_exists($path)) {
    $jobstart = time();
    symlink($audiosource, $symlink);

    $message = "Clean Database";
    if ($servername === null) {
        echo "$message" . "\r\n";
    }

    $message = "job begins for audiosource $path " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $root = $symlink . $base;

    delete_removed_tracks($root);
    clean_files();

    $jobend = time();
    $seconds = $jobend - $jobstart;
    $duration = formattime($seconds);

    $message = "job is ready and took $duration";
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
} else {
    $message = "Error: path $path doesn't exist";
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
}

fclose($log);
mysqli_close($link);
?>