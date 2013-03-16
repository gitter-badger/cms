<?php
namespace Gratheon\CMS\Service;
/**
 * @author Artjom Kurapov, Sergei Pashko
 * @since 24.09.11 19:51
 */
class DefaultService {

    public function getLastMessages() {
    }

    public function ping($strService = 'ping.feedburner.com', $strPath = '') {
        global $config;

        $siteurl = sys_url;
        $blogname = $config->default_title;

		require_once sys_root.'vendor/phpxmlrpc/phpxmlrpc/lib/xmlrpc.inc';

        $client = new \xmlrpc_client($strPath, $strService, 80);
        $message = new \xmlrpcmsg("weblogUpdates.ping", array(new \xmlrpcval($blogname), new \xmlrpcval($siteurl)));
        $result = $client->send($message);

        if (!$result || $result->faultCode()) {
            return (false);
        }
        return (true);
    }

    public function untiny_message($message) {
        if (strstr($message, 'http://')) {
            $i = 10;
            while (preg_match("#http:\/\/t\.co\/(\w+)#", $message, $regex) && $i--) {
                $short_url = $regex[0];
                $message = preg_replace("#http:\/\/t\.co\/(\w+)#", $this->untiny($short_url), $message, 1);
            }
        }
        return $message;
    }

    public function untiny($short_url) {
        $response = @simplexml_load_file('http://untiny.me/api/1.0/extract/?url=' . $short_url);
        $result = $response->org_url;
        if (strstr($result, 'http://')) {
            return $this->untiny($result);
        }
        else
        {
            return $short_url;
        }
    }
}
