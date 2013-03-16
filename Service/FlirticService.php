<?php
/**
 * @author Artjom Kurapov
 * @since 02.11.12 1:47
 */


namespace Gratheon\CMS\Service;
class FlirticService extends \Gratheon\CMS\Service\DefaultService {

    public $bUseOauth = false;
    public $sLastError;


    function postMessage($sMessage, $aSyncAccount, $sTitle = '') {

        $sUsername = $aSyncAccount['login'];
        $sPassword = $aSyncAccount['decrypted_password'];

/*
		$driver = new \Behat\Mink\Driver\ZombieDriver(
			new \Behat\Mink\Driver\NodeJS\Server\ZombieServer()
		);

		// init session:
		$session = new \Behat\Mink\Session($driver);

		// start session:
		$session->start();

		// open some page in browser:
		$session->visit('http://ru.flirtic.com');
		//echo $session->getPage()->getHtml();

		$js = '
		  browser.
		    fill("input[name=user]", "'.$sUsername.'").
		    fill("input[name=pass]", "'.$sPassword.'").
		    pressButton("OK", function() {


				browser.visit("http://ru.flirtic.com/830016", function(err){
		      		browser.fill("textarea[name=message]","'.$sMessage.'");
		      		browser.pressButton("Опубликовать", function(){
		      			browser.html()
		      			stream.end();
		      		});
		      });
			});
';

		pre($session->evalJS($js));
		$session->stop();
*/
        return true;
    }
}
