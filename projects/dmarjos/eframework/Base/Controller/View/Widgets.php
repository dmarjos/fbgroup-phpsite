<?php
/**
 * @ignore
 */
class Base_Controller_View_Widgets {

	public $view;

	function init() {
		$this->view=new Base_Controller_View();
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
	 * class Invoices extends DB_Model {
	 *     function init() {
	 *         parent::init();
	 *     }
	 * }
	 * </code>
	 */
	function loadModel($name,$parameters=false) {
		$appPath=Base_Dispatcher::getApplicationPath();
		$modelPath=$appPath."/models";
		if (file_exists($modelPath."/".ucfirst($name).".php")) {
			require_once($modelPath."/".ucfirst($name).".php");
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

	function load($name) {
        $args = func_get_args();

		$appPath=Base_Dispatcher::getApplicationPath();
		if (strpos($name,"_")) {
			$wPath=str_replace("_","/",$name);
			$wDir=dirname($wPath)."/";
			$wName=basename($wPath);
		} else {
			$wDir="";
			$wName=$name;
		}
		
		if (file_exists(Base::getParameter("EFW_LIB_DIR").'/widgets/'.$wDir.$wName."Widget.php")	)
       		require_once Base::getParameter("EFW_LIB_DIR").'/widgets/'.$wDir.$wName."Widget.php";
		else
       		require_once $appPath.'/widgets/'.$wDir.$wName."Widget.php";
       		
        $name = basename($name)."Widget";
		//die($name);

        $widget = new $name();
        $widget->view =& $this->view;
		$widget->init();
        return call_user_func_array(array(&$widget, 'run'), array_slice($args, 1));
    }

    function render($widgetView, $show=true) {
		$wDir=Base_Registry::Get("EFW_WIDGETS_DIR");
		IF (!$wDir) {
			$wDir=Base_Registry::Get("EFW_VIEW_PATH")."/widgets";
		}
		$wDir=Base::getParameter("EFW_CURRENT_CONTROLLER")->_getViewPath()."/".$wDir;
		if (strpos($widgetView,"_")) {
			$wPath=str_replace("_","/",$widgetView);
			$wDir.="/".dirname($wPath);
			$wName=basename($wPath);
		} else {
			$wName=$widgetView;
		}
		if (file_exists(Base::getParameter("EFW_LIB_DIR").'/widgets/views/'.$wDir.$wName.".php"))
			$widgetFile=Base::getParameter("EFW_LIB_DIR").'/widgets/views/'.$wDir.$wName.".php";
		else 
			$widgetFile="$wDir/{$wName}.php";
		$view=$this->view->fetch($widgetFile);
		if ($show) echo $view;
		else return $view;

	}

}