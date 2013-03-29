<?php
/**
 * Article page type
 * @version 1.1.2
 */

namespace Gratheon\CMS\Module;
use Gratheon\CMS;
use Gratheon\Core;

class Article
	extends \Gratheon\CMS\ContentModule
	implements \Gratheon\CMS\Module\Behaviour\Searchable,
	\Gratheon\CMS\Module\Behaviour\VisibleOnDashboard
{

	public $name = 'article';

	public $models = array(
		'content_article', 'content_image', 'content_menu',
		'content_article_autodraft', 'content_external_video',
		'sys_tags', 'content_tags', 'content_comment', 'sys_banned',
		'content_menu_rights', 'sys_user', 'tpl_links_page'
	);

	public $public_methods = array('front_view', 'add_article');

	public $static_methods = array('add_comment', 'addTranslatedArticle');


	// Content module

	public function edit($recMenu = null) {

		/** @var \Gratheon\CMS\Model\Article $content_article */
		$content_article           = $this->model('Article');
		$content_article_autodraft = $this->model('ArticleAutodraft');
		$content_article->imagemodel = $this->model('Image');
		$this->setAmazon($content_article->imagemodel);

		//$content_article->imagemodel

		$parentID = $recMenu->ID;

		if($parentID) {
			$recElement = $content_article->obj("parentID='$parentID'");

			//Inline images search and replace for edit mode
			$module              = $this;
			$recElement->content = $content_article->decodeImages($recElement->content, false, false,
				function($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating) use ($module) {
				return $module->decodeInlineImage($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating);
			});


			$controller = $this->controller;

			$recElement->content = $content_article->decodeEmbeddableForAdmin(
				$recElement->content,
				function($module, $ID, $recEmbeddedElement) use ($controller) {
					/** @var \Gratheon\Core\Controller $controller */
					return $controller->loadModule($module, function($objModule) use ($ID, $recEmbeddedElement) {
						/** @var \Gratheon\CMS\Module\Behaviour\Embeddable $objModule */
						return $objModule->getPlaceholder($recEmbeddedElement);
					});
				}
			);

			//Draft editing
			if($_GET['draftID']) {
				$objDraft = $content_article_autodraft->obj((int)$_GET['draftID']);
			}
			else {
				$objDraft = $content_article_autodraft->obj("nodeID='$parentID' ORDER BY date_added DESC", "ID,DATE_FORMAT(date_added, '%d.%m.%Y %H:%i') date_added_formatted");

				if($objDraft->ID) {
					$objDraft->link_load = sys_url . 'content/content/edit/?ID=' . $parentID . '&draftID=' . $objDraft->ID;


					$this->assign('info', array(
						$this->translate('Last changes were not saved, do you want to') . ' <a href="' . $objDraft->link_load . '" class="ajax" title="' . $objDraft->date_added_formatted . '">' .
								$this->translate('open last draft') . '</a>?'
					));
					$this->assign('objDraft', $objDraft);
				}
			}

			//Connections tab

			$recElement->content = str_replace(array('&lt;', '&gt;'), array('&amp;lt;', '&amp;gt;'), $recElement->content);

			$this->assign('recElement', $recElement);
		}

		$this->assign('arrExtraTabs', array(
			'semantic'    => array(
				'title'    => 'Semantics',
				'template' => 'ModuleBackend/article/' . __FUNCTION__ . '.tab_semantic.tpl'
			),

			'integration' => array(
				'title'    => 'Integration',
				'template' => 'ModuleBackend/article/' . __FUNCTION__ . '.tab_integration.tpl'
			),
		));
		$this->assign('show_URL', true);
		$this->assign('show_LJ', $this->config('lj_login') ? 1 : 0);
	}


	public function update($parentID) {

		/** @var \Gratheon\CMS\Model\Article $content_article */
		$content_article           = $this->model('Article');
		$content_article_autodraft = $this->model('ArticleAutodraft');
		$content_menu              = $this->model('Menu');

		$content_article->imagemodel = $this->model('Image');
		$this->setAmazon($content_article->imagemodel);


		$menu = new \Gratheon\CMS\Menu();

		$oFilter = new \Gratheon\Core\TextFilter();


		$recElement = $content_article->obj('parentID=' . $parentID);
		$recMenu    = $content_menu->obj($parentID);

		if($this->controller->in->post['method'] && $this->controller->in->post['method'] != 'front_view') {
			return false;
		}

		if(strlen($this->controller->in->post['article_content']) > 0 && $recElement) {
			//delete all drafts
			$content_article_autodraft->delete("nodeID=" . $parentID);

			$recElement->content       = stripslashes($this->controller->in->post['article_content']);
			$recElement->content_index = $oFilter->convert_html_to_text($recElement->content);
			$recElement->content       = $content_article->encodeImages($recElement->content, $parentID);
			$recElement->content       = $content_article->encodeEmbeddables($recElement->content, $parentID);


			$recElement->date_changed = 'NOW()';
			$content_article->update($recElement, "ID='{$recElement->ID}'");


			$this->ping_frontpage_updates($this->controller->in->post['ping']);

			if($this->controller->in->post['crosspost']['livejournal']) {
				$strContent = $content_article->decodeImages($recElement->content, $parentID);

				$recArticle = $this->get_article($parentID);

				if($recArticle->images) {
					foreach($recArticle->images as $image) {
						$strContent .= "<a title='" . $image->title . "' href='" . $image->link_original . "'><img src='" . $image->link_square . "' /></a>";
					}

				}
				$strContent = str_replace('<hr />', '<lj-cut text="more..">', $strContent);
				$strContent = $strContent . "</lj-cut><br /><a href='" . $menu->getPageURL($parentID) . "'>original..</a><br />";

				$sys_sync_account = $this->model('sys_sync_account');


				$content_news = new Gratheon\CMS\Module\News();

				/** @var $objExportService LivejournalService */
				$objExportService     = $content_news->getServiceObject('livejournal');
				$arrExportSyncAccount = (array)$sys_sync_account->obj("service='livejournal'", "*, DECODE(`password`,'".\SiteConfig::db_encrypt_salt."') decrypted_password");
				$objExportService->postMessage($strContent, $arrExportSyncAccount, $recMenu->title);
			}

			if($this->controller->in->post['download_format'] == 'docx') {
				require_once 'external_libraries/docxgen/phpDocx.php';
				$phpdocx         = new phpdocx(sys_root . "cms/external_libraries/docxgen/template.docx");
				$phpdocx->tmpDir = sys_root . 'app/content/cache/docx/word/';


				//$phpdocx->addImage("dog1","./example_dog.jpg");

				$wordContent = str_replace('<br />', "\n", $oFilter->convert_html_to_text($recElement->content));

				$phpdocx->assign("#TITLE#", $this->controller->in->post['title']);
				$phpdocx->assign('INFO', $wordContent);

				$file = $parentID . '_' . $this->controller->in->post['title'] . ".docx";
				$phpdocx->save(sys_root . 'app/content/cache/docx/word/' . $file);

				return array(
					'class' => 'done',
					'msg'   => $this->translate('File created..') .
							' <a href="' . sys_url . 'app/content/cache/docx/word/' . $file . '">' . $file . '</a>');
			}

		}
		else {
			return array(
				'class' => 'error',
				'msg'   => $this->translate('Empty article content or nothing to update'
				));
		}
	}


	public function insert($parentID) {
		/** @var \Gratheon\CMS\Model\Article $content_article*/
		$content_article           = $this->model('Article');
		$content_article_autodraft = $this->model('ArticleAutodraft');
		$content_menu              = $this->model('Menu');

		$content_article->imagemodel = $this->model('Image');
		$this->setAmazon($content_article->imagemodel);

		$oFilter                   = new \Gratheon\Core\TextFilter();

		if($this->controller->in->post['method'] && $this->controller->in->post['method'] != 'front_view') {
			return false;
		}

		$content_article_autodraft->delete("nodeID IS NULL");

		$recElement                = new \Gratheon\Core\Record();
		$recElement->parentID      = $parentID;
		$recElement->content       = stripslashes($this->controller->in->post['article_content']);
		$recElement->date_added    = 'NOW()';
		$recElement->content_index = $oFilter->convert_html_to_text($recElement->content);
		$recElement->content       = $content_article->encodeImages($recElement->content, $parentID);
		$content_article->insert($recElement);

		$recMenu = $content_menu->obj($parentID);

		/** @var $objExportService LivejournalService */

		/*
		if($this->controller->in->post['post_lj']) {
			global $menu;

			$module     = $this;
			$strContent = $content_article->decodeImages($recElement->content, $parentID, false,
				function($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating) use ($module) {
					return $module->decodeInlineImage($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating);
				});


			$recArticle = $this->get_article($parentID);

			if($recArticle->images) {
				foreach($recArticle->images as $image) {
					$strContent .= "<a title='" . $image->title . "' href='" . $image->link_original . "'><img src='" . $image->link_square . "' /></a>";
				}

			}
			$strContent = str_replace('<hr />', '<lj-cut text="..">', $strContent);
			$strContent = $strContent . "</lj-cut><br /><a href='" . $menu->getPageURL($parentID) . "'>..</a><br />";

			global $config;
			$sys_sync_account = $this->model('sys_sync_account');


			$content_news = new \Gratheon\CMS\Model\News();


			$objExportService     = $content_news->getServiceObject('livejournal');
			$arrExportSyncAccount = (array)$sys_sync_account->obj("service='livejournal'", "*, DECODE(`password`,'".\SiteConfig::db_encrypt_salt."') decrypted_password");
			$objExportService->postMessage($strContent, $arrExportSyncAccount, $recMenu->title);
		}


		$oRemoteService = new \Gratheon\CMS\Service\DefaultService();

		if($_POST['ping_technorati']) {
			$oRemoteService->ping('rpc.technorati.com', '/rpc/ping');
		}
		if($_POST['ping_yandex']) {
			$oRemoteService->ping('ping.blogs.yandex.ru', '/RPC2');
		}
		if($_POST['ping_google']) {
			$oRemoteService->ping('blogsearch.google.com', '/ping/RPC2');
		}
		if($_POST['ping_feedburner']) {
			$oRemoteService->ping('ping.feedburner.com');
		}
*/
	}


	public function decodeInlineImage($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating) {

		if($links) {
			if($recImage->thumbnail_type == 'original') {
				$str = str_replace("<!--image[$imageReplacementMatch]-->", "<img style=\"$strStyle\" rel=\"" . $recImage->parentID . "\" src='" . $recImage->link_original . "' alt='" . $recImage->title . "' " . $strRating . "/>", $str);
				$str = str_replace("<!--image[$imageReplacementMatch]--$strStyle-->", "<img style=\"$strStyle\" rel=\"" . $recImage->parentID . "\" src='" . $recImage->link_original . "' alt='" . $recImage->title . "' " . $strRating . "/>", $str);
				return $str;
			}
			else {
				//sys_url . "res/image/original/" . $recImage->ID . "." . $recImage->image_format;

				if(file_exists(sys_root . '/res/image/inline/' . $recImage->ID . "." . $recImage->image_format)) {
					$recImage->link_src = sys_url . "res/image/inline/" . $recImage->ID . "." . $recImage->image_format;
				}

				$recImage->html_style = $strStyle;

				$objView = new \Gratheon\Core\View(new \Gratheon\Core\View\SmartyProxy($this->controller));
				$objView->assign('recImage', $recImage);
				$objView->assign('strRating', $strRating);

				$str = str_replace("<!--image[$imageReplacementMatch]-->", $objView->view('ModuleFrontend/image/inline.tpl'), $str);
				$str = str_replace("<!--image[$imageReplacementMatch]--$strStyle-->", $objView->view('ModuleFrontend/image/inline.tpl'), $str);
				return $str;
			}
		}
		else {
			$str = str_replace("<!--image[$imageReplacementMatch]-->",
					"<img style=\"$strStyle\" rel=\"" . $recImage->parentID . "\" src='" . $recImage->link_src . "' alt='" . $recImage->title . "' title='" . $recImage->title . "'/>", $str);

			$str = str_replace("<!--image[$imageReplacementMatch]--$strStyle-->",
					"<img style=\"$strStyle\" rel=\"" . $recImage->parentID . "\" src='" . $recImage->link_src . "' alt='" . $recImage->title . "' title='" . $recImage->title . "'/>", $str);
			return $str;
		}
	}


	public function delete($parentID) {
		$content_article_autodraft = $this->model('ArticleAutodraft');
		$content_article           = $this->model('Article');

		$content_article_autodraft->delete("nodeID=" . $parentID);
		$content_article->delete("parentID=" . $parentID);
	}


	// Other admin methods
//	public function save_draft() {
//		$content_article_autodraft = new content_article_autodraft();
//		$content_article_autodraft->add_draft((int)$_GET['id'], $_POST['title'], $_POST['article_content']);
//		die(1);
//	}


	private function ping_frontpage_updates($aServicesToNotify) {
		$oRemoteService = new \Gratheon\CMS\Service\DefaultService();
		if($aServicesToNotify['technorati']) {
			$oRemoteService->ping('rpc.technorati.com', '/rpc/ping');
		}
		if($aServicesToNotify['yandex']) {
			$oRemoteService->ping('ping.blogs.yandex.ru', '/RPC2');
		}
		if($aServicesToNotify['google']) {
			$oRemoteService->ping('blogsearch.google.com', '/ping/RPC2');
		}
		if($aServicesToNotify['feedburner']) {
			$oRemoteService->ping('ping.feedburner.com');
		}
	}


	public function list_articles() {
		$this->use_gz = false;

		$strFunction = strtolower(__FUNCTION__);
		$offset      = $_GET['page'] > 0 ? $this->per_page * ((int)$_GET['page'] - 1) : 0;

		//Filter on top
		$strFilter = '1=1';
		if($this->controller->in->request['date_from']) {
			$_SESSION[$this->name][$strFunction]['date_from'] = $this->controller->in->request['date_from'];
			$arrDate                                          = explode('.', $this->controller->in->request['date_from']);
			$strFromDate                                      = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
		}
		elseif($_SESSION[$this->name][$strFunction]['date_from']) {
			$arrDate     = explode('.', $_SESSION[$this->name][$strFunction]['date_from']);
			$strFromDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
		}

		if($strFromDate) {
			$strFilter .= " AND t1.date_added>='$strFromDate 00:00:00'";
		}

		if($this->controller->in->request['date_to']) {
			$_SESSION[$this->name][$strFunction]['date_to'] = $this->controller->in->request['date_to'];
			$arrDate                                        = explode('.', $this->controller->in->request['date_to']);
			$strToDate                                      = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
		}
		elseif($_SESSION[$this->name][$strFunction]['date_to']) {
			$arrDate   = explode('.', $_SESSION[$this->name][$strFunction]['date_to']);
			$strToDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
		}

		if($strToDate) {
			$strFilter .= " AND t1.date_added<='$strToDate 23:59:59'";
		}

		$content_article = $this->model('Article');

		$intPerPage = $this->per_page;

		//query block
		$arrList = $content_article->q(
			"SELECT SQL_CALC_FOUND_ROWS DATE_FORMAT(t1.date_added,'%d.%m.%Y %H:%i') added_time_formatted, t2.title, t1.ID
			FROM content_article t1
			LEFT JOIN content_menu t2 ON t2.id=t1.parentID

			WHERE $strFilter
			ORDER BY t1.date_added DESC
			LIMIT {$offset},{$intPerPage}"
		);

		$total_count = $content_article->count();

		$intPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;

		foreach($arrList as &$objItem) {
			$objItem->link_edit   = sys_url . 'content/call/' . $this->name . '/edit_article/?id=' . $objItem->ID;
			$objItem->link_delete = sys_url . 'content/call/' . $this->name . '/delete_article/?id=' . $objItem->ID . "&page=" . $intPage;
		}

		#Create page navigation for first page
		//$intPage=isset($_GET['page'])? (int)$_GET['page']:0;

		$this->assign('title', $this->translate('Articles') . ' (' . $total_count . ')');
		$objPaginator = new CMS\Paginator($this->controller->in, $total_count, $intPage, $this->per_page);

		$this->assign('objPaginator', $objPaginator);
		$this->assign('title', $this->translate('Articles'));
		$this->assign('title_badge', $total_count);
		$this->assign('arrList', $arrList);
		$this->assign('link_filter', sys_url . '/content/call/' . $this->name . '/' . $strFunction . '/');
		$this->assign('link_add', sys_url . '/content/call/' . $this->name . '/edit_article/');

		$this->assign('form_filter', $_SESSION[$this->name][$strFunction]);

		//$this->assign('show_twitter',$this->config('twitter_login'));

		return $this->controller->view('ModuleBackend/' . $this->name . '/' . __FUNCTION__ . '.tpl');
	}

	public function setAmazon(&$content_image){
		if($this->config('amazon_key', 'Image')) {
			$content_image->setAmazonData(
				$this->config('amazon_bucket', 'Image'),
				$this->config('amazon_host', 'Image'),
				$this->config('amazon_key', 'Image'),
				$this->config('amazon_secret', 'Image')
			);
		}
	}

	//Private methods
	public function get_article($parentID) {
		$menu = new \Gratheon\CMS\Menu();
		$user = $this->controller->user;

		if(!$parentID) {
			return false;
		}

		/** @var \Gratheon\CMS\Model\Article $content_article */
		/** @var \Gratheon\CMS\Model\Image $content_image */

		$content_article = $this->model('Article');
		$content_menu    = $this->model('Menu');
		$content_image   = $this->model('Image');


		$this->setAmazon($content_image);

		$content_article->imagemodel = $content_image;



		$content_menu_rights = $this->model('content_menu_rights');
		$tpl_links_page      = $this->model('tpl_links_page');
		$sys_user            = $this->model('sys_user');
		$sys_tags            = $this->model('sys_tags');
		$content_tags        = $this->model('content_tags');

		$recEntry = $content_menu->getMenuRecord($parentID, $this->name);

		if(!$recEntry) {
			return false;
		}
		$recEntry->utime              = gmdate('D, d M Y H:i:s T', $recEntry->date_added_unix);
		$recEntry->time_added_iso8601 = date("c", $recEntry->date_added_unix);
		$recEntry->url                = $menu->getPageURL($recEntry->ID);
		$recEntry->rights             = $content_menu_rights->arrint("pageID='$parentID' AND groupID='" . $user->data['groupID'] . "'", 'rightID');
		$recEntry->element            = $content_article->obj('parentID=' . $recEntry->ID);
		$recEntry->elementTypes       = $content_menu->arrint('parentID=' . $parentID, "DISTINCT(module)");
		$recEntry->pageConnectionObjs = $tpl_links_page->arr(
			"pageID<>'$parentID' AND connectionID=(SELECT connectionID FROM {$tpl_links_page->table} WHERE pageID='$parentID')",
			"langID,pageID",
				$tpl_links_page->table . " t1 INNER JOIN " .
						$content_menu->table . " t2 ON t2.ID=t1.pageID"
		);

		if($recEntry->pageConnectionObjs) {
			foreach($recEntry->pageConnectionObjs as $arrConnection) {
				$recEntry->pageConnections[$arrConnection->langID] = $menu->getPageURL($arrConnection->pageID);
				$recEntry->pageConnectionIDs[]                     = $arrConnection->pageID;
			}
		}

		$content_menu_rating = $this->model('content_menu_rating');

		//Lazy load child elements
		if($recEntry->elementTypes) {
			$arrFoundEmbeddedIDs = array();


			$controller                 = $this->controller;
			$recEntry->element->content = $content_article->decodeEmbeddablesForPublic(
				$recEntry->element->content,

				function($module, $recElement) use ($controller, &$arrFoundEmbeddedIDs) {
					/** @var \Gratheon\Core\Controller $controller */
					return $controller->loadModule($module, function($objModule) use ($recElement, &$arrFoundEmbeddedIDs) {
						$arrFoundEmbeddedIDs[] = $recElement->ID;

						/**@var \Gratheon\CMS\Module\Behaviour\Embeddable $objModule*/
						return $objModule->decodeEmbeddable($recElement);
					});
				}
			);

			$arrFoundEmbeddedIDs = array_unique($arrFoundEmbeddedIDs);

			if($arrFoundEmbeddedIDs) {
				$strWhere = " AND ID NOT IN (" . implode(',', $arrFoundEmbeddedIDs) . ")";
			}

			$recEntry->elementTypes = $content_menu->arrint("parentID='$parentID'" . $strWhere, "DISTINCT(module)");


			$self = $this;
			foreach($recEntry->elementTypes as $strType) {
				$this->controller->loadModule($strType, function($module) use($recEntry, $strType, $content_article, $self, $arrFoundEmbeddedIDs) {
					if(method_exists($module, 'getArticleData')) {
						$recEntry->subNode[$strType] = $module->getArticleData($recEntry->ID, $arrFoundEmbeddedIDs);

						if($strType == 'image') {
							$recEntry->element->content = $content_article->decodeImages(
								$recEntry->element->content, true, true,
								function($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating) use ($self) {
									return $self->decodeInlineImage($str, $recImage, $links, $imageReplacementMatch, $strStyle, $strRating);
								});
						}
					}
				});

				switch($strType) {
					case 'category':
						$recEntry->galleries = $content_menu->q(
							"SELECT t1.*
                            FROM content_menu as t1
                            LEFT JOIN content_image as t2 ON t1.ID=t2.parentID
                            WHERE t1.parentID='{$recEntry->ID}' AND t1.module='category' and t1.method='image_list'
                            ORDER BY t1.position"
						);

						foreach($recEntry->galleries as &$gallery) {

							$gallery->images = $content_menu->q(
								"SELECT t1.title,t1.parentID,t2.ID,t2.float_position,t2.image_format,t2.thumbnail_type, t2.cloud_storage
                                FROM content_menu as t1
                                LEFT JOIN content_image as t2 ON t1.ID=t2.parentID
                                WHERE t1.parentID='{$gallery->ID}' AND t1.module='image'
                                ORDER BY t1.position"
							);

							if($gallery->images) {
								foreach($gallery->images as &$image) {
									$image->link_original = $content_image->getOriginalURL($image);
									switch($image->thumbnail_type) {
										case 'square':
											$image->link_icon = $content_image->getSquareURL($image);
											break;

										case 'thumb':
											$image->link_icon = $content_image->getRectangleURL($image);
											break;
									}
								}
							}
						}
						break;
				}

			}
		}



		if($recEntry->userID) {
			$recEntry->author = $sys_user->obj($recEntry->userID, "ID, login,firstname,lastname");
		}

		//Tags
		$recEntry->arrTags = $sys_tags->arr(
			't1.ID=t2.tagID AND t2.contentID=' . $parentID,
			't1.ID, t1.pop, t1.title',
				$sys_tags->table . ' t1 LEFT JOIN ' . $content_tags->table . ' t2 ON t1.ID=t2.tagID'
		);

		return $recEntry;
	}


	public function get_adminpanel_box_list() {
		$content_article = $this->model('Article');
		$content_menu    = $this->model('Menu');

		$objLastData['data'] = $content_article->arr(
			"t1.module='" . $this->name . "' ORDER BY date_changed DESC LIMIT 20", "t1.*",
				$content_menu->table . ' t1 LEFT JOIN ' .
						$content_article->table . ' t2 ON t1.ID=t2.parentID');

		$objLastData['title'] = $this->translate('Articles');
		$objLastData['count'] = $content_article->int("1=1", "COUNT(*)");

		return $objLastData;
	}


	public function get_article_tags($parentID, $bMetaTags = false) {
		$sys_tags = $this->model('sys_tags');

		return $sys_tags->q("SELECT t1.ID, t1.pop, t1.title
                                FROM sys_tags t1 LEFT JOIN content_tags t2 ON t1.ID=t2.tagID
                                WHERE t1.ID=t2.tagID AND t2.contentID='$parentID'" . ($bMetaTags
				? " AND t1.title LIKE '%:%'" : " AND t1.title NOT LIKE '%:%'"));
	}


	public function get_article_series($arrTags, $intLanguage) {
		$menu = new \Gratheon\CMS\Menu();

		$sys_tags  = $this->model('sys_tags');
		$arrSeries = array();

		if($arrTags) {
			foreach($arrTags as $objTag) {
				$strTag = $objTag->title;
				if(strpos($strTag, 'series:') !== false) {
					$strSeriesTag             = str_replace('series:', '', $objTag->title);
					$arrSeries[$strSeriesTag] = $sys_tags->q(
						"SELECT t3.title, t3.ID
                        FROM sys_tags t1
                        INNER JOIN content_tags t2 ON t1.ID=t2.tagID
                        INNER JOIN content_menu t3 ON t3.ID=t2.contentID
                        WHERE t1.ID='{$objTag->ID}' AND t3.langID='{$intLanguage}' AND t3.module='article'
                        ORDER BY t3.date_added DESC", 'ray');

					if(count($arrSeries[$strSeriesTag]) < 2) {
						unset($arrSeries[$strSeriesTag]);
					}
					else {
						if($arrSeries[$strSeriesTag]) {
							foreach($arrSeries[$strSeriesTag] as &$arrPage) {
								$arrPage['link_view'] = $menu->getPageURL($arrPage['ID']);
							}
						}
					}
				}
			}
		}

		return $arrSeries;
	}


	//Searchable
	public function search_from_public($q) {
		$content_article = $this->model('Article');
		$arrArticles     = $content_article->search($q, $this->controller->user->data['groupID']);

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_article->lastListCount;
		$arrEnvelope->title = $this->controller->translate('Articles');
		$arrEnvelope->list  = $arrArticles;

		return $arrEnvelope;
	}


	public function searchByTag($tagID) {
		$content_article = $this->model('Article');
		$menu            = new \Gratheon\CMS\Menu();

		$arrArticles = $content_article->q(
			"SELECT t2.title,t2.ID
			FROM content_tags as t4
			INNER JOIN content_menu as t2 ON t4.contentID=t2.ID
			INNER JOIN content_article as t1 ON t2.ID=t1.parentID
			INNER JOIN content_menu_rights as t3 ON t3.pageID=t2.ID AND t3.groupID='" . $this->controller->user->data['groupID'] . "' AND rightID=2
			WHERE t4.tagID = '$tagID'", 'array'
		);

		if($arrArticles) {
			foreach($arrArticles as &$item) {
				$item->link_view = $menu->getPageURL($item->ID) . '/';
			}
		}

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_article->count();
		$arrEnvelope->title = $this->translate('Articles');
		$arrEnvelope->list  = $arrArticles;

		return $arrEnvelope;
	}


	public function search_from_admin($q) {
		$menu = new \Gratheon\CMS\Menu();

		$content_article = $this->model('Article');

		$arrArticles = $content_article->q(
			"SELECT t2.title,t2.ID
			FROM content_article as t1
			INNER JOIN content_menu as t2 ON t2.ID=t1.parentID
			WHERE t1.content_index LIKE '%$q%' OR t2.title LIKE '%$q%'"
		);

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_article->count();
		$arrEnvelope->title = $this->translate('Articles');

		foreach($arrArticles as &$item) {
			$item->link_view = $menu->getPageURL($item->ID) . '/';
			$item->link_edit = 'content/#' . $item->ID;
		}

		$arrEnvelope->list = $arrArticles;

		return $arrEnvelope;
	}


	//public methods
	public function front_view($parentID) {
		global $menu;
		//$recElement=$content_article->obj('parentID='.$parentID);

		$tree = new \Gratheon\CMS\Tree;

		$content_menu = $this->model('content_menu');
		$objContent   = $this->get_article($parentID);

		if(in_array(4, $objContent->rights)) {
			$this->controller->loadModule('comment', function($objComment) use($objContent) {
				/** @var \Gratheon\CMS\Module\Comment $objComment */
				$objContent->arrComments = $objComment->getNodeComments($objContent->ID);
			});
			$this->add_js('comment/article_view.js');
			$this->assign('link_comments_rss', sys_url . 'front/call/comment/front_rss/?nodeID=' . $parentID);
		}

		if(in_array(3, $objContent->rights)) {
			$this->assign('link_comment', sys_url . 'front/call/comment/front_add/?nodeID=' . $parentID);
		}
		//add navigation
		$arrSelected            = $tree->buildSelected($parentID);
		$objContent->navigation = $tree->buildLevels($arrSelected);


		if($this->config('public_editing_enabled')) {
			$this->assign('link_edit', $menu->getTplPage('article_adding') . '?ID=' . $parentID);
		}

		$objContent->arrTags   = $this->get_article_tags($parentID);
		$objContent->arrSeries = $this->get_article_series($this->get_article_tags($parentID, true), $objContent->langID);

		$this->assign('title', $this->controller->config('title_article') . $content_menu->int($parentID, 'title'));
		$this->assign('article', $objContent);
		return $objContent;
		//$this->assign('content_template','module.article.view.tpl');
	}


	public function category_view(&$recEntry) {
		$tree = new \Gratheon\CMS\Tree;

		$this->add_js('image/image.js');

		$recEntry = $this->get_article($recEntry->ID);
		//$recEntry->element=$content_article->obj('parentID='.$recEntry->ID);

		if($recEntry->element->content) {
			$arrParagraphs = explode('<hr />', $recEntry->element->content);
			if(count($arrParagraphs) > 1) {
				$recEntry->element->content = $arrParagraphs[0];
			}
			else {
				$arrParagraphs              = explode('<hr>', $recEntry->element->content);
				$recEntry->element->content = $arrParagraphs[0];
			}
		}

		$menu = new \Gratheon\CMS\Menu();

		$arrSelected          = $tree->buildSelected($recEntry->ID);
		$recEntry->navigation = $tree->buildLevels($arrSelected);

		if($this->config('public_editing_enabled') && $recEntry->userID == $this->user->data['ID']) {
			$recEntry->link_edit = $menu->getTplPage('article_adding') . '?ID=' . $recEntry->ID;
		}
	}


	public function category_tile(&$recEntry) {
		$menu = new \Gratheon\CMS\Menu();
		$tree = new \Gratheon\CMS\Tree;

		$this->add_js('image/image.js');

		$recEntry = $this->get_article($recEntry->ID);

		$content_menu  = $this->model('Menu');
		$content_image = $this->model('Image');

		$recEntry      = $content_menu->getMenuRecord($recEntry->ID, $this->name);
		$recEntry->url = $menu->getPageURL($recEntry->ID);

		//$recEntry->element = $content_article->obj('parentID=' . $recEntry->ID);
		$image = $content_menu->q("SELECT t1.title,t1.parentID,t2.ID,t2.float_position,t2.image_format,t2.thumbnail_type, t2.cloud_storage
                                    FROM content_menu as t1
                                    LEFT JOIN content_image as t2 ON t1.ID=t2.parentID
                                    WHERE t1.parentID='{$recEntry->ID}' AND t1.module='image'
                                    ORDER BY t1.position
                                    LIMIT 1", 'object'
		);

		$image->link_original  = $content_image->getOriginalURL($image);
		$image->link_square    = $content_image->getSquareURL($image);
		$image->link_rectangle = $content_image->getSquareURL($image);

		$recEntry->image = $image;

		if($recEntry->element->content) {
			$arrParagraphs = explode('<hr />', $recEntry->element->content);
			if(count($arrParagraphs) > 1) {
				$recEntry->element->content = $arrParagraphs[0];
			}
			else {
				$arrParagraphs              = explode('<hr>', $recEntry->element->content);
				$recEntry->element->content = $arrParagraphs[0];
			}
		}


		$arrSelected          = $tree->buildSelected($recEntry->ID);
		$recEntry->navigation = $tree->buildLevels($arrSelected);

		if($this->config('public_editing_enabled') && $recEntry->userID == $this->user->data['ID']) {
			$recEntry->link_edit = $menu->getTplPage('article_adding') . '?ID=' . $recEntry->ID;
		}
	}


	public function category_rss(&$item) {
		$tempData           = $this->category_view($item);
		$item->content      = $item->element->content;
		$item->flash_videos = $item->element->flash_videos;
	}
}