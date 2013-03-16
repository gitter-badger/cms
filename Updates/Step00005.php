<?php
namespace Gratheon\CMS\Updates;

class Step00005 extends \Gratheon\CMS\Sync{
	var $description='Pages now support metadata saving';
	
	function process(){
		if($this->existsTable('content_menu') && !$this->existsTableField('content_menu','content_template')){
			$this->ask("alter table `content_menu` add column `content_template` varchar(200) default NULL  after `container_template`");
		}
		if($this->existsTable('content_menu') && !$this->existsTableField('content_menu','meta_keywords')){
			$this->ask("alter table `content_menu` add column `meta_keywords` mediumtext   NULL  COLLATE utf8_unicode_ci  after `content_template`, add column `meta_description` mediumtext   NULL  COLLATE utf8_unicode_ci  after `meta_keywords`, add column `meta_latitude` double   NULL  after `meta_description`, add column `meta_longitude` double   NULL  after `meta_latitude`");
		}
				
		return $this->bUpdateSuccess;
	}
	
}

