<?php
namespace Gratheon\CMS;
use Gratheon\Core;

/**
 * Menu module, one DB table needed
 * @author Artjom Kurapov <artkurapov@gmail.com>
 */

class Menu extends \Gratheon\CMS\Tree {
	public $intLangCount = 1;
	static $arrURLCache = array();


	function __construct() {
//		$sys_languages      = new \Gratheon\Core\Model('sys_languages');
//		$this->intLangCount = $sys_languages->int('1=1', 'COUNT(*)');

		parent::__construct();
	}


	function loadLanguageCount() {
		$sys_languages      = new \Gratheon\Core\Model('sys_languages');
		$this->intLangCount = $sys_languages->int('1=1', 'COUNT(*)');
		return $this->intLangCount;
	}


	function getPageByModule($moduleName, $language){
		$content_menu   = \Gratheon\CMS\Model\Menu::singleton();

		$intPage = $content_menu->int("module='$moduleName' AND langID='$language'", 'ID');

		if($this->intLangCount > 1) {
			$strPage = $this->getPageURL($intPage);
		}
		else {
			$strPage = $this->getPageURL($intPage);
		}
		return $strPage;
	}

	function getPageURL($intID) {
		$arrParents = $this->buildSelected((int)$intID);


		foreach($arrParents as $ID) {
			if($ID == 1) {
				continue;
			}

			if(!isset(Menu::$arrURLCache[$ID])) {
				Menu::$arrURLCache[$ID] = $strPath = $this->int($ID, "smart_url");
			}
			else {
				$strPath = Menu::$arrURLCache[$ID];
			}

			$arrPath[] = strlen($strPath) > 0 ? $strPath : $ID;
			unset($strPath);
		}

		$url = defined('sys_url_rel') ? sys_url_rel : sys_url;

		if($this->intLangCount > 1 && $arrPath) {
			$strPage = $url . join('/', $arrPath) . '/'; //.$system->language
		}
		elseif($arrPath) {
			unset($arrPath[0]);
			$strPage = $url;

			if($arrPath) {
				$strPage .= join('/', $arrPath) . '/';
			}
		}

		return $strPage;
	}


	function getTplPage($intTemplateLink, $langID = 'eng') {
		$tpl_links_page = new \Gratheon\Core\Model('tpl_links_page');
		$tpl_links      = new \Gratheon\Core\Model('tpl_links');
		$content_menu   = \Gratheon\CMS\Model\Menu::singleton();

		if(!is_numeric($intTemplateLink)) {
			$intTemplateLink = $tpl_links->int("`tag`='$intTemplateLink'", 'ID');

			if(!$intTemplateLink) {
				return '';
			}
		}

		$intPage = $tpl_links_page->int("t1.connectionID='$intTemplateLink'", 't1.pageID',
				$tpl_links_page->table . " t1 INNER JOIN " .
						$content_menu->table . " t2 ON t2.ID=t1.pageID AND t2.langID='{$langID}'");

		if($this->intLangCount > 1) {
			$strPage = $this->getPageURL($intPage);
		}
		else {
			$strPage = $this->getPageURL($intPage);
		}

		return $strPage;
	}


	function getTemplateLinks() {
		$arrLinks  = array();
		$tpl_links = new \Gratheon\Core\Model('tpl_links');
		$arrRows   = $tpl_links->arr();

		if($arrRows) {
			foreach($arrRows as $link) {
				if($link->tag) {
					$arrLinks[$link->tag] = $this->getTplPage($link->tag);
				}
			}
		}
		return $arrLinks;
	}


	private $buildFullTree = false;


	public function buildFullTree(&$arrTree, $arrSelected, $intParentID) {

		$this->buildFullTree = true;
//		echo '<pre>';
		$list                = $this->buildTree($arrTree, $arrSelected, $intParentID);
		$this->buildFullTree = false;

//		print_r($arrSelected);
//		print_r($list);
//		echo '</pre>';
		return $list;
	}


	public function buildTree(&$arrTree = null, $arrSelected = array(1), $intParentID = 0, $intMaxLevel = 0, $intLevel = 0) {
		if(!isset($arrTree)) {
			$arrTree = array();
		}

		$arrLevel = $this->buildLevel($intParentID);
//		print_r($intParentID."<br/>");
//		print_r($arrLevel);
//		print_r($this->strWhere);

		if(is_array($arrLevel)) {
			foreach($arrLevel as $node) {
				$node->level = $intLevel;
				//$node->link = sys_url . '/content/main/content/edit/' . $node->ID;
				$node->front_link = $this->getPageURL($node->ID);

				$node->subnodes = array();


				if($this->buildFullTree) {
//					print_r($arrLevel);

//					print_r($arrSelected);
//					$arrSelected = array_merge($arrSelected, $node->children);
					$this->buildTree($node->subnodes, $arrSelected, $node->ID, $intMaxLevel, $node->level + 1);
				}
				else if(!$arrSelected || in_array($node->ID, $arrSelected)) {
					$this->buildTree($node->subnodes, $arrSelected, $node->ID, $intMaxLevel, $node->level + 1);
				}

				$arrTree[]      = $node;

			}
		}

//		print_r($arrTree);
		return $arrTree;
	}
}