<?php
namespace Gratheon\CMS\Updates;

class Step00006 extends \Gratheon\CMS\Sync{
	var $description='Password storing in DB is now encrypted';
	
	function process(){
		if($this->existsTable('sys_config') && !$this->existsTableField('sys_config','var_value_binary')){
			$this->ask("alter table `sys_config` add column `var_value_binary` varbinary (128)  NULL  after `var_value`");
		}
				
		return $this->bUpdateSuccess;
	}
	
}