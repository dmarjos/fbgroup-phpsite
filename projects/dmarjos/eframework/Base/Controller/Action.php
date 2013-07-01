<?
/**
 * @package Base
 * @subpackage Controller
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Base_Controller_Action class  is the parent of all controllers, and defines how your controller should be run.
 *
 * As you saw in Base::Dispatcher , all controllers MUST extend this class. However, you can (and should, by the way) subclass this, and use your subclass to <i>extend</i> your controller classes. Let's see how:
 *
 * As with any application, you probably will have some code that is common to all your controllers. Is certainly a bad approach to copy and paste this common code in all your files, for obvious reasons (maintenance, for example). So, how EFW can help you with this?
 *
 * <code>
 * class Resources_Classes_SuperController extends Base_Controller_Action {
 *         function init() {
 *                 parent::init(); // call Base_Controller_Action init() method
 *         }
 *         function preDispatch() {
 *                 parent::PreDispatch(); // call Base_Controller_Action preDispatch() method
 *         }
 * }
 * </code>
 * You can see the class name: <b>Resources_Classes_SuperController</b>. We'll discuss something about this a bit later. For now, we'll say that this code must be in a file named DOCROOT/Resources/Classes/SuperController.php
 *
 * Now, you can use this newly created class to superclass your controllers:
 * <code>
 * class IndexController extends Resources_Classes_SuperController {
 *    .......
 *    .......
 *    .......
 * }
 * </code>
 *
 * If you look closely, you'll note the capitalized words in the Supercontroller class name. This capitalizing <b>MUST</b> match the actual capitalizing of the path and file names components in your filesystem. As said before, EFW will look for the file defining this "super controller" in DOCROOT/Resources/Classes/SuperController.php, but you can put it anywhere in your webserver public html folder, as long as you rename the class wherever is used. 
 * 
 * For example: 
 * If you decide call this "superclass", let's say simply "SuperController", you MUST save it as DOCROOT/SuperController.php.
 * If you decide call it "common_classes_SuperController", you MUST save it as DOCROOT/common/classes/SuperController.php.
 *
 * The run chain of a controller is as follows:
 *
 * $controller->init(); // Performs a global initialization of a controller<br/>
 * $controller->preDispatch(); // Does something before dispatching the action<br/>
 * $controller->ACTION([parameters list]); // Executes the proper action<br/>
 * $controller->postDispatch(); // Does something after the action has been dispatched<br/>
 *
 * You can prevent the chain from continuing by returning a boolean FALSE value.<br/>
 * E.g.<br/>
 * If init() returns false, the chain ends there. <br/>
 * If preDispatch() returns false, ACTION and postDispatch won't be executed.
 * and so on...
 *
 * Beware of returning a <b>false</b> value (not 0, empty string or empty array). If the returning value IS NOT a <b>BOOLEAN FALSE</b> value, the chain will continue to execute
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @see Base_Dispatcher
 */
class Base_Controller_Action {

	/**
	* @private
	*/
    private $Parameters=array();
    private $_CONTROLLER_NAME="";
    private $_ACTION="";
    private $_VIEW_PATH="";
    private $_CONTROLLER_PATH="";

    public $scripts=array();
    public $styles=array();

    public $pageTitle='';
    public $meta=array();

    /**
	 * This method initializes the controller and sets the View manager.
	 *
	 * You can override this method in a Base_Controller_Action subclass to do something when the controller 
	 * is initialized. Please, be sure you call this method. 
	 *
	 * <code>
	 * class Resources_Classes_SuperController extends Base_Controller_Action {
	 *         function init() {
	 *				   #
	 *				   # Your code here
	 *				   #
	 *                 parent::init(); // call Base_Controller_Action init() method
	 *				   #
	 *				   # Your code here too...
	 *				   #
	 *         }
	 * }
	 * </code>
	 */
    function init() {
		$this->view=new Base_Controller_View();
    }

	/**
	 * @ignore
	 */
    function setParameters($params) {
		if (is_array($params))
			$this->Parameters=$params;
    }

	/**
	 * @ignore
	 */
    function getParameters() {
		return $this->Parameters;
    }

	/**
	 * @ignore
	 */
    function _setController($controller) {
		$this->_CONTROLLER_NAME=$controller;
    }
    
	/**
	 * @ignore
	 */
    function _setControllerPath($controllerPath) {
		$this->_CONTROLLER_PATH=$controllerPath;
    }
    
	/**
	 * @ignore
	 */
    function _setViewPath($viewPath) {
		$this->_VIEW_PATH=$viewPath;
    }
    
	/**
	 * @ignore
	 */
    function _setAction($action) {
		$this->_ACTION=$action;
    }

	/**
	 * This function returns the currently running controller's name
	 * Altough if intended for internal use only, you can use it wherever you need it
	 */
    function _getController() {
		return strtolower($this->_CONTROLLER_NAME);
    }
    
	/**
	 * This function returns the currently running controller's physical path
	 * Altough if intended for internal use only, you can use it wherever you need it
	 */
    function _getControllerPath() {
		return $this->_CONTROLLER_PATH;
    }
    
	/**
	 * This function returns the current views physical path
	 * Altough if intended for internal use only, you can use it wherever you need it
	 */
    function _getViewPath() {
		return $this->_VIEW_PATH;
    }
    
	/**
	 * This function returns the currently running action name
	 * Altough if intended for internal use only, you can use it wherever you need it
	 */
    function _getAction() {
		return strtolower($this->_ACTION);
    }

	/**
	 * @ignore
	 */
    function getParam($name) {
    
		if (isset($this->Parameters[$name]))
			return $this->Parameters[$name];
		
		return NULL;
    
    }

	/**
	 * @ignore
	 */
    function setParam($name,$value) {
    
		$this->Parameters[$name]=$value;
		return NULL;
    
    }

    /**
	 * This function loads a model into the object hierarchy.
	 * 
	 * Models are a key piece in any MVC framework. It allows you to put your data storage related code completely separated from your processing code. A good example of this is database related code.
	 *
	 * When you load a model, its name is accessible as a property in your controller. Let' see an example:
	 *
	 * Suppose you want to manage all your "invoices" related methods in a logical way. You should create a model:
	 * <code>
	 * class InvoicesModel extends DB_Model {
	 *     function init() {
	 *         parent::init();
	 *     }
	 *
	 *     function generateInvoice() {
	 *			......
	 *			......
	 *     }
	 *
	 * }
	 * </code>
	 *
	 * Models has to be in the application/models folder. In the example above, the file <b>MUST</b> be named as application/models/InvoicesModel.php
	 *
	 * In the method for your action, you can do this:
	 *
	 * <code>
	 * class Users extends Base_Controller_Action {
	 *		function invoice() {
	 *			$this->loadModel("invoices");
	 *			$this->Invoices->generateInvoice();
	 *		}
	 * }
	 * </code>
	 *
	 * Please, notice that the model name will be lowercase with the first letter in capital, as in the above example
	 *
	 * @param string $name The name of the file which contains the class. That file <b>MUST</b> define a class with the same name plus the Model suffix (InvoicesModel in the example above), or an exception will be thrown. Also, the file name must contain the "Model" suffix.
	 * @param mixed  $parameters Parameters to be sent to the model.
	 */
	function loadModel($name,$parameters=false) {
		$appPath=Base_Dispatcher::getApplicationPath();
		$modelPath=$appPath."/models";
		if (file_exists($modelPath."/".ucfirst(strtolower($name)).".php")) {
			require_once($modelPath."/".ucfirst(strtolower($name)).".php");
			if (!class_exists($name."Model")) 
				throw new Base_Controller_Action_Exceptions_ModelNotDefined("Model $nameModel is not defined within $name.php");
				
			$modelName="{$name}Model";
			if ($parameters)
				$this->{$name}=new $modelName($parameters);
			else
				$this->{$name}=new $modelName();
		} else
			throw new Base_Controller_Action_Exceptions_ModelDoesNotExists("Model name is invalid");
	}

	/**
	 * This function loads a library into the object hierarchy.
	 * 
	 * Libraries are not part of the MVC paradigm, but allows you to have some code encapsulated into a class,
	 * for example a image handling class, or a captcha generating class, or a class you've got from the phpClasses.org (TM) site
	 *
	 * When you load a library, its name is accessible as a property in your controller. Let' see an example:
	 *
	 * Libraries has to be in the application/libraries folder.
	 *
	 * Suppose you have a class which creates a graphical chart:
	 *
	 * <code>
	 * class MyGraphCharts {
	 * 		......
	 * 		......
	 *		function draw() { ... };
	 * }
	 * </code>
	 *
	 * In the method for your action, you can do this:
	 *
	 * <code>
	 * class Charts extends Base_Controller_Action {
	 *		function pie() {
	 *			$this->loadLibrary("MyGraphCharts");
	 *			$this->Mygraphcharts->draw();
	 *		}
	 * }
	 * </code>
	 *
	 * Please, notice that the library name will be lowercase with the first letter in capital, as in the above example
	 *
	 * @param string $name The name of the file which contains the class. That file <b>MUST</b> define a class with the same name, or an exception will be thrown.
	 */
	function loadLibrary($name) {
		$appPath=Base_Dispatcher::getApplicationPath();
		$libraryPath=$appPath."/libraries";
		if (file_exists($libraryPath."/".ucfirst(strtolower($name)).".php")) {
			require_once($libraryPath."/".ucfirst(strtolower($name)).".php");
			if (!class_exists($name)) 
				throw new Base_Controller_Action_Exceptions_LibraryNotDefined("Library $name is not defined within $name.php");
				
			$libraryName="{$name}";
			$this->{$name}=new $libraryName();
		} else
			throw new Base_Controller_Action_Exceptions_LibraryDoesNotExists("Library name is invalid");
	}

	/**
	 * This method is executed before dispatching the corresponding ACTION.
	 * 
	 * Its purpose is to allow executing code prior to the action itself. For example, if overriden in a
	 * Base_Controller_Action subclass (which would be the preferred place to do that), you can check if
	 * the user has logged in, before allowing him/her to proceed
	 *
	 * The default behavior is to do nothing
	 */
	public function preDispatch() {
	}
	
	/**
	 * This method is executed after dispatching the corresponding ACTION.
	 * 
	 * Its purpose is to allow executing code after to the action itself. 
	 *
	 * The default behavior is to execute the corresponding View (what will be sent to the browser)
	 * 
	 * You can override this method in a Base_Controller_Action subclass to replace its functionality
	 * or to add something else, before or after executing the View. Please, be sure you call this method
	 * if you are extending its functionality. 
	 *
	 * If you are replacing it, no need to call this one.
	 *
	 * <code>
	 * class Resources_Classes_SuperController extends Base_Controller_Action {
	 *         function postDispatch() {
	 *				   #
	 *				   # Your code here
	 *				   #
	 *                 parent::postDispatch(); // call Base_Controller_Action postDispatch() method
	 *				   #
	 *				   # Your code here too...
	 *				   #
	 *         }
	 * }
	 * </code>
	 */
	
	public function postDispatch() {
		$viewPath=$this->_getViewPath();
		$controllerPath=$this->_getControllerPath();
		$controllerName=str_replace("controller","",$this->_getController());
		if (!empty($controllerPath)) $controllerPath="/$controllerPath/"; else $controllerPath="/";
		$action=str_replace("action","",$this->_getAction());
		if (empty($this->tplFile))
			$this->tplFile="$viewPath$controllerPath$action.php";
		$view=$this->view->fetch($_SERVER["DOCUMENT_ROOT"]."/".$this->tplFile);
		$html="";
		if (!$_GET["portlet"]) {
			$html.="<html>\n";
			$html.="<head>\n";
			if ($this->pageTitle) $html.="<title>".$this->pageTitle."</title>\n";
			foreach($this->meta as $meta) $html.='<meta '.$meta["type"].'="'.$meta["name"].'" content="'.$meta["content"].'" />'."\n";
			
			foreach($this->scripts as $script) $html.='<script type="text/javascript" src="'.$script.'"></script>'."\n";
			foreach($this->styles as $style) $html.='<link type="text/css" rel="stylesheet" href="'.$style.'" />'."\n";
			$html.="</head>\n";
			$html.="<body>\n";
		}
		$html.=$view;
		if (!$_GET["portlet"]) {
			$html.="</body>\n";
			$html.="</html>\n";
		}		
		echo $html;        
	}

	/**
	* @ignore
	*/
	public function errorAction() {
		dump_var($this);
	}

	/**
	* This function sends a Location: header to the given url.
	*
	* @parameter string $url The url to redirect to.
	*/
    public function _redirect($url) {
        $redirect_prefix=Base::getParameter("redirect_prefix");
        if ($redirect_prefix) $url=$redirect_prefix.$url;
		header("location: ".$url);
		die();
    }

	/**
	* This function forwards the execution to a given controller->action. It's similar to Base_Dispatcher::doDispatch().<br>
	* Please, be aware that this method will not redirect your browser to a new location. It will only transfer the execution flow to a new controller/action
	* @see Base_Dispatcher::doDispatch()
	*
	* @param string $controller The controller to called
	* @param string $action The action to be called
	* @param array  $params An associative array with parameters to be passed to the controller->action.
	*/
    public function _forward($controller,$action,$params=NULL) {
		Base_Dispatcher::doDispatch($controller,$action,$params);
		die();
    }

    public function addScript($path) {
    	$this->scripts[$path]=$path;
    }

    public function addStyle($path) {
    	$this->styles[$path]=$path;
    }

    public function addMeta($type, $name,$content) {
    	$this->meta[]=array("type"=>$type,"name"=>$name,"content"=>$content);
    }

}
