<?php
namespace Gratheon\CMS\Updates;

class Step00023 extends \Gratheon\CMS\Sync{
    var $description='Added formula and sidenotes schema';

    function process(){

        if(!$this->existsTable('content_formula')){
            $this->ask("CREATE TABLE `content_formula` (
              `ID` int(11) NOT NULL AUTO_INCREMENT,
              `parentID` int(11) DEFAULT NULL,
              `content` text COLLATE utf8_unicode_ci,
              `format` enum('latex','mathml','ascii') COLLATE utf8_unicode_ci DEFAULT 'latex',
              `description` text COLLATE utf8_unicode_ci,
              PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");
        }

        if(!$this->existsTable('content_sidenote')){
            $this->ask("CREATE TABLE `content_sidenote` (
              `ID` int(11) NOT NULL AUTO_INCREMENT,
              `parentID` int(11) DEFAULT NULL,
              `position` enum('left','right','foot') CHARACTER SET latin1 DEFAULT 'right',
              `content` text COLLATE utf8_unicode_ci,
              PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        }

        if(!$this->existsTable('content_person')){
            $this->ask("CREATE TABLE `content_person` (
              `ID` int(11) NOT NULL AUTO_INCREMENT,
              `firstname` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
              `lastname` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
              `sex` enum('male','female') CHARACTER SET latin1 DEFAULT NULL,
              `date_birth` datetime DEFAULT NULL,
              PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

            $this->ask("INSERT INTO content_person (firstname, lastname, sex, date_birth)
            SELECT firstname, lastname, 'male', date_birth FROM sys_user WHERE sex='m'");

            $this->ask("INSERT INTO content_person (firstname, lastname, sex, date_birth)
            SELECT firstname, lastname, 'female', date_birth FROM sys_user WHERE sex='f'");

            $this->ask("ALTER TABLE `sys_user`     ADD COLUMN `personID` INT NULL AFTER `groupID`;");

            $this->ask("UPDATE sys_user SET personID = (SELECT ID FROM content_person WHERE content_person.firstname = sys_user.firstname AND content_person.lastname=sys_user.lastname)");

            $this->ask("ALTER TABLE `sys_user` DROP COLUMN `firstname`, DROP COLUMN `lastname`, DROP COLUMN `sex`, DROP COLUMN `date_birth`;");
        }

        return $this->bUpdateSuccess;
    }
}