<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 20:32
 *
 * @method content_image_record obj
 */
namespace Gratheon\CMS\Model;

class Image extends \Gratheon\Core\Model {
    private static $instance;

    private $amazon_key = '';
    private $amazon_secret = '';
    private $amazon_bucket ='';
    private $amazon_host = '';

    /**
     * @return Image
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    function __construct() {
        parent::__construct('content_image');
    }


	public function setAmazonData($bucket, $host, $key='', $secret=''){
		if($key){
			$this->amazon_key = $key;
		}
		if($secret){
			$this->amazon_secret = $secret;
		}

		$this->amazon_bucket = $bucket;
		$this->amazon_host = $host;
	}


    public function getURL($image, $size='original', $relative=true) {

		if(!$image){
			return null;
		}
        $subpath = 'image/'.$size.'/' . $image->ID . '.' . $image->image_format;

        if($image->cloud_storage=='amazon' && $this->amazon_host!='' && $this->amazon_bucket!=''){
            return 'https://'.$this->amazon_host.'/'.$this->amazon_bucket.'/'.$subpath;
        }

        return ($relative ? sys_url_rel : sys_url) . 'res/'.$subpath;
    }

    public function getOriginalURL($image) {
        $subpath = 'image/original/' . $image->ID . '.' . $image->image_format;

        if($image->cloud_storage=='amazon'){
            return 'https://'.$this->amazon_host.'/'.$this->amazon_bucket.'/'.$subpath;
        }
        return sys_url . 'res/'.$subpath;
    }

    public function getSquareURL($image) {
        $subpath = 'image/square/' . $image->ID . '.' . $image->image_format;

        if($image->cloud_storage=='amazon'){
            return 'https://'.$this->amazon_host.'/'.$this->amazon_bucket.'/'.$subpath;
        }

        return sys_url . 'res/'.$subpath;
    }

    public function getRectangleURL($image) {
        $subpath = 'image/thumb/' . $image->ID . '.' . $image->image_format;

        if($image->cloud_storage=='amazon'){
            return 'https://'.$this->amazon_host.'/'.$this->amazon_bucket.'/'.$subpath;
        }

        return sys_url . 'res/'.$subpath;
    }

    public function getResizedURL($image, $w = 600, $h = 500, $src='original') {
		$this->getSquareURL($image);
        //return sys_url . 'front/call/image/resized/' . $image->ID . '.jpg?w=' . $w . '&h=' . $h . '&src='.$src.'&ramd=' . rand(1, 9999);
    }

    /**
     * Copies temp file
     *
     * @param $strFileTmpName
     * @param $resultFileName
     * @return bool
     */
    public function copyTmpFile($strFileTmpName, $resultFileName){
        $strOriginalFile = sys_root . 'res/image/original/' . $resultFileName;

        $bool_added = copy($strFileTmpName, $strOriginalFile);
        if ($_POST['title']) {
            unlink($strFileTmpName);
        }

        return move_uploaded_file($strFileTmpName, $strOriginalFile);
    }
    /*
   final public function __destruct(){
       unset(self::$instance);
   }
   */
}