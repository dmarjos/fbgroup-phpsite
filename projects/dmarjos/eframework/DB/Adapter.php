<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
define('DB_RETURN_AS_ARRAY',0x000001);
define('DB_RETURN_AS_OBJECT',0x000003);
class DB_Adapter {

    private $Adapter="Mysql";
    
    public $host="localhost";
    public $dbname="";
    public $username="";
    public $password="";
	public $returnType=DB_RETURN_AS_OBJECT;    
    private $dbConnector=NULL;
    
    
    function __construct() {
    }

    function init($adapter,$host,$username,$pass,$db) {
		$this->Adapter=$adapter;
		$this->username=$username;
		$this->password=$pass;
		$this->host=$host;
		$this->dbname=$db;
		
		$dbClass="DB_Adapter_".ucwords(strtolower($this->Adapter));
		$this->dbConnector=new $dbClass($this->host,$this->username,$this->password,$this->dbname);
    }
    
    function connect() {
		$this->dbConnector->connect();	
    }
    
    function close() {
		$this->dbConnector->close();
		unset($this->dbConnector);
    }

    function select($fields) {
		return new DB_Sql_Select($fields);
    }
    
    function Insert() {
		return new DB_Sql_Insert();
    }

    function Update($table) {
		return new DB_Sql_Update($table);
    }

    function Delete($table) {
		return new DB_Sql_Delete($table);
    }

    function query($SQL) {
		if (is_object($SQL)) {
			$sql=$SQL->__toString();
		} else
			$sql=$SQL;
			
		if (Base::getParameter("useDBCache")) {
			$key=md5($sql);
			$this->cacheKey=$key;
		}
		$retVal=$this->dbConnector->query($sql);
		return $retVal;
    }
    
	
    function execute($SQL) {
		return $this->query($SQL);
    }
    
    function numRows($res) {
		return $this->dbConnector->numRows($res);
    }
    
    function fetchAll($res) {
		if (Base::getParameter("useDBCache")) {
			if (!Cache_Frontend::checkIsCached($this->cacheKey)) {
				$retVal=$this->dbConnector->fetchAll($res);
				Cache_Frontend::saveCache($this->cacheKey,serialize($retVal));
			} else {
				$retVal=unserialize(Cache_Frontend::getCache($this->cacheKey));
			}
		} else {
			$retVal=$this->dbConnector->fetchAll($res,$this->returnType);
		}
		return $retVal;
    }
        
    function fetchRow($res) {
		return $this->dbConnector->fetchRow($res,$this->returnType);
    }
        
    function insertID() {
		return $this->dbConnector->insertId();
    }
}