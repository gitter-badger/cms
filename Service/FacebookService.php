<?php
/**
 * @author Artjom Kurapov
 * @since 09.12.11 0:33
 */
namespace Gratheon\CMS\Service;

class FacebookService extends \Gratheon\CMS\Service\DefaultService{

    public $bUseOauth = true;
    private $accessToken;

    public function setAccess($token){
        $this->accessToken = $token;
    }

    public function getUser($id='me'){
        $strUser = @file_get_contents('https://graph.facebook.com/' . $id . '?access_token=' . $this->accessToken);
        $arrUser = (array)json_decode($strUser);
        return $arrUser;
    }
}
