<?php
namespace Gratheon\CMS\Updates;

class Step00019 extends \Gratheon\CMS\Sync{
    var $description='RSS as permission system added';

    function process(){


        if(!$this->existsTableField('content_module', 'supports_rss')){
            $this->ask("ALTER TABLE `content_module` ADD COLUMN `supports_rss` TINYINT(1) DEFAULT '0' NULL AFTER `is_active`;");

            $this->ask("INSERT IGNORE INTO content_menu_rights (pageID, groupID, rightID)
                    SELECT pageID, 3, 6 FROM content_menu_rights WHERE rightID=2 AND groupID=3");

            $this->ask("INSERT IGNORE INTO `sys_rights`(`ID`,`title`) VALUES ( 6,'Visible in RSS');");
        }

        return $this->bUpdateSuccess;
    }
}






