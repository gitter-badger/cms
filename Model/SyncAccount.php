<?php
/**
 * @author Artjom Kurapov
 * @since 22.08.11 22:14
 * @method content_article_record obj
 */
namespace Gratheon\CMS\Model;

class SyncAccount extends \Gratheon\Core\Model{
	private static $instance;

	/**
	 * @return sys_sync_account
	 */
	public static function singleton(){
		if(!isset(self::$instance)){
			$c = __CLASS__;
            self::$instance = new $c;
		}
		return self::$instance;
	}

	final function __construct(){
		parent::__construct('sys_sync_account');
	}


	public function setPassword($sValue, $ID){
		$this->q("UPDATE sys_sync_account SET password=ENCODE('$sValue','".\SiteConfig::db_encrypt_salt."') WHERE ID='$ID'");
	}
}

class sys_sync_account_record extends \Gratheon\Core\Record{
	/** @var string */ 	public $login;
	/** @var string */ 	public $password;
	/** @var int */ 	public $user_id;
	/** @var int */ 	public $domain;

	/** @var string */ 	public $key;
	/** @var string */ 	public $key2;
	/** @var string */ 	public $key3;
	/** @var string */ 	public $key4;
}
