<?php
namespace Gratheon\CMS\Updates;

class Step00002 extends \Gratheon\CMS\Sync{
	var $description='Menu structure now only reflects actual data. Same image can be embedded in multiple nodes/articles';
	
	function process(){
		if($this->existsTable('sys_config') && !$this->existsTableField('sys_config','var_type')){
			$this->ask("alter table `sys_config` add column `var_type` varchar (100) DEFAULT 'text' NULL  COMMENT 'text,textarea' after `var_value`");
		}
				
		return $this->bUpdateSuccess;
	}
	
}