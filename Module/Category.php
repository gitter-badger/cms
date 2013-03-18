<?php
/**
 * Category module works as listing wrapper
 * It allows to list and browse different types of elements within
 *
 * @author Artjom Kurapov
 * @version 1.0.2
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Category extends \Gratheon\CMS\ContentModule {

	public $name = 'category';

	public $models = array(
		'content_article', 'content_image', 'content_menu', 'content_menu_rights',
		'content_category', 'sys_languages',
		'sys_tags', 'content_tags', 'content_comment', 'content_poll'
	);


	public $public_methods = array('pub_list', 'image_list', 'tag_map', 'pub_tiles', 'pub_plain_list');
	public $static_methods = array('front_rss');


	function edit($recMenu = null) {
		$content_category = $this->model('content_category');

		$parentID = $recMenu->ID;

		if($parentID) {
			$recElement = $content_category->obj('parentID=' . $parentID);
			$this->assign('recElement', $recElement);
		}
		$this->assign('show_URL', true);
	}


	function update($parentID) {
		$content_category = $this->model('content_category');

		$recElement = $content_category->obj('parentID=' . $parentID);

		$recElement->deepness = $_POST['deepness'];
		$recElement->orderby  = $_POST['orderby'];
		if($_POST['elements_per_page']) {
			$recElement->elements_per_page = $_POST['elements_per_page'];
		}
		$content_category->update($recElement);
	}


	function insert($parentID) {
		$content_category = $this->model('content_category');

		$recElement           = new \stdClass();
		$recElement->parentID = $parentID;
		$recElement->deepness = $_POST['deepness'];
		$recElement->orderby  = $_POST['orderby'];
		if($_POST['elements_per_page']) {
			$recElement->elements_per_page = $_POST['elements_per_page'];
		}
		$content_category->insert($recElement);
	}


	function delete($parentID) {
		$content_category = $this->model('content_category');
		$content_category->delete("parentID=" . $parentID);
	}


	/*
	 function admin_search($q) {
		 global $controller, $menu;
		 $content_menu = $this->model('content_category');

		 $arrArticles = $content_menu->arr("title LIKE '%" . $q . "%'", 'title,ID');

		 $arrEnvelope        = new SearchEnvelope();
		 $arrEnvelope->count = $content_menu->count();
		 $arrEnvelope->title = $controller->translate('Categories');

		 foreach ($arrArticles as &$item) {
			 $item->link_view = $menu->getPageURL($item->ID) . '/';
			 $item->link_edit = 'content/#' . $item->ID;
		 }

		 $arrEnvelope->list = $arrArticles;

		 return $arrEnvelope;
	 }
 */
	function pub_list($parentID, $item_view = 'category_view') {
		$content_menu        = $this->model('Menu');
		$content_category    = $this->model('content_category');
		$content_menu_rights = $this->model('content_menu_rights');
		$sys_tags            = $this->model('sys_tags');
		$content_tags        = $this->model('content_tags');

		//$this->add_js('/ext/jquery/jquery.hotkeys.js');

		$strFilter   = $strOrder = '';
		$arrCategory = $content_category->obj('parentID=' . $parentID);

		if($arrCategory->deepness == 'only_children') {
			$strFilter .= " AND t1.parentID='$parentID' AND
			(
                t1.module IN ('article','poll','mirror', 'article') AND
                t1.method='front_view' AND
                t2.groupID='" . $this->controller->user->data['groupID'] . "' AND
                t2.rightID=2 OR t1.module IN ('file','image','video','map')
			)
			";
		}
		else {
			$strFilter .= " AND
					t1.module IN ('article','poll', 'article') AND
					t1.method='front_view' AND
					t2.groupID='" . $this->controller->user->data['groupID'] . "' AND
					t1.langID='{$this->controller->langID}' AND
					t2.rightID=2
			";
		}

		if($arrCategory->orderby) {
			$strOrder .= " GROUP BY t1.id ORDER BY t1." . $arrCategory->orderby . " DESC ";
		}

		if($arrCategory->elements_per_page) {
			$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if($intPage < 1) {
				$intPage = 1;
			}
			$offset = ($intPage - 1) * $arrCategory->elements_per_page;
			$strOrder .= " LIMIT $offset, " . $arrCategory->elements_per_page . " ";
		}
		else {
			$strOrder .= " LIMIT 50";
		}


		//Query
		$strSQL = "SELECT SQL_CALC_FOUND_ROWS t1.*,DATEDIFF(NOW(),t1.date_added) as diff,DATE_FORMAT(t1.date_added,'%d.%m %H:%i') as date_added2
				FROM " . $content_menu->table . " t1
				LEFT JOIN " . $content_menu_rights->table . " t2 ON t2.pageID=t1.ID
				WHERE 1=1
					$strFilter

				$strOrder ";

		$arrList = $content_menu->q($strSQL);

		//Paginator
		if($arrCategory->elements_per_page) {
			$total_count  = $content_menu->count();
			$objPaginator = new CMS\Paginator($this->controller->input, $total_count, $intPage, $arrCategory->elements_per_page);


			$this->assign('objPaginator', $objPaginator);
		}

		//pre($arrList);

		if($arrList) {
			foreach($arrList as &$item) {
				$this->controller->loadModule($item->module, function ($objModule) use (&$item, $content_menu, $sys_tags, $content_tags, $item_view) {

					/**
					 * @var $item_view = category_tile or category_view
					 */
					$item->template = 'ModuleFrontend/' . $objModule->name . '/' . $item_view . '.tpl';


					if(method_exists($objModule, $item_view)) {
						$objModule->{$item_view}($item);
					}

					if(!$item->template) {
						$item->template = 'ModuleFrontend/' . $objModule->name . '/' . $item_view . '.tpl';
					}
					/*
										$item->comment_count = $content_menu->int('parentID=' . $item->ID . " AND module='comment'", 'COUNT(*)');
										$item->arrTags       = $sys_tags->arr('t1.ID=t2.tagID AND t2.contentID=' . $item->ID, 't1.ID, t1.pop, t1.title',
												$sys_tags->table . ' t1 LEFT JOIN ' . $content_tags->table . ' t2 ON t1.ID=t2.tagID');
										*/

				});
			}
		}

		$this->assign('arrCategoryItems', $arrList);
		//$this->assign('content_template','module.category.view.tpl');
	}


	function pub_plain_list($parentID) {

		$content_menu        = $this->model('Menu');
		$content_category    = $this->model('content_category');
		$content_menu_rights = $this->model('content_menu_rights');

		$strFilter   = $strOrder = '';
		$arrCategory = $content_category->obj('parentID=' . $parentID);

		if($arrCategory->deepness == 'only_children') {
			$strFilter .= " AND t1.parentID='$parentID'";
		}
		else {
			$strFilter .= " AND t1.langID='{$this->controller->langID}'";
		}

		if($arrCategory->orderby) {
			$strOrder .= " GROUP BY t1.id ORDER BY t1." . $arrCategory->orderby . " DESC ";
		}

		//Query
		$strSQL = "SELECT SQL_CALC_FOUND_ROWS t1.*, DATEDIFF(NOW(),t1.date_added) as diff, DATE_FORMAT(t1.date_added,'%d.%m %H:%i') as date_added2
	FROM " . $content_menu->table . " t1
	WHERE 1=1 $strFilter $strOrder";

		$arrList = $content_menu->q($strSQL);

		$menu = new \Gratheon\CMS\Menu();
		if($arrList){
			foreach($arrList as &$item){
				$item->url = $menu->getPageURL($item->ID);
			}
		}

		$this->assign('arrCategoryItems', $arrList);
	}


	function pub_tiles($parentID) {
		$this->pub_list($parentID, 'category_tile');
	}


	function image_list($parentID) {
		$content_category = $this->model('content_category');
		$content_menu     = $this->model('content_menu');

		$this->add_js('/ext/jquery/jquery.hotkeys.js');
		$this->add_js($this->name . '/' . __FUNCTION__ . '.js');
		$arrCategory = $content_category->obj('parentID=' . $parentID);

		$strFilter = '';
		$strOrder  = '';

		if($arrCategory->deepness == 'only_children') {
			$strFilter .= " AND t1.parentID='$parentID' AND t1.module IN ('image') ";
		}
		else {
			$strFilter .= " AND
					t1.module IN ('image') AND
					t1.method='front_view' AND
					t2.groupID='" . $this->controller->user->data['groupID'] . "' AND
					t2.rightID=2
			";
		}

		if($arrCategory->orderby) {
			$strOrder .= " GROUP BY t1.id ORDER BY t1." . $arrCategory->orderby . " DESC ";
		}

		if($arrCategory->elements_per_page) {
			$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if($intPage < 1) {
				$intPage = 1;
			}
			$offset = ($intPage - 1) * $arrCategory->elements_per_page;
			$strOrder .= " LIMIT $offset, " . $arrCategory->elements_per_page . " ";
		}
		else {
			$strOrder .= " LIMIT 50";
		}

		//Query
		$strSQL = "SELECT SQL_CALC_FOUND_ROWS t1.*,DATEDIFF(NOW(),t1.date_added) as diff,DATE_FORMAT(t1.date_added,'%d.%m %H:%i') as date_added2
				FROM content_menu t1
				LEFT JOIN content_menu_rights t2 ON t2.pageID=t1.ID
				WHERE 1=1 $strFilter $strOrder ";

		$arrList = $content_menu->q($strSQL);

		//Paginator
		if($arrCategory->elements_per_page) {
			$total_count  = $content_menu->count();
			$objPaginator = new CMS\Paginator($this->controller->input, $total_count, $intPage, $arrCategory->elements_per_page);

			$this->assign('objPaginator', $objPaginator);
		}


//		if($arrList) {
////			$modImage = new modImage;
////			$modImage->load_models();
////			$modImage->load_config();
////
//			foreach($arrList as &$item) {
//				$item->template = 'ModuleFrontend/' . $item->module . '/category_view.tpl';
//
//				if(method_exists($modImage, 'category_view')) {
//					$modImage->category_view($item);
//				}
//			}
//		}

		$this->assign('arrCategoryItems', $arrList);
	}


	function front_rss($parentID = 1) {
		$content_category = $this->model('content_category');
		$sys_languages    = $this->model('sys_languages');
		$content_menu     = $this->model('content_menu');


		// Load RSS module
		require_once('vendor/tot-ra/feedcreator/feedcreator.class.php');
		$rss = new \UniversalFeedCreator();
		$rss->encoding='UTF-8';

		// Find category
		$arrCategory = $content_category->obj('parentID=' . $parentID);
		$strFilter   = '';
		$strOrder    = '';

		if($arrCategory->deepness == 'only_children') {
			$strFilter .= " t1.parentID='$parentID' AND ";
		}
		if($arrCategory->orderby) {
			$strOrder .= " ORDER BY t1." . $arrCategory->orderby . " DESC ";
		}
		if($arrCategory->elements_per_page) {
			$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$offset  = ($intPage - 1) * 10;
			$strOrder .= " LIMIT $offset, 10 ";
		}

		$langID = (int)$_GET['lang'];
		if(!$langID) {
			$langID = $sys_languages->int("is_default=1", 'ID');
		}

		$rss->title                     = $content_menu->int("ID=" . $parentID, 'title');
		$rss->description               = $rss->title; //"Personal Blog";
		$rss->descriptionTruncSize      = 500;
		$rss->descriptionHtmlSyndicated = true;
		$rss->link                      = sys_url;
		$rss->image->title              = $rss->title;
		$rss->image->link               = sys_url;
		$rss->image->url                = sys_url . 'res/avatar.jpg';
		$rss->syndicationURL            = sys_url . substr($_SERVER["PHP_SELF"],1);

		//Query all images
		//That have parent article viewable
		//and that support rss
		$strSQL = "SELECT SQL_CALC_FOUND_ROWS t1.*,
					DATEDIFF(NOW(),t1.date_added) as diff,
					UNIX_TIMESTAMP(t1.date_added) as date_added_unix,
					DATE_FORMAT(t1.date_added,'%d.%m %H:%i') as date_added2
				FROM content_menu t1

				INNER JOIN content_module t3 ON t3.ID=t1.module AND t3.supports_rss=1
				LEFT JOIN content_menu_rights  t2 ON t2.pageID=t1.ID
				WHERE 
					$strFilter
					t1.langID='{$langID}' AND
					t1.method='front_view' AND
					t2.groupID='{$this->controller->user->data['groupID']}' AND
					t2.rightID=6
				$strOrder ";

		$arrList = $content_menu->q($strSQL);

		//$controller->MIME = 'application/rss+xml';
		$sys_tags      = $this->model('sys_tags');
		$content_tags  = $this->model('content_tags');
		$content_image = $this->model('Image');

		//List all elements
		foreach($arrList as &$item) {

			$this->controller->loadModule($item->module, function ($objModule)
			use (&$item, $content_menu, &$rss, $sys_tags, $content_tags, $content_image) {

				$menu = new \Gratheon\CMS\Menu();

				if(method_exists($objModule, 'category_view')) {
					$objModule->category_view($item);

					$item->comment_count = $content_menu->int('parentID=' . $item->ID . " AND module='comment'", 'COUNT(*)');
					$item->arrTags       = $sys_tags->arr('t1.ID=t2.tagID AND t2.contentID=' . $item->ID, 't1.ID, t1.pop, t1.title', $sys_tags->table . ' t1 LEFT JOIN ' . $content_tags->table . ' t2 ON t1.ID=t2.tagID');

					$recEntry               = new \FeedItem();
					$recEntry->link         = $recEntry->guid = $menu->getPageURL($item->ID); //sys_url.'article/'.$item->ID;
					$recEntry->title        = $item->title;
					$recEntry->files        = $item->arrFiles;
					$recEntry->flash_videos = $item->flash_videos;
					$recEntry->images       = $item->images;
					$recEntry->description  = $item->element->content; //$data->short;

					/** @var \Gratheon\CMS\Model\Image $content_image*/
					foreach((array)$item->images as $image) {
						$recEntry->description .= '<a href="' . $content_image->getURL($image, 'original', false) . '"><img src="' . $content_image->getURL($image, 'square', false) . '" alt="' . $image->title . '"/></a>';
					}

					$recEntry->description = str_replace("href='/", "href='" . sys_url . "/", $recEntry->description); //relative to absolute urls
					$recEntry->description = str_replace('href="/', 'href="' . sys_url . '/', $recEntry->description); //relative to absolute urls

					//optional
					$recEntry->descriptionTruncSize      = 500;
					$recEntry->descriptionHtmlSyndicated = true;
					$recEntry->date                      = date('r', $item->date_added_unix);

					$rss->addItem($recEntry);
				}
			});
		}

		return $rss->createFeed("RSS2.0");
		//		return $rss->saveFeed("RSS2.0", sys_root."app/front/view/bin/category_{$arrCategory->ID}.rss");
	}
}