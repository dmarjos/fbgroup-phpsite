<?
/**
 * @package Base
 * @subpackage Dispatcher
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Base_Dispatcher_Routes class allows to create routing rules to call custom controlller/actions based on the request URI. It's pretty much as RewriteRules, with a few differences. For example, you can use this to have dynamic routing rules, which are not allowed in a .htaccess file.
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 */
class Base_Dispatcher_Routes {

	/**
	 * @ignore
	 */
    public static $Routes=array();
    
	/**
	 * This method sets a routing rule.
	 *
	 * A routing rule looks like a RewriteRule, but instead of working with paths, it works with controller->action(parameters)
	 *
	 * As you saw before in Base_Dispatcher, a request URI is converted into a controller->action call:
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
	 *
	 * But some times, you will need to manage the convertion in a different way. For example, you could need to have a variable component in the request URI, between the controller and the action parts. Let's see an example:
	 *
	 * <code>
	 * http://yourdomain/projects/eframework/statistics
	 * http://yourdomain/projects/webtracker/statistics
	 * </code>
	 *
	 * I'll assume you have all your projects names in an array:
	 * <code>
	 * $projects=array(
	 *     "eframework",
	 *     "webtracker",
	 *     "project3",
	 *     "project4"
	 * );
	 * </code>
	 * 
	 * You just need to do this in your configuration script (normally index.php), <b>BEFORE</b> the call to Base_Dispatcher::Dispatch():
	 *
	 * <code>
	 * Base_Dispatcher_Routes::setRouteRule("/project/[projects]/statistics",array("controller"=>"Project_Plugins","action"=>"statistics"),array("projects"=>$projects));
	 * </code>
	 *
	 * @param string $expression This is what you expect to receive in the request URI (see the example urls above)
	 * @param array $mvc an associative array with 2 elements: "controller" which contains the controller's name (see above) and "action" which holds the action name, that is the controller->action to be executed if the rule is met.
	 * @param mixed $params An associative arrays which elements are the variable names that are used in the expression
	 */
    public static function setRouteRule($expression,$mvc,$params=NULL) {
		$routes=self::$Routes;
		$routes[]=array($expression,$mvc,$params);
		self::$Routes=$routes;
    
    }

	/**
	 * @ignore
	 */
    public static function uriMatchRule() {
    
    	$urlPrefix=Base::getParameter("EFW_URL_PREFIX");
    	if ($urlPrefix) {
    		$_SERVER["REQUEST_URI"]=str_replace($urlPrefix,"",$_SERVER["REQUEST_URI"]);
    	}
		$retVal=false;

		foreach(self::$Routes as $idx => $routeDef) {
			$expression=$routeDef[0];
			if (substr($expression,-1)!="/") $expression.="/";
			if (substr($_SERVER["REQUEST_URI"],-1)!="/") $_SERVER["REQUEST_URI"].="/";
			$URI=$_SERVER["REQUEST_URI"];
			$URI=str_replace("?".$_SERVER["QUERY_STRING"],"",$URI);
			//echo "Probando $expression contra $URI<br/>";
			$params=$routeDef[2];
			$variables=self::getVariables($expression);
			$mvc=$routeDef[1];
			foreach($variables as $idx => $var) {
				$expression=str_replace("[".$var."]","(.*)",$expression);
			}
			if (preg_match_all("'$expression'si",$URI,$matches,PREG_SET_ORDER)) {
				$routeMatch=true;
				$_params=array();
				foreach($variables as $idx => $var) {
					$position=$idx+1;
					if (is_null($params))
						$routeMatch=false;
					elseif (is_array($params)) {
						if (!in_array($matches[0][$position],$params[$var])) {
							$routeMatch=false;
							break;
						}
						$_params[$var]=$matches[0][$position];
					} else
						$_params[$var]=$matches[0][$position];
				}
				$mvc["parameters"]=$_params;
		
				if ($routeMatch) {
					//dump_var($mvc);
					return $mvc;
				}
			}
		}
		return false;
    }
    
	/**
	 * @ignore
	 */
    private static function getVariables($expression) {
		$vars=array();
		$idx=0;
		$stPos=strpos($expression,"[");
		while ($stPos!==false) {
			$stPos++;
			$enPos=strpos($expression,"]",$stPos);
			if ($enPos===false)
				break;
			$enPos--;
			$variable=substr($expression,$stPos,($enPos-$stPos)+1);
			$vars[]=$variable;
			$stPos=strpos($expression,"[",$enPos);
		}
	
		return $vars;    
    }

}
