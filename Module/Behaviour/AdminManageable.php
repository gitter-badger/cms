<?php
/**
 * @author Artjom Kurapov
 * @since 18.03.13 0:40
 */

namespace Gratheon\CMS\Module\Behaviour;
use \Gratheon\CMS;

interface AdminManageable{

	public function edit($recMenu);
	public function update($parentID);
	public function insert($parentID);
	public function delete($parentID);
}