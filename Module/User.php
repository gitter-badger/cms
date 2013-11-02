<?php
namespace Gratheon\CMS\Module;

class User extends \Gratheon\CMS\ContentModule {
	var $name = 'user';
	var $public_methods = array('register', 'forgot', 'activate', 'reactivate', 'profile');
	var $static_methods = array('login', 'logout', 'openid', 'sync_facebook_friends', 'friends_foaf', 'facebook_connect', 'google_connect');


	function insert($parentID) {
		$this->save_mail_templates();
	}


	function update($parentID) {
		$this->save_mail_templates();
	}


	function edit($recMenu = null) {
		$parentID = $recMenu->ID;
		if($parentID) {
			$moduleID = $content_module->int('title="' . $this->name . '"', 'ID');
			$langID   = $content_menu->int($parentID, 'langID');

			$arrEmail = $sys_email_templates->obj("tag='activation_mail' AND moduleID='$moduleID' AND langID='$langID'");
			$this->assign('arrEmail', $arrEmail);
		}

		$this->assign('show_URL', true);
	}


	function delete($parentID) {
	}


	function register() {

		$sys_user = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');

		if($this->controller->in->post) {
			$regData = \stdClass();
			$regData->login    = trim($this->controller->in->post['login']);
			$regData->email    = strtolower(trim($this->controller->in->post['email']));
			$regData->password = md5($this->controller->in->post['password']);

			$regData->langID  = $this->controller->langID;
			$regData->groupID = 1;

			//Validate

			//Login
			if(!preg_match("/([A-Z0-9]{5,15})/i", $regData->login)) {
				$arrErrors[] = $this->translate("Login must consist of 5-15 letters or numbers");
			}
			$exUser = $sys_user->obj('login="' . $regData->login . '"');
			if($exUser) {
				$arrErrors[] = $this->translate('Login is already registered');
			}

			//Email
			if(!preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i", $regData->email)) {
				$arrErrors[] = $this->translate("Invalid email format");
			}
			$exUser = $sys_user->obj('email="' . $regData->email . '"');
			if($exUser) {
				$arrErrors[] = $this->translate('Email is already registered');
			}

			//Password
			if(strlen($this->controller->in->post['password']) < 5) {
				$arrErrors[] = $this->translate('Password must be at least 5 symbols long');
			}

			if($this->controller->in->post['password'] != $this->controller->in->post['password2']) {
				$arrErrors[] = $this->translate('Passwords do not match');
			}


			if(!$arrErrors) {
				$regData->activation_hash = md5(time() . sys_url);
				$regData->ID              = $sys_user->insert($regData);

				$regContact = \stdClass();
				$regContact->user_id      = $regData->ID;
				$regContact->phone_mobile = $this->controller->in->post['phone_mobile'];
				$regContact->post_index   = $this->controller->in->post['post_index'];
				$regContact->home_address = $this->controller->in->post['address'];

				$userID = $sys_user_contact->insert($regContact);

				$this->send_activation($regData->ID, $regData->activation_hash);

				/*$user->login($regData->login,md5($regData->password));*/
				//$this->redirect($_SERVER['REQUEST_URI'].'/&success=1');
				$this->assign('registered', 1);
			}
			else {
				$this->assign('errors', $arrErrors);
				$this->assign('user_registration', $this->controller->in->post);
			}
		}

	}


	function send_activation($userID, $strHash) {

		$sys_user = $this->model('sys_user');
		$content_module = $this->model('content_module');
		$sys_email_templates = $this->model('sys_email_templates');

		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();

		$arrUser  = $sys_user->obj($userID);
		$moduleID = $content_module->int('title="' . $this->name . '"', 'ID');
		$langID   = $this->controller->langID;

		$arrTemplate = $sys_email_templates->obj("tag='activation_mail' AND moduleID='$moduleID' AND langID='$langID'");

		$arrTemplate->text = str_replace('{user}', $arrUser->firstname, $arrTemplate->text);
		$arrTemplate->html = str_replace('{user}', $arrUser->firstname, $arrTemplate->html);

		$arrTemplate->html = str_replace('{link}', $menu->getTplPage('user_activation') . '?hash=' . $strHash, $arrTemplate->html);
		$arrTemplate->text = str_replace('{link}', $menu->getTplPage('user_activation') . '?hash=' . $strHash, $arrTemplate->text);

		$this->controller->smarty('strHTML', $arrTemplate->html);
		$arrTemplate->html = $this->controller->view(sys_root . 'app/front/view/tpl_wrapper/front.email.tpl');
		//mail($arrUser->email,$arrTemplate->title,$arrTemplate->text);


		require_once(sys_root . 'vendor/phpmailer/phpmailer/class.phpmailer.php');
		$mail = new \PHPMailer();

		$mail->From     = $this->controller->config('system_email');
		$mail->FromName = $this->controller->config('system_email_name');
		$mail->Sender   = $this->controller->config('system_email');
		$mail->AddReplyTo($this->controller->config('system_email'), $this->controller->config('system_email_name'));

		$mail->AddAddress($arrUser->email);
		$mail->IsHTML(true);
		$mail->Subject = $arrTemplate->title;
		$mail->Body    = $arrTemplate->html;
		$mail->AltBody = $arrTemplate->text;
		$mail->Send();
	}


	function reactivate() {

		$strEmail = $this->controller->in->post['email'];
		$arrUser  = $sys_user->obj("email='$strEmail'");
		if($arrUser->ID) {
			$arrUser->activation_hash = md5(time() . sys_url);
			$this->send_activation($arrUser->ID, $arrUser->activation_hash);
			$sys_user->update($arrUser);
		}

		//		$this->assign('content_template','module.user.reactivation.tpl');
		$this->assign('sent', $arrUser->ID ? 1 : 0);
	}


	function forgot() {
		$strEmail = $this->controller->in->post['email'];
		$arrUser  = $sys_user->obj("email='$strEmail'");
		if($arrUser->ID) {
			$strPass           = $arrUser->password = substr(md5(time() . sys_url), 0, 10);
			$arrUser->password = md5($arrUser->password);
			$sys_user->update($arrUser);
		}

		$moduleID = $content_module->int('title="' . $this->name . '"', 'ID');
		$langID   = $system->langID;

		//Send email template
		$arrTemplate       = $sys_email_templates->obj("tag='forgot_password' AND moduleID='$moduleID' AND langID='$langID'");
		$arrTemplate->text = str_replace('{user}', $arrUser->firstname, $arrTemplate->text);
		$arrTemplate->html = str_replace('{user}', $arrUser->firstname, $arrTemplate->html);
		$arrTemplate->html = str_replace('{password}', $strPass, $arrTemplate->html);
		$arrTemplate->text = str_replace('{password}', $strPass, $arrTemplate->text);

		if($arrTemplate && $arrUser->ID) {
			//			mail($arrUser->email,$arrTemplate->title,$arrTemplate->text);


			$this->controller->smarty('strHTML', $arrTemplate->html);
			$arrTemplate->html = $this->controller->view(sys_root . 'app/front/view/tpl_wrapper/front.email.tpl');


			require_once(sys_root . 'vendor/phpmailer/phpmailer/class.phpmailer.php');
			$mail = new \PHPMailer();

			$mail->From     = $this->controller->config('system_email');
			$mail->FromName = $this->controller->config('system_email_name');
			$mail->Sender   = $this->controller->config('system_email');
			$mail->AddReplyTo($this->controller->config('system_email'), $this->controller->config('system_email_name'));

			$mail->AddAddress($arrUser->email);
			$mail->IsHTML(true);
			$mail->Subject = $arrTemplate->title;
			$mail->Body    = $arrTemplate->html;
			$mail->AltBody = $arrTemplate->text;
			$mail->Send();

		}

		$this->assign('sent', $arrUser->ID ? 1 : 0);
		//$this->assign('content_template','module.user.forgot.tpl');
	}


	function activate() {
		$strHash = $_GET['hash'];
		$arrUser = $sys_user->obj("activation_hash='$strHash'");

		$arrUser->activated = 1;
		$sys_user->update($arrUser);

		$strLink = sys_url . $menu->getTplPage(2);

		$this->assign('link_reactivation', $strLink);
		$this->assign('activated', $arrUser->ID ? 1 : 0);
		//$this->assign('content_template','module.user.activation.tpl');
	}


	function profile() {

		$userID = $user->data['ID'];
		if(!$userID) {
			return;
		}

		if($this->controller->in->post) {
			//Email
			if(!preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i", $this->controller->in->post['email'])) {
				$arrErrors[] = $this->translate("Invalid email format");
			}

			//Password
			if(strlen($this->controller->in->post['password']) > 0 && strlen($this->controller->in->post['password']) < 5) {
				$arrErrors[] = $this->translate('Password must be at least 5 symbols long');
			}

			if($this->controller->in->post['password'] != $this->controller->in->post['password2']) {
				$arrErrors[] = $this->translate('Passwords do not match');
			}

			if(!$arrErrors) {
				$regData->email = strtolower(trim($this->controller->in->post['email']));
				if($this->controller->in->post['password']) {
					$regData->password = md5($this->controller->in->post['password']);
				}
				$regData->firstname = $this->controller->in->post['firstname'];
				$regData->lastname  = $this->controller->in->post['lastname'];
				$regData->langID    = $system->langID;
				$regData->ID        = $user->data['ID'];
				$sys_user->update($regData);

				$regContact->phone_mobile = $this->controller->in->post['phone_mobile'];
				$regContact->post_index   = $this->controller->in->post['post_index'];
				$regContact->home_address = $this->controller->in->post['address'];

				$this->assign('saved', 1);
				$sys_user_contact->update($regContact, 'user_id=' . $regData->ID);
			}
			else {
				$this->assign('errors', $arrErrors);
			}
		}

		$arrUser = $sys_user->obj($userID, "*",
			'sys_user t1 LEFT JOIN sys_user_contact t2 ON t2.user_id=t1.ID');


		if(!$userID) {
			return;
		}

		$this->assign('profile_user', $arrUser);
		//$this->assign('content_template','module.user.profile.tpl');
	}


	function save_mail_templates() {

		if($this->controller->in->post['activate_title']) {
			$moduleID = $content_module->int('title="' . $this->name . '"', 'ID');
			$langID   = $this->controller->in->request['langID'];

			$recEmail->title = $this->controller->in->request['activate_title'];
			$recEmail->text  = $this->controller->in->request['activate_text'];
			$recEmail->html  = $this->controller->in->request['activate_html'];

			$sys_email_templates->update($recEmail, "tag='activation_mail' AND moduleID='$moduleID' AND langID='$langID'");
		}
	}


	//static methods
	function login() {
		$strLogin = $this->controller->in->post['login'];
		$strPass  = md5($this->controller->in->post['pass']);
		$objUser  = $sys_user->obj("login='$strLogin' AND password='$strPass'");
		if(!$objUser) {
			$_SESSION[$this->name][strtolower(__FUNCTION__)]['errors'][] = $this->translate('Wrong username or password');
		}
		elseif(!$objUser->activated) {
			$_SESSION[$this->name][strtolower(__FUNCTION__)]['errors'][] = $this->translate('Email is not activated');
		}
		else {
			$user->login($strLogin, $strPass);
		}

		$this->controller->redirect(sys_url);
	}


	function logout() {
		$user->logout();
		$this->controller->redirect(sys_url);
	}


	function openid() {


		$op = new \Gratheon\Kurapov\Module\BasicProvider;
		$op->login = 'test';
		$op->password = 'test';
		$op->server();

	}


	function openid_old() {

		global $user, $known, $run_mode, $openid_profile, $sreg, $p, $g, $charset, $port, $proto, $controller;

		$openid_profile = array(
			# Basic Config - Required
			'auth_username' => 'test',
			'auth_password' => '098f6bcd4621d373cade4e832627b4f6',
			'auth_realm'    => 'gefast',
			'idp_url'       => sys_url . 'call/' . $this->name . '/' . __FUNCTION__ . '/',
			'auth_domain'   => sys_url, //.'profile/openid_identity/',

			# Optional Config - Please see README before setting these
			#	'microid'	=>	array('user@site.com', 'http://delegator.url'),
			#	'pavatar'	=>	'http://your.site.com/path/pavatar.img',
			# Advanced Config - Please see README before setting these
			#	'allow_gmp'	=>	false,
			#	'allow_test'	=> 	false,
			#	'lifetime'	=>	1440,
			#	'paranoid'	=>	false, # EXPERIMENTAL
			# Debug Config - Please see README before setting these
			#	'debug'		=>	false,
			#	'logfile'	=>	'/tmp/phpMyID.debug.log',
			#	'force_bigmath'	=>	false,
		);

		$sreg = array(
			'nickname' => $user->data['FIO'], //'tot_ra',
			'email'    => $user->data['email'],
			'fullname' => $user->data['FIO'], //'Artjom Kurapov',
			'dob'      => $user->data['birth_date'], //'1985-01-11',
			'gender'   => $user->data['sex'], //'M',
			'country'  => 'EE',
			'language' => 'ru',
			#	'postcode'		=> '22000',
			#	'timezone'		=> 'America/New_York'
		);


		/**
		 * Set a constant to indicate that phpMyID is running
		 *
		 * use .htaccess if you have problems:

		SetEnvIf Authorization "(.*)" PHP_AUTH_DIGEST=$1
		RewriteCond %{HTTP:Authorization} !^$
		RewriteCond %{QUERY_STRING} openid.mode=authorize
		RewriteCond %{QUERY_STRING} !auth=
		RewriteCond %{REQUEST_METHOD} =GET

		RewriteRule (.*) ?%{QUERY_STRING}&auth=%{HTTP:Authorization} [L]
		 */
		define('PHPMYID_STARTED', true);

		/**
		 * List the known types and modes
		 * @name $known
		 * @global array $GLOBALS['known']
		 */
		$known = array('assoc_types'   => array('HMAC-SHA1'),

					   'openid_modes'  => array(
						   'accept',
						   'associate',
						   'authorize',
						   'cancel',
						   'checkid_immediate',
						   'checkid_setup',
						   'check_authentication',
						   'error',
						   'id_res',
						   'login',
						   'logout',
						   'test'),

					   'session_types' => array('',
						   'DH-SHA1'),

					   'bigmath_types' => array('DH-SHA1'),
		);


		$g = 2;

		$p = '155172898181473697471232257763715539915724801966915404479707' .
				'7953140576293785419175806512274236981889937278161526466314385615958256881888' .
				'8995127215884267541995034125870655654980358010487053768147672651325574704076' .
				'5857479291291572334510643245094715007229621094194349783925984760375594985848' .
				'253359305585439638443';

		require_once(sys_root . 'ext/openid/MyID.php');


		$charset = 'utf-8'; //'iso-8859-1';

		// Set the internal encoding
		if(function_exists('mb_internal_encoding')) {
			mb_internal_encoding('utf-8');
		}

		// Avoid problems with non-default arg_separator.output settings
		// Credit for this goes to user 'prelog' on the forums
		ini_set('arg_separator.output', '&');

		// Do a check to be sure everything is set up correctly
		self_check();


		// Determine the HTTP request port

		$port = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' && $_SERVER['SERVER_PORT'] == 443)
				|| $_SERVER['SERVER_PORT'] == 80)
				? ''
				: ':' . $_SERVER['SERVER_PORT'];

		// Determine the HTTP request protocol
		$proto = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? 'https' : 'http';


		// Set the authorization state - DO NOT OVERRIDE
		//$openid_profile['authorized'] = false;

		// Set a default log file
		if(!array_key_exists('logfile', $openid_profile)) {
			$openid_profile['logfile'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $openid_profile['auth_realm'] . '.debug.log';
		}

		// Set a default IDP URL
		if(!array_key_exists('idp_url', $openid_profile)) {
			$openid_profile['idp_url'] = sprintf("%s://%s%s%s", $proto, $_SERVER['SERVER_NAME'], $port, $_SERVER['PHP_SELF']);
		}

		// Determine the requested URL - DO NOT OVERRIDE
		$openid_profile['req_url'] = sprintf("%s://%s%s%s", $proto, $_SERVER['HTTP_HOST'], $port, $_SERVER["REQUEST_URI"]);

		// Set the default allowance for testing
		if(!array_key_exists('allow_test', $openid_profile)) {
			$openid_profile['allow_test'] = false;
		}

		// Set the default allowance for gmp
		if(!array_key_exists('allow_gmp', $openid_profile)) {
			$openid_profile['allow_gmp'] = false;
		}

		// Set the default force bigmath - BAD IDEA to override this
		if(!array_key_exists('force_bigmath', $openid_profile)) {
			$openid_profile['force_bigmath'] = false;
		}

		// Determine if GMP is usable
		$openid_profile['use_gmp'] = (extension_loaded('gmp') && $openid_profile['allow_gmp']) ? true : false;

		// Determine if I can perform big math functions
		$openid_profile['use_bigmath'] = (extension_loaded('bcmath') || $openid_profile['use_gmp'] || $openid_profile['force_bigmath']) ? true : false;

		// Set a default authentication domain
		//if (! array_key_exists('auth_domain', $openid_profile)) $openid_profile['auth_domain'] = $openid_profile['req_url'] . ' ' . $openid_profile['idp_url'];

		// Set a default authentication realm
		if(!array_key_exists('auth_realm', $openid_profile)) {
			$openid_profile['auth_realm'] = 'phpMyID';
		}

		// Determine the realm for digest authentication - DO NOT OVERRIDE
		$openid_profile['php_realm'] = $openid_profile['auth_realm'] . (ini_get('safe_mode') ? '-' . getmyuid() : '');

		// Set a default lifetime - the lesser of GC and cache time
		if(!array_key_exists('lifetime', $openid_profile)) {
			$sce                        = session_cache_expire() * 60;
			$gcm                        = ini_get('session.gc_maxlifetime');
			$openid_profile['lifetime'] = $sce < $gcm ? $sce : $gcm;
		}


		/*
				  * Optional Initialization
				  */
		// Setup optional headers
		$openid_profile['opt_headers'] = array();

		// Determine if I should add microid stuff
		if(array_key_exists('microid', $openid_profile)) {
			$hash   = sha1($openid_profile['idp_url']);
			$values = is_array($openid_profile['microid']) ? $openid_profile['microid'] : array($openid_profile['microid']);

			foreach($values as $microid) {
				preg_match('/^([a-z]+)/i', $microid, $mtx);
				$openid_profile['opt_headers'][] = sprintf('<meta name="microid" content="%s+%s:sha1:%s" />', $mtx[1], $proto, sha1(sha1($microid) . $hash));
			}
		}

		// Determine if I should add pavatar stuff
		if(array_key_exists('pavatar', $openid_profile)) {
			$openid_profile['opt_headers'][] = sprintf('<link rel="pavatar" href="%s" />', $openid_profile['pavatar']);
		}


		/*
				  * Do it
				  */
		// Decide which runmode, based on user request or default
		$run_mode = (isset($this->controller->in->request['openid_mode'])
				&& in_array($this->controller->in->request['openid_mode'], (array)$known['openid_modes']))
				? $this->controller->in->request['openid_mode']
				: 'no';

		// Run in the determined runmode
		debug("Run mode: $run_mode at: " . time());
		debug($this->controller->in->request, 'Request params');

		switch($run_mode) {
			case 'no':
				return $this->controller->view('profile.openid_idenity.no.tpl');
				break;
			default:
				eval($run_mode . '_mode();');
				break;

		}


		exit();
	}


	public function sync_facebook_friends() {

		$sys_sync_account = sys_sync_account::singleton();

		$aSyncAccount = (array)$sys_sync_account->obj("service='facebook'");

		$sys_user         = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');
		$iso_languages    = $this->model('iso_languages');

		//read all Friends
		//AAAAAAITEghMBALbVIuuRUPa1Gv4qP14eiUmL2UFZCzGdV9OSFLG6uZABA0MQqOCkiVVQ7w1EdWDk5pZAnyTa0FytcAnhvq1lIO14ZAbUKQZDZD
		/*
				$friends = (array)json_decode(file_get_contents('https://graph.facebook.com/me/friends?access_token=' . $aSyncAccount['key3']));

				$i = 0;
				foreach ($friends['data'] as $user) {
					$ID = $sys_user_contact->int("facebook='{$user->id}'", "userID");
					if ($ID) {
						continue;
					}
					else {
						$i++;
						if ($i > 10) continue;

						$friend = (array)json_decode(file_get_contents('https://graph.facebook.com/' . $user->id . '?access_token=' . $aSyncAccount['key3']));

						$newuser = array(
							'login' => $user->name,
							'firstname' => $friend['first_name'],
							'lastname' => $friend['last_name'],
						);

						if (isset($friend['gender'])) {
							$newuser['sex'] = $friend['gender'] == 'female' ? 'f' : 'm';
						}

						if (isset($friend['locale'])) {
							$lang = $iso_languages->int("id_1='".current(explode('_',$friend['locale']))."'","ID");

							if($lang){
								$newuser['langID'] = $lang;
							}
						}

						if(isset($friend['birthday'])){
							$newuser['date_birth']=date('d.m.Y', strtotime($friend['birthday']));
						}

						$ID = $sys_user->insert($newuser);
						$sys_user_contact->insert(array(
							'userID' => $ID,
							'facebook' => $user->id
						));

					}
					//$friend_profile = $facebook->api('/'.$user['id']);
				}
				*/


		$source_friends = $sys_user_contact->ray("userID<>1 ORDER BY last_facebook_friend_sync ASC LIMIT 10", "userID, facebook");

		$sys_user_relation = $this->model('sys_user_relation');

		foreach($source_friends as $source) {
			//Read common friends
			$common_friends = explode(
				'<uid>',
				str_replace(array('</friends_getMutualFriends_response>', '</uid>', "\n"), '', file_get_contents('https://api.facebook.com/method/friends.getMutualFriends?target_uid=' . $source['facebook'] .
							'&access_token=AAAAAAITEghMBALbVIuuRUPa1Gv4qP14eiUmL2UFZCzGdV9OSFLG6uZABA0MQqOCkiVVQ7w1EdWDk5pZAnyTa0FytcAnhvq1lIO14ZAbUKQZDZD')
				));

			if($common_friends) {
				unset($common_friends[0]);

				foreach($common_friends as $common_id) {
					$sys_user_relation->insert(array(
						'srcUserID' => $source['userID'],
						'dstUserID' => $sys_user_contact->int("facebook='" . trim($common_id) . "'")
					));
				}
			}

			$sys_user_contact->update("last_facebook_friend_sync=NOW()", "userID='{$source['userID']}'");
		}

	}


	public function friends_foaf() {
		$sys_user = $this->model('sys_user');
		//$sys_user_contact = $this->model('sys_user_contact');

		$friends = $sys_user->q(
			"SELECT *
            FROM sys_user t1
            INNER JOIN sys_user_contact t2 ON t1.ID=t2.userID
            INNER JOIN content_person t3 ON t3.ID=t1.personID
            WHERE t1.groupID=1", "ray"
		);
		$this->assign('friends', $friends);

		return 'ModuleFrontend/user/friends_foaf.tpl';
	}


	public function facebook_connect() {

		$sys_user         = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');
		$content_person = $this->model('content_person');

		if(!$_SESSION['facebook_visitor']) {
			$service = new \Gratheon\CMS\Service\FacebookService();
			$service->setAccess($this->controller->in->request['accessToken']);
			$facebookUser = $service->getUser();

			//pre($facebookUser);
			if($facebookUser['id']) {
				$_SESSION['facebook_visitor'] = $facebookUser;

				$contact = $sys_user_contact->int("facebook='{$facebookUser['id']}'", "userID");
				if(!$contact) {

//					$personID = $content_person->insert(array(
//						'firstname' => $facebookUser['first_name'],
//						'lastname'  => $facebookUser['last_name'],
//						'sex'       => $facebookUser['gender']
//					));

					$_SESSION['facebook_visitor']['uid'] = $uid = $sys_user->insert(array(
						//'pesonID'   => $personID,
						'groupID'   => 3,
						'login'     => $facebookUser['username'],
						'password'  => '',
						'firstname' => $facebookUser['first_name'],
						'lastname'  => $facebookUser['last_name'],
						'langID'    => $facebookUser['locale'] == 'ru_RU' ? 'rus' : 'eng',
					));

					$sys_user_contact->insert(array(
						'facebook' => $facebookUser['id'],
						'URL'=> $facebookUser['link'],
						'userID'   => $uid,
					));
				}
				else {
					$_SESSION['facebook_visitor']['uid'] = $contact['id'];
					$this->controller->user->auto_login($contact['id']);
				}
			}
			else {
				$_SESSION['facebook_visitor'] = false;
			}
		}

		echo json_encode($_SESSION['facebook_visitor']);
		//$config->facebookAPI[0]
	}


	public function google_connect() {
		$sys_user         = $this->model('sys_user');
		$sys_user_contact = $this->model('sys_user_contact');

		require_once sys_root . 'ext/google-api-php-client/src/apiClient.php';
		require_once sys_root . 'ext/google-api-php-client/src/contrib/apiOauth2Service.php';

		$client = new apiClient();
		$client->setApplicationName("Google UserInfo PHP Starter Application");
		// Visit https://code.google.com/apis/console?api=plus to generate your
		// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
		$client->setClientId('821324064990.apps.googleusercontent.com');
		$client->setClientSecret('YdmFmThBb057WHj8YuJze0vH');

		$client->setRedirectUri(sys_url . 'call/' . $this->name . '/' . __FUNCTION__ . '/');
		// $client->setDeveloperKey('insert_your_developer_key');
		$oauth2 = new apiOauth2Service($client);

		if(isset($_GET['code'])) {
			$client->authenticate();
			$_SESSION['token'] = $client->getAccessToken();
			$redirect          = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
		}

		if(isset($_SESSION['token'])) {
			$client->setAccessToken($_SESSION['token']);
		}

		if(isset($this->controller->in->request['logout'])) {
			unset($_SESSION['token']);
			unset($_SESSION['google_visitor']);
			$client->revokeToken();
			$this->controller->redirect(sys_url);
		}

		if($client->getAccessToken()) {
			$googleUser = $oauth2->userinfo->get();


			if($googleUser['id']) {
				$_SESSION['google_visitor'] = $googleUser;

				$contact = $sys_user_contact->int("google='{$googleUser['id']}'", "userID");
				if(!$contact) {
					$_SESSION['google_visitor']['uid'] = $uid = $sys_user->insert(array(
						'groupID'   => 3,
						'login'     => $googleUser['name'],
						'email'     => $googleUser['email'],
						'password'  => '',
						'firstname' => $googleUser['given_name'],
						'lastname'  => $googleUser['family_name'],
						'url'       => $googleUser['link'],
						'langID'    => $googleUser['locale'] == 'ru' ? 'rus' : 'eng',
						'sex'       => ($googleUser['gender'] == 'male' ? 'm' : 'f')
					));

					$sys_user_contact->insert(array(
						'google' => $googleUser['id'],
						'userID' => $uid,
					));
				}
				else {
					$_SESSION['google_visitor']['uid'] = $contact['id'];
					global $user;
					$user->auto_login($contact['id']);
				}
			}
			else {
				$_SESSION['google_visitor'] = false;
			}

			$_SESSION['token'] = $client->getAccessToken();
		}
		else {
			$authUrl = $client->createAuthUrl();
		}


		if(isset($personMarkup)) {
			print $personMarkup;
		}

		if(isset($authUrl)) {
			$this->controller->redirect($authUrl);
			//header("Location:" . $authUrl);
		}
		else {
			$this->controller->redirect(sys_url);
			//header("Location: ".sys_url);
			//print "<a class='logout' href='?logout'>Logout</a>";
		}
	}
}
/*
require sys_root . 'vendor/tot-ra/lightopenid/provider/provider.php';
class BasicProvider extends \LightOpenIDProvider{
    public $select_id = true;
    public $login = '';
    public $password = '';

    function __construct()
    {
        parent::__construct();

        # If we use select_id, we must disable it for identity pages,
        # so that an RP can discover it and get proper data (i.e. without select_id)
        if(isset($_GET['id'])) {
            $this->select_id = false;
        }
    }

    function setup($identity, $realm, $assoc_handle, $attributes)
    {
        header('WWW-Authenticate: Basic realm="' . $this->data['openid_realm'] . '"');
        header('HTTP/1.0 401 Unauthorized');
    }

    function checkid($realm, &$attributes)
    {
        if(!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        }

        if ($_SERVER['PHP_AUTH_USER'] == $this->login
            && $_SERVER['PHP_AUTH_PW'] == $this->password
        ) {
            # Returning identity
            # It can be any url that leads here, or to any other place that hosts
            # an XRDS document pointing here.
            return $this->serverLocation . '?id=' . $this->login;
        }

        return false;
    }

}
*/