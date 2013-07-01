<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @package Base
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @abstract
 * @ignore
 */
/**
 * The Base::Filesystem implements a low-level file handling functions
 * @todo Add more functionality to this class
 */
class Base_Filesystem {
    
    public function log($file,$message,$params="") {
		fputs(fopen($file,"a+"),$message."\n=================\n".($params?print_r($params,true)."\n================\n":""));
    }
}
