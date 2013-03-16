<?php
/**
 * News page type
 *
 * @author Artjom Kurapov
 * @version 1.1.0
 */
namespace Gratheon\CMS\Module;
use \Gratheon\CMS;

class News extends \Gratheon\CMS\ContentModule implements \Gratheon\CMS\Module\Behaviour\Searchable {

    public $name = 'news';
    public $public_methods = array('front_view', 'view_details','twitter_feed');
    public $static_methods = array('add_comment', 'importExternalServices', 'import_lastfm_music', 'import_livejournal', 'testLJ', 'activity_stream', 'import_wakoopa_software');
    //public $cron_methods=array('sync_twitter_posts'=>30);

    public $per_page = 20;
    private $defaultTwitterImportLanguage = 1;

    //adminpanel methods

    public function main() {
        global $controller;
        $controller->redirect(sys_url . '/content/call/' . $this->name . '/list_news/');
    }

    public function list_news() {
        $this->use_gz = false;
        $sys_languages = $this->model('sys_languages');

        $strFunction = strtolower(__FUNCTION__);
        $offset = $_GET['page'] > 0 ? $this->per_page * ((int)$_GET['page'] - 1) : 0;

        //Filter on top
        $strFilter = '1=1';
        if ($_REQUEST['date_from']) {
            $_SESSION[$this->name][$strFunction]['date_from'] = $_REQUEST['date_from'];
            $arrDate = explode('.', $_REQUEST['date_from']);
            $strFromDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
        }
        elseif ($_SESSION[$this->name][$strFunction]['date_from']) {
            $arrDate = explode('.', $_SESSION[$this->name][$strFunction]['date_from']);
            $strFromDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
        }
        else {
            $_SESSION[$this->name][$strFunction]['date_from'] = date('d.m.Y', time() - 90 * 24 * 3600);
            $strFromDate = date('Y-m-d', time() - 90 * 24 * 3600);
        }
        if ($strFromDate) {
            $strFilter .= " AND t1.date_added>='$strFromDate 00:00:00'";
        }

        if ($_REQUEST['date_to']) {
            $_SESSION[$this->name][$strFunction]['date_to'] = $_REQUEST['date_to'];
            $arrDate = explode('.', $_REQUEST['date_to']);
            $strToDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
        }
        elseif ($_SESSION[$this->name][$strFunction]['date_to']) {
            $arrDate = explode('.', $_SESSION[$this->name][$strFunction]['date_to']);
            $strToDate = $arrDate[2] . '-' . $arrDate[1] . '-' . $arrDate[0];
        }
        else {
            $_SESSION[$this->name][$strFunction]['date_to'] = date('d.m.Y', time());
            $strToDate = date('Y-m-d', time());
        }

        if ($strToDate) {
            $strFilter .= " AND t1.date_added<='$strToDate 23:59:59'";
        }

        $content_news = $this->model('News');

        $strLang = $sys_languages->int("is_default=1", 'ID');
        $intPerPage = $this->per_page;

        //query block
        $arrList = $content_news->q(
            "SELECT SQL_CALC_FOUND_ROWS DATE_FORMAT(t1.date_added,'%d.%m.%Y %H:%i') added_time_formatted, t2.title, t1.ID
			FROM content_news t1
			LEFT JOIN content_news_body t2 ON t2.newsID=t1.ID AND t2.langID='{$strLang}'
			WHERE $strFilter
			ORDER BY t1.date_added DESC
			LIMIT {$offset},{$intPerPage}", "array"
        );

//        $total_count  = $content_news->q("SELECT FOUND_ROWS()", 'int');

        $intPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;

        foreach ($arrList as $objItem) {
            $objItem->link_edit = sys_url . 'content/call/' . $this->name . '/edit_news/?id=' . $objItem->ID;
            $objItem->link_delete = sys_url . 'content/call/' . $this->name . '/delete_news/?id=' . $objItem->ID . "&page=" . $intPage;
        }

        #Create page navigation for first page
        $total_count = $content_news->count();
        $this->assign('title', $this->translate('News'));
        $this->assign('title_badge', $total_count);
        $objPaginator = new CMS\Paginator($this->controller->input, $total_count, $intPage, $this->per_page);

//        $this->assign('messages', array('info' => array($this->translate('News are articles that have strong chronological dependence'))));
        $this->assign('objPaginator', $objPaginator);
        $this->assign('arrList', $arrList);
        $this->assign('link_filter', sys_url . '/content/call/' . $this->name . '/' . $strFunction . '/');
        $this->assign('link_add', sys_url . '/content/call/' . $this->name . '/edit_news/');

        if ($this->config('sync_twitter')) {
            $this->assign('link_twitter_sync', sys_url . '/content/call/' . $this->name . '/sync_twitter_posts/');
            //$this->assign('link_twitter_sync',sys_url.'/content/call/'.$this->name.'/sync_twitter_posts/');
        }

        $this->assign('form_filter', $_SESSION[$this->name][$strFunction]);
//        $this->assign('info', array('News is crosslingual module'));
        //$this->assign('show_twitter',$this->config('twitter_login'));

        return 'ModuleBackend/' . $this->name . '/' . __FUNCTION__ . '.tpl';
    }

    public function edit_news() {
        global $user, $controller;

        $strFunction = strtolower(__FUNCTION__);
        $ID = (int)$_GET['id'];

        $this->add_css($this->name . '/' . __FUNCTION__ . '.css');
        $this->add_js($this->name . '/' . __FUNCTION__ . '.js');

        $content_news = $this->model('News');
        $content_news_body = $this->model('NewsBody');
        $recItem = new \Gratheon\Core\Record();

        if ($_POST) {
            $recItem->userID = $user->data['ID'];

            if ($_POST['date_added_formatted']) {
                $arrDate = explode('.', $_POST['date_added_formatted']);

                if (!$_POST['time_added_formatted']) {
                    $_POST['time_added_formatted'] = '00:00';
                }

                $recItem->date_added = implode('-', array($arrDate[2], $arrDate[1], $arrDate[0])) . ' ' . $_POST['time_added_formatted'];
            }
            else {
                $recItem->date_added = 'NOW()';
            }


            if ($ID) {
                $content_news->update($recItem, 'ID=' . $ID);
                $content_news_body->delete("newsID='$ID'");

                if ($_POST['title']) {
                    foreach ($_POST['title'] as $sLang => $sVal) {

                        if (strlen($_POST['title']) < 2 && strlen($_POST['news_editor'][$sLang]) < 2) {
                            continue;
                        }
                        $recBody = new \Gratheon\Core\Record();
                        $recBody->title = stripslashes($sVal);
                        $recBody->content = stripslashes($_POST['news_editor'][$sLang]);
                        $recBody->langID = $sLang;
                        $recBody->newsID = $ID;

                        $content_news_body->insert($recBody);
                    }
                }
            }
            else {
                $ID = $content_news->insert($recItem);

                if ($_POST['title']) {
                    foreach ($_POST['title'] as $sLang => $sVal) {
                        $recBody = new  \Gratheon\Core\Record();;

                        if (strlen($_POST['title']) < 2 && strlen($_POST['news_editor'][$sLang]) < 2) {
                            continue;
                        }

                        $recBody->title = stripslashes($sVal);
                        $recBody->content = stripslashes($_POST['news_editor'][$sLang]);
                        $recBody->langID = $sLang;
                        $recBody->newsID = $ID;

                        $content_news_body->insert($recBody);
                    }
                }
            }

            $controller->redirect(sys_url . '/content/call/' . $this->name . '/list_news/');

            /*
               if($_FILES){
                   $modImage=new modImage();
                   $modImage->load_models();
                   $recRelation->image_id=$modImage->addFile();
                   $recRelation->news_id=$ID;
                   $content_news_images->insert($recRelation);
               }
               */
            //$this->redirect(sys_url.'/content/call/'.$this->name.'/list_news/');
        }

        if ($ID) {
            $objNews = $content_news->obj($ID,
                "*,DATE_FORMAT(date_added,'%d.%m.%Y') date_added_formatted,DATE_FORMAT(date_added,'%H:%i') time_added_formatted");


            $objNews->title = $content_news_body->map("newsID='$ID'", "langID,title");
            $objNews->content = $content_news_body->map("newsID='$ID'", "langID,content");

            if ($objNews->title) {
                foreach ($objNews->title as &$val) {
                    $val = str_replace(array('"', "'"), array('&#34;', '&#39;'), $val);
                }
            }

            /*
               $objNews->images=$content_news_images->arr('t1.news_id='.$ID, 't1.image_id ID, t2.image_format',
               $content_news_images->table.' t1 INNER JOIN '.$content_image->table.' t2 ON t2.id=t1.image_id');

               if($objNews->images)
               foreach($objNews->images as &$image){
                   $image->link_view=sys_url.'res/image/thumb/'.$image->ID.'.'.$image->image_format.'?rand='.rand(0,1000);
                   $image->link_original=sys_url.'res/image/original/'.$image->ID.'.'.$image->image_format;
               }
               */
            //pre($objNews);
            $this->add_js_var('news_delete_image', sys_url . 'content/call/' . $this->name . '/delete_image/');

            $this->assign('objNews', $objNews);
        }

        $sys_languages = $this->model('sys_languages');

        $this->add_js_var('default_tab', $this->langID);
        $this->assign('link_save', sys_url . '/content/call/' . $this->name . '/' . $strFunction . '/?id=' . $_GET['id']);
        $this->assign('link_back', sys_url . '/content/call/' . $this->name . '/list_news/');

        return 'ModuleFrontend/' . $this->name . '/' . __FUNCTION__ . '.tpl';
    }

    public function delete_news() {
        global $controller;
        $ID = (int)$_GET['id'];

        $content_news = $this->model('News');
        $content_news_images = $this->model('NewsImage');

        $aImages = $content_news_images->arrint("news_id='$ID'", "image_id");
        if ($aImages) {
            foreach ($aImages as $intImage) {
                $this->delete_image($intImage);
            }
        }

        $content_news->delete("ID='$ID'");
        $controller->redirect(sys_url . '/content/call/' . $this->name . '/list_news/?page=' . $_GET['page']);
    }

    public function delete_image($ID = null) {
        if (!$ID) {
            $ID = (int)$_GET['ID'];
        }

        $modImage = new modImage();
        $modImage->load_models();

        if ($ID) {
            $modImage->deleteByID($ID);
        }
    }

    public function getServiceObject($sService) {
        $strCleanService = $this->getServiceName($sService);

        $strClass = '\Gratheon\CMS\Service\\'.ucfirst($strCleanService) . 'Service';
        //$strPath = sys_root . 'cms/external_services/' . $strFile . '.php';

		/** @var $objImportService \Gratheon\CMS\Service\DefaultService */
		$objImportService = new $strClass;
		return $objImportService;

    }

    public function getServiceName($sService) {
        return str_replace(array('sync_', '_import', '_export'), '', $sService);
    }


    //integration methods
    public function importExternalServices() {
        //$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

		/** @var \Gratheon\CMS\Model\NewsExternal $content_news_external */
		/** @var \Gratheon\CMS\Service\DefaultService $objImportService */
        $content_news_external = $this->model('NewsExternal');

        $sys_sync_account = $this->model('sys_sync_account');
        $sys_config = $this->model('sys_config');

        $aImportAccounts = $sys_config->ray("var_name LIKE 'sync_%_import'");
        $aExportAccounts = $sys_config->ray("var_name LIKE 'sync_%_export'");

        foreach ($aImportAccounts as $aSyncConfig) {
            $objImportService = $this->getServiceObject($aSyncConfig['var_name']);
            $aImportSyncAccount = (array)$sys_sync_account->obj($aSyncConfig['var_value'], "*, DECODE(`password`,'".\SiteConfig::db_encrypt_salt."') decrypted_password");

            if (!$objImportService) {
                continue;
            }


			$arrPosts = $objImportService->getLastMessages($aImportSyncAccount);

            if ($this->config($aSyncConfig['var_name'] . '_language')) {
                $content_news_external->defaultImportLanguage = $this->config($aSyncConfig['var_name'] . '_language');
            }

            $aImportedPosts = $content_news_external->importMessages($arrPosts, $aImportSyncAccount['ID']);


			$aImportedPosts = array('test');
pre($aImportedPosts);
pre($aExportAccounts);
            foreach ($aExportAccounts as $arrExportConfig) {
                $objExportService = $this->getServiceObject($arrExportConfig['var_name']);

                $arrExportSyncAccount = (array)$sys_sync_account->obj(
					$arrExportConfig['var_value'],
						"*, DECODE(`password`,'".\SiteConfig::db_encrypt_salt."') decrypted_password"
				);

                if ($arrExportSyncAccount['service']!='odnoklassniki') {
                    continue;
                }
				pre('------');
				pre($arrExportConfig);
				pre($objExportService);
				pre($arrExportSyncAccount);

//if($arrExportConfig['var_name']=='sync_odnoklassniki_export'){
//	$objExportService->postMessage("yess", $arrExportSyncAccount);
//}

				pre($objExportService);
                foreach ($aImportedPosts as $strMessage) {
                    if ($strMessage[0] == '@') {
                        continue;
                    }
					if(method_exists($objExportService, 'postMessage')){
                    	$objExportService->postMessage($strMessage, $arrExportSyncAccount);
					}
                }
            }
        }

        //$controller->redirect(sys_url.'/content/call/'.$this->name.'/list_news/?page='.$_GET['page']);
    }

    public function import_lastfm_music() {
        $sys_sync_account = $this->model('sys_sync_account');
        $content_music_played = $this->model('content_music_played');

        $arrMusicSync = $sys_sync_account->obj("service='lastfm'", '`key`, login');
        if (!$arrMusicSync) {
            return;
        }


        $objLastTracks = simplexml_load_string(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=' . $arrMusicSync->login . '&api_key=' . $arrMusicSync->key));

        foreach ($objLastTracks->recenttracks->track as $objTrack) {
            $arrLastTrack = array(
                'artist' => (string)$objTrack->artist,
                'track' => (string)$objTrack->name,
                'album' => (string)$objTrack->album,
                'artist_link' => (string)$objTrack->url,
                'image_link' => (string)end($objTrack->image),
            );

            if ($objTrack->date) {
                $arrLastTrack['date_updated'] = date("Y-m-d H:i", strtotime((string)$objTrack->date));
            }
            else {
                $arrLastTrack['date_updated'] = 'NOW()';
            }

            $exID = $content_music_played->int("artist='" . addslashes($arrLastTrack['artist']) . "' AND track='" . addslashes($arrLastTrack['track']) . "'", "ID");

            if ($exID) {
                $content_music_played->update($arrLastTrack, "ID='$exID'");
                $content_music_played->update("play_count=play_count+1", "ID='$exID'");
            }
            else {
                $content_music_played->insert($arrLastTrack);
            }
        }
    }

    public function findFacebookMusic() {
        //audio.search
    }


    public function list_last_music_block() {
        $content_music_played = $this->model('content_music_played');
        $arrLastTracks = $content_music_played->arr("1=1 ORDER BY date_updated DESC LIMIT 10");

        return $arrLastTracks;
    }

    public function list_software_block() {
        $content_software_used = $this->model('content_software_used');
        $arrLastTracks = $content_software_used->arr("1=1 ORDER BY active_seconds DESC LIMIT 10");

        return $arrLastTracks;
    }

    public function import_livejournal() {

        $sys_sync_account = $this->model('sys_sync_account');
        $content_news_external = $this->model('content_news_external');
        $content_news = $this->model('News');
        $content_news_body = $this->model('NewsBody');

        if ($this->config('livejournal_language')) {
            $defaultImportLanguage = $this->config('livejournal_language');
        }
        else {
            $defaultImportLanguage = 2;
        }

        $intConfig = $this->config('sync_livejournal');
        $aSyncAccount = (array)$sys_sync_account->obj($intConfig);
        $oFilter = new \Gratheon\Core\TextFilter();

        $intYear = $_GET['year'] ? $_GET['year'] : date('Y');
        $intMonth = $_GET['month'] ? $_GET['month'] : date('m');
        $intMonth = str_pad($intMonth, 2, '0', STR_PAD_LEFT);


        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, "http://www.livejournal.com/export_do.bml?authas=" . $aSyncAccount['login']);
        curl_setopt($tuCurl, CURLOPT_POST, 1);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, "&encid=2&field_allowmask=on&field_currents=on&field_event=on&field_eventtime=on&field_itemid=on&field_logtime=on&field_security=on&field_subject=on&format=xml&header=on&month=$intMonth&what=journal&year=$intYear");
        curl_setopt($tuCurl, CURLOPT_COOKIE, "ljuniq=oMMRgGdqgrRpxeU:1293561002:pgstats0:m1; ljmastersession=v1:u1305061:s651:a0JU0ormZRB//Thanks%20for%20signing%20in%20%2F%20LiveJournal%20loves%20you%20a%20lot%20%2F%20Here%20have%20a%20cookie; ljloggedin=u1305061:s651; BMLschemepref=horizon; langpref=ru/1293561002; ljsession=v1:u1305061:s651:t1293559200:gdbb9964b0557402e634dba875041c7d6b27d80cc//Thanks%20for%20signing%20in%20%2F%20LiveJournal%20loves%20you%20a%20lot%20%2F%20Here%20have%20a%20cookie; __unam=37d207d-12768af7d48-75aa3e75-636; __utma=164322722.298367916.1279744979.1279744979.1286044970.2; __utmz=164322722.1279744979.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); ljlive-bubble=1; ps_tid=");
        //curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml","SOAPAction: \"/soap/action/query\"", "Content-length: ".strlen($data)));

        $tuData = curl_exec($tuCurl);

        if (!curl_errno($tuCurl)) {
            $info = curl_getinfo($tuCurl);
            //echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
        }
        else {
            echo 'Curl error: ' . curl_error($tuCurl);
        }

        curl_close($tuCurl);

        $oXML = simplexml_load_string($tuData);

        foreach ($oXML->entry as $aRow) {
            $aNews['userID'] = 1;
            $aNews['date_added'] = (string)$aRow->eventtime;

            $intId = (int)$aRow->itemid;
            if ($content_news_external->int("servicePostID='" . $intId . "' AND serviceName='livejournal'") || !$intId) {
                continue;
            }

            $aNewsContent['newsID'] = $content_news->insert($aNews);

            $aNewsContent['title'] = (string)$aRow->subject;
            $aNewsContent['content'] = (string)$aRow->event;
            $aNewsContent['content_index'] = $oFilter->convert_html_to_text((string)$aRow->event);
            $aNewsContent['langID'] = $defaultImportLanguage;

            $content_news_body->insert($aNewsContent);

            $content_news_external->insert(array(
                'newsID' => $aNewsContent['newsID'],
                'serviceName' => 'livejournal',
                'servicePostID' => $intId,
                'syncAccount' => $aSyncAccount['ID']
            ));

        }

        $intMonth = (int)$intMonth;

        if ($intMonth == 1) {
            $intMonth = 12;
            $intYear--;
        }
        else {
            $intMonth--;
        }

        if ($intYear != 2000) {
            echo "<script>
		window.location='" . sys_url . "call/news/import_livejournal/?year=$intYear&month=$intMonth';
		</script>
		";
        }


    }

    public function ping($strService = 'ping.feedburner.com', $strPath = '') {
        require_once('external_libraries/xml-rpc/xmlrpc.inc');
        $siteurl = sys_url;
        $blogname = sys_title;

        $client = new xmlrpc_client($strPath, $strService, 80);
        $message = new xmlrpcmsg("weblogUpdates.ping", array(new xmlrpcval($blogname), new xmlrpcval($siteurl)));
        $result = $client->send($message);
        //pre($result);

        if (!$result || $result->faultCode()) {
            return (false);
        }
        return (true);
    }

    public function list_news_block($pageID) {
		$menu = new \Gratheon\CMS\Menu();

        $content_news = $this->model('News');

        $intCount = $this->config('list_news_block_limit');
        $intCount = $intCount ? $intCount : 5;

        $strDateFormat = $this->controller->config('date_format_sql');
        $strDateFormat = $strDateFormat ? $strDateFormat : '%d.%m.%Y';

        $arrNews = $content_news->arr(
            "1=1 ORDER BY date_added DESC LIMIT $intCount",
            "t1.*,t2.title, t2.content, DATE_FORMAT(date_added,'$strDateFormat') date_added_formatted, t3.serviceName",
            "content_news t1
		INNER JOIN content_news_body t2 ON t1.ID=t2.newsID AND t2.langID='{$this->controller->langID}' AND t2.title<>''
		LEFT JOIN content_news_external t3 ON t3.newsID=t1.ID");

        $strMenuPath = $menu->getTplPage('news_details');

        if ($arrNews) {
            foreach ($arrNews as &$arrItem) {
                $arrItem->link_view = $strMenuPath . $arrItem->ID;

                if ($arrItem->serviceName == 'twitter') {
                    $arrItem->content = $this->addTwitterLinks($arrItem->content);
                }
            }
        }

        return $arrNews;

    }

    public function addTwitterLinks($strContent) {
        $strContent = preg_replace("/[ ,\.]?(http\:\/\/([^\/ ]*)([0-9A-Z\?\.=\/\-_&]*))[ \.,]?/i", " <a href='$1'>$2</a> ", $strContent);

        $strContent = preg_replace("/[^A-Z0-9_@]?@([A-Za-z0-9_]*)[ ,\.]?/i", " <a class='twitter_person' href='http://twitter.com/$1'>$1</a> ", $strContent);

        $strContent = preg_replace("/[^A-Z0-9_]?#([A-Za-z0-9_]*)[ ,\,]?/i", " <a class='twitter_tag' href='http://twitter.com/#search?q=%23$1'>$1</a>  ", $strContent);

        //$strContent=preg_replace("/ (http[^ \.]*) /i"," <a href='$1'>&rarr;</a> ",$strContent);


        return $strContent;
    }

    public function category_view(&$recEntry) {
        global $controller;

        $tree = new \Gratheon\CMS\Tree;

        $content_article = $this->model('Article');
        $content_menu = $this->model('Menu');
        $sys_tags = $this->model('sys_tags');
        $content_external_video = $this->model('content_external_video');

        $recEntry->element = $content_article->obj('parentID=' . $recEntry->ID);
        if ($recEntry->element->content) {
            $arrParagraphs = explode('<hr />', $recEntry->element->content);
            if (count($arrParagraphs) > 1) {
                $recEntry->element->content = $arrParagraphs[0];
            }
        }
        $recEntry->images = $content_menu->q(
            "SELECT t1.title,t1.parentID,t2.ID,t2.float_position,t2.image_format,t2.thumbnail_type
			FROM content_menu as t1
			LEFT JOIN content_image as t2 ON t1.ID=t2.parentID
			WHERE t2.float_position<>'inline' AND t1.parentID='{$recEntry->ID}' AND t1.module='image'
			ORDER BY t1.position", "object"
        );

        $recEntry->url = sys_url . $controller->getPath($recEntry->ID) . '/';

        $recEntry->arrTags = $sys_tags->q(
            "SELECT t1.ID, t1.pop, t1.title
			FROM sys_tags t1
			LEFT JOIN content_tags t2 ON t1.ID=t2.tagID
			WHERE t1.ID = t2.tagID AND t2.contentID='{$recEntry->ID}'", "array"
        );

        //External flash videos
        $recEntry->flash_videos = $content_external_video->q(
            "SELECT *
			FROM content_menu as t1
			LEFT JOIN content_external_video as t2 ON t1.ID=t2.parentID
			WHERE t1.parentID='{$recEntry->ID}' AND t1.module='extvideo'
			ORDER BY t1.position", "array"
        );

        foreach ($recEntry->flash_videos as &$item) {
            $item->flash_path = '/ext/asflv_player/player.swf';
            $item->FlashVars = '';
            $item->FlashVars .= 'autoHideOther=true';
            $item->width = 600;
            $item->height = 350;

            if ($item->site == 'youtube.com') {
                $item->src = 'http://www.youtube.com/v/' . $item->site_id;

                $item->FlashVars .= '&amp;MediaLink=' . $item->src;
                $item->FlashVars .= '&amp;MediaLink2=' . $item->src . '%26fmt=18';
                $item->FlashVars .= '&amp;logoLink=' . 'http://www.youtube.com/watch?v=' . $item->site_id;
            }
        }

        $objFile = new modFile();
        $objFile->load_models();
        $recEntry->arrFiles = $objFile->getNodeFiles($item->ID);
        //$recEntry->arrFiles=$this->getFiles($recEntry->ID);

        $arrSelected = $tree->buildSelected($recEntry->ID);
        $recEntry->navigation = $tree->buildLevels($arrSelected);
    }

    public function category_rss(&$item) {
        $this->category_view($item);
        $item->content = $item->element->content;
        $item->flash_videos = $item->element->flash_videos;
    }

    public function search_from_public($q) {
		$menu = new \Gratheon\CMS\Menu();
        $content_news_body = $this->model('NewsBody');
        $content_news = $this->model('News');


        $arrEnvelope = new \Gratheon\CMS\SearchEnvelope();

        $arrEnvelope->title = $this->translate('News');

        $sBaseURL = $menu->getTplPage('news_details');

        $arrArticles = $content_news_body->arr("content_index LIKE '%" . $q . "%' OR title LIKE '%" . $q . "%'", "title, newsID ID, content");
        $arrEnvelope->count = $content_news->count();

        if ($sBaseURL) {
            foreach ($arrArticles as &$item) {
                if (!$item->title) {
                    $item->title = substr(strip_tags($item->content), 0, 100);
                }
                $item->link_view = $sBaseURL . '' . $item->ID;
            }
        }


        $arrEnvelope->list = $arrArticles;

        return $arrEnvelope;
    }

    public function search_from_admin($q) {
    }



	function getArticleData($parentID, $arrFoundEmbeddedIDs = array()) {
		/*
		  if($arrFoundEmbeddedIDs) {
			  $strWhere = " AND t1.ID NOT IN (" . implode(',', $arrFoundEmbeddedIDs) . ")";
		  }
  */

		$content_menu = $this->model('Menu');
		$arrList['twitter_feeds'] = $content_menu->ray("parentID='" . $parentID . "' AND module='news' AND method='twitter_feed' ORDER BY position");



		return $arrList;
	}

    //
    //Public methods
    //
    public function front_view() {
		$menu = new \Gratheon\CMS\Menu();

        if (is_numeric(end($this->controller->input->URL))) {
            return $this->view_details();
        }

        //$intArticleCount=$content_news->int('1=1','COUNT(*)');
        $intPerPage = 30;
        //$intPageCount=ceil($intArticleCount/$intPerPage);

        if (isset($_GET['page'])) {
            $intPageOffset = ((int)$_GET['page'] - 1) * $intPerPage;
        }
        else {
            $intPageOffset = 0;
        }

        $content_news = $this->model('News');
        $content_image = $this->model('Image');
        $content_news_images = $this->model('NewsImage');

        $arrNews = $content_news->q(
            "SELECT SQL_CALC_FOUND_ROWS t1.*,t2.login,DATEDIFF(NOW(),t1.date_added) as diff,
				DATE_FORMAT(t1.date_added,'%H:%i') as time_added,
				DATE_FORMAT(t1.date_added,'%d.%m.%Y') as date_added,
				t3.*,
				t4.serviceName

			FROM content_news as t1
			LEFT JOIN sys_user AS t2 ON t1.userID=t2.ID
			INNER JOIN content_news_body AS t3 ON t3.newsID = t1.ID
			LEFT JOIN content_news_external AS t4 ON t4.newsID = t1.ID
			WHERE 1=1 ORDER BY t1.date_added DESC
			LIMIT $intPerPage
			OFFSET $intPageOffset", "array"
        );

        //Create page navigation for first page
        $intPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;
        $intTotalCount = $content_news->count();
        $objPaginator = new CMS\Paginator($this->controller->input, $intTotalCount, $intPage, $intPerPage);

        $this->assign('objPaginator', $objPaginator);
        $sBaseURL = $menu->getTplPage('news_details');

        if ($arrNews) {
            foreach ($arrNews as &$objNews) {
                $objNews->images = $content_news_images->q(
                    "SELECT t1.image_id, t2.image_format
					FROM content_news_images t1
					INNER JOIN content_image t2 ON t2.id=t1.image_id
					WHERE t1.news_id='{$objNews->ID}'", "array");

                if ($objNews->images) {
                    foreach ($objNews->images as &$image) {
                        $image->link_image = $content_image->getRectangleURL($image); //sys_url . 'res/image/thumb/' . $image->image_id . '.' . $image->image_format;
                    }
                }

                if ($objNews->serviceName == 'twitter') {
                    $objNews->content = $this->addTwitterLinks($objNews->content);
                }

                $objNews->link_view = $sBaseURL . '' . $objNews->ID;
            }
        }

        $this->assign('article', $arrNews[0]);
        $this->assign('intPageCount', $objPaginator->page_count);
        $this->assign('arrArticles', $arrNews);
    }

    public function view_details() {
        //$recElement=$content_article->obj('parentID='.$parentID);

        $this->add_js('front.front.article.js');
		$this->add_css('front.article.css');

        $ID = end($this->controller->input->URI);

        $content_news = $this->model('News');

        $arrItem = $content_news->q(
            "SELECT *, DATEDIFF(NOW(),date_added) as diff, DATE_FORMAT(date_added,'%d MONTH %Y, %H:%i') as date_formatted,
            DATE_FORMAT(date_added,'%M') as date_month, t3.serviceName
			FROM content_news t1
			INNER JOIN content_news_body t2 ON t1.ID=t2.newsID AND t2.langID='{$this->controller->langID}'
			LEFT JOIN content_news_external t3 ON t3.newsID=t1.ID
			WHERE t1.ID='$ID'", "object"
        );

        if ($arrItem->serviceName == 'twitter') {
            $arrItem->content = $this->addTwitterLinks($arrItem->content);
        }

        $arrItem->date_formatted = str_replace('MONTH', strtolower($this->translate($arrItem->date_month)), $arrItem->date_formatted);
        $this->assign('title', $this->config('title_article') . $arrItem->title);
        $this->assign('element', $arrItem);

        $this->content_template = 'news/view_details.tpl';
    }

    public function activity_stream() {
        $content_news = $this->model('content_news');

        $arrList = $content_news->q(
            "SELECT DATE_FORMAT(t1.date_added,'%Y-%m-%dT%H:%i:%sZ') added_time_formatted, t2.content, t1.userID, t1.ID
            FROM content_news t1
            LEFT JOIN content_news_body t2 ON t2.newsID=t1.ID AND t2.langID='rus'
            ORDER BY t1.date_added DESC
            LIMIT 20", "ray"
        );


		$menu = new \Gratheon\CMS\Menu();
        $strMenuPath = $menu->getTplPage('news_details');

        echo '
          {
            "items" : [';

        foreach ($arrList as $item) {

            echo '{
              "published": "' . $item['added_time_formatted'] . '",
              "actor": {
                "url": "' . sys_url . '",
                "objectType" : "person",
                "id": "' . $item['userID'] . '",
                "image": {
                  "url": "' . sys_url . 'res/sys/avatar.jpg",
                  "width": 225,
                  "height": 225
                },
                "displayName": "Artjom Kurapov"
              },
              "verb": "post",
              "object" : {
                "url": "' . $strMenuPath . $item['ID'] . '",
                "id": "' . $item['ID'] . '",
                "content": "' . $item['content'] . '",
              },
              "target" : {
                "url": "' . sys_url . 'rus/news/",
                "objectType": "blog",
                "id": "news",
                "displayName": "Artjom Kurapov"
              }
            }';
        }

        echo ']
          }';
    }
}