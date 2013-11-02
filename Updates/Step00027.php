<?php
namespace Gratheon\CMS\Updates;

class Step00027 extends \Gratheon\CMS\Sync{
    var $description='Article titles';

    function process(){

		if(!$this->existsTableField('content_article','title')){
			$this->ask("ALTER TABLE `content_article`     ADD COLUMN `title` VARCHAR(250) CHARSET utf8 COLLATE utf8_unicode_ci NULL AFTER `date_changed`;");

			$this->ask("ALTER TABLE `content_article` CHANGE `ID` `ID` INT(11) NOT NULL AUTO_INCREMENT COMMENT '' FIRST, CHANGE `parentID` `parentID` INT(11) NULL COMMENT 'links to cms_menu' AFTER `ID`, CHANGE `title` `title` VARCHAR(250) NULL COLLATE utf8_unicode_ci COMMENT '' AFTER `parentID`, CHANGE `content` `content` TEXT NOT NULL COLLATE utf8_unicode_ci COMMENT '' AFTER `title`, CHANGE `date_added` `date_added` DATETIME NULL COMMENT '' AFTER `content`, CHANGE `date_changed` `date_changed` DATETIME NULL COMMENT '' AFTER `date_added`, CHANGE `content_index` `content_index` TEXT NULL COLLATE utf8_unicode_ci COMMENT '' AFTER `date_changed`;");

			$this->ask("UPDATE content_article SET title = (SELECT title FROM content_menu WHERE content_menu.ID=content_article.parentID)");

			$this->ask("ALTER TABLE `sys_tags`     ADD COLUMN `color` VARCHAR(20) NULL AFTER `pop`;");
		}

		return $this->bUpdateSuccess;
	}
}

