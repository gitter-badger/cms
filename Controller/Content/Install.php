<?php
namespace Gratheon\Cms\Controller\Content;

class Install extends \Gratheon\Core\Controller {

	public function log($result){
		echo "<div>".$result."</div>";
	}

    function updates() {
        $sys_update = $this->model('sys_update');

        if (!ini_get('safe_mode')) {
            set_time_limit(300);
        }

		$pathToUpdates = sys_root . '/vendor/Gratheon/CMS/Updates/';

        $dir = dir($pathToUpdates);

		echo "<div style='background-color:white'>";

        $tmpSync = new \Gratheon\CMS\Sync();
        if (!$tmpSync->existsTable('sys_update')) {

            $tmpSync->q("CREATE TABLE `sys_update` (
              `ID` int(11) NOT NULL,                   
              `execution_time` datetime default NULL,  
              PRIMARY KEY  (`ID`)                      
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1   ");
        }


        //List files in images directory
        while (($file = $dir->read()) !== false) {
            if (is_file($pathToUpdates . $file)) {
                $sID = str_replace(array('.php','Step'), '', $file);
                $ID = (int)$sID;
                $exExecution = $sys_update->int($ID);
                if (!$exExecution) {
                    $this->log('Executing update #' . $ID);
                    //require_once($pathToUpdates . $file);
                    $strUpdateName = "\\Gratheon\\CMS\\Updates\\Step" . $sID;

                    $objUpdate = new $strUpdateName('');
                    $bSuccess = $objUpdate->process();

                    if ($objUpdate->bReloadNeeded) {
						$this->log("update #$ID requested window reload");
                        echo "<script>window.location.reload();</script>";
                        exit();
                    }

                    if ($bSuccess) {
                        $sys_update->insert(array("ID" => $ID, "execution_time" => "NOW()"));
                    }
                    else {
                        if ($objUpdate->bStopsUpgradeOnFailure) {
							$this->log("update #$ID failed critically, upgrade process halted");
							$dir->close();
							echo '</div>';
							exit();
                        }
                        else {
							$this->log("update #$ID failed");
                        }
                    }
                }
            }
        }

		echo '</div>';
        $dir->close();
    }
}