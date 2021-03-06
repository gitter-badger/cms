<?php
/**
 * @author Artjom Kurapov
 * @since 22.09.11 22:44
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Map extends \Gratheon\CMS\SearchableContentModule
	implements \Gratheon\CMS\Module\Behaviour\Embeddable {

	public $name = 'map';
	public $models = array('content_map');
	public $public_methods = array('front_view');


	public function edit($recMenu = null) {
		$content_map = $this->model('content_map');
		$parentID    = $recMenu->ID;

		if($parentID) {
			$recElement = $content_map->obj("parentID='$parentID'");
			$this->assign('recElement', $recElement);
		}
	}


	function update($parentID) {
		$content_map = $this->model('content_map');

		$recElement = $content_map->obj('parentID=' . $parentID);

		$recElement->geoX    = $this->controller->in->post['geoX'];
		$recElement->geoY    = $this->controller->in->post['geoY'];
		$recElement->zoom    = $this->controller->in->post['zoom'];
		$recElement->service = $this->controller->in->post['service'];

		$content_map->update($recElement);
	}


	function insert($parentID) {
		$content_map = $this->model('content_map');

		$recElement           = new \stdClass();
		$recElement->parentID = $parentID;
		$recElement->geoX     = $this->controller->in->post['geoX'];
		$recElement->geoY     = $this->controller->in->post['geoY'];
		$recElement->zoom     = $this->controller->in->post['zoom'];
		$recElement->service  = $this->controller->in->post['service'];

		$content_map->insert($recElement);
	}


	public function category_view(&$recEntry) {
		$maps = $this->getArticleData($recEntry->parentID);

		if($maps) {
			$this->assign('map', $maps[0]);
		}


		//pre($recEntry);
		$recEntry->template = 'ModuleFrontend/map/front_view.tpl';
		/*
		$content_movie = $this->model('content_movie');
		$aMovies = $content_movie->ray("1=1 ORDER BY rating DESC");
		$this->assign('movies',$aMovies);
		*/
	}


	public function init() {
		$mapAPI = $this->controller->config->get('mapAPI');

		if($mapAPI) {
			if(isset($mapAPI['yandex'])) {
				$this->controller->add_js('http://api-maps.yandex.ru/1.1/index.xml?key=' . $mapAPI['yandex'], false);
			}

			if(isset($mapAPI['google'])) {
				$this->controller->add_js('https://maps.googleapis.com/maps/api/js?key=' . $mapAPI['google'] . '&sensor=true', false);
			}

			if(isset($mapAPI['bing'])) {
				$this->controller->add_js('http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0', false);
				$this->add_js_var('bing_key', $mapAPI['bing']);
			}

			if(isset($mapAPI['nokia'])) {
				$this->controller->add_js('http://api.maps.ovi.com/jsl.js', false);
				$this->add_js_var('nokia_key', $mapAPI['nokia']);
			}

			if(isset($mapAPI['yahoo'])) {
				$this->controller->add_js('http://api.maps.yahoo.com/ajaxymap?v=3.6&appid=' . $mapAPI['yahoo'], false);
			}
		}
		$this->controller->add_js('map/map.js');
	}


	public function getArticleData($parentID) {
		$content_map = $this->model('content_map');
		$arrMaps     = $content_map->arr(
			"t1.parentID='$parentID'", 't2.*',
			'content_menu t1 INNER JOIN content_map t2 ON t1.ID=t2.parentID'
		);

		$this->init();
		return $arrMaps;
	}


	public function search_from_public($q) {
		$this->init();
		return parent::search_from_public($q);
	}


	//Embeddable
	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$ID       = $menu->elementID;

		$content_map = $this->model('content_map');
		$record      = $content_map->obj('parentID=' . $parentID);

		return $record->geoX . 'x' . $record->geoY;
	}


	public function decodeEmbeddable($menu) {
		$parentID = $menu->ID;
		$ID       = $menu->elementID;

		$this->init();

		$this->controller->add_js('map/map.js');
		$content_map = $this->model('content_map');
		$record      = $content_map->obj("parentID=" . $parentID);

		$this->assign('map', $record);
		return '<div class="embed_map">' . $this->controller->objView->view('ModuleFrontend/map/embed_map.tpl') . '</div>';
	}
}
