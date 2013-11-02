<?php
namespace Gratheon\CMS;
use Gratheon\CMS;

/**
 * Tree generator, high query usage
 *
 * @author Artjom Kurapov <artkurapov@gmail.com>
 */

class Tree extends \Gratheon\Core\Model
{
    public $table;

    public $arrselected = array();
    public $arrlevels = array();
    public $flatTree;
    public $strSelect = '*';
    public $strWhere = '';
    public $strFrom = '';
    public $strOrder = 't1.position';

    public $arrType = array('', 'article', 'comment', 'file', 'gallery', 'image');

    static $arrParentIDCache = array(); //max 1000 elements
    static $arrParentCache = array(); //max 1000 elements

    public function __construct($table = 'content_menu')
    {
        $this->table = $table;
        parent::__construct($table);
    }

    /**
     * Tree initialization. Creates tree with defined MaxLevel depth.
     * Caches result in session to minimize database usage
     *
     * @param int $MaxLevel
     * @param bool $useCache
     *
     */
    public function initialize($MaxLevel = 1, $useCache = false)
    {
        parent::__construct($this->table);
        if ($useCache) {
            if (isset($_SESSION['content_menu']) && is_array($_SESSION['content_menu']))
                $this->flatTree = $_SESSION['content_menu'];
            else {
                $this->flatTree = $this->build(1, $MaxLevel, 1, array(), array());
                $_SESSION['content_menu'] = $this->flatTree;
            }
        }
        else {
            $this->flatTree = $this->build(1, $MaxLevel, 1);
        }
    }

    /**
     * Construct linear menu array
     *
     * @param int $intParentID
     * @param int $intMaxLevel
     * @param int $intLevel
     *
     * @internal param \of $array maximum positions for root levels $arrSubPosMax
     *
     * @internal param \of $array positions of parent roots relevant to their level positions $arrSubPosSel
     * @return mixed array
     */
    public function build($intParentID, $intMaxLevel = 0, $intLevel = 0)
    {
        $arrTree = array();
        $arrLevel = $this->buildLevel($intParentID);

        if (is_array($arrLevel))
            foreach ($arrLevel as $node) {
                $node->level = $intLevel;
                $arrTree[] = $node;

                if ($intMaxLevel > $node->level)
                    $arrTree = array_merge($arrTree, $this->build($node->ID, $intMaxLevel, $node->level + 1));
            }
        return $arrTree;
    }

    /**
     * Construct recursive array
     *
     * @param null $arrTree
     * @param array $arrSelected
     * @param int $intParentID
     * @param int $intMaxLevel
     * @param int $intLevel
     * @return array|null
     */
    public function buildTree(&$arrTree = null, $arrSelected = array(1), $intParentID = 0, $intMaxLevel = 0, $intLevel = 0)
    {
        if (!isset($arrTree)) {
            $arrTree = array();
        }

        $arrLevel = $this->buildLevel($intParentID);
        if (is_array($arrLevel))
            foreach ($arrLevel as $node) {
                $node->level = $intLevel;
                //$node->link = sys_url . '/content/main/content/edit/' . $node->ID;
                //$node->front_link = $menu->getPageURL($node->ID);

                $node->subnodes = array();
                $arrTree[] = $node;
                if (!$arrSelected || in_array($node->ID, $arrSelected)) {
                    $this->buildTree($node->subnodes, $arrSelected, $node->ID, $intMaxLevel, $node->level + 1);
                }

            }
        return $arrTree;
    }


    public function buildSelected($selectedID)
    {
        $selected_tmp = $selectedID;
        while (1 <> 0)
        {
            $arrSelected[] = $selected_tmp;
            $selected_tmp = $this->getParentID($selected_tmp);
            if ($selected_tmp == 0)
                break;
        }
        return array_reverse($arrSelected);
    }

    public function buildLevel($parentID)
    {
        $ret = $this->arr(
			$this->strWhere . " t1.parentID='$parentID'
			ORDER BY " . $this->strOrder,
            $this->strSelect, $this->table . ' t1 ' . $this->strFrom);

        $limits = $this->obj("parentID='$parentID'", "MAX(position) as mx, MIN(position) as mn");
        if (is_array($ret))
            foreach ($ret as $key => $val)
            {
                $ret[$key]->max = $limits->mx;
                $ret[$key]->min = $limits->mn;
                $arrChildren = $this->arr("parentID='" . $ret[$key]->ID . "' ORDER BY position", 'ID');

                $ret[$key]->children = array();
                foreach ($arrChildren as $item)
                    $ret[$key]->children[] = $item->ID;

            }
        return $ret;
    }

    public function buildLevels($arrselected)
    {
        $arrlevels = array();
        foreach ($arrselected as $ID){
            if(isset(Tree::$arrParentIDCache[$ID]) && Tree::$arrParentIDCache[$ID]){
                $recParent = new \stdClass();
                $recParent->title = Tree::$arrParentIDCache[$ID]->title;
                $recParent->ID = Tree::$arrParentIDCache[$ID]->ID;

                $arrlevels[] = $recParent;
            }
            else{
                $arrlevels[] = (array)$this->obj("ID='$ID'", "title,ID");
            }
        }
        return $arrlevels;
    }

    //@returns linear array of all children IDs
    public function getLinearChildIDs($parentID, &$arrParentIDs)
    {
        $arrIDs = $this->arrint("parentID=$parentID", 'ID');
        foreach ($arrIDs as $id) {
            $arrParentIDs[] = $id;
            $this->getLinearChildIDs($id, $arrParentIDs);
        }
        return $arrIDs;
    }


    public function getParentID($selectedID)
    {
        if((int)$selectedID==1){
            return null;
        }

        if (!isset(Tree::$arrParentIDCache[$selectedID])) {
            $intParentID = $this->int("ID='$selectedID'", "parentID");
            if (isset(Tree::$arrParentIDCache[$selectedID]) && count(Tree::$arrParentIDCache[$selectedID]) < 1000) {
                Tree::$arrParentIDCache[$selectedID] = $intParentID;
            }
        }
        else {
            $intParentID = Tree::$arrParentIDCache[$selectedID];
        }

        return $intParentID;
    }


    public function getParent($selectedID)
    {
        if (!isset(Tree::$arrParentCache[$selectedID])) {
            $recParent = $this->obj("ID='$selectedID'");
            Tree::$arrParentIDCache[$selectedID] = $recParent->parentID;

            if (count(Tree::$arrParentCache[$selectedID]) < 50) {
                Tree::$arrParentCache[$selectedID] = $recParent;
            }
        }
        else {
            $recParent = Tree::$arrParentCache[$selectedID];
        }

        return $recParent;
    }

}