<?php
/**
 * @author Artjom Kurapov
 * @since 10.03.13 22:00
 */

namespace Gratheon\CMS\Module\Behaviour;
use \Gratheon\CMS;

interface Cloudy{

	public function copyToCloud($filename);
	public function copyFromCloud($filename);
	public function deleteFromCloud($filename);
}