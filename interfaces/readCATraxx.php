<?php

error_reporting(E_ERROR);
require_once('../settings.inc');
require_once('../helpers/functions.php');

//require_once('intDELtoSQL.php');

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
    global $link, $pdolink;
    $dir = access_escape($dir);

    $sql = "select track.TrackID, AlbumID, DiscNo, Index,"
            . "Mid(StrConv(Title, 64),   1, 254) as ucTitle1,"
            . "Mid(StrConv(Title, 64),   255, 254) as ucTitle2,"
            . "Length, AudioBitrate,"
            . "Mid(StrConv(Folder, 64),   1, 254) as ucFolder1,"
            . "Mid(StrConv(Folder, 64),   255, 254) as ucFolder2,"
            . "Mid(StrConv(Filename, 64),   1, 254) as ucFilename1,"
            . "Mid(StrConv(Filename, 64),   255, 254) as ucFilename2,"
            . "LastModified "
            . "from track inner join audiofilelink on track.trackid = audiofilelink.trackid where folder like '$dir%' order by folder, filename";

    $pdoresult = $pdolink->query($sql);
    $discnoprev = 0;
    $folderprev = "";
    $boxsettitleprev = "";

    while ($row = $pdoresult->fetch()) {

        // get CATraxx information
        // track and audio link
        $cattrackid = $row["TrackID"];
        $catalbumid = $row["AlbumID"];
        $discno = $row["DiscNo"];
        $track = $row["Index"];
        $tracktitle = mb_convert_encoding($row["ucTitle1"] . $row["ucTitle2"], 'UTF-8', 'UTF-16LE');
        $playingtime = $row["Length"];
        $bitrate = $row["AudioBitrate"];
        if ($bitrate % 16 == 0) {
            $bitratemode = "CBR";
        } else {
            $bitratemode = "VBR";
        }
        $folder = mb_convert_encoding($row["ucFolder1"] . $row["ucFolder2"], 'UTF-8', 'UTF-16LE');
        $folder = str_replace("M:\\Music", "", $folder);
        $folder = str_replace("\\", "/", $folder);
        $folder = mb_substr($folder, 0, mb_strlen($folder) - 1);
        $filename = mb_convert_encoding($row["ucFilename1"] . $row["ucFilename2"], 'UTF-8', 'UTF-16LE');
        $time = strtotime($row["LastModified"]);
        $trackmodified = date('Ymd', $time);

        // track artist
        $sql = "select ArtistPersonID from artisttracklink where trackid = $cattrackid";
        $pdoresult2 = $pdolink->query($sql);
        $row2 = $pdoresult2->fetch();
        $catartistid = $row2["ArtistPersonID"];
        $sql = "select StrConv(Name, 64) as ucName from artistperson where artistpersonid = $catartistid";
        $pdoresult2 = $pdolink->query($sql);
        $row2 = $pdoresult2->fetch();
        $artist = mb_convert_encoding($row2["ucName"], 'UTF-8', 'UTF-16LE');
        $artistletter = getLetter($artist);

        // disc
        if ($folder != $folderprev or $discno != $discnoprev) {
            $sql = "select StrConv(Title, 64) as ucTitle from media where albumid = $catalbumid and discno = $discno";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $disctitle = mb_convert_encoding($row2["ucTitle"], 'UTF-8', 'UTF-16LE');
            //$discplayingtime = $row2["PlayingTime"];
        }

        // album
        if ($folder != $folderprev) {
            $sql = "select Released, StrConv(Title, 64) as ucTitle, FormatID, StatusID, BoxSetID, BoxSetIndex, LastModified from album where albumid = $catalbumid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $released = $row2["Released"];
            $albumtitle = mb_convert_encoding($row2["ucTitle"], 'UTF-8', 'UTF-16LE');
            $catformatid = $row2["FormatID"];
            $statusid = $row2["StatusID"];
            $catboxsetid = $row2["BoxSetID"];
            if ($row2["BoxSetIndex"] <> -1) {
                $boxsetindex = $row2["BoxSetIndex"] - 1;
                $len = mb_strrpos($folder, "/");
                $boxsetfolder = mb_substr($folder, 0, $len);
            } else {
                $boxsetindex = -1;
            }
            $time = strtotime($row2["LastModified"]);
            $albummodified = date("Ymd", $time);
        }

        // album artist
        if ($folder != $folderprev) {
            $sql = "select ArtistPersonID from artistalbumlink where albumid = $catalbumid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $catalbumartistid = $row2["ArtistPersonID"];
            $sql = "select StrConv(Name, 64) as ucName, CountryID from artistperson where artistpersonid = $catalbumartistid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $albumartist = mb_convert_encoding($row2["ucName"], 'UTF-8', 'UTF-16LE');
            $albumartistletter = getLetter($albumartist);
            $catcountryid = $row2["CountryID"];
        }

        // country
        if ($folder != $folderprev) {
            $sql = "select * from country where countryid = $catcountryid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $country = utf8_encode($row2["Country"]);
        }

        // format
        if ($folder != $folderprev) {
            $sql = "select * from format where formatid = $catformatid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $format = utf8_encode($row2["Format"]);
        }

        // genre
        if ($folder != $folderprev) {
            $sql = "select * from genrealbumlink where albumid = $catalbumid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $catgenreid = $row2["GenreID"];
            $sql = "select * from genre where genreid = $catgenreid";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $genre = utf8_encode($row2["MainGenre"]);
        }

        // boxset
        if ($catboxsetid <> -1) {
            $sql = "select AlbumID, Released, StrConv(Title, 64) as ucTitle, FormatID, StatusID, LastModified from album where boxsetid = $catboxsetid and boxsetindex = 1";
            $pdoresult2 = $pdolink->query($sql);
            $row2 = $pdoresult2->fetch();
            $boxsettitle = mb_convert_encoding($row2["ucTitle"], 'UTF-8', 'UTF-16LE');

            if ($boxsettitle != $boxsettitleprev) {

                $catboxsetalbumid = $row2["AlbumID"];
                $boxsetreleased = $row2["Released"];
                $catboxsetformatid = $row2["FormatID"];
                $boxsetstatusid = $row2["StatusID"];
                $time = strtotime($row2["LastModified"]);
                $boxsetmodified = date("Ymd", $time);

                // boxset artist
                $sql = "select * from artistalbumlink where albumid = $catboxsetalbumid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $catboxsetartistid = $row2["ArtistPersonID"];
                $sql = "select StrConv(Name, 64) as ucName, CountryID from artistperson where artistpersonid = $catboxsetartistid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $boxsetartist = mb_convert_encoding($row2["ucName"], 'UTF-8', 'UTF-16LE');
                $boxsetartistletter = getLetter($boxsetartist);
                $catboxsetcountryid = $row2["CountryID"];

                // boxset artist country
                $sql = "select * from country where countryid = $catboxsetcountryid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $boxsetcountry = utf8_encode($row2["Country"]);

                // boxset format
                $sql = "select * from format where formatid = $catboxsetformatid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $boxsetformat = utf8_encode($row2["Format"]);

                // boxset genre
                $sql = "select * from genrealbumlink where albumid = $catboxsetalbumid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $catboxsetgenreid = $row2["GenreID"];
                $sql = "select * from genre where genreid = $catboxsetgenreid";
                $pdoresult2 = $pdolink->query($sql);
                $row2 = $pdoresult2->fetch();
                $boxsetgenre = utf8_encode($row2["MainGenre"]);
            } // end boxset
        }

        // Write to MySQL

        if ($catboxsetid <> -1) {
            if ($boxsettitle != $boxsettitleprev) {

                // boxset country
                $escboxsetcountry = mysqli_real_escape_string($link, $boxsetcountry);
                if ($escboxsetcountry <> '') {
                    $sql = "select * from countries where country = '$escboxsetcountry'";
                    $result = mysqli_query($link, $sql);
                    if (mysqli_affected_rows($link) == 0) {
                        $sqli = "insert into countries (country) values ('$escboxsetcountry')";
                        mysqli_query($link, $sqli);
                        $boxsetcountryid = mysqli_insert_id($link);
                    } else {
                        $boxsetcountryid = mysqli_fetch_assoc($result)["Id"];
                    }
                } else {
                    $boxsetcountryid = -1;
                }

                // boxset artist
                $escboxsetartist = mysqli_real_escape_string($link, $boxsetartist);
                $escboxsetartistletter = mysqli_real_escape_string($link, $boxsetartistletter);
                $sql = "select * from artists where name = '$escboxsetartist'";
                $result = mysqli_query($link, $sql);
                if (mysqli_affected_rows($link) == 0) {
                    $sqli = "insert into artists (name, letter, countryid) values ('$escboxsetartist', '$escboxsetartistletter', $boxsetcountryid)";
                    mysqli_query($link, $sqli);
                    $boxsetartistid = mysqli_insert_id($link);
                } else {
                    $row2 = mysqli_fetch_assoc($result);
                    $boxsetartistid = $row2["Id"];
                    if ($row2["Name"] != $boxsetartist or $row2["Letter"] != $boxsetartistletter or $row2["CountryId"] != $boxsetcountryid) {
                        $sqlu = "update artists set name = '$escboxsetartist', letter = '$escboxsetartistletter', countryid = $boxsetcountryid where id = $boxsetartistid";
                        mysqli_query($link, $sqlu);
                        echo "update " . $boxsetartistletter . " - " . $boxsetartist . " - " . $boxsetcountry . "\r\n";
                    }
                }

                // boxset format
                $escboxsetformat = mysqli_real_escape_string($link, $boxsetformat);
                if ($escboxsetformat <> '') {
                    $sql = "select * from formats where format = '$escboxsetformat'";
                    $result = mysqli_query($link, $sql);
                    if (mysqli_affected_rows($link) == 0) {
                        $sqli = "insert into formats (format) values ('$escboxsetformat')";
                        mysqli_query($link, $sqli);
                        $boxsetformatid = mysqli_insert_id($link);
                    } else {
                        $boxsetformatid = mysqli_fetch_assoc($result)["Id"];
                    }
                } else {
                    $boxsetformatid = -1;
                }

                // boxset genre
                $escboxsetgenre = mysqli_real_escape_string($link, $boxsetgenre);
                if ($escboxsetgenre <> '') {
                    $sql = "select * from genres where genre = '$escboxsetgenre'";
                    $result = mysqli_query($link, $sql);
                    if (mysqli_affected_rows($link) == 0) {
                        $sqli = "insert into genres (genre) values ('$escboxsetgenre')";
                        mysqli_query($link, $sqli);
                        $boxsetgenreid = mysqli_insert_id($link);
                    } else {
                        $boxsetgenreid = mysqli_fetch_assoc($result)["Id"];
                    }
                } else {
                    $boxsetgenreid = -1;
                }

                // boxset
                $escboxsettitle = mysqli_real_escape_string($link, $boxsettitle);
                $escboxsetfolder = mysqli_real_escape_string($link, $boxsetfolder);
                $symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
                $sboxsetartist = str_replace($symbols, "_", $boxsetartist);
                $lastfolder = mb_substr(mb_strrchr($boxsetfolder, '/'), 1);
                $boxsetimagefilename = $sboxsetartist . " - " . $lastfolder . ".jpg";
                $escboxsetimagefilename = mysqli_real_escape_string($link, $boxsetimagefilename);
                $sql = "select * from albums where folder = '$escboxsetfolder'";
                $result = mysqli_query($link, $sql);

                if (mysqli_affected_rows($link) == 0) {
                    $sqli = "insert into albums (artistid, released, title, formatid, genreid, folder, imagefilename, checked, statusid, isboxset, boxsetid, boxsetindex, lastmodified)"
                            . " values ($boxsetartistid, $boxsetreleased, '$escboxsettitle', $boxsetformatid, $boxsetgenreid, '$escboxsetfolder', '$escboxsetimagefilename', false, $boxsetstatusid, true, -1, -1, $boxsetmodified)";
                    mysqli_query($link, $sqli);
                    $boxsetid = mysqli_insert_id($link);
                    echo $boxsetartist . " - " . $boxsetreleased . " - " . $boxsettitle . "\r\n";
                } else {
                    $row2 = mysqli_fetch_assoc($result);
                    $boxsetid = $row2["Id"];
                    if ($row2["ArtistId"] != $boxsetartistid or $row2["Released"] != $boxsetreleased or $row2["Title"] != $boxsettitle or
                            $row2["FormatId"] != $boxsetformatid or $row2["GenreId"] != $boxsetgenreid or $row2["StatusId"] != $boxsetstatusid) {
                        $sqlu = "update albums set title = '$escboxsettitle', formatid = $boxsetformatid, genreid = $boxsetgenreid, folder = '$escboxsetfolder', statusid = $boxsetstatusid where id = $boxsetid";
                        mysqli_query($link, $sqlu);
                        echo "update " . $boxsetartist . " - " . $boxsetreleased . " - " . $boxsettitle . "\r\n";
                    }
                    if ($row2["Folder"] != $boxsetfolder or $row2["ImageFileName"] != $boxsetimagefilename) {
                        $sqlu = "update albums set folder = '$escboxsetfolder', imagefilename = '$escboxsetimagefilename' where id = $boxsetid";
                        mysqli_query($link, $sqlu);
                        echo "update " . $boxsetartist . " - " . $boxsetreleased . " - " . $boxsettitle . "\r\n";
                    }
                    $time = strtotime($row2["LastModified"]);
                    $lastmodified = date("Ymd", $time);
                    if ($lastmodified != $boxsetmodified) {
                        $sqlu = "update albums set lastmodified = $boxsetmodified where id = $boxsetid";
                        mysqli_query($link, $sqlu);
                    }
                }
            } // end boxset
            $boxsettitleprev = $boxsettitle;
        }

        // country
        if ($folder != $folderprev) {
            $esccountry = mysqli_real_escape_string($link, $country);
            if ($esccountry <> '') {
                $sql = "select * from countries where country = '$esccountry'";
                $result = mysqli_query($link, $sql);
                if (mysqli_affected_rows($link) == 0) {
                    $sqli = "insert into countries (country) values ('$esccountry')";
                    mysqli_query($link, $sqli);
                    $countryid = mysqli_insert_id($link);
                } else {
                    $countryid = mysqli_fetch_assoc($result)["Id"];
                }
            } else {
                $countryid = -1;
            }
        }

        // album artist
        if ($folder != $folderprev) {
            $escalbumartist = mysqli_real_escape_string($link, $albumartist);
            $escalbumartistletter = mysqli_real_escape_string($link, $albumartistletter);
            $sql = "select * from artists where name = '$escalbumartist'";
            $result = mysqli_query($link, $sql);
            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into artists (name, letter, countryid) values ('$escalbumartist', '$escalbumartistletter', $countryid)";
                mysqli_query($link, $sqli);
                $albumartistid = mysqli_insert_id($link);
            } else {
                $row2 = mysqli_fetch_assoc($result);
                $albumartistid = $row2["Id"];
                if ($row2["Name"] != $albumartist or $row2["Letter"] != $albumartistletter or $row2["CountryId"] != $countryid) {
                    $sqlu = "update artists set name = '$escalbumartist', letter = '$escalbumartistletter', countryid = $countryid where id = $albumartistid";
                    mysqli_query($link, $sqlu);
                    echo "update " . $albumartistletter . " - " . $albumartist . " - " . $country . "\r\n";
                }
            }
        }

        // formats
        if ($folder != $folderprev) {
            $escformat = mysqli_real_escape_string($link, $format);
            if ($escformat <> '') {
                $sql = "select * from formats where format = '$escformat'";
                $result = mysqli_query($link, $sql);
                if (mysqli_affected_rows($link) == 0) {
                    $sqli = "insert into formats (format) values ('$escformat')";
                    mysqli_query($link, $sqli);
                    $formatid = mysqli_insert_id($link);
                } else {
                    $formatid = mysqli_fetch_assoc($result)["Id"];
                }
            } else {
                $formatid = -1;
            }
        }

        // genres
        if ($folder != $folderprev) {
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
        if ($folder != $folderprev) {
            $escalbumtitle = mysqli_real_escape_string($link, $albumtitle);
            $escfolder = mysqli_real_escape_string($link, $folder);
            $symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
            $salbumartist = str_replace($symbols, "_", $albumartist);
            $lastfolder = mb_substr(mb_strrchr($folder, '/'), 1);
            $imagefilename = $salbumartist . " - " . $lastfolder . ".jpg";
            $escimagefilename = mysqli_real_escape_string($link, $imagefilename);
            if ($boxsetindex == -1) {
                $boxsetid = -1;
            }
            $sql = "select * from albums where folder = '$escfolder'";
            $result = mysqli_query($link, $sql);

            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into albums (artistid, released, title, formatid, genreid, folder, imagefilename, checked, statusid, isboxset, boxsetid, boxsetindex, lastmodified)"
                        . " values ($albumartistid, $released, '$escalbumtitle', $formatid, $genreid, '$escfolder', '$escimagefilename', false, $statusid, false, $boxsetid, $boxsetindex, $albummodified)";
                mysqli_query($link, $sqli);
                $albumid = mysqli_insert_id($link);
                echo "insert " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
            } else {
                $row2 = mysqli_fetch_assoc($result);
                $albumid = $row2["Id"];
                if ($row2["ArtistId"] != $albumartistid or $row2["Released"] != $released or $row2["Title"] != $albumtitle or
                        $row2["FormatId"] != $formatid or $row2["GenreId"] != $genreid or $row2["StatusId"] != $statusid) {
                    $sqlu = "update albums set artistid = $albumartistid, released = $released, title = '$escalbumtitle',
                                               formatid = $formatid, genreid = $genreid, statusid = $statusid
                             where id = $albumid";
                    mysqli_query($link, $sqlu);
                    echo "update " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                }
                if ($row2["Folder"] != $folder or $row2["ImageFileName"] != $imagefilename) {
                    $sqlu = "update albums set folder = '$escfolder', imagefilename = '$escimagefilename' where id = $albumid";
                    mysqli_query($link, $sqlu);
                    echo "update folder or image filename " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                }
                if ($row2["BoxsetId"] != $boxsetid or $row2["BoxsetIndex"] != $boxsetindex) {
                    $sqlu = "update albums set boxsetid = $boxsetid, boxsetindex = $boxsetindex where id = $albumid";
                    mysqli_query($link, $sqlu);
                    echo "update boxset " . $albumartist . " - " . $released . " - " . $albumtitle . "\r\n";
                }
                $time = strtotime($row2["LastModified"]);
                $lastmodified = date("Ymd", $time);
                if ($lastmodified != $albummodified) {
                    $sqlu = "update albums set lastmodified = $albummodified where id = $albumid";
                    mysqli_query($link, $sqlu);
                }
            }
        }

        // discs
        if ($folder != $folderprev or $discno != $discnoprev) {
            $escdisctitle = mysqli_real_escape_string($link, $disctitle);
            $sql = "select * from discs where albumid = $albumid and discno = $discno";
            $result = mysqli_query($link, $sql);
            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into discs (albumid, discno, title) values ($albumid, $discno, '$escdisctitle')";
                mysqli_query($link, $sqli);
                //echo "insert " . $albumartist . " - " . $released . " - " . $albumtitle . " - " . $disctitle . "\r\n";
            } else {
                $row2 = mysqli_fetch_assoc($result);
                if ($row2["Title"] != $disctitle) {
                    $sqlu = "update discs set title = '$escdisctitle' where albumid = $albumid and discno = $discno";
                    mysqli_query($link, $sqlu);
                    echo "update " . $albumartist . " - " . $released . " - " . $albumtitle . " - " . $disctitle . "\r\n";
                }
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
                $row2 = mysqli_fetch_assoc($result);
                $artistid = $row2["Id"];
                if ($row2["Letter"] != $artistletter) {
                    $sqlu = "update artists set letter = '$escartistletter' where id = $artistid";
                    mysqli_query($link, $sqlu);
                    echo "update " . $artistletter . " - " . $artist . "\r\n";
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

        //echo mysqli_affected_rows($link) . " " . $track . " " . $tracktitle . "\r\n";
        if (mysqli_affected_rows($link) == 0) {
            $sqli = "insert into tracks (albumid, discno, track, title, artistid, playingtime, audiobitrate, audiobitratemode, filename, lastmodified)
                            values ($albumid, $discno, $track, '$esctracktitle', $artistid, $playingtime, $bitrate, '$bitratemode', '$escfilename', $trackmodified)";
            mysqli_query($link, $sqli);
        } else {
            $row2 = mysqli_fetch_assoc($result);
            if ($row2["AlbumId"] != $albumid or $row2["DiscNo"] != $discno or $row2["Track"] != $track or
                    $row2["Title"] != $tracktitle or $row2["ArtistId"] != $artistid or $row2["PlayingTime"] != $playingtime or
                    $row2["AudioBitrate"] != $bitrate or $row2["AudioBitrateMode"] != $bitratemode or $row2["FileName"] != $filename) {
                $sqlu = "update tracks set albumid = $albumid, discno = $discno, track = $track,
                                           title = '$esctracktitle', artistid = $artistid, playingtime = $playingtime,
                                           audiobitrate = $bitrate, audiobitratemode = '$bitratemode', filename = '$escfilename'
                where albumid = $albumid and discno = $discno and track = $track";
                //where albumid = $albumid and filename = '$escfilename'";
                mysqli_query($link, $sqlu);
                echo "update " . $albumartist . " - " . $released . " - " . $albumtitle . " - " . $discno . "-" . $track . " " . $tracktitle . "\r\n";
                if ($row2["PlayingTime"] != $playingtime) {
                    echo $row2["PlayingTime"] . " " . " " . $playingtime . "\r\n";
                }
            }
            $time = strtotime($row2["LastModified"]);
            $lastmodified = date("Ymd", $time);
            if ($lastmodified != $trackmodified) {
                $sqlu = "update tracks set lastmodified = $trackmodified where albumid = $albumid and discno = $discno and track = $track";
                mysqli_query($link, $sqlu);
            }
        }

        $folderprev = $folder;
        $discnoprev = $discno;
    }
}

$link = mysqli_connect($server, $username, $password, $database);
$dsn = "odbc:driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=C:\\Users\\Ruud\\Documenten\\Hobby\\Muziek\\Verzameling\\MP3.mdb;charset=utf16le";
$pdolink = new PDO($dsn);

$dir = "M:\\Music";
//$dir = "M:\\Music\Populair\PQRS\Soulwax";

read_files($dir);

mysqli_close($link);
?>
