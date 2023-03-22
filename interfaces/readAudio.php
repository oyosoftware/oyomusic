<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../include/getid3/getid3.php');
require_once('../include/date_time.php');
require_once('../include/special_characters.php');

function getLetter($name) {
    $letter = strunacc(mb_substr($name, 0, 1));
    switch (true) {
        case preg_match("/[a-z]/", $letter):
            $letter = strtoupper($letter);
            break;
        case preg_match("/[A-Z]/", $letter):
            break;
        case preg_match("/[0-9]/", $letter):
            $letter = "#";
            break;
        case $letter == "(":
            $letter = "&";
            break;
        case!preg_match("/[A-Z]/", $letter):
            $letter = "$";
            break;
    }
    return $letter;
}

function get_records($path) {
    global $servername, $link;
    if ($servername !== null) {
        $escpath = mysqli_real_escape_string($link, $path);
        $sql = "select count(*) as records from albums inner join tracks on id = albumid where folder like '$escpath%'";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $records = $row["records"];
        return $records;
    }
}

function read_files($dir) {
    global $getID3, $link, $audiosource, $counter, $log, $servername, $records;
    $iter = new DirectoryIterator($dir);
    $itercounter = 0;
    $discnoprev = 0;
    $logged = false;
    $albumartistlogged = false;
    $albumtitlelogged = false;

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

                    $pos = mb_strlen($audiosource);
                    $folder = mb_substr($item->getPath(), $pos);
                    $filename = $item->getFilename();
                    $lastmodified = date("Ymd");

                    // fetch tags

                    $itercounter++;

                    $pathname = $item->getPathname();

                    $pathname = $item->getPathname();
                    $pathname = str_ireplace("\\", "/", $pathname);
                    if (mb_substr($pathname, 0, 7) === "file://") {
                        $pathname = mb_substr($pathname, 7);
                    }
                    $tag = $getID3->analyze($pathname);

                    if ($tag["tags"] === null) {
                        $logtext = "file name may be too long for " . $pathname;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ", \n";
                        }
                        continue;
                    }

                    if ($ext == 'mp3' or $ext == 'wav') {
                        $albumartist = $tag["tags"]["id3v2"]["band"][0];
                        $albumartistletter = getLetter($albumartist);
                        $genre = $tag["tags"]["id3v2"]["genre"][0];
                        $released = $tag["tags"]["id3v2"]["year"][0];
                        $albumtitle = $tag["tags"]["id3v2"]["album"][0];
                        $discno = $tag["tags"]["id3v2"]["part_of_a_set"][0];
                        if (!$discno) {
                            $discno = 1;
                        }
                        $artist = $tag["tags"]["id3v2"]["artist"][0];
                        $artistletter = getLetter($artist);
                        $track = $tag["tags"]["id3v2"]["track_number"][0];
                        $tracktitle = $tag["tags"]["id3v2"]["title"][0];
                    } else {
                        $albumartist = $tag["tags"]["vorbiscomment"]["albumartist"][0];
                        $albumartistletter = getLetter($albumartist);
                        $genre = $tag["tags"]["vorbiscomment"]["genre"][0];
                        $released = $tag["tags"]["vorbiscomment"]["date"][0];
                        $albumtitle = $tag["tags"]["vorbiscomment"]["album"][0];
                        $discno = $tag["tags"]["vorbiscomment"]["discnumber"][0];
                        if (!$discno) {
                            $discno = 1;
                        }
                        $artist = $tag["tags"]["vorbiscomment"]["artist"][0];
                        $artistletter = getLetter($artist);
                        $track = $tag["tags"]["vorbiscomment"]["tracknumber"][0];
                        $tracktitle = $tag["tags"]["vorbiscomment"]["title"][0];
                    }

                    $format = strtoupper($tag["audio"]["dataformat"]);
                    $playingtime = round($tag["playtime_seconds"]);
                    $bitrate = floor($tag["audio"]["bitrate"] / 1000);
                    $bitratemode = mb_strtoupper($tag["audio"]["bitrate_mode"]);

                    $id3warnings = $tag["warning"];
                    if ($id3warnings != null) {
                        $tracklogged = false;
                        foreach ($id3warnings as $value) {
                            if ($value and strpos($value, "ID3v1") == false) {
                                if ($tracklogged == false and $logged == false) {
                                    $logtext = "warning: file may be corrupt " . $folder . "/" . $filename;
                                    fwrite($log, $logtext . "\r\n");
                                    if ($servername !== null) {
                                        $response = array('logtext' => $logtext);
                                        $json = json_encode($response);
                                        echo $json . ",\n";
                                    }
                                }
                                if ($logged == false) {
                                    fwrite($log, $value . "\r\n");
                                    if ($servername !== null) {
                                        $response = array('logtext' => $value);
                                        $json = json_encode($response);
                                        echo $json . ",\n";
                                    }
                                }
                                $logged = true;
                                $tracklogged = true;
                            }
                        }
                    }

                    // logging

                    $error = false;
                    $warning = false;

                    if ($albumartist == "") {
                        $error = true;
                        if (!$albumartistlogged) {
                            $logtext = "error: albumartist is empty for album $folder";
                            fwrite($log, $logtext . "\r\n");
                            if ($servername !== null) {
                                $response = array('logtext' => $logtext);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                            $logged = true;
                            $albumartistlogged = true;
                        }
                    }

                    if ($genre == "") {
                        $warning = true;
                        $logtext = "warning: genre is empty for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if (!is_numeric($released)) {
                        $error = true;
                        $logtext = "error: released is not numeric for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if ($albumtitle == "") {
                        $error = true;
                        if (!$albumtitlelogged) {
                            $logtext = "error: albumtitle is empty for album $folder";
                            fwrite($log, $logtext . "\r\n");
                            if ($servername !== null) {
                                $response = array('logtext' => $logtext);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                            $logged = true;
                            $albumtitlelogged = true;
                        }
                    }

                    if (!is_numeric($discno)) {
                        $error = true;
                        $logtext = "error: discno is not numeric for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if ($artist == "") {
                        $warning = true;
                        $logtext = "warning: artist is empty for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if (!is_numeric($track)) {
                        $warning = true;
                        $logtext = "warning: track " . $track . " is not (completely) numeric for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $pos = mb_strpos($track, "/");
                        $track = mb_substr($track, 0, $pos);
                        if (!is_numeric($track)) {
                            $error = true;
                            $logtext = "error: track is still not numeric for track " . $folder . "/" . $filename;
                            fwrite($log, $logtext . "\r\n");
                            if ($servername !== null) {
                                $response = array('logtext' => $logtext);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                        }
                        $logged = true;
                    }

                    if ($tracktitle == "") {
                        $warning = true;
                        $logtext = "warning: tracktitle is empty for track " . $folder . "/" . $filename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if ($error) {
                        $itercounter--;
                        continue;
                    }

                    // album artists

                    if ($itercounter == 1) {
                        if ($servername !== null) {
                            $response = array('name' => $albumartist);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $escalbumartist = mysqli_real_escape_string($link, $albumartist);
                        $escalbumartistletter = mysqli_real_escape_string($link, $albumartistletter);
                        $sql = "select * from artists where name = '$escalbumartist'";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into artists (name, letter, countryid) values ('$escalbumartist', '$escalbumartistletter', -1)";
                            mysqli_query($link, $sqli);
                            $albumartistid = mysqli_insert_id($link);
                            $message = "insert $albumartist";
                            if ($servername === null) {
                                echo $message . "\r\n";
                            } else {
                                $response = array('message' => $message);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                        } else {
                            $row = mysqli_fetch_assoc($result);
                            $albumartistid = $row["Id"];
                            if ($row["Name"] != $albumartist or $row["Letter"] != $albumartistletter) {
                                $sqlu = "update artists set name = '$escalbumartist', letter = '$escalbumartistletter' where id = $albumartistid";
                                mysqli_query($link, $sqlu);
                                $message = "update $albumartist";
                                if ($servername === null) {
                                    echo $message . "\r\n";
                                } else {
                                    $response = array('message' => $message);
                                    $json = json_encode($response);
                                    echo $json . ",\n";
                                }
                            }
                        }
                    }

                    // formats

                    if ($itercounter == 1) {
                        $sql = "select * from formats where format = '$format'";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into formats (format) values ('$format')";
                            mysqli_query($link, $sqli);
                            $formatid = mysqli_insert_id($link);
                        } else {
                            $formatid = mysqli_fetch_assoc($result)["Id"];
                        }
                    }

                    // genres

                    if ($itercounter == 1) {
                        $escgenre = mysqli_real_escape_string($link, $genre);
                        if ($escgenre <> '') {
                            $sql = "select * from genres where genre = '$escgenre'";
                            $result = mysqli_query($link, $sql);
                            if (mysqli_affected_rows($link) == 0) {
                                $sqli = "insert into genres (genre) values ('$escgenre')";
                                mysqli_query($link, $sqli);
                                $genreid = mysqli_insert_id($link);
                            } else {
                                $genreid = mysqli_fetch_assoc($result)["Id"];
                            }
                        } else {
                            $genreid = -1;
                        }
                    }

                    // albums

                    if ($itercounter == 1) {
                        if ($servername !== null) {
                            $response = array('title' => $albumtitle);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $escalbumtitle = mysqli_real_escape_string($link, $albumtitle);
                        $escfolder = mysqli_real_escape_string($link, $folder);
                        $symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
                        $falbumartist = str_replace($symbols, "_", $albumartist);
                        $lastfolder = substr(strrchr($folder, '/'), 1);
                        $imagefilename = $falbumartist . " - " . $lastfolder . ".jpg";
                        $escimagefilename = mysqli_real_escape_string($link, $imagefilename);
                        $sql = "select * from albums where folder = '$escfolder'";
                        $result = mysqli_query($link, $sql);

                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into albums (artistid, released, title, formatid, genreid, folder, imagefilename, lastmodified)
                                     values ($albumartistid, $released, '$escalbumtitle', $formatid, $genreid, '$escfolder', '$escimagefilename', $lastmodified)";
                            mysqli_query($link, $sqli);
                            $albumid = mysqli_insert_id($link);
                            $message = "insert $albumartist - $released - $albumtitle";
                            if ($servername === null) {
                                echo $message . "\r\n";
                            } else {
                                $response = array('message' => $message);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                        } else {
                            $row = mysqli_fetch_assoc($result);
                            $albumid = $row["Id"];
                            if ($row["ArtistId"] != $albumartistid or $row["Released"] != $released or $row["Title"] != $albumtitle or
                                    $row["GenreId"] != $genreid) {
                                $sqlu = "update albums set artistid = $albumartistid, released = $released, title = '$escalbumtitle',
                                                           genreid = $genreid, lastmodified = $lastmodified
                                         where id = $albumid";
                                mysqli_query($link, $sqlu);
                                $message = "update $albumartist - $released - $albumtitle";
                                if ($servername === null) {
                                    echo $message . "\r\n";
                                } else {
                                    $response = array('message' => $message);
                                    $json = json_encode($response);
                                    echo $json . ",\n";
                                }
                            }
                            if ($row["Folder"] != $folder or $row["ImageFileName"] != $imagefilename) {
                                $sqlu = "update albums set folder = '$escfolder', imagefilename = '$escimagefilename', lastmodified = $lastmodified where id = $albumid";
                                mysqli_query($link, $sqlu);
                                $message = "update folder or image filename $albumartist - $released - $albumtitle";
                                if ($servername === null) {
                                    echo $message . "\r\n";
                                } else {
                                    $response = array('message' => $message);
                                    $json = json_encode($response);
                                    echo $json . ",\n";
                                }
                            }
                        }
                    }

                    // discs

                    if ($discno != $discnoprev) {
                        $sql = "select * from discs where albumid = $albumid and discno = $discno";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into discs (albumid, discno) values ($albumid, $discno)";
                            mysqli_query($link, $sqli);
                        }
                    }

                    // artists

                    $escartist = mysqli_real_escape_string($link, $artist);
                    $escartistletter = mysqli_real_escape_string($link, $artistletter);
                    if ($artist <> '') {
                        $sql = "select * from artists where name = '$escartist'";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into artists (name, letter, countryid) values ('$escartist', '$escartistletter', -1)";
                            mysqli_query($link, $sqli);
                            $artistid = mysqli_insert_id($link);
                        } else {
                            $row = mysqli_fetch_assoc($result);
                            $artistid = $row["Id"];
                            if ($row["Name"] != $artist or $row["Letter"] != $artistletter) {
                                $sqlu = "update artists set name = '$escartist', letter = '$escartistletter' where id = $artistid";
                                mysqli_query($link, $sqlu);
                                $message = "update $artist";
                                if ($servername === null) {
                                    echo $message . "\r\n";
                                } else {
                                    $response = array('message' => $message);
                                    $json = json_encode($response);
                                    echo $json . ",\n";
                                }
                            }
                        }
                    } else {
                        $artistid = -1;
                    }

                    // tracks

                    if ($servername !== null) {
                        $response = array('pathname' => "$folder/$filename");
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                    $esctracktitle = mysqli_real_escape_string($link, $tracktitle);
                    $escfolder = mysqli_real_escape_string($link, $folder);
                    $escfilename = mysqli_real_escape_string($link, $filename);
                    $sql = "select * from albums inner join tracks on id = albumid where folder = '$escfolder' and filename = '$escfilename'";
                    $result = mysqli_query($link, $sql);
                    $affectedrows = mysqli_affected_rows($link);

                    $keyexists = false;
                    if ($affectedrows == 0) {
                        $sql = "select * from albums inner join tracks on id=albumid where albumid=$albumid and discno=$discno and track=$track";
                        $result2 = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) > 0) {
                            $keyexists = true;
                        }
                    } else {
                        $row = mysqli_fetch_assoc($result);
                        if ($row["AlbumId"] != $albumid or $row["DiscNo"] != $discno or $row["Track"] != $track) {
                            $sql = "select * from albums inner join tracks on id=albumid where albumid=$albumid and discno=$discno and track=$track";
                            $result2 = mysqli_query($link, $sql);
                            if (mysqli_affected_rows($link) > 0) {
                                $keyexists = true;
                            }
                        }
                    }

                    if ($keyexists) {
                        $row2 = mysqli_fetch_assoc($result2);
                        $oldfolder = $row2["Folder"];
                        $oldfilename = $row2["FileName"];
                        $sqld = "delete from tracks where albumid=$albumid and discno=$discno and track=$track";
                        mysqli_query($link, $sqld);
                        $counter = $counter - 1;
                        $records = $records - 1;
                        if ($servername !== null) {
                            $response = array('records' => $records);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logtext = "warning: track index already exists and will be deleted for " . $oldfolder . "/" . $oldfilename;
                        fwrite($log, $logtext . "\r\n");
                        if ($servername !== null) {
                            $response = array('logtext' => $logtext);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        $logged = true;
                    }

                    if ($affectedrows == 0) {
                        $sqli = "insert into tracks (albumid, discno, track, title, artistid, playingtime, audiobitrate, audiobitratemode, filename, lastmodified)
                                 values ($albumid, $discno, $track, '$esctracktitle', $artistid, $playingtime, $bitrate, '$bitratemode', '$escfilename', $lastmodified)";
                        mysqli_query($link, $sqli);
                        $counter = $counter + 1;
                        $records = $records + 1;
                        if ($servername !== null) {
                            $response = array('records' => $records);
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                        if ($keyexists) {
                            $logtext = "warning: new track index is inserted for:" . $folder . "/" . $filename;
                            fwrite($log, $logtext . "\r\n");
                            if ($servername !== null) {
                                $response = array('logtext' => $logtext);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                            $logged = true;
                        }
                    } else {
                        $oldalbumid = $row["AlbumId"];
                        $olddiscno = $row["DiscNo"];
                        $oldtrack = $row["Track"];
                        if ($row["AlbumId"] != $albumid or $row["DiscNo"] != $discno or $row["Track"] != $track or
                                $row["Title"] != $tracktitle or $row["ArtistId"] != $artistid or $row["PlayingTime"] != $playingtime or
                                $row["AudioBitrate"] != $bitrate or $row["AudioBitrateMode"] != $bitratemode) {
                            $sqlu = "update tracks set albumid = $albumid, discno = $discno, track = $track,
                                                       title = '$esctracktitle', artistid = $artistid, playingtime = $playingtime,
                                                       audiobitrate = $bitrate, audiobitratemode = '$bitratemode', lastmodified = $lastmodified
                                     where albumid = $oldalbumid and discno = $olddiscno and track = $oldtrack";
                            mysqli_query($link, $sqlu);
                            $message = "update $albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                            if ($servername === null) {
                                echo $message . "\r\n";
                            } else {
                                $response = array('message' => $message);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                        }
                        if ($row["FileName"] != $filename) {
                            $sqlu = "update tracks set filename = '$escfilename', lastmodified = $lastmodified
                                     where albumid = $albumid and discno = $discno and track = $track";
                            mysqli_query($link, $sqlu);
                            $message = "update file $albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                            if ($servername === null) {
                                echo $message . "\r\n";
                            } else {
                                $response = array('message' => $message);
                                $json = json_encode($response);
                                echo $json . ",\n";
                            }
                        }
                    }

                    $discnoprev = $discno;
                } // endif ext audio
            } // end else not dir
        } // endif ./..
    } // end items in dir
}

$log = fopen("error.log", "w");
$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);
$getID3 = new getID3;

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

    $message = "Read Audio";
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
