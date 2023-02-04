<html>

    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>
        <base href="https://www.musixmatch.com/" target="_blank">
        <title>Get the lyrics!</title>

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
                visibility      : hidden;
            }
            input {
                width           : 250px;
            }
            #textsep {
                width           : 100px;
                text-align      : center;
                font-size       : 14pt;
            }
            .search {
                display         : none;
                white-space     : nowrap;
            }
            .shownot, .variable {
                display         : none;
            }
            #songtext {
                border          : 0px solid black;
            }
            p:not([class=search]) {
                border          : 1px solid black;
                font-size       : 14px;
                background      : #B3CEB3;
                padding         : 3px 5px 3px 5px;
            }
            p[class='mxm-lyrics__content '] {
                border          : 0px solid black;
                background      : white;
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

                $("#search").keydown(function (event) {
                    if (event.which === 13) {
                        $("#search").submit();
                    }
                });

                $("h1").css("font-size", "14pt");
                $("h2").css("font-size", "10pt");

                $("th").contents().unwrap().wrap("<table class='headertable'><tr><td class='headertd'></td></tr></table>");

                $("body").css("visibility", "visible");

                if (parent.length === 0) {
                    $("body").css("margin", "20px");
                    $(".search").css("display", "block");
                }

                parent.$.initFrame($("#response", parent.document));

            });
        </script>

        <?php
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
                echo "<b><a href='" . $config['url'] . "'>Original page</a></b>";
            } else {
                echo "<h3>404 Page not found!</h3>";
                $error_shown = true;
                echo '<script>$(".search").css("display", "block")</script>';
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
                            echo '<div id="songtext">';
                            foreach ($grab->html[0] as $html2) {
                                echo $html2;
                            }
                            echo '</div>';
                            break;
                        }
                    }
                }

                if (!$error) {
                    echo '<div id="textsep">&#1161;</div>';
                    echo '<br/>';
                }
            }

            if (!$error_shown and $error) {
                echo "<h3>404 Page not found!</h3>";
                echo '<script>$(".search").css("display", "block")</script>';
            }
        }
        ?>

        <form class="search" id="search" action="http://<?= $script ?>" method="post" target="_self">
            <label for="artist">Artist: </label>
            <input type="text" name="artist" value="<?= $artist ?>" autofocus="autofocus">
            &nbsp;&nbsp;
            <label for="song">Song: </label>
            <input type="text" name="song" value="<?= $song ?>">
            <input class="shownot" type="text" name="showformiffound" value="<?= $showformiffound ?>">
        </form>
        <p class="search"></p>

        <?php
        $artistsong = $artist . " " . $song;
        $artistsong = str_replace("/", "%2F", $artistsong);
        $artistsong = str_replace("\\", "%5C", $artistsong);
        $artistsong = str_replace("#", "", $artistsong);
        $artistsong = str_replace("%", "", $artistsong);
        $artistsong = str_replace('"', "%22", $artistsong);
        $artistsong = str_replace("'", "%27", $artistsong);
        $artistsong = str_replace(" ", "%20", $artistsong);
        show_form($artistsong);
        ?>

    </body>

</html>