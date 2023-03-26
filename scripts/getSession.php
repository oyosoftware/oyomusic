<?php

error_reporting(E_ERROR);

require_once('../settings.inc');

$documentroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
if (is_null($documentroot)) {
    $documentroot = filter_input(INPUT_ENV, 'DOCUMENT_ROOT');
}
$joomlapath = $documentroot . $joomlapath;

if (file_exists($joomlapath)) {
    define('_JEXEC', 1);
    define('JPATH_BASE', $joomlapath);
    require_once($joomlapath . "/includes/defines.php");
    require_once($joomlapath . "/includes/framework.php");

    $app = JFactory::getApplication('site');
    $session = JFactory::getSession();

    $username = $session->get("user.username");
    $expire = $session->getExpire();
    $start = $session->get("session.timer.start") ? $session->get("session.timer.start") : 0;
    $last = $session->get("session.timer.last") ? $session->get("session.timer.last") : 0;
    $now = $session->get("session.timer.now") ? $session->get("session.timer.now") : 0;

    $session = (object) [];
    $session->username = $username;
    $session->expire = $expire;
    $session->start = $start;
    $session->last = $last;
    $session->now = $now;

    $session = 'getSession(' . json_encode($session, JSON_PRETTY_PRINT) . ")";
    echo $session;
}
?>