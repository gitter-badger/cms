<?php
/**
 * @author Artjom Kurapov
 * @since 27.08.12 19:59
 */
namespace Gratheon\CMS\Controller\Content;

class ProtectedContentController extends \Gratheon\Core\Controller{
	public function init($strMethod) {
		$this->arrLanguages = $this->initLanguages($this->in->URI, $this->config->get('use_language_detection'));
		$this->load_translations($this->in->URI[0]);
		$this->loadUser();

		if ((!isset($this->user->data['ID']) || $this->user->data['groupID'] != 2) && $strMethod != 'package_translations_js') {
			$this->redirect(sys_url . 'content/profile/login/');
		}
		$this->assign('user', $this->user->data);
	}
}