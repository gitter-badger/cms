<?php
/**
 * @author Artjom Kurapov
 * @since 22.09.11 22:44
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Movie extends \Gratheon\Core\Module{

	public $name='movie';
	public $models = array('content_movie');
	public $public_methods = array('list_movies');

	public function list_movies(){
		$content_movie = $this->model('content_movie');
		$aMovies = $content_movie->ray("1=1 ORDER BY rating DESC");
		$this->assign('movies',$aMovies);
	}
}
