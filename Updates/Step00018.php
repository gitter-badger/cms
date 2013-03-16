<?php
namespace Gratheon\CMS\Updates;

class Step00018 extends \Gratheon\CMS\Sync{
    var $description='Content rating added';

    function process(){

        if(!$this->existsTable('content_menu_rating'))
        $this->ask("CREATE TABLE `content_menu_rating`(
                 `parentID` INT ,
                 `xrate_tag` VARCHAR(30) ,
                 `rating` TINYINT
               );");

        return $this->bUpdateSuccess;
    }
}
