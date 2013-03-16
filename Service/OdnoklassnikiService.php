<?php
/**
 * @author Artjom Kurapov
 * @since 02.11.12 1:47
 */
 

namespace Gratheon\CMS\Service;
class OdnoklassnikiService extends \Gratheon\CMS\Service\DefaultService {

    public $bUseOauth = false;
    public $sLastError;


    function postMessage($sMessage, $aSyncAccount, $sTitle = '') {

        $sUsername = $aSyncAccount['login'];
        $sPassword = $aSyncAccount['decrypted_password'];


		$driver = new \Behat\Mink\Driver\ZombieDriver(
			new \Behat\Mink\Driver\NodeJS\Server\ZombieServer()
		);

		// init session:
		$session = new \Behat\Mink\Session($driver);

		// start session:
		$session->start();

		// open some page in browser:
		$session->visit('http://odnoklassniki.ru');

		$js = '

		  // Fill email, password and submit form
		  //browser.
		 //   fill("input#field_email", "'.$sUsername.'").
		//    fill("input#field_password", "'.$sPassword.'");
		browser.html();
		    //pressButton("input#hook_FormButton_button_go", function() {

		      // Form submitted, new page loaded.
		      //assert.ok(browser.success);
		      //assert.equal(browser.text(".mctc_nameLink"), "Артём Курапов");
//		      browser.fill("input#content_textarea","'.$sMessage.'");
//		      browser.pressButton("input#content_btn_submit", function(){
//
//		      	//browser.html();
//		      	stream.end();
//		      });

//		    });
';
pre($js);
		$session->evalJS($js);

        return true;
    }
}
