<?php
/**
 * @author Artjom Kurapov
 * @since 07.04.13 13:26
 */
namespace Gratheon\CMS\Model;

class AdminMenu extends \Gratheon\Core\Model{

	use ModelSingleton;

	final function __construct() {
		parent::__construct('content_module_menu');
	}

	public function getHierarchicalArray() {
		$arrModuleMenu = array();
		$arrModules    = $this->arr('parentID=0 ORDER BY position');
		foreach($arrModules as $item) {

			$arrNode = array(
				'name'   => $item->module,
				'rel'    => $item->module . '/' . $item->method,
				'title'  => $item->title,
				'link'   => sys_url . 'content/call/' . $item->module . '/' . $item->method,
				'active' => ($this->in->URI[3] == $item->module) ? 1 : 0
			);

			//add second level menu
			$arrChildren = $this->arr('parentID=' . $item->ID . ' ORDER BY position');
			foreach($arrChildren as $item2) {
				$arrNode['children'][] = array(
					'name'   => $item2->file,
					'rel'    => $item2->module . '/' . $item2->method,
					'title'  => $item2->title,
					'link'   => sys_url . 'content/call/' . $item2->module . '/' . $item2->method . '/',
					'active' => (in_array($this->in->URI[4], $this->arrint('parentID=' . $item->ID, 'module')))
							? 1 : 0,
				);
			}

			$arrModuleMenu[] = $arrNode;
		}

		return $arrModuleMenu;
	}
}
