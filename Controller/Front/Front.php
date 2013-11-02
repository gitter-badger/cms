<?php
namespace Gratheon\CMS\Controller\Front;
use Gratheon;

/**
 * Classic frontend controller
 * @author Artjom Kurapov <artkurapov@gmail.com>
 * @version 1.1.0
 **/
class Front extends \Gratheon\Core\Controller {
	public $content_template = 'front.main.tpl';
	public $preinit_languages = true;
	public $load_config = true;
	public $langID = 'eng';


	public $models = array(
		'iso_languages',
		'content_menu', 'content_article', 'content_comment', 'content_poll', 'content_poll_answers', 'content_poll_votes',
		'content_menu_rights', 'content_file_jar', 'content_file', 'content_image',
		'sys_banned', 'sys_languages', 'sys_tags', 'content_tags', 'content_module', 'content_module_connections');


	public function loadLanguages() {
		$sys_languages = $this->model('sys_languages');

		$recLanguage  = $sys_languages->obj("ID='" . $this->langID . "'");
		$arrLanguages = $sys_languages->arr('1=1', 'ID,native_spell,native_spellin');

		$this->assign('recLanguage', $recLanguage);
		$this->assign('arrLanguages', $arrLanguages);
	}


	public function handleUnknownRoutes() {
		if(end($this->in->URI) == 'rss.xml') {
			return $this->rss();
		}

		if($this->in->URL) {
			$content_menu = $this->model('Menu');
			$menu         = new \Gratheon\CMS\Menu();
			$menu->loadLanguageCount();
			//$content_menu->load_config();

			$ID = $content_menu->int("smart_url='" . end($this->in->URL) . "'", "ID");

			if($ID) {
				$this->redirect($menu->getPageURL($ID));
			}
		}

		if(!in_array($this->in->URI[2], $this->arrLanguages)) {
			$this->error->fatal(404);
		}
	}


	public function handleViewPermissionDenied() {
		echo "Permission denied";
		exit();
//        $this->assign('errors', array($this->translate('Please login')));
//        $this->container_template = sys_root . 'app/front/view/layout/front.page.tpl';
//        $this->assign('content_template', sys_root . 'app/front/view/helpers/messages.tpl');
	}


	public function handleCustomPage($objMenuElement) {
	}


	private function findDeeperMenuElement($aURLs, &$objElement, $arrRootChildURLs) {
		$content_menu = $this->model('content_menu');

		foreach($aURLs as $key => $strObjectURL) {
			$strObjectURL = urldecode($strObjectURL);

			if(is_numeric($strObjectURL)) {
				$recElement = $content_menu->obj("smart_url='" . addslashes($strObjectURL) . "' AND parentID='{$objElement->ID}'");

				if(!$recElement) {
					$recElement = $content_menu->obj("ID=" . (int)$strObjectURL . " AND parentID='{$objElement->ID}'");
				}
			}
			else {
				$recElement = $content_menu->obj("smart_url='" . addslashes($strObjectURL) . "' AND parentID='{$objElement->ID}'");
			}

			//skip first element if its language identifier, since its in objElement
			if(!$key && in_array($strObjectURL, $arrRootChildURLs)) {
				continue;
			}
			elseif($recElement) {
				$objElement = $recElement;
			}
			else {
				break;
			}
		}
	}


	public function abstractMain() {
		$controller = $this;
		$user       = $this->user;
		$menu       = $this->menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();

		$content_menu               = $this->model('content_menu');
		$content_menu_rights        = $this->model('content_menu_rights');
		$content_module_connections = $this->model('content_module_connections');

		//Dynamically load module depending on page type

		//Step 1. Using smart URL
		$objElement = $content_menu->obj("parentID=1 AND langID='" . $this->langID . "'");

		$arrRootChildURLs = (array)$content_menu->arrint("parentID=1 AND smart_url IS NOT NULL", "smart_url");
		if($this->in->URL) {
			$this->findDeeperMenuElement($this->in->URL, $objElement, $arrRootChildURLs);
		}

		$strObjectURL = end($this->in->URL);
		//Step 2. Using ID
		if($objElement->parentID == 1 && is_numeric($strObjectURL)) {
			$objElement = $content_menu->obj($strObjectURL);
		}

		if(!$objElement->ID) {
			$sReturn = $this->handleUnknownRoutes();
			if($sReturn) {
				return $sReturn;
			}
		}
		else {
			if($objElement->parentID == 1 && count($this->in->URL) > 0 && !in_array(end($this->in->URL), $arrRootChildURLs)) {
				$sReturn = $this->handleUnknownRoutes($objElement);
				if($sReturn != '') {
					return $sReturn;
				}
			}
			//check permissions

			$boolCanAccess = $content_menu_rights->int("pageID='{$objElement->ID}' AND groupID='" . $user->data['groupID'] . "' AND rightID=2 ", "1");

			$arrBlocks = array();

			if(!$boolCanAccess) {
				$this->handleViewPermissionDenied();
			}
			else {
				$this->loadMenuWithSelection($objElement->ID);

				$this->handleCustomPage($objElement);

				if($objElement->title) {
					if($objElement->meta_title) {
						$this->assign('title', $objElement->meta_title);
					}
					else {
						$this->assign('title', $objElement->title);
					}
				}

				if($objElement->container_template) {
					$controller->container_template = sys_root . 'vendor' . $this->route . '/View/layout/' . $objElement->container_template;
				}

				$this->page = $objElement;

				if($objElement->module) {
					//Find all
					$arrConnections = $content_module_connections->arr("(pageID IS NULL AND source_module IS NULL AND source_method IS NULL) OR pageID='{$objElement->ID}' OR source_module='{$objElement->module}' AND source_method='{$objElement->method}'", "destination_module,destination_method");

					$arrBlocks = array();
					if($arrConnections) {
						foreach($arrConnections as $objModuleConnection) {
							//Call page primary module
							$strModule = $objModuleConnection->destination_module;
							$strMethod = $objModuleConnection->destination_method;

							$this->loadModule($strModule, function ($objModule) use ($objElement, $strMethod, $objModuleConnection, &$arrBlocks) {
								if(method_exists($objModule, $strMethod)) {
									$arrBlocks[$objModuleConnection->destination_module][$strMethod] = $objModule->$strMethod($objElement->ID);
									$objModule->add_js($objModuleConnection->destination_module . '/' . $strMethod . '.js');
//									$objModule->add_css($objModuleConnection->destination_module . '/' . $strMethod . '.css');
								}
							});
						}
					}


					//Call page primary module

					$this->loadModule($objElement->module, function ($objModule) use ($objElement, $controller) {

						$objModule->init($objElement->method);

						if(method_exists($objModule, $objElement->method)) {
							$objModule->{$objElement->method}($objElement->ID);

							$controller->add_js($objModule->name . '/' . $objModule->name . '.js');
//							$controller->add_css($objModule->name . '/' . $objModule->name . '.css');

							$controller->add_js($objModule->name . '/' . $objElement->method . '.js');
//							$controller->add_css($objModule->name . '/' . $objElement->method . '.css');

							$strContentTemplate = 'ModuleFrontend/';

							if(isset($objModule->content_template) && $objModule->content_template) {
								$strContentTemplate .= $objModule->content_template;
							}
							elseif(!$objElement->content_template) {
								$strContentTemplate .= $objModule->name . '/' . $objElement->method . '.tpl';
							}
							else {
								$strContentTemplate .= $objModule->name . '/' . $objElement->content_template;
							}
//print_r($objModule);
//print_r($strContentTemplate);
							$controller->assign('content_template', $strContentTemplate);
						}
					});
				}
			}

			$this->assign('arrBlocks', $arrBlocks);
			$this->assign('page', $this->page);
			$this->assign('intMenuSelected', $objElement->ID);

		}

		if(!$controller->container_template) {
			$controller->container_template = sys_root . 'vendor' . $this->route . '/View/layout/front.main.tpl';
		}

		$this->assign('arrLinks', $menu->getTemplateLinks());
		$this->assign('translations', $this->getTranslationsAsJS());
		$this->assign('controller', $controller);
		$this->add_js_var('sys_url', sys_url);
//		if(isset($_GET['a'])){
//			echo $controller->container_template;
//			exit();
//		}

		return $controller->container_template;
	}


	/**
	 * Intended for ajax and system calls that do not require general data loading like menu, links
	 *
	 * @param null $module
	 * @param null $method
	 *
	 * @return string
	 */
	public function abstractModuleCall($module = null, $method = null) {
		if(!$module || !$method) {
			$module = $this->in->URI[3];
			$method = $this->in->URI[4];
		}

		$controller = $this;

		$this->loadModule($module, function ($objModule) use ($module, $method, &$controller) {
			$objModule->controller = $controller;
			$objModule->init();

			if(method_exists($objModule, $method) && in_array($method, (array)$objModule->static_methods)) {
				$controller->assign('content_template', sys_root . 'app/front/view/page/' . $module . '/' . $method . '.tpl');
				return $objModule->{$method}();
			}
			else {
				pre('method disabled');
				exit();
			}
		});
	}


	//Generic search, depending on content type
	public function search() {
//        $this->add_css('night_theme.css', 'screen');

		$content_menu   = $this->model('content_menu');
		$sys_tags       = $this->model('sys_tags');
		$content_module = $this->model('content_module');

		$this->add_js('front.search.js');
//        $this->add_css('front.search.css');

		$objElement = $content_menu->obj("parentID=1 AND langID='" . $this->langID . "'");

		$this->loadMenuWithSelection($objElement->ID);

		//win2utf
		//$q = (urldecode($this->URI[3]));
		$q = urldecode($this->in->request['q']);

		/*
				$q = str_replace('%', '\%', $q);
				$q = str_replace('_', '\_', $q);
		*/
		$arrResults = array();
		if(strlen($q) > 3) {
			$intTagID = $sys_tags->int("title='" . addslashes($q) . "'", 'ID');

			$arrResults = array();
			$arrModules = $content_module->arrint("is_active=1", 'ID');
			foreach($arrModules as $module) {
				$this->loadModule($module, function ($objModule) use ($q, &$arrResults) {

					/** @var \Gratheon\CMS\Module\Behaviour\Searchable $objModule */
					if(method_exists($objModule, 'search_from_public')) {
						$results = $objModule->search_from_public(addslashes($q));
						if($results->count > 0) {
							$arrResults[$objModule->name] = $results;
						}
					}
				});
			}
		}

		$this->assign('title', $this->translate('Search') . ' : ' . $this->in->request['q']);
		$this->assign('search_query', $this->in->request['q']);
		$this->assign('arrResults', $arrResults);

		$this->assign('content_template', 'element.search.tpl');

		return $this->view('layout/front.search.tpl');
	}


	public function rss() { //latest article feed
		$sys_languages = $this->model('sys_languages');
		$content_menu  = $this->model('content_menu');

		$iParentID = $_GET['id'];
		$iLang     = $_GET['lang'];
		if(!$iLang) {
			$iLang = $sys_languages->int("is_default=1", "ID");
		}

		if(!$iParentID && $iLang) {
			$iParentID = $content_menu->int("parentID=1 AND langID='$iLang'", "ID");
		}

		$this->MIME = "text/xml";

		return $this->loadModule('category', function ($objModule) use ($iParentID) {
			/** @var \Gratheon\CMS\Module\Category $objModule */
			return $objModule->front_rss($iParentID);
		});

	}


	public function loadMenuWithSelection($intSelectedNode) {
		$menu = new \Gratheon\CMS\Menu;
		$menu->loadLanguageCount();

		$content_menu = $this->model('content_menu');

		$arrIDs = $menu->buildSelected($intSelectedNode);

		if($this->strMenuType == 'full') {
			$strParents = '';
		}
		else if(count($arrIDs) > 0) {
			$strParents = " t1.parentID IN (" . implode(',', $arrIDs) . ") AND ";
		}
		else {
			$strParents = " t1.parentID='$intSelectedNode'";
		}

		//get menu
		$menu->strWhere = "
			t1.langID='" . $this->langID . "' AND
			$strParents
			t1.ID IN ( 
				SELECT pageID FROM content_menu_rights t2 WHERE
					t2.pageID=t1.ID AND
					t2.groupID='" . $this->user->data['groupID'] . "' AND
					t2.rightID=1 
				) AND ";

		switch($this->strMenuType) {
			case 'double1-x':
				$arrFirstLevelTree = array();
				$arrSubTree        = array();

				$menu->buildTree($arrFirstLevelTree, $arrIDs, $arrIDs[1], 1);
				if(isset($arrIDs[2])){
					$menu->buildTree($arrSubTree, $arrIDs, $arrIDs[2], 1);
				}

				$this->assign('arrFirstLevelTree', $arrFirstLevelTree);
				$this->assign('arrSubTree', $arrSubTree);
				break;

			case 'hierarchical':
				$arrTree = array();
				$menu->buildTree($arrTree, $arrIDs, $arrIDs[1]);
				$this->assign('arrMenu', $arrTree);
				break;
//
			case 'full':
				$arrTree = array();
//				echo '<pre>';
				$menu->buildFullTree($arrTree, array(1), $arrIDs[1], 5);
//				print_r($arrIDs);
//				print_r($arrTree);
				$this->assign('arrMenu', $arrTree);
				break;
		}

		//breadcrumbs
		if($arrIDs) {
			foreach($arrIDs as $ID) {
				if($ID == 1) {
					continue;
				}
				$arrMenuItem['id']    = $ID;
				$arrMenuItem['link']  = $menu->getPageURL($ID);
				$arrMenuItem['title'] = $content_menu->int($ID, 'title');
				$arrMenuNavigation[]  = $arrMenuItem;
			}
		}

		//empty from field

		//$this->assign('intMenuDeep', $intOpenDeepness);
		$this->assign('arrMenuSelected', $arrIDs);
		$this->assign('arrMenuNavigation', $arrMenuNavigation);
		$menu->strFrom = '';

	}
}