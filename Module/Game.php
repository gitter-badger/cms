<?php
/**
 * @author Artjom Kurapov
 * @since 22.09.11 22:44
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Game extends \Gratheon\CMS\ContentModule {

	public $name = 'game';
	public $models = array('content_game');
	public $public_methods = array('view', 'list_games');


	public function insert($parentID) {

	}


	public function update($parentID) {

	}


	function getArticleData($parentID, $arrFoundEmbeddedIDs = array()) {
		$content_game = $this->model('content_game');
		$content_menu = $this->model('content_menu');
		/*
		  if($arrFoundEmbeddedIDs) {
			  $strWhere = " AND t1.ID NOT IN (" . implode(',', $arrFoundEmbeddedIDs) . ")";
		  }
  */
		$arrList = $content_game->arr(
			" t1.parentID='" . $parentID . "' AND t1.module='game' ORDER BY t1.position", 't2.*',
				$content_menu->table . " as t1 LEFT JOIN " .
						$content_game->table . ' as t2 ON t1.elementID=t2.ID');


		return $arrList;
	}


	public function edit($recMenu) {
		$this->assign('hideTitle', true);
		$this->assign('show_URL', true);
		$content_game = $this->model('content_game');
		$games        = $content_game->ray("1=1 ORDER BY ID DESC");
		$this->assign('games', $games);
	}


	public function list_games() {
		$content_game = $this->model('content_game');
		$aGames       = $content_game->ray("1=1 ORDER BY rating DESC");
		$this->assign('games', $aGames);
	}
}
