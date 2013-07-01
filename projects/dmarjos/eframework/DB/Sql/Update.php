<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Sql_Update {

    private $fields=NULL;
    private $table=NULL;
    private $wheres=NULL;
    
    function __construct($table) {
		$this->table=$table;
		return $this;
    }
    
    function set($values) {
		$this->fields=$values;
		return $this;
    }

    function where($condition) {
		if (is_null($this->wheres)) $this->wheres=array();
		$this->wheres[]=$condition;
		return $this;
    }
    
    function __toString() {
    
		$sql="UPDATE ";
		if (!$this->table) return false;
	
		$sql.=$this->table;
			
		if (!is_array($this->fields)) return false;
	
		$_set=array();
		foreach($this->fields as $field=>$value) {
			if (eregi("(.*)\(",$value))
			$_set[]="$field=$value";
			else
			$_set[]="$field='$value'";
		}
		
		$sql.=" SET ".implode(",",$_set);
	
		if ($this->wheres) {
			$sql.=" WHERE ".implode(" AND ",$this->wheres)." ";
		}
		
		return $sql;
    }
}