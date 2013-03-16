<?php
namespace Gratheon\CMS\Updates;

class Step00010 extends \Gratheon\CMS\Sync{
	var $description='Meta title editing';
	
	function process(){
		
		if($this->existsTable('content_menu') && !$this->existsTableField('content_menu','meta_title')){
			$this->q("ALTER table `content_menu` 
				add column `meta_title` varchar (250)  NULL  COLLATE utf8_unicode_ci  after `content_template`");        
		}
		
		return $this->bUpdateSuccess;
	}
	
}