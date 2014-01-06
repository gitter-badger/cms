<?php
namespace Gratheon\CMS\Updates;

class Step00028 extends \Gratheon\CMS\Sync{
    var $description='Movies module';

    function process(){

		if(!$this->existsTable('content_movie')){

			$this->ask("CREATE TABLE `content_movie` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` mediumint(9) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `langID` varchar(3) DEFAULT 'eng',
  `imdbID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NewIndex1` (`imdbID`)
) ENGINE=MyISAM AUTO_INCREMENT=210 DEFAULT CHARSET=latin1");

		}

		return $this->bUpdateSuccess;
	}
}
