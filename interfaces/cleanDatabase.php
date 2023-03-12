<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../include/date_time.php');

function delete_removed_tracks($dir) {
    global $link, $audiosource, $log, $servername;

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
    $previousfolder = "";
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

        $folder = $row["folder"];
        $filename = $row["filename"];

        if ($folder <> $previousfolder) {
            $dirlist = scandir($audiosource . $folder);
        }

        if (!in_array($filename, $dirlist)) {
            $albumid = $row["albumid"];
            $discno = $row["discno"];
            $track = $row["track"];
            $sqld = "delete from tracks where albumid = $albumid and discno = $discno and track = $track";
            mysqli_query($link, $sqld);
            $path = $audiosource . $folder . "/" . $filename;
            $logtext = "track " . $path . " doesn't exist anymore and will be deleted";
            fwrite($log, $logtext . "\r\n");
            if ($servername !== null) {
                $response = array('logtext' => $logtext);
                $json = json_encode($response);
                echo $json . ",\n";
            }
        }
        $previousfolder = $folder;
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
            $logtext = "delete artist " . $name;
            fwrite($log, $logtext . "\r\n");
            if ($servername !== null) {
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

$log = fopen("error.log", "w");
$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);

$base = "";
//$base = "/Populair/DEF/Foucault, Jeffrey";

$audiosource = str_ireplace("\\", "/", $audiosource);
$base = str_ireplace("\\", "/", $base);

switch (true) {
    case mb_substr($audiosource, 0, 2) === "//":
        break;
    case mb_substr($audiosource, 1, 2) === ":/":
        break;
    case mb_substr($audiosource, 0, 7) === "file://":
        break;
    case mb_substr($audiosource, 0, 1) === "/":
        $audiosource = $documentroot . $audiosource;
        break;
    default:
        $audiosource = "../" . $audiosource;
        break;
}

$path = $audiosource . $base;

if (file_exists($path)) {
    $jobstart = time();

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

    delete_removed_tracks($base);
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
