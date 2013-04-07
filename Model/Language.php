<?php
/**
 * @author Artjom Kurapov
 * @since 07.04.13 14:41
 */

namespace Gratheon\CMS\Model;
use Gratheon\CMS;

class Language extends \Gratheon\Core\Model{
	use ModelSingleton;

	final function __construct() {
		parent::__construct('sys_languages');
	}


	/**
	 * @return CMS\Entity\Language[]
	 */
	public function getLanguages(){
		return $this->map("1=1 ORDER BY is_default DESC");
	}
}