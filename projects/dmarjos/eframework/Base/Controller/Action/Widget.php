<?php 
class Base_Controller_Action_Widget {
	
	public $currentController=null;
	
	function init() {
		$this->currentController=Base::getParameter("EFW_CURRENT_CONTROLLER");	
	}
	
	function run() {
		
	}
	
}
