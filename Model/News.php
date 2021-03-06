<?php
/**
 * @author Artjom Kurapov
 * @since 04.09.11 13:39
 */
namespace Gratheon\CMS\Model;

class News extends \Gratheon\Core\Model{
	use ModelSingleton;

	final function __construct(){
		parent::__construct('content_news');
	}

}


class content_news_record extends \Gratheon\Core\Record{
	/** @var int */ 	public $userID;
	/** @var int */ 	public $categoryID;
	/** @var string */ 	public $date_added;
	/** @var string */ 	public $date_open_from;
	/** @var string */ 	public $date_open_to;
}
