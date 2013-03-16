<?php
/**
 * @author Artjom Kurapov
 * @since 07.09.11 23:03
 */
namespace Gratheon\CMS\Model;

class NewsBody extends \Gratheon\Core\Model{
	private static $instance;

	/**
	 * @return content_news
	 */
	public static function singleton(){
		if(!isset(self::$instance)){
			$c = __CLASS__;
            self::$instance = new $c;
		}
		return self::$instance;
	}

	final function __construct(){
		parent::__construct('content_news_body');
	}
}


class content_news_body_record extends \Gratheon\Core\Record{
	/** @var int */ 	public $newsID;
	/** @var int */ 	public $langID;
	/** @var string */ 	public $title;
	/** @var string */ 	public $content;
	/** @var string */ 	public $content_index;
}
