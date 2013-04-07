<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 21:33
 *
 * @method content_menu_record obj
 */

namespace Gratheon\CMS\Model;

class Menu extends \Gratheon\Core\Model {

	use ModelSingleton;

	final function __construct() {
		parent::__construct('content_menu');
	}

	public function increaseChildPositionsAfterEq($parentID, $position = 0) {
		$this->update(
			"position = position+1",
			"parentID = '$parentID' AND position>='$position'"
		);
	}

	public function reorderChildPositions($parentID) {
		$children = $this->arr("parentID = '$parentID' ORDER BY `position` DESC");
		$position = 0;

		if($children) {
			foreach($children as $record) {
				$this->update(
					array(
						"position" => $position,
						"ID"       => $record->ID
					)
				);
				$position++;
			}
		}
	}


	/**
	 * @param $ID
	 * @param string $strModule
	 * @param string $strMethod
	 *
	 * @return content_menu_record
	 */
	function getMenuRecord($ID, $strModule = 'article', $strMethod = 'front_view') {
		return $this->q(
			"SELECT *,
				DATEDIFF(NOW(),date_added) AS diff,
				UNIX_TIMESTAMP(date_added) AS date_added_unix
			FROM content_menu
			WHERE ID='$ID' AND module='$strModule' AND method='$strMethod'
			ORDER BY date_added DESC", "object"
		);
	}
}

class content_menu_record extends \Gratheon\Core\Record {
	/** @var int */
	public $ID;

	/** @var int */
	public $parentID;

	/** @var int */
	public $langID;

	/** @var int */
	public $userID;

	/** @var string */
	public $title;

	/** @var string */
	public $position;

	/** @var string */
	public $date_added;

	/** @var string */
	public $module;

	/** @var string */
	public $utime;

	/** @var string */
	public $time_added_iso8601;

	/** @var string */
	public $url;

	/** @var Array */
	public $rights;

	/** @var \Gratheon\Core\Record */
	public $element;

}