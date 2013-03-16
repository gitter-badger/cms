<?php
/**
 * @author Artjom Kurapov
 * @since 02.06.12 21:28
 */
namespace Gratheon\CMS\Model;

class Diagnostics {

    public function getServerMemoryStatus() {
        $fh = fopen('/proc/meminfo', 'r');
        $free = $total = 0;
        while ($line = fgets($fh)) {
            if (strpos($line, 'MemTotal') !== false) {
                $total = explode(':', $line);
                $total = 1024 * (int)trim(str_replace('kB', '', $total[1]));
            }
            if (strpos($line, 'MemFree') !== false) {
                $free = explode(':', $line);
                $free = 1024 * (int)trim(str_replace('kB', '', $free[1]));
            }
        }

        fclose($fh);

        if($total==0) return array(0,0);
        return array(round(100 * $free / $total, 2),$total);
    }

    function getFreeSpace() {
        $free_space = disk_free_space("/");
        $total_space = disk_total_space("/");

        if($total_space==0) return array(0,0);

        return array(
            round(100 * $free_space / $total_space, 2),
            $total_space
        );
    }

    /*
         *
         *	PHP functions
         *
         */
    function functionIconvExists() {
        if (function_exists("iconv") && function_exists("iconv_get_encoding") && function_exists("iconv_set_encoding")) {
            return "<span class='green'>Available</span>";
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function functionMultiByteExists() {
        if (function_exists("mb_convert_encoding") && function_exists("mb_detect_encoding") && function_exists("mb_get_info")) {
            return ("<span class='green'>Available</span>");
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function functionMimeMagicExists() {
        if (function_exists("mime_content_type")) {
            $sCurrentMime = '';
            if ($sCurrentMime = mime_content_type(__FILE__)) {
                return ("<span class='green'>Available</span> - (" . $sCurrentMime . ")");
            }
            else {
                return "<span class='red'>Unvailable</span>";
            }
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function functionFileInfoExists() {
        if (function_exists("finfo_file") && function_exists("finfo_open") && defined("FILEINFO_MIME")) {
            $sCurrentMime = '';
            $rFileInfo = finfo_open(FILEINFO_MIME);
            if ($sCurrentMime = finfo_file($rFileInfo, __FILE__)) {
                return ("<span class='green'>Available</span> - (" . $sCurrentMime . ")");
            }
            else {
                return "<span class='orange'>Unvailable</span>";
            }
            finfo_close($rFileInfo);
        }
        else {
            return "<span class='orange'>Unvailable</span>";
        }
        return false;
    }

    function functionSystemExists() {
        if (function_exists("system") && function_exists("ob_start") && function_exists("ob_get_clean")) {
            $sSystemCall = 'file -i -b ' . __FILE__;
            $sCurrentMime = '';

            ob_start();
            @system($sSystemCall);
            $sCurrentMime = ob_get_clean();

            if ($sCurrentMime) {
                return ("<span class='green'>Available</span> - (" . $sCurrentMime . ")");
            }
            else {
                return "<span class='red'>Unvailable</span>";
            }
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function functionShellExecExists() {
        if (function_exists("shell_exec") && function_exists("escapeshellcmd")) {
            $sSystemCall = 'file -i -b ' . escapeshellcmd(__FILE__);
            $sCurrentMime = '';

            if (($sCurrentMime = @shell_exec($sSystemCall)) !== false) {
                return ("<span class='green'>Available</span> - (" . $sCurrentMime . ")");
            }
            else {
                return "<span class='red'>Unvailable</span>";
            }
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function functionExecImagemagicExists() {
        if (function_exists("exec") && function_exists("escapeshellarg")) {
            $sTestFile = 'test.jpg';
            $aOrigSizes = @getimagesize($sTestFile);
            $sSystemCall = $this->sImagemagicPath . ' -size ' . $aOrigSizes[0] . 'x' . $aOrigSizes[1] . ' ' . escapeshellarg($sTestFile) . ' -resize 100x100 ' . escapeshellarg('result_' . $sTestFile) . '';
            $aResults = array();
            $aSizes = array();

            if (file_exists('result_' . $sTestFile)) {
                if (!@unlink('result_' . $sTestFile)) {
                    $aResults[] = "<span class='red'>Kustutada ei saanud enne.</span>";
                }
            }

            if (!file_exists($sTestFile)) {
                $aResults[] = "<span class='red'>Test fail on puudu.</span>";
            }
            else {
                @exec($sSystemCall);
            }

            if (file_exists('result_' . $sTestFile)) {
                $aSizes = @getimagesize('result_' . $sTestFile);
                $aResults[] = "<span class='blue'>" . $aSizes[0] . " x " . $aSizes[1] . "</span>";

                if (!@unlink('result_' . $sTestFile)) {
                    $aResults[] = "<span class='red'>Kustutada ei saanud hiljem.</span>";
                }

                return ("<span class='green'>Available</span> - (" . implode(", ", $aResults) . ")");
            }
            else {
                return "<span class='red'>Unvailable</span> - (" . implode(", ", $aResults) . ")";
            }
        }
        else {
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }


    /*
         *
         *	PHP extentsions
         *
         */
    function extentsionMultiByteExists() {
        $sEXT = 'mbstring';
        if (extension_loaded($sEXT)) {
            return ("<span class='green'>Available</span>");
        }
        else {
            if (defined("PHP_SHLIB_SUFFIX")) {
                $sPrefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
                return $this->dlLocal($sPrefix . $sEXT . '.' . PHP_SHLIB_SUFFIX);
            }
            else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    return $this->dlLocal($sEXT . '.dll');
                }
                else {
                    return $this->dlLocal($sEXT . '.so');
                }
            }
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function extentsionGDExists() {
        $sEXT = 'gd';
        if (extension_loaded($sEXT)) {
            $sGDVersion = '';
            if (function_exists("gd_info")) {
                $sGDVersion = gd_info();
                $sGDVersion = $sGDVersion['GD Version'];
            }
            return ("<span class='green'>Available</span> - " . $sGDVersion);
        }
        else {
            if (defined("PHP_SHLIB_SUFFIX")) {
                $sPrefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
                return $this->dlLocal($sPrefix . $sEXT . '.' . PHP_SHLIB_SUFFIX);
            }
            else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    return $this->dlLocal($sEXT . '.dll');
                }
                else {
                    return $this->dlLocal($sEXT . '.so');
                }
            }
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function extentsionMySQLExists() {
        $sEXT = 'mysql';
        if (extension_loaded($sEXT)) {
            return ("<span class='green'>Available</span>");
        }
        else {
            if (defined("PHP_SHLIB_SUFFIX")) {
                $sPrefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
                return $this->dlLocal($sPrefix . $sEXT . '.' . PHP_SHLIB_SUFFIX);
            }
            else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    return $this->dlLocal($sEXT . '.dll');
                }
                else {
                    return $this->dlLocal($sEXT . '.so');
                }
            }
            return "<span class='red'>Unvailable</span>";
        }
        return false;
    }

    function extentsionFileInfoExists() {
        $sEXT = 'fileinfo';
        if (extension_loaded($sEXT)) {
            return ("<span class='green'>Available</span>");
        }
        else {
            if (defined("PHP_SHLIB_SUFFIX")) {
                $sPrefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
                return $this->dlLocal($sPrefix . $sEXT . '.' . PHP_SHLIB_SUFFIX);
            }
            else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    return $this->dlLocal($sEXT . '.dll');
                }
                else {
                    return $this->dlLocal($sEXT . '.so');
                }
            }
            return "<span class='orange'>Unvailable</span>";
        }
        return false;
    }


    /*
         *
         *	PHP checks
         *
         */
    function checkSafeMode() {
        if (function_exists("ini_get")) {
            if (ini_get('safe_mode')) {
                return "<span class='red'>ON</span>";
            }
            else {
                return "<span class='green'>OFF</span>";
            }
        }
        else {
            return "<span class='blue'>no info</span>";
        }
        return false;
    }

    function checkPostMaxSize() {
        if (function_exists("ini_get")) {
            return "<span class='blue'>" . $this->returnBytes(ini_get('post_max_size')) . "</span>";
        }
        else {
            return "<span class='blue'>No info</span>";
        }
        return false;
    }

    function checkMemoryLimit() {
        if (function_exists("ini_get")) {
            return "<span class='blue'>" . $this->returnBytes(ini_get('memory_limit')) . "</span>";
        }
        else {
            return "<span class='blue'>No info</span>";
        }
        return false;
    }

    function checkIncludePath() {
        if (function_exists("get_include_path")) {
            return "<span class='small'>" . @get_include_path() . "</span>";
        }
        else {
            return "<span class='small'>No info</span>";
        }
        return false;
    }


    function MySqlServerVersion() {
        if (function_exists("mysql_get_server_info")) {
            return mysql_get_server_info();
        }
        else {
            return "<span class='orange'>Can't check!</span>";
        }
        return false;
    }

    function MySqlServerConnection() {
        if (function_exists("mysql_connect") && function_exists("mysql_error") && function_exists("mysql_query") && function_exists("mysql_fetch_assoc")) {
            $rDB = @mysql_connect($this->sMySqlUser['host'], $this->sMySqlUser['username'], $this->sMySqlUser['password']);
            if (!$rDB) {
                return ("<span class='red'>Could not connect</span>" . " (" . @mysql_error() . ")");
            }
            $sQuery = @mysql_query("SELECT VERSION() AS mysql_version");
            $aQuery = @mysql_fetch_assoc($sQuery);
            if (isset($aQuery['mysql_version']) && $aQuery['mysql_version']) {
                return ("<span class='green'>Query OK (" . $aQuery['mysql_version'] . ")</span>");
            }
            else {
                return ("<span class='red'>Connection OK but Query failed!</span>");
            }
        }
        else {
            trigger_error("Test failed for \"<b>MySQL server connection</b>\"!", E_USER_ERROR);
        }
        return false;
    }

    function MySqlEngines() {
        $sReturn = '';
        if (function_exists("mysql_connect") && function_exists("mysql_error") && function_exists("mysql_query") && function_exists("mysql_fetch_assoc")) {
            $rDB = @mysql_connect($this->sMySqlUser['host'], $this->sMySqlUser['username'], $this->sMySqlUser['password']);
            if (!$rDB) {
                return ("<span class='red'>Could not connect</span>" . " (" . @mysql_error() . ")");
            }
            $sQuery = @mysql_query("SHOW ENGINES");
            while ($aQuery = @mysql_fetch_assoc($sQuery)) {
                if (isset($aQuery['Support']) && ($aQuery['Support'] == 'YES' || $aQuery['Support'] == 'DEFAULT')) {
                    $sReturn .= ("<span class='green'>Supported - " . $aQuery['Engine'] . ($aQuery['Support'] == 'DEFAULT' ? ' (DEFAULT)' : '') . "</span><br />");
                }
            }
            return $sReturn;
        }
        else {
            trigger_error("Test failed for \"<b>MySQL engines</b>\"!", E_USER_ERROR);
        }
        return false;
    }


    /*
            *
            *	PHP versions
            *
            */
    function definedPHPHostVars() {
        if (defined("PHP_VERSION") && defined("PHP_OS")) {
            if (@version_compare($this->sMinPHPVersion, PHP_VERSION, "<") >= 1) {
                return "<span class='green'>" . (PHP_VERSION . ' | ' . PHP_OS) . " (PHP " . $this->phpVerReal() . ")</span>";
            }
            else {
                return "<span class='red'>" . (PHP_VERSION . ' | ' . PHP_OS) . " (PHP " . $this->phpVerReal() . ")</span>";
            }
        }
        else {
            trigger_error("Test failed for \"<b>PHP version full</b>\", defined <b>PHP_VERSION and PHP_OS</b> sys variables not found!", E_USER_ERROR);
        }
        return false;
    }

    function phpVerReal() {
        if (function_exists("phpversion")) {
            $v = phpversion();
            $version = array();

            foreach (explode('.', $v) AS $bit) {
                if (is_numeric($bit)) {
                    $version[] = $bit;
                }
            }

            return (implode('.', $version));
        }
        else {
            trigger_error("Test failed for \"<b>PHP version</b>\", function <b>phpversion</b> does not exists!", E_USER_ERROR);
        }
        return false;
    }


    /*
            *
            *	Helper functions
            *
            */
    function returnBytes($iBytes) {
        $iBytes = trim($iBytes);
        $sLast = strtolower($iBytes{strlen($iBytes) - 1});
        switch ($iLast) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $iBytes *= 1024;
            case 'm':
                $iBytes *= 1024;
            case 'k':
                $iBytes *= 1024;
        }

        return $iBytes;
    }

    function dlLocal($extensionFile) {
        // make sure that we are ABLE to load libraries
        if (!(bool)ini_get("enable_dl") || (bool)ini_get("safe_mode")) {
            return "<span class='orange'>Unvailable, extensions loading Unvailable</span>";
        }

        // check to make sure the file exists
        if (!file_exists($extensionFile)) {
            return "<span class='orange'>Unvailable, {$extensionFile} does not exist</span>";
        }

        // check the file permissions
        if (!is_executable($extensionFile)) {
            return "<span class='orange'>Unvailable, {$extensionFile} is not executable</span>";
        }

        // we figure out the path
        $currentDir = getcwd() . "/";
        $currentExtPath = ini_get("extension_dir");
        $subDirs = preg_match_all("/\//", $currentExtPath, $matches);
        unset($matches);

        //lets make sure we extracted a valid extension path
        if (!(bool)$subDirs) {
            return "<span class='orange'>Unvailable, could not determine a valid extension path</span>";
        }

        $extPathLastChar = strlen($currentExtPath) - 1;

        if ($extPathLastChar == strrpos($currentExtPath, "/")) {
            $subDirs--;
        }

        $backDirStr = "";
        for ($i = 1; $i <= $subDirs; $i++) {
            $backDirStr .= "..";
            if ($i != $subDirs) {
                $backDirStr .= "/";
            }
        }

        // construct the final path to load
        $finalExtPath = $backDirStr . $currentDir . $extensionFile;

        // now we execute dl() to actually load the module
        if (!dl($finalExtPath)) {
            trigger_error("Test failed while loading module!", E_USER_ERROR);
        }

        // if the module was loaded correctly, we must bow grab the module name
        $loadedExtensions = get_loaded_extensions();
        $thisExtName = $loadedExtensions[sizeof($loadedExtensions) - 1];

        // lastly, we return the extension name
        return ("<span class='green'>Available</span>");
    }

}