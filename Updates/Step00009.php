<?php
namespace Gratheon\CMS\Updates;

class Step00009 extends \Gratheon\CMS\Sync{
	var $description='Add config';
	
	function process(){
		$sys_config=new \Gratheon\Core\Model('sys_config');
		
		if(!$sys_config->int("application='content' AND var_name='skin'",'var_name')){
			$sys_config->insert(array(
				'application'	=>'content',
				'var_name'		=>'skin',
				'var_value'		=>'luminous',
				'var_type'		=>'radio',
				'var_select_values'	=> 'luminous|webstyle',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
		
		if(!$sys_config->int("application='content' AND var_name='translation_mode'",'var_name')){
			$sys_config->insert(array(
				'application'	=>'content',
				'var_name'		=>'translation_mode',
				'var_value'		=>'0',
				'var_type'		=>'checkbox',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
		
		if(!$sys_config->int("application='front' AND var_name='translation_mode'",'var_name')){
			$sys_config->insert(array(
				'application'	=>'front',
				'var_name'		=>'translation_mode',
				'var_value'		=>'0',
				'var_type'		=>'checkbox',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
	
		$sys_config->update(array(
			'var_type'		=>'radio',
			'var_select_values'	=> 'square|thumb|original',
		),"application='image' AND var_name='thumbnail_type'");
		
		$sys_config->update(array(
			'var_type'		=>'radio',
			'var_select_values'	=> 'right|bottom',
		),"application='image' AND var_name='float_position'");
		
		
		if(!$this->existsTable('sys_sync_account') ){
			$this->ask("CREATE TABLE `sys_sync_account` (
	                    `ID` int(11) NOT NULL auto_increment,                                          
	                    `service` varchar(200) character set utf8 default NULL,                        
	                    `login` varchar(200) character set utf8 collate utf8_unicode_ci default NULL,  
	                    `password` varbinary(200) default NULL,                                        
	                    `user_id` varchar(200) default NULL,                                           
	                    `key` varchar(200) character set utf8 default NULL,                            
	                    `key2` varchar(200) character set utf8 default NULL,                           
	                    `key3` varchar(200) character set utf8 default NULL,                           
	                    `key4` varchar(200) character set utf8 default NULL,                           
	                    PRIMARY KEY  (`ID`)                                                            
	                  ) ENGINE=MyISAM DEFAULT CHARSET=cp1251");        
		}
		
		return $this->bUpdateSuccess;
	}
	
}