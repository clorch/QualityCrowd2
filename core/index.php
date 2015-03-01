<?php
require('../vendor/autoload.php');

// DS is the directory separator
define('DS', DIRECTORY_SEPARATOR);
// DSX is the escaped DS for the use in a PCRE
define('DSX', preg_quote(DS));

// determine root path
$rootPath = preg_replace('#core'.DSX.'index.php$#', '', __FILE__);

// read config file
$cf = require($rootPath.'core'.DS.'config.php');

// setup PHP error handling
if ($cf['debug']) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}

// set timezone
date_default_timezone_set($cf['timezone']);

// setup path constants
define('ROOT_PATH', $rootPath);
define('BATCH_PATH', $rootPath . 'batches' . DS);
define('DATA_PATH', $rootPath . 'data' . DS);
define('MEDIA_PATH', $rootPath . 'media' . DS);
define('LIB_PATH', $rootPath . 'core' . DS . 'lib' . DS);
define('TMP_PATH', $rootPath . 'core' . DS . 'tmp' . DS);
define('TEMPLATE_PATH', $rootPath . 'core' . DS . 'template' . DS);

// setup url constants
$baseURL = 'http://' . $_SERVER['HTTP_HOST'];
$baseURL .= preg_replace('#core/index.php$#', '', $_SERVER['PHP_SELF']);

define('BASE_URL', $baseURL);
define('MEDIA_URL', $baseURL . 'media/');

// process HTTP request
$req = new Clho\QualityCrowd\Request();
$req->process();
