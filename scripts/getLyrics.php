<?php

error_reporting(E_ERROR);

require_once('../include/special_characters.php');

$artist = filter_input(INPUT_GET, "artist");
$song = filter_input(INPUT_GET, "song");

$artistsong = $artist . " " . $song;
$artistsong = preg_replace('/[?&#]/', '', $artistsong);
$artistsong = preg_replace('/\s+/', ' ', $artistsong);
$artistsong = rawurlencode($artistsong);

class grabber {

    function grabhtml($url, $start, $end) {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $file = file_get_contents($url, false, stream_context_create($arrContextOptions));
        if ($file) {
            $match = null;
            $pattern = "{" . $start . "(.*?)" . $end . "}s";
            if (preg_match_all($pattern, $file, $match)) {
                $this->html = $match;
            }
        }
    }

}

$lyrics = new stdClass();
$lyrics->found = false;
$foundbegin = false;

$tracks = true;
getLyrics();

if (is_null($lyrics->content) && $lyrics->empty !== "Instrumental\n") {
    $tracks = false;
    getLyrics();
}

function getLyrics() {
    global $artist, $song, $artistsong, $lyrics, $tracks, $foundbegin;

    if ($tracks) {
        $config['url'] = "https://www.musixmatch.com/search/" . $artistsong . "/tracks";
    } else {
        $config['url'] = "https://www.musixmatch.com/search/" . $artistsong;
    }

    $config['start_tag'] = '<h2 class="media-card-title">';
    $config['end_tag'] = '</h2>';
    $songgrabber = new grabber;
    $songgrabber->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

    $config['start_tag'] = '<h3 class="media-card-subtitle">';
    $config['end_tag'] = '</h3>';
    $artistgrabber = new grabber;
    $artistgrabber->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

    if ($songgrabber->html) {
        $lyrics->page = $config['url'];
    }

    if ($songgrabber->html) {
        $unmark = false;
        again:
        foreach ($songgrabber->html[0] as $key => $html) {
            $match = null;
            $htmla = $artistgrabber->html[1][$key];
            $pattern = "{<span(.*?)><span><a(.*?)>(.*?)</a>}s";
            preg_match_all($pattern, $htmla, $match);
            $foundartist = $match[3][0];

            $searchartist = mb_strtoupper($artist);
            $searchartist = strunacc($searchartist);
            $searchartist = strunmrk($searchartist);
            $searchartist = trim(preg_replace('/\s+/', ' ', $searchartist));

            $foundartist = mb_strtoupper($foundartist);
            $foundartist = strunacc($foundartist);
            $foundartist = strunmrk($foundartist);
            $foundartist = trim(preg_replace('/\s+/', ' ', $foundartist));

            $pos = mb_strpos($foundartist, $searchartist);
            if ($pos === false) {
                continue;
            }

            $match = null;
            $pattern = "{<span>(.*?)</span>}s";
            preg_match_all($pattern, $html, $match);
            $foundsong = $match[1][0];

            if ($foundsong === null) {
                continue;
            }

            $searchsong = mb_strtoupper($song);
            $searchsong = strunacc($searchsong);
            if ($unmark) {
                $searchsong = strunmrk($searchsong);
            }
            $searchsong = trim(preg_replace('/\s+/', ' ', $searchsong));

            $foundsong = mb_strtoupper($foundsong);
            $foundsong = strunacc($foundsong);
            if ($unmark) {
                $foundsong = strunmrk($foundsong);
            }
            $foundsong = trim(preg_replace('/\s+/', ' ', $foundsong));

            if ($foundbegin && $foundsong !== $searchsong) {
                continue;
            }

            $pos = mb_strpos($foundsong, $searchsong);
            if (!$searchsong && !$foundsong) {
                $pos = mb_strpos($html, "abstrack");
                if ($pos !== false) {
                    $pos = 0;
                }
            }

            if ($pos !== false) {
                $lyrics->found = true;
                $pattern = "{href=\"(.*?)\"}s";
                preg_match_all($pattern, $html, $match);

                $config['url'] = "https://www.musixmatch.com" . $match[1][0];
                $config['start_tag'] = '<p class="mxm-lyrics__content ">';
                $config['end_tag'] = '</p>';
                $contentgrabber = new grabber;
                $contentgrabber->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

                if ($contentgrabber->html) {
                    $lyrics->content = null;
                    $lyrics->empty = null;
                    foreach ($contentgrabber->html[1] as $html) {
                        $document = new DOMDocument();
                        $document->loadHTML($html);
                        foreach ($document->childNodes as $content) {
                            if (!is_null($content->nodeValue)) {
                                $html = utf8_decode($content->nodeValue);
                                $lyrics->content .= $html . "\n";
                            }
                        }
                    }
                } elseif (!$lyrics->content) {
                    $config['start_tag'] = '<h2 class="mxm-empty__title">';
                    $config['end_tag'] = '</h2>';
                    $emptygrabber = new grabber;
                    $emptygrabber->grabhtml($config['url'], $config['start_tag'], $config['end_tag']);

                    if ($emptygrabber->html) {
                        $lyrics->empty = null;
                        foreach ($emptygrabber->html[1] as $html) {
                            $document = new DOMDocument();
                            $document->loadHTML($html);
                            foreach ($document->childNodes as $empty) {
                                if (!is_null($empty->nodeValue)) {
                                    $html = utf8_decode($empty->nodeValue);
                                    $lyrics->empty .= $html . "\n";
                                }
                            }
                        }
                    }
                }

                if ($searchsong === $foundsong && !$lyrics->empty) {
                    break;
                }
                if ($searchsong === $foundsong && $lyrics->empty === "Instrumental\n") {
                    break;
                }
                if ($pos === 0) {
                    $foundbegin = true;
                }
            }
        }
        if (!$lyrics->found && !$unmark) {
            $unmark = true;
            goto again;
        }
    }
}

$lyrics = 'getLyrics(' . json_encode($lyrics) . ")";
echo $lyrics;
?>