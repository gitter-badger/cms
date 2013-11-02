<?php

namespace Gratheon\Cms\Controller\Content;

/**
 * Backend controller
 * @version 1.1.3
 **/
class Tag extends \Gratheon\CMS\Controller\Content\ProtectedContentController {
	public $preinit_languages = true;
	public $load_config = true;

	const NAME = 'tag';

	public function loadWrapper() {
		/** @var $adminMenu \Gratheon\CMS\Model\AdminMenu */
		/** @var $sys_languages \Gratheon\CMS\Model\Language */
		$sys_languages = $this->model('Language');
		$adminMenu = $this->model('AdminMenu');

		$arrLanguages  = $sys_languages->getLanguages();
		$arrModuleMenu = $adminMenu->getHierarchicalArray();

		$this->assign('sys_url', sys_url);
		$this->assign('link_jsmode', sys_url . 'content/profile/toggle_jsmode/');
		$this->assign('arrLanguages', $arrLanguages);
		$this->assign('arrModuleMenu', $arrModuleMenu);
	}


	public function listPopTags(){
		/**
		 * @var $tags \Gratheon\CMS\Model\Tag
		 */
		$tags = $this->model('Tag');
		$tagList = $tags->listPopTags();
		echo json_encode($tagList);
		exit();
	}

	public function list_tags(){

	}

}
