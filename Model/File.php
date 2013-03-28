<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 20:32
 *
 * @method content_image_record obj
 */
namespace Gratheon\CMS\Model;

class File extends \Gratheon\Core\Model {
	private static $instance;

	private $amazon_bucket;
	private $amazon_host;


	/**
	 * @return \Gratheon\CMS\Model\File
	 */
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c              = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}


	function __construct() {
		parent::__construct('content_file');
	}


	public function getURL($file) {
		$subpath = 'file/' . $file->ID . '.' . $file->ext;

		if($this->amazon_host != '' && $this->amazon_bucket != '') {
			return 'https://' . $this->amazon_host . '/' . $this->amazon_bucket . '/' . $subpath;
		}

		return sys_url . 'res/'.$subpath;
	}


	public function setAmazonData($bucket, $host, $key = '', $secret = '') {
		if($key) {
			$this->amazon_key = $key;
		}
		if($secret) {
			$this->amazon_secret = $secret;
		}

		$this->amazon_bucket = $bucket;
		$this->amazon_host   = $host;
	}
}