<?php
/**
 * @package Base
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Base_Dispatcher class does all the dirty work for MVC applications. It tooks the request URI entered at the URL, and convert it into a call to a<br/>Controller->Action
 *
 * This is how URIs are managed and converted to proper controller->action calls:
 *
 * <b>In this examples we'll asume that your application files resides in [DOCUMENT_ROOT]/application</b>
 *
 * <pre>
 * http://yourdomain/somepath
 *        You must have a controllers/SomepathController.php file, which <b><i>MUST</i></b> define a class 
 *        named SomepathController extending Base_Controller_Action, and an <b>indexAction</b> method within the class
 * http://yourdomain/somepath/something
 *        You must have a controllers/SomepathController.php file, which <b><i>MUST</i></b> define a class 
 *        named SomepathController extending Base_Controller_Action, and an <b>somethingAction</b> method within the class.
 *        Note how your "something" will be suffixed with "Action", althought the "Action" word MUST NOT be in the URI.
 *        (e.g. http://yourdomain/somepath/somethingaction will look for a "somethingactionAction" method in the controller class)
 * http://yourdomain/somepath_somesubpath
 *        You must have a controllers/Somepath/SomesubpathController.php file, which <b><i>MUST</i></b> define a class 
 *        named Somepath_SomesubpathController extending Base_Controller_Action, and an <b>indexAction</b> method within the class
 *        <i>Note that an underscore ('_') in the controller name will be treated as a path separator, allowing you to organize your 
 *        controllers in folders and subfolders. There is no limitations on depth, besides your Operating System ones.</i>
 * http://yourdomain/somepath/something/parameter1/parameter2/parameterN
 *        Anything following the controller/action part of the URI, will be passed as method parameters, 
 *        and is <i>your responsability</i> to define those parameters in your method definition. Beware of PHP syntax regarding
 *        method or function parameters. Let's see this simple example:
 * </pre>
 * <code>
 * class Mycontroller extends Base_Controller_Action {
 *       function mymethodAction($parameter1,$parameter2) {
 *           ......
 *           ......
 *           ......
 *       }
 * }
 * </code>
 * <pre>
 *        To call the "mymethod" action you should go to http://yourdomain/mycontroller/mymethod/somedata1/somedata2
 *        if you ommit the 3rd and 4th part of the URI (somedata1 and somedata2), you're likely to get a 
 *        "Missing argument X for ...." PHP warning. You can avoid this warning defining your expected parameters
 *        as optional (just add a =false in the parameter list - e.g. <i>function mymethodAction($parameter1=false,$parameter2=false)</i>)
 * </pre>
 *
 * @package Base
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 */
class Base_Dispatcher {

	/**
	 * This method converts the given URL into a controller->action call.
	 * You must call this method for your application to run.
	 *
	 * <code>
	 * ini_set("include_path",ini_get("include_path").PATH_SEPARATOR.$_SERVER["DOCUMENT_ROOT"]."/library/efw");
	 * require_once("Loader.php");
	 * Base_Dispatcher::Dispatch();
	 * </code>
	 * @return void
	 */
	
    public static function dispatch() {
    
		$data=Base_Dispatcher_Routes::uriMatchRule();
	
		if (!$data) {	
			$URI=$_SERVER["REQUEST_URI"];
			if (preg_match_all("'/([a-z0-9_]*)/([a-z0-9_]*)/(.*)'si",$URI,$matches,PREG_SET_ORDER)) {
				$_CONTROLLER_PATH=str_replace("_","/",$matches[0][1]);
				$_ACTION=str_replace("_","",$matches[0][2]);
				$_PARAMS=$matches[0][3];
			} elseif (preg_match_all("'/([a-z0-9_]*)/([a-z0-9_]*)'si",$URI,$matches,PREG_SET_ORDER)) {
				$_CONTROLLER_PATH=str_replace("_","/",$matches[0][1]);
				$_ACTION=str_replace("_","",$matches[0][2]);
				$_PARAMS=NULL;
			} elseif (preg_match_all("'/([a-z0-9_]*)'si",$URI,$matches,PREG_SET_ORDER)) {
				$_CONTROLLER_PATH=str_replace("_","/",$matches[0][1]);
				$_ACTION="index";
				$_PARAMS=NULL;
			} else {
				$_CONTROLLER_PATH="index";
				$_ACTION="index";
				$_PARAMS=NULL;
			}
		} else {
			$_CONTROLLER_PATH=str_replace("_","/",$data["controller"]);
			$_ACTION=$data["action"];
			$_PARAMS=$data["parameters"];
		}
	
		self::doDispatch($_CONTROLLER_PATH,$_ACTION,$_PARAMS);
    }

	/**
	 * This method does the dirty work.
	 * Altough this method is called internally, you can use it when managing exceptions, to force a specific controller->action to be run
	 *
	 * <code>
	 * ini_set("include_path",ini_get("include_path").PATH_SEPARATOR.$_SERVER["DOCUMENT_ROOT"]."/library/efw");
	 * require_once("Loader.php");
	 * try {
	 * 	   Base_Dispatcher::Dispatch();
	 * } catch (Base_Dispatcher_NoActionException $eNA) {
	 *     Base_Dispatcher::doDispatch("index","error",array("severity"=>fatal,"message"=>"Requested action does not exists!"));
	 * }
	 * </code>
	 * @return void
	 */
	
    public static function doDispatch($_CONTROLLER_PATH,$_ACTION,$_PARAMS) {

		$_CONTROLLER_PATH=str_replace("_","/",$_CONTROLLER_PATH);
		$_cArray=explode("/",$_CONTROLLER_PATH);
		for($i=0; $i < count($_cArray); $i++)
			$_cArray[$i]=ucwords(strtolower($_cArray[$i]));
			
		$_CONTROLLER_PATH=implode("/",$_cArray);
	
		$_CONTROLLER_FILE=basename($_CONTROLLER_PATH);
		if (empty($_CONTROLLER_FILE)) $_CONTROLLER_FILE="index";
		$_CONTROLLER_PATH=dirname($_CONTROLLER_PATH);
		
		if ($_CONTROLLER_PATH==".") $_CONTROLLER_PATH="";
		if (substr($_CONTROLLER_PATH,0,1)=="/") 
			$_CONTROLLER_PATH=substr($_CONTROLLER_PATH,1);
		
		
		if (!empty($_CONTROLLER_PATH)) 
			if (substr($_CONTROLLER_PATH,-1)!="/") $_CONTROLLER_PATH.="/";
			
		$applicationPath=self::getApplicationPath();
			
		if (!file_exists("$applicationPath/controllers/".$_CONTROLLER_PATH.ucwords(strtolower($_CONTROLLER_FILE))."Controller.php")) 
			throw new Base_Dispatcher_NoControllerException("Controller $applicationPath/controllers/".$_CONTROLLER_PATH.ucwords(strtolower($_CONTROLLER_FILE))."Controller.php doesn't exists!");
		$className=str_replace("/","_",$_CONTROLLER_PATH.$_CONTROLLER_FILE);
		$className.="Controller";
		require_once("$applicationPath/controllers/".$_CONTROLLER_PATH.ucwords(strtolower($_CONTROLLER_FILE))."Controller.php");
		if (!class_exists($className,false))
			throw new Base_Dispatcher_FileDoesNotContainsClass("File ".$_CONTROLLER_PATH.ucwords(strtolower($_CONTROLLER_FILE))."Controller.php  exists, but it doesn't defines $className!");
	
		$class=new $className();
		
		if (!is_a($class,'Base_Controller_Action'))
			throw new Base_Dispatcher_InvalidParentException("Class $className is not an instance of Base_Controller_Action!");
			
			
		if (empty($_ACTION)) $_ACTION="index";
		
		$_ACTION.="Action";
	
		$class->setParameters($_PARAMS);    
		$class->_setController($className);
	
		$viewPath=Base_Registry::Get("EFW_VIEW_PATH");
		if (empty($viewPath) || is_null($viewPath) || !is_dir($viewPath)) $viewPath="$applicationPath/views";
		//dump_var($_CONTROLLER_PATH);
		$class->_setViewPath($viewPath);
		$class->_setControllerPath(strtolower($_CONTROLLER_PATH.$_CONTROLLER_FILE));
		$class->_setAction($_ACTION);
	
		$class->actionParameters=array();
		if (is_string($_PARAMS)) {
			if (substr($_PARAMS,-1)=="/") $_PARAMS=substr($_PARAMS,0,-1);
			$class->actionParameters=explode("/",$_PARAMS);
		} elseif (is_array($_PARAMS))
			$class->actionParameters=$_PARAMS;
			
		if (!method_exists($class,$_ACTION) && !method_exists($class,"__call")) {
			Base_Registry::Set("EFW_ERROR",array("class"=>$className,"action"=>$_ACTION));
			throw new Base_Dispatcher_NoActionException("$_ACTION is not defined within $className!");
		}
		
		Base::setParameter("EFW_CURRENT_CONTROLLER", $class);
		if (method_exists($class,"init")) {
			$retVal=$class->init();
		}
		
		if (method_exists($class,"predispatch") && ($retVal!==false)) {
			$retVal=$class->preDispatch();
		}
	
		if ($retVal!==false) {
			$paramCount=count($class->actionParameters);
	
			eval('$retVal=$class->$_ACTION('."'".implode("','",$class->actionParameters)."'".');');		
		}		
		
		if (method_exists($class,"postdispatch") && ($retVal!==false)) {
			$retVal=$class->postDispatch();
		}
		
	
    }

	/**
	 * This method returns the current application path. It only purpose is to be used internaly, but you can use it wherever you need to
	 * 
	 * @return string 
	 */
	public static function getApplicationPath() {
		$appPath=Base_Registry::Get("EFW_APPLICATION_PATH");
		if (!$appPath || is_null($appPath) || empty($appPath)) {
			$appPath="application";
			self::setApplicationPath($appPath);
		}
		return $appPath;
	}
	
	/**
	 * This method sets the current application path. If you put your MVC files in any location other than DOCROOT/application, you should set the propper path using this method
	 * This allows you to manage multiple applications with only one EFW installation in your DOCROOT
	 *
	 * E.g.:
	 * <code>
	 * if (eregi("^/surveys",$_SERVER["REQUEST_URI"]))
	 *     Base_Dispatcher::setApplicationPath("surveys_applications");
	 * elseif (eregi("^/ecommerce",$_SERVER["REQUEST_URI"]))
	 *     Base_Dispatcher::setApplicationPath("ecommerce_applications");
	 * </code>
	 * 
	 * Obviously, you MUST provide a valid path, relative to your DOCROOT
	 *
	 * @param string $appPath The path your application resides on
	 * @return void
	 */
	public static function setApplicationPath($appPath) {
		Base_Registry::Set("EFW_APPLICATION_PATH",$appPath);
	}
	
	
}
