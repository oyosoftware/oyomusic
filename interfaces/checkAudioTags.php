<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../plugins/getid3/getid3.php');
require_once('../include/date_time.php');

function get_records($path) {
    global $servername, $link;
    if ($servername !== null) {
        $escpath = mysqli_real_escape_string($link, $path);
        $sql = "select count(*) as records from albums inner join tracks on id = albumid where folder like '$escpath%' ";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $records = $row["records"];
        return $records;
    }
}

function read_files($dir) {
    global $getID3, $counter, $servername, $onlyPrivateFrames;
    $iter = new DirectoryIterator($dir);

    foreach ($iter as $item) {
        if ($item != '.' && $item != '..') {
            if ($item->isDir()) {
                read_files("$dir/$item");
            } else {
                $ext = $item->getExtension();
                if ($ext == 'mp3' or $ext == 'wav' or $ext == 'flac' or $ext == 'ogg') {

                    $counter++;
                    if (($counter % 10000) == 0) {
                        $message = "counter: $counter " . date('H:i:s');
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

                    // fetch tags

                    $pathname = $item->getPathname();
                    $pathname = str_ireplace("\\", "/", $pathname);
                    if (mb_substr($pathname, 0, 7) === "file://") {
                        $pathname = mb_substr($pathname, 7);
                    }
                    $tags = $getID3->analyze($pathname);

                    if ($tags["tags"] === null) {
                        $message = "file name may be too long for " . $pathname;
                        if ($servername !== null) {
                            $response = array('message' => $message);
                            $json = json_encode($response);
                            echo $json . ", \n";
                        }
                        continue;
                    }

                    if ($ext == 'mp3' or $ext == 'wav') {
                        $firsttag = true;
                        $albumartist = $tags["tags"]["id3v2"]["band"][0];
                        $released = $tags["tags"]["id3v2"]["year"][0];
                        $albumtitle = $tags["tags"]["id3v2"]["album"][0];
                        $discno = $tags["tags"]["id3v2"]["part_of_a_set"][0];
                        if (!$discno) {
                            $discno = 1;
                        }
                        $track = $tags["tags"]["id3v2"]["track_number"][0];
                        $tracktitle = $tags["tags"]["id3v2"]["title"][0];
                        if ($servername !== null) {
                            if ($firsttag == true) {
                                $response = array('name' => $albumartist);
                                $json = json_encode($response);
                                echo $json . ",\n";
                                $response = array('title' => $albumtitle);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                            $response = array('pathname' => "$pathname");
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }

                        if ($onlyPrivateFrames == false) {
                            foreach ($tags["tags"]["id3v2"] as $key => $value) {
                                if ($key <> "title"
                                        and $key <> "artist"
                                        and $key <> "band"
                                        and $key <> "album"
                                        and $key <> "track_number"
                                        and $key <> "part_of_a_set"
                                        and $key <> "year"
                                        and $key <> "genre"
                                        and $key <> "length"
                                        and $key <> "text") {
                                    If ($firsttag == true) {
                                        $message = "$albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                                        if ($servername === null) {
                                            echo $message . "\r\n";
                                        } else {
                                            $response = array('message' => $message);
                                            $json = json_encode($response);
                                            echo $json . ",\n";
                                        }
                                        $firsttag = false;
                                    }
                                    foreach ($tags["tags"]["id3v2"][$key] as $key2 => $value2) {
                                        $message = "$key" . "[$key2]: $value2";
                                        if ($servername === null) {
                                            $message = "    " . $message;
                                            echo $message . "\r\n";
                                        } else {
                                            $message = "&nbsp;&nbsp;&nbsp;&nbsp;" . $message;
                                            $response = array('message' => $message);
                                            $json = json_encode($response, JSON_INVALID_UTF8_IGNORE);
                                            echo $json . ",\n";
                                        }
                                    }
                                }
                                if ($key == "text") {
                                    foreach ($tags["tags"]["id3v2"]["text"] as $key3 => $value3) {
                                        if ($key3 <> "Album Artist"
                                                and $key3 <> "Tool Name"
                                                and $key3 <> "Tool Version"
                                                and $key3 <> "HDCDx") {
                                            if ($firsttag == true) {
                                                $message = "$albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                                                if ($servername === null) {
                                                    echo $message . "\r\n";
                                                } else {
                                                    $response = array('message' => $message);
                                                    $json = json_encode($response);
                                                    echo $json . ",\n";
                                                }
                                                $firsttag = false;
                                            }
                                            $message = "$key" . "[$key3]: $value3";
                                            if ($servername === null) {
                                                $message = "    " . $message;
                                                echo "$message" . "\r\n";
                                            } else {
                                                $message = "&nbsp;&nbsp;&nbsp;&nbsp;" . $message;
                                                $response = array('message' => $message);
                                                $json = json_encode($response, JSON_INVALID_UTF8_IGNORE);
                                                echo $json . ",\n";
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $count = 0;
                        $count = count($tags["id3v2"]["PRIV"]);
                        if ($count > 0) {
                            If ($firsttag == true) {
                                $message = "$albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                                if ($servername === null) {
                                    echo $message . "\r\n";
                                } else {
                                    $response = array('message' => $message);
                                    $json = json_encode($response);
                                    echo $json . ",\n";
                                }
                                $firsttag = false;
                            }
                            $message = "there are $count PRIV (private) frames";
                            if ($servername === null) {
                                $message = "    " . $message;
                                echo "$message" . "\r\n";
                            } else {
                                $message = "&nbsp;&nbsp;&nbsp;&nbsp;" . $message;
                                $response = array('message' => $message);
                                $json = json_encode($response, JSON_INVALID_UTF8_IGNORE);
                                echo $json . ",\n";
                            }
                        }
                    }
                }
            }
        }
    }
}

$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);
$getID3 = new getID3;

$onlyPrivateFrames = false;

$base = "";
//$base = "/Populair/MNO/Of Montreal";

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

    $records = get_records($base);
    if ($servername !== null) {
        $response = array('records' => $records);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $message = "Check Audio Tags";
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

    $counter = 0;
    read_files($path);

    $message = "counter: $counter " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $jobend = time();
    $seconds = $jobend - $jobstart;
    $duration = formattime($seconds);

    $message = "job is ready " . date('H:i:s') . " and took $duration";
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
} else {
    $message = "error: path $path doesn't exist";
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }
}

mysqli_close($link);
?>