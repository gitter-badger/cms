<?php
/**
 * Modules that can be searched from public and admin sides
 */
namespace Gratheon\CMS\Module\Behaviour;

interface Searchable{
    //public function searchByTag($tagID);
	public function search_from_public($q);
	public function search_from_admin($q);
}
