<?php
namespace Gratheon\CMS;

/**
 * Sync updates SQL schema and data according to written update-files
 * @version 1.0.0
 */
class Sync extends \Gratheon\Core\Model {

	var $bUpdateSuccess = true;
	var $bReloadNeeded = false;
	var $bStopsUpgradeOnFailure = true;


	function ask($query) {
		pre($this->adapter->mysql_error);

		$this->q($query);
		if($this->adapter->mysql_error) {
			pre($this->adapter->mysql_error);
			$this->bUpdateSuccess = false;
		}
	}


	function existsFK($strTable, $strName) {
		$strTableSQL = $this->q('SHOW CREATE TABLE `' . $strTable . '`', 'int');

		preg_match_all("/CONSTRAINT\s*`(.*)`\s*FOREIGN KEY\s*\(`(.*)`\)\s*REFERENCES\s*`(.*)`/Ui", $strTableSQL, $arrMatches);

		return in_array($strName, $arrMatches[1]);
	}


	function existsTableField($strTable, $strField) {
		$arrRows = $this->q("SHOW COLUMNS FROM `{$strTable}`");

		foreach($arrRows as $arrRow) {
			if($arrRow->Field == $strField) {
				return true;
			}
		}
		return false;
	}


	function existsTableFieldType($strTable, $strField, $strType = null) {
		$arrRows = $this->q("SHOW COLUMNS FROM `{$strTable}`");
		foreach($arrRows as $arrRow) {
			if($arrRow->Field == $strField AND (!$strType || $arrRow->Type == $strType)) {
				//pre($strTable);
				//pre($arrRow);
				return true;
			}
		}
		return false;
	}


	function existsTableFieldKey($strTable, $strField, $strType = null) {
		$arrRows = $this->q("SHOW COLUMNS FROM `{$strTable}`");
		foreach($arrRows as $arrRow) {
			if($arrRow->Field == $strField AND (!$strType || $arrRow->Key == $strType)) {
				return true;
			}
		}
		return false;
	}


	function existsTableForeignKey($strTable, $strField) {
		$oResult = $this->q("SHOW CREATE TABLE `{$strTable}`", 'ray');
		$sResult = $oResult[0]['Create Table'];

		if(strpos($sResult, "FOREIGN KEY (`" . $strField . "`) REFERENCES") !== false) {
			return true;
		}

		return false;
	}


	function existsTable($strTable) {
		$arrTables = $this->q("SHOW TABLES", 'arrint');
		if(in_array($strTable, $arrTables)) return true;
		else {
			return false;
		}
	}


	function existsIndex($strTable, $strIndex) {
		$arrRows = $this->q("SHOW INDEX FROM `$strTable` WHERE Key_name = '$strIndex'");

		return count($arrRows);

	}
}