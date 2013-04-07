<?php
/**
 * Article draft
 *
 * @author Artjom Kurapov
 * @since 22.08.11 21:57
 */
namespace Gratheon\CMS\Model;
use Gratheon\CMS;

class ArticleAutodraft extends \Gratheon\Core\Model {
	use ModelSingleton;


	final function __construct() {
		parent::__construct('content_article_autodraft');
	}


	public function add_draft($intArticle, $strTitle, $strContent) {
		if(trim($strContent) == '') {
			return false;
		}


		$recArticle          = new CMS\Entity\ArticleDraftRecord();
		$recArticle->content = trim(stripslashes($strContent));
		$recArticle->title   = trim(stripslashes($strTitle));
		;
		$recArticle->date_added = 'NOW()';

		if($intArticle) {

			$content_article = new CMS\Model\Article;
			$objArticle      = $content_article->obj("parentID='$intArticle'");

			$recArticle->nodeID  = $intArticle;
			$recArticle->content = $content_article->encodeImages($recArticle->content, $recArticle->nodeID);


			$objLastChange = $this->obj('nodeID=' . $recArticle->nodeID);

			if($objLastChange->content != $recArticle->content && $objArticle->content != $recArticle->content) {
				$this->insert($recArticle);
			}
		}
		else {
			$this->insert($recArticle);
		}

		return true;
	}
}