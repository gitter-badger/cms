<?php
/**
 * Youtube and other external videos
 *
 * @version 1.3
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Video extends \Gratheon\CMS\ContentModule
	implements \Gratheon\CMS\Module\Behaviour\Searchable, \Gratheon\CMS\Module\Behaviour\Embeddable {

	public $models = array('content_external_video', 'content_menu');
	public $name = 'video';
	
	private $model_name = 'ExternalVideo';


	public function insert($parentID) {
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		$recElement                = new \Gratheon\Core\Record();
		$recElement->parentID      = $parentID;
		$recElement->site          = $this->controller->in->post['site'];
		$recElement->site_id       = trim($this->controller->in->post['site_id']);
		$recElement->custom_player = $this->controller->in->post['custom_player'];
		$recElement->use_hd        = (int)$this->controller->in->post['use_hd'];
		$recElement->mode          = $this->controller->in->post['mode'];

		switch($recElement->mode) {
			case 'service':
				$recElement->site_id = $content_external_video->parseCode($recElement->site, $recElement->site_id);

				if($this->controller->in->post['title']== '' && $recElement->site_id) {
					$title = $content_external_video->getVideoTitle($recElement->site, $recElement->site_id);

					if($title) {
						$title = addslashes($title);
						$content_menu->update("title='$title'", "ID='$parentID'");
					}
				}
				break;
			case 'url':
				$recElement->external_flv = $this->controller->in->post['external_flv'];
				break;
		}

		$content_external_video->insert($recElement);
	}

	public function update($parentID) {
		/** @var \Gratheon\CMS\Model\ExternalVideo $content_external_video*/
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		$recElement          = $content_external_video->obj('parentID=' . $parentID);
		$recElement->site    = $this->controller->in->post['site'];
		$recElement->site_id = trim($this->controller->in->post['site_id']);

		$recElement->custom_player = $this->controller->in->post['custom_player'];
		$recElement->external_flv  = $this->controller->in->post['external_flv'];
		$recElement->use_hd        = (int)$this->controller->in->post['use_hd'];
		$recElement->mode          = $this->controller->in->post['mode'];

		switch($recElement->mode) {
			case 'service':
				$recElement->site_id = $content_external_video->parseCode($recElement->site, $recElement->site_id);

				if($this->controller->in->post['title'] == '' && $recElement->site_id) {
					$title = $content_external_video->getVideoTitle($recElement->site, $recElement->site_id);

					if($title) {
						$title = addslashes($title);
						$content_menu->update("title='$title'", "ID='$parentID'");
					}
				}
				break;
			case 'url':
				$recElement->external_flv = $this->controller->in->post['external_flv'];
				break;
		}
		$content_external_video->update($recElement, 'parentID=' . $parentID);
	}

	public function edit($recMenu = null) {

		$content_external_video = $this->model($this->model_name);

		$this->add_js($this->name . '/' . __FUNCTION__ . '.js');

		$parentID = $recMenu->ID;
		$this->assign('bHideContainer', true);
		$this->assign('arrSupportedSites', $content_external_video->getSupportedServices());

		if(is_dir(sys_root . '/ext/flowplayer/')) {
			$arrSupportedPlayers[] = 'flowplayer';
		}
		if(is_dir(sys_root . '/ext/jwplayer/')) {
			$arrSupportedPlayers[] = 'jwplayer';
		}

		$this->assign('arrSupportedPlayers', $arrSupportedPlayers);

		if($parentID) {
			$recElement = $content_external_video->obj('parentID=' . $parentID);
			$this->getVideoParams($recElement);


			if($recElement->mode == 'url') {
				$recElement->file_ext = end(explode('.', $recElement->external_flv));
			}

			$this->assign('recElement', $recElement);
		}

		if(file_exists(sys_root . '/ext/asflv_player/player.swf')) {
			$this->assign('hasPlayer', 1);
		}
	}

	public function delete($parentID) {
		$content_external_video = $this->model($this->model_name);
		$recElement             = $content_external_video->delete('parentID=' . $parentID);
	}

	public function get_adminpanel_box_list() {
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		$objLastData['data'] = $content_external_video->arr(
			"t1.module='video' ORDER BY t1.date_added DESC LIMIT 9", '*',
				$content_menu->table . " as t1 LEFT JOIN " .
						$content_external_video->table . ' as t2 ON t1.ID=t2.parentID');

		foreach($objLastData['data'] as &$item) {
			$this->getVideoParams($item);
		}

		$objLastData['title'] = $this->translate('Videos');
		$objLastData['count'] = $content_external_video->int("1=1", "COUNT(*)");
		return $objLastData;
	}

	//Private methods
	public function getVideoParams($recElement) {
		$content_external_video = $this->model($this->model_name);

		$recElement->FlashVars = '';

		if($recElement->mode == 'url') {
			$recElement->file_ext = end(explode('.', $recElement->external_flv));
			$content_menu         = $this->model('Menu');
			$content_image        = $this->model('Image');

			$image = $content_menu->int('t1.parentID="' . $recElement->parentID . '" AND module="image" LIMIT 1', 't2.ID, t2.image_format',
					$content_menu->table . " t1 LEFT JOIN " .
							$content_image->table . " t2 ON t2.parentID=t1.ID");

			if($image) {
				$recElement->file_image = $content_image->getOriginalURL($image);
			}
		}

		if($recElement->external_flv) {
			if($recElement->custom_player) {
				switch($recElement->custom_player) {
					case 'flowplayer':
						$recElement->flash_path = '/ext/flowplayer/flowplayer-3.2.5.swf';
						$recElement->FlashVars  = 'config={"playlist":[' . ($recElement->file_image ? '{"url": "' . $recElement->file_image . '", "scaling": "scale"},' : '') . '{"url":"' . $recElement->external_flv . '","autoPlay":false,"autoBuffering":true}],"clip":{}}';
						$recElement->src        = $recElement->external_flv;
						break;
					case 'jwplayer':
						$recElement->flash_path = '/ext/jwplayer/player.swf';
						$recElement->FlashVars  = 'image=' . $recElement->file_image . '&file=' . $recElement->external_flv . '&stretching=fill&controlbar.position=over';
						$recElement->src        = $recElement->external_flv;
						break;
				}

			}
			else {
				$recElement->flash_path = $recElement->src = $recElement->external_flv;
			}
		}
		else {
			$content_external_video->codeToFlash($recElement);
		}

		$recElement->width  = 600;
		$recElement->height = 350;


	}

	public function getArticleData($parentID, $arrFoundEmbeddedIDs = array()) {
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		if($arrFoundEmbeddedIDs) {
			$strWhere = " AND t1.ID NOT IN (" . implode(',', $arrFoundEmbeddedIDs) . ")";
		}

		$arrPublicVideos = $content_external_video->arr(
			" t1.parentID='" . $parentID . "' " . $strWhere . " AND t1.module='video' ORDER BY t1.position", '*',
				$content_menu->table . " as t1 LEFT JOIN " .
						$content_external_video->table . ' as t2 ON t1.ID=t2.parentID');

		if($arrPublicVideos) {
			foreach($arrPublicVideos as &$recElement) {
				$this->getVideoParams($recElement);
			}
		}

		return $arrPublicVideos;
	}

	//Public methods

	public function search_from_public($q) {
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		$arrPublicVideos = $content_external_video->arr(
			"title LIKE '%" . $q . "%' AND t1.module='video' ORDER BY t1.position", '*',
				$content_menu->table . " as t1 LEFT JOIN " .
						$content_external_video->table . ' as t2 ON t1.ID=t2.parentID');

		$arrEnvelope           = new \Gratheon\Core\Record();
		$arrEnvelope->count    = $content_menu->count();
		$arrEnvelope->title    = $this->translate('Videos');
		$arrEnvelope->template = 'ModuleFrontend/video/front_search.tpl';

		foreach($arrPublicVideos as &$item) {
			$this->getVideoParams($item);
		}

		$arrEnvelope->list = $arrPublicVideos;

		return $arrEnvelope;
	}

	public function search_from_admin($q) {
	}

	public function list_video_block($pageID) {
		$content_external_video = $this->model($this->model_name);
		$content_menu           = $this->model('content_menu');

		$intCount = $this->config('list_videos_block_limit');
		$intCount = $intCount ? $intCount : 5;

		$strDateFormat = $this->controller->config('date_format_sql');
		$strDateFormat = $strDateFormat ? $strDateFormat : '%d.%m.%Y';

		$arrVideos = $content_menu->arr(
			"t1.module='video' AND t1.langID='{$this->controller->langID}' ORDER BY date_added DESC LIMIT $intCount", "*",
				$content_menu->table . " t1 INNER JOIN " .
						$content_external_video->table . " t2 ON t1.ID=t2.parentID");

		if($arrVideos) {
			foreach($arrVideos as &$arrItem) {
				$this->getVideoParams($arrItem);
			}
		}


		return $arrVideos;

	}


	//Public methods
	public function category_view(&$recEntry) {
		$content_external_video = $this->model($this->model_name);

//		$this->add_css('video/video.css');

		$recEntry->video = $content_external_video->obj("parentID='{$recEntry->ID}'");
		$this->getVideoParams($recEntry->video);
		$recEntry->template = 'ModuleFrontend/' . $recEntry->module . '/article_video.tpl';
	}

	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$content_external_video = $this->model($this->model_name);
		$record                 = $content_external_video->obj("parentiD='$parentID'");

		$URL = $content_external_video->getPreviewImage($record->site, $record->site_id);

		if($URL) {
			return '<img src="' . $URL . '" />';
		}
		else {
			return '<img src="' . sys_url . '/vendor/Gratheon/CMS/assets/img/video/placeholder.jpg" />';
		}
	}

	public function decodeEmbeddable($menu) {
		$parentID = $menu->ID;
		$ID = $menu->elementID;

		$content_external_video = $this->model('content_external_video');
		$content_menu           = $this->model('content_menu');


		$video = $content_external_video->q(
			"SELECT * FROM content_menu as t1
            LEFT JOIN content_external_video as t2 ON t1.ID=t2.parentID
            WHERE t1.ID=$parentID AND t1.module='video' ORDER BY t1.position",
			'object'
		);
		if(!$video) {
			return '';
		}

		$this->getVideoParams($video);

		if($video->site == 'html') {
			return '<div class="video">'.$video->site_id.'</div>';
		}
		else {
			//$this->getVideoParams($video->video);

			$this->assign('video', $video);
			return '<div class="video">'.$this->controller->objView->view('ModuleFrontend/video/article_video.tpl').'</div>';
		}
	}
}