<?php
namespace Gratheon\CMS\Module;
use Gratheon\CMS;
use Gratheon\Core;

class Formula extends \Gratheon\CMS\ContentModule 
	implements \Gratheon\CMS\Module\Behaviour\Embeddable{
	
    public function edit($recMenu = null) {
        $content_formula = $this->model('content_formula');
        $this->assign('bHideContainer', true);

        $parentID = $recMenu->ID;
        if ($parentID) {
            $recElement = $content_formula->obj('parentID=' . $parentID);
            $this->assign('recElement', $recElement);
        }

        $this->add_js('http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML', false);
        //$this->add_js('/cms/external_libraries/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML', false);
    }

    public function update($parentID) {
        $content_formula = $this->model('content_formula');
        $recElement = $content_formula->obj('parentID=' . $parentID);

        /** @var $recElement content_comment_record */
        $recElement->content = ($this->controller->in->post['content']);
        $recElement->description = ($this->controller->in->post['description']);
        $recElement->format = $this->controller->in->post['format'];

        $content_formula->update($recElement);
    }

    public function insert($parentID) {
        $content_formula = $this->model('content_formula');

        $recElement = new \Gratheon\Core\Record();
        $recElement->parentID = $parentID;
        $recElement->content = ($this->controller->in->post['content']);
        $recElement->description = ($this->controller->in->post['description']);
        $recElement->format = $this->controller->in->post['format'];
        $content_formula->insert($recElement);
    }

    public function delete($parentID) {
        $content_formula = $this->model('content_formula');
        $content_formula->delete("parentID=" . $parentID);
    }



    //Embeddable
    public function getPlaceholder($menu){
		$parentID = $menu->ID;
		$ID = $menu->elementID;

        $content_formula = $this->model('content_formula');
        $record = $content_formula->obj('parentID=' . $parentID);

        return $record->content; //"<img style=\"background-color:white;\" src=\"http://latex.codecogs.com/gif.latex?".urlencode($record->content)."\" />";
    }

    public function decodeEmbeddable($menu){
		$parentID = $menu->ID;
		$ID = $menu->elementID;

        $this->controller->add_js('http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML', false);

        $content_formula = $this->model('content_formula');
        $content_menu = $this->model('content_menu');


        //$menu = $content_menu->obj($ID);
        $record = $content_formula->obj("parentID='$ID'");

        if($record->format=='latex'){
            $record->content = '<div class="formula">
            <div class="formula_source">\\['. $record->content. '\\]</div>
            <div class="formula_description">'.nl2br($record->description).'</div>
            <div href="#formula'.$ID.'" class="formula_title">'.$menu->title.'</div>
            </div>
            ';
        }

        return $record->content;
    }
}