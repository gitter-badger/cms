<?
require 'vkapi.class.php';

$api_id = 1234; // Insert here id of your application
$secret_key = ' your secret key '; // Insert here secret key of your application

$VK = new vkapi($api_id, $secret_key);

$resp = $VK->api('getProfiles', array('uids'=>'1,6492'));

print_r($resp);
?>
