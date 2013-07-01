<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Sql_Insert {

    private $fields=NULL;
    private $table=NULL;

    function __construct() {
	return $this;
    }
    
    function into($table) {
	$this->table=$table;
	return $this;
    }

    function set($values) {
	$this->fields=$values;
	return $this;
    }

    
    function __toString() {
    
	$sql="INSERT INTO ";
	if (!$this->table) return false;

	$sql.=$this->table;
	    
	if (!is_array($this->fields)) return false;

	$_set=array();
	foreach($this->fields as $field=>$value) {
	    if (eregi("(.*)\(",$value))
		$_set[]="$field=$value";
	    elseif (substr($value,0,1)=="'")
		$_set[]="$field=$value";
	    else
		$_set[]="$field='$value'";
	}
	
	$sql.=" set ".implode(",",$_set);

    	
	return $sql;
    }
}