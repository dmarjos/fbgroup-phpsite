<?
/**
 * Loader
 * 
 * This file is the initialization script for eFramework
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @package Scripts
 */

/**
 * @ignore
 */
define('BASEPATH',$_SERVER["DOCUMENT_ROOT"].'/');


/**
 * This function dumps a variable (any var type can be given)
 * 
 * @param mixed $var The variable to dump
 * @param bool $die Optional: If provided, the script will end after dumping
 * @return void
 */
function dump_var($var,$die=true) {
    
    echo "<pre>";
    var_dump($var);
    if ($die) die();

}

/**
 * Helper function for seting routing rules. 
 * Routing rules are much RewriteRules like, but with a propietary syntax. See Base_Dispatcher_Routes for explanation
 * 
 * @param string $expression The expression for wich the rule will be set
 * @param array $mvc The MVC parameters for the rule if URI matches it.
 * @param array $params an array with parameters to pass on 
 * @return void
 */
function setRouteRule($expression,$mvc,$params=NULL) {

    Base_Dispatcher_Routes::setRouteRule($expression,$mvc,$params);    
}


/**
 * 
 * @ignore
 */
function __autoload($class) {

    $classPath=str_replace("_","/",$class).".php";

    if (file_exists(dirname(__FILE__)."/".$classPath)) {
	require_once(dirname(__FILE__)."/".$classPath);
	if (!class_exists($class,false)) {
	    trigger_error("$classPath exists but $class is not defined",E_USER_ERROR);
	}
//	$obj=new $class();
	return true;
    } elseif (file_exists($classPath)) {
	require_once($classPath);
	if (!class_exists($class,false)) {
	    trigger_error("$classPath exists but $class is not defined",E_USER_ERROR);
	}
//	$obj=new $class();
	return true;
    } elseif (file_exists("application/controllers/$classPath")) {
	require_once("application/controllers/$classPath");
	if (!class_exists($class,false)) {
	    trigger_error("$classPath exists but $class is not defined",E_USER_ERROR);
	}
//	$obj=new $class();
	return true;
    } elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/$classPath")) {
	require_once($_SERVER["DOCUMENT_ROOT"]."/$classPath");
	if (!class_exists($class,false)) {
	    trigger_error("$classPath exists but $class is not defined",E_USER_ERROR);
	}
//	$obj=new $class();
	return true;
    }
    throw new Base_Loader_ClassNotFoundException($class."(".$classPath.".php) does not exists!!!");
//    return true;
    die($class."(".$classPath.".php) does not exists!!!");
}

/**
 * 
 * @ignore
 */
function exception_handler($exception) {
    die($exception->getMessage());
    dump_var($exception);
}
  
/**
 * 
 * @ignore
 */
function error_handler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

	case E_WARNING:
		echo "<b>Warning</b> $errstr on line $errline in file $errfile<br />";
    default:
//        echo "Unknown error type: [$errno] $errstr on line $errline in file $errfile<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}


//set_exception_handler("exception_handler");
//set_error_handler("error_handler");

$URI=$_SERVER["REQUEST_URI"];
$DOCROOT=$_SERVER["DOCUMENT_ROOT"];
$SCRIPT_DIR=dirname($_SERVER["SCRIPT_FILENAME"]);

$SCRIPT_DIR=str_replace("$DOCROOT","",$SCRIPT_DIR);
$URI=substr($URI,strlen($SCRIPT_DIR));

//dump_var($_SERVER);
Base::setParameter("EFW_LIB_DIR",dirname(__FILE__));
$serverName=$_SERVER["SERVER_NAME"];
if (substr($serverName,0,4)=="www.") $serverName=substr($serverName,4);
if (is_dir("system/configs/$serverName") && file_exists("system/configs/$serverName/config.php")) {
	/**
	 * @ignore
	 */
	require_once("system/configs/$serverName/config.php");
} elseif (is_dir("application/configs/$serverName") && file_exists("application/configs/$serverName/config.php")) {
	/**
	 * @ignore
	 */
	require_once("application/configs/$serverName/config.php");
} elseif (is_dir("application/configs") && file_exists("application/configs/config.php")) {
	/**
	 * @ignore
	 */
	require_once("application/configs/config.php");
}