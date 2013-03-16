<?php
/**
 * @author Artjom Kurapov
 * @since 24.09.11 19:57
 */

namespace Gratheon\CMS\Service;

class TwitterService extends \Gratheon\CMS\Service\DefaultService{
	public $bUseOauth = true;

	public function getLastMessages($aSyncAccount){
		require_once('TwitterService/twitteroauth/twitteroauth.php');
		//require_once('twitter_oauth/config.php');
		$connection = new \TwitterOAuth($aSyncAccount['key'], $aSyncAccount['key2'], $aSyncAccount['key3'], $aSyncAccount['key4']);
		$connection->get('account/verify_credentials');
		return $connection->get('statuses/user_timeline');
	}
}
