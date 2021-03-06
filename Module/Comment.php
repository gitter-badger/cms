<?php
/**
 * Comment page element
 *
 * @version 1.1.0
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Comment
	extends \Gratheon\CMS\ContentModule
	implements \Gratheon\CMS\Module\Behaviour\Searchable, \Gratheon\CMS\Module\Behaviour\VisibleOnDashboard {

	var $name = 'comment';
	var $models = array('content_comment', 'content_menu', 'sys_banned', 'sys_languages', 'content_menu_rights');
	public $static_methods = array('front_delete', 'front_add', 'front_rss');


	public function edit($recMenu = null) {
		$content_comment = $this->model('Comment');
		$this->assign('bHideContainer', true);

		$parentID = $recMenu->ID;
		if($recMenu->elementID) {
			$recElement = $content_comment->obj('ID=' . $recMenu->elementID);
			$this->assign('recElement', $recElement);
		}
		elseif($parentID) {
			$recElement = $content_comment->obj('parentID=' . $parentID);
			$this->assign('recElement', $recElement);
		}
	}


	public function update($parentID) {
		$content_comment = $this->model('Comment');
		$recElement      = $content_comment->obj('parentID=' . $parentID);

		/** @var $recElement content_comment_record */
		$recElement->content  = stripslashes($this->controller->in->post['mceEditor']);
		$recElement->author   = $this->controller->in->post['title'];
		$recElement->url      = $this->controller->in->post['url'];
		$recElement->show_url = (int)$this->controller->in->post['show_url'];

		$content_comment->update($recElement);
	}


	public function insert($parentID) {
		$content_comment = $this->model('Comment');

		$recElement             = new content_comment_record();
		$recElement->parentID   = $parentID;
		$recElement->content    = stripslashes($this->controller->in->post['mceEditor']);
		$recElement->date_added = 'NOW()';
		$recElement->author     = $this->controller->in->post['title'];
		$recElement->url        = $this->controller->in->post['url'];
		$recElement->show_url   = (int)$this->controller->in->post['show_url'];
		$content_comment->insert($recElement);
	}


	public function delete($parentID) {
		$content_comment = $this->model('Comment');
		$content_comment->delete("parentID=" . $parentID);
	}


	public function get_adminpanel_box_list() {
		$content_comment = $this->model('Comment');
		$content_menu    = $this->model('Menu');

		$objLastData['data'] = $content_comment->arr(
			'1=1 ORDER BY m.date_added DESC LIMIT 13',
			'*,m.parentID as articleID',
				'content_comment as c LEFT JOIN ' .
						'content_menu as m ON c.parentID=m.ID'
		);

		$objLastData['title'] = $this->translate('Comments');
		$objLastData['count'] = $content_comment->int("1=1", "COUNT(*)");

		return $objLastData;
	}


	//
	// Static methods
	//
	public function dashboard_delete() {
		global $user;
		$content_menu = $this->model('Menu');

		$ID = (int)$this->controller->in->get('ID');
		if($user->data['groupID'] == 2 && $ID) {
			$content_menu->delete($ID);
			$this->delete($ID);
		}
		die();
	}


	//adds comment to defined node (article or poll)
	public function front_add() {
		global $user, $menu;

		setcookie('name', $this->controller->in->post['comment_name'], strtotime("+1 week"), '/');
		setcookie('url', $this->controller->in->post['comment_url'], strtotime("+1 week"), '/');
		setcookie('email', $this->controller->in->post['comment_email'], strtotime("+1 week"), '/');


		$intNodeID                                  = (int)$this->controller->in->get('nodeID');
		$intParentID                                = $this->controller->in->get('parentID') ? (int)$this->controller->in->get('parentID') : $intNodeID;
		$this->controller->in->post['comment_body'] = stripslashes($this->controller->in->post['reply']); // $_SESSION['front']['comment_field']


		$this->checkForDisabledJS($intNodeID, $this->controller->in->post['scripter']);
		$this->checkForSpam($this->controller->in->post['robotizer']);

		$recComment = new \Gratheon\Core\Record();
		$this->fillFacebookData($this->controller->in->post['facebook_id'], $this->controller->in->post['facebook_access_key'], $recComment);

		//Prepare all comment data
		if($this->controller->in->post['comment_url']) {
			if(!preg_match('/http:/i', $this->controller->in->post['comment_url'])) {
				$this->controller->in->post['comment_url'] = 'http://' . $this->controller->in->post['comment_url'];
			}
			$recComment->url = $this->controller->in->post['comment_url'];
		}

		$recMenu             = new \Gratheon\Core\Record();
		$recMenu->title      = $this->controller->in->post['comment_name'];
		$recMenu->module     = 'comment';
		$recMenu->date_added = 'NOW()';
		if($user->data['ID']) {
			$recMenu->userID = $user->data['ID'];
		}
		$recMenu->parentID = $intParentID;
		$recMenu->position = $content_menu->int('parentID=' . $recMenu->parentID, 'MAX(position)+1 as pos');
		$recMenu->ID       = $content_menu->insert($recMenu);

		$recComment->content = strip_tags($this->controller->in->post['comment_body']);

		if($_SESSION['facebook_visitor']) {
			/*$recComment->author = $_SESSION['facebook_visitor']['name'];
			$recComment->email = $_SESSION['facebook_visitor']['email'];*/
			$recComment->userID = $_SESSION['facebook_visitor']['uid'];
		}
		else {
			$recComment->author = $this->controller->in->post['comment_name'];
			$recComment->email  = $this->controller->in->post['comment_email'];
		}

		if($recComment->url) {
			$recComment->show_url = $content_comment->int("url='{$recComment->url}' AND show_url=1 LIMIT 1", "ID") ? 1 : 0;
		}

		if($this->controller->in->post['comment_email']) {
			$recComment->gravatar = md5(strtolower(trim($this->controller->in->post['comment_email'])));
		}
		$recComment->parentID   = $recMenu->ID;
		$recComment->date_added = 'NOW()';
		$recComment->author_IP  = $user->data['intIP'];
		$content_comment->insert($recComment);

		$_SESSION['front']['comment_time'] = time();
		$this->notifyEmailSubscribers($recMenu, $content_menu, $content_comment, $intNodeID, $menu);


		header('Location: ' . $menu->getPageURL($intNodeID));
		exit();
	}


	public function fillFacebookData($uid, $ak, &$recComment) {
		$oConvertor = new \Gratheon\Core\ObjectCovertor();
		if($uid) {
			$arrFacebookResult = $oConvertor->jsonToArray(file_get_contents('https://graph.facebook.com/' . $uid . '/?access_token=' . $ak));

			if($arrFacebookResult->id) {
				$recComment->facebook_id = $arrFacebookResult->id;
				$recComment->author      = $arrFacebookResult->first_name . ' ' . $arrFacebookResult->last_name;
			}
		}
	}


	public function checkForDisabledJS($intNodeID, $controlField) {
		global $menu, $controller;
		if(!$controlField) {
			$_SESSION['front']['last_comment'] = $this->controller->in->post['reply'];
			$controller->redirect($menu->getPageURL($intNodeID) . '/?nojs=1');
		}
	}


	private function notifyEmailSubscribers($recMenu, $content_menu, $content_comment, $intNodeID, $menu) { //Send mail to admin
		require_once(sys_root . 'vendor/phpmailer/phpmailer/class.phpmailer.php');
		$mail           = new \PHPMailer();
		$mail->From     = $this->controller->in->post['comment_email'];
		$mail->FromName = $this->controller->in->post['comment_name'];
		$mail->Subject  = 'Comment to your post..';
		$mail->Body     = $this->controller->in->post['comment_name'] . ' writes:<br />' . $this->controller->in->post['comment_body'];
		$mail->AddAddress(comments_email, 'incoming');
		$mail->Send();
		$recParentComment = $content_menu->obj('t1.module="comment" AND t1.ID=' . $recMenu->parentID, 't2.*', $content_menu->table . ' as t1 LEFT JOIN ' . $content_comment->table . ' as t2 ON t2.parentID=t1.ID');

		if(is_object($recParentComment) && $this->controller->in->post['comment_email'] <> $recParentComment->email && strlen($recParentComment->email) > 5) {
			$mail           = new PHPMailer();
			$mail->From     = $this->controller->in->post['comment_email'];
			$mail->FromName = $this->controller->in->post['comment_name'];
			$mail->Subject  = 'Comment to your post..';
			if(!$this->controller->in->post['comment_name']) {
				$this->controller->in->post['comment_name'] = 'Someone';
			}
			$mail->Body = $this->controller->in->post['comment_name'] . ' left a comment at ' . $menu->getPageURL($intNodeID) . '/ <br />' . $this->controller->in->post['comment_body'] . ' <br />';
			$mail->AddAddress($recParentComment->email, 'incoming');
			$mail->Send();
		}
	}


	private function checkForSpam($controlField) {
		global $user, $error;

		$recBan     = new \Gratheon\Core\Record();
		$sys_banned = $this->model('sys_banned');

		$arrBannedIPs = $sys_banned->arrint('1=1', 'intIP');
		//Autoban robots who try to use invalid fields
		if(strlen($controlField) > 0) {
			$recBan->intIP = ip2long($user->IP);
			if(!in_array($recBan->intIP, $arrBannedIPs)) {
				$sys_banned->insert($recBan);
				$error->fatal('banned', $user->IP);
			}
		}

		if(in_array(ip2long($user->IP), $arrBannedIPs)) {
			$error->fatal('banned', $user->IP);
		}
	}


	public function search_from_public($q) {
		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();

		$content_menu    = $this->model('Menu');
		$content_comment = $this->model('Comment');

		$arrList = $content_menu->arr("t2.content LIKE '%" . $q . "%' AND t1.module='comment'",
			't1.title,t1.ID,t2.ID commentID',
				$content_menu->table . ' t1 INNER JOIN ' .
						$content_comment->table . ' t2 ON t2.parentID=t1.ID');

		$arrEnvelope        = new \Gratheon\CMS\SearchEnvelope();
		$arrEnvelope->count = $content_menu->count();
		$arrEnvelope->title = $this->translate('Comments');

		foreach($arrList as &$item) {
			$rootID = $this->getCommentRoot($item->ID);
			$item->title .= ' &rarr; ' . $content_menu->int($rootID, 'title');
			$item->link_view = $menu->getPageURL($rootID) . '/#n' . $item->commentID;
		}

		$arrEnvelope->list = $arrList;

		return $arrEnvelope;
	}


	public function search_from_admin($q) {
	}


	//last comments and defined node comment rss feed
	public function front_rss() {
		$menu = new \Gratheon\CMS\Menu;
		$tree = new \Gratheon\CMS\Tree;
		$rss  = new \Gratheon\Core\View\RSSProxy();

		$content_menu = $this->model('Menu');
//		$content_comment = $this->model('Comment');

		// Load RSS module

		$langID    = $this->controller->langID;
		$strFilter = '';

		$nodeID = (int)$this->controller->in->get('nodeID');
		if($nodeID) {
			$arrIDs = array();
			$tree->getLinearChildIDs((int)$this->controller->in->get('nodeID'), $arrIDs);
			$strFilter .= "t1.ID IN (" . implode(',', $arrIDs) . ") AND ";
			$rss->title = $this->controller->translate('Comments to') . ' "' . $content_menu->int("ID=" . $nodeID, 'title') . '"';
		}

		//$rss->useCached(); // use cached version if age<1 hour

		//Query all elements
		$strSQL = "SELECT SQL_CALC_FOUND_ROWS t1.*,t3.content,t3.ID commentID,
					DATEDIFF(NOW(),t1.date_added) AS diff,
					UNIX_TIMESTAMP(t1.date_added) AS unix_added,
					DATE_FORMAT(t1.date_added,'%d.%m %H:%i') AS date_added2
				FROM content_menu t1
				LEFT JOIN content_comment t3 ON t3.parentID=t1.ID
				WHERE 
					$strFilter
					t1.langID='{$langID}' AND
					t1.module IN ('comment')
				ORDER BY t1.date_added DESC
				LIMIT 50";

		//pre($strSQL);
		$arrList = $content_menu->q($strSQL);

//		$controller->MIME = 'application/rss+xml';

		$content_image = $this->model('Image');

		//pre($arrList);
		//List all elements
		if($arrList) {
			foreach($arrList as &$item) {

//				$recEntry              = new FeedItem();
				$recEntry                = array();
				$recEntry['guid']        = $menu->getPageURL($item->parentID) . '/#n' . $item->commentID; //sys_url.'article/'.$item->ID;
				$recEntry['title']       = $item->title;
				$recEntry['description'] = $item->content; //$data->short;
				$recEntry['date']        = date('r', $item->unix_added);

				foreach((array)$item->images as $image) {
					$recEntry['description'] .= '<a href="' . $content_image->getOriginalURL($image) . '"><img src="' . $content_image->getSquareURL($image) . '" alt="' . $image->title . '"/></a>';
				}

				$recEntry['description'] = str_replace("href='/", "href='" . sys_url . "/", $recEntry->description); //relative to absolute urls
				$recEntry['description'] = str_replace('href="/', 'href="' . sys_url . '/', $recEntry->description); //relative to absolute urls

				$rss->addArticle($recEntry);

			}
		}
		return $rss->getResultRSSFeed();
		//		return $rss->saveFeed("RSS2.0", sys_root."app/front/view/bin/comments_{$arrCategory->ID}.rss");
	}


	public function list_last_comments() {
		$content_comment = $this->model('Comment');
		$content_menu    = $this->model('Menu');

		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();
		$tree = new \Gratheon\CMS\Tree();

		$iLimit = $this->config('list_last_comment_count') ? $this->config('list_last_comment_count') : 9;

		$arrLastComments = $content_comment->arr(
			'c.userID IS NULL OR c.userID IS NOT NULL AND u.groupID<>2 ORDER BY m.date_added DESC LIMIT ' . $iLimit,
			'u.*, m.*, c.*,p.*, m.parentID as articleID,c.ID commentID',
			'content_comment as c
			LEFT JOIN content_menu as m ON c.parentID=m.ID
			LEFT JOIN sys_user as u ON c.userID = u.ID
			LEFT JOIN content_person as p ON u.personID = p.ID
			');

		$tree->strWhere = "module='comment' AND langID='{$this->controller->langID}' AND ";
		if($arrLastComments) {
			foreach($arrLastComments as &$item) {
				$item->content     = strip_tags($item->content);
				$item->link_delete = sys_url . 'front/call/comment/front_delete/?ID=' . $item->ID;
				$arrParents        = array_reverse($tree->buildSelected($item->articleID));
				foreach($arrParents as $parent) {
					$recParent = $tree->getParent($parent);
					if($recParent->module != 'comment') {
						$item->article_path  = $menu->getPageURL($recParent->ID);
						$item->article_title = $content_menu->int($recParent->ID, "title");
						break;
					}
				}
			}
		}

		return $arrLastComments;
	}


	public function list_top_commentors() {
		$content_comment = $this->model('Comment');

		$arrLastUsers = $content_comment->q(
			'SELECT COUNT(userID) cnt, t3.firstname, t3.lastname
			FROM content_comment t1
			INNER JOIN sys_user t2 ON t2.ID=t1.userID
			INNER JOIN content_person t3 ON t3.ID=t2.personID
			WHERE userID IS NOT NULL AND userID!=1
			GROUP BY userID
			ORDER BY cnt DESC
			LIMIT 15'
		);

		return $arrLastUsers;
	}


	//custom private methods
	public function getNodeComments($parentID) {
		$content_comment = $this->model('Comment');
		$tree            = new \Gratheon\CMS\Tree;

//        $this->add_css($this->name . '/' . __FUNCTION__ . '.css');
		$this->add_js($this->name . '/' . __FUNCTION__ . '.js');

		$tree->strWhere = "module='comment' AND ";
		$arrComments    = $tree->build($parentID, 30);

		foreach($arrComments as &$item) {
			$item->element = $content_comment->obj(
				"c.parentID='" . $item->ID . "' OR c.ID='" . $item->elementID . "'",

				"c.*, ct.url u_url, p.firstname, u.email u_email, DATEDIFF(NOW(), c.date_added) as daysago,
				DATE_FORMAT(c.date_added,'%H:%i') as time_added,
				DATE_FORMAT(c.date_added,'%W') as weekDay,
				DATE_FORMAT(c.date_added,'%e') as monthDay,
				DATE_FORMAT(c.date_added,'%M') as month,
				DATE_FORMAT(c.date_added,'%Y') as year,
				ct.facebook facebook_id",

				'content_comment as c
				LEFT JOIN sys_user as u ON c.userID = u.ID
				LEFT JOIN sys_user_contact ct ON ct.userID=u.ID
				LEFT JOIN content_person p ON p.ID=u.personID
				'
			);

			if(!$item->element) {
				continue;
			}
			if(isset($item->element->u_email)) {
				$item->element->email = $item->element->u_email ? $item->element->u_email : $item->element->email;
				$item->element->url   = $item->element->u_url ? $item->element->u_url : $item->element->url;
			}

			$item->element->author = isset($item->element->firstname) ? $item->element->firstname : $item->element->author;


			$item->element->content = $content_comment->replaceSmilies($item->element->content);
			$item->element->content = $content_comment->formatHTML($item->element->content);
		}

//		echo '<pre>';
//        print_r($arrComments);
		return $arrComments;
	}


	//looks up the content tree for node that is not comment. needed to get comment's article path
	public function getCommentRoot($commentNodeID) {
		$tree         = new \Gratheon\CMS\Tree;
		$content_menu = $this->model('Menu');

		$arrParents = array_reverse($tree->buildSelected($commentNodeID));
		foreach($arrParents as $parent) {
			$recParent = $content_menu->obj($parent);
			if($recParent->module != 'comment') {
				return $recParent->ID;
			}
		}
	}
}