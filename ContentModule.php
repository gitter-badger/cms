<?php
/**
 * @author Artjom Kurapov
 * @since 02.03.12 22:10
 */
namespace Gratheon\CMS;
use Gratheon\CMS\Module\Behaviour;

class ContentModule extends \Gratheon\Core\Module
implements \Gratheon\CMS\Module\Behaviour\AdminManageable{
    public function edit($recMenu) {
        $this->assign('show_URL', true);
    }

    public function update($parentID) {
        $this->assign('show_URL', true);
    }

    public function insert($parentID) {}
    public function delete($parentID) {}

}
