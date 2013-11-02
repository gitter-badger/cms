<?php
/**
 * File page type
 * @version 1.3
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Sitemap extends \Gratheon\CMS\ContentModule {

	var $models = array('content_redirect', 'content_menu', 'tpl_links', 'content_menu_rights', 'content_sitemap', 'content_sitemap_node');

	var $static_methods = array('front_xml');
	var $arrModulesExcluded = array("'comment'", "'image'", "'file'", "'video'");
	var $name = 'sitemap';


	public function edit($recMenu = null) {
		$tree = new \Gratheon\CMS\Tree;

		$content_redirect     = $this->model('content_redirect');
		$content_sitemap      = $this->model('content_sitemap');
		$content_sitemap_node = $this->model('content_sitemap_node');

		$parentID = $recMenu->ID;

		if($parentID) {
			$recElement = $content_redirect->obj('parentID=' . $parentID);

			$recElement->pageIDs      = (array)$content_sitemap->arrint("parentID='$parentID'", 'pageID');
			$recElement->listing_mode = $content_sitemap_node->int("parentID='$parentID'", 'listing_mode');
			$this->assign('recElement', $recElement);
		}

		$tree->strWhere = "
			t1.module NOT IN (" . implode(',', $this->arrModulesExcluded) . ") AND  ";


		$tree->initialize(5, false);

		$recSelected     = new \Gratheon\Core\Record();
		$recSelected->ID = 1;
		$arrTree         = $tree->flatTree;
		$arrSelected     = $tree->buildSelected(1);

		$this->assign('arrTree', $arrTree);

		//$this->assign('bHideContainer',true);
		$this->assign('show_URL', true);
	}


	public function insert($parentID) {
		$content_sitemap      = $this->model('content_sitemap');
		$content_sitemap_node = $this->model('content_sitemap_node');

		$content_sitemap_node->insert(array(
			'parentID'     => $parentID,
			'listing_mode' => $this->controller->in->post['listing_mode']
		));

		$content_sitemap->delete("parentID='$parentID'");
		if($this->controller->in->post['pageIDs']) {
			foreach($this->controller->in->post['pageIDs'] as $id) {
				$content_sitemap->insert(array('parentID' => $parentID, 'pageID' => $id));
			}
		}

	}


	public function update($parentID) {
		$content_sitemap      = $this->model('content_sitemap');
		$content_sitemap_node = $this->model('content_sitemap_node');

		$content_sitemap_node->update("listing_mode='" . $this->controller->in->post['listing_mode'] . "'", "parentID='$parentID'");
		$content_sitemap->delete("parentID='$parentID'");
		if($this->controller->in->post['pageIDs']) {
			foreach($this->controller->in->post['pageIDs'] as $id) {
				$content_sitemap->insert(array('parentID' => $parentID, 'pageID' => $id));
			}
		}
	}


	public function delete($parentID) {
		$content_sitemap      = $this->model('content_sitemap');
		$content_sitemap_node = $this->model('content_sitemap_node');

		$content_sitemap->delete("parentID='$parentID'");
		$content_sitemap_node->delete("parentID='$parentID'");
	}


	public function front_view($parentID) {

		$content_sitemap      = $this->model('content_sitemap');
		$content_sitemap_node = $this->model('content_sitemap_node');

		$this->add_css($this->name . '/' . __FUNCTION__ . '.css');

		$strListingMode = $content_sitemap_node->int("parentID='$parentID'", 'listing_mode');
		$strSwitch      = '';
		if($strListingMode == 'hide') {
			$strSwitch = 'NOT';
		}


		$tree = new \Gratheon\CMS\Tree();
		//get menu
		$tree->strWhere = "
			t1.langID='" . $this->controller->langID . "' AND
			t1.module NOT IN (" . implode(',', $this->arrModulesExcluded) . ") AND
			t1.ID $strSwitch IN ( 
					SELECT t2.pageID FROM {$content_sitemap->table} t2 WHERE
					t2.parentID='$parentID'
				) AND ";


		//$tree->initialize(5,false);
		$arrFlatTree = array();
		$tree->buildTree($arrFlatTree, 0, 1, 10, 1);

		$this->assign('arrSitemap', $arrFlatTree);
	}


	public function front_xml() {
		$content_menu_rights = $this->model('content_menu_rights');

		$this->controller->MIME = 'text/xml';

		$sOut = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

		$parentID = 1;

		//get menu
		$SitemapTree           = new \Gratheon\CMS\Tree();
		$SitemapTree->strWhere = "
			t1.module NOT IN (" . implode(',', $this->arrModulesExcluded) . ") AND
			t1.ID IN (
				SELECT pageID FROM " . $content_menu_rights->table . " t2
				WHERE  t2.groupID='3' AND t2.rightID=2 ) AND ";


		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();
		$SitemapTree->initialize(10);


		$arrFlatTree = $SitemapTree->flatTree;

		foreach($arrFlatTree as $objElement) {

			$this->controller->loadModule(
				$objElement->module,
				function ($objModule) use (&$sOut, $objElement, $menu) {

					if(method_exists($objModule, 'sitemap_xml')) {
						$sOut .= $objModule->sitemap_xml($objElement);
					}
					else {
						$sOut .= '
	<url>
		<loc>' . str_replace('&',urlencode('&'),$menu->getPageURL($objElement->ID)) . '</loc>
		<priority>0.5000</priority>
	</url>';
					}
				});
		}


		$sOut .= '</urlset>';
		return $sOut;

	}
}