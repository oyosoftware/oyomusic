* oyomusic 2.0
* tested with jQuery 3.4.0
* http://www.oyoweb.nl
*
* © 2015-2021 oYoSoftware
* MIT License
*
* oyomusic is a library for showing your own musical content

Full documentation will follow, for now I give a short description

The database is comprised of the following tables:
- Artists
- Albums
- Discs
- Tracks
- Countries
- Formats
- Genres

Most of the data can be read from the ID-tags in the files, except for (Artist)-country, Disc Title and the Boxset data.
In the future I will make them available by a data entry program.

To start with your database, do the following steps:
- Make sure you have set up a server with PHP and MySQL (I use WampServer)
- You have to use the SQL-script music.sql from the sql folder to create the database
- Open this file to change the databse name if you want to
- Run this file in phpMyAdmin or MySQL Workbench
- Open the file settings.inc to change the settings:
    - Change at least the server, username, password and database name
    - The page title is the text that appears in the browser tab
    - The header title appears at the top of the web page
    - The records per page determines how many records are visible on a page, if more there will be a navigator
    - The search records per page determines how many records are visible on a page when searching by the form fields on top of the page
    - The page range determines how many pages are visible in the navigator, if more the navigation bar will shift
    - The audio source is the root where your music is stored
    - The image path is the path where your album covers are stored
    - The image path for your thumbs is the path where your album covers for album lists are stored
    - The image path for artists is the path where your artist images are stored
    - Make sure that all these images have the name folder.jpg
    - If you use Joomla, you can use the absolute path beginning from the webroot to login for extra functionality (download albums)

Now that you have set up the library you can run some scripts from the interfaces folder:
- Go to a command line with cmd or beter, use Cmder, so you have a full screen command line and a full buffer that you can scroll in
- Go to the interfaces folder
- Use the command php -f scriptname.php
- First, run intMP3toSQL.php to scan your audio source, you can eventually change the $base parameter to do a partial scan
- If you have a CATraxx databse, you can convert your database instead, using intCATtoSQL.php
- After that you have to run intCALtoSQL.php to do some calculations
- If some files are deleted from the audiosource after a scan, you have to run intDELtoSQL.php
- To collect your images, you have to run colImages.php
- For some scripts there will be a file called error.log

If all is well, you can now view your collection by going to your server link and actually play some music

If you want to set your collection online, there are a few considerations:
- Use a good synchronisation program, like SyncBackPro, because maybe your collection is too big to use an FTP program
- Zip your database by phpMyAdmin, because most providers only allow small file sizes to upload. Don't use the create database for this file.
- Change your settings file online, according to the database name online.

GOOD LUCK