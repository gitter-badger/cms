<?php
namespace Gratheon\CMS\Test\Unit;

/**
 * Class ArticleTest
 *
 * @package Gratheon\CMS\Test\Unit
 * @property \Gratheon\CMS\Menu $object
 */
class MenuTest extends \PHPUnit_Framework_TestCase{
	protected $object;

    public function setUp(){
//        //$this->getMock('Model', array('q','__construct','Model'),array(),'',false,false,false);
//        $this->getMock('\Gratheon\Core\Model');
        $this->getMock('\Gratheon\CMS\Tree');
//
        require_once sys_test_root.'../Menu.php';
        $this->object = new \Gratheon\CMS\Menu();
    }

    /**
     * @test
     */
    public function buildTree(){
        $r = $this->object->buildTree();
		$this->assertEquals(array(), $r);
    }
}