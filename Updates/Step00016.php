<?php
namespace Gratheon\CMS\Updates;

class Step00016 extends \Gratheon\CMS\Sync{
    var $description='Language key modifications, INNODB migration';

    function process(){
        if($this->existsTableFieldType('content_news_body','langID','int(11)')){
			
            $this->ask("ALTER TABLE `content_news_body`
			   ADD COLUMN `lang` VARCHAR(3) NULL AFTER `langID`,
			   CHANGE `langID` `langID` INT(11) NULL ;");

            $this->ask("UPDATE content_news_body SET lang='eng' WHERE langID=1;");
            $this->ask("UPDATE content_news_body SET lang='rus' WHERE langID=2;");
            $this->ask("UPDATE content_news_body SET lang='est' WHERE langID=3;");

            $this->ask("ALTER TABLE `content_news_body` DROP COLUMN langID;");
            $this->ask("ALTER TABLE `content_news_body` CHANGE `lang` `langID` CHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'eng' NULL ;");
        }

        return $this->bUpdateSuccess;
    }
}