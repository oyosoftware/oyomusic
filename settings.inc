<?php
$server = "localhost";
$username = "username";
$password = "password";
$database = "music";
$pagetitle = "oYoMusic: Music Collection Software";
$headertitle = "My Music Collection";
$recordspage = 10; // number of lines in the album list
$searchrecordspage = 100; // number of lines in the album search list
$pagerange = 9; // number of visible pages in the page navigator
// take only one of these audio sources:
//$audiosource = "/audio"; // web root folder
$audiosource = "audio"; // web project folder (this project)
// or make a symbolic link for a drive or network path in your project folder
// mklink /D "project folder" "audio source"
// example: mklink /D "audio" "C:\Music"
// example: mklink /D "audio" "\\Computername\Shared\Music"
// for access to a network path : open gpedit.msc
// go to Computer Configuration → Windows Settings → Security Settings → Local Policies → Securty Options
// Network access: Let Everyone permissions apply to anonymous users – change to Enabled
// Network access: Shares that can be accessed anonymously – enter name of share you created
$imagepath = "images/albums";
$imagepaththumbs = "images/albums/thumbs";
$imagepathartists = "images/artists";
$joomlapath = "/joomla"; // extra functionality for users who are logged in in Joomla
$catraxxdatabasepath = "C:\Users\Someone\Documents\Music\collection.mdb"; // for interfacing with CATraxx
$catraxxaudiosource = "Q:\Music"; // the main path you chose for importing in CATraxx
?>
