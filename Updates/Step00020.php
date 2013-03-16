<?php
namespace Gratheon\CMS\Updates;

class Step00020 extends \Gratheon\CMS\Sync{
    var $description='Added facebook/google sync';

    function process(){

        if(!$this->existsTableField('sys_user_contact', 'facebook')){
            $this->ask("ALTER TABLE `sys_user_contact` ADD COLUMN `facebook` VARCHAR(30) NULL;");
        }

        if(!$this->existsTableField('sys_user_contact', 'google')){
            $this->ask("ALTER TABLE `sys_user_contact` ADD COLUMN `google` VARCHAR(30) NULL AFTER `facebook`;");
        }

        return $this->bUpdateSuccess;
    }
}


