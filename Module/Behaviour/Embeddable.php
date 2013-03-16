<?php
/**
 * @author Artjom Kurapov
 * @since 19.01.12 22:00
 */

namespace Gratheon\CMS\Module\Behaviour;
use \Gratheon\CMS;

interface Embeddable{

	public function decodeEmbeddable($menu);
	public function getPlaceholder($menu);
}