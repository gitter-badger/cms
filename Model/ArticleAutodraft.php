<?php
/**
 * Article draft
 *
 * @author Artjom Kurapov
 * @since 22.08.11 21:57
 */
namespace Gratheon\CMS\Model;

class ArticleAutodraft extends \Gratheon\Core\Model{
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
		parent::__construct('content_article_autodraft');
	}
/*
	final public function __destruct(){
		unset(self::$instance);
	}
*/
	public function add_draft($intArticle,$strTitle, $strContent){
		if (trim($strContent)==''){
			return false;
		}


		$recArticle 			= new content_article_autodraft_record();
		$recArticle->content 	= trim(stripslashes($strContent));
		$recArticle->title 		= trim(stripslashes($strTitle));;
		$recArticle->date_added = 'NOW()';

		if ($intArticle) {

			$content_article = new content_article;
			$objArticle = $content_article->obj("parentID='$intArticle'");

			$recArticle->nodeID 	= $intArticle;
			$recArticle->content 	= $content_article->encodeImages($recArticle->content,$recArticle->nodeID);


			$objLastChange = $this->obj('nodeID='.$recArticle->nodeID);

			if ($objLastChange->content!=$recArticle->content && $objArticle->content!=$recArticle->content){
				$this->insert($recArticle);
			}
		}
		else{
			$this->insert($recArticle);
		}
	}
}


class content_article_autodraft_record extends \Gratheon\Core\Record{
	/** @var string */ 	public $content;
	/** @var string */ 	public $title;
	/** @var string */ 	public $date_added;
	/** @var int */ 	public $nodeID;
}
