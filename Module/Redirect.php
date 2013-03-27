<?php
/**
 * File page type
 * @version 1.0.2
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Redirect extends \Gratheon\CMS\ContentModule {

    var $models = array('content_redirect', 'content_menu', 'tpl_links');
    var $arrModulesExcluded = array("'comment'", "'image'", "'file'", "'video'");

    function edit($recMenu = null) {
        $content_redirect = $this->model('content_redirect');
        $tpl_links = $this->model('tpl_links');

        $parentID = $recMenu->ID;

        if ($parentID) {
            $recElement = $content_redirect->obj('parentID=' . $parentID);
            $this->assign('recElement', $recElement);
        }

        $tree = new \Gratheon\CMS\Tree;

        $tree->strWhere = "
			t1.module NOT IN (" . implode(',', $this->arrModulesExcluded) . ") AND  ";
        $tree->initialize(5, false);

        $recSelected = new \Gratheon\Core\Record();
        $recSelected->ID = 1;
        $arrTree = $tree->flatTree;

        $this->assign('arrTree', $arrTree);
        $this->assign('arrConnections', $tpl_links->arr());
        $this->assign('bHideTags', true);
        $this->assign('show_URL', true);
    }

    function insert($parentID) {
        $content_redirect = $this->model('content_redirect');

        $newElement = new \Gratheon\Core\Record();
        $newElement->parentID = $parentID;
        $newElement->destination_type = $this->controller->in->post['destination_type'];
        $newElement->URL = $this->controller->in->post['URL'];
        if ($this->controller->in->post['pageID']) {
            $newElement->pageID = $this->controller->in->post['pageID'];
        }

        if ($this->controller->in->post['connectionID']) {
            $newElement->connectionID = $this->controller->in->post['connectionID'];
        }

        $oldElement = $content_redirect->obj('parentID=' . $parentID);

        if ($oldElement->ID) {
            $newElement->ID = $oldElement->ID;
            $content_redirect->update($newElement);
        }
        else {
            $newElement->ID = $content_redirect->insert($newElement);
        }
    }

    function update($parentID) {
        $content_redirect = $this->model('content_redirect');

        $newElement = new \Gratheon\Core\Record();
        $newElement->destination_type = $this->controller->in->post['destination_type'];
        $newElement->URL = $this->controller->in->post['URL'];
        if ($this->controller->in->post['pageID']) {
            $newElement->pageID = $this->controller->in->post['pageID'];
        }

        if ($this->controller->in->post['connectionID']) {
            $newElement->connectionID = $this->controller->in->post['connectionID'];
        }

        $content_redirect->update($newElement, "parentID='$parentID'");
    }

    function delete($parentID) {
        $content_redirect = $this->model('content_redirect');
        $content_redirect->delete("parentID='$parentID'");
    }

    function front_view($parentID) {

        $content_redirect = $this->model('content_redirect');

        $objLink = $content_redirect->obj("parentID='$parentID'");

		$menu = new \Gratheon\CMS\Menu();

        if ($objLink->destination_type == 'URL') {
            $this->controller->redirect($objLink->URL);
        }
        elseif ($objLink->destination_type == 'page') {
			$this->controller->redirect($menu->getPageURL($objLink->pageID));
        }
        elseif ($objLink->destination_type == 'connection') {
			$this->controller->redirect($menu->getTplPage($objLink->connectionID));
        }
    }

}