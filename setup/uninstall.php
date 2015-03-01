<?php
// ONLY FOR DEVELOPMENT!

// to use the uninstaller uncomment the following line
// CAUTION: this uninstaller deletes all batches, media files and results
die('Forbidden');

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DS', DIRECTORY_SEPARATOR);
define('DSX', preg_quote(DS));

$rootPath = preg_replace('#setup' . DSX . 'uninstall.php$#', '', __FILE__);

rrmdir($rootPath . 'batches');
rrmdir($rootPath . 'data');
rrmdir($rootPath . 'media');
rrmdir($rootPath . 'core' . DS . 'tmp');
rrmdir($rootPath . 'vendor');
unlink($rootPath . '.htaccess');

echo "Done";


function rrmdir($dir) 
{   
    if (!is_dir($dir)) return;

    $objects = scandir($dir);
    if(sizeof($objects) > 0) {
        foreach ($objects as $file) {
            if ($file == "." || $file == "..") continue;
            
            if (is_dir($dir.DS.$file)) {
                rrmdir($dir.DS.$file);
            } else {   
                @chmod($dir.DS.$file, 0777);
                unlink($dir.DS.$file);
            }
        }
    }
    
    @chmod($dir, 0777);
    rmdir($dir);
}
