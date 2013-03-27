<?php
/**
 * @version 1.1.4
 * @author Artjom Kurapov
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Mirror extends \Gratheon\CMS\ContentModule {

    var $models = array('content_menu');

    var $name = 'mirror';

    function edit($recMenu = null) {
        $tree = new \Gratheon\CMS\Tree;

        //Integration tab
        $tree->strWhere = " t1.module IN ('article','category','redirect') AND  ";
        $tree->initialize(5, false);
        $arrTree = $tree->build(1, 5, 1);
        //pre($arrTree);

        $this->assign('info', array($this->translate('Reflected elements are automatically updated if source is changed')));
        $this->assign('show_URL', true);
        $this->assign('arrTree', $arrTree);
    }

    function update($parentID) {
        $intDestinationID = (int)$this->controller->in->post['destinationID'];

        $content_menu = $this->model('content_menu');

        $content_menu->update("elementID='$intDestinationID'", "ID='$parentID'");

    }

    function insert($parentID) {
        $intDestinationID = (int)$this->controller->in->post['destinationID'];

        $content_menu = $this->model('content_menu');

        $content_menu->update("elementID='$intDestinationID'", "ID='$parentID'");
    }

    function category_view(&$recEntry) {
        $parentID = $recEntry->ID;

        $content_menu = $this->model('content_menu');

        $objMirror = $content_menu->obj($parentID);
        $objElement = $content_menu->obj($objMirror->elementID);

        if ($objElement->module) {


            $this->controller->loadModule($objElement->module, function($objModule) use ($objElement, $objMirror, $parentID, &$recEntry) {
				$menu = new \Gratheon\CMS\Menu();
                $objModule->init($objElement->method);

                if (method_exists($objModule, 'category_view')) {
                    $objModule->category_view($objElement);
                    $recEntry = $objElement;
                    $recEntry->title = $objMirror->title;
                    $recEntry->url = $menu->getPageURL($parentID);
                }
            });

            $recEntry->template = 'ModuleFrontend/' . $objElement->module . '/category_view.tpl';
        }
    }

    function front_view($parentID) {
        $content_menu = $this->model('content_menu');

        $objMirror = $content_menu->obj($parentID);
        $objElement = $content_menu->obj($objMirror->elementID);
        $module = $this;

        if ($objElement->module) {
            $objElement = $this->controller->loadModule($objElement->module, function($objModule) use($objElement, $objMirror, $module, &$controller){
                $objModule->init($objElement->method);

                if (method_exists($objModule, $objElement->method)) {

                    $module->add_js($objModule->name . '/' . $objElement->method . '.js');
                    $module->add_css($objModule->name . '/' . $objElement->method . '.css');

                    $objElement = $objModule->{$objElement->method}($objElement->ID);

                    $objElement->title = $objMirror->title;

                    return $objElement;
                }
            });

            $this->assign('element', $objElement);

            if (!$objElement->content_template) {
                $this->content_template = $objElement->module . '/' . $objElement->method . '.tpl';
            }
            else {
                $this->content_template = $objElement->module . '/' . $objElement->content_template;
            }

            return $objElement;
        }
    }
}