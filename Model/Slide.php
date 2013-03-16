<?php
namespace Gratheon\CMS\Model;

class Slide extends \Gratheon\Core\Model {
	private static $instance;


	/**
	 * @return content_slide
	 */
	public static function singleton() {
		if (!isset(self::$instance)) {
			$c              = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}


	final function __construct($callParent=true) {
		if($callParent){
			parent::__construct('content_slide');
		}
	}


	public function getSlideTitleFromCode($service, $code){
		switch($service){
			case 'slideshare':
				preg_match('/title="([^"]*)"/i',$code,$matches);
				if(isset($matches[1])){
					return $matches[1];
				}
				/*
				<iframe src="http://www.slideshare.net/slideshow/embed_code/10973558" width="427" height="356" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC;border-width:1px 1px 0;margin-bottom:5px" allowfullscreen> </iframe> <div style="margin-bottom:5px"> <strong> <a href="http://www.slideshare.net/antonkeks/2-basics" title="Java Course 2: Basics" target="_blank">Java Course 2: Basics</a> </strong> from <strong><a href="http://www.slideshare.net/antonkeks" target="_blank">Anton Keks</a></strong> </div>
				*/
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


		switch ($service) {
			case 'slideshare':
				return '<iframe src="http://www.slideshare.net/slideshow/embed_code/' . $code . '" style="border: 0px; padding: 0px; margin: 0px; background-color: transparent;width:400px;height: 600px;" scrolling="no"></iframe>';

			case 'prezi':
				return '<div class="prezi-player">
					<object name="prezi_{$recElement->serviceCode}" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
						<param name="movie" value="http://prezi.com/bin/preziloader.swf"/>
						<param name="allowfullscreen" value="true"/>
						<param name="allowscriptaccess" value="always"/>
						<param name="bgcolor" value="#ffffff"/>
						<param name="flashvars" value="prezi_id=' . $code . '&amp;lock_to_path=0&amp;color=ffffff&amp;autoplay=no&amp;autohide_ctrls=0"/>
						<embed id="preziEmbed_tqi_plm2udhi" name="preziEmbed_' . $code . '" src="http://prezi.com/bin/preziloader.swf"
							   type="application/x-shockwave-flash" allowfullscreen="true" style="width:100%;height: 600px;" allowscriptaccess="always"
							   bgcolor="#ffffff" flashvars="prezi_id=' . $code . '&amp;lock_to_path=0&amp;color=ffffff&amp;autoplay=no&amp;autohide_ctrls=0"></embed>
					</object>
				</div>';

			case 'speakerdeck':
				return '<iframe style="border: 0px; padding: 0px; margin: 0px; background-color: transparent; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px; width: 668px; height: 563.849609375px; background-position: initial initial; background-repeat: initial initial; " mozallowfullscreen="true" webkitallowfullscreen="true" frameborder="0" allowtransparency="true" id="presentation_frame_' . $code . '" src="http://speakerdeck.com/embed/' . $code . '?slide=1"></iframe>';

		}
		return '';
	}


	public function parseCode($service, $input) {
		$serviceCode = $input;
		switch ($service) {
			case 'slideshare':
				$serviceCode = explode('http://www.slideshare.net/slideshow/embed_code/', stripslashes($input));
				if (count($serviceCode) > 1) {
					$serviceCode = $serviceCode[1];
				}
				else {
					$serviceCode = $serviceCode[0];
				}

				$serviceCode = current(explode('"', $serviceCode));

				//$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;

				break;

			case 'prezi':
				$serviceCode = current(explode('/', str_replace('http://prezi.com/', '', $input)));
				break;

			case 'speakerdeck':

				if (strpos($input, "script async ") !== false) {
					$serviceCode = explode('"', $input);
					$serviceCode = $serviceCode[3];
				}
				break;
		}

		return $serviceCode;
	}
}