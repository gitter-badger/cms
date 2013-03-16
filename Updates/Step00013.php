<?php
namespace Gratheon\CMS\Updates;

class Step00013 extends \Gratheon\CMS\Sync{
    var $description='News tagging';

    function process(){
        /*
        if(!$this->existsTable('news_tags')){
            $this->ask("CREATE TABLE `content_news_tags` (
                                `tagID` int(11) NOT NULL default '0',
                                `newsID` int(11) NOT NULL default '0',
                                UNIQUE KEY `newsID` (`tagID`,`newsID`)
                              ) ENGINE=MyISAM DEFAULT CHARSET=latin1");

        }
*/
        $content_news_body = new \Gratheon\Core\Model('content_news_body');
        $sys_tags = new \Gratheon\Core\Model('sys_tags');
        $content_news_tags = new \Gratheon\Core\Model('content_news_tags');

        $arrNews = $content_news_body->q("
            SELECT t1.*
            FROM content_news_body t1
            INNER JOIN content_news_external t2 ON t2.newsID=t1.newsID
            WHERE t2.serviceName='twitter'
            ORDER BY t1.newsID DESC
            LIMIT 1000");

        foreach($arrNews as $arrItem){
            preg_match_all("/[^A-Z0-9_@]?@([A-Za-z0-9_]*)[ ,\.]?/i",$arrItem->content,$arrNameMatches);

            preg_match_all("/[^A-Z0-9_]?#([A-Za-z0-9_]*)[ ,\,]?/i",$arrItem->content,$arrTags);



            foreach($arrTags[1] as $strTag){
                $intExSysTag=$sys_tags->int("title='".$strTag."'",'ID');
                if (!$intExSysTag){
                    $recTag->title=$strTag;
                    $recTag->pop=1;
                    $intExSysTag=$sys_tags->insert($recTag);
                }

                $content_news_tags->q("INSERT IGNORE INTO content_news_tags SET tagID='$intExSysTag', newsID='{$arrItem->newsID}'");
            }

            #Update tag count info
            $sys_tags->q("UPDATE sys_tags
                            SET pop=(
                                SELECT COUNT(ID)
                                FROM content_news_tags as t2
                                WHERE tagID=sys_tags.ID
                            ) + (SELECT COUNT(tagID) FROM content_tags t3 WHERE tagID=sys_tags.ID)");

            $sys_tags->q("DELETE FROM sys_tags WHERE pop=0");


        }
        return $this->bUpdateSuccess;
    }
}