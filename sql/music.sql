SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `music` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `music`;

DROP TABLE IF EXISTS `albums`;
CREATE TABLE IF NOT EXISTS `albums` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ArtistId` int(11) NOT NULL,
  `Released` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `DiscCount` int(11) DEFAULT '0',
  `FormatId` int(11) DEFAULT '-1',
  `PlayingTime` int(11) DEFAULT '0',
  `GenreId` int(11) DEFAULT '-1',
  `Folder` varchar(255) DEFAULT NULL,
  `ImageFileName` varchar(255) DEFAULT NULL,
  `Checked` bit(1) DEFAULT b'0',
  `StatusId` int(11) DEFAULT '-1',
  `IsBoxset` bit(1) DEFAULT b'0',
  `BoxsetId` int(11) DEFAULT '-1',
  `BoxsetIndex` int(11) DEFAULT '-1',
  `LastModified` date DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Folder` (`Folder`),
  KEY `ArtistIdReleasedTitle` (`ArtistId`,`Released`,`Title`),
  KEY `BoxsetIdBoxsetIndex` (`BoxsetId`,`BoxsetIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `artists`;
CREATE TABLE IF NOT EXISTS `artists` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Letter` varchar(3) NOT NULL,
  `AlbumCount` int(11) DEFAULT '0',
  `CountryId` int(11) DEFAULT '-1',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Country` varchar(31) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Country` (`Country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `discs`;
CREATE TABLE IF NOT EXISTS `discs` (
  `AlbumId` int(11) NOT NULL,
  `DiscNo` int(11) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `PlayingTime` int(11) DEFAULT '0',
  PRIMARY KEY (`AlbumId`,`DiscNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `formats`;
CREATE TABLE IF NOT EXISTS `formats` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Format` varchar(15) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Format` (`Format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `genres`;
CREATE TABLE IF NOT EXISTS `genres` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Genre` varchar(31) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Genre` (`Genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Status` (`Status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tracks`;
CREATE TABLE IF NOT EXISTS `tracks` (
  `AlbumId` int(11) NOT NULL,
  `DiscNo` int(11) NOT NULL,
  `Track` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `ArtistId` int(11) NOT NULL,
  `PlayingTime` int(11) DEFAULT '0',
  `AudioBitrate` int(11) DEFAULT '0',
  `AudioBitrateMode` varchar(15) DEFAULT NULL,
  `FileName` varchar(255) DEFAULT NULL,
  `LastModified` date DEFAULT NULL,
  PRIMARY KEY (`AlbumId`,`DiscNo`,`Track`),
  UNIQUE KEY `FileNameAlbumId` (`FileName`,`AlbumId`),
  KEY `ArtistId` (`ArtistId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;
