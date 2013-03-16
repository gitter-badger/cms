<?php

/**
 * @property content_slide $object
 */
class slide_test extends PHPUnit_Framework_TestCase{
    public function setUp(){
        //$this->getMock('Model', array('q','__construct','Model'),array(),'',false,false,false);
        $this->getMock('\Gratheon\Core\Record');
        $this->getMock('\Gratheon\Core\Model');

        require_once '../Model/slide.php';
        $this->object = new \Gratheon\CMS\Model\Slide(false);
    }


	/**
	 * @test
	 */
	public function parseSpeakerDeck(){
		$code = $this->object->parseCode('speakerdeck','<script async class="speakerdeck-embed" data-id="4ec103887cdd0500510047c8" data-ratio="1.3333333333333333" src="//speakerdeck.com/assets/embed.js"></script>');

		$this->assertEquals('4ec103887cdd0500510047c8', $code);
	}

	/**
	 * @test
	 */
	public function getTitleSpeakerDeck(){
		$title = $this->object->getSlideTitle('speakerdeck','4ec103887cdd0500510047c8');

		$this->assertEquals('Google + PHP = Zend Framework ?', $title);
	}

	/**
	 * @test
	 */
	public function getSlideTitleFromCode_slideshare(){
		$title = $this->object->getSlideTitleFromCode(
			'slideshare',
			'<iframe src="http://www.slideshare.net/slideshow/embed_code/10973558" width="427" height="356" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC;border-width:1px 1px 0;margin-bottom:5px" allowfullscreen> </iframe> <div style="margin-bottom:5px"> <strong> <a href="http://www.slideshare.net/antonkeks/2-basics" title="Java Course 2: Basics" target="_blank">Java Course 2: Basics</a> </strong> from <strong><a href="http://www.slideshare.net/antonkeks" target="_blank">Anton Keks</a></strong> </div>'
		);

		$this->assertEquals('Java Course 2: Basics', $title);
	}

}