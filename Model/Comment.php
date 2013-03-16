<?php
/**
 * @author Artjom Kurapov
 * @since 04.09.11 13:35
 */
namespace Gratheon\CMS\Model;

class Comment extends \Gratheon\Core\Model {
    private static $instance;

    /**
     * @return Comment
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    final function __construct() {
        parent::__construct('content_comment');
    }

    public function replaceSmilies($str) {
        $arrSourcePattern = array(' :D ', ' :) ', ' :| ', ' :( ', ' :O ', ' :P ');
        $arrResultPattern = array(
            ' <img src="' . sys_url . 'app/front/img/smile/35.png" alt=":D" /> ',
            ' <img src="' . sys_url . 'app/front/img/smile/36.png" alt=":)" /> ',
            ' <img src="' . sys_url . 'app/front/img/smile/37.png" alt=":|" /> ',
            ' <img src="' . sys_url . 'app/front/img/smile/38.png" alt=":(" /> ',
            ' <img src="' . sys_url . 'app/front/img/smile/39.png" alt=":O" /> ',
            ' <img src="' . sys_url . 'app/front/img/smile/40.png" alt=":P" /> ');
        return str_replace($arrSourcePattern, $arrResultPattern, $str);
    }

    public function formatHTML($str) {
        //$str=nl2br($str);
        $codeBlocks = explode('<code>', $str);

        $out = '';
        foreach ($codeBlocks as $i => $block) {
            if (!$i) {
                $out .= nl2br($block);
                continue;
            }

            $out .='<code>';
            $chunk = explode('</code>', $block);
            if (count($chunk) > 1) {
                $out .= $chunk[0].'</code>'.nl2br($chunk[1]);
            }
            else{
                $out .= nl2br($chunk[0]);
            }

        }

        return $out;
    }
}


class content_comment_record extends \Gratheon\Core\Record {
    /** @var int */
    public $parentID;
    /** @var string */
    public $author;
    /** @var string */
    public $url;
    /** @var string */
    public $show_url;
    /** @var string */
    public $content;
    /** @var string */
    public $date_added;
}
