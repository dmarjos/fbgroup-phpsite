<?
if (!defined('BASEPATH')) exit('No direct script access allowed');

class DB_Adapter_Mysql {

    public $host="localhost";
    public $dbname="";
    public $username="";
    public $password="";

    protected $dbLink=NULL;
    
    protected $Modifiers=array(
		"ALL"=>"ALL",
		"DISTINCT"=>"DISTINCT",
		"DISTINCTROW"=>"DISTINCTROW",
		"HIGH_PRIORITY"=>"HIGH_PRIORITY",
		"STRAIGHT_JOIN"=>"STRAIGHT_JOIN",
		"SQL_SMALL_RESULT"=>"SQL_SMALL_RESULT",
		"SQL_BIG_RESULT"=>"SQL_BIG_RESULT",
		"SQL_BUFFER_RESULT"=>"SQL_BUFFER_RESULT",
		"SQL_CACHE"=>"SQL_CACHE",
		"SQL_NO_CACHE"=>"SQL_NO_CACHE",
		"SQL_CALC_FOUND_ROWS"=>"SQL_CALC_FOUND_ROWS"
    );
    
    function error($message,$params=NULL) {
	
		$bt=array("MESSAGE"=>$message);
		if (! is_null($params)) $bt["PARAMETERS"]=$params;

		dump_var($bt);
    }
    
    function __construct($host,$username,$password,$database) {
    
		$this->username=$username;
		$this->password=$password;
		$this->host=$host;
		$this->dbname=$database;
	
    }

    function connect() {
    	$this->dbLink=@mysql_connect($this->host,$this->username,$this->password,true);
    	if (!$this->dbLink) {
    		throw new DB_Exceptions_CouldNotConnect("No se pudo conectar a la base de datos");
    		die();
    	}
		mysql_select_db($this->dbname,$this->dbLink) or $this->error(mysql_error());
    }
    
    function close() {
		mysql_close($this->dbLink);
		unset($this->dbLink);
    }
    
    function query($query) {
    	if (!$this->dbLink) $this->connect();
		$res=mysql_query($query,$this->dbLink) or $this->error(mysql_error(),array("SQL_QUERY"=>$query));
		return $res;
    }

    function fetchAll($resource,$returnType=DB_RETURN_AS_OBJECT) {
		$retVal=array();
		while ($record=mysql_fetch_array($resource,MYSQL_ASSOC)) {
			switch ($returnType) {
				case DB_RETURN_AS_OBJECT:
					$row=new StdClass();
					foreach($record as $field => $value) {
						$field=strtolower($field);
						$row->{$field}=$value;
					}
					break;
				default:
					$row=$record;				
			}
			$retVal[]=$row;
		}
		return $retVal;
    }
    
    function fetchRow($result,$returnType=DB_RETURN_AS_OBJECT) {
		$record=mysql_fetch_array($result,MYSQL_ASSOC);
		if (!$record) return false;
		switch ($returnType) {
			case DB_RETURN_AS_OBJECT:
				$row=new StdClass();
				foreach($record as $field => $value) {
					$field=strtolower($field);
					$row->{$field}=$value;
				}
				break;
			default:
				$row=$record;				
		}
		return $row;
    }
    
    function insertID() {
		return mysql_insert_id($this->dbLink);
    }
    
    function numRows($resource) {
		return mysql_num_rows($resource);
    }

    function affectedRows($resource) {
		return mysql_affected_rows($resource);
    }

    function limit($records,$offset=NULL) {
    
		if (is_null($offset)) {
			return "LIMIT $records";
		} else {
			return "LIMIT $records OFFSET $offset";
		}
		
    }

}