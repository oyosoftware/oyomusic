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
// example: mklink /D "audio" "\\Computername\Shared\Music"
$imagepath = "images/albums";
$imagepaththumbs = "images/albums/thumbs";
$imagepathartists = "images/artists";
$joomlapath = "/joomla"; // extra functionality for users who are logged in in Joomla
$catraxxdatabasepath = "C:\Users\Someone\Documents\Music\collection.mdb"; // for interfacing with CATraxx
$catraxxaudiosource = "Q:\Music"; // the main path you chose for importing in CATraxx
?>
