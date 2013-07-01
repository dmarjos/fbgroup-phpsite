<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class DB_Sql_Select {

	/*
	dssafsdffdsf
	*/
	
    private $modifiers=NULL;
    private $fields=NULL;
    private $tables=NULL;
    private $joins=false;
    private $wheres=NULL;
    private $groups=NULL;
    private $having=NULL;
    private $orders=NULL;
    private $limit=NULL;

    function __construct($fields) {
        $this->fields=$fields;
        return $this;
    }
    
    function from($tables) {
        $this->tables=$tables;
        return $this;
    }

    function join($table,$condition,$type="inner") {
        if (!$this->joins) {
            $this->joins = array();
        }
        $this->joins[]=array($table,$condition,$type);
        return $this;
    }
    
    function where($condition) {
        if (is_null($this->wheres)) $this->wheres=array();
        if (is_array($condition)) {
            for($i=0; $i<count($condition);$i++) $this->wheres[]=$condition[$i];
        } else
            $this->wheres[]=$condition;
        return $this;
    }
    
    function group_by($condition) {
        if (is_null($this->groups)) $this->groups=array();
        $this->groups[]=$condition;
        return $this;
    }
    
    function having($condition) {
        if (is_null($this->having)) $this->having=array();
        $this->having[]=$condition;
        return $this;
    }
    
    function orders($condition) {
        if (is_null($this->orders)) $this->orders=array();
        $this->orders[]=$condition;
        return $this;
    }
    
    function limit($records,$offset=null) {
        $this->limit=array($records,$offset);
        return $this;
    }
    
    function __toString() {
    
	$sql="SELECT ";
	if (!$this->fields) return false;
	$sql.=$this->fields." ";
	if (!$this->tables) return false;
	if (is_array($this->tables)) {
	    $tables=array();
	    foreach($this->tables as $key => $val) {
		if (!is_numeric($key)) {
		    $tables[]="$val as $key";
		} else
		    $tables[]="$val";
	    } 
	    $from=implode(", ",$tables);
	} else
	    $from=$this->tables;
	    
	$sql.="FROM $from ";
	if ($this->joins) {
	    $joins=array();
	    foreach($this->joins as $idx => $join)  {
            if ($join[2]=="inner") {
                if (eregi("(.)=(.*)",$join[1]))
                    $joins[]="inner join ".$join[0]." on ".$join[1];
                else
                    $joins[]="cross join ".$join[0]." using(".$join[1].")";
            } else 
                $joins[]=$join[2]." join ".$join[0]." on ".$join[1];
	    }
	    $sql.=implode(" ",$joins)." ";
	}
	if ($this->wheres) {
	    $sql.="WHERE ".implode(" AND ",$this->wheres)." ";
	}
	
	if ($this->groups) {
	    $sql.="GROUP BY ".implode(", ",$this->groups)." ";
	}

	if ($this->having) {
	    $sql.="HAVING ".implode(", ",$this->having)." ";
	}

	if ($this->orders) {
	    $sql.="ORDER BY ".implode(", ",$this->orders)." ";
	}

	if ($this->limit) {
	    $sql.="LIMIT ".$this->limit[0];
	    if ($this->limit[1])
		$sql.=" OFFSET ".$this->limit[1] ;
	}

	return $sql;
    }
}