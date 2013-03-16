<?php
namespace Gratheon\CMS\Updates;

class Step00024 extends \Gratheon\CMS\Sync{
    var $description='Ties menu with modules';

    function process(){

        $this->ask("ALTER TABLE `content_menu` ADD CONSTRAINT `content_menu_FK2` FOREIGN KEY (`module`) REFERENCES `content_module` (`ID`) ON DELETE SET NULL  ON UPDATE CASCADE ;");
    }
}