<?php

/**
 * @file
 * A single location to store configuration.
 */

//define('CONSUMER_KEY', 'LpseywtQpO63lB96fiC5w');
//define('CONSUMER_SECRET', 'ZQ8z2UTA4PS5hOWGRuH888VJutvIVPoMIqPPMAf3Fc');

require_once(realpath(dirname(__FILE__)).'/../../sys/Config.php');
require_once(realpath(dirname(__FILE__)).'/../../SiteConfig.php');
define('OAUTH_CALLBACK', sys_url.'cms/external_services/TwitterService/twitter_oauth/callback.php');