<?php
/**
 * @author Artjom Kurapov
 * @since 18.01.12 13:28
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Slide extends \Gratheon\CMS\SearchableContentModule implements \Gratheon\CMS\Module\Behaviour\Embeddable{
	var $name = 'slide';

	private $supportedServices = array(
		'slideshare'  => 'Slideshare',
		'prezi'       => 'Prezi',
		'speakerdeck' => 'Speaker Deck',
	);


	function insert($parentID) {
		$content_slide = $this->model('Slide');
		/** @var \Gratheon\CMS\Model\Slide $content_slide*/
		$content_menu = $this->model('content_menu');

		$recElement           = new \Gratheon\Core\Record();
		$recElement->parentID = $parentID;
		$recElement->service  = $_POST['service'];

		$recElement->serviceCode = $content_slide->parseCode($recElement->service, $_POST['serviceCode']);
		$content_slide->insert($recElement);

		$recMenu = $content_menu->obj($parentID);

		if($recMenu->title=='' && $recElement->serviceCode){
			$strTitle = $content_slide->getSlideTitleFromCode($recElement->service, $_POST['serviceCode']);
			$strTitle = html_entity_decode($strTitle);

			if(!$strTitle){
				$strTitle = $content_slide->getSlideTitle($recElement->service, $recElement->serviceCode);
			}
			$content_menu->update(array('title' => $strTitle),"ID='$parentID'");
		}
	}


	function update($parentID) {
		$content_slide = $this->model('Slide');
		$content_menu = $this->model('content_menu');

		/** @var \Gratheon\CMS\Model\Slide $content_slide*/

		$recElement           = $content_slide->obj("parentID='$parentID'");
		$recElement->parentID = $parentID;
		$recElement->service  = $_POST['service'];
		$recElement->serviceCode = $content_slide->parseCode($recElement->service, $_POST['serviceCode']);

		$content_slide->update($recElement, 'parentID=' . $parentID);


		$recMenu = $content_menu->obj($parentID);

		if($recMenu->title=='' && $recElement->serviceCode){
			$strTitle = $content_slide->getSlideTitle($recElement->service, $recElement->serviceCode);
			$content_menu->update(array('title' => $strTitle),"ID='$parentID'");
		}
	}


	public function edit($recMenu = null) {
		$content_slide = $this->model('Slide');

		$parentID = $recMenu->ID;
		$this->assign('arrServices', $this->supportedServices);

		/** @var \Gratheon\CMS\Model\Slide $content_slide*/

		if ($parentID) {
			$recElement = $content_slide->obj('parentID=' . $parentID);

			$recElement->html = $content_slide->codeToHTML($recElement->service, $recElement->serviceCode);

			$this->assign('recElement', $recElement);
		}
	}


	public function delete($parentID) {
		$content_slide = $this->model('content_slide');
		$content_slide->delete('parentID=' . $parentID);
	}


	public function getArticleData($parentID) {
		$content_slide = $this->model('Slide');

		/** @var \Gratheon\CMS\Model\Slide $content_slide*/

		$arrSlides     = $content_slide->ray(
			"t1.parentID='$parentID' ORDER BY t1.position", 't2.*',
			'content_menu t1 INNER JOIN content_slide t2 ON t1.ID=t2.parentID'
		);

		foreach ($arrSlides as &$slide) {
			$slide['html'] = $content_slide->codeToHTML($slide['service'], $slide['serviceCode']);
		}

		return $arrSlides;
	}

	//Embeddable
	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$ID = $menu->elementID;

		$content_slide = $this->model('Slide');
		$content_menu = $this->model('Menu');
		$record       = $content_slide->obj('parentID=' . $parentID);
		$recMenu       = $content_menu->obj('ID=' . $parentID);
		return $record->service.' : '.$recMenu->title;
	}


	public function decodeEmbeddable($menu) {
		$parentID = $menu->ID;
		$ID = $menu->elementID;

		$content_slide = $this->model('Slide');
		$slide       = $content_slide->obj("parentID=" . $ID);

		/** @var \Gratheon\CMS\Model\Slide $content_slide*/

		$content = $content_slide->codeToHTML($slide->service, $slide->serviceCode);

		return $content;
	}


	//public methods
	public function front_view($parentID) {
		$content_menu = $this->model('Menu');
		$content_slide = $this->model('Slide');

		/** @var \Gratheon\CMS\Model\Slide $content_slide*/

		if ($parentID) {
			$recMenu = $content_menu->obj($parentID);
			$recElement = $content_slide->obj('parentID=' . $parentID);

			$recElement->html = $content_slide->codeToHTML($recElement->service, $recElement->serviceCode);

			$this->assign('recMenu', $recMenu);
			$this->assign('recElement', $recElement);
		}
	}

}
