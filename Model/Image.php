<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 20:32
 *
 * @method content_image_record obj
 */
namespace Gratheon\CMS\Model;

class Image extends \Gratheon\Core\Model {
	use ModelSingleton;

    private $amazon_key = '';
    private $amazon_secret = '';
    private $amazon_bucket ='';
    private $amazon_host = '';

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

	public static function getMimeContentType($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mp3',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

	public function getLastImages($count) {
		$images        = $this->q(
			"SELECT SQL_CALC_FOUND_ROWS *
					FROM content_image
					ORDER BY date_added DESC, ID DESC
					LIMIT  $count", "array"
		);

		//		$objPaginator->url='#image/list_images/';

		if($images) {
			foreach($images as &$item) {
				$item->image_link = $this->getURL($item, 'square');
			}
		}
		return $images;
	}

}