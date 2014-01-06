<?php
/**
 * @author Artjom Kurapov
 * @since 02.12.13 23:05
 */

namespace Gratheon\Cms\Controller\Content;
class Menu extends \Gratheon\CMS\Controller\Content\ProtectedContentController {
	public $preinit_languages = true;
	public $load_config = true;

	//Menu
	public function initialize() {
		$tree = new \Gratheon\CMS\Tree;
		$tree->initialize();
		$arrTree     = $tree->flatTree;
		$arrSelected = $tree->buildSelected(1);


		$this->assign('arrTree', $arrTree);
		$this->assign('maxAddLevel', 10);
		$this->assign('arrSelected', $arrSelected);
	}

	public function menu_parents() {
		$tree         = new \Gratheon\CMS\Tree;
		$this->MIME = 'application/json';
		$ID           = $this->in->get['ID'];
		$arrParents   = $tree->buildSelected($ID);
		$oConvertor   = new \Gratheon\Core\ObjectCovertor();

		echo $oConvertor->arrayToJson($arrParents);
	}

	public function menu_precise_move() {
		/** @var \Gratheon\CMS\Model\Menu $content_menu */
		$content_menu = $this->model('Menu');

		$ID          = (int)$this->in->get['ID'];
		$position    = (int)$this->in->get['pos'];
		$newParentID = (int)$this->in->get['parentID'];
		if(!$newParentID) {
			$newParentID = 1;
		}

		$item = $content_menu->obj($ID);
		if($item) {
			$content_menu->increaseChildPositionsAfterEq($newParentID, $position);

			$oldParentID = $item->parentID;

			$data           = new \Gratheon\Core\Record();
			$data->position = $position;
			$data->parentID = $newParentID;
			$content_menu->update($data, "ID='$ID' LIMIT 1");

			$content_menu->reorderChildPositions($oldParentID);
			$content_menu->reorderChildPositions($newParentID);
		}
	}


	public function menu_preload() {
		$tree = new \Gratheon\CMS\Tree;

		$this->MIME = 'application/json';
		$this->initialize();

		$intParentNode = $this->in->get['ID'];
		//$tree->strWhere=' langID='.(int)$this->in->get['langID'].' AND ';
		//$strLimit=isset($this->in->get['limit']) ? $this->in->get['limit'] : '0,30';
		$tree->strOrder .= ',date_added ';

		$arrSelNodes    = $tree->buildSelected($intParentNode);
		$arrSubPosMax[] = 0;

		foreach($arrSelNodes as $item) {
			$recMax = $tree->obj("ParentID='$item'", "MAX(position) as mx");
			$recSel = $tree->obj("ID='$item'", "position");

			$arrSubPosMax[] = $recMax->mx;
			$arrSubPosSel[] = $recSel->position;
		}

		$arrSubPosSel[] = 0;

		$arrTree = $tree->build($intParentNode, 1, count($arrSelNodes) + 1, $arrSubPosMax, $arrSubPosSel);

		$oConvertor = new \Gratheon\Core\ObjectCovertor();

		return $oConvertor->arrayToJson($oConvertor->objectToArray($arrTree, '', 1));
	}
}
