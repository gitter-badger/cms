<?php
namespace Gratheon\CMS\Updates;

class Step00011 extends \Gratheon\CMS\Sync{
	var $description='Video player support';

	function process(){

		if($this->existsTable('content_external_video') && $this->existsTableField('content_external_video','use_custom_player')){
			$this->q("alter table `content_external_video` change `use_custom_player` `custom_player` varchar (50)  NULL ");
		}

		return $this->bUpdateSuccess;
	}

}