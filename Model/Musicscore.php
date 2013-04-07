<?php
namespace Gratheon\CMS\Model;

class Musicscore extends \Gratheon\Core\Model {
	use ModelSingleton;

	final function __construct($callParent=true) {
		if($callParent){
			parent::__construct('content_musicscore');
		}
	}


	public function getSlideTitleFromCode($service, $code){
		switch($service){
			case 'slideshare':
				preg_match('/title="([^"]*)"/i',$code,$matches);
				if(isset($matches[1])){
					return $matches[1];
				}
				break;
		}

		return '';
	}


	public function getSlideTitle($service, $code) {

		$title = $Url = '';

		switch ($service) {
			case 'speakerdeck':
				$raw_page = file_get_contents('http://speakerdeck.com/embed/' . $code . '?slide=1');
				preg_match("/presentation_link: '(.*)'/i", $raw_page, $matches);
				$Url = $matches[1];
				break;
		}

		if($Url==''){
			return '';
		}
		$str = file_get_contents($Url);
		if (strlen($str) > 0) {
			preg_match("/\<title\>(.*)\<\/title\>/", $str, $title);
			$title =  $title[1];
		}

		switch ($service) {
			case 'speakerdeck':
				$title = str_replace(' // Speaker Deck', '', $title);
				break;
		}

		return $title;
	}


	public function codeToHTML($service, $code) {
		if (!$code) {
			return '';
		}

/*
		<iframe width="100%" height="394" src="http://musescore.com/node/71090/embed" frameborder="0"></iframe><span><a href="http://musescore.com/user/53463/scores/71090">Pirates off the Carribian</a> by <a href="http://musescore.com/user/53463">Komponist50</a></span>
*/
		//$result = file_get_contents('http://musescore.com/user/'.$code);
		switch ($service) {
			case 'musescore.com':
				$code = explode('/scores/', $code);
				return '<div class="embed_musicscore"><iframe scrolling=no src="http://musescore.com/node/'.$code[1].'/embed" frameborder="0" ></iframe></div>';

		}
		return '';
	}


	public function parseCode($service, $input) {
		if(substr($input,0,7)!='<iframe'){
			return $input;
		}
		$serviceCode = $input;
		switch ($service) {
			case 'musescore.com':
				$serviceCode = explode('href="http://musescore.com/user/', $input);
				$serviceCode = $serviceCode[1];
				$serviceCode = current(explode('"',$serviceCode));
				//19721/scores/61246
				//$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;
				break;
		}

		return $serviceCode;
	}
}