<?php
error_reporting(E_ERROR);

$artist = ucwords(filter_input(INPUT_POST, "artist"));
$artist = preg_replace('/\s+/', ' ', $artist);
$song = ucwords(filter_input(INPUT_POST, "song"));
$song = preg_replace('/\s+/', ' ', $song);
$showformiffound = filter_input(INPUT_POST, "showformiffound");
if ($showformiffound === "") {
    $showformiffound = "yes";
}
$script = filter_input(INPUT_SERVER, 'HTTP_HOST') . filter_input(INPUT_SERVER, 'SCRIPT_NAME');
if ($script === "") {
    $script = filter_input(INPUT_ENV, 'HTTP_HOST') . filter_input(INPUT_ENV, 'SCRIPT_NAME');
}
?>

<!DOCTYPE html>

<html>

    <head>

        <title>Get the lyrics!</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>
        <base href="https://www.musixmatch.com/" target="_blank">

        <style>
            *    {
                box-sizing      : border-box;
            }
            body {
                font-family     : Arial, Verdana, Helvetica, sans-serif;
                font-size       : 10pt;
                margin          : 0px;
                padding         : 0px;
                border          : 0px solid black;
            }
            input {
                width           : 250px;
            }
            .textsep {
                width           : 100px;
                text-align      : center;
                font-size       : 14pt;
            }
            .search {
                display         : none;
                white-space     : nowrap;
            }
            input[name=showformiffound] {
                display         : none;
            }
            .lyrics {
                border          : 0px solid black;
                background      : white;
                margin          : 0px;
                white-space     : pre-wrap;
                word-break      : break-word;
            }
            .headertable {
                border          : 1px solid black;
                width           : 100%;
                border-spacing  : 1px;
                font-size       : 14px;
                margin-bottom   : 14px;
            }
            .headertd {
                background      : #B3CEB3;
                padding         : 3px 5px 3px 5px;
            }
        </style>

    </head>

    <body>

        <script>
            $(document).ready(function () {

                $("input").focus(function () {
                    $(this).select();
                });

                setTimeout(function () {
                    $("input[name=artist]").focus();
                }, 0);

                $(".search").keydown(function (event) {
                    if (event.which === 13) {
                        if ($("input[name='artist']").val() !== "" && $("input[name='song']").val() !== "") {
                            $(".search").submit();
                        }
                    }
                });

                if (parent.length === 0) {
                    $("body").css("margin", "20px");
                    $(".search").css("display", "block");
                } else {
                    $("form").prepend("<br/>");
                }

                try {
                    var responseFrame = parent.frames["response"];
                    if (responseFrame) {
                        $("body").css("overflow-y", "hidden");
                        var documentObserver = new ResizeObserver(function () {
                            var height = responseFrame.document.body.scrollHeight;
                            $(responseFrame.frameElement).height(height);
                        });
                        documentObserver.observe(document.documentElement);
                    }
                } catch (error) {
                }
            });
        </script>

        <form class="search" id="search" action="http://<?= $script ?>" method="post" target="_self">
            <label for="artist">Artist: </label>
            <input type="text" name="artist" value="<?= $artist ?>">
            &nbsp;&nbsp;
            <label for="song">Song: </label>
            <input type="text" name="song" value="<?= $song ?>">
            <input type="text" name="showformiffound" value="<?= $showformiffound ?>">
        </form>

        <br/>

        <?php
        $error_shown = false;

        function show_form($artistsong) {
            global $song;
            global $error_shown;

            class grabber {

                var $html = '';

                function grabhtml($url, $start, $end) {

                    $arrContextOptions = array(
                        "ssl" => array(
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                        ),
                    );
                    $file = file_get_contents($url, false, stream_context_create($arrContextOptions));
                    if ($file) {
                        if (preg_match_all("#$start(.*?)$end#s", $file, $match)) {
                            $this->html = $match;
                        }
                    }
                }

            }

            // Check existence of Songtext
            $config['url'] = "https://www.musixmatch.com/search/" . $artistsong;
            $config['start_tag'] = '<h2 class="media-card-title">';
            $config['end_tag'] = '</h2>';

            $grab = new grabber;
            $grab->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

            if ($grab->html) {
                $html = $grab->html[0][0];
                echo "<b><a href='" . $config['url'] . "'>Original page</a></b>\r\n";
            } else {
                echo "<h3>404 Page not found!</h3>\r\n";
                $error_shown = true;
                echo "<script>$('.search').css('display', 'block')</script>\r\n";
            }

            // Songtext
            $config['url'] = "https://www.musixmatch.com/search/" . $artistsong . "/tracks";
            $config['start_tag'] = '<h2 class="media-card-title">';
            $config['end_tag'] = '</h2>';

            $grab = new grabber;
            $grab->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

            $error = true;
            if ($grab->html) {
                sort($grab->html[0]);

                foreach ($grab->html[0] as $html) {

                    preg_match_all("#<span>(.*?)</span>#s", $html, $match);
                    $newsong = ucwords($match[1][0]);
                    $pos = mb_strpos($newsong, $song);
                    //$word = strtok($song, " ");

                    if ($pos !== false and $pos === 0) {

                        preg_match_all("#href=\"(.*?)\"#s", $html, $match);
                        $config['url'] = "https://www.musixmatch.com" . $match[1][0];
                        $config['start_tag'] = '<p class="mxm-lyrics__content ">';
                        $config['end_tag'] = '</p>';

                        $grab = new grabber;
                        $grab->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

                        if ($grab->html) {
                            $error = false;
                            echo "<div class='songtext'><p class='lyrics'>\r\n";
                            foreach ($grab->html[1] as $html2) {
                                $html2 = trim($html2);
                                echo $html2;
                            }
                            echo "</p></div>\r\n";
                            break;
                        }
                    }
                }

                if (!$error) {
                    echo "<br/>\r\n";
                    echo "<div class='textsep'>&#1161;</div>\r\n";
                }
            }

            if (!$error_shown and $error) {
                echo "<h3>404 Page not found!</h3>\r\n";
                echo "<script>$('.search').css('display', 'block')</script>\r\n";
            }
        }

        $artistsong = trim($artist . " " . $song);
        if ($artistsong !== "") {
            $artistsong = str_replace("/", "%2F", $artistsong);
            $artistsong = str_replace("\\", "%5C", $artistsong);
            $artistsong = str_replace("#", "", $artistsong);
            $artistsong = str_replace("%", "", $artistsong);
            $artistsong = str_replace('"', "%22", $artistsong);
            $artistsong = str_replace("'", "%27", $artistsong);
            $artistsong = str_replace(" ", "%20", $artistsong);
            show_form($artistsong);
        }
        ?>

    </body>

</html>