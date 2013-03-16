<?php
/**
 * @author Artjom Kurapov
 * @since 24.09.11 23:40
 */
namespace Gratheon\CMS\Service;

class VkontakteService{

	public $bUseOauth = true;

    public function __construct(){
        include_once('VkontakteService/vkapi.class.php');
    }

	public function postMessage($strMessage,$aSyncAccount){
		$vkontakte = new \vkapi('3213896', 'ULV237SXMx9XSHCR7CQf');

		//$vkontakte = new \vkuserapi ($aSyncAccount['login'],$aSyncAccount['decrypted_password']);
		$vkontakte->api ('wall.post',array('ts'=>time(),'message'=> $strMessage));

		unset ($vkontakte);
	}
}
