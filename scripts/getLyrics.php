<?php
error_reporting(E_ERROR);

$artist = filter_input(INPUT_GET, "artist");
$artist = preg_replace('/\s+/', ' ', $artist);
$song = filter_input(INPUT_GET, "song");
$song = preg_replace('/\s+/', ' ', $song);
$artistsong = $artist . " - " . $song;
$referer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
if (is_null($referer)) {
    $referer = filter_input(INPUT_ENV, 'HTTP_REFERER');
}
?>

<!DOCTYPE html>

<html>

    <head>

        <title><?= $artist ?> - <?= $song ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>

        <style type="text/css">
            * {
                box-sizing        : border-box;
            }
            body {
                font-family       : Arial, Verdana, Helvetica, sans-serif;
                font-size         : 10pt;
                margin            : 0px;
                padding           : 8px;
                padding-right     : 0px;
            }
            ::-webkit-scrollbar {
                height              : 10px;
                width               : 10px;
            }
            ::-webkit-scrollbar-corner {
                background-color    : transparent;
            }
            ::-webkit-scrollbar-thumb {
                background          : white;
                border              : 2px solid black;
                border-radius       : 5px;
            }
            A:link {
                text-decoration   : none;
            }
            A:visited {
                text-decoration   : none;
            }
            A:hover {
                text-decoration   : underline;
            }
            .back {
                font-size           : 13.33px;
                height              : 25px;
                vertical-align      : top;
            }
            .wrapper {
                padding-right       : 8px;
                width               : 100%;
            }
            .header {
                border              : 1px solid black;
                width               : 100%;
                min-width           : 254px;
                border-spacing      : 1px;
            }
            .headertitle {
                background-color    : #B3CEB3;
                color               : black;
                font-weight         : bold;
                font-size           : 18.66px;
                padding             : 8px;
            }
            iframe[name=response] {
                border            : 0px solid black;
                width             : 100%;
                margin            : 0px;
                padding           : 0px;
            }
            .search {
                display           : none;
            }
        </style>

    </head>

    <body>

        <script>
            $(document).ready(function () {
                $(".search").submit();
            });
        </script>

        <div class="back"><a class="backlink" href="<?= $referer ?>">Back</a></div>

        <div class="wrapper">
            <div class="header">
                <div class="headertitle"><?= $artistsong ?></div>
            </div>
        </div>

        <form class="search" action="getLyricsMM.php" method="post" target="response">
            <input type="text" name="artist" value="<?= $artist ?>">
            <input type="text" name="song" value="<?= $song ?>">
            <input type="text" name="showformiffound" value="no">
        </form>

        <div class="wrapper">
            <iframe name="response"></iframe>
        </div>

    </body>

</html>