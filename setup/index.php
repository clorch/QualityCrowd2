<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DS', DIRECTORY_SEPARATOR);
define('DSX', preg_quote(DS));

$fileMode = 0660;
$dirMode = 0770;

// initialize variables for the error messages through the setup process
$err = '';
$msg = array();

/*
 * Determine paths and URLs
 */
$rootPath = preg_replace('#setup' . DSX . 'index.php$#', '', __FILE__);
$baseURL = preg_replace('#setup/index.php$#', '', $_SERVER['PHP_SELF']);

/*
 * check if setup is disabled
 */
if (file_exists($rootPath.DS.'setup'.DS.'disabled.php')) {
	$err = "setup disabled";
	goto webpage;
}

/*
 * check PHP config
 */
if (ini_get('short_open_tag') == '' && version_compare(PHP_VERSION, '5.4.0', '<')) {
	$err = "enable 'short_open_tag' in your PHP configuration or update to PHP 5.4.0 or later";
	goto webpage;
}
$msg[] = "checked PHP configuration";

/*
 * check support for mod_rewrite
 */

if (function_exists('apache_get_modules')) {
	$modules = apache_get_modules();
	$mod_rewrite = in_array('mod_rewrite', $modules);
	if (!$mod_rewrite)
	{
		$err = "mod_rewrite not available";
		goto webpage;
	}
	$msg[] = "checked mod_rewrite support";
}

/*
 * check if vendor directory has been created 
 */
if (!file_exists($rootPath.DS.'vendor')) {
	$err = "vendor directory missing. run <code>composer install</code>";
	goto webpage;
}

/* 
 * create directories
 */
$dirs = array(
	$rootPath . 'data',
	$rootPath . 'media',
	$rootPath . 'core' . DS . 'tmp',
	$rootPath . 'core' . DS . 'tmp' . DS . 'batch-cache',
	$rootPath . 'core' . DS . 'tmp' . DS . 'img-cache',
	$rootPath . 'core' . DS . 'tmp' . DS . 'browscap',
);

foreach($dirs as $dir) {
	if (!file_exists($dir)) {
		$parent = dirname($dir);
		if (!is_writable($parent)) {
			$err = "'$parent' is not writable";
			goto webpage;
		}

		mkdir($dir, $dirMode);
		$msg[] = "created $dir";
	}

	if (!is_writable($dir)) {
		$err = "'$dir' is not writable";
		goto webpage;
	}
}

/*
 * install example batches
 */
if (!file_exists($rootPath . 'batches')) {
	rcopy($rootPath . 'setup' . DS . 'example-batches', $rootPath . 'batches');
	$msg[] = "installed example batches";
}

/*
 * setup main .htaccess file
 */

if (!file_exists($rootPath . '.htaccess')) {
	$htaccess = file_get_contents($rootPath.'setup'.DS.'main.htaccess');
	$htaccess = str_replace('##BASEURL##', $baseURL, $htaccess);
	file_put_contents($rootPath . '.htaccess', $htaccess);
	$msg[] = "written $rootPath.htaccess";
}

/*
 * setup other .htaccess files
 */
file_put_contents($rootPath.'data'.DS.'.htaccess', "Require all denied\n");
$msg[] = "written {$rootPath}data".DS.".htaccess";

file_put_contents($rootPath.'batches'.DS.'.htaccess', "Require all denied\n");
$msg[] = "written {$rootPath}batches".DS.'.htaccess';

file_put_contents($rootPath.'core'.DS.'tmp'.DS.'.htaccess', "Require all denied\n");
$msg[] = "written {$rootPath}core".DS.'tmp'.DS.'.htaccess';

file_put_contents($rootPath.'core'.DS.'tmp'.DS.'img-cache'.DS.'.htaccess', "Require all granted\n");
$msg[] = "written {$rootPath}core".DS.'tmp'.DS.'img-cache'.DS.'.htaccess';

/* 
 * fix permissions
 */
$dirs = array(
	$rootPath . 'batches',
	$rootPath . 'data',
	$rootPath . 'media',
	$rootPath . 'core' . DS . 'tmp',
);
foreach($dirs as $dir) {
	if (rchmod($dir, $fileMode, $dirMode)) {
		$msg[] = "fixed permssions for $dir";
	} else {
		$err = "error fixing permssions for $dir";
		goto webpage;
	}
}

/*
 * disable setup script
 */
$dir = $rootPath.'setup'.DS;
if (!is_writable($dir)) {
	$err = "'$dir' is not writable";
	goto webpage;
}
if (file_put_contents($dir.'disabled.php', "<?php\n// to reenable the setup script delete this file")) {
	chmod($dir.'disabled.php', $fileMode);
	$msg[] = "disabled setup script";
}


function rcopy($path, $dest, $dmode = 0750, $fmode = 0640)
{
	$ignore = ['.', '..', '.DS_Store'];

    if(is_dir($path)) {
        @mkdir($dest);
        @chmod($dest, $dmode);
        $objects = scandir($path);
        if(sizeof($objects) > 0) {
            foreach($objects as $file) {
                if(in_array($file, $ignore)) continue;
                if(is_dir($path.DS.$file)) {
                    rcopy($path.DS.$file, $dest.DS.$file, $dmode, $fmode);
                } else {
                    copy($path.DS.$file, $dest.DS.$file);
                    @chmod($dest.DS.$file, $fmode);
                }
            }
        }
        return true;
    } 

    if(is_file($path)) {
        $r = copy($path, $dest);
        @chmod($dest.DS.$file, $fmode);
        return $r;
    } 

    return false;
}

function rchmod($path, $filemode, $dirmode) 
{ 
	$ignore = ['.', '..', '.DS_Store'];

    if (!is_dir($path)) {
        return chmod($path, $filemode); 
    }

    $dh = opendir($path); 
    while (($file = readdir($dh)) !== false) 
    { 
    	if(in_array($file, $ignore)) continue;
       
        $fullpath = $path.DS.$file; 
        if(is_link($fullpath)) {
            return false; 
        }
        if(!is_dir($fullpath) && !@chmod($fullpath, $filemode)) {
            return false; 
        }
        if(!rchmod($fullpath, $filemode, $dirmode)) {
            return false;
        }
    }
    closedir($dh);

    return chmod($path, $dirmode);
}

/*
 * display webpage
 */

webpage:

$returnPage = (isset($_GET['r']) ? '/' . $_GET['r'] : '');

?>
<!doctype html>
<html>
	<head>
		<title>QualityCrowd 2 - Setup</title>

		<link rel="stylesheet" href="<?= $baseURL ?>core/files/css/style.css" />
	</head>
	<body>
		<div class="header">
			<h1>QualityCrowd</h1>
		</div>
		
		<h2>Setup</h2>

		<?php if (count($msg)):?>
		<h3>Done</h3>
		<?php endif; ?>
		<ul>
			<?php foreach($msg as $m): ?>
			<li><?= $m ?></li>
			<?php endforeach; ?>
		</ul>

		<?php if ($err == ''): ?>
		<p>Setup complete</p>
		<p>
			<a href="<?= $baseURL ?>admin<?= $returnPage ?>">
				<?= ($returnPage == '' ? 'Admin Panel' : 'Return') ?>
			</a>
		</p>

		<?php else: ?>
		<h3>Error</h3>
		<ul class="errormessage">
			<li><?= $err ?></li>
		</ul>
		<p><a href="<?= $baseURL ?>setup/index.php">Retry</a></p>
		<?php endif; ?>
		<div class="footer">
		</div>
	</body>
</html>
