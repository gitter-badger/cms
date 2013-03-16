<?php
/**
 * @author Artjom Kurapov
 * @since 19.01.12 22:00
 */

namespace Gratheon\CMS;
use \Gratheon\CMS;

class SearchableContentModule extends ContentModule implements \Gratheon\CMS\Module\Behaviour\Searchable{

        function search_from_public($q) {
            $content_model = $this->model('content_'.$this->name);

            $arrSlides = $content_model->arr(
                "title LIKE '%" . $q . "%' AND t1.module='".$this->name."' ORDER BY t1.position", '*',
                "content_menu as t1 LEFT JOIN " .
                 $content_model->table . ' as t2 ON t1.ID=t2.parentID');

			$arrEnvelope = new SearchEnvelope();
            $arrEnvelope->count = $content_model->count();
            $arrEnvelope->title = $this->controller->translate(ucfirst($this->name).'s');
            $arrEnvelope->template = 'ModuleFrontend/'.$this->name.'/front_search.tpl';
            $arrEnvelope->list = $arrSlides;

            return $arrEnvelope;
        }

        function search_from_admin($q) {
			$arrEnvelope = $this->search_from_public($q);
			$arrEnvelope->template = 'ModuleBackend/'.$this->name.'/front_search.tpl';
        }
}