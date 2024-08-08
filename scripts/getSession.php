<?php
/*
  oyomusic 2.2
  tested with joomla 4.4.6
  http://www.oyoweb.nl

  © 2015-2024 oYoSoftware
  MIT License

  getSession: get login data for joomla session to be able to download albums
  partly taken from /includes/app.php  */

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

    $container = \Joomla\CMS\Factory::getContainer();
    $container->alias('session.web', 'session.web.site')
        ->alias('session', 'session.web.site')
        ->alias('JSession', 'session.web.site')
        ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
        ->alias(\Joomla\Session\Session::class, 'session.web.site')
        ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');
    $app = $container->get(\Joomla\CMS\Application\SiteApplication::class);

    $session = \Joomla\CMS\Factory::getApplication()->getSession();
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