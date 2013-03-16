<?php
namespace Gratheon\CMS\Updates;

class Step00012 extends \Gratheon\CMS\Sync{
	var $description='Domain description';

	function process(){

		if($this->existsTable('sys_sync_account') && !$this->existsTableField('sys_sync_account','domain')){
			$this->q("alter table `sys_sync_account` add column `domain` varchar (200)  NULL  after `user_id`,change `key` `key` varchar (200)  NULL  COLLATE utf8_general_ci ");
		}
		
		if($this->existsTable('sys_config') && !$this->existsTableField('sys_config','sorting')){
			$this->q("alter table `sys_config` add column `sorting` mediumint   NULL  after `var_select_values`");
		}
		
		if($this->existsTable('content_news_twitter') && !$this->existsTable('content_news_external')){
			$this->q("rename table `content_news_twitter` to `content_news_external`");
			$this->q("alter table `content_news_external` 
				add column `serviceName` varchar (30)  NULL  after `servicePostID`, 
				add column `syncAccount` int   NULL  after `serviceName`,
				change `twitterID` `servicePostID` bigint (20)  NULL ");
		}

		return $this->bUpdateSuccess;
	}

}
