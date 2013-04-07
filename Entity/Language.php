<?php
/**
 * @author Artjom Kurapov
 * @since 07.04.13 15:04
 */

namespace Gratheon\CMS\Entity;
class Language{
	/** @var string */ public $ID;
	/** @var string */ public $native;
	/** @var string */ public $english;
	/** @var string */ public $native_spell;
	/** @var string */ public $native_spellin;
	/** @var boolean */ public $is_default;
}