<?php
/**
 * @author Artjom Kurapov
 * @since 08.12.12 1:41
 */

namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Person extends \Gratheon\CMS\ContentModule implements \Gratheon\CMS\Module\Behaviour\Embeddable {
	public $static_methods = array('json_list_persons');


	public function json_list_persons() {
		$model = $this->model('content_person');
		$list  = $model->arr("1=1", "id, CONCAT(firstname,' ', lastname) name");
		echo json_encode($list);
	}


	public function edit($menu) {
		$this->add_js('modules/person/edit.js');
		$content_person = $this->model('content_person');
//		$content_menu = $this->model('content_menu');

		$person = $content_person->obj($menu->elementID);

		$this->assign('recElement', $person);
	}


	public function insert($parentID) {
		$content_menu = $this->model('content_menu');

		$content_menu->q("UPDATE " . $content_menu->table . " SET elementID='" . (int)$_POST['person_id'] . "' WHERE ID='$parentID'");
	}


	public function update($parentID) {
		$content_menu = $this->model('content_menu');

		$content_menu->q("UPDATE " . $content_menu->table . " SET elementID='" . (int)$_POST['person_id'] . "' WHERE ID='$parentID'");
	}


	//Embeddable
	public function getPlaceholder($menu) {
//		$content_person = $this->model('content_person');
//		$record         = $content_person->obj('ID=' . $menu->elementID);

		$return = $menu->title;
//		if($record->wikipedia) {
//		}
		$return = "<a href=\"#\">" . $return . "</a>";
		return $return;
	}


	public function decodeEmbeddable($menu) {
		$content_person = $this->model('content_person');
		$record         = $content_person->obj('ID=' . $menu->elementID);

		$return = $menu->title;
		if($record->wikipedia) {
			$return = "<a class='person' href='" . $record->wikipedia . "'>" . $return . "</a>";
		}
		return $return;

		//return "<a class='person' href='/person/".$menu->elementID."'>".$menu->title."</a>";
	}
}