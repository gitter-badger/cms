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
		$sys_languages      = new \Gratheon\Core\Model('sys_languages');
		$this->intLangCount = $sys_languages->int('1=1', 'COUNT(*)');

		parent::__construct();
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
		if($this->intLangCount > 1 && $arrPath) {
			$strPage = sys_url . join('/', $arrPath) . '/'; //.$system->language
		}
		elseif($arrPath) {
			unset($arrPath[0]);
			$strPage = sys_url;

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


	public function buildTree(&$arrTree = null, $arrSelected = array(1), $intParentID = 0, $intMaxLevel = 0, $intLevel = 0) {
		if(!isset($arrTree)) {
			$arrTree = array();
		}

		$arrLevel = $this->buildLevel($intParentID);
		if(is_array($arrLevel)) {
			foreach($arrLevel as $node) {
				$node->level = $intLevel;
				//$node->link = sys_url . '/content/main/content/edit/' . $node->ID;
				$node->front_link = $this->getPageURL($node->ID);

				$node->subnodes = array();
				$arrTree[]      = $node;
				if(!$arrSelected || in_array($node->ID, $arrSelected)) {
					$this->buildTree($node->subnodes, $arrSelected, $node->ID, $intMaxLevel, $node->level + 1);
				}

			}
		}
		return $arrTree;
	}
}