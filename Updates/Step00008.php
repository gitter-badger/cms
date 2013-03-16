<?php
namespace Gratheon\CMS\Updates;

class Step00008 extends \Gratheon\CMS\Sync{
	var $description='Sync accounts';
	
	function process(){
		if(!$this->existsTable('sys_sync_account') ){
			$this->ask("
			CREATE TABLE `sys_sync_account` (                                                
                    `ID` int(11) NOT NULL auto_increment,                                          
                    `service` varchar(200) character set utf8 default NULL,                        
                    `login` varchar(200) character set utf8 collate utf8_unicode_ci default NULL,  
                    `user_id` varchar(200) default NULL,                                           
                    `key` varchar(200) character set utf8 default NULL,                            
                    `key2` varchar(200) character set utf8 default NULL,                           
                    `key3` varchar(200) character set utf8 default NULL,                           
                    `key4` varchar(200) character set utf8 default NULL,                           
                    `password` varbinary(200) default NULL,                                        
                    PRIMARY KEY  (`ID`)                                                            
                  ) ENGINE=MyISAM DEFAULT CHARSET=cp1251       
			");
		}
		
		return $this->bUpdateSuccess;
	}
	
}