<?php

error_reporting(22519);
require_once('../settings.inc');
require_once('../plugins/getid3/getid3.php');
require_once('../helpers/functions.php');

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

function read_files($dir) {
    global $getID3, $link, $symaudiosource, $counter, $log, $encoders;
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
                        echo "counter: " . $counter . " " . date('H:i:s') . "\r\n\r\n";
                    }

                    $pos = mb_strlen($symaudiosource);
                    $folder = mb_substr($item->getPath(), $pos);
                    $filename = $item->getFilename();
                    $lastmodified = date("Ymd");

                    // fetch tags

                    $itercounter++;
                    $pathname = $item->getPathname();
                    $tag = $getID3->analyze($pathname);

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
                                    fwrite($log, "warning: file may be corrupt " . $folder . "/" . $filename . "\r\n");
                                }
                                if ($logged == false) {
                                    fwrite($log, $value . "\r\n");
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
                            fwrite($log, "error: albumartist is empty for album $folder\r\n");
                            $logged = true;
                            $albumartistlogged = true;
                        }
                    }

                    if ($genre == "") {
                        $warning = true;
                        fwrite($log, "warning: genre is empty for track " . $folder . "/" . $filename . "\r\n");
                        $logged = true;
                    }

                    if (!is_numeric($released)) {
                        $error = true;
                        fwrite($log, "error: released is not numeric for track " . $folder . "/" . $filename . "\r\n");
                        $logged = true;
                    }

                    if ($albumtitle == "") {
                        $error = true;
                        if (!$albumtitlelogged) {
                            fwrite($log, "error: albumtitle is empty for album $folder\r\n");
                            $logged = true;
                            $albumtitlelogged = true;
                        }
                    }

                    if (!is_numeric($discno)) {
                        $error = true;
                        fwrite($log, "error: discno is not numeric for track " . $folder . "/" . $filename . "\r\n");
                        $logged = true;
                    }

                    if ($artist == "") {
                        $warning = true;
                        fwrite($log, "warning: artist is empty for track " . $folder . "/" . $filename . "\r\n");
                        $logged = true;
                    }

                    //$track = $tag["tags"]["id3v2"]["track_number"][0];
                    if (!is_numeric($track)) {
                        $warning = true;
                        fwrite($log, "warning: track " . $track . " is not (completely) numeric for track " . $folder . "/" . $filename . "\r\n");
                        $pos = mb_strpos($track, "/");
                        $track = mb_substr($track, 0, $pos);
                        if (!is_numeric($track)) {
                            $error = true;
                            fwrite($log, "error: track is still not numeric for track " . $folder . "/" . $filename . "\r\n");
                        }
                        $logged = true;
                    }

                    if ($tracktitle == "") {
                        $warning = true;
                        fwrite($log, "warning: tracktitle is empty for track " . $folder . "/" . $filename . "\r\n");
                        $logged = true;
                    }

                    if ($error) {
                        $itercounter--;
                        continue;
                    }

                    // album artists

                    if ($itercounter == 1) {
                        $escalbumartist = mysqli_real_escape_string($link, $albumartist);
                        $escalbumartistletter = mysqli_real_escape_string($link, $albumartistletter);
                        $sql = "select * from artists where name = '$escalbumartist'";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) == 0) {
                            $sqli = "insert into artists (name, letter, countryid) values ('$escalbumartist', '$escalbumartistletter', -1)";
                            mysqli_query($link, $sqli);
                            $albumartistid = mysqli_insert_id($link);
                            echo "insert " . $albumartist . "\r\n";
                        } else {
                            $row = mysqli_fetch_assoc($result);
                            $albumartistid = $row["Id"];
                            if ($row["Name"] != $albumartist or $row["Letter"] != $albumartistletter) {
                                $sqlu = "update artists set name = '$escalbumartist', letter = '$escalbumartistletter' where id = $albumartistid";
                                mysqli_query($link, $sqlu);
                                echo "update " . $albumartist . "\r\n";
                            }
                            if ($row["Name"] != $albumartist) {
                                fwrite($log, "warning: albumartist for album $folder has changed to $albumartist\r\n");
                                $logged = true;
                                echo "warning: albumartist for album $folder has changed to $albumartist\r\n";
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
                            echo "insert " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                        } else {
                            $row = mysqli_fetch_assoc($result);
                            $albumid = $row["Id"];
                            if ($row["ArtistId"] != $albumartistid or $row["Released"] != $released or $row["Title"] != $albumtitle or
                                    $row["GenreId"] != $genreid) {
                                $sqlu = "update albums set artistid = $albumartistid, released = $released, title = '$escalbumtitle',
                                                           genreid = $genreid, lastmodified = $lastmodified
                                         where id = $albumid";
                                mysqli_query($link, $sqlu);
                                echo "update " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                            }
                            if ($row["Folder"] != $folder or $row["ImageFileName"] != $imagefilename) {
                                $sqlu = "update albums set folder = '$escfolder', imagefilename = '$escimagefilename', lastmodified = $lastmodified where id = $albumid";
                                mysqli_query($link, $sqlu);
                                echo "update folder " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                            }
                            if ($row["Title"] != $albumtitle) {
                                fwrite($log, "warning: albumtitle for album $folder has changed to $albumtitle\r\n");
                                $logged = true;
                                echo "warning: albumtitle for album $folder has changed to $albumtitle\r\n";
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
                            if ($row["Letter"] != $artistletter) {
                                $sqlu = "update artists set letter = '$escartistletter' where id = $artistid";
                                mysqli_query($link, $sqlu);
                                echo "update " . $artist . "\r\n";
                            }
                        }
                    } else {
                        $artistid = -1;
                    }

                    // tracks

                    $esctracktitle = mysqli_real_escape_string($link, $tracktitle);
                    $escfolder = mysqli_real_escape_string($link, $folder);
                    $escfilename = mysqli_real_escape_string($link, $filename);
                    $sql = "select * from albums inner join tracks on id = albumid where folder = '$escfolder' and filename = '$escfilename'";
                    $result = mysqli_query($link, $sql);
                    $affectedrows = mysqli_affected_rows($link);

                    if ($affectedrows == 0) {
                        $sql = "select * from tracks where albumid = $albumid and discno = $discno and track = $track";
                        $result = mysqli_query($link, $sql);
                        if (mysqli_affected_rows($link) > 0) {
                            $sqld = "delete from tracks where albumid = $albumid and discno = $discno and track = $track";
                            mysqli_query($link, $sqld);
                            fwrite($log, "warning: key already exists for track and will be changed to " . $folder . "/" . $filename . "\r\n");
                            $logged = true;
                        }
                    } else {
                        $row = mysqli_fetch_assoc($result);
                        if ($row["AlbumId"] != $albumid or $row["DiscNo"] != $discno or $row["Track"] != $track) {
                            $sql = "select * from tracks where albumid = $albumid and discno = $discno and track = $track";
                            $result = mysqli_query($link, $sql);
                            if (mysqli_affected_rows($link) > 0) {
                                $sqld = "delete from tracks where albumid = $albumid and discno = $discno and track = $track";
                                mysqli_query($link, $sqld);
                                fwrite($log, "warning: key already exists for track and will be changed to:" . $folder . "/" . $filename . "\r\n");
                                $logged = true;
                            }
                        }
                    }

                    if ($affectedrows == 0) {
                        $sqli = "insert into tracks (albumid, discno, track, title, artistid, playingtime, audiobitrate, audiobitratemode, filename, lastmodified)
                                 values ($albumid, $discno, $track, '$esctracktitle', $artistid, $playingtime, $bitrate, '$bitratemode', '$escfilename', $lastmodified)";
                        mysqli_query($link, $sqli);
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
                        }
                        if ($row["FileName"] != $filename) {
                            $sqlu = "update tracks set filename = '$escfilename, lastmodified = $lastmodified
                                     where albumid = $albumid and discno = $discno and track = $track";
                            mysqli_query($link, $sqlu);
                            echo "update file " . $albumartist . " - " . $released . " - " . $albumtitle . " - " . $discno . "-" . $track . " - " . $tracktitle . "\r\n";
                        }
                        if ($row["Title"] != $tracktitle) {
                            fwrite($log, "warning: tracktitle for track $folder/$filename has changed to $tracktitle\r\n");
                            $logged = true;
                            echo "warning: tracktitle for track $folder/$filename has changed to $tracktitle\r\n";
                        }
                    }

                    $discnoprev = $discno;
                } // endif ext mp3
            } // end else not dir
        } // endif ./..
    } // end items in dir
    if ($logged) {
        fwrite($log, "\r\n");
        echo "\r\n";
    }
}

$log = fopen("error.log", "w");
$link = mysqli_connect($server, $username, $password, $database);
$getID3 = new getID3;

$base = "";
//$base = "/World/123";
$path = $audiosource . $base;

if (file_exists($path)) {
    $symaudiosource = "/#u";
    if (file_exists($symaudiosource)) {
        rmdir($symaudiosource);
    }
    symlink($audiosource, $symaudiosource);
    $path = realpath($path);
    $path = str_ireplace("\\", "/", $path);
    $pos = mb_strlen($path) - mb_strlen($base);
    $base = mb_substr($path, $pos);
    $root = $symaudiosource . $base;
    echo "\r\njob begins for audiosource " . $audiosource . $base . " " . date('H:i:s') . "\r\n\r\n";
    read_files($root);
    rmdir($symaudiosource);
    echo "counter: " . $counter . " " . date('H:i:s') . "\r\n\r\n";
    echo "job is ready " . "\r\n";
} else {
    echo "\r\nError: path " . $path . " doesn't exist\r\n";
}

mysqli_close($link);
fclose($log);
?>
