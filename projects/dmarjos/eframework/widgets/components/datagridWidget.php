<?php
class datagridWidget extends Base_Controller_Action_Widget {
	
	function run($parameters=false) {
		$this->currentController->addScript("/resources/js/datagrid.js");
		$this->currentController->addStyle("/resources/css/datagrid.css");
		foreach($parameters as $key=>$value) {
			$this->view->assign(strtolower($key),$value);
			
		}
		//if ($parameters) dump_var($parameters);
	}
}