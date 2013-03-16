<?php
/**
 * @author Artjom Kurapov
 * @since 02.03.12 22:10
 */
namespace Gratheon\CMS;

class ContentModule extends \Gratheon\Core\Module{
    public function edit() {
        $this->assign('show_URL', true);
    }

    public function update() {
        $this->assign('show_URL', true);
    }

    public function insert($parentID) {
    }

    public function delete($parentID) {
    }

}
