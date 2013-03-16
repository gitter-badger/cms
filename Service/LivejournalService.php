<?php
/**
 * @author Artjom Kurapov
 * @since 24.09.11 19:46
 */

namespace Gratheon\CMS\Service;
class LivejournalService extends \Gratheon\CMS\Service\DefaultService {

    public $bUseOauth = false;
    public $sLastError;


    function postMessage($sMessage, $aSyncAccount, $sTitle = '') {

        $sUsername = $aSyncAccount['login'];
        $sPassword = $aSyncAccount['decrypted_password'];

        require_once('vendor/phpxmlrpc/phpxmlrpc/lib/xmlrpc.inc');

        //Relative to absolute links
        $sMessage = str_replace('src="/', 'src="' . sys_url, $sMessage);
        $sMessage = str_replace('href="/', 'href="' . sys_url, $sMessage);
        $sMessage = str_replace('href=\'/', 'href=\'' . sys_url, $sMessage);

        $time = time();
        $year = date('Y', $time);
        $month = date('m', $time);
        $day = date('d', $time);
        $hour = date('H', $time);
        $minute = date('i', $time);

        $oRemoteClient = new \xmlrpc_client("/interface/xmlrpc", "livejournal.com", 80);

        $aMessage = new \xmlrpcval(array(
                'username' => new \xmlrpcval($sUsername, 'string'),
                'password' => new \xmlrpcval($sPassword, 'string'),

                'ver' => new \xmlrpcval('1', 'string'),
                'lineendings' => new \xmlrpcval('pc', 'string'),
                'event' => new \xmlrpcval($sMessage, 'string'),
                'subject' => new \xmlrpcval($sTitle, 'string'),

                'year' => new \xmlrpcval($year, 'int'),
                'mon' => new \xmlrpcval($month, 'int'),
                'day' => new \xmlrpcval($day, 'int'),
                'hour' => new \xmlrpcval($hour, 'int'),
                'min' => new \xmlrpcval($minute, 'int')), 'struct'
        );

        $oMessage = new \xmlrpcmsg('LJ.XMLRPC.postevent');
        $oMessage->addparam($aMessage);
        $oRemoteClient->setDebug(0);
        $result = $oRemoteClient->send($oMessage);

        if ($result->faultCode() != 0) {
            $this->sLastError = $result->faultString();
            return false;
        }
        return true;
    }
}
