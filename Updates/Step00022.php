<?php
namespace Gratheon\CMS\Updates;

class Step00022 extends \Gratheon\CMS\Sync{
    var $description='Added amazon image hosting';

    function process(){

        if(!$this->existsIndex('sys_translations', 'application_index')){
            $this->ask("ALTER TABLE `sys_translations` ADD INDEX `application_index` (`application`);");
        }

        return $this->bUpdateSuccess;
    }
}

