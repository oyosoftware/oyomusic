<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);

function get_files_count($path) {
    global $servername;
    if ($servername !== null) {
        $files = 0;
        $iter = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

        foreach ($iter as $fileinfo) {
            if ($fileinfo->isFile() && strtolower($fileinfo->getExtension()) === 'jpg') {
                $files++;
            }
        }
        return $files;
    }
}

function clean_artist_images($dir) {
    global $link, $counter, $files, $servername;

    $iter = new DirectoryIterator($dir);

    foreach ($iter as $item) {
        $ext = $item->getExtension();
        if ($ext === 'jpg') {
            $filename = $item->getFilename();
            $escfilename = mysqli_real_escape_string($link, $filename);
            $length = mb_strlen($escfilename) - 4;
            $name = mb_substr($escfilename, 0, $length);
            $sql = "select * from artists where name = '$name'";
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($result);
            $counter++;
            if (mysqli_affected_rows($link) !== 0 and $name === $row["Name"]) {
                if (($counter % 10000) === 0) {
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

                $collectpath = $dir . "/" . $filename;
                if ($servername !== null) {
                    $response = array('pathname' => $collectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }

                if ($servername !== null) {
                    $imagecollectpath = $collectpath;
                    $response = array('imagepathname' => $imagecollectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            } else {
                $message = "delete image $filename";
                if ($servername !== null) {
                    $response = array('message' => $message);
                    $json = json_encode($response);
                    echo $json . ",\n";
                } else {
                    echo "$message" . "\r\n";
                }
                $collectpath = $dir . "/" . $filename;
                unlink($collectpath);
                $files--;
                if ($servername !== null) {
                    $response = array('files' => $files);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
                $counter--;
            }
        }
    }
}

function clean_album_images($dir) {
    global $link, $counter, $files, $servername;

    $iter = new DirectoryIterator($dir);

    foreach ($iter as $item) {
        $ext = $item->getExtension();
        if ($ext === 'jpg') {
            $filename = $item->getFilename();
            $escfilename = mysqli_real_escape_string($link, $filename);
            $sql = "select * from albums where imagefilename = '$escfilename'";
            $result = mysqli_query($link, $sql);
            $row = mysqli_fetch_assoc($result);
            $counter++;
            if (mysqli_affected_rows($link) !== 0 and $filename === $row["ImageFileName"]) {
                if (($counter % 10000) === 0) {
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

                $collectpath = $dir . "/" . $filename;
                if ($servername !== null) {
                    $response = array('pathname' => $collectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }

                if ($servername !== null) {
                    $imagecollectpath = $collectpath;
                    $response = array('imagepathname' => $imagecollectpath);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
            } else {
                $message = "delete image $filename";
                if ($servername !== null) {
                    $response = array('message' => $message);
                    $json = json_encode($response);
                    echo $json . ",\n";
                } else {
                    echo "$message" . "\r\n";
                }
                $collectpath = $dir . "/" . $filename;
                unlink($collectpath);
                $files--;
                if ($servername !== null) {
                    $response = array('files' => $files);
                    $json = json_encode($response);
                    echo $json . ",\n";
                }
                $counter--;
            }
        }
    }
}

$servername = $argv[1];
$documentroot = $argv[2];
$link = mysqli_connect($server, $username, $password, $database);

$base = "../" . $imagepathartists;
$base = str_ireplace("\\", "/", $base);
$path = $base;

if (file_exists($path)) {
    $jobstart = time();

    $files = get_files_count($base);
    if ($servername !== null) {
        $response = array('files' => $files);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $message = "Clean Images";
    if ($servername === null) {
        echo "$message" . "\r\n";
    }

    $message = "job begins for image path $path " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $counter = 0;
    clean_artist_images($base);

    $message = "counter: $counter " . date('H:i:s');
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

$base = "../" . $imagepath;
$base = str_ireplace("\\", "/", $base);
$path = $base;

if (file_exists($path)) {
    $jobstart = time();

    $files = get_files_count($base);
    if ($servername !== null) {
        $response = array('files' => $files);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $message = "job begins for image path $path " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $counter = 0;
    clean_album_images($base);

    $message = "counter: $counter " . date('H:i:s');
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

$base = "../" . $imagepaththumbs;
$base = str_ireplace("\\", "/", $base);
$path = $base;

if (file_exists($path)) {
    $files = get_files_count($base);
    if ($servername !== null) {
        $response = array('files' => $files);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $message = "job begins for image path $path " . date('H:i:s');
    if ($servername === null) {
        echo "$message" . "\r\n";
    } else {
        $response = array('message' => $message);
        $json = json_encode($response);
        echo $json . ",\n";
    }

    $counter = 0;
    clean_album_images($base);

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
