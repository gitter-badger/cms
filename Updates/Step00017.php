<?php
namespace Gratheon\CMS\Updates;

class Step00017 extends \Gratheon\CMS\Sync{
    var $description='Category view';

    function process(){

        $this->ask("UPDATE content_menu SET method='pub_list' WHERE method='front_view' AND `module`='category';");


        if(!$this->existsTableField('content_comment','userID')){
            $this->ask("ALTER TABLE `content_comment` ADD COLUMN `userID` INT NULL AFTER `parentID`;");
            $this->ask("ALTER TABLE `content_comment` CHANGE `author` `author` TEXT CHARACTER SET utf8 COLLATE utf8_estonian_ci NULL ;");
            $this->ask("ALTER TABLE `content_comment`
               CHANGE `content` `content` TEXT CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
               CHANGE `gender` `gender` TINYINT(1) DEFAULT '0' NULL ,
               CHANGE `url` `url` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT '' NULL ,
               CHANGE `email` `email` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT '' NULL ,
               CHANGE `gravatar` `gravatar` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT '' NULL ;");
        }

        if(!$this->existsTableField('sys_user','url')){
            $this->ask("ALTER TABLE `sys_user`
           ADD COLUMN `url` VARCHAR(250) CHARSET utf8 NULL AFTER `groupID`;");
        }

        if(!$this->existsTableField('sys_user','email')){
            $this->ask("ALTER TABLE `sys_user`
           ADD COLUMN `email` VARCHAR(250) CHARSET utf8 NULL AFTER `url`;");
        }

        return $this->bUpdateSuccess;
    }
}


/*
ALTER TABLE `sys_user_contact`
   ADD COLUMN `facebook` VARCHAR(100) CHARSET utf8 NULL AFTER `livejournal`,
   CHANGE `LJ` `livejournal` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ;

CREATE TABLE `content_map`(
   `ID` INT NOT NULL AUTO_INCREMENT ,
   `parentID` INT ,
   `geoX` VARCHAR(15) ,
   `geoY` VARCHAR(15) ,
   `zoom` VARCHAR(15) DEFAULT '13',
   `layer` VARCHAR(15) DEFAULT 'HYBRID',
   PRIMARY KEY (`ID`)
 );


 //todo - html field from content_ext videos

ALTER TABLE `sys_user_contact`
   ADD COLUMN `last_facebook_friend_sync` DATETIME NULL AFTER `city`;

UPDATE content_menu SET method='pub_list' WHERE module='category' AND method='front_view';

RENAME TABLE `content_external_video` TO `content_video`;