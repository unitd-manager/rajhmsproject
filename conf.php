<?
$config['cp.TimeZone'] = 'Asia/Kolkata';
date_default_timezone_set($config['cp.TimeZone']);

define('CP_HOST', $_SERVER['HTTP_HOST']);
$docRoot = $_SERVER['DOCUMENT_ROOT'];

//================================================================//
if (CP_HOST == "rajhms.cubosale.in" || CP_HOST == "rajhmscopy.cubosale.in") {
    define('CP_ENV', 'production');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');
} else if (CP_HOST == "habibiyahms.smartprosoft.com") {
    define('CP_ENV', 'testing');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');

} else if (CP_HOST == "rajhms.testpilotweb3.com:92") {
    define('CP_ENV', 'development');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');

} else if (CP_HOST == "rajhms.localhost") {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $rootFolder = substr($docRoot, 0, stripos($docRoot, '/rajhms/'));

    define('CP_ENV', 'local');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');
}

define('CP_PATH', CP_CORE_PATH . 'CP/');
define('CP_PATH2', CP_CORE_PATH . 'CP2/');
//================================================================//
require_once(CP_PATH . 'common/lib/inc_path.php');

/*** Local Server **/
$config['local'] = array(
     'db' => array(
          'host'     => 'localhost'
         ,'username' => 'root'
         ,'password' => $_SERVER['dbPassword']
         ,'dbname'   => 'rajhms'
     )
    ,'display_errors' => true
);

/*** Development Server **/
$config['development'] = $config['local'];
$config['development']['db']['username'] = 'rajhms';
$config['development']['db']['password'] = 'h1a2b3i4b5i6y';
$config['development']['display_errors'] = false;


/*** Testing Server **/
$config['testing'] = $config['development'];
$config['testing']['db']['dbname']   = 'rajhms';
$config['testing']['db']['username'] = 'rajhms';
$config['testing']['db']['password'] = 'm3U6T6GC4SK79K';
$config['testing']['display_errors'] = false;

/*** Production Server **/
$config['production'] = $config['testing'];
$config['production']['db']['dbname']   = 'rajhms';
$config['production']['db']['username'] = 'rajhms';
$config['production']['db']['password'] = '88PhuRqGgvOoZTg';
$config['production']['display_errors'] = false;

//================================================================//
require_once(CP_PATH . 'common/lib/Registry.php');
$cfgCommon = require_once(CP_PATH . 'common/lib/config.php');
$cfgCommon2 = require_once(CP_PATH2 . 'common/lib/config.php');
$cfgMast = require_once($cfgCommon['cp.masterPath'] . 'lib/config.php');
$cfgMast2   = require_once(CP_LIB_PATH2 . 'config.php');
$cfgLoc  = require_once($cfgCommon['cp.localPath'] . 'lib/config.php');

$cpCfg = array_merge($config, $cfgCommon, $cfgCommon2, $cfgMast, $cfgMast2, $cfgLoc);
Zend_Registry::set('cpCfg',$cpCfg);
//================================================================//
