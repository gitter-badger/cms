<?php
/**
 * @author Artjom Kurapov
 * @since 22.08.11 22:14
 * @method content_article_record obj
 */
namespace Gratheon\CMS\Model;

class Article extends \Gratheon\Core\Model {
	private static $instance;
	public $imagemodel;


	/**
	 * @return \Gratheon\CMS\Model\Article
	 */
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c              = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}


	final function __construct($test=false) {
		if(!$test)
		parent::__construct('content_article');
	}


	function encodeImages($str, $parentID) {
		$content_image = new \Gratheon\Core\Model('content_image');

		preg_match_all('/<img (style="([^"]*)" )?rel="([0-9]*)" src="([^"]*)"([^>]*)>/i', $str, $arrMatches);
		/*
						$arrInlineImages = $content_menu->arrint("t1.module='image' AND t1.parentID='$parentID'",
						"t2.ID",$content_menu->table.' AS t1 LEFT JOIN '.$content_image->table.' t2 ON t2.parentID=t1.ID');
				*/
		//Set inline later
		if(count($arrMatches[2]) > 0) {
			foreach($arrMatches[2] as $item) {
				$content_image->q("UPDATE content_image SET float_position='inline' WHERE parentID='$item'");
			}
		}

		$str = preg_replace('/<img (style="([^"]*)" )?rel="([0-9]*)" src="([^"]*)"([^>]*)>/i', "<!--image[$3]--$2-->", $str);

		return $str;
	}


	function decodeImages($str, $links = false, $publicView = false, $imageCallback) {
		$content_menu        = new \Gratheon\Core\Model('content_menu');
		$content_image       = $this->imagemodel;
		$content_menu_rating = new \Gratheon\Core\Model('content_menu_rating');

		preg_match_all('/<!--image\[([0-9]*)\]--(([^>]*)--)*>/i', $str, $arrMatches);

		if(count($arrMatches[1]) > 0) {
			foreach($arrMatches[1] as $key => $imageReplacementMatch) {
				if(!$imageReplacementMatch) {
					continue;
				}

				$strStyle = $arrMatches[3][$key];

				$recImage = $content_image->obj("t2.ID='$imageReplacementMatch'", "t1.*, t2.title, t2.ID parentID",
						$content_image->table . " t1 INNER JOIN " .
								$content_menu->table . " t2 ON t2.elementID=t1.ID");

				if(!isset($recImage->ID)) {
					continue;
				}


				$strRating = '';
				if($publicView) {
					$arrRatings = $content_menu_rating->map("parentID='$imageReplacementMatch'", "xrate_tag, rating");
					if($arrRatings) {
						foreach($arrRatings as $k=> $v) {
							$strRating .= "data-xrate-" . $k . "='" . $v['rating'] . "' ";
						}
					}
				}

				$recImage->link_original = $content_image->getURL($recImage);
				switch($recImage->thumbnail_type) {
					case 'thumb':
						$recImage->link_src = $content_image->getURL($recImage, 'thumb');
						break;

					case 'square':
						$recImage->link_src = $content_image->getURL($recImage, 'square');
						break;

					default:
						$recImage->link_src = $recImage->link_original;
						break;
				}
				$str = $imageCallback($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating);
			}
		}

		return $str;
	}



	public function encodeEmbeddables($article) {

     preg_match_all('/<span rel="([0-9]*)" class="embed([^"]*)">(.*?)\<\/span\>/is', $article, $arrMatches);

     $str = preg_replace('/<span rel="([0-9]*)" class="embed([^"]*)">(.*?)<\/span>/is', "<!--embed[$1]-->", $article);

     return $str;
 }

	public function decodeEmbeddablesForPublic($str, $moduleHandler) {
		preg_match_all('/<!--embed\[([0-9]*)\]-->/i', $str, $arrMatches);

		$content_menu = new \Gratheon\Core\Model('content_menu');

		if(count($arrMatches[1]) > 0) {
			foreach($arrMatches[1] as $menuID) {
				$recElement = $content_menu->obj($menuID);
				$html       = $moduleHandler($recElement->module, $recElement);
				$str        = str_replace('<!--embed[' . $menuID . ']-->', $html, $str);
			}
		}

		return $str;
	}


	public function decodeEmbeddableForAdmin($str, $moduleHandler) {
		preg_match_all('/<!--embed\[([0-9]*)\]-->/i', $str, $arrMatches);

		$content_menu = new \Gratheon\Core\Model('content_menu');

		if(count($arrMatches[1]) > 0) {
			foreach($arrMatches[1] as $item) {
				$recElement  = $content_menu->obj($item);
				$placeholder = $moduleHandler($recElement->module, $item, $recElement);
				$str         = str_replace('<!--embed[' . $item . ']-->', '<span rel="' . $item . '" class="embed embed_' . $recElement->module . '">' . $placeholder . '</span>', $str);
			}
		}

		return $str;
	}


	function search($q, $intGroupID) {
		$arrArticles = $this->q(
			"SELECT t2.title,t2.ID
			FROM content_article as t1
			INNER JOIN content_menu as t2 ON t2.ID=t1.parentID
			INNER JOIN content_menu_rights as t3 ON t3.pageID=t2.ID AND t3.groupID='$intGroupID' AND rightID=2
			WHERE t1.content_index LIKE '%$q%' OR t2.title LIKE '%$q%'", 'array'
		);

		$this->lastListCount = $this->count();

		$menu = new \Gratheon\CMS\Menu();
		if($arrArticles) {
			foreach($arrArticles as &$item) {
				$item->link_view = $menu->getPageURL($item->ID) . '/';
			}
		}
		return $arrArticles;
	}
}

class content_article_record extends \Gratheon\Core\Record {

}
/*
function php_highlight($source, $classes = true) {
    $r1 = $r2 = '##';

    // adds required PHP tags (at least with vers. 5.0.5 this is required)
    if (strpos($source, ' ?>') === false) // xml is not THAT important ;-)
    {
        $source = "<?php " . $source . " ?>";
        $r1 = '#&lt;\?.*?(php)?.*?&nbsp;#s';
        $r2 = '#\?&gt;#s';
    }
    elseif (strpos($source, '<? ') !== false)
    {
        $r1 = '--';
        $source = str_replace('<? ', '<?php ', $source);
    }

    $source = highlight_string($source, true);

    if ($r1 == '--') $source = preg_replace('#(&lt;\?.*?)(php)?(.*?&nbsp;)#s', '\\1\\3', $source);

    $source = preg_replace (array ( '/.*<code>\s*<span style="color: #000000">/',    //
                                    '#</span>\s*</code>#',                          //  <code><span black>
                                    $r1, $r2,                 // php tags
                                    '/<span[^>]*><\/span>/'   // empty spans
                                  ),'',$source);

    if ($classes) $source = str_replace(
        array('style="color: #0000BB"', 'style="color: #007700"',
            'style="color: #DD0000"', 'style="color: #FF8000"'),

        array('class="default"', 'class="keyword"',
            'class="string"', 'class="comment"',), $source);
    return $source;
}
*/