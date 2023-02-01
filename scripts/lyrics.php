<?php
error_reporting(E_ERROR);

$artist = filter_input(INPUT_GET, "artist");
$artist = preg_replace('/\s+/', ' ', $artist);
$song = filter_input(INPUT_GET, "song");
$song = preg_replace('/\s+/', ' ', $song);
$artistsong = $artist . " - " . $song;
$referer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
?>
<html>

    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?= $artist ?> - <?= $song ?></title>

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
            ::-webkit-scrollbar-thumb {
                background          : white;
                border              : 2px solid black;
                border-radius       : 4px;
            }
            ::-webkit-scrollbar-thumb:vertical {
                border-right        : 1px solid black;
            }
            ::-webkit-scrollbar-thumb:horizontal {
                border-bottom       : 1px solid black;
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
            .titletable {
                border            : 1px solid black;
                border-spacing    : 1px;
                width             : 100%;
                margin            : 0px;
                padding           : 0px;
            }
            .titlevalue {
                background-color  : #B3CEB3;
                color             : black;
                font-weight       : bold;
                font-size         : 14pt;
                padding           : 8px;
            }
            #response {
                border            : 0px solid black;
                width             : 100%;
                margin            : 0px;
                padding           : 0px;
            }
            #search {
                display           : none;
            }
            .backcell {
                font-size         : 10pt;
            }
        </style>

        <script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>

        <script>
            $(document).ready(function () {

                $.initFrame = function (response) {
                    try {
                        if (response.contents())
                        {
                            response.outerHeight(response.contents().find("body").outerHeight());
                            response.contents().find("input[name='artist']").focus();
                            parent.$.initWindow();
                        }
                    } catch (error) {
                    }
                };

                $.addWrapper = function (sel) {
                    sel.each(function () {
                        $(this).wrap('<table class="wrappertable"><tr><td class="wrappertd"></td></tr></table>');
                    });
                    $(".wrappertable").css({
                        "margin": "0px",
                        "padding": "0px",
                        "padding-right": "8px",
                        "border-spacing": "0px",
                        "width": "100%"
                    });
                    $(".wrappertd").css({
                        "margin": "0px",
                        "padding": "0px",
                        "width": "100%"
                    });
                };

                $(window).resize(function () {
                    $("#response").outerHeight($("#response").contents().find("#songtext").outerHeight());
                });

                $.addWrapper($(".titletable, #response"));



                $("#search").submit();

            });
        </script>

    </head>

    <body>
        <table>
            <tr>
                <td class="backcell"><a href="<?= $referer ?>">Back</a></td>
            </tr>
        </table>
        <p></p>

        <table class="titletable">
            <tr>
                <td class="titlevalue"><?= $artistsong ?></td>
            </tr>
        </table>
        <p></p>

        <form id="search" action="getLyricsMM.php" method="post" target="response">
            <input type="text" name="artist" value="<?= $artist ?>" autofocus="autofocus">
            <input type="text" name="song" value="<?= $song ?>">
            <input type="text" name="showformiffound" value="no">
        </form>

        <iframe id="response" name="response" scrolling="no"></iframe>

    </body>

</html>