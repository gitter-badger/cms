<?php

namespace Gratheon\CMS\Controller\Content;
use Gratheon\CMS;

/**
 * Settings - translations, users etc.
 * @author Artjom Kurapov
 * @version 1.1.1
 * @p
 *
 */
class Settings extends \Gratheon\CMS\Controller\Content\ProtectedContentController {
	public $per_page = 20;
	public $preinit_languages = true;
	public $load_config = true;

	var $arrModulesExcluded = array("'comment'", "'image'", "'file'", "'video'");


	function main() {

		$this->redirect(sys_url . '/content/settings/view_settings/');
	}


	function view_settings() {
		$sys_config       = $this->model('sys_config');
		$sys_languages    = $this->model('sys_languages');
		$sys_sync_account = $this->model('sys_sync_account');

		if($this->in->post) {
			foreach($this->in->post['var_name'] as $sApp => $aValue) {
				foreach($aValue as $sKey => $sValue) {
					$recVar = $sys_config->obj("var_name='$sKey' AND application='$sApp'");


					switch($recVar->var_type) {
						case 'password':
							if($sValue) {
								$recVar->var_value_binary = $sValue;
								$sys_config->update("var_value_binary=ENCODE('$sValue','" . \SiteConfig::db_encrypt_salt . "')", "var_name='$sKey' AND application='$sApp'");
							}
							break;

						default:
							$recVar->var_value = $sValue;
							$sys_config->update($recVar, "var_name='$sKey' AND application='$sApp'");
							break;

					}

				}
			}

			$sys_config->update("`var_value`='" . date('Y-m-d H:i') . "'", "var_name='setting_save_time' AND `application`='content'");
		}

		$this->assign('title', $this->translate('Settings'));
		$this->assign('info', array($this->translate('You can configure module behaviour here')));

		$arrCategories = $sys_config->arrint("1=1 ORDER BY sorting", "DISTINCT(application)");

		foreach($arrCategories as $strCategory) {
			$arrVars = $sys_config->arr("application='$strCategory' ORDER BY sorting, application");

			foreach($arrVars as &$arrVar) {
				$aSyncVar = explode('_', $arrVar->var_name);

				if(end($aSyncVar) == 'language') {
					$arrVar->select_values = $sys_languages->map("1=1", "ID, native `key`");
				}
				elseif($aSyncVar[0] == 'sync') {
					$arrVar->select_values = $sys_sync_account->map("service='{$aSyncVar[1]}'", "ID,CONCAT(domain, ' #', ID) `key`");
				}
				elseif($arrVar->var_select_values) {
					$arrSelectValues = explode('|', $arrVar->var_select_values);
					foreach($arrSelectValues as $sKey) {
						$arrVar->select_values[$sKey] = $this->translate('111', null, array('code' => 'module_' . $arrVar->application . '_var_' . $arrVar->var_name . '_val_' . $sKey));
					}
				}
			}
			$arrConfigVars[$strCategory] = $arrVars;
		}

		$this->assign('link_save', sys_url . '/content/settings/view_settings/');
		$this->assign('arrConfigVars', $arrConfigVars);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	public function view_diagnostics() {
		$d = new \Gratheon\CMS\Model\Diagnostics();

		function decodeSize($bytes) {
			$types = array('B', 'KB', 'MB', 'GB', 'TB');
			for($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) {
				;
			}
			return (round($bytes, 2) . " " . $types[$i]);
		}

		$this->assign('support_mysql', $d->extentsionMySQLExists());
		$this->assign('support_gd', $d->extentsionGDExists());
		$this->assign('support_iconv', $d->functionIconvExists());

		list($free_hdd_percent, $total_hdd) = $d->getFreeSpace();
		list($free_ram_percent, $total_ram) = $d->getServerMemoryStatus();

		$this->assign('memory_limit', $d->checkMemoryLimit());

		$this->assign('free_ram_percent', $free_ram_percent);
		$this->assign('total_ram', decodeSize($total_ram));

		$this->assign('server_free_space_percent', $free_hdd_percent);
		$this->assign('server_total_space', decodeSize($total_hdd));
		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	//Sync accounts
	function redirect_sync() {
		$sys_sync_account = $this->model('sys_sync_account');

		$_SESSION['sync'] = $arrSync = (array)$sys_sync_account->obj($_GET['id']);
		//pre($_SESSION['sync']);

		$objSocial = new SocialUser();
		$objSocial->getSocialProfile($arrSync['service'], $this->in->sURL);

		$this->redirect('external_services/twitter_oauth/redirect.php');
	}


	function list_sync_accounts() {


		$sys_sync_account = $this->model('sys_sync_account');
		if($_SESSION['sync']['ID'] && $_SESSION['sync']['access_token']) {
			$sys_sync_account->update(array(
				'key3'    => $_SESSION['sync']['access_token']['oauth_token'],
				'key4'    => $_SESSION['sync']['access_token']['oauth_token_secret'],

				'login'   => $_SESSION['sync']['access_token']['screen_name'],
				'user_id' => $_SESSION['sync']['access_token']['user_id'],
			), "ID={$_SESSION['sync']['ID']}");
		}

		//pre($_SESSION['sync']);

		$arrAccounts = $sys_sync_account->arr();

		foreach($arrAccounts as &$arrAccount) {
			$arrAccount->link_connect   = sys_url . 'content/settings/redirect_sync/?ID=' . $arrAccount->ID;
			$arrAccount->link_edit      = sys_url . '/content/settings/edit_sync_account/?id=' . $arrAccount->ID;
			$arrAccount->link_edit_ajax = 'settings' . '/edit_sync_account/?id=' . $arrAccount->ID;
		}

		$this->assign('arrAccounts', $arrAccounts);
		$this->assign('title', $this->translate('Sync accounts'));

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function edit_sync_account() {
		/** @var $sys_sync_account \Gratheon\CMS\Model\SyncAccount  */

		$sys_sync_account = $this->model('SyncAccount');
		//$sys_sync_account = $this->model('sys_sync_account');
		$ID               = (int)$_GET['id'];

		if($ID) {
			$objItem = $sys_sync_account->obj($ID);


			$strService = ucfirst($objItem->service) . 'Service';
			$strClass = '\Gratheon\CMS\Service\\'. $strService;

			if(class_exists($strClass)){
				/**
				 * @var $objService \Gratheon\CMS\Service\DefaultService
				 */
				$objService = new $strClass;

				switch($objItem->service){
					case 'facebook':
						if($this->config('sync_facebook')) {
							$strFacebook      = $sys_sync_account->int($this->config('sync_facebook'), "`key`");

							if($strFacebook) {
								$this->add_js_var('facebook_commenting_api', $strFacebook);
								$this->assign('facebook_commenting_api', $strFacebook);
							}
						}
					break;

					case 'twitter':
						$this->assign('link_sync', sys_url . 'content/settings/redirect_sync/?id=' . $ID);
						break;

					case 'vkontakte':
						$this->assign('link_sync', 'https://oauth.vk.com/authorize?client_id='.$this->config('vkontakte_key').'&scope=SETTINGS&redirect_uri='.sys_url.'content/settings/oauth_connect/&response_type=code');
						break;
				}
				$this->assign('service', $objService);
			}

			$this->assign('item', $objItem);


			if($this->in->post) {
				$recAccount        = new \stdClass();
				$recAccount->login = $this->in->post['login'];
				$sys_sync_account->update($recAccount, "ID='$ID'");

				if($this->in->post['password']) {
					$sys_sync_account->setPassword($this->in->post['password'], $ID);
				}

				//$this->redirect(sys_url . '/content/settings/list_sync_accounts/');
			}
		}

		$this->assign('link_save', sys_url . '/content/settings/edit_sync_account/?id=' . $ID);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function oauth_connect(){
	}

	//Translations
	function list_translations() {

		$this->use_gz     = false;
		$sys_languages    = $this->model('sys_languages');
		$sys_translations = $this->model('sys_translations');

		if($this->in->post['export']) {
			return $this->export_translations();
		}

		$intPage = isset($_GET['page']) ? (int)$_GET['page'] : ($_SESSION['content']['translations']['list_filter']['page'] ? (int)$_SESSION['content']['translations']['list_filter']['page'] : 1);

		if(isset($this->in->post['application'])) {
			$_SESSION['content']['translations']['list_filter']['application'] = $this->in->post['application'];
		}

		if(isset($this->in->post['keyword'])) {
			$_SESSION['content']['translations']['list_filter']['keyword'] = $this->in->post['keyword'];
		}

		$strApp = $_SESSION['content']['translations']['list_filter']['application'];
		$strKey = $_SESSION['content']['translations']['list_filter']['keyword'];

		$offset = $this->per_page * ($intPage - 1);

		$arrLanguages = $sys_languages->arrint("1=1", "ID");
		$strFilter    = '';

		if($strApp) {
			$strFilter .= " AND application='$strApp'";
		}

		if($strKey) {
			$strFilter .= " AND ( code LIKE '%$strKey%' ";
			foreach($arrLanguages as $iKey => $sLang) {
				$strFilter .= " OR $sLang LIKE '%$strKey%'";
				//if($iKey+1<count($arrLanguages)) $strFilter.=" OR ";
			}
			$strFilter .= ")";
		}

		//query block
		$arrList = $sys_translations->arr(
			"1=1 $strFilter
		ORDER BY date_added DESC
		LIMIT " . $offset . ',' . $this->per_page, "SQL_CALC_FOUND_ROWS *");

		$total_count = $sys_translations->count();
		foreach($arrList as &$item) {
			$item->link_delete    = sys_url . '/content/settings/delete_translation/?id=' . $item->ID;
			$item->link_edit      = sys_url . '/content/settings/edit_translation/?id=' . $item->ID;
			$item->link_edit_ajax = 'settings' . '/edit_translation/?id=' . $item->ID;
		}

		//Create page navigation for first page


		$objPaginator = new CMS\Paginator($this->in, $total_count, $intPage, $this->per_page);
//		$objPaginator->url=sys_url.'/content/'.'settings'.'/list_translations/';
		$this->assign('objPaginator', $objPaginator);

		$this->assign('title', $this->translate('Translations'));
		$this->assign('title_badge', $total_count);
		$this->assign('application', $strApp);
		$this->assign('arrAvailableLanguages', $sys_languages->arr());
		$this->assign('keyword', $strKey);
		$this->assign('arrData', $arrList);

		$this->assign('link_add', sys_url . '/content/settings/edit_translation/');
		$this->assign('link_filter', sys_url . '/content/settings/list_translations/');
		$this->assign('link_import', sys_url . '/content/settings/import_translations/');

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function import_translations() {


		$sys_translations = $this->model('sys_translations');

		function getCSVValues($sString, $sSeparator = ",") {
			$sString   = str_replace('""', "'", $sString);
			$aBits     = explode($sSeparator, $sString);
			$aElements = array();

			for($i = 0; $i < count($aBits); $i++) {
				if(($i % 2) == 1) {
					$aElements[] = $aBits[$i];
				}
				else {
					$sRest     = $aBits[$i];
					$sRest     = preg_replace("/^" . $sSeparator . "/", "", $sRest);
					$sRest     = preg_replace("/" . $sSeparator . "$/", "", $sRest);
					$aElements = array_merge($aElements, explode($sSeparator, $sRest));
				}
			}
			return $aElements;
		}

		if($_FILES) {
			$sCSVFileData = file_get_contents($_FILES['file']['tmp_name']);

			if(mb_detect_encoding($sCSVFileData)) {
				$sCSVFileData = mb_convert_encoding(mb_substr($sCSVFileData, 2, mb_strlen($sCSVFileData)), "UTF-8", mb_detect_encoding($sCSVFileData));
			}
			else {
				$sCSVFileData = mb_convert_encoding(mb_substr($sCSVFileData, 2, mb_strlen($sCSVFileData)), "UTF-8", "UTF-16LE");
			}

			$aCSVLines = explode("\n", $sCSVFileData);
			for($i = 0; $i < count($aCSVLines); $i++) {
				$arrRow = getCSVValues($aCSVLines[$i], "\t");

				if($i == 0) {
					$arrFirstRow = $arrRow;
				}
				else {

					$strWhere = "application='{$arrRow[0]}'";

					if($arrRow[1]) {
						$strWhere .= " AND code='{$arrRow[1]}'";
					}
					elseif($arrRow[2]) {
						$strWhere .= " AND eng='{$arrRow[2]}'";
					}

					$aEx = $sys_translations->obj($strWhere);

					foreach($arrRow as $iKey => $strKey) {
						if($iKey > 1 && $iKey < count($arrRow) - 2) {
							$arrNewRow[$arrFirstRow[$iKey]] = $arrRow[$iKey];
						}
					}

					if($aEx) {
						if($this->in->post['overwrite']) {
							$sys_translations->update($arrNewRow, "ID='{$aEx->ID}'");
							$intUpdates++;
						}
						else {
							$intSkipped++;
						}
					}
					else {
						$arrNewRow['application'] = $arrRow[0];

						if($arrRow[1]) {
							$arrNewRow['code'] = $arrRow[1];
						}
						$sys_translations->insert($arrNewRow);
						$intInserts++;
					}
				}
				unset($arrNewRow);
			}

			$this->assign('import_info', array(
				'inserts' => (int)$intInserts,
				'updates' => (int)$intUpdates,
				'skipped' => (int)$intSkipped,
			));
		}

		$this->assign('link_import', sys_url . '/content/settings/import_translations/');
		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function delete_translation() {
		$sys_translations = $this->model('sys_translations');

		if($_GET['id']) {
			$sys_translations->delete((int)$_GET['id']);
		}

		$this->redirect(sys_url . '/content/settings/list_translations/');
	}


	function edit_translation() {
		$sys_translations = $this->model('sys_translations');
		$sys_languages    = $this->model('sys_languages');


		$arrAvailableLanguages = $sys_languages->arr();

		if($this->in->post) {
			$objItem->application = $this->in->post['application'];
			if(trim($this->in->post['code'])) {
				$objItem->code = trim($this->in->post['code']);
			}

			foreach($arrAvailableLanguages as $aLang) {
				$objItem->{$aLang->ID} = $this->in->post[$aLang->ID];
			}

			if((int)$_GET['id']) {
				$sys_translations->update($objItem, 'ID=' . $_GET['id']);
			}
			else {
				$objItem->date_added = 'NOW()';
				$sys_translations->insert($objItem);
			}
			$this->redirect(sys_url . '/content/settings/list_translations/');
		}

		if($_GET['id']) {
			$objItem = $sys_translations->obj((int)$_GET['id']);
			$this->assign('item', $objItem);
		}

		$this->assign('arrAvailableLanguages', $arrAvailableLanguages);
		$this->assign('link_save', sys_url . '/content/settings/edit_translation/?id=' . $_GET['id']);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function export_translations() {

		$sys_translations = $this->model('sys_translations');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename=SomeFile_' . time() . '.csv;');
		header('Content-Transfer-Encoding: binary');


		mb_internal_encoding("UTF-8");

		$strApp = $_SESSION['content']['translations']['list_filter']['application'];
		$strKey = $_SESSION['content']['translations']['list_filter']['keyword'];

		$strCSV    = '';
		$strFilter = '';

		if($strApp) {
			$strFilter .= " AND application='$strApp'";
		}
		if($strKey) {
			$strFilter .= " AND ( code LIKE '%$strKey%' ) ";
		}

		//query block
		$arrList = $sys_translations->ray(
			"1=1 $strFilter
		ORDER BY date_added DESC");


		$arrRow = array_keys($arrList[0]);
		foreach($arrRow as $key => $strValue) {
			if(in_array($key, array('ID'))) {
				continue;
			}
			$strCSV .= $strValue . "\t";
		}
		$strCSV .= "\n";

		foreach($arrList as $arrRow) {
			foreach($arrRow as $key => $strValue) {
				if(in_array($key, array('ID'))) {
					continue;
				}
				$strCSV .= $strValue . "\t";
			}
			$strCSV .= "\n";
		}


		$strCSV = chr(255) . chr(254) . mb_convert_encoding($strCSV, "UTF-16LE", "UTF-8");
		header('Content-Length: ' . strlen($strCSV));
		echo $strCSV;
		//pre($arrList);
		exit();
	}


	//Email templates
	function list_emails() {
		$this->use_gz = false;


		$sys_email_templates = $this->model('sys_email_templates');

		$offset = isset($_GET['page']) ? $this->per_page * ($_GET['page'] - 1) : 0;
		if($offset < 0) {
			$offset = 0;
		}


		//query block
		$arrList = (array)$sys_email_templates->arr(
			"1=1 ORDER BY moduleID, title
		LIMIT " . $offset . ',' . $this->per_page,
			"title, ID,tag"
		);

		$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		if($arrList) {
			foreach($arrList as &$objItem) {
				$objItem->link_edit = sys_url . '/content/settings/edit_email/?id=' . $objItem->ID;
			}
			$this->assign('info', array($this->translate('In order to make emails friendlier and at the same time easier to manage, following templates are used')));
		}
		else {
			$this->assign('info', array($this->translate('No templates were found')));
		}

		#Create page navigation for first page
		$objPaginator      = new CMS\Paginator($this->in, $sys_email_templates->count(), $intPage, $this->per_page);
		$objPaginator->url = sys_url . '/content/settings/' . __FUNCTION__ . '/';

		$this->assign('title', $this->translate('Email templates'));
		$this->assign('objPaginator', $objPaginator);
		$this->assign('arrList', $arrList);


		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function edit_email() {

		$ID = (int)$_GET['id'];

		$sys_email_templates = $this->model('sys_email_templates');
		if($this->in->post) {
			$objItem->title = $this->in->post['title'];
			$objItem->html  = stripslashes($this->in->post['html']);
			$objItem->text  = stripslashes($this->in->post['text']);
			$objItem->ID    = (int)$_GET['id'];

			$sys_email_templates->update($objItem, "ID=$ID");

			$this->redirect(sys_url . '/content/settings/list_emails/');
		}

		if($ID) {
			$objItem = $sys_email_templates->obj($ID);
			$this->assign('arrEmail', $objItem);
		}

		$this->assign('link_list', sys_url . '/content/settings/list_emails/');
		$this->assign('link_save', sys_url . '/content/settings/' . __FUNCTION__ . '/?id=' . $ID);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function list_connections() {

		$this->use_gz = false;
		$content_menu = $this->model('Menu');

		$tpl_links           = $this->model('tpl_links');
		$sys_email_templates = $this->model('sys_email_templates');

		$offset = isset($_GET['page']) ? $this->per_page * ($_GET['page'] - 1) : 0;
		if($offset < 0) {
			$offset = 0;
		}


		//query block
		$arrList = (array)$tpl_links->arr(
			"1=1 ORDER BY description
		LIMIT " . $offset . ',' . $this->per_page,
			"*"
		);

		$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

		if($arrList) {
			foreach($arrList as &$objItem) {
				$objItem->link_edit = sys_url . '/content/settings/edit_connection/?id=' . $objItem->ID;
				$objItem->arrLinks  = $content_menu->ray(
					"t2.connectionID='{$objItem->ID}'",
					"t1.title, t1.ID, t3.native language",
					"content_menu t1
					INNER JOIN tpl_links_page t2 ON t2.pageID=t1.ID
					INNER JOIN sys_languages t3 ON t3.ID = t1.langID"

				);

			}
			$this->assign('info', array($this->translate('Connecting several pages from diffrent languages makes it possible to use direct links from templates to dynamic pages.
This way written source can be connected with changeable destination')));
		}
		else {
			$this->assign('info', array($this->translate('No connections were found')));
		}

		#Create page navigation for first page
		$objPaginator = new CMS\Paginator($this->in, $sys_email_templates->count(), $intPage, $this->per_page);
		//$objPaginator->url=sys_url.'/content/'.'settings'.'/'.__FUNCTION__.'/';

		$this->assign('title', $this->translate('Page connections'));
		$this->assign('info', array($this->translate('Connections between pages are needed to have stable links from code while having dynamic menu structure and also to logically connect different language trees')));
		$this->assign('objPaginator', $objPaginator);
		$this->assign('arrList', $arrList);
		$this->assign('link_add_connection', sys_url . '/content/settings/edit_connection/');

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function edit_connection() {

		$ID = (int)$_GET['id'];

		$tree = new \Gratheon\CMS\Tree;

		$tpl_links      = $this->model('tpl_links');
		$tpl_links_page = $this->model('tpl_links_page');

		if($this->in->post) {
			if($ID) {
				$objItem->description = ($this->in->post['description']);
				$objItem->tag         = ($this->in->post['tag']);

				$tpl_links->update($objItem, "ID=$ID");

				$tpl_links_page->delete("connectionID='$ID'");

				if($this->in->post['pageIDs']) {
					foreach($this->in->post['pageIDs'] as $pageID) {
						$recConnection->pageID       = $pageID;
						$recConnection->connectionID = $ID;
						$tpl_links_page->insert($recConnection);
					}
				}
			}
			else {
				$objItem->description = ($this->in->post['description']);
				$objItem->tag         = ($this->in->post['tag']);

				$ID = $tpl_links->insert($objItem);

				if($this->in->post['pageIDs']) {
					foreach($this->in->post['pageIDs'] as $pageID) {
						$recConnection->pageID       = $pageID;
						$recConnection->connectionID = $ID;
						$tpl_links_page->insert($recConnection);
					}
				}
			}
			$this->redirect(sys_url . '/content/settings/list_connections/');
		}

		if($ID) {
			$objItem = $tpl_links->obj($ID);

			$objItem->pageIDs = $tpl_links_page->arrint("connectionID='$ID'", "pageID");
			$this->assign('arrItem', $objItem);
		}

		$tree->strWhere = "
			t1.module NOT IN (" . implode(',', $this->arrModulesExcluded) . ") AND  ";

		$tree->initialize(5, false);

		$recSelected->ID = 1;
		$arrTree         = $tree->flatTree;

		$this->assign('arrTree', $arrTree);

		$this->assign('link_list', sys_url . '/content/settings/list_connections/');
		$this->assign('link_save', sys_url . '/content/settings/' . __FUNCTION__ . '/?id=' . $ID);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function getFilterValue($value, $strFunction) {
		if(isset($_REQUEST[$value])) {
			$strValue = $_SESSION['settings'][$strFunction]['filter'][$value] = $_REQUEST[$value];
		}
		elseif($_SESSION['settings'][$strFunction][$value]) {
			$strValue = $_SESSION['settings'][$strFunction]['filter'][$value];
		}
		return $strValue;
	}


	//Users
	function list_users() {


		$this->use_gz   = false;
		$sys_user       = $this->model('sys_user');
		$sys_user_group = $this->model('sys_user_group');

		$strFunction = strtolower(__FUNCTION__);
		$intPage     = isset($_GET['page']) ? (int)$_GET['page'] : (int)$_SESSION['content']['users']['list_filter']['page'];


		//Filter on top
		$strFilter = '1=1';
		$offset    = isset($_GET['page']) ? $this->per_page * ($_GET['page'] - 1) : 0;

		$intGroup    = $this->getFilterValue('group', $strFunction);
		$strDateFrom = $this->getFilterValue('date_from', $strFunction);
		$strDateTo   = $this->getFilterValue('date_to', $strFunction);
		$strKeyword  = $this->getFilterValue('keyword', $strFunction);

		if($strKeyword) {
			$strFilter .= " AND (
			t1.login LIKE '%" . $strKeyword . "%' OR
			t1.email LIKE '%" . $strKeyword . "%' OR
			t1.firstname LIKE '%" . $strKeyword . "%' OR
			t1.lastname LIKE '%" . $strKeyword . "%'
			)";
		}

		if($intGroup) {
			$strFilter .= " AND t1.groupID='" . $intGroup . "'";
		}

		if($strDateFrom) {
			$arrDate     = explode('.', $strDateFrom);
			$strDateFrom = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
			$strFilter .= " AND t1.date_added>='$strDateFrom 00:00:00'";
		}
		/*
			  else{
			  $_SESSION['settings'][$strFunction]['date_from']=date('d.m.Y',time());
			  $strDateFrom=date('Y-m-d',time());
			  $strFilter.=" AND t1.date_added>='$strDateFrom 00:00:00'";
			  }*/

		if($strDateTo) {
			$arrDate   = explode('.', $strDateTo);
			$strDateTo = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
			$strFilter .= " AND t1.date_added<='$strDateTo 23:59:59'";
		}
		//query block
		$arrList = $sys_user->q(
			"SELECT SQL_CALC_FOUND_ROWS * FROM sys_user
            WHERE $strFilter
            ORDER BY date_added DESC
            LIMIT " . $offset . ',' . $this->per_page
		);

		$total_count  = $sys_user->count();
		$objPaginator = new CMS\Paginator($this->in, $total_count, $intPage, $this->per_page);

		if($arrList) {
			foreach($arrList as &$item) {
				$item->link_delete    = sys_url . '/content/settings/delete_user/?id=' . $item->ID;
				$item->link_edit      = sys_url . '/content/settings/edit_user/?id=' . $item->ID;
				$item->link_edit_ajax = 'settings' . '/edit_user/?id=' . $item->ID;
			}
		}
		else {
			$this->assign('info', array($this->translate('No users were found')));
		}

		$this->assign('title', $this->translate('Users'));
		$this->assign('title_badge', $total_count);
		$this->assign('objPaginator', $objPaginator);
		$this->assign('filter', array('group' => $this->in->post['group']));
		$this->assign('arrData', $arrList);
		$this->assign('groups', $sys_user_group->arr());
		$this->assign('link_add', sys_url . '/content/settings/edit_user/');
		$this->assign('link_filter', sys_url . '/content/settings/' . $strFunction . '/');
		$this->assign('form_filter', $_SESSION['settings'][$strFunction]);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function delete_user() {

		$sys_user         = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');

		if($_GET['id']) {
			$sys_user->delete((int)$_GET['id']);
			$sys_user_contact->delete('user_id=' . (int)$_GET['id']);
		}
		$this->redirect(sys_url . '/content/settings/list_users/');
	}


	function edit_user() {

		$sys_user         = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');
		$sys_user_group   = $this->model('sys_user_group');
		$sys_languages    = $this->model('sys_languages');
		$iso_countries    = $this->model('iso_countries');

		if($this->in->post) {
			$objItem            = new stdClass();
			$objItem->groupID   = $this->in->post['groupID'];
			$objItem->langID    = $this->in->post['langID'];
			$objItem->login     = $this->in->post['login'];
			$objItem->firstname = $this->in->post['firstname'];
			$objItem->lastname  = $this->in->post['lastname'];
			$objItem->email     = $this->in->post['email'];
			if($this->in->post['password']) {
				$objItem->password = md5($this->in->post['password']);
			}

			$objContact               = new stdClass();
			$objContact->countryID    = $this->in->post['country_id'];
			$objContact->phone_mobile = $this->in->post['phone_mobile'];
			$objContact->post_index   = $this->in->post['post_index'];
			$objContact->home_address = $this->in->post['home_address'];

			$ID = (int)$_GET['id'];

			if($ID) {
				$objContact->userID = $ID;
				$sys_user->update($objItem, "ID=$ID");
				$sys_user_contact->update($objContact, "userID=$ID");
			}
			else {
				$objItem->date_added = 'NOW()';
				$objContact->userID  = $sys_user->insert($objItem);
				$sys_user_contact->insert($objContact);
			}
			//$this->redirect(sys_url . '/content/settings/list_users/');
		}

		if($_GET['id']) {
			$objItem = $sys_user->obj(
				(int)$_GET['id'],
				"*, INET_NTOA(IP) IP",
					$sys_user->table . " t1 LEFT JOIN " .
							$sys_user_contact->table . " t2 ON t2.userID=t1.ID"
			);
			$this->assign('item', $objItem);
		}

		$this->assign('link_list', sys_url . '/content/settings/list_users/');
		$this->assign('groups', $sys_user_group->arr());
		$this->assign('languages', $sys_languages->arr());
		$this->assign('countries', $iso_countries->arr());
		$this->assign('link_save', sys_url . '/content/settings/edit_user/?id=' . $_GET['id']);
		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	//User groups
	function list_groups() {
		$this->use_gz = false;


		$sys_user       = $this->model('sys_user');
		$sys_user_group = $this->model('sys_user_group');

		//query block
		$arrList = $sys_user_group->arr(
			"1=1 ORDER BY date_added DESC");

		if($arrList) {
			foreach($arrList as &$item) {
				//$item->link_delete='/content/settings/delete_user/?id='.$item->ID;
				$item->link_edit  = sys_url . '/content/settings/edit_group/?id=' . $item->ID;
				$item->user_count = $sys_user->int("groupID='" . $item->ID . "'", 'COUNT(id)');
			}
		}
		else {
			$this->assign('info', array($this->translate('No groups were found')));
		}

		$this->assign('title', $this->translate('User groups'));
		$this->assign('filter', array('group' => $this->in->post['group']));
		$this->assign('arrData', $arrList);
		$this->assign('groups', $sys_user_group->arr());
		$this->assign('link_add', sys_url . '/content/settings/edit_group/');
		$this->assign('link_filter', sys_url . '/content/settings/list_groups/');

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}


	function edit_group() {
		$sys_user_group = $this->model('sys_user_group');
		$sys_languages  = $this->model('sys_languages');
		$iso_countries  = $this->model('iso_countries');


		if($this->in->post) {
			$objItem        = new stdClass();
			$objItem->title = $this->in->post['title'];
			$ID             = (int)$_GET['id'];

			if($ID) {
				$sys_user_group->update($objItem, "ID='$ID'");
			}
			else {
				$objItem->date_added = 'NOW()';
				$sys_user_group->insert($objItem);
			}
			$this->redirect(sys_url . '/content/settings/list_groups/');
		}

		if($_GET['id']) {
			$objItem = $sys_user_group->obj(
				(int)$_GET['id']
			);
			$this->assign('item', $objItem);
		}

		$this->assign('link_list', sys_url . '/content/settings/list_groups/');
		$this->assign('groups', $sys_user_group->arr());
		$this->assign('languages', $sys_languages->arr());
		$this->assign('countries', $iso_countries->arr());
		$this->assign('link_save', sys_url . '/content/settings/edit_group/?id=' . $_GET['id']);

		return $this->view('controller_page/settings/' . __FUNCTION__ . '.tpl');
	}
}