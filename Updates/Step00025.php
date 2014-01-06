<?php
namespace Gratheon\CMS\Updates;

class Step00025 extends \Gratheon\CMS\Sync {
	var $description = 'User module';


	function process() {
		if(!$this->existsTableField('sys_user_contact', 'email')) {
			$this->ask("ALTER TABLE `sys_user_contact`     ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone_home`;");

			$this->ask("UPDATE sys_user_contact t1 SET email = (SELECT email FROM sys_user t2 WHERE t1.userID=t2.ID);");
		}

		if(!$this->existsTableField('sys_user_contact', 'URL')) {
			$this->ask("ALTER TABLE `sys_user_contact`     ADD COLUMN `URL` VARCHAR(255) NULL AFTER `phone_home`;");


			$this->ask("ALTER TABLE `sys_user` DROP COLUMN `url`;");
			$this->ask("ALTER TABLE `sys_user_contact`     ADD COLUMN `personID` INT NULL AFTER `userID`;");
			$this->ask("UPDATE sys_user_contact t1 SET personID = (SELECT personID FROM sys_user t2 WHERE t1.userID=t2.ID)");
			$this->ask("ALTER TABLE `sys_user_contact` DROP FOREIGN KEY  `sys_user_contact_FK1` ;");
			$this->ask("ALTER TABLE `content_person`     ADD COLUMN `wikipedia` VARCHAR(250) CHARSET utf8 NULL AFTER `date_birth`;");
		}


		if(!$this->existsTable('content_poll_question')) {
			//Poll related
			$this->ask("CREATE TABLE `content_poll_question`(     `ID` INT NOT NULL AUTO_INCREMENT ,     `pollID` INT ,     `title` MEDIUMTEXT CHARSET utf8 ,     `position` MEDIUMINT ,     PRIMARY KEY (`ID`)  );");

			$this->ask("ALTER TABLE `content_poll_answers`     ADD COLUMN `questionID` INT NULL AFTER `pollID`;");
			$this->ask("ALTER TABLE `content_poll_answers`     ADD COLUMN `type` ENUM('radio','checkbox','text') DEFAULT 'radio' NULL AFTER `answer`;");
			$this->ask("ALTER TABLE `content_poll`     ADD COLUMN `show_results` TINYINT(1) DEFAULT '1' NULL AFTER `restriction`;");

			$this->ask("CREATE TABLE `content_poll_response`(     `ID` INT ,     `pollID` INT ,     `date_added` DATETIME ,     `userID` INT ,     `IP` INT   );");

			if($this->existsTableField('content_poll_votes','date_added')){
				$this->ask("ALTER TABLE `content_poll_votes` DROP COLUMN `date_added`;");
			}

			$this->ask("ALTER TABLE `content_poll_votes` DROP COLUMN `IP`, DROP COLUMN `userID`,    ADD COLUMN `responseID` INT NULL AFTER `answerID`,     ADD COLUMN `value` TEXT CHARSET utf8 COLLATE utf8_unicode_ci NULL AFTER `responseID`;");

			$this->ask("ALTER TABLE `content_poll_votes`     ADD COLUMN `questionID` INT NULL AFTER `pollID`;");
			$this->ask("ALTER TABLE `content_poll`     ADD COLUMN `authentication_limit` ENUM('none','freetext','user') DEFAULT 'none' NULL AFTER `title`,    CHANGE `restriction` `network_restriction` ENUM('IP','Subnet','Users') CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ;");
		}

		return $this->bUpdateSuccess;
	}
}
