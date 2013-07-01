<?
/**
 * @package Base
 * @subpackage Exceptions
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the exception type thrown when a required model does exists but it doesn't defines the model
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 * @abstract
*/
class Base_Controller_Action_Exceptions_ModelNotDefined extends Exception {
}