<?
/**
 * @package Base
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Base::Registry implements a quick and dirty variable storage method. It uses an static array to store name=value pairs. Just like Base, it is an abstract class, so you can not instantiate it
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @abstract
 */
abstract class Base_Registry {

    public static $reg=array();
    
	/**
	 * Method to initialize the registry
	 * 
	 * @return void
	 */
    public static function initRegistry() {
		self::$reg=array();
    }
    
	/**
	 * Method to store a variable into the registry
	 * 
	 * @param string $var The variable to be stored
	 * @param mixed $val The value
	 * @return void
	 */
    public static function set($var,$val) {
		$_reg=self::$reg;
		$_reg[$var]=$val;
		self::$reg=$_reg;
    }
    
	/**
	 * Method to store a variable into the registry
	 * 
	 * @param string $var The variable to be retrieved
	 * @return mixed 
	 */
    public static function get($var) {
		return self::$reg[$var];
    }
    
	/**
	 * Method to check if a variable is stored in the registry
	 * 
	 * @param string $var The variable to be retrieved
	 * @return boolean
	 */
    public static function is_set($var) {
		return isset(self::$reg[$var]);
    }

	/**
	 * Method to dump the registry itself. Debugging purposes
	 * 
	 * @return void
	 */
    public static function dump() {
		dump_var(self::$reg);
    }
}