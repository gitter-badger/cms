<?php
/**
 * @author Artjom Kurapov
 * @since 08.11.12 22:29
 */

namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Timeline extends \Gratheon\CMS\SearchableContentModule implements \Gratheon\CMS\Module\Behaviour\Embeddable {
	var $name = 'slide';

	private $supportedServices = array(
		'musescore.com'  => 'musescore.com',
	);


	function insert($parentID) {
		$content_musicscore = $this->model('Musicscore');
		/** @var \Gratheon\CMS\Model\Musicscore $content_musicscore*/
		$content_menu = $this->model('content_menu');

		$recElement             = new \Gratheon\Core\Record();
		$recElement->service    = $this->controller->in->post['service'];
		$recElement->service_id = $content_musicscore->parseCode($recElement->service, $this->controller->in->post['serviceCode']);
		$ID                     = $content_musicscore->insert($recElement);

		$content_menu->update(array('elementID' => $ID), "ID='$parentID'");

		$recMenu = $content_menu->obj($parentID);

		if($recMenu->title == '' && $recElement->serviceCode) {
			$strTitle = $content_musicscore->getSlideTitleFromCode($recElement->service, $this->controller->in->post['serviceCode']);
			$strTitle = html_entity_decode($strTitle);

			if(!$strTitle) {
				$strTitle = $content_musicscore->getSlideTitle($recElement->service, $recElement->serviceCode);
			}
			$content_menu->update(array('title'     => $strTitle), "ID='$parentID'");
		}
	}


	function update($parentID) {
		$content_musicscore = $this->model('Musicscore');
		$content_menu       = $this->model('content_menu');
		$ID                 = $content_menu->int("ID='$parentID'", 'elementID');

		/** @var \Gratheon\CMS\Model\Musicscore $content_musicscore*/

		$recElement             = $content_musicscore->obj($ID);
		$recElement->service    = $this->controller->in->post['service'];
		$recElement->service_id = $content_musicscore->parseCode($recElement->service, $this->controller->in->post['serviceCode']);

		$content_musicscore->update($recElement, 'ID=' . $ID);


		$recMenu = $content_menu->obj($parentID);
/*
		if($recMenu->title == '' && $recElement->serviceCode) {
			$strTitle = $content_musicscore->getSlideTitle($recElement->service, $recElement->serviceCode);
			$content_menu->update(array(
				'title' => $strTitle,
			), "ID='$parentID'");
		}*/
	}


	public function edit($recMenu = null) {
		$content_musicscore = $this->model('Musicscore');
		$content_menu       = $this->model('content_menu');

		$this->assign('arrServices', $this->supportedServices);

		/** @var \Gratheon\CMS\Model\Musicscore $content_musicscore*/

		$ID = $recMenu->elementID;
		if($ID) {
			$recElement       = $content_musicscore->obj('ID=' . $ID);
			$recElement->html = $content_musicscore->codeToHTML($recElement->service, $recElement->service_id);

			$this->assign('recElement', $recElement);
		}
	}


	public function delete($parentID) {
		$content_musicscore = $this->model('Musicscore');
		$content_menu       = $this->model('content_menu');
		$ID                 = $content_menu->int("ID='$parentID'", 'elementID');
		$content_musicscore->delete('ID=' . $ID);
	}


	public function getArticleData($parentID) {
		$content_musicscore = $this->model('Musicscore');

		/** @var \Gratheon\CMS\Model\Musicscore $content_musicscore*/

		$arrScores = $content_musicscore->ray(
			"t1.parentID='$parentID' ORDER BY t1.position", 't2.service, t2.service_id',
			'content_menu t1 INNER JOIN content_musicscore t2 ON t1.elementID=t2.ID'
		);

		if($arrScores){
			foreach($arrScores as &$score) {
				$score['html'] = $content_musicscore->codeToHTML($score['service'], $score['service_id']);
			}
		}

		return $arrScores;
	}


	//Embeddable
	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$ID = $menu->elementID;
		$content_musicscore = $this->model('Musicscore');
		$content_menu       = $this->model('Menu');
		$ID                 = $content_menu->int("ID='$parentID'", 'elementID');

		$record  = $content_musicscore->obj('ID=' . $ID);
		$recMenu = $content_menu->obj('ID=' . $parentID);
		return $record->service . ' : ' . $recMenu->title;
	}


	public function decodeEmbeddable($menu) {
		$parentID = $menu->ID;
		$ID = $menu->elementID;

		$content_musicscore = $this->model('Musicscore');
		$content_menu       = $this->model('Menu');
		$ID                 = $content_menu->int("ID='$ID'", 'elementID');

		$score = $content_musicscore->obj("ID=" . $ID);

		/** @var \Gratheon\CMS\Model\Musicscore $content_musicscore*/

		$content = $content_musicscore->codeToHTML($score->service, $score->service_id);

		return $content;
	}
}
