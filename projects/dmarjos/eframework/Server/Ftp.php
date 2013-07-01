<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Server_Ftp {

	private $ftpHandler=null;
	private $debugMode=false;
	
	function enableDebugMode($enable = false) {
		$this->debugMode=$enable;
	}
	function connect($server,$username,$password) {
		set_time_limit(100);
		if ($this->debugMode) echo "Trying to connect to $server<br/>";
		$this->ftpHandler=ftp_connect($server);
		if (!$this->ftpHandler) 
			throw new Server_FTP_CouldNotConnectException("Connection to host $server has failed");
			
		
	}

	
}