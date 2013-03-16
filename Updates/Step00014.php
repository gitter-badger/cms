<?php
namespace Gratheon\CMS\Updates;

class Step00014 extends \Gratheon\CMS\Sync{
    var $description='Configuration save time';

    function process(){

        if(!$this->existsTableField('sys_config','sorting')){
            $this->ask("ALTER TABLE `sys_config`
               ADD COLUMN `sorting` MEDIUMINT NULL AFTER `var_select_values`;");
        }

        $this->ask("INSERT INTO `sys_config`(`ID`,`application`,`var_name`,`var_value`,`var_value_binary`,`var_type`,`var_select_values`,`sorting`)
            VALUES ( NULL,'content','setting_save_time',NULL,NULL,'text',NULL,'25');");

        return $this->bUpdateSuccess;
    }
}