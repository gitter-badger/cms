<?php
namespace Gratheon\CMS\Model;

class Tag extends \Gratheon\Core\Model {
	use ModelSingleton;


	final function __construct($callParent=true) {
		if($callParent){
			parent::__construct('sys_tags');
		}
	}


	public function listPopTags(){
		return $this->q("SELECT * FROM sys_tags ORDER BY pop DESC LIMIT 35;");
	}

}