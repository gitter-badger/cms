<?php
/**
 * @author Artjom Kurapov
 * @since 07.09.11 23:07
 */
namespace Gratheon\CMS\Model;

class NewsImage extends \Gratheon\Core\Model{
	use ModelSingleton;

	final function __construct(){
		parent::__construct('content_news_images');
	}

}


class content_news_images_record extends \Gratheon\Core\Record{
	/** @var int */ 	public $newsID;
	/** @var int */ 	public $langID;
	/** @var string */ 	public $title;
	/** @var string */ 	public $content;
}
