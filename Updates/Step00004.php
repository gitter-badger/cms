<?php
namespace Gratheon\CMS\Updates;

class Step00004 extends \Gratheon\CMS\Sync{
	var $description='News module basic structure';
	
	function process(){
		if(!$this->existsTable('content_news')){
			$this->ask("
				CREATE TABLE `content_news` (                             
		                `ID` int(11) NOT NULL auto_increment,                   
		                `categoryID` int(11) default NULL,                      
		                `userID` int(11) default '1',                           
		                `date_added` datetime default NULL,                     
		                `date_open_from` datetime default NULL,                 
		                `date_open_to` datetime default NULL,                   
		                PRIMARY KEY  (`ID`)                                     
		              ) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8  
		              ");
		}
		
		if(!$this->existsTable('content_news_body')){
			$this->ask("
				CREATE TABLE `content_news_body` (                                               
                     `newsID` int(11) default NULL,                                                 
                     `langID` int(11) default NULL,                                                 
                     `title` varchar(250) character set utf8 collate utf8_unicode_ci default NULL,  
                     `content` text character set utf8 collate utf8_unicode_ci,                     
                     `content_index` text character set utf8 collate utf8_unicode_ci                
                   ) ENGINE=MyISAM DEFAULT CHARSET=utf8 
		              ");
		}
		
		if(!$this->existsTable('content_news_category')){
			$this->ask("
				CREATE TABLE `content_news_category` (                                     
                         `ID` int(11) NOT NULL auto_increment,                                    
                         `parentID` int(11) default NULL,                                         
                         `deepness` enum('entire_tree','only_children') default 'only_children',  
                         `orderby` enum('date_added','title','position') default 'position',      
                         `elements_per_page` mediumint(9) default '5',                            
                         PRIMARY KEY  (`ID`),                                                     
                         UNIQUE KEY `parentID` (`parentID`)                                       
                       ) ENGINE=MyISAM DEFAULT CHARSET=utf8  
		              ");
		}
		
		if(!$this->existsTable('content_news_images')){
			$this->ask("
				CREATE TABLE `content_news_images` (    
                       `image_id` int(11) default NULL,      
                       `news_id` int(11) default NULL        
                     ) ENGINE=MyISAM DEFAULT CHARSET=utf8  
		              ");
		}
		if(!$this->existsTable('content_news_twitter')){
			$this->ask("
				CREATE TABLE `content_news_twitter` (   
                        `newsID` int(11) default NULL,        
                        `twitterID` bigint(20) default NULL   
                      ) ENGINE=MyISAM DEFAULT CHARSET=utf8  
		              ");
		}
		return $this->bUpdateSuccess;
	}
}

