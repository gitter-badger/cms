<?php
namespace Gratheon\CMS\Updates;

class Step00001 extends \Gratheon\CMS\Sync{
	var $description='Menu structure now only reflects actual data. Same image can be embedded in multiple nodes/articles';
	
	function process(){
		if($this->existsTable('content_menu') && $this->existsTableField('content_menu','deleteable')){
			$this->ask("ALTER table `content_menu` drop column `deleteable`, drop column `insertable`,add column `elementID` int   NULL  after `smart_url`");
			$this->ask("UPDATE content_menu t1 SET elementID=(SELECT ID FROM content_image t2 WHERE t2.parentID=t1.ID) WHERE module='image'");
		}
		
		if($this->existsTable('content_image') && !$this->existsTableField('content_image','md5')){
			$this->ask("alter table `content_image` add column `md5` varchar (32)  NULL  after `thumbnail_type`");
		}
		
		$this->md5Images();
		
		return $this->bUpdateSuccess;
	}
	
	function md5Images(){
		$intHashed=$this->q("SELECT COUNT(ID) FROM content_image WHERE md5 IS NOT NULL",'int');
		$intTotal=$this->q("SELECT COUNT(ID) FROM content_image",'int');
		if($intTotal>0){
			pre('hashing images..'.round(100*$intHashed/$intTotal)."% completed");
		}
		
		$arrImages=$this->q("SELECT * FROM content_image WHERE md5 IS NULL LIMIT 10");
		
		foreach ($arrImages as $arrImage){
			$strMD5=md5_file(sys_root.'/res/image/original/'.$arrImage->ID.'.'.$arrImage->image_format);
			$this->q("UPDATE content_image SET md5='$strMD5' WHERE ID='$arrImage->ID'");
		}
		
		$intLeft=$this->q("SELECT COUNT(ID) FROM content_image WHERE md5 IS NULL",'int');
		if($intLeft){
			$this->bReloadNeeded=true;
		}
	}
}