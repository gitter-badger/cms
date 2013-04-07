<?php

namespace Gratheon\Cms\Controller\Content;

/**
 * Backend controller
 * @version 1.1.3
 **/
class Content extends \Gratheon\CMS\Controller\Content\ProtectedContentController {
	public $preinit_languages = true;
	public $load_config = true;

	const NAME = 'content';

	private $image_extensions = array('jpg', 'gif', 'png', 'bmp', 'jpeg', 'tiff');


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


	public function add_connection() {
		$tpl_links_page = $this->model('tpl_links_page');
		$tpl_links      = $this->model('tpl_links');

		$src = (int)$this->in->get['ID'];
		$dst = (int)$this->in->get['target'];

		$iConnection = $tpl_links_page->int("pageID='$src' OR pageID='$dst'", "connectionID");
		if(!$iConnection) {
			$iConnection = $tpl_links->insert(array());
		}
		$tpl_links_page->insert(array(
			'connectionID' => $iConnection,
			'pageID'       => $src
		));
		$tpl_links_page->insert(array(
			'connectionID' => $iConnection,
			'pageID'       => $dst
		));
	}


	public function find_connection() {
		$content_menu = content_menu::singleton();
		$sWord        = $this->in->get['q'];
		$ID           = (int)$this->in->get['ID'];
		if(strlen($sWord) > 2) {
			$arrSrc     = $content_menu->obj($ID);
			$arrResults = $content_menu->arrint("ID<>'$ID' AND title LIKE '%{$sWord}%' AND module = '{$arrSrc->module}' AND langID<>'{$arrSrc->langID}'", "CONCAT(title,'|',ID)");
			return implode("\n", $arrResults);
		}
	}


	public function delete_connection() {
		$tpl_links_page = $this->model('tpl_links_page');
		$tpl_links      = $this->model('tpl_links');

		$src = (int)$this->in->get['ID'];
		$dst = (int)$this->in->get['target'];

		$iConnection = $tpl_links->q("SELECT t1.connectionID FROM tpl_links_page t1 INNER JOIN tpl_links_page t2 ON t1.connectionID=t2.connectionID WHERE t1.pageID = '$src' AND t2.pageID='$dst' ", "int");
		$tpl_links_page->delete("connectionID='{$iConnection}' AND pageID='{$dst}'");
		$iPagesLeft = $tpl_links_page->int("connectionID='{$iConnection}'", "COUNT(pageID)");
		if($iPagesLeft < 2) {
			$tpl_links_page->delete("connectionID='{$iConnection}'");
			$tpl_links->delete($iConnection);
		}
	}


	public function main() {
		$this->use_gz = false;

		$sys_languages  = $this->model('sys_languages');
		$iso_languages  = $this->model('iso_languages');
		$content_module = $this->model('content_module');

		$tree = new \Gratheon\CMS\Tree;

		$strType = '';
		foreach($tree->arrType as $item) {
			$strType .= '"' . $item . '"';
			if($item <> end($tree->arrType)) {
				$strType .= ',';
			}
		}

		if($this->in->post['langID']) {
			$_SESSION['content']['lang_content'] = $this->in->post['lang_content'];
		}

		$strLangAlpha2 = $sys_languages->int(
			"t1.ID='$this->langID'", "t2.id_1",
				$sys_languages->table . " t1 INNER JOIN " .
						$iso_languages->table . " t2 ON t1.ID=t2.id_2B");

		$this->add_css('/vendor/twitter/bootstrap/css/bootstrap.min.css', false);
		$this->add_css('/vendor/twitter/bootstrap/css/bootstrap-responsive.min.css', false);

		$this->add_css('/vendor/jquery/jquery-ui/themes/base/jquery.ui.all.css', false);
		$this->add_css('main.css');
		//$this->add_css($this->skin.'/reset.css');
		$this->add_css('layout.css');
		$this->add_css('desktop.css');
		$this->add_css('menu.css');
		//$this->add_css('/cms/app/content/css/'.$this->skin.'/twitter_extension.css', false);
		//$this->add_css($this->skin.'/forms.css');
		//$this->add_css($this->skin.'/datepicker.css');
		$this->add_css('fancybox.css');
		$this->add_css('jquery.autocomplete.css');


		$this->add_js('/vendor/jquery/jquery/jquery-1.7.2.js', false);
		$this->add_js('/vendor/jquery/cookie/jquery.cookie.js');
		$this->add_js('/vendor/backbonejs/underscorejs/underscore-min.js', false);
		$this->add_js('/vendor/backbonejs/backbonejs/backbone-min.js', false);
		/*
				$this->add_css('/ext/jquery/imgAreaSelect 0.9.8/css/imgareaselect-default.css', false);
				$this->add_js('/ext/jquery/imgAreaSelect 0.9.8/jquery.imgareaselect.min.js');
				*/
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.core.js');
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.widget.js');
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.mouse.js');
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.draggable.js');
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.datepicker.js');
		$this->add_js('/vendor/jquery/jquery-ui/ui/jquery.ui.autocomplete.js');
		$this->add_js('/vendor/jquery/jquery-ui/external/jquery.metadata.js');

		$this->add_js('/vendor/jquery/form/jquery.form.js');
		$this->add_js('/vendor/padolsey/sonic/src/sonic.js');
		$this->add_js('/vendor/desandro/masonry/jquery.masonry.min.js');

		$this->add_js(self::NAME . '/' . __FUNCTION__ . '.menu.js');
		$this->add_js(self::NAME . '/' . __FUNCTION__ . '.js');
		$this->add_js(self::NAME . '/' . __FUNCTION__ . '.init.js');
		$this->add_js(self::NAME . '/' . __FUNCTION__ . '.note.js');

		$this->add_js('/vendor/Gratheon/upload5/upload5.js');
		$this->add_js('/vendor/jquery/maskedinput/jquery.maskedinput-1.3.min.js');
		$this->add_js('/vendor/swfupload/swfupload/swfupload.js');
		$this->add_js('/vendor/twitter/bootstrap/js/bootstrap.js', false);

		$this->add_js_var('image_list_url', sys_url . 'content/call/image/list_last_images/');
		$this->add_js_var('file_upload_url', sys_url . 'content/content/batch_file_insert/');
		$this->add_js_var('swfu_flash_url', sys_url . 'cms/external_libraries/swfupload2/swfupload.swf');
		$this->add_js_var('link_embed_info', sys_url . 'content/content/embed_info/');
		$this->add_js_var('phpsessid', session_id());
		$this->add_js_var('sys_url', sys_url);
		$this->add_js_var('session_id', session_id());
		$this->add_js_var('autologout_passive', $this->config('autologout_passive') ? $this->config('autologout_passive') : 5 * 60);
		$this->add_js_var('autologout_active', $this->config('autologout_active') ? $this->config('autologout_active') : 5 * 60);
		$this->add_js_var('autologout_ping', $this->config('autologout_ping') ? $this->config('autologout_ping') : 30);

		$this->loadWrapper();

		/*
				$this->add_css('/cms/external_libraries/imperavi_redactor_7.6.3/css/redactor.css', false);
				$this->add_js('/cms/external_libraries/imperavi_redactor_7.6.3/redactor.min.js',false);
				*/
		$this->add_css('/vendor/imperavi/redactorjs/css/redactor.css', false);
		$this->add_js('/vendor/imperavi/redactorjs/redactor.js', false);


		$this->add_js_var('editor_interface_language', $strLangAlpha2);

		$arrModules = $content_module->ray("is_active=1 ORDER BY adminpanel_box_order DESC");

		foreach($arrModules as &$module) {
			foreach($this->routes as $path) {
				if(file_exists(sys_root . 'vendor/' . $path . '/assets/img/icons/menu/' . $module['ID'] . '.png')) {
					$module['icon'] = sys_url . 'vendor/' . $path . '/assets/img/icons/menu/' . $module['ID'] . '.png';
				}
			}
		}

		$this->assign('arrModules', $arrModules);
		$this->assign('strType', $strType);
		$this->assign('title', $this->config('title'));
		$this->assign('translations', $this->getTranslationsAsJS());

		return $this->view('layout/' . __FUNCTION__ . '.tpl');
	}


	public function dashboard($embedded = 0) {
		$content_module = $this->model('content_module');

		$this->add_js('dashboard.js');
		$this->add_css('dashboard.css');

		//List last changed content

		$arrLastContent   = array();
		$arrDraftArticles = array();

		$arrModules = $content_module->arrint('is_active=1 ORDER BY adminpanel_box_order DESC', 'ID');

		$controller = $this;
		if($arrModules) {
			foreach($arrModules as $strModule) {
				$this->loadModule($strModule, function ($objModule) use ($strModule, &$arrLastContent, &$controller) {

					/** @var $objModule */

					if(method_exists($objModule, 'get_adminpanel_box_list')) {
						$controller->add_js('modules/' . $objModule->name . '/get_adminpanel_box_list.js');
						$controller->add_css('modules/' . $objModule->name . '/get_adminpanel_box_list.css');

						$arrData = $objModule->get_adminpanel_box_list();

						$arrData['name'] = $strModule;

						if(!$arrData['template']) {

							$sTemplate = 'ModuleBackend/' . $objModule->name . '/get_adminpanel_box_list.tpl';


							$arrData['template'] = $sTemplate;
							//if (file_exists($sTemplate)) {}
						}

						$arrData['icon'] = sys_url . 'app/content/img/' . ($objModule->isCMS ? 'cms/' : '') . 'icons/menu/' . $strModule . '.png';

						if($arrData['data']) {
							$arrLastContent[] = $arrData;
						}
					}
				});
			}
		}

		$this->assign('arrLastContent', $arrLastContent);
		$this->assign('arrDraftArticles', $arrDraftArticles);

		return $this->view('controller_page/dashboard.tpl');
	}


	//does transliteration and cleaning
	public function transformSmartURL($strURL) {
		$strURL = str_replace(' ', '_', $strURL);
		$strURL = str_replace('/', '-', $strURL);
		$strURL = str_replace(',', '_', $strURL);
		$strURL = mb_strtolower($strURL, mb_detect_encoding($strURL));

		$oConvertor = new TextConvertor();
		$strURL     = $oConvertor->toTranslit($strURL);
		return $strURL;
	}


	public function editTags($strTags, $intPageID) {
		$sys_tags     = $this->model('sys_tags');
		$content_tags = $this->model('content_tags');

		$arrTags = explode(',', $strTags);
		foreach($arrTags as $key => $item) {
			$arrModTags[] = strtolower(trim($item));
		}

		#Add tag transformation
		$strTmp = '';
		foreach($arrModTags as $key => $item) {
			$strTmp .= "'" . $item . "'";
			if($key < count($arrModTags) - 1) {
				$strTmp .= ",";
			}
		}

		#Insert only new tags to avoid long cycles
		$arrExTags = $sys_tags->arrint("t1.title IN (" . $strTmp . ") AND t2.contentID=" . $intPageID, 't1.title',
				$sys_tags->table . ' t1 LEFT JOIN ' .
						$content_tags->table . ' t2 ON t1.ID=t2.tagID');

		$arrAllExTags = $sys_tags->arrint("t2.contentID=" . $intPageID, 't1.title',
				$sys_tags->table . ' t1 LEFT JOIN ' .
						$content_tags->table . ' t2 ON t1.ID=t2.tagID');

		foreach($arrModTags as $item) {
			if(!in_array($item, $arrExTags) && strlen($item) > 1) {
				$intExSysTag = $sys_tags->int("title='" . $item . "'", 'ID');
				if(!$intExSysTag) {
					$recTag        = new \Gratheon\Core\Record();
					$recTag->title = $item;
					$recTag->pop   = 1;
					$intExSysTag   = $sys_tags->insert($recTag);
				}

				$recConnection            = new \Gratheon\Core\Record();
				$recConnection->tagID     = $intExSysTag;
				$recConnection->contentID = $intPageID;
				$content_tags->insert($recConnection);
			}
		}

		#Delete ex tags that got deleted
		foreach($arrAllExTags as $item) {
			if(!in_array($item, $arrModTags)) {
				$content_tags->delete('tagID=' . $sys_tags->int("title='" . $item . "'", 'ID'));
			}
		}

		#Update tag count info
		$sys_tags->q("UPDATE " . $sys_tags->table . " SET pop=(SELECT COUNT(ID) FROM " . $content_tags->table . " as t2 WHERE tagID=" . $sys_tags->table . ".ID)");
		$sys_tags->delete("pop=0");
	}


	//General content management
	public function save() {

		$ID = (int)$this->in->get['ID'];

		$content_menu        = $this->model('content_menu');
		$sys_languages       = $this->model('sys_languages');
		$content_menu_rights = $this->model('content_menu_rights');


		if(!$this->in->post) {
			$output['msg'] = $this->translate('Message post is empty');
			return json_encode($output);
		}

		$recMenu = new \Gratheon\Core\Record;

		if($ID) {
			$recMenu = $content_menu->obj('ID=' . $ID);

			if(!$recMenu) {
				$output['msg'] = $this->translate('Article not found, cannot update it');
				return json_encode($output);
			}
		}
		elseif($this->in->post['ftp_files']) {
			$arrFiles = $this->in->post['ftp_files'];
			unset($this->in->post['ftp_files']);

			foreach($arrFiles as $strFile) {
				$this->batch_ftp_file_move($strFile);
			}

			$output['ID']  = $this->in->get['parentID'];
			$output['msg'] = $this->translate('Article not found, cannot update it');
			return json_encode($output);
		}

		$recMenu->title = stripslashes($this->in->post['title']);
		$recMenu->module     = $this->in->request['module'] ? : $recMenu->module;
		$recMenu->method     = $this->in->request['method'] ? : $recMenu->method;
		$arrDate             = explode(' ', $this->in->post['date_added']);
		$strTime             = $arrDate[1];
		$arrDate             = explode('.', $arrDate[0]);
		$strDate             = implode('-', array($arrDate[2], $arrDate[1], $arrDate[0]));
		$recMenu->date_added = $this->in->post['date_added'] <> '' ? $strDate . ' ' . $strTime : 'NOW()';

		//changing language moves node back to root
		if($ID && $recMenu->langID != $this->in->request['langID']) {
			$recMenu->parentID = 1;
		}


		if($this->in->post['elementID']) {
			$recMenu->elementID = (int)$this->in->post['elementID'];
		}

		$recMenu->langID           = $this->in->post['langID'] ? $this->in->post['langID'] : $recMenu->langID;
		$recMenu->smart_url        = $this->in->post['url'];
//		$recMenu->meta_title       = stripslashes($this->in->post['meta_title']);
//		$recMenu->meta_description = stripslashes($this->in->post['meta_description']);
//		$recMenu->meta_keywords    = stripslashes($this->in->post['meta_keywords']);
//
//		if($this->in->post['meta_latitude']) {
//			$recMenu->meta_latitude = $this->in->post['meta_latitude'];
//		}
//
//		if($this->in->post['meta_longitude']) {
//			$recMenu->meta_longitude = $this->in->post['meta_longitude'];
//		}

		if($this->in->request['container_template']) {
			$recMenu->container_template = $this->in->request['container_template'];
		}

		//$this->transformSmartURL
		if(!$recMenu->smart_url) {
			unset($recMenu->smart_url);
		}
		if(!$recMenu->method) {
			unset($recMenu->method);
		}

		//change existant node
		if($ID) {
			$content_menu->update($recMenu, 'ID=' . $ID);
			$content_menu_rights->delete('pageID=' . $ID);

			$recMenuRight = new \Gratheon\Core\Record();

			foreach((array)$this->in->post['user_rights'] as $intGroup => $arrGroup) {
				foreach($arrGroup as $intRight) {
					$recMenuRight->groupID = $intGroup;
					$recMenuRight->pageID  = $ID;
					$recMenuRight->rightID = $intRight;
					$content_menu_rights->insert($recMenuRight);
				}
			}

			if($recMenu->module) {
				$this->loadModule($recMenu->module, function ($objModule) use ($ID, &$output) {
					/** @var Module $objModule */
					if(method_exists($objModule, 'update')) {
						$output = $objModule->update($ID);
					}
				});
			}
			$intReloadID = $ID;
		}
		else {
			$recMenu->parentID = $this->in->get['parentID'] ? : $recMenu->parentID;
			$recMenu->position = $content_menu->int("parentID='". $recMenu->parentID."'", 'MAX(position)+1');

			if($recMenu->position===null){
				$recMenu->position = 0;
			}

			//use parent's langID
			if(!$this->in->post['langID']) {
				$recMenu->langID = $content_menu->int("ID='{$recMenu->parentID}'", 'langID');
			}

			if(!$recMenu->langID) {
				$recMenu->langID = $sys_languages->int("is_default=1", 'ID');
			}


			//check url to be unique
			if($recMenu->smart_url && $content_menu->int("smart_url='" . $recMenu->smart_url . "' && ID<>" . $this->in->get['parentID'])) {
				$output['msg'] = $this->translate('URL already exists') . ': ' . $recMenu->smart_url;
				return json_encode($output);
			}

			#Insert menu object
			$ID = $recMenu->ID = $content_menu->insert($recMenu);

			$recMenuRight = new \Gratheon\Core\Record();
			//Add system rights
			foreach((array)$this->in->post['user_rights'] as $intGroup => $arrGroup) {
				foreach($arrGroup as $intRight) {
					$recMenuRight->groupID = $intGroup;
					$recMenuRight->pageID  = $ID;
					$recMenuRight->rightID = $intRight;
					$content_menu_rights->insert($recMenuRight);
				}
			}

			$this->loadModule($recMenu->module, function ($objModule) use ($ID) {
				if(method_exists($objModule, 'insert')) {
					$strFailure = $objModule->insert($ID);

					if($strFailure) {
						return $strFailure;
					}
				}
			});


			$intReloadID = $content_menu->int('ID=' . $ID, 'parentID');
		}

		$this->editTags($this->in->post['tags'], $ID);

		$this->MIME   = 'application/json';
		$output['ID'] = $intReloadID;
		return json_encode($output);

	}


	public function edit($ID = null) {
		$sys_rights          = $this->model('sys_rights');
		$sys_templates       = $this->model('sys_templates');
		$content_module      = $this->model('content_module');
		$sys_languages       = $this->model('sys_languages');
		$sys_user_group      = $this->model('sys_user_group');
		$content_menu        = $this->model('content_menu');
		$content_menu_rights = $this->model('content_menu_rights');
		$sys_tags            = $this->model('sys_tags');

		$this->add_js('/cms/external_libraries/jquery/plugins/jquery.jtags.js');
		$this->add_js('content/edit.js');
		$this->add_css('content_edit.css');

		$tpl_links_page = $this->model('tpl_links_page');

		$ID       = isset($this->in->get['ID']) ? $this->in->get['ID'] : $ID;
		$parentID = isset($this->in->get['parentID']) ? $this->in->get['parentID'] : '';
		if(!$ID && !$parentID) {
			$parentID = 1;
		}

		$arrModules    = $content_module->arr('is_active=1 ORDER BY adminpanel_box_order DESC');
		$strFormAction = sys_url . "/content/content/save/?ID=$ID&parentID=$parentID&module=" . $this->in->request['module']; //."&langID=".$lang;
		$arrRights     = $sys_rights->arr();
		$strModule     = $this->in->request['module'] ? $this->in->request['module'] : $arrModules[0]->ID;

		if(in_array($strModule, array('poll', 'article'))) {
			$arrDefRights = $sys_rights->arrint("ID NOT IN (1,5)", "ID");
		}
		elseif(in_array($strModule, array('image', 'file', 'comment'))) {
			$arrDefRights = $sys_rights->arrint("ID NOT IN (1,3,4,5)", "ID");
		}
		elseif(in_array($strModule, array('category'))) {
			$arrDefRights = $sys_rights->arrint("ID NOT IN (3,4,5)", "ID");
		}
		else {
			$arrDefRights = $sys_rights->arrint("ID NOT IN (1,3,4,5)", "ID");
		}


		$this->assign('arrModules', $arrModules);
		$this->assign('arrLanguages', $sys_languages->arr("1=1 ORDER BY is_default DESC"));
		$this->assign('form_action', $strFormAction);
		$this->assign('userGroups', $sys_user_group->arr());
		$this->assign('defaultRights', $arrDefRights);
		$this->assign('sysRights', $arrRights);
		$this->assign('arrContainerTemplates', $sys_templates->arr("`type`='container' ORDER BY ID"));

		//Edit mode
		if($ID) {
			$recMenu = $content_menu->obj($ID, "*, DATE_FORMAT(date_added,'%d.%m.%Y %H:%i') date_added_formatted");

			if($recMenu->parentID == 1) {
				$this->assign("info", array($this->translate("This is website language root node")));
			}

			//fetch rights
			foreach($arrRights as $right) {
				$recMenu->rights[$right->ID] = (array)$content_menu_rights->arrint("pageID=" . $ID . " AND rightID=" . $right->ID, "groupID");
			}

			$arrTags       = $sys_tags->arrint(
				"t2.contentID=" . $recMenu->ID, 't1.title',
				'sys_tags t1 LEFT JOIN content_tags t2 ON t1.ID=t2.tagID'
			);
			$recMenu->tags = implode(', ', $arrTags);
			$recMenu->url  = sys_url . $this->getPath($ID);

			$strFailure = false;
			if($recMenu->module) {
				$this->loadModule($recMenu->module, function ($objModule) use ($recMenu, &$strFailure) {

					$objModule->assign('arrMethods', $objModule->public_methods);

					if(method_exists($objModule, 'edit')) {
						$strFailure = $objModule->edit($recMenu);

						$objModule->add_js($objModule->controller->skin . '/modules/' . $objModule->name . '/edit.js');
						$objModule->add_css($objModule->controller->skin . '/modules/' . $objModule->name . '/edit.css');

					}
				});
			}

			if($strFailure) {
				return $strFailure;
			}

			$strModule      = $recMenu->module;
			$recMenu->title = htmlentities($recMenu->title, ENT_QUOTES, 'UTF-8');

			$recMenu->pageConnections = $tpl_links_page->q("
                SELECT pageID, title
                FROM {$tpl_links_page->table} t1
                INNER JOIN {$content_menu->table} t2 ON t2.ID=t1.pageID
                WHERE pageID<>'$ID' AND connectionID=(SELECT connectionID FROM {$tpl_links_page->table} WHERE pageID='$ID')");

			$this->add_js_var('menu_id', $ID);

			$this->assign('title', 'Edit ' . $recMenu->title);
		}

		//Add mode
		else {
			$recMenu         = new \Gratheon\Core\Record();
			$recMenu->method = 'view';
			$recMenu->module = $this->in->get['module'];
			$recMenu->langID = $content_menu->int("ID='$parentID'", "langID");
			$this->add_js_var('menu_id', 0);
			$this->assign('title', 'New ' . $strModule);

			$this->loadModule($strModule, function ($objModule) use ($recMenu) {
				$objModule->controller->assign('arrMethods', $objModule->public_methods);

				if(method_exists($objModule, 'edit')) {
					$objModule->edit($recMenu);
				}
			});
		}

		$templateFile = sys_root . 'vendor/Gratheon/CMS/View/ModuleBackend/' . $strModule . '/edit.tpl';
		if(file_exists($templateFile)) {
			$this->assign('contentTemplate', $templateFile);
		}

		$namespaces = $this->namespaces;

		foreach($namespaces as $namespace) {
			$templateFile = sys_root . 'vendor' . str_replace('\\','/',$namespace) . '/View/ModuleBackend/' . $strModule . '/edit.tpl';
			if(file_exists($templateFile)) {
				$this->assign('contentTemplate', $templateFile);
			}
		}


		$this->assign('recMenu', $recMenu);

		if($recMenu->module) {
			$this->assign('is_translateable', false);
		}

		if($this->in->get['modal']) {
			return $this->view('controller_page/edit.modal.tpl');
		}
		else {
			return $this->view('controller_page/edit.tpl');
		}
	}


	public function delete($ID = null) {
		$content_menu = $this->model('content_menu');
		$content_tags = $this->model('content_tags');
		$sys_tags     = $this->model('sys_tags');

		if(!$ID) {
			$ID = (int)$this->in->get['ID'];
		}

		$recMenu = $content_menu->obj($ID);

		//you cannot delete root catalogs
		if($ID == 1 || $recMenu->parentID == 1) {
			return false;
		}


		#Delete tags
		$content_tags->delete('contentID=' . $ID);

		#Update tag count info
		$sys_tags->q("UPDATE sys_tags SET pop=(SELECT COUNT(ID) FROM content_tags as t2 WHERE tagID=sys_tags.ID)");
		$sys_tags->delete("pop=0");

		//recursion deletion
		$arrMenu = $content_menu->arrint('parentID=' . $ID, 'ID');
		if(is_array($arrMenu)) {
			foreach($arrMenu as $item) {
				$this->delete($item);
			}
		}

		$this->loadModule($recMenu->module, function ($objModule) use ($ID) {
			if(method_exists($objModule, 'delete')) {
				$objModule->delete($ID);
			}
		});

		$content_menu->delete($ID);
		return '{"ID":' . $recMenu->parentID . '}';
	}


	public function paste() {
		$content_menu = $this->model('content_menu');

		$arrIDs      = explode(',', $this->in->get['ids']);
		$intParentID = (int)$this->in->get['parentID'];
		if($arrIDs) {
			foreach($arrIDs as $srcID) {
				$dstMenu = $srcMenu = $content_menu->obj($srcID);
				if($srcMenu->module != 'image') {
					continue;
				}
				$dstMenu->parentID   = $intParentID;
				$dstMenu->date_added = 'NOW()';
				$dstMenu->langID     = $content_menu->int($intParentID, "langID");
				unset($dstMenu->smart_url);
				unset($dstMenu->ID);
				$content_menu->insert($dstMenu);
			}
		}
		echo "{ID:$intParentID}";
		exit();
	}


	public function call() {
		$strModule   = $this->in->URI[3];
		$strFunction = $this->in->URI[4] ? $this->in->URI[4] : 'main';
		$this->loadWrapper();

		$controller = $this;

		$this->useScriptedRedirect = true;

		if($strModule) {
			$view = $this->loadModule($strModule, function ($objModule) use ($strModule, $strFunction, $controller) {

				/** @var \Gratheon\Core\Module $objModule */
				$objModule->init($strFunction);

				if($this->in->get['static']) {
					$objModule->strWrapperTpl = 'layout/main.tpl';
				}
				else {
					$objModule->strWrapperTpl = 'layout/ajax.tpl';
				}

				$objModule->add_css('modules/' . $objModule->name . '/' . $strFunction . '.css');
				$objModule->add_js('modules/' . $objModule->name . '/' . $strFunction . '.js');

				$contentTemplate = sys_root . 'vendor' . $objModule->route . '/View/ModuleBackend/' . $objModule->name . '/' . $strFunction . '.tpl';
				if(!is_file($contentTemplate)){
					$contentTemplate = sys_root . 'vendor/Gratheon/CMS/View/ModuleBackend/' . $objModule->name . '/' . $strFunction . '.tpl';
				}


				$objModule->assign('content_template', $contentTemplate);
				$objModule->assign('strModule', $strModule);


				$controller->assign('title', '');
//				$controller->cache_css();

				if(method_exists($objModule, $strFunction)) {
					if(isset($this->in->URI[5])){
						$id = $this->in->URI[5];
					}
					else{
						$id = null;
					}
					$objModule->$strFunction($id);
					return $controller->view($objModule->strWrapperTpl);
				}
			});
			return $view;
		}
	}


	public function redirect($url) {

		if($this->useScriptedRedirect) {
			echo "<script>top.location = '" . $url . "';</script>";
			exit();
		}
		else {
			parent::redirect($url);
		}
	}


	public function getPath($intID) {
		$tree         = new \Gratheon\CMS\Tree;
		$content_menu = $this->model('content_menu');

		$arrParents = $tree->buildSelected((int)$intID);
		foreach($arrParents as $ID) {
			if($ID == 1) {
				continue;
			}
			$strPath = $content_menu->int($ID, "smart_url");
			//pre($strPath);
			$arrPath[] = strlen($strPath) > 0 ? $strPath : $ID;
			unset($strPath);
		}
		//pre($arrPath);
		return join('/', (array)$arrPath);
	}


	public function getDefaultParentID() {
		$content_menu = $this->model('content_menu');

		return $content_menu->int("t1.parentID='1' AND t2.is_default=1", "*",
			"content_menu t1 INNER JOIN sys_language t2 ON t2.ID=t1.langID");
	}


	public function batch_file_insert() {
		$this->in->get['parentID'] = $this->in->request['parentID'] = ($this->in->request['parentID'] ? $this->in->request['parentID']
				: $this->getDefaultParentID());

		if(!is_array($_FILES['file']['name'])) {
			/*$this->in->post['title'] = */

			$strFilename = $_FILES['file']['name'];
			$arrFile     = explode('.', $strFilename);
			$strExt      = strtolower(end($arrFile));

			if(in_array($strExt, $this->image_extensions)) {
				$this->in->request['module']    = 'image';
				$this->in->post['cut_position'] = 2;

			}
			else {
				$this->in->post['title']     = $strFilename;
				$this->in->request['module'] = 'file';
			}

			$this->save();
		}
		else {

			$tmpFiles = $_FILES;
			unset($_FILES);

			foreach($tmpFiles['file']['name'] as $fileKey => $strFilename) {
				$this->in->post['title'] = $strFilename;
				$arrFile        = explode('.', $strFilename);
				$strExt         = strtolower(end($arrFile));

				if(in_array($strExt, $this->image_extensions)) {
					$this->in->request['module'] = 'image';

					$this->in->post['cut_position'] = 2;

				}
				else {
					$this->in->request['module'] = 'file';
				}
				//		mail('artkurapov@gmail.com','test',print_r($this->in->request,true).print_r($_FILES,true));

				$_FILES['file']['name']     = $tmpFiles['file']['name'][$fileKey];
				$_FILES['file']['tmp_name'] = $tmpFiles['file']['tmp_name'][$fileKey];
				$_FILES['file']['size']     = $tmpFiles['file']['size'][$fileKey];
				$_FILES['file']['error']    = $tmpFiles['file']['error'][$fileKey];
				$_FILES['file']['type']     = $tmpFiles['file']['type'][$fileKey];

				$this->save();
			}
			echo 1;
		}
	}


	/**
	 * Moves single file
	 * Used for mass file adding from FTP /res/incoming directory
	 *
	 * @param $strFile
	 */
	public function batch_ftp_file_move($strFile) {
		set_time_limit(300);

		$this->in->post['title'] = $strFilename = $strFile;
		$arrFile        = explode('.', $strFilename);
		$strExt         = strtolower(end($arrFile));

		if(in_array($strExt, $this->image_extensions)) {
			$this->in->post['module'] = 'image';

			$this->in->post['cut_position'] = 2;

		}
		else {
			$this->in->post['module'] = 'file';
		}

		$this->save();
	}


	//Generic search, depending on content type
	public function search() {
		$content_module = $this->model('content_module');

		$this->add_js('front.search.js');
		$this->add_css('front.search.css');

		//$content_menu->obj("parentID=1 AND langID='" . $this->langID . "'");

		$q = (urldecode($this->in->get['q']));
		$q = str_replace('%', '\%', $q);
		$q = str_replace('_', '\_', $q);

		$arrModules = $content_module->arrint("is_active=1 ORDER BY adminpanel_box_order DESC", 'ID');

		$arrResults = array();
		foreach($arrModules as $module) {

			$this->loadModule($module, function ($objModule) use ($module, &$arrLastContent, $q, &$arrResults) {

				if(method_exists($objModule, 'search_from_admin')) {
					$arrResults[$module]        = $objModule->search_from_admin($q);
					if($arrResults[$module]){
						$arrResults[$module]->name  = $module;
						$arrResults[$module]->isCMS = $objModule->isCMS;
						if(!isset($arrResults[$module]->count) || isset($arrResults[$module]->count) && !$arrResults[$module]->count) {
							unset($arrResults[$module]);
						}
					}
				}
			});
		}

		$this->assign('title', $this->translate('Search') . ' : ' . $q);
		$this->assign('query', $q);
		$this->assign('arrResults', $arrResults);

		return $this->view('controller_page/' . __FUNCTION__ . '.tpl');
	}


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


	public function close_window() {
		echo "<script>top.menu_load(" . $this->in->post['menu_parentID'] . ",1);</script>";
	}


	public function menu_edit() {
		$content_menu = $this->model('content_menu');

		if($this->in->post['menu_parentID']) {
			$newMenu        = new \Gratheon\Core\Record();
			$newMenu->title = $this->in->post['menu_title'];
			$newMenu->ID    = $this->in->post['elementID'];

			//Case we change parent language
			if(isset($this->in->post['langID'])) {
				$newMenu->langID   = $this->in->post['langID'];
				$newMenu->parentID = 1;
			}

			$content_menu->update($newMenu);
			$this->element_change($this->in->post['elementID']);
			exit();
		}
	}


	public function menu_precise_move() {
		/** @var \Gratheon\CMS\Model\Menu $content_menu  */
		$content_menu = $this->model('Menu');

		$ID       = (int)$this->in->get['ID'];
		$position = (int)$this->in->get['pos'];
		$newParentID = (int)$this->in->get['parentID'];
		if(!$newParentID) {
			$newParentID = 1;
		}

		$item            = $content_menu->obj($ID);
		if($item){
			$content_menu->increaseChildPositionsAfterEq($newParentID, $position);

			$oldParentID = $item->parentID;
			$item->position = $position;
			$item->parentID = $newParentID;
			$content_menu->update($item);


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


	public function menu_parents() {
		global $system;

		$tree         = new \Gratheon\CMS\Tree;
		$system->MIME = 'application/json';
		$ID           = $this->in->get['ID'];
		$arrParents   = $tree->buildSelected($ID);
		$oConvertor   = new \Gratheon\Core\ObjectCovertor();

		echo $oConvertor->arrayToJson($arrParents);
	}


	public function embed_info() {
		$content_menu = $this->model('content_menu');

		$ID      = (int)$this->in->get['id'];
		$objMenu = $content_menu->obj($ID, "ID,module,elementID,title");

		$this->loadModule($objMenu->module, function ($objModule) use (&$objMenu, $ID) {
			/** @var \Gratheon\CMS\Module\Behaviour\Embeddable $objModule */
			if(method_exists($objModule, 'getPlaceholder')) {
				$objMenu->html = $objModule->getPlaceholder($objMenu); //$objMenu['module']. '#'.$ID;
			}
			else {
				$objMenu->html = $objMenu->module . '#' . $ID . ' cannot be embedded';
			}
		});


		$oConvertor = new \Gratheon\Core\ObjectCovertor();
		echo $oConvertor->arrayToJson((array)$objMenu);
		exit();
	}
}