<?php

error_reporting(22519);
require_once('../settings.inc');

$root = $_SERVER['DOCUMENT_ROOT'];
$joomlapath = $root . $joomlapath;

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

    $data = 'getSession({username: "' . $username . '", expire: ' . $expire . ', start: ' . $start . ', last: ' . $last . ', now: ' . $now . '})';
    echo $data;

    $log = fopen("output.log", "w");
    fwrite($log, $data);
    fclose($log);
}
?>