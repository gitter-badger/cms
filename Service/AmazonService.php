<?php
/**
 * @author Artjom Kurapov
 * @since 12.07.12 22:59
 */

namespace Gratheon\CMS\Service;

require_once 'AmazonService/AmazonS3.php';

class AmazonService{

    public function __construct($bucket, $key, $secret){
        $this->amazon_bucket = $bucket;
        $this->amazon_key = $key;
        $this->amazon_secret = $secret;

        $this->link = new \AmazonS3($this->amazon_key, $this->amazon_secret);
    }

    public function copyFile($subPath){
        if($this->link->getObjectInfo($this->amazon_bucket, $subPath)){
            return false;
        }

        $link = $this->link;
        $this->link->putObjectFile( sys_root."res/".$subPath, $this->amazon_bucket, $subPath, $link::ACL_PUBLIC_READ);

        return true;
    }

    public function deleteFile($subPath){
        if(!$this->link->getObjectInfo($this->amazon_bucket, $subPath)){
            return false;
        }

        $this->link->deleteObject( $this->amazon_bucket, $subPath);
        return true;
    }
}