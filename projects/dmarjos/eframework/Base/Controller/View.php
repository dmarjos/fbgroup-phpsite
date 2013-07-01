<?
/**
 * @package Base
 * @subpackage Controller
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the standard Views manager for the framework<br>
 *
 * Views are html files, that can (and mostly will) include variables to be shown. The view is parsed as a PHP file, so you can use PHP syntax to show your variables, and also you can use PHP code to control cycles, such as foreach, while, etc.
 * 
 * It has several internal commands:
 *
 * <code>
 * {include file="file_to_be_included" dir="path_to_file"}
 * </code>
 *
 * "file_to_be_included" <b>MUST</b> have its extension.
 * "path_to_file" <b>MUST</b> be a valid path 
 * E.g.: {include file="header.php" dir="
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 */
class Base_Controller_View {

	/**
	 * @ignore
	 */
	public $_viewVars=array();
	/**
	 * @ignore
	 */
	public $template_dir="";
	
	/**
	 * @ignore
	 */
	function __construct() {
	}
	
	/**
	 * Use this method to assign a value to a view's variable
	 * @param string $var The variable to be assigned
	 * @param mixed $val The value
	 */
	function assign($var,$val) {
		$this->_viewVars[$var]=$val;
	}

	/**
	 * Use this method to get the value for a view's variable
	 * @param string $var The variable to be retrieved
	 */
	function get($var) {
		return $this->_viewVars[$var];
	}

	/**
	 * @ignore
	 */
	function __ParseParameters($theParamStr) {

		$params=array();
		if (preg_match_all('/([A-Za-z_0-9]*)="([A-Za-z0-9_\s\+\-\*\/\.;,\(\)\{\}\[\]=><!\'&|]*)"/s',$theParamStr,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$theParamValue=$match[2];
				if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1);
				$params[strtoupper($match[1])]=str_replace('"','',$theParamValue);
			}
		} elseif (preg_match_all('/([A-Za-z_0-9]*)=\'([A-Za-z0-9_\+\-\*\/\.\{\}\(\)\[\]=><!;,]*)\'/s',$theParamStr,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$theParamValue=$match[2];
				if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1);
				$params[strtoupper($match[1])]=str_replace('"','',$theParamValue);
			}
		}
		return $params;
	}

	/**
	 * @ignore
	 */
	function processTags(&$content) {
        if(preg_match_all("/(<TPL:([a-z]*)(\s+)([^>]*)(\s*)>)(.*?)(<\/TPL:\\2>)/Usi",$content,$matches,PREG_SET_ORDER)) { 
            foreach($matches as $match) { 
                $tplCommand="bt_".$match[2]; 
                $tplCmdParams=$this->__ParseParameters($match[4]); 
                $tplBlock=$match[6]; 
                $retVal=$tplBlock; 
                if (method_exists($this,$tplCommand)) {
                    $retVal=$this->{$tplCommand}($tplCmdParams,$tplBlock,$vars); 
                } 
                $content=str_replace($match[0],$retVal,$content); 
            } 
        } 
		preg_match_all("/(<TPL:([a-z]*)(\s+)([^>]*)([\s]*)([\/]*)>)/si",$content,$matches,PREG_SET_ORDER);
		if($matches) { 
            foreach($matches as $match) { 
                $tplCommand="tg_".$match[2]; 
                $tplCmdParams=$this->__ParseParameters($match[4]); 
//                if (substr($tplCmdParams,-1)=="/") $tplCmdParams=substr($tplCmdParams,0,-1); 
//                $retVal=$this->{$tplCommand}($tplCmdParams,$vars,$caller); 
				$retVal="";
                if (method_exists($this,$tplCommand)) 
                    $retVal=$this->{$tplCommand}($tplCmdParams);

                $content=str_replace($match[0],$retVal,$content); 
            } 
        } 
		/*		
		if (preg_match_all("'{(INCLUDE)(\s+)([^}]*?)([\s]*)}'si",$content,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$tplCommand=$match[1];
				$tplCmdParams=$this->__ParseParameters($match[3]);
//				dump_var($tplCmdParams);
				if($tplCmdParams["FILE"]) {
					$path=$tplCmdParams["FILE"];
					if ($tplCmdParams["DIR"]) {
						$dir=$tplCmdParams["DIR"];
						if (substr($dir,-1)!="/") $dir.="/";
						$path=$_SERVER["DOCUMENT_ROOT"]."/".$dir.$path;
					} else {
						$dir=$this->template_dir;
						if (substr($dir,-1)!="/") $dir.="/";
						$path=$dir.$path;
					}
					$content=str_replace($match[0],"<?php include(\"$path\");?>",$content);
				}
			}
		}
		if (preg_match_all("'{(WIDGET)(\s+)([^}]*?)([\s]*)}'si",$content,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$tplCommand=$match[1];
				$tplCmdParams=$this->__ParseParameters($match[3]);
				if($tplCmdParams["NAME"]) {
					$widget =& new Base_Controller_View_Widgets();
					$widget->init();
					$params=array();
					foreach ($tplCmdParams as $attr => $value) {
						if ($attr!="NAME") $params[$attr]=$value;
					};
					$widget->load($tplCmdParams["NAME"],$params);
					$widgetResult=$widget->render($tplCmdParams["NAME"],false);
				}
				$content=str_replace($match[0],$widgetResult,$content);
			}
		}
		*/
	}
	
	/**
	 * This method fetchs and returns the processed view.
	 * 
	 * @param string $path the path where the view file resides.
	 * @return string the parsed html to be sent to the browser
	 */
	function fetch($path) {
		extract($this->_viewVars);
		if (!file_exists($path)) {
			$this->_fetchError($path);
		}
		$viewFile=file_get_contents($path);
		$this->processTags($viewFile);
		ob_start();
		eval("?>$viewFile<?");
		$view=ob_get_contents();
		ob_end_clean();
		return $view;
	}

	function _fetchError($path) {
		Base_Controller_View_Error::Show("View doesn't exists","The file $path is missing");
	}
	
	/**
	 * @ignore
	 */
    private function tg_Widget($Parameters) {
    	$widgetResult="";
		if($Parameters["NAME"]) {
			$widget = new Base_Controller_View_Widgets();
			$widget->init();
			$params=array();
			foreach ($Parameters as $attr => $value) {
				if ($attr!="NAME") $params[$attr]=$value;
			};
			$widget->load($Parameters["NAME"],$params);
			$widgetResult=$widget->render($Parameters["NAME"],false);
		}
		return $widgetResult;
    }
    private function tg_Include($Params) { 
        if (!$this->_CheckParameters($Params,array("FILE"=>PARAM_STRING))) return ""; 
        $fileName=""; 
        $baseDir=""; 
        if(isset($Params["FILE"])) $fileName=$Params["FILE"]; 
        if(isset($Params["DIR"])) $baseDir=$Params["DIR"]; 
        if (!empty($baseDir)) $fileName=$baseDir."/".$fileName; 
		if($Params["FILE"]) {
			$path=$Params["FILE"];
			if ($Params["DIR"]) {
				$dir=$Params["DIR"];
				if (substr($dir,-1)!="/") $dir.="/";
				$path=$_SERVER["DOCUMENT_ROOT"]."/".$dir.$path;
			} else {
				$dir=$this->template_dir;
				if (substr($dir,-1)!="/") $dir.="/";
				$path=$dir.$path;
			}
			$retVal=str_replace($match[0],"<?php include(\"$path\");?>",$content);
		}
        return $retVal; 
    } 

    private function _CheckParameters($params,$paramDef) { 
        if (!is_array($params)) return false; 
        if (!is_array($paramDef)) return false; 

        foreach($paramDef as $key=>$type) { 
        if (!isset($params[$key])) return false; 
        switch ($type) { 
            case PARAM_STRING: 
            break; 
            case PARAM_ARRAY: 
            if (!is_array($params[$key])) return false; 
            break; 
            case PARAM_BOOLEAN: 
            if (!is_bool($params[$key])) return false; 
            break; 
            case PARAM_INT: 
            if (!is_int($params[$key])) return false; 
            break; 
        } 
        } 

            return true; 
    } 

    
}
