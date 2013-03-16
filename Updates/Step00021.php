<?php
namespace Gratheon\CMS\Updates;

class Step00021 extends \Gratheon\CMS\Sync{
    var $description='Added amazon image hosting';

    function process(){

        if(!$this->existsTableField('content_image', 'amazon_hosted')){
            $this->ask("ALTER TABLE `content_image`
                     ADD COLUMN `amazon_hosted` TINYINT(1) DEFAULT NULL AFTER `md5`;");
        }

        return $this->bUpdateSuccess;
    }
}



