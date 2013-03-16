<?php
/**
 * @author Artjom Kurapov
 * @since 25.08.11 21:33
 * 
 * @method content_menu_record obj
 */

namespace Gratheon\CMS\Model;

class Menu extends \Gratheon\Core\Model {
	private static $instance;

	/**
	 * @return content_menu
	 */
	public static function singleton(){
		if(!isset(self::$instance)){
			$c = __CLASS__;
            self::$instance = new $c;
		}
		return self::$instance;
	}

	final function __construct(){
		parent::__construct('content_menu');
	}

	final public function __clone(){
		trigger_error('Cloning not allowed on a singleton object', E_USER_ERROR);
	}
/*
	final public function __destruct(){
		if($_GET['a'])
		pre(debug_backtrace());
		unset(self::$instance);
	}
*/
	/**
	 * @param $ID
	 * @param string $strModule
	 * @param string $strMethod
	 * @return content_menu_record
	 */
	function getMenuRecord($ID, $strModule='article',$strMethod = 'front_view'){
		return $this->q(
			"SELECT *,
				DATEDIFF(NOW(),date_added) as diff,
				UNIX_TIMESTAMP(date_added) as date_added_unix
			FROM content_menu
			WHERE ID='$ID' AND module='$strModule' AND method='$strMethod'
			ORDER BY date_added DESC","object"
		);
	}
}

class content_menu_record extends \Gratheon\Core\Record{
	/** @var int */ 	public $ID;
	/** @var int */ 	public $parentID;
	/** @var int */ 	public $langID;
	/** @var int */ 	public $userID;
	/** @var string */ 	public $title;
	/** @var string */ 	public $position;
	/** @var string */ 	public $date_added;
	/** @var string */ 	public $module;
	/** @var string */ 	public $utime;
	/** @var string */ 	public $time_added_iso8601;
	/** @var string */ 	public $url;
	/** @var Array */ 	public $rights;
	/** @var \Gratheon\Core\Record */ 	public $element;

}