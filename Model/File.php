<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 20:32
 *
 * @method content_image_record obj
 */
namespace Gratheon\CMS\Model;

class File extends \Gratheon\Core\Model {
	use ModelSingleton;

	public final function __construct() {
		parent::__construct('content_file');
	}

	private $amazon_bucket;
	private $amazon_host;

	public function getURL($file) {
		$subpath = 'file/' . $file->ID . '.' . $file->ext;

		if(isset($file->cloud_storage) && $file->cloud_storage=='amazon' && $this->amazon_host != '' && $this->amazon_bucket != '') {
			return 'https://' . $this->amazon_host . '/' . $this->amazon_bucket . '/' . $subpath;
		}

		return sys_url . 'res/'.$subpath;
	}

	public function getLocalURL($file) {
		$subpath = 'file/' . $file->ID . '.' . $file->ext;

		return sys_url . 'res/'.$subpath;
	}

	public function localFileExists($file) {
		$subpath = 'file/' . $file->ID . '.' . $file->ext;

		return file_exists(sys_root . 'res/'.$subpath);
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