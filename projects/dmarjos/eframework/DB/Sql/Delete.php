<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Sql_Delete {

    private $fields=NULL;
    private $table=NULL;
    private $wheres=NULL;
    
    function __construct($table) {
	$this->table=$table;
	return $this;
    }
    
    function where($condition) {
	if (is_null($this->wheres)) $this->wheres=array();
	$this->wheres[]=$condition;
	return $this;
    }
    
    function __toString() {
    
	$sql="DELETE FROM ";
	if (!$this->table) return false;

	$sql.=$this->table;
	    
	if ($this->wheres) {
	    $sql.=" WHERE ".implode(" AND ",$this->wheres)." ";
	}
	
	return $sql;
    }
}