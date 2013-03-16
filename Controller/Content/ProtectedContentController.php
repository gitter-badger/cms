<?php
/**
 * @author Artjom Kurapov
 * @since 27.08.12 19:59
 */
namespace Gratheon\CMS\Controller\Content;

class ProtectedContentController extends \Gratheon\Core\Controller{
	public function init($strMethod) {
		global $config, $error;

		$arrIPs = str_replace(' ', '', $this->config('enforced_folder_protection_IPs'));
		$arrIPs = (array)explode(',', $arrIPs);

		if ($config->content['enforced_folder_protection']['enabled'] &&
				!in_array($this->user->IP, $arrIPs) &&
				!in_array($this->user->IP, $config->content['enforced_folder_protection']['IPs']) &&
				!$this->user->link->int("IP=INET_ATON('{$this->user->IP}') AND groupID=2")
		) {

			$error->fatal(404);
		}

		if ((!isset($this->user->data['ID']) || $this->user->data['groupID'] != 2) && $strMethod != 'package_translations_js') {
			$this->redirect(sys_url . 'content/profile/login/');
		}
		$this->assign('user', $this->user->data);
	}
}