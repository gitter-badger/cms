<?php
namespace Gratheon\Cms\Controller\Content;

class Profile extends \Gratheon\Core\Controller {
    var $models = array('sys_users', 'sys_languages');
    var $name = 'profile';

    function main() {
        $sys_languages = $this->model('sys_languages');

		$this->add_css('/vendor/twitter/bootstrap/css/bootstrap.min.css', false);
        $this->add_js('/vendor/jquery/jqyery/jquery-1.7.2.js');
        $this->add_js('content.profile.js');

        $arrLanguages = $sys_languages->arr();
        $this->assign('arrLanguages', $arrLanguages);
        $this->assign('title', $this->translate('Authentication'));

        if (isset($this->in->post['login'])) {
            $success = $this->user->login($this->in->post['login'], md5($this->in->post['pass']));
        }

        if ($success || $this->user->data['ID'] && $this->user->data['groupID'] == 2) {
            $this->redirect(sys_url . 'content/');
        }

        $this->assign('sys_url', sys_url);

        return $this->view('layout/login.tpl');

    }

    function logout() {
        $this->user->logout();
        $this->redirect(sys_url . 'content/profile/login/');
    }

    function error($str) {
        global $system;
        $this->assign('error', $system->translate($str));
        return $this->view('error.tpl');
    }

    function ping() {
        global $user;

        if ($_GET['sid']) {
            session_id($_GET['sid']);
        }

        echo "{expire_time:" . session_cache_expire() . ",user_id:" . (int)$user->data['ID'] . "}";
    }

}