<?php
namespace Gratheon\CMS\Updates;

class Step00026 extends \Gratheon\CMS\Sync{
    var $description='Image exif';

    function process(){

		$this->ask("ALTER TABLE `content_image`     ADD COLUMN `EXIF` TEXT CHARSET utf8 NULL AFTER `amazon_hosted`;");
		$this->ask("ALTER TABLE `content_file`     ADD COLUMN `cloud_storage` VARCHAR(15) NULL AFTER `scribd_show`;");
		$this->ask("ALTER TABLE `content_image`     CHANGE `amazon_hosted` `cloud_storage` VARCHAR(15) NULL ;");
		$this->ask("UPDATE content_image SET cloud_storage = 'amazon' WHERE cloud_storage='1'");
	}
}