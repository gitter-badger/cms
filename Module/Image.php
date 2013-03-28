<?php
/**
 * Image page object
 * @version 1.1.2
 * @uses ext/jcrop
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Image extends CMS\ContentModule
	implements CMS\Module\Behaviour\Searchable,
	CMS\Module\Behaviour\VisibleOnDashboard,
	CMS\Module\Behaviour\Cloudy,
	CMS\Module\Behaviour\Embeddable {

	public $static_methods = array('front_rss', 'resized');
	public $name = 'image';
	public $allowedExtensions = array('jpg', 'gif', 'png', 'bmp', 'jpeg');
	public $exifExtensions = array('jpg', 'jpeg', 'tiff');
	public $per_page = 40;

	protected $arrFolders = array('square', 'thumb', 'original');


	public function insert($parentID) {
		$content_menu = $this->model('Menu');

		$filename = $_FILES['file']['name'];
		$aFile    = explode('.', $filename);
		if(count($aFile) > 1) {
			unset($aFile[count($aFile) - 1]);
			$filename = implode('.', $aFile);
		}

		$strMenuTitle = $this->controller->in->post['title'] ? $this->controller->in->post['title'] : $filename;
		//$content_menu->q('UPDATE '.$content_menu->table.' SET title="'.$strMenuTitle.'" WHERE ID='.$parentID);

		$intElement = $this->addFile($parentID);
		$content_menu->q(
			"UPDATE content_menu
			SET title='{$strMenuTitle}', elementID='{$intElement}'
			WHERE ID='{$parentID}'"
		);


		$this->saveImageRating($this->controller->in->post['xrate'], $parentID);

		$content_image = $this->model('Image');

		if($_GET['returnHTML']) {
			echo '<img rel="' . $parentID . '" src="' . $content_image->getURL($content_image->obj($intElement), 'thumb') . '" />';
		}
	}


	public function addFile($parentID = null, $arrExtraSizes = array(), $strField = 'file', $key = null) {
		$content_menu  = $this->model('Menu');
		$content_image = $this->model('Image');
		/** @var CMS\Model\Image $content_image */

		$arrFile = $this->getFileArray($strField, $key);

		$strExt = strtolower(end(explode('.', $arrFile['name'])));
//		$intCutPosition = isset($this->controller->in->post['cut_position']) ? $this->controller->in->post['cut_position'] : 1;

		if(!in_array($strExt, $this->allowedExtensions)) {
			throw new \Exception("Unsupported image filetype");
		}

		//Add to database
		$recElement = new \Gratheon\Core\Record();
		if($parentID) {
			$recElement->parentID = $parentID;
		}

		$recElement->filename       = $arrFile['name'];
		$recElement->date_added     = 'NOW()';
		$recElement->float_position = isset($this->controller->in->request['float_position']) ? $this->controller->in->request['float_position'] : $this->config('float_position');
		$recElement->thumbnail_size = isset($this->controller->in->post['thumbnail_size']) ? $this->controller->in->post['thumbnail_size'] : $this->config('thumbnail_size');
		$recElement->thumbnail_type = isset($this->controller->in->post['thumbnail_type']) ? $this->controller->in->post['thumbnail_type'] : $this->config('thumbnail_type');
		$recElement->ID             = $content_image->insert($recElement);

		$filename = $recElement->ID . '.' . $strExt;

		$strOriginalFile = $this->getOriginalFilePath($filename);

		//move the actual file
		$bool_added = false;
		if($_FILES[$strField]['name']) {
			$bool_added = move_uploaded_file($arrFile['tmpfile'], $strOriginalFile);
		}
		elseif(file_exists($arrFile['tmpfile'])) {
			$content_image->copyTmpFile($arrFile['tmpfile'], $filename);
		}

		if($bool_added) {
			//Update size info


			$arrImageData = getimagesize($strOriginalFile);

			$recElement->image_format = $strExt; //$ArrImageTypes[$arrImageData[2]];
			$recElement->width        = $arrImageData[0];
			$recElement->height       = $arrImageData[1];
			$content_image->update($recElement);

			//Generate thumbnails
			$this->resizeImage($filename, $recElement->thumbnail_size, $arrExtraSizes);


			//Copy to cloud
			if($this->config('amazon_key') && $this->copyToCloud($filename)) {
				$recElement->cloud_storage = 'amazon';
			}

			$content_image->update($recElement);


			if(in_array($strExt, $this->exifExtensions)) {
				$recElement->EXIF = serialize(exif_read_data($strOriginalFile));
				$content_image->update($recElement);
			}


			if($this->config('amazon_cloudonly')) {
				$this->deleteLocalFiles($filename, array_keys($arrExtraSizes));
			}

			return $recElement->ID;
		}
		else {
			$content_image->delete('ID=' . $recElement->ID);
			$content_menu->delete('ID=' . $parentID);
			throw new \Exception('File moving failed from ' . $arrFile['tmpfile']);
		}
	}


	private function getFileArray($strField, $key) {
		$arrFile = array();

		if($_FILES[$strField]['name']) {
			if(!is_null($key)) {
				$arrFile['name']     = $_FILES[$strField]['name'][$key];
				$arrFile['type']     = $_FILES[$strField]['type'][$key];
				$arrFile['tmp_name'] = $_FILES[$strField]['tmp_name'][$key];
				$arrFile['error']    = $_FILES[$strField]['error'][$key];
				$arrFile['size']     = $_FILES[$strField]['size'][$key];
				$arrFile['tmpfile']  = & $_FILES[$strField]['tmp_name'][$key];
			}
			else {
				$arrFile            = & $_FILES[$strField];
				$arrFile['tmpfile'] = $strFileTmpName = & $_FILES[$strField]['tmp_name'];
			}
		}
		elseif(file_exists($this->getIncomingFilePath($this->controller->in->post['title']))) {
			$arrFile['tmpfile'] = $this->getIncomingFilePath($this->controller->in->post['title']);

			$arrFile['name'] = $this->controller->in->post['title'];
			$arrFile['type'] = CMS\Model\Image::getMimeContentType($arrFile['tmpfile']);
			$arrFile['size'] = filesize($arrFile['tmpfile']);
		}

		return $arrFile;
	}


	private function getIncomingFilePath($title) {
		return sys_root . '/res/incoming/' . $title;
	}


	public function update($parentID) {
		$content_image = $this->getImageModel();
		$content_menu  = $this->model('Menu');

		$recMenu    = $content_menu->obj($parentID);
		$recElement = $content_image->obj($recMenu->elementID);
		$strExt     = $recElement->image_format;

		$recElement->date_added     = $this->controller->in->post['date_added'];
		$recElement->float_position = $this->controller->in->post['float_position'] ? $this->controller->in->post['float_position'] : $this->config('float_position');
		$recElement->thumbnail_size = $this->controller->in->post['thumbnail_size'] ? $this->controller->in->post['thumbnail_size'] : $this->config('thumbnail_size');
		$recElement->thumbnail_type = $this->controller->in->post['thumbnail_type'] ? $this->controller->in->post['thumbnail_type'] : $this->config('thumbnail_type');
		$filename                   = $recElement->ID . '.' . $strExt;

		$arrCropPositions = explode(':', $this->controller->in->post['crop_position']);


		$copiedFromCloud = false;
		if($this->config('cloud_hosting') && $this->config('amazon_cloudonly')) {
			$copiedFromCloud = $this->copyFromCloud($filename);
		}

		$this->resizeImage($filename, $recElement->thumbnail_size, array(), $arrCropPositions);


		$content_image->update($recElement, 'parentID=' . $parentID);


		//Copy to cloud

		if($copiedFromCloud) {
			$this->deleteFromCloud($filename);
			$this->copyToCloud($filename);
			$recElement->cloud_storage = 'amazon';

			if($this->config('cloud_hosting')){
				$this->deleteLocalFiles($filename, $this->arrFolders);
			}
		}
		$content_image->update($recElement, 'parentID=' . $parentID);


		$this->saveImageRating($this->controller->in->post['xrate'], $parentID);
	}


	public function copyFromCloud($filename) {
		if($this->config('amazon_key')) {
			$cloudAdapter = new \Gratheon\CMS\Service\AmazonService(
				$this->config('amazon_bucket'),
				$this->config('amazon_key'),
				$this->config('amazon_secret')
			);

			$targetPath = sys_root.'res/image/original/' . $filename;

			$cloudAdapter->copyFileFromCloud(
				$targetPath,
				$this->getOriginalFilePath($filename)
			);

			return is_file($targetPath);
		}
		return false;
	}


	public function deleteFromCloud($filename, $arrExtraSizes = array()) {
		if($this->config('amazon_key')) {

			$cloudAdapter = new \Gratheon\CMS\Service\AmazonService(
				$this->config('amazon_bucket'),
				$this->config('amazon_key'),
				$this->config('amazon_secret')
			);

			$cloudAdapter->deleteFile('image/original/' . $filename);
			$cloudAdapter->deleteFile('image/square/' . $filename);
			$cloudAdapter->deleteFile('image/thumb/' . $filename);

			if($arrExtraSizes) {
				foreach($arrExtraSizes as $folder => $size) {
					$cloudAdapter->deleteFile('image/' . $folder . '/' . $filename);
				}
			}
		}
	}


	private function saveImageRating($arrRates, $parentID) {
		$content_menu_rating = $this->model('content_menu_rating');
		$content_menu_rating->delete('parentID=' . $parentID);

		if($arrRates) {
			foreach($arrRates as $k => $v) {
				if($v > 0) {
					$content_menu_rating->insert(array(
							'parentID'  => $parentID,
							'xrate_tag' => $k,
							'rating'    => $v
						)
					);
				}
			}
		}
	}


	public function edit($recMenu = null) {

		$content_image       = $this->getImageModel();
		$content_menu_rating = $this->model('content_menu_rating');

		$parentID = $recMenu->ID;
		$this->assign('bHideContainer', true);
		$this->assign('bHideTags', true);
		$this->assign('arrExtraTabs', array(
			3 => array(
				'title'    => 'Semantics',
				'template' => 'ModuleBackend/image/edit.tab_semantic.tpl'
			)
		));


		if($parentID) {
			if($recMenu->elementID) {
				$recElement = $content_image->obj($recMenu->elementID);
			}
			else {
				$recElement = $content_image->obj("parentID='" . $recMenu->ID . "'");
			}

			$recElement->link_image     = $content_image->getURL($recElement, 'original');
			$recElement->link_square    = $content_image->getURL($recElement, 'square');
			$recElement->link_rectangle = $content_image->getURL($recElement, 'thumb');

			//sys_url . 'front/call/image/resized/' . $recElement->ID . '.jpg?w=600&h=500&src=original&ramd=' . rand(1, 9999); //sys_url.'res/image/thumb/'.$recElement->ID.'.'.$recElement->image_format.'?ramd='.rand(1,9999);


			$recElement->xrate = $content_menu_rating->map("parentID='" . $recMenu->ID . "'", "xrate_tag, rating");
			if(strlen($recElement->EXIF) > 2) {
				$recElement->EXIF = unserialize($recElement->EXIF);
			}
			$this->assign('recElement', $recElement);
		}
	}


	public function delete($parentID) {
		$content_image       = $this->model('Image');
		$content_menu_rating = $this->model('content_menu_rating');

		$recElement = $content_image->obj('parentID=' . $parentID);

		//if($content_menu->int("elementID='$recElement->ID' AND parentID<>'$parentID'", "COUNT(ID)") == 1)
		{
			$this->deleteByID($recElement->ID);
			$content_menu_rating->delete('parentID=' . $parentID);
		}
	}


	public function deleteByID($ID, $arrExtraFolders = array()) {
		$content_image = $this->model('Image');

		$recElement = $content_image->obj($ID);
		$filename   = $recElement->ID . '.' . $recElement->image_format;



		$arrFolders = $this->arrFolders;
		if($arrExtraFolders) {
			$arrFolders = array_merge($arrFolders, $arrExtraFolders);
		}

		$this->deleteLocalFiles($filename, $arrFolders);
		$this->deleteFromCloud($filename, $arrExtraFolders);

		$content_image->delete('ID=' . $recElement->ID);
	}


	public function get_adminpanel_box_list() {
		$content_image = $this->getImageModel();
		$content_menu  = $this->model('Menu');

		$objLastData['data'] = $content_menu->arr(
			"t1.module='image' ORDER BY t1.date_added DESC LIMIT 20",
			't1.title,t2.ID,t1.parentID, t2.image_format, t1.ID nodeID, t2.cloud_storage',
				$content_menu->table . ' t1 LEFT JOIN ' .
						$content_image->table . ' t2 ON t2.ID=t1.elementID');

		if($objLastData['data']) {
			foreach($objLastData['data'] as &$item) {
				$item->link_square_small = $item->link_square = $content_image->getSquareURL($item);
				//sys_url . 'front/call/image/resized/' . $item->ID . '.jpg?w=65&h=65&src=square';
			}
		}

		$objLastData['title'] = $this->translate('Images');
		$objLastData['count'] = $content_image->int("1=1", "COUNT(ID)");

		return $objLastData;
	}


	public function list_images() {

		$content_image = $this->getImageModel();

		$offset = $_GET['page'] > 0 ? $this->per_page * ((int)$_GET['page'] - 1) : 0;
		$intPerPage = $this->per_page;
		$images        = $content_image->q(
			"SELECT SQL_CALC_FOUND_ROWS *
			FROM content_image
			ORDER BY date_added DESC, ID DESC
			LIMIT {$offset},{$intPerPage}", "array"
		);

		$total_count = $content_image->count();
		$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;
		$objPaginator = new CMS\Paginator($this->controller->in, $total_count, $intPage, $this->per_page);
		$objPaginator->url='#image/list_images/';

		if($images) {
			foreach($images as &$item) {
				$item->image_link = $content_image->getURL($item, 'thumb');

			}
		}

		$this->assign('images', $images);
		$this->assign('objPaginator', $objPaginator);

		return $this->controller->view($this->strWrapperTpl);
	}


	private function getImageModel() {
		/** @var CMS\Model\Image $content_image */
		$content_image = $this->model('Image');
		if($this->config('amazon_key')) {
			$content_image->setAmazonData($this->config('amazon_bucket'), $this->config('amazon_host'), $this->config('amazon_key'), $this->config('amazon_secret'));
		}
		return $content_image;
	}


	public function edit_image($id){
		$id = intval($id);
		$content_image = $this->getImageModel();
		$image = $content_image->obj($id);
		$image->source = $content_image->getOriginalURL($image);
		$this->assign('image', $image);

		$content_menu = $this->model('Menu');
		$pages = $content_menu->arr("(elementID='$id' OR ID='{$image->parentID}') AND module='image'");
		$this->assign('pages', $pages);


		return $this->controller->view($this->strWrapperTpl);
	}


	public function delete_image($id){
		$this->deleteByID($id);
		$this->controller->redirect('#image/list_images/&page='.$_GET['page']);
	}


	public function addURLFile($strURL, $parentID = null, $arrExtraSizes = null) {
		$content_image = $this->model('Image');

		$strExt = strtolower(end(explode('.', $strURL)));

		if(!in_array($strExt, $this->allowedExtensions)) {
			throw new \Exception("Invalid filetype");
		}

		$recElement = new \Gratheon\Core\Record();
		if($parentID) {
			$recElement->parentID = $parentID;
		}

		$recElement->filename       = end(explode('/', $strURL));
		$recElement->date_added     = 'NOW()';
		$recElement->thumbnail_size = $this->config('thumbnail_size'); //100;
		$recElement->thumbnail_type = $this->config('thumbnail_type'); //'square';
		$recElement->ID             = $content_image->insert($recElement);

		$filename = $recElement->ID . '.' . $recElement->image_format;

		//Add file to res folder
		$strOriginalFile = $this->getOriginalFilePath($filename);

		$arrImageData = getimagesize($strOriginalFile);

		$recElement->image_format = $strExt; //$ArrImageTypes[$arrImageData[2]];
		$recElement->width        = $arrImageData[0];
		$recElement->height       = $arrImageData[1];
		$content_image->update($recElement);


		$copied = copy($strURL, $strOriginalFile);

		if($copied) {
			$this->resizeImage($filename, $recElement->thumbnail_size, $arrExtraSizes);

			if($this->config('amazon_key') && $this->copyToCloud($filename, $arrExtraSizes)) {
				$recElement->cloud_storage = 'amazon';

				if($this->config('amazon_cloudonly')) {
					$this->deleteLocalFiles($filename, array_keys($arrExtraSizes));
				}
			}


			$content_image->update($recElement);

			return $recElement->ID;
		}
		else {
			$content_image->delete($recElement->ID);
			throw new \Exception('File copying failed');
		}
	}


	private function getOriginalFilePath($filename) {
		return sys_root . 'res/image/original/' . $filename;
	}


	private function resizeImage($filename, $maxSize, $arrExtraSizes = array(), $arrCropPositions = array()) {

		//Add file to res folder
		$strOriginalFile = $this->getOriginalFilePath($filename);
		$strThumbFile    = $this->getThumbFilePath($filename);
		$strSquareFile   = $this->getSquareFilePath($filename);
		$strInlineFile   = $this->getInlineFilePath($filename);

		$oConvertor = new \Gratheon\CMS\Model\ImageConvertor();

		$oConvertor->loadImage($strOriginalFile);

		//cut rectangle
		$oConvertor->resizeRectangle($maxSize, $maxSize);
		$oConvertor->outputImage($strThumbFile);


		//cut square
		$oConvertor->cutSquare($maxSize);
		$oConvertor->outputImage($strSquareFile);

		//custom sizes
		if(is_array($arrExtraSizes)) {
			foreach($arrExtraSizes as $folder => $arrSizes) {
				if(!$arrSizes['width']) {
					$arrSizes['width'] = $arrSizes['thumbnail_size'];
				}

				if(!$arrSizes['height']) {
					$arrSizes['height'] = $arrSizes['thumbnail_size'];
				}

				$oConvertor->resizeRectangle($arrSizes['width'], $arrSizes['height']);
				$oConvertor->outputImage($this->getCustomSizeFilePath($folder, $filename));
			}
		}

		//custom cropping
		if(count($arrCropPositions) > 2) {
			list($width, $height) = $oConvertor->getOriginalSize();
			$oConvertor->setCropPosition(
				intval($width * $arrCropPositions[4] / $arrCropPositions[0]),
				intval($height * $arrCropPositions[5] / $arrCropPositions[1]),
				intval($width * $arrCropPositions[6] / $arrCropPositions[0]),
				intval($height * $arrCropPositions[7] / $arrCropPositions[1])
			);

			$oConvertor->resizeRectangle(
				intval($width * $arrCropPositions[2] / $arrCropPositions[0]),
				intval($height * $arrCropPositions[3] / $arrCropPositions[1]),
				false,
				false
			);

			$oConvertor->outputImage($strInlineFile);
		}


		//
//		$oConvertor->loadImage($strOriginalFile);
//
//		if($intOriginalSize) {
//			$oConvertor->resizeRectangle($intOriginalSize, $intOriginalSize);
//			$oConvertor->outputImage($strOriginalFile);
//		}
//
//		$oConvertor->resizeRectangle($recElement->thumbnail_size, $recElement->thumbnail_size);
//		$oConvertor->outputImage(sys_root . 'res/image/thumb/' . $recElement->ID . '.' . $strExt);
//
//
//		$oConvertor->cutSquare($recElement->thumbnail_size, $intCutPosition);
//		$oConvertor->outputImage(sys_root . 'res/image/square/' . $recElement->ID . '.' . $strExt);
//
//		if($arrExtraSizes) {
//			foreach($arrExtraSizes as $folder => $arrSizes) {
//				if($arrSizes['thumbnail_size']) {
//					$oConvertor->resizeRectangle($arrSizes['thumbnail_size'], $arrSizes['thumbnail_size']);
//					$oConvertor->outputImage(sys_root . 'res/image/' . $folder . '/' . $recElement->ID . '.' . $strExt);
//				}
//				elseif($arrSizes['width'] || $arrSizes['height']) {
//					$oConvertor->resizeRectangle($arrSizes['width'], $arrSizes['height']);
//					$oConvertor->outputImage(sys_root . 'res/image/' . $folder . '/' . $recElement->ID . '.' . $strExt);
//				}
//			}
//		}
	}


	private function getThumbFilePath($filename) {
		return sys_root . 'res/image/thumb/' . $filename;
	}


	private function getSquareFilePath($filename) {
		return sys_root . 'res/image/square/' . $filename;
	}


	private function getInlineFilePath($filename) {
		return sys_root . 'res/image/inline/' . $filename;
	}


	private function getCustomSizeFilePath($folder, $filename) {
		return sys_root . 'res/image/' . $folder . '/' . $filename;
	}


	public function copyToCloud($filename, $arrExtraSizes = array()) {
		if($this->config('amazon_key')) {
			$cloudAdapter = new \Gratheon\CMS\Service\AmazonService(
				$this->config('amazon_bucket'),
				$this->config('amazon_key'),
				$this->config('amazon_secret')
			);


			$cloudAdapter->copyFile($this->getOriginalFilePath($filename), 'image/original/' . $filename);
			$cloudAdapter->copyFile($this->getSquareFilePath($filename), 'image/square/' . $filename);
			$cloudAdapter->copyFile($this->getThumbFilePath($filename), 'image/thumb/' . $filename);

			if($arrExtraSizes) {
				foreach($arrExtraSizes as $folder => $size) {
					$cloudAdapter->copyFile($this->getCustomSizeFilePath($folder, $filename), 'image/' . $folder . '/' . $filename);
				}
			}
			return true;
		}
		return false;
	}


	private function deleteLocalFiles($file, $arrFolders) {
		foreach($arrFolders as $folder) {
			if(file_exists(sys_root . 'res/image/' . $folder . '/' . $file)) {
				unlink(sys_root . 'res/image/' . $folder . '/' . $file);
			}
		}
	}


	public function search_from_public($q) {
		$content_menu  = $this->model('Menu');
		$content_image = $this->getImageModel();

		$arrImages = $content_menu->arr("t1.title LIKE '%" . $q . "%' AND t1.module='image'",
			't1.title,t2.ID, t2.image_format, t2.cloud_storage',
				$content_menu->table . ' t1 LEFT JOIN ' .
						$content_image->table . ' t2 ON t2.parentID=t1.ID');

		$arrEnvelope           = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count    = $content_menu->count();
		$arrEnvelope->title    = $this->controller->translate('Images');
		$arrEnvelope->template = 'ModuleFrontend/image/front_search.tpl';

		foreach($arrImages as &$item) {
			$item->link_square = $content_image->getURL($item, 'square');
			$item->link_view   = $content_image->getURL($item);
		}

		$arrEnvelope->list = $arrImages;

		return $arrEnvelope;
	}


	public function search_from_admin($q) {
		$content_menu  = $this->model('Menu');
		$content_image = $this->getImageModel();

		$arrImages = $content_menu->arr(
			"t1.title LIKE '%" . $q . "%' AND t1.module='image'",
			't1.title,t2.ID, t2.image_format, t1.ID nodeID, t2.cloud_storage',
			'content_menu t1 LEFT JOIN content_image t2 ON t2.parentID=t1.ID');

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_menu->count();
		$arrEnvelope->title = $this->translate('Images');

		foreach($arrImages as &$item) {
			$item->link_square = $content_image->getSquareURL($item);
		}

		$arrEnvelope->list = $arrImages;

		return $arrEnvelope;
	}


	public function category_view(&$recEntry) {
		$content_image = $this->model('Image');

		$arrImage                = $content_image->obj("parentID='" . $recEntry->ID . "'");
		$arrImage->link_original = sys_url . "res/image/original/" . $arrImage->ID . "." . $arrImage->image_format;
		$arrImage->link_thumb    = sys_url . "res/image/square/" . $arrImage->ID . "." . $arrImage->image_format;
		$recEntry->image         = $arrImage;
	}


	public function front_rss() {
		$sys_languages = $this->model('sys_languages');
		$content_menu  = $this->model('Menu');
		$content_image = $this->model('Image');


		require_once('vendor/tot-ra/feedcreator/feedcreator.class.php');

		$langID = (int)$_GET['lang'];
		if(!$langID) {
			$langID = $sys_languages->int("is_default=1", 'ID');
		}

		$groupID = $this->controller->user->data['groupID'];

		$strSQL = "
			SELECT t1.*,UNIX_TIMESTAMP(t1.date_added) as unix_added, t2.ID as imageID, t2.image_format
			FROM content_menu t1
			INNER JOIN content_menu article ON t1.parentID = article.ID
			INNER JOIN content_menu_rights articleright ON article.ID = articleright.pageID AND articleright.rightID=2 AND articleright.groupID = '$groupID'
			INNER JOIN content_image t2 ON t2.parentID=t1.ID

			WHERE t1.langID='" . $langID . "' AND t1.module IN ('" . $this->name . "')
			ORDER BY date_added DESC LIMIT 50";


		$arrEntries = $content_menu->q($strSQL);

		$rss = new \UniversalFeedCreator();
		$rss->useCached('RSS2.0', 'images.rss');

		$rss->title                     = sys_title . ' / Image Feed'; //"Artjom Kurapov";
		$rss->description               = sys_title . ' / Image Feed'; //"Personal Blog";
		$rss->descriptionTruncSize      = 500;
		$rss->descriptionHtmlSyndicated = true;
		$rss->link                      = sys_url;
		$rss->syndicationURL            = sys_url . $_SERVER["PHP_SELF"];
		$rss->XMLNS                     = 'xmlns:media="http://search.yahoo.com/mrss"';

		if(is_array($arrEntries)) {
			foreach($arrEntries as $key => $item) {
				$recEntry                 = new \FeedItem();
				$recEntry->guid           = $recEntry->link = sys_url . 'article/' . $item->parentID;
				$recEntry->title          = $item->title;
				$recEntry->description    = '<img src="' . sys_url . 'res/image/original/' . $item->imageID . '.' . $item->image_format . '" alt="' . $item->title . '"/>';
				$recEntry->date           = date('r', $item->unix_added);
				$recEntry->thumbnail      = sys_url . 'res/image/thumb/' . $item->imageID . '.' . $item->image_format;
				$recEntry->original_image = sys_url . 'res/image/original/' . $item->imageID . '.' . $item->image_format;
				//$recEntry->link = sys_url.'res/image/original/'.$item->imageID;
				$rss->addItem($recEntry);
			}
		}

		echo $rss->createFeed("RSS2.0");
	}


	public function resized() {
		$content_image = $this->model('Image');

		$id     = (int)current(explode('.', $this->controller->in->URI[5]));
		$width  = (int)$_GET['w'] ? (int)$_GET['w'] : 65;
		$height = (int)$_GET['h'] ? (int)$_GET['h'] : 65;
		$strSrc = $_GET['src'] == 'square' ? 'square' : 'original';

		if(!$id) {
			die('no image id set');
		}

		$this->controller->MIME = 'image/jpeg';

		//$id = $_GET['id'];
		$objImage    = $content_image->obj($id);
		$link_square = sys_root . 'res/image/' . $strSrc . '/' . $objImage->ID . '.' . $objImage->image_format;

		if(!$objImage) {
			die('no image found');
		}

		$oConvertor = new \Gratheon\CMS\Model\ImageConvertor();
		$oConvertor->loadImage($link_square);
		$oConvertor->resizeRectangle($width, $height);
		//$oConvertor->cutSquare($width, 2);
		echo $oConvertor->outputImage('browser');
	}


	public function getArticleData($parentID) {
		$this->add_js('image/image.js');

		$content_menu = $this->model('content_menu');


		$images = $content_menu->q(
			"SELECT t1.title, t2.ID,t2.float_position,t2.image_format,t2.thumbnail_type, t1.ID parentID, t2.cloud_storage
			FROM content_menu as t1
			LEFT JOIN content_image as t2 ON t1.ID=t2.parentID
			WHERE t2.float_position IN ('bottom','right') AND t1.parentID='$parentID' AND t1.module='image'
			ORDER BY t1.position"
		);


		$return = array();
		if($images) {
			foreach($images as $image) {
				$return[$image->float_position][] = $this->attachImageInfo($image);
			}
		}
		return $return;
	}


	function attachImageInfo($image) {
		$content_image       = $this->getImageModel();
		$content_menu_rating = $this->model('content_menu_rating');

		$arrRatings    = $content_menu_rating->map("parentID='{$image->parentID}'", "xrate_tag, rating");
		$image->rating = '';
		if($arrRatings) {
			foreach($arrRatings as $k => $v) {
				$image->rating .= "data-xrate-" . $k . "='" . $v['rating'] . "' ";
			}
		}


		/** @var CMS\Model\Image $content_image */

		$image->link_original  = $content_image->getURL($image);
		$image->link_square    = $content_image->getURL($image, 'square');
		$image->link_rectangle = $content_image->getURL($image, 'thumb');
		$image->link_icon      = $content_image->getURL($image, $image->thumbnail_type);
		return $image;

	}


	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$ID       = $menu->elementID;

		$content_image = $this->model('Image');
		$objImage      = $content_image->obj("parentID='$parentID' OR ID='$ID'", 'ID, image_format, cloud_storage');
		return $content_image->getURL($objImage, 'thumb');
	}


	public function decodeEmbeddable($menu) {
//		$parentID = $menu->ID;
//		$ID       = $menu->elementID;

		return '';
	}

}