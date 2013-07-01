<?php
/**
 * @package Base
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the main class for the framework. It is abstract, so you can not instantiate it.
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @abstract
*/
abstract class Base {

	static $version = "1.0";
	
	/**
	 * 
	 * @ignore
	 */
	private $_parameters=array("EFW_SETUP_PATH_SLASH"=>"/","EFW_SETUP_USEDBCACHE"=>false);

	/**
	 * This method initializes the framework, reading configuration files, creating DB objects, VIEWS manager, and so on.
	 *
	 * It also sends X-Powered-By header to the browser, with the version number
	 */
	public static function initialize() {
		header("X-Powered-By: eFramework MVC version ".self::$version);
	}
	
	/**
	 * Function to set different parameters for the framework to work on.
	 * Altough it can be anything you need to set for global use, there are (for now) one internal parameter:
	 *
	 * path_slash which is defined as "/" for unix systems. You should set to "\" if writing code to be run on windows
	 * 
	 * Parameters name will be CAPITALIZED, and prefixed with EFW_SETUP_ to avoid overwriting with other Base_Registry::Set calls. See Base_Registry for further information
	 *
	 * @param string $parameter The parameter to be set
	 * @param mixed $value The parameter value
	 * @return void
	 */
	public static function setParameter($parameter,$value) {
        $parameter=strtoupper($parameter);
		Base_Registry::Set("EFW_SETUP_{$parameter}",$value);
	}
	
	/**
	 * Function to get different parameters
	 *
	 * @param string $parameter The parameter to be set
	 * @return mixed The stored value for parameter
	 */
	public static function getParameter($parameter) {
        $parameter=strtoupper($parameter);
		return Base_Registry::Get("EFW_SETUP_{$parameter}");
	}
	
	/**
	 * 
	 * @ignore
	 */
	public static function __set($name,$value) {
		self::setParameter(strtoupper($name),$value);
	}
	
	/**
	 * 
	 * @ignore
	 */
	public static function __get($name) {
		return self::getParameter(strtoupper($name));
	}
	
	

}
