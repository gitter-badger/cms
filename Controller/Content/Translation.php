<?php

namespace Gratheon\CMS\Controller\Content;
use Gratheon\CMS;

/**
 * @author Artjom Kurapov
 */
class Translation extends \Gratheon\CMS\Controller\Content\ProtectedContentController {
	public $per_page = 20;
	public $preinit_languages = true;
	public $load_config = true;

	const NAME = 'translation';

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
			$item->link_delete    = sys_url . '/content/translation/delete_translation/?id=' . $item->ID;
			$item->link_edit      = sys_url . '/content/translation/edit_translation/?id=' . $item->ID;
			$item->link_edit_ajax = 'translation/edit_translation/?id=' . $item->ID;
		}

		//Create page navigation for first page


		$objPaginator = new CMS\Paginator($this->in, $total_count, $intPage, $this->per_page);

		$this->assign('objPaginator', $objPaginator);

		$this->assign('title', $this->translate('Translations'));
		$this->assign('title_badge', $total_count);
		$this->assign('application', $strApp);
		$this->assign('arrAvailableLanguages', $sys_languages->arr());
		$this->assign('keyword', $strKey);
		$this->assign('arrData', $arrList);

		$this->assign('link_add', sys_url . '/content/translation/edit_translation/');
		$this->assign('link_filter', sys_url . '/content/translation/list_translations/');
		$this->assign('link_import', sys_url . '/content/translation/import_translations/');

		return $this->view('controller_page/translation/' . __FUNCTION__ . '.tpl');
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

		$this->assign('link_import', sys_url . '/content/translation/import_translations/');
		return $this->view('controller_page/translation/' . __FUNCTION__ . '.tpl');
	}


	function delete_translation() {
		$sys_translations = $this->model('sys_translations');

		if($_GET['id']) {
			$sys_translations->delete((int)$_GET['id']);
		}

		$this->redirect(sys_url . '/content/translation/list_translations/');
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
			$this->redirect(sys_url . '/content/translation/list_translations/');
		}

		if($_GET['id']) {
			$objItem = $sys_translations->obj((int)$_GET['id']);
			$this->assign('item', $objItem);
		}

		$this->assign('arrAvailableLanguages', $arrAvailableLanguages);
		$this->assign('link_save', sys_url . '/content/translation/edit_translation/?id=' . $_GET['id']);

		return $this->view('controller_page/translation/' . __FUNCTION__ . '.tpl');
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
}