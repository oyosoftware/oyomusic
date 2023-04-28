<?php

error_reporting(E_ERROR);
require_once('../settings.inc');
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

function get_records($dir) {
    global $servername, $pdolink;
    if ($servername !== null) {
        $dir = access_escape_string($dir);
        $sql = "select count(*) as records from track inner join audiofilelink on track.trackid = audiofilelink.trackid where folder like '$dir%'";
        $pdoresult = $pdolink->query($sql);
        $row = $pdoresult->fetch();
        $records = $row["records"];
        return $records;
    }
}

function read_files($dir) {
    global $link, $pdolink, $catraxxaudiosource, $counter, $servername, $records;
    $dir = access_escape_string($dir);

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
        $folder = str_replace($catraxxaudiosource, "", $folder);
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

        // Write to database

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

                if ($servername !== null) {
                    $response = array('name' => htmlspecialchars($boxsetartist));
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
                $escboxsetartist = mysqli_real_escape_string($link, $boxsetartist);
                $escboxsetartistletter = mysqli_real_escape_string($link, $boxsetartistletter);
                $sql = "select * from artists where name like '$escboxsetartist%'";
                $result = mysqli_query($link, $sql);
                while ($row2 = mysqli_fetch_assoc($result)) {
                    $subboxsetartist = mb_substr($row2["Name"], 0, 100);
                    if ($boxsetartist === $subboxsetartist) {
                        $boxsetartistid = $row2["Id"];
                        $compboxsetartist = mb_substr($row2["Name"], 0, 100);
                        $compboxsetartistletter = $row2["Letter"];
                        $compboxsetcountryid = $row2["CountryId"];
                    }
                }
                if (mysqli_affected_rows($link) == 0) {
                    $sqli = "insert into artists (name, letter, countryid) values ('$escboxsetartist', '$escboxsetartistletter', $boxsetcountryid)";
                    mysqli_query($link, $sqli);
                    $boxsetartistid = mysqli_insert_id($link);
                    $message = "insert $boxsetartist";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                } else {
                    if ($compboxsetartist != $boxsetartist or $compboxsetartistletter != $boxsetartistletter or $compboxsetcountryid != $boxsetcountryid) {
                        $sqlu = "update artists set name = '$escboxsetartist', letter = '$escboxsetartistletter', countryid = $boxsetcountryid where id = $boxsetartistid";
                        mysqli_query($link, $sqlu);
                        $message = "update $boxsetartistletter - $boxsetartist - $boxsetcountry";
                        if ($servername === null) {
                            echo $message . "\r\n";
                        } else {
                            $response = array('message' => htmlspecialchars($message));
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
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

                if ($servername !== null) {
                    $response = array('title' => htmlspecialchars($boxsettitle));
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
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
                    $sqli = "insert into albums (artistid, released, title, formatid, genreid, folder, imagefilename, checked, statusid, isboxset, boxsetid, boxsetindex, lastmodified)
                             values ($boxsetartistid, $boxsetreleased, '$escboxsettitle', $boxsetformatid, $boxsetgenreid, '$escboxsetfolder', '$escboxsetimagefilename', false, $boxsetstatusid, true, -1, -1, $boxsetmodified)";
                    mysqli_query($link, $sqli);
                    $boxsetid = mysqli_insert_id($link);
                    $message = "insert $boxsetartist - $boxsetreleased - $boxsettitle";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                } else {
                    $row2 = mysqli_fetch_assoc($result);
                    $boxsetid = $row2["Id"];
                    if ($row2["ArtistId"] != $boxsetartistid or $row2["Released"] != $boxsetreleased or $row2["Title"] != $boxsettitle or
                        $row2["FormatId"] != $boxsetformatid or $row2["GenreId"] != $boxsetgenreid or $row2["StatusId"] != $boxsetstatusid) {
                        $sqlu = "update albums set artistid = $boxsetartistid, released = $boxsetreleased, title = '$escboxsettitle',
                                                   formatid = $boxsetformatid, genreid = $boxsetgenreid, statusid = $boxsetstatusid
                                 where id = $boxsetid";
                        mysqli_query($link, $sqlu);
                        $message = "update $boxsetartist - $boxsetreleased - $boxsettitle";
                        if ($servername === null) {
                            echo $message . "\r\n";
                        } else {
                            $response = array('message' => htmlspecialchars($message));
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
                    }
                    if ($row2["Folder"] != $boxsetfolder or $row2["ImageFileName"] != $boxsetimagefilename) {
                        $sqlu = "update albums set folder = '$escboxsetfolder', imagefilename = '$escboxsetimagefilename' where id = $boxsetid";
                        mysqli_query($link, $sqlu);
                        $message = "update folder or image filename $boxsetartist - $boxsetreleased - $boxsettitle";
                        if ($servername === null) {
                            echo $message . "\r\n";
                        } else {
                            $response = array('message' => htmlspecialchars($message));
                            $json = json_encode($response);
                            echo $json . ",\n";
                        }
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
            if ($servername !== null) {
                $response = array('name' => htmlspecialchars($albumartist));
                $json = json_encode($response);
                echo $json . ",\n";
            }
            $escalbumartist = mysqli_real_escape_string($link, $albumartist);
            $escalbumartistletter = mysqli_real_escape_string($link, $albumartistletter);
            $sql = "select * from artists where name like '$escalbumartist%'";
            $result = mysqli_query($link, $sql);
            while ($row2 = mysqli_fetch_assoc($result)) {
                $subalbumartist = mb_substr($row2["Name"], 0, 100);
                if ($albumartist === $subalbumartist) {
                    $albumartistid = $row2["Id"];
                    $compalbumartist = mb_substr($row2["Name"], 0, 100);
                    $compalbumartistletter = $row2["Letter"];
                    $compcountryid = $row2["CountryId"];
                }
            }
            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into artists (name, letter, countryid) values ('$escalbumartist', '$escalbumartistletter', $countryid)";
                mysqli_query($link, $sqli);
                $albumartistid = mysqli_insert_id($link);
                $message = "insert $albumartist";
                if ($servername === null) {
                    echo $message . "\r\n";
                } else {
                    $response = array('message' => htmlspecialchars($message));
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            } else {
                if ($compalbumartist != $albumartist or $compalbumartistletter != $albumartistletter or $compcountryid != $countryid) {
                    $sqlu = "update artists set name = '$escalbumartist', letter = '$escalbumartistletter', countryid = $countryid where id = $albumartistid";
                    mysqli_query($link, $sqlu);
                    $message = "update $albumartistletter - $albumartist - $country";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
            }
        }

        // format

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

        // genre

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

        // album

        if ($folder != $folderprev) {
            if ($servername !== null) {
                $response = array('title' => htmlspecialchars($albumtitle));
                $json = json_encode($response);
                echo $json . ",\n";
            }
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
                $sqli = "insert into albums (artistid, released, title, formatid, genreid, folder, imagefilename, checked, statusid, isboxset, boxsetid, boxsetindex, lastmodified)
                         values ($albumartistid, $released, '$escalbumtitle', $formatid, $genreid, '$escfolder', '$escimagefilename', false, $statusid, false, $boxsetid, $boxsetindex, $albummodified)";
                mysqli_query($link, $sqli);
                $albumid = mysqli_insert_id($link);
                $message = "insert $albumartist - $released - $albumtitle";
                if ($servername === null) {
                    echo $message . "\r\n";
                } else {
                    $response = array('message' => htmlspecialchars($message));
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            } else {
                $row2 = mysqli_fetch_assoc($result);
                $albumid = $row2["Id"];
                if ($row2["ArtistId"] != $albumartistid or $row2["Released"] != $released or $row2["Title"] != $albumtitle or
                    $row2["FormatId"] != $formatid or $row2["GenreId"] != $genreid or $row2["StatusId"] != $statusid) {
                    $sqlu = "update albums set artistid = $albumartistid, released = $released, title = '$escalbumtitle',
                                               formatid = $formatid, genreid = $genreid, statusid = $statusid
                             where id = $albumid";
                    mysqli_query($link, $sqlu);
                    $message = "update $albumartist - $released - $albumtitle";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
                if ($row2["Folder"] != $folder or $row2["ImageFileName"] != $imagefilename) {
                    $sqlu = "update albums set folder = '$escfolder', imagefilename = '$escimagefilename' where id = $albumid";
                    mysqli_query($link, $sqlu);
                    $message = "update folder or image filename $albumartist - $released - $albumtitle";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
                if ($row2["BoxsetId"] != $boxsetid or $row2["BoxsetIndex"] != $boxsetindex) {
                    $sqlu = "update albums set boxsetid = $boxsetid, boxsetindex = $boxsetindex where id = $albumid";
                    mysqli_query($link, $sqlu);
                    $message = "update boxset $albumartist - $released - $albumtitle";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
                $time = strtotime($row2["LastModified"]);
                $lastmodified = date("Ymd", $time);
                if ($lastmodified != $albummodified) {
                    $sqlu = "update albums set lastmodified = $albummodified where id = $albumid";
                    mysqli_query($link, $sqlu);
                }
            }
        }

        // disc

        if ($folder != $folderprev or $discno != $discnoprev) {
            $escdisctitle = mysqli_real_escape_string($link, $disctitle);
            $sql = "select * from discs where albumid = $albumid and discno = $discno";
            $result = mysqli_query($link, $sql);
            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into discs (albumid, discno, title) values ($albumid, $discno, '$escdisctitle')";
                mysqli_query($link, $sqli);
            } else {
                $row2 = mysqli_fetch_assoc($result);
                if ($row2["Title"] != $disctitle) {
                    $sqlu = "update discs set title = '$escdisctitle' where albumid = $albumid and discno = $discno";
                    mysqli_query($link, $sqlu);
                    $message = "update $albumartist - $released - $albumtitle - $disctitle";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
            }
        }

        // artist

        $escartist = mysqli_real_escape_string($link, $artist);
        $escartistletter = mysqli_real_escape_string($link, $artistletter);
        if ($artist <> '') {
            $sql = "select * from artists where name like '$escartist%'";
            $result = mysqli_query($link, $sql);
            while ($row2 = mysqli_fetch_assoc($result)) {
                $subartist = mb_substr($row2["Name"], 0, 100);
                if ($artist === $subartist) {
                    $artistid = $row2["Id"];
                    $compartist = mb_substr($row2["Name"], 0, 100);
                    $compartistletter = $row2["Letter"];
                }
            }
            if (mysqli_affected_rows($link) == 0) {
                $sqli = "insert into artists (name, letter, countryid) values ('$escartist', '$escartistletter', -1)";
                mysqli_query($link, $sqli);
                $artistid = mysqli_insert_id($link);
            } else {
                if ($compartist != $artist or $compartistletter != $artistletter) {
                    $sqlu = "update artists set name = '$escartist', letter = '$escartistletter' where id = $artistid";
                    mysqli_query($link, $sqlu);
                    $message = "update $artistletter - $artist";
                    if ($servername === null) {
                        echo $message . "\r\n";
                    } else {
                        $response = array('message' => htmlspecialchars($message));
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
            }
        } else {
            $artistid = -1;
        }

        // track

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

        if (mysqli_affected_rows($link) == 0) {
            $sqli = "insert into tracks (albumid, discno, track, title, artistid, playingtime, audiobitrate, audiobitratemode, filename, lastmodified)
                     values ($albumid, $discno, $track, '$esctracktitle', $artistid, $playingtime, $bitrate, '$bitratemode', '$escfilename', $trackmodified)";
            mysqli_query($link, $sqli);
        } else {
            $row2 = mysqli_fetch_assoc($result);
            if ($row2["AlbumId"] != $albumid or $row2["DiscNo"] != $discno or $row2["Track"] != $track or
                $row2["Title"] != $tracktitle or $row2["ArtistId"] != $artistid or $row2["PlayingTime"] != $playingtime or
                $row2["AudioBitrate"] != $bitrate or $row2["AudioBitrateMode"] != $bitratemode) {
                $sqlu = "update tracks set albumid = $albumid, discno = $discno, track = $track,
                                           title = '$esctracktitle', artistid = $artistid, playingtime = $playingtime,
                                           audiobitrate = $bitrate, audiobitratemode = '$bitratemode'
                         where albumid = $albumid and discno = $discno and track = $track";
                mysqli_query($link, $sqlu);
                $message = "update $albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                if ($servername === null) {
                    echo $message . "\r\n";
                } else {
                    $response = array('message' => htmlspecialchars($message));
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            }
            if ($row2["FileName"] != $filename) {
                $sqlu = "update tracks set filename = '$escfilename'
                         where albumid = $albumid and discno = $discno and track = $track";
                mysqli_query($link, $sqlu);
                $message = "update filename $albumartist - $released - $albumtitle - $discno-$track - $tracktitle";
                if ($servername === null) {
                    echo $message . "\r\n";
                } else {
                    $response = array('message' => htmlspecialchars($message));
                    $json = json_encode($response);
                    echo $json . ",\n";
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

$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);
$dsn = "odbc:driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=" . $catraxxdatabasepath . ";charset=utf16le";
$pdolink = new PDO($dsn);

$base = "";
//$base = "\Populair\TUV";
$path = $catraxxaudiosource . $base;

$jobstart = time();

$records = get_records($path);
if ($servername !== null) {
    $response = array('records' => $records);
    $json = json_encode($response);
    echo $json . ",\n";
}

$message = "Read CATraxx";
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

mysqli_close($link);
?>
