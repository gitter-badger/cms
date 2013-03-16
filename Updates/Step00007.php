<?php
namespace Gratheon\CMS\Updates;

class Step00007 extends \Gratheon\CMS\Sync{
	var $description='Translations use code instead of english version if its defined';
	
	function process(){
		if($this->existsTable('sys_translations') && !$this->existsTableField('sys_translations','code')){
			$this->ask("alter table `sys_translations` add column `code` varchar (50)  NULL  COLLATE utf8_unicode_ci  after `application`");
		}
		
		if($this->existsTable('sys_config') && !$this->existsTableField('sys_config','var_select_values')){
			$this->ask("alter table `sys_config` add column `var_select_values` mediumtext   NULL  COLLATE utf8_unicode_ci  after `var_type`");
		}
		
		$this->ask("UPDATE sys_config SET var_select_values='luminous|webstyle' WHERE application='content' AND var_name='skin';");
				
		return $this->bUpdateSuccess;
	}
	
}