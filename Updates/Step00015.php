<?php
namespace Gratheon\CMS\Updates;

class Step00015 extends \Gratheon\CMS\Sync{
    var $description='Language key modifications, INNODB migration';

    function process(){

        if(!$this->existsTableFieldKey('content_article','parentID')){
            $this->ask("ALTER TABLE `content_article`  ADD KEY `parentID`(`parentID`), ENGINE=InnoDB;");

            pre($this->arrint("SELECT ID FROM content_article WHERE parentID NOT IN (SELECT id FROM content_menu)"));
            if(!$this->bUpdateSuccess){
            }
        }

        if($this->existsTableFieldType('sys_user','langID','int(11)')){
            $this->ask("ALTER TABLE `sys_user`  ADD COLUMN `lang` CHAR(3) DEFAULT 'eng' NULL;");
            $this->ask("UPDATE sys_user SET lang='eng' WHERE langID=1;");
            $this->ask("UPDATE sys_user SET lang='rus' WHERE langID=2;");
            $this->ask("UPDATE sys_user SET lang='est' WHERE langID=3;");

            $this->ask("ALTER TABLE `sys_user` DROP COLUMN langID;");
            $this->ask("ALTER TABLE `sys_user` CHANGE `lang` `langID` CHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'eng' NULL ;");
        }

        if($this->existsTableFieldType('content_menu','langID','int(11)')){
            $this->ask("ALTER TABLE `content_menu` CHANGE `parentID` `parentID` INT(11) NULL;");
            $this->ask("UPDATE `content_menu` SET `parentID`=NULL WHERE `parentID`=0;");

            $this->ask("ALTER TABLE `content_menu`  ADD COLUMN `lang` CHAR(3) DEFAULT 'eng' NULL AFTER `elementID`;");

            $this->ask("UPDATE content_menu SET lang='eng' WHERE langID=1;");
            $this->ask("UPDATE content_menu SET lang='rus' WHERE langID=2;");
            $this->ask("UPDATE content_menu SET lang='est' WHERE langID=3;");

            $this->ask("ALTER TABLE `content_menu` DROP COLUMN langID;");
            $this->ask("ALTER TABLE `content_menu` CHANGE `lang` `langID` CHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'eng' NULL ;");
            $this->ask("ALTER TABLE `content_menu` CHANGE `module` `module` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");

            $this->ask("ALTER TABLE `content_menu` ADD KEY `langID` (`langID`)");
            $this->ask("ALTER TABLE `content_menu` ADD KEY `module` (`module`)");
            $this->ask("ALTER TABLE `content_menu` ADD KEY `parentID` (`module`)");
        }

        if($this->existsTableField('content_module','id')){
            $this->ask("ALTER TABLE `content_module` DROP COLUMN `id`,  CHANGE `title` `ID` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,  DROP PRIMARY KEY, ADD PRIMARY KEY(`ID`);");
        }



        if($this->existsTableFieldType('sys_languages','ID','int(11)')){
            $this->ask("ALTER TABLE `sys_languages` DROP COLUMN `ID`;");
            $this->ask("ALTER TABLE `sys_languages` CHANGE `alpha3` `ID` CHAR(3)  COLLATE utf8_unicode_ci NOT NULL FIRST;");
            $this->ask("ALTER TABLE `sys_languages` ADD PRIMARY KEY(`ID`);");
        }

        if($this->existsTableField('sys_user_contact','user_id')){
            $this->ask("ALTER TABLE `sys_user_contact` CHANGE `user_id` `userID` int(11) NULL FIRST;");
            $this->ask("ALTER TABLE `sys_user_contact` CHANGE `country_id` `countryID` int(11) after `userID`;");
            $this->ask("ALTER TABLE `sys_user_contact` ADD KEY `countryID` (`countryID`)");
            $this->ask("ALTER TABLE `sys_user_contact` ADD KEY `userID` (`countryID`)");
        }



        //Change to InnoDB
        $this->ask("ALTER TABLE `content_menu`                  ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_menu_rights`           ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_tags`                  ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_module`                ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_module_connections`    ENGINE = INNODB;");

        $this->ask("ALTER TABLE `content_article`   ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_category`  ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_comment`   ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_file`      ENGINE = INNODB;");
        $this->ask("ALTER TABLE `content_image`     ENGINE = INNODB;");

        $this->ask("ALTER TABLE `sys_languages`     ENGINE = INNODB;");
        $this->ask("ALTER TABLE `sys_user`          ENGINE = INNODB;");
        $this->ask("ALTER TABLE `sys_user_contact`  ENGINE = INNODB;");
        $this->ask("ALTER TABLE `sys_user_group`    ENGINE = INNODB;");
        $this->ask("ALTER TABLE `sys_tags`          ENGINE = INNODB;");
        $this->ask("ALTER TABLE `sys_rights`          ENGINE = INNODB;");

        //Foreign keys
        if(!$this->existsTableForeignKey('content_article','parentID')){
            $this->ask("ALTER TABLE `content_article`   ADD CONSTRAINT `content_article_FK1`     FOREIGN KEY (`parentID`) REFERENCES `content_menu` (`ID`) ON DELETE CASCADE;");
        }

        if(!$this->existsTableForeignKey('content_menu','langID')){
            $this->ask("ALTER TABLE `content_menu`      ADD CONSTRAINT `content_menu_FK1`        FOREIGN KEY (`langID`) REFERENCES `sys_languages` (`ID`);");
        }

        if(!$this->existsTableForeignKey('content_menu','module')){
            $this->ask("ALTER TABLE `content_menu`      ADD CONSTRAINT `content_menu_FK2`        FOREIGN KEY (`module`) REFERENCES `content_module` (`ID`) ON DELETE SET NULL;");
        }

        if(!$this->existsTableForeignKey('content_menu','parentID')){
            $aMissingIDs = $this->q("SELECT ID FROM content_menu t1 WHERE parentID NOT IN (SELECT ID FROM content_menu t2 WHERE t2.ID=t1.parentID)",'arrint');
            if($aMissingIDs){
                $this->ask("UPDATE content_menu SET parentID=NULL WHERE ID IN (".implode(',',$aMissingIDs).")");
            }
            $this->ask("ALTER TABLE `content_menu`      ADD CONSTRAINT `content_menu_FK3`        FOREIGN KEY (`parentID`) REFERENCES `content_menu` (`ID`) ON DELETE CASCADE;");
        }

        if(!$this->existsTableForeignKey('sys_user','groupID')){
            $this->ask("ALTER TABLE `sys_user`          ADD CONSTRAINT `sys_user_FK1`         FOREIGN KEY (`groupID`) REFERENCES `sys_user_group` (`ID`) ON DELETE SET NULL;");
        }

        if(!$this->existsTableForeignKey('sys_user_contact','userID')){
            $this->ask("ALTER TABLE `sys_user_contact`  ADD CONSTRAINT `sys_user_contact_FK1` FOREIGN KEY (`userID`) REFERENCES `sys_user` (`ID`) ON DELETE CASCADE;");
        }

        return $this->bUpdateSuccess;
    }
}