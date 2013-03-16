<?php
namespace Gratheon\CMS\Updates;

class Step00003 extends \Gratheon\CMS\Sync{
	var $description='Image default configurations';
	
	function process(){
		$sys_config=new \Gratheon\Core\Model('sys_config');
		
		if(!$sys_config->int("application='image' AND var_name='original_size'",'var_name')){
			$sys_config->insert(array(
				'application'=>'image',
				'var_name'=>'original_size',
				'var_value'=>'800',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
		
		if(!$sys_config->int("application='image' AND var_name='thumbnail_type'",'var_name')){
			$sys_config->insert(array(
				'application'=>'image',
				'var_name'=>'thumbnail_type',
				'var_value'=>'square',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
				
		if(!$sys_config->int("application='image' AND var_name='thumbnail_size'",'var_name')){
			$sys_config->insert(array(
				'application'=>'image',
				'var_name'=>'thumbnail_size',
				'var_value'=>'200',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
		
		if(!$sys_config->int("application='image' AND var_name='float_position'",'var_name')){
			$sys_config->insert(array(
				'application'=>'image',
				'var_name'=>'float_position',
				'var_value'=>'bottom',
			));
			
			if($sys_config->adapter->mysql_error){
				$this->bUpdateSuccess=false;
			}
		}
				
		return $this->bUpdateSuccess;
	}
	
}