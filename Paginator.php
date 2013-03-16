<?php
namespace Gratheon\CMS;

/**
 * Generates and calculates info about listing items
 */
class Paginator {

    public function __construct($input, $element_count, $page = 1, $perpage = 7) {

        $this->element_count = $element_count;
        $this->per_page = $perpage;
        $this->page_count = ceil($this->element_count / $this->per_page);

        //Limit top and bottom page counts
        $this->selected = $page > $this->page_count ? $this->page_count : $page;
        $this->selected = $this->selected < 1 ? 1 : $this->selected;

        $this->page_offset = $page <= $this->page_count ? ($this->selected - 1) * $this->per_page : 0;
        $this->next_page = $this->selected < $this->page_count ? $this->selected + 1 : $this->page_count;
        $this->prev_page = $this->selected > 1 ? $this->selected - 1 : 1;
        $this->max = $this->next_page + 1 < $this->page_count ? $this->next_page + 1 : $this->page_count;
        $this->start = $this->prev_page - 2 > 0 ? $this->prev_page - 2 : 1;
        $this->pages = array(1 => 1);

        $this->url = $input->sURL . '?';


        $intPage = $this->start;
        while ($intPage < $this->max) {
            $intPage++;
            $this->pages[$intPage] = $intPage;
        }
        $this->pages[$this->page_count] = $this->page_count;
    }

}