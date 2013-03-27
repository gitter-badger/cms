<?php
/**
 * Frequently Asked Questions module
 * @version 1.0.1
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Faq extends \Gratheon\CMS\ContentModule {

    public $name = 'faq';

    public $models = array('content_faq', 'content_menu');

    function front_view($parentID) {
        $tree = new \Gratheon\CMS\Tree;

        $content_menu = $this->model('Menu');
        $content_faq = $this->model('content_faq');

        $recEntry = $content_menu->obj($parentID);

        $arrQuestions = $content_faq->arr("parentID='$parentID' ORDER BY orderID");

        $arrSelected = $tree->buildSelected($parentID);
        $recEntry->navigation = $tree->buildLevels($arrSelected);

        $this->assign('element', $recEntry);
        $this->assign('arrQuestions', $arrQuestions);
    }

    function insert($parentID) {

        $content_faq = $this->model('content_faq');

        $content_faq->delete("parentID='" . $parentID . "'");

        $position = 1;

        if ($this->controller->in->post['questions']) {
            foreach ((array)$this->controller->in->post['questions'] as $key => $strQuestion) {
                if (strlen($strQuestion) > 0) {
                    $recAnswer = new \Gratheon\Core\Record();
                    $recAnswer->question = $strQuestion;
                    $recAnswer->answer = $this->controller->in->post['answers'][$key];
                    $recAnswer->orderID = $position;
                    $recAnswer->parentID = $parentID;
                    $content_faq->insert($recAnswer);
                    $position++;
                }
            }
        }
    }

    function edit($recMenu = null) {
        $content_faq = $this->model('content_faq');
        $parentID = $recMenu->ID;

        $this->add_css($this->name . '/' . __FUNCTION__ . '.css');
        $this->add_js($this->name . '/' . __FUNCTION__ . '.js');

        if ($parentID) {
            $recElement = new \Gratheon\Core\Record();
            $recElement->answers = $content_faq->arr("parentID='" . $parentID . "' ORDER BY orderID");

            $this->assign('recElement', $recElement);
        }
        $this->assign('show_URL', true);
    }

    function update($parentID) {
        $content_faq = $this->model('content_faq');

        $content_faq->delete("parentID='" . $parentID . "'");

        $position = 1;

        if ($this->controller->in->post['questions']) {
            foreach ((array)$this->controller->in->post['questions'] as $key => $strQuestion) {
                if (strlen($strQuestion) > 0) {
                    $recAnswer = new \Gratheon\Core\Record();
                    $recAnswer->question = $strQuestion;
                    $recAnswer->answer = stripslashes($this->controller->in->post['answers'][$key]);
                    $recAnswer->orderID = $position;
                    $recAnswer->parentID = $parentID;
                    $content_faq->insert($recAnswer);
                    $position++;
                }
            }
        }

    }

    function delete($parentID) {
        $content_faq = $this->model('content_faq');

        $content_faq->delete('parentID=' . $parentID);
    }


}