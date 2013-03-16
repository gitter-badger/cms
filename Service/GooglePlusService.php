<?php
/**
 * Google+ (plus.google.com) PHP Curl bot
 * @since Sep 29 2011
 * @version 3.0
 * @link http://360percents.com/
 * @@author Artjom Kurapov, Luka Pušić <pusic93@gmail.com>
 */
class GoogleplusService extends DefaultService {

	public $bUseOauth = false;


	private $cookie = '';
	private $useragent = 'Mozilla/4.0 (compatible; MSIE 5.0; S60/3.0 NokiaN73-1/2.0(2.0617.0.0.7) Profile/MIDP-2.0 Configuration/CLDC-1.1)';


	public function postMessage($status = 'testing crosspost', $aSyncAccount) {

		$pageid = false;
		//$pc_uagent = 'Mozilla/5.0 (X11; Linux x86_64; rv:7.0.1) Gecko/20100101 Firefox/7.0.1';
		$debug = FALSE;

		/**
		 * MAIN BLOCK
		 * login_data() just collects login form info
		 * login($postdata) logs you in and you can do pretty much anything you want from here on
		 */
		$loginData = $this->login_data($aSyncAccount['login'], $aSyncAccount['decrypted_password']);
		$this->login($loginData);

		sleep(1);
		$this->update_profile_status($status);
		sleep(1);
		$this->logout(); //optional - log out
	}


	/**
	 * 1. GET: http://plus.google.com/
	 * Parse the webpage and collect form data
	 *
	 * @param $login
	 * @param $pass
	 *
	 * @return array (string postdata, string postaction)
	 */
	function login_data($login, $pass) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_URL, "https://plus.google.com/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$buf = trim(utf8_decode(html_entity_decode(curl_redir_exec($ch))));
		//$newurl = curl_getinfo($ch);
		curl_close($ch);

		//echo "\n[+] Sending GET request to: https://plus.google.com/\n\n";

		$toreturn = '';

		$doc = new DOMDocument;
		$doc->loadxml($buf);
		$inputs = $doc->getElementsByTagName('input');
		foreach ($inputs as $input) {
			switch ($input->getAttribute('name')) {
				case 'Email':
					$toreturn .= 'Email=' . urlencode($login) . '&';
					break;
				case 'Passwd':
					$toreturn .= 'Passwd=' . urlencode($pass) . '&';
					break;
				default:
					$toreturn .= $input->getAttribute('name') . '=' . urlencode($input->getAttribute('value')) . '&';
			}
		}


		function tidy($str) {
			return rtrim($str, "&");
		}

		return array(tidy($toreturn), $doc->getElementsByTagName('form')->item(0)->getAttribute('action'));
	}


	/**
	 * 2. POST login: https://accounts.google.com/ServiceLoginAuth
	 *
	 * @param $postdata
	 */
	function login($postdata) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_URL, $postdata[1]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata[0]);
		$buf = curl_exec($ch); #this is not the g+ home page, because the b**** doesn't redirect properly
		curl_close($ch);

		//echo $buf;
		//echo "\n[+] Sending POST request to: " . $postdata[1] . "\n\n";
	}


	/**
	 * 3. GET status update form:
	 * Parse the webpage and collect form data
	 * //todo
	 */
	function update_profile_status() {
		/*
		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		  curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		  curl_setopt($ch, CURLOPT_URL, 'https://m.google.com/app/plus/?v=compose&group=m1c&hideloc=1');
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		  $buf    = utf8_decode(html_entity_decode(str_replace('&', '', curl_exec($ch))));
		  $header = curl_getinfo($ch);
		  curl_close($ch);

		  //	echo $buf;

		  $params = '';
		  $doc    = new DOMDocument;
		  $doc->loadxml($buf);
		  $inputs = $doc->getElementsByTagName('input');

		  foreach ($inputs as $input) {
			  if (($input->getAttribute('name') != 'editcircles')) {
				  $params .= $input->getAttribute('name') . '=' . urlencode($input->getAttribute('value')) . '&';
			  }
		  }
		  $params .= 'newcontent=' . urlencode($GLOBALS['status']);
		  //$baseurl = $doc->getElementsByTagName('base')->item(0)->getAttribute('href');
		  $baseurl = 'https://m.google.com' . parse_url($header['url'], PHP_URL_PATH);



		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		  curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		  curl_setopt($ch, CURLOPT_URL, $baseurl . '?v=compose&group=m1c&hideloc=1&a=post');
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		  curl_setopt($ch, CURLOPT_REFERER, $baseurl . '?v=compose&group=m1c&hideloc=1');
		  //curl_setopt($ch, CURLOPT_POST, 1);
		  //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		  //https://m.google.com/app/plus/mp/165/data

		  $buf    = curl_redir_exec($ch, $params);
		  $header = curl_getinfo($ch);
		  curl_close($ch);
  */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_URL, 'https://m.google.com/app/plus/mp/165/data'
	//	$baseurl . '?v=compose&group=m1c&hideloc=1&a=post'
	);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'https://m.google.com/');
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		//https://m.google.com/app/plus/mp/165/data

		$buf    = curl_redir_exec($ch,'[null,['.
		'[null,null,6,"[null,1,\"1322753798892_0.2501001788412067\",null,\"test\",null,null,null,null,null,[null,null,[[null,null,\"1c\",null,null,5]]]]"],'.
		'[null,null,28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,7],[null,5]],\"1322753798921\",\"1322753798921\"]]]]"],'.
		'[null,null,28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,5],[null,7]],\"1322753766696\",\"1322753766696\"]],null,null,\"1322753798975\"]]"]'.
		'],"4znmvxi2vl3f5u5gse4nd5ik1km9tfk"]'
/*
[null,[
[null,"19",6,"[null,1,\"1322755030978_0.6556875951383596\",null,\"aa\",null,null,null,null,null,[null,null,[[null,null,\"1c\",null,null,5]]]]"],
[null,"20",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,7],[null,5]],\"1322755030996\",\"1322755030996\"]]]]"],
[null,"15",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,2],[null,5]],\"1322755008493\",\"1322755008493\"]],null,null,\"1322755031012\"]]"],
[null,"16",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,5],[null,7]],\"1322755012841\",\"1322755012841\"]],null,null,\"1322755031012\"]]"],
[null,"17",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,7],[null,9]],\"1322755023563\",\"1322755023563\"]],null,null,\"1322755031012\"]]"],
[null,"18",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,9],[null,7]],\"1322755028635\",\"1322755028635\"]],null,null,\"1322755031012\"]]"]
],"4znmvxi2vl3f5u5gse4nd5ik1km9tfk"]'

[null,[
	[null,"12",6,"[null,1,\"1322755167775_0.25746118305555055\",null,\"faaa\",null,null,null,null,null,[null,null,[[null,null,\"1c\",null,null,5]]]]"],
	[null,"13",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,7],[null,5]],\"1322755167794\",\"1322755167794\"]]]]"],
	[null,"11",28,"[null,[null,0,\"apps-mobile-ui_187.11, Tue Nov 29 12:26:59 2011 (1322598419)\",null,[[null,2,[null,1],null,null,null,[null,[null,5],[null,7]],\"1322755161907\",\"1322755161907\"]],null,null,\"1322755167807\"]]"]
],"4znmvxi2vl3f5u5gse4nd5ik1km9tfk"]
*/
		);
		$header = curl_getinfo($ch);
		curl_close($ch);

		//echo "\n[+] POST Updating status on: " . $baseurl . "\n\n";
		pre(htmlentities($buf));
	}


	/**
	 * Not implemented yet!
	 * just ignore this function for now
	 */
/*
	function update_page_status() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $GLOBALS['pc_uagent']);
		curl_setopt($ch, CURLOPT_URL, 'https://plus.google.com/u/0/b/' . $GLOBALS['pageid'] . '/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$buf = utf8_decode(html_entity_decode(str_replace('&', '', curl_exec($ch))));
		curl_close($ch);
	}

*/
	/**
	 * 3. GET logout:
	 * Just logout to look more human like and reset cookie :)
	 */
	function logout() {
		echo "\n[+] GET Logging out: \n\n";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/m/logout');
		$buf = curl_redir_exec($ch);
		curl_close($ch);
	}

}

function curl_redir_exec($ch, $postVars = false, $base_redirect = "http://plus.google.com/") {
	static $curl_loops = 0;
	static $curl_max_loops = 20;
	if ($curl_loops++ >= $curl_max_loops) {
		$curl_loops = 0;
		return FALSE;
	}
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if ($postVars) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$data = curl_exec($ch);
	if (curl_error($ch)) {
		return false;
	}
	list($header, $data) = explode("\n\r", $data, 2);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//print_r($header);
	//print_r(htmlentities($data));
	$redirect_url = '/Location: (^\n)/i';

	if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match($redirect_url, $header, $matches);
		$new_url = str_replace('Location: ', '',
			current(
				explode("\n",
					substr($header,
						strpos($header, "Location:"), 500))));

		pre($new_url);
		if (!$new_url) {
			//couldn't process the url to redirect to
			$curl_loops = 0;
			return $data;
		}
		curl_setopt($ch, CURLOPT_URL, $new_url);

		return curl_redir_exec($ch, $postVars);
	}
	else
	{
		$curl_loops = 0;
		return $data;
	}
}