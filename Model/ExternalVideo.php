<?php
namespace Gratheon\CMS\Model;

class ExternalVideo extends \Gratheon\Core\Model {
	use ModelSingleton;

	final function __construct() {
		parent::__construct('content_external_video');
	}

	public function getSupportedServices() {
		return array(


			'youtube.com'      => 'Youtube',
			'vimeo.com'        => 'Vimeo',
			'yandex.ru'        => 'Яндекс видео',
			'video.google.com' => 'Google video',
			'metacafe.com'     => 'Metacafe',
			'rutube.ru'        => 'Rutube',
			'dailymotion.com'  => 'Dailymotion.com',
			'smotri.com'       => 'Smotri.com',
			'blip.tv'          => 'Blip.tv',
			'mail.ru'          => 'Mail.ru',
			'myspace.com'      => 'Myspace',
			'russia.ru'        => 'Russia.ru',
			'vesti.ru'         => 'Вести',
			'bloggingheads.tv' => 'Bloggingheads.tv',
			'justin.tv'        => 'Justin.tv',
			'snob.ru'          => 'snob.ru',
			'html'        	   => 'html',

		);
	}




	public function getVideoTitle($service, $code) {

		$title = $Url = '';

		switch ($service) {
			case 'youtube.com':

				try{
					$url = "http://gdata.youtube.com/feeds/api/videos/" . $code;
					$doc = new \DOMDocument;
					$doc->load($url);
					$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;
				}
				catch(\Exception $e){

				}

				break;

			case 'vimeo.com':
				$title = $this->getPageTitle('http://vimeo.com/' . $code);
				$title = str_replace(array(' on Vimeo'), '', $title);
				break;

			case 'dailymotion.com':
				$title = $this->getPageTitle('http://www.dailymotion.com/video/' . $code);
				$title = str_replace(array(' - Video Dailymotion'), '', $title);
				break;

			case 'video.google.com':
				$title = $this->getPageTitle('http://video.google.com/videoplay?docid=' . $code);
				$title = html_entity_decode($title);
				break;
		}

		return $title;
	}


	private function getPageTitle($Url) {
		$title = '';
		$str   = file_get_contents($Url);
		if (strlen($str) > 0) {
			preg_match("/\<title\>(.*)\<\/title\>/", $str, $title);
			$title = $title[1];
		}
		return $title;
	}


	public function codeToFlash(&$recElement) {

		switch ($recElement->site) {
			case 'youtube.com':
				$recElement->flash_path = 'http://www.youtube.com/v/' . $recElement->site_id . '&amp;fs=1&amp;rel=0&amp;showsearch=0&amp;showinfo=0&amp;cc_load_policy=1';
				$recElement->src        = 'http://www.youtube.com/v/' . $recElement->site_id;
				break;

			case 'rutube.ru':
				$recElement->flash_path = 'http://video.rutube.ru/' . $recElement->site_id;
				$recElement->src        = $recElement->flash_path;
				break;

			case 'video.google.com':
				$recElement->flash_path = 'http://video.google.com/googleplayer.swf?docid=' . $recElement->site_id . '&amp;hl=en&amp;fs=true';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'blip.tv':
				$recElement->flash_path = 'http://blip.tv/play/' . $recElement->site_id;
				$recElement->src        = $recElement->flash_path;

				$recElement->html = '<iframe src="http://blip.tv/play/' . $recElement->site_id . '.html" width="550" height="441" frameborder="0" allowfullscreen></iframe>
       				<embed type="application/x-shockwave-flash" src="http://a.blip.tv/api.swf#' . $recElement->site_id . '" style="display:none"></embed>';
				break;

			case 'vimeo.com':
				$recElement->flash_path = 'http://vimeo.com/moogaloop.swf?clip_id=' . $recElement->site_id . '&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=&amp;fullscreen=1';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'metacafe.com':
				$recElement->flash_path = 'http://www.metacafe.com/fplayer/' . $recElement->site_id . '/player.swf';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'smotri.com':
				$recElement->flash_path = 'http://pics.smotri.com/scrubber_custom8.swf?file=' . $recElement->site_id . '&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml&highquality=1';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'bloggingheads.tv':
				$recElement->flash_path = 'http://static.bloggingheads.tv/maulik/offsite/offsite_flvplayer.swf';
				$recElement->src        = $recElement->flash_path;
				$recElement->FlashVars  = 'playlist=http%3A%2F%2Fbloggingheads%2Etv%2Fdiavlogs%2Fliveplayer%2Dplaylist%2F' . $recElement->site_id . '%2F00%3A00%2F45%3A19';
				break;

			case 'mail.ru':
				$strURL = $recElement->site_id;
				$strURL = str_replace('http://video.mail.ru/mail/', '', $strURL);
				$strURL = str_replace('.html', '', $strURL);
				$arrIDs = explode('/', $strURL);

				$strFlashURL = "http://img.mail.ru/r/video2/player_v2.swf?par=http://content.video.mail.ru/mail/" . $arrIDs[0] . "/" . $arrIDs[1] . "/$" . $arrIDs[2] . "$0$2611&page=1&imaginehost=video.mail.ru&perlhost=video.mail.ru&alias=mail&username=" . $arrIDs[0] . "&albumid=" . $arrIDs[1] . "&id=" . $arrIDs[2] . "&catalogurl=http://video.mail.ru/themes/clips";

				$recElement->flash_path = $strFlashURL;
				$recElement->src        = $strFlashURL;
				break;

			case 'yandex.ru':
				$recElement->flash_path = 'http://static.video.yandex.ru/lite/' . $recElement->site_id . '/';
				$recElement->src        = 'http://static.video.yandex.ru/lite/' . $recElement->site_id . '/';
				break;

			case 'russia.ru':
				$recElement->flash_path = 'http://www.russia.ru/player/main.swf?103';
				$recElement->src        = $recElement->flash_path;
				$recElement->FlashVars .= $recElement->site_id;
				break;

			case 'myspace.com':
				$recElement->flash_path = 'http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $recElement->site_id . ',t=1,mt=video';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'vesti.ru':
				$recElement->flash_path = 'http://www.vesti.ru/i/flvplayer.swf?vid=' . $recElement->site_id . '&autostart=false';
				$recElement->src        = $recElement->flash_path;
				break;

			case 'justin.tv':
				$recElement->flash_path = 'http://www-cdn.justin.tv/widgets/archive_embed_player.swf';
				$recElement->FlashVars  = 'auto_play=false&start_volume=25&title=Title&channel=startupschool&archive_id=' . $recElement->site_id;
				break;

			case 'dailymotion.com':
				$recElement->flash_path = 'http://www.dailymotion.com/swf/video/' . $recElement->site_id;
				break;

			case 'snob.ru':
				$recElement->flash_path = 'http://www.snob.ru/player/video.swf';

				$recElement->FlashVars .= $recElement->site_id;
				break;
		}


	}

	public function parseCode($service, $input) {

		$code = $input;

		switch ($service) {
			case 'snob.ru':
				preg_match("/flashvars=\"(.*)&sid=/", $input, $aTemp);
				if($aTemp){
					$code = $aTemp[1];
				}
				break;


			case 'youtube.com':
				$aTemp = str_replace('<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/', '', stripslashes($input));
				if($aTemp){
					$code = substr($aTemp, 0, 11);
				}
				break;

			case 'justin.tv':
				preg_match("/archive_id=([0-9]*)/", $input, $aTemp);
				if($aTemp){
					$code = $aTemp[1];
				}
				break;

			case 'russia.ru':
				$code = str_replace('<embed name="playerblog" src="http://www.russia.ru/player/main.swf?103" flashvars="', '', stripslashes($input));
				$code = current(explode('"', $code));
				break;

			case 'yandex.ru':

				$aTemp = explode('yandex.ru/lite/', stripslashes($input));
				if ($aTemp[1]) {
					$code = current(explode('"', $aTemp[1]));
				}
				else {
					$code = stripslashes($code);
				}
				break;

			case 'rutube.ru':
				$aTemp = explode('http://video.rutube.ru/', stripslashes($input));
				if ($aTemp[1]) {
					$code = current(explode('"', $aTemp[1]));
				}
				else {
					$code = stripslashes($input);
				}
				break;

			case 'dailymotion.com':
				preg_match("/embed\/video\/([a-z0-9]*)/i", $input, $aTemp);
				if($aTemp){
					$code = $aTemp[1];
				}
				break;
		}

		return $code;
	}

	public function getPreviewImage($service, $code){
     switch($service){
         case 'youtube.com':
             return 'http://i.ytimg.com/vi/'.$code.'/0.jpg';

         case 'vimeo.com':
             $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$code.php"));

             return $hash[0]['thumbnail_large'];

         default:
             return false;
     }
 }
}