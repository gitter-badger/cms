<?php
/**
 * File page type
 * @version 1.2.1
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class File extends \Gratheon\CMS\ContentModule implements \Gratheon\CMS\Module\Behaviour\Searchable {

	public $name = 'file';

	private function getFileModel() {
		/** @var CMS\Model\File $content_file */
		$content_file = $this->model('File');
		if($this->config('amazon_key', 'image')) {
			$content_file->setAmazonData(
				$this->config('amazon_bucket', 'image'),
				$this->config('amazon_host', 'image'),
				$this->config('amazon_key', 'image'),
				$this->config('amazon_secret', 'image')
			);
		}
		return $content_file;
	}


	public function edit($recMenu = null) {
		$content_file = $this->getFileModel();
//		$content_file_scribd = $this->model('content_file_scribd');

//		$this->add_js('/ext/audio_player/player.js');
		$this->add_js($this->name . '/' . __FUNCTION__ . '.js');

		$parentID = $recMenu->ID;

		if($parentID) {
			$recElement = $content_file->obj('parentID=' . $parentID);
			if($recElement) {
//				$recElement->scribd = $content_file_scribd->obj("fileID='{$recElement->ID}'");
				$recElement->url = $content_file->getURL($recElement);
				//$recElement->root_path     = sys_root . '/res/file/' . $recElement->ID . '.' . $recElement->ext;
				$recElement->scribd_source = 'http://d.scribd.com/ScribdViewer.swf?document_id=' . $recElement->scribd->docID . '&amp;access_key=' . $recElement->scribd->access_key . '&amp;page=1&amp;version=1&amp;viewMode=';

//				if($recElement->ext == 'mp3') {
//					require_once('external_libraries/getid3/getid3.php');
//					$getid3           = new getID3();
//					$getid3->encoding = 'UTF-8';
//					$getid3->Analyze($recElement->root_path);
//
//					$recElement->id3['filesize']        = round($getid3->info['filesize'] / 1024) . ' kB';
//					$recElement->id3['playtime_string'] = $getid3->info['playtime_string'];
//					$recElement->id3['bitrate']         = ($getid3->info['audio']['bitrate'] / 1000) . ' kbps';
//					$recElement->id3['title']           = $getid3->info['tags']['id3v2']['title'] ? implode(',', $getid3->info['tags']['id3v2']['title']) : $getid3->info['tags']['id3v1']['title'];
//					$recElement->id3['artist']          = $getid3->info['tags']['id3v2']['artist'] ? implode(',', $getid3->info['tags']['id3v2']['artist']) : $getid3->info['tags']['id3v1']['artist'];
//				}
			}

			$this->assign('recElement', $recElement);
		}
		else {


			function LoadFiles($dir) {
				$Files = array();
				$It    = opendir($dir);
				if(!$It) {
					die('Cannot list files for ' . $dir);
				}
				while($Filename = readdir($It)) {
					if($Filename == '.' || $Filename == '..') {
						continue;
					}
					$LastModified = filemtime($dir . $Filename);
					$Files[]      = array($dir . $Filename, $LastModified);
				}

				return $Files;
			}

			function DateCmp($a, $b) {
				return ($a[1] < $b[1]) ? -1 : 0;
			}

			function SortByDate(&$Files) {
				usort($Files, 'DateCmp');
			}


			if(is_dir(sys_root . '/res/incoming/')) {
				$arrFiles = LoadFiles(sys_root . '/res/incoming/');

				if($arrFiles) {
					SortByDate($arrFiles);
					foreach($arrFiles as $file) {
						$arrClearFiles[] = end(explode('/', $file[0]));
					}
					//pre($arrClearFiles);
				}

				$this->assign('arrFiles', $arrClearFiles);
			}
		}

//		$this->assign('link_audio_player', sys_url . 'ext/audio_player/player.swf');
		$this->assign('bHideContainer', true);

//		$this->assign('show_URL',true);
	}


	public function insert($parentID) {
		/** @var $content_file \Gratheon\CMS\Model\File */
		$content_file = $this->getFileModel();
		$content_menu = $this->model('Menu');

		$strMenuTitle    = $this->controller->in->post['title'];// ? $this->controller->in->post['title'] : $newElement->filename;
		$oldElement = $content_file->obj('parentID=' . $parentID);

		$this->addFile($oldElement, $parentID, $this->controller->in->post['title']);


//		if($this->config('amazon_key') && $this->copyToCloud($filename, $arrExtraSizes)) {
//			$recElement->amazon_hosted = 1;
//
//			if($this->config('amazon_cloudonly')) {
//				$this->deleteLocalFiles($filename, array_keys($arrExtraSizes));
//			}
//		}


		if($strMenuTitle){
			$content_menu->q('UPDATE ' . $content_menu->table . ' SET title="' . $strMenuTitle . '" WHERE ID=' . $parentID);
		}

//		if($newElement->scribd_show && in_array($strExt, $this->document_scribd_extensions) && $this->config('scribd_api_key')) {
		//$this->addFileToScribd($newElement->ID, $strFileURL, array('title' => $strMenuTitle, 'ext' => $strExt));
//		}
	}


	public function update($parentID) {
		/** @var $content_file \Gratheon\CMS\Model\File */
		$content_menu = $this->model('Menu');
		$content_file = $this->getFileModel();

		$strMenuTitle = $this->controller->in->post['title'] ? $this->controller->in->post['title'] : $_FILES['file']['name'];
		$content_menu->q('UPDATE ' . $content_menu->table . ' SET title="' . $strMenuTitle . '" WHERE ID=' . $parentID);


		$oldElement = $content_file->obj('parentID=' . $parentID);

		if($oldElement->ID) {
			$newElement                = new \Gratheon\Core\Record();
			$newElement->ID            = $oldElement->ID;
			$newElement->scribd_upload = (int)$this->controller->in->post['scribd_upload'];
			$newElement->scribd_show   = (int)$this->controller->in->post['scribd_show'];
			$content_file->update($newElement);
		}
	}


	public function delete($parentID) {
		/** @var $content_file \Gratheon\CMS\Model\File */
		$content_file = $this->getFileModel();

		$arrFile     = $content_file->obj("parentID='$parentID'");
		$strFilePath = sys_root . '/res/file/' . $arrFile->ID . '.' . $arrFile->ext;

		$content_file->delete("ID='{$arrFile->ID}'");

		if(file_exists($strFilePath)) {
			unlink($strFilePath);
		}

		if($this->config('scribd_api_key')) {
			//$this->deleteFromScribd($arrFile->ID);
		}
	}


	public function list_files() {
		/** @var $content_file \Gratheon\CMS\Model\File */
		$content_file = $this->getFileModel();
//		$content_file->getFiles();

		$offset     = $this->controller->in->get('page') > 0 ? $this->per_page * ((int)$this->controller->in->get('page') - 1) : 0;
		$intPerPage = $this->per_page;
		$files      = $content_file->q(
			"SELECT SQL_CALC_FOUND_ROWS t1.*, t2.ID pageID
				FROM content_file t1
				LEFT JOIN content_menu t2 ON t2.ID=t1.parentID
				ORDER BY date_added DESC, ID DESC
				LIMIT $offset , $intPerPage", "array"
		);

		$total_count  = $content_file->count();
		$intPage      = $this->controller->in->get('page') ? (int)$this->controller->in->get('page') : 0;
		$objPaginator = new CMS\Paginator($this->controller->in, $total_count, $intPage, $this->per_page);
		//		$objPaginator->url='#image/list_images/';

		if($files) {
			foreach($files as &$item) {
				$item->link_download = $content_file->getURL($item);
				if($content_file->localFileExists($item)) {
					$item->link_download_local = $content_file->getLocalURL($item);
				}

				if($item->pageID) {
					$item->link_page = '#page/' . $item->pageID;
				}
				else {
					$item->link_delete = sys_url . 'content/call/file/delete_file/' . $item->ID.'?page='.$intPage;
				}

				$iconExt = $item->ext;
				if(in_array($iconExt, array('doc', 'xls', 'ppt'))) {
					$iconExt .= 'x';
				}

				if($iconExt == 'gz') {
					$iconExt = 'zip';
				}
				if($iconExt == 'txt') {
					$iconExt = 'text';
				}

				$item->link_icon = sys_url . 'vendor/Gratheon/CMS/assets/img/icons/filetype/' . $iconExt . '/' . $iconExt . '-16_32.png';

			}
		}

		$this->assign('files', $files);
		$this->assign('objPaginator', $objPaginator);

		return $this->controller->view($this->strWrapperTpl);
	}


	public function delete_file($fileID) {
		$intPage      = $this->controller->in->get('page') ? (int)$this->controller->in->get('page') : 0;
		$content_file = $this->getFileModel();

		$arrFile = $content_file->obj("ID='$fileID'");
		$content_file->delete("ID='$fileID'");
		if($arrFile) {
			$strFilePath = sys_root . '/res/file/' . $arrFile->ID . '.' . $arrFile->ext;

			if(file_exists($strFilePath)) {
				unlink($strFilePath);
			}
		}

		$this->controller->redirect('#file/list_files?page='.$intPage);
	}


	public function get_adminpanel_box_list() {
		$content_file = $this->getFileModel();
		$content_menu = $this->model('Menu');

		$objLastData['data']  = $content_file->arr("1=1 ORDER BY date_added DESC LIMIT 20", "t1.ext, t2.*",
				$content_file->table . ' t1 LEFT JOIN ' . $content_menu->table . ' t2 ON t1.parentID=t2.ID');
		$objLastData['title'] = $this->translate('Files');
		$objLastData['count'] = $content_file->int("1=1", "COUNT(*)");
		$objLastData['width'] = '33%';


		return $objLastData;
	}


	//Public side
	function front_view($ID) {
		$content_file = $this->getFileModel();
		$item         = $content_file->obj("parentID=" . $ID);
//		$url = sys_url . "res/file/" . $item->ID . "." . $item->ext;
		$url = $content_file->getURL($item);

		$this->controller->redirect($url);
	}


	function category_view(&$recEntry) {
		$content_file = $this->getFileModel();

		$recEntry->file = $content_file->obj("parentID='" . $recEntry->ID . "'");

//		if($recEntry->file->ext == 'mp3') {
//			$this->add_js('/ext/audio_player/player.js');
//		}

//		if($this->config('amazon_key', 'Image')) {
//			$content_file->setAmazonData(
//				$this->config('amazon_bucket', 'Image'),
//				$this->config('amazon_host', 'Image'),
//				$this->config('amazon_key', 'Image'),
//				$this->config('amazon_secret', 'Image')
//			);
//		}

		//$arrFile->url=sys_url."res/file/".$arrFile->ID.".".$arrFile->ext;
		$recEntry->url = $content_file->getURL($recEntry->file);
//		$this->getFile($recEntry->file);


	}


	function search_from_public($q) {
		$content_file = $this->getFileModel();
		$content_menu = $this->model('Menu');

		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();

		$arrList = $content_menu->arr("t1.title LIKE '%" . $q . "%' AND t1.module='file'",
			't1.title,t1.parentID, t2.ID fileID',
				$content_menu->table . ' t1 LEFT JOIN ' .
						$content_file->table . ' t2 ON t2.parentID=t1.ID');

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_menu->count();
		$arrEnvelope->title = $this->controller->translate('Files');

		if($arrList) {
			foreach($arrList as &$item) {
				$item->title .= ' &rarr; ' . $content_menu->int($item->parentID, 'title');
				$item->link_view = $menu->getPageURL($item->parentID) . '#f' . $item->fileID;
			}
		}

		$arrEnvelope->list = $arrList;

		return $arrEnvelope;
	}


	function search_from_admin($q) {
	}


	//Custom private methods
	function getArticleData($nodeID) {
		$content_file = $this->getFileModel();

		$arrFiles = $content_file->prepare(['nodeID' => $nodeID])->q(
			"SELECT t1.title,t2.MIME, t1.ID AS nodeID, t2.scribd_show, t2.ext, t2.filename,t2.ID, t2.size, t2.cloud_storage
			FROM content_menu AS t1
			LEFT JOIN content_file AS t2 ON t1.ID=t2.parentID
			WHERE t1.parentID=:nodeID AND t1.module='file'
			ORDER BY t1.position");

		if($this->config('amazon_key', 'Image')) {
			$content_file->setAmazonData(
				$this->config('amazon_bucket', 'Image'),
				$this->config('amazon_host', 'Image'),
				$this->config('amazon_key', 'Image'),
				$this->config('amazon_secret', 'Image')
			);
		}

		if($arrFiles) {
			foreach($arrFiles as &$item) {
				$item->url = $content_file->getURL($item);
//				$this->getFile($item);
			}
		}

		return $arrFiles;
	}


	public function addFile($oldElement=false, $parentID=null, $title=false) {
		$newElement           = new \Gratheon\Core\Record();

		if($parentID){
			$newElement->parentID = $parentID;
		}

		/** @var $content_file \Gratheon\CMS\Model\File */
				$content_file = $this->getFileModel();

		if($_FILES['file']['name']) {
			$newElement->MIME     = $_FILES['file']['type'];
			$newElement->filename = $_FILES['file']['name'];
		}
		elseif(file_exists(sys_root . '/res/incoming/' . $title)) {
			$newElement->MIME     = CMS\Model\Image::getMimeContentType(sys_root . '/res/incoming/' . $this->controller->in->post['title']);
			$newElement->filename = $title;
		}

		$strExt          = strtolower(end(explode('.', $newElement->filename)));
		$newElement->ext = $strExt;

		if($oldElement && $oldElement->ID) {
//			$newElement->ID = $oldElement->ID;
//			$newElement->scribd_upload = (int)$this->controller->in->post['scribd_upload'];
//			$newElement->scribd_show   = (int)$this->controller->in->post['scribd_show'];
//			$content_file->update($newElement);
		}
		else {
//			$newElement->scribd_upload = (int)$this->controller->in->post['scribd_upload'];
//			$newElement->scribd_show   = (int)$this->controller->in->post['scribd_show'];
			$newElement->date_added = 'NOW()';
			$newElement->ID         = $content_file->insert($newElement);
		}


		$filename = $newElement->ID . '.' . $strExt;

		$strFilePath        = sys_root . 'res/file/' . $filename;
		$sourceIncomingFile = sys_root . 'res/incoming/' . $this->controller->in->post['title'];

		if($_FILES['file']['name']) {
			$bool_added = move_uploaded_file($_FILES['file']['tmp_name'], $strFilePath);

		}
		elseif(is_file($sourceIncomingFile)) {
			$bool_added = copy(
				$sourceIncomingFile,
				$strFilePath
			);

			if($bool_added) {
				unlink($sourceIncomingFile);
			}
		}


		$newElement->size = filesize($strFilePath);
		$content_file->update($newElement);
	}

//
//	function getFile(&$item) {
//		$content_file        = $this->getFileModel();
//
//		$content_menu        = $this->model('Menu');
//		$content_file_scribd = $this->model('content_file_scribd');
//		$content_image       = $this->model('Image');
//
//
//		if($item->ext == 'flv') {
//			$item->flash_path = sys_url . '/ext/jwplayer/player-viral.swf';
//			$item->FlashVars  = 'autoHideOther=true';
//			$item->width      = 448;
//			$item->height     = 336;
//			//$item->FlashVars.='&amp;defaultMedia=1';
//			$item->FlashVars .= '&amp;file=' . $item->url;
//
//			$strFile = $content_menu->int(
//				't1.parentID="' . $item->nodeID . '" AND module="image" LIMIT 1',
//				'CONCAT(t2.ID,".",t2.image_format) file ',
//					$content_menu->table . " t1 LEFT JOIN " .
//							$content_image->table . " t2 ON t2.parentID=t1.ID");
//			if($strFile) {
//				$item->FlashVars .= '&amp;image=' . urlencode(sys_url . 'res/image/original/' . $strFile);
//			}
//		}
//
//		$item->scribd = $content_file_scribd->obj("fileID='{$item->ID}'");
//	}

//
//	function file_download() {
//		/** @var $content_file \Gratheon\CMS\Model\File */
//
//		$content_file        = $this->getFileModel();
//		$intFileID    = (int)$this->URI[3];
//
//		$recFile    = $content_file->obj($intFileID);
//		$this->MIME = $recFile->MIME;
//		$this->headers('attachment', $recFile->filename);
//		$fp = fopen(sys_url_resource . 'file/' . $recFile->ID, 'rb');
//		fpassthru($fp);
//		fclose($fp);
//		exit();
//	}

//
//	//Scribd functions
//	private function addFileToScribd($intFileID, $strPath, $arrParams) {
//		/** @var $content_file \Gratheon\CMS\Model\File */
//		$content_file_scribd = $this->model('content_file_scribd');
//
//		set_time_limit(200);
//		$scribd = new Scribd($this->config('scribd_api_key'), $this->config('scribd_secret'));
//
//		$data = $scribd->uploadFromUrl($strPath, $arrParams['ext'], null, null, null, null, null, $this->config('scribd_api_key'));
//		$scribd->changeSettings($data['doc_id'], $arrParams['title']);
//
//		if($data) {
//			$content_file_scribd->insert(array(
//				'fileID'     => $intFileID,
//				'docID'      => $data['doc_id'],
//				'access_key' => $data['access_key']));
//		}
//
//	}
//
//
//	private function deleteFromScribd($intFileID) {
//		$content_file_scribd = $this->model('content_file_scribd');
//
//		require_once('external_services/scribd/Scribd.php');
//		$scribd = new Scribd($this->config('scribd_api_key'), $this->config('scribd_secret'));
//
//		$doc_id = $content_file_scribd->int("fileID='$intFileID'", 'docID');
//		if($doc_id) {
//			$data = $scribd->delete($doc_id);
//		}
//	}

}