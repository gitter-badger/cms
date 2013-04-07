<?php
/**
 * @author Artjom Kurapov
 * @since 24.09.11 0:36
 */
namespace Gratheon\CMS\Model;

class NewsExternal extends \Gratheon\Core\Model{
	use ModelSingleton;

	public $defaultImportLanguage = 'rus';
	public $sLastError;


	final function __construct(){
		parent::__construct('content_news_external');
	}



	/**
	 * @param array $arrPosts
	 * @param int $intSyncAccount
	 * @return array of imported posts
	 */
	public function importMessages($arrPosts,$intSyncAccount){
		$content_news = new \Gratheon\Core\Model('content_news');
		$content_news_external = new \Gratheon\Core\Model('content_news_external');
		$content_news_body = new \Gratheon\Core\Model('content_news_body');

		$arrImported = array();
		if($arrPosts){
			foreach($arrPosts as $objTwit){
				$objTwit->id=number_format($objTwit->id, 0, '', '');

				$exNews = $content_news_external->int("servicePostID='{$objTwit->id}' AND serviceName='twitter'",'newsID');

				if(!$exNews){
					$recNews = new \Gratheon\Core\Record();
					$recNewsContent = new \Gratheon\Core\Record();
					$recTwitConn = new \Gratheon\Core\Record();

					$recNews->date_added	= date('Y-m-d H:i:s',strtotime($objTwit->created_at));
					$recNews->userID		= 1;
					$recNewsContent->newsID = $content_news->insert($recNews);

					$recNewsContent->langID			= $this->defaultImportLanguage;
					$recNewsContent->title			= $objTwit->text;
					$recNewsContent->content_index	= $objTwit->text;
					$recNewsContent->content		= $objTwit->text;
					$content_news_body->insert($recNewsContent);

					$recTwitConn->newsID = $recNewsContent->newsID;
					$recTwitConn->servicePostID	= $objTwit->id;
					$recTwitConn->serviceName	= 'twitter';
					$recTwitConn->syncAccount	= $intSyncAccount;
					$content_news_external->insert($recTwitConn);

					$arrImported[]=$objTwit->text;
				}
			}
		}

		return $arrImported;
	}
}

class content_news_external_record extends \Gratheon\Core\Record{
	/** @var int */ 	public $newsID;
	/** @var string */ 	public $serviceName;
	/** @var string */ 	public $syncAccount;
	/** @var int */ 	public $servicePostID;
}
