<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Mysql extends DB_Adapter {

    function __construct($host,$username,$pass,$db) {
		$this->username=$username;
		$this->password=$pass;
		$this->host=$host;
		$this->dbname=$db;
		parent::init("Mysql",$this->host,$this->username,$this->password,$this->dbname);
    }

}