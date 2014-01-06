<?php
/**
 * @author Artjom Kurapov
 * @since 22.09.11 22:44
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Movie extends \Gratheon\Core\Module {

	public $name = 'movie';
	public $public_methods = array('front_view');


	public function list_movies() {
		$content_movie = $this->model('content_movie');

		$intPerPage = $this->per_page;
		$offset     = $this->controller->in->get('page') > 0 ? $this->per_page * ((int)$this->controller->in->get('page') - 1) : 0;

		$aMovies = $content_movie->q(
			"SELECT SQL_CALC_FOUND_ROWS *
			FROM content_movie
			WHERE 1=1
			ORDER BY rating DESC
			LIMIT $offset,$intPerPage"
		);

		$total_count = $content_movie->count();
		$intPage     = $this->controller->in->get('page') ? (int)$this->controller->in->get('page') : 0;

		$objPaginator = new CMS\Paginator($this->controller->in, $total_count, $intPage, $this->per_page);
		$this->assign('objPaginator', $objPaginator);

		$this->assign('link_add', sys_url . '/content/call/' . $this->name . '/edit_movie/');
		$this->assign('arrList', $aMovies);
	}


	public function front_view() {
		$content_movie = $this->model('content_movie');
		$aMovies       = $content_movie->ray("1=1 ORDER BY rating DESC");
		$this->assign('movies', $aMovies);
	}


	public function edit_movie() {
		$ID          = (int)$this->controller->in->get('id');
		$content_movie = $this->model('content_movie');
		$movie = $content_movie->obj($ID);
		$this->assign('movie',$movie);
	}
}
