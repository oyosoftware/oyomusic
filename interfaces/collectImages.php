<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../include/date_time.php');

function get_records($path) {
    global $servername, $link;
    if ($servername !== null) {
        $escpath = mysqli_real_escape_string($link, $path);
        $sql = "select count(*) as records from albums where folder like '$escpath%'";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $records = $row["records"];
        return $records;
    }
}

function collect_images($dir) {
    global $link, $audiosource, $counter, $imagepath, $imagepaththumbs, $imagepathartists, $servername;

    $escdir = mysqli_real_escape_string($link, $dir);
    $sql = "select * from albums where folder like '$escdir%' order by folder";
    $result = mysqli_query($link, $sql);

    $previousartistid = 0;

    while ($row = mysqli_fetch_assoc($result)) {
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

        $artistid = $row["ArtistId"];
        $albumtitle = $row["Title"];
        $folder = $row["Folder"];

        // artist

        if ($artistid <> $previousartistid) {
            $sqla = "select * from artists where id = $artistid";
            $resulta = mysqli_query($link, $sqla);
            $rowa = mysqli_fetch_assoc($resulta);
            $symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|");
            $albumartist = str_replace($symbols, "_", $rowa["Name"]);

            if ($servername !== null) {
                $response = array('name' => $albumartist);
                $json = json_encode($response);
                echo $json . ",\n";
            }

            // artist image

            $path = $audiosource . mb_substr($folder, 0, mb_strrpos($folder, '/')) . "/folder.jpg";
            if (file_exists($path)) {
                $pathdate = date('YmdHis', filemtime($path));

                $collectpath = "../" . $imagepathartists . "/" . $albumartist . ".jpg";
                if ($servername !== null) {
                    $response = array('pathname' => $collectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }

                if (file_exists($collectpath)) {
                    $collectpathdate = date('YmdHis', filemtime($collectpath));
                }
                $makeimage = !file_exists($collectpath) || $pathdate > $collectpathdate;

                if ($makeimage) {
                    if (!file_exists($collectpath)) {
                        $command = "insert";
                    } else {
                        $command = "update";
                    }
                    $image1 = imagecreatefromjpeg($path);
                    imagejpeg($image1, $collectpath, 100);
                }

                if ($servername !== null) {
                    $imagecollectpath = str_replace("%", "%25", $collectpath);
                    $imagecollectpath = str_replace("#", "%23", $imagecollectpath);
                    $response = array('imagepathname' => $imagecollectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }

                if ($makeimage) {
                    if ($servername !== null) {
                        $response = array('newimagepathname' => $imagecollectpath);
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }

                if ($makeimage) {
                    $message = "$command image $collectpath";
                    if ($servername === null) {
                        echo "$message" . "\r\n";
                    } else {
                        $response = array('message' => $message);
                        $json = json_encode($response);
                        echo $json . ",\n";
                    }
                }
            }
        }

        // album

        if ($servername !== null) {
            $response = array('title' => $albumtitle);
            $json = json_encode($response);
            echo $json . ",\n";
        }

        // album image

        $path = $audiosource . $folder . "/folder.jpg";
        if (file_exists($path)) {
            $pathdate = date('YmdHis', filemtime($path));
            $lastfolder = mb_substr(mb_strrchr($folder, '/'), 1);

            $collectpath = "../" . $imagepath . "/" . $albumartist . " - " . $lastfolder . ".jpg";
            if ($servername !== null) {
                $response = array('pathname' => $collectpath);
                $json = json_encode($response);
                echo $json . ",\n";
            }

            if (file_exists($collectpath)) {
                $collectpathdate = date('YmdHis', filemtime($collectpath));
            }
            $makeimage = !file_exists($collectpath) || $pathdate > $collectpathdate;

            if ($makeimage) {
                if (!file_exists($collectpath)) {
                    $command = "insert";
                } else {
                    $command = "update";
                }
                $image1 = imagecreatefromjpeg($path);
                imagejpeg($image1, $collectpath, 100);
            }

            if ($servername !== null) {
                $imagecollectpath = str_replace("%", "%25", $collectpath);
                $imagecollectpath = str_replace("#", "%23", $imagecollectpath);
                $response = array('imagepathname' => $imagecollectpath);
                $json = json_encode($response);
                echo $json . ",\n";
            }

            if ($makeimage) {
                if ($servername !== null) {
                    $response = array('newimagepathname' => $imagecollectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            }

            if ($makeimage) {
                $message = "$command image $collectpath";
                if ($servername === null) {
                    echo "$message" . "\r\n";
                } else {
                    $response = array('message' => $message);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            }

            // album thumb image

            $collectpath = "../" . $imagepaththumbs . "/" . $albumartist . " - " . $lastfolder . ".jpg";
            if (file_exists($collectpath)) {
                $collectpathdate = date('YmdHis', filemtime($collectpath));
            }
            $makeimage = !file_exists($collectpath) || $pathdate > $collectpathdate;

            if ($makeimage) {
                $image1 = imagecreatefromjpeg($path);
                $image2 = imagescale($image1, 50);
                imagejpeg($image2, $collectpath, 100);
            }
        }

        $previousartistid = $artistid;
    }
}

$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);

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

    $message = "Collect Images";
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
    collect_images($base);

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
    $message = "Error: path $path doesn't exist" . $dirlist[2];
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
