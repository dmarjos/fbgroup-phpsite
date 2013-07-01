<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Model {
	var $dbHandler;
	
	function __construct() {
		$this->dbHandler=Base_Registry::Get("DB");
		if (!is_object($this->dbHandler))
			throw new Base_Exception_NoDBHandler("No se ha inicializado el manejador de base de datos");
			
		$rt=Base::getParameter("defaultRecordsetType");
		if ($rt) $this->setReturnType($rt);
		if (substr(get_class($this->dbHandler),0,3)!=="DB_")
			throw new Base_Exception_DBHandlerIsNotDB("No se ha inicializado el manejador de base de datos");
	}
	
	function setReturnType($rt=DB_RETURN_AS_ARRAY) {
		$this->dbHandler->returnType=$rt;
	}
	
}