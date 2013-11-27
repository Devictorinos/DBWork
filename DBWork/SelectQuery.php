<?php


namespace DBWork;

use PDO;

use PDOException;

class SelectQuery
{

	private $dbh;
	private $table;
	private $alias;
	private $join;
	private $fields;

	private $where  = array();
	private $params = array();
	private $on     = array();
	private $groupBy;

	private $order  = null;
	private $limit  = null;


	public function __construct($dbh, $table, $alias, $fields)
	{
		$this->dbh    = $dbh;
		$this->table  = $table;
		$this->alias  = $alias;
		$this->fields = $fields;
	}

	//WHERE CLAUSE
	public function where($field, $operation, $subject,$oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->where[]  = " $_Switch $field $operation ?";
		$this->params[] = $subject;
		return $this;
	}

	//WHERE IN
	public function whereIn($field, array $list, $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$fList  =  $this->inClause($list);
		$this->where[] = "$_Switch $field IN  $fList";
		$this->params  = array_merge($this->params, $list);
		return $this;

	}

	//WHERE BETWEEN
	public function whereBetween($field, $a, $b, $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->where[]  = " $_Switch ($field BETWEEN ? AND ?) ";
		$this->params[] = $a;
		$this->params[] = $b;
		return $this;
	}

	//WHERE AND OR
	public function andOr($field1, $operator1, $a, $field2, $operator2, $b, $oprSwitch = null)
	{

		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->where[]  = "$_Switch ($field1 $operator1 ? OR $field2 $operator2 ? )";
		$this->params[] = $a;
		$this->params[] = $b;
		return $this;
	}


	//ON CLAUSE
	public function on($field, $operator, $subject, $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === 'on') { $_Switch = "ON";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->on[] = "$_Switch $field $operator $subject";
		//$this->params[] = $subject;
		return $this;
	}


	//AND ON 
	public function onAnd($field, $operator, $subject,  $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->on[] = "$_Switch $field $operator ?";
		$this->params[] = $subject;
		return $this;
	}

	//ON BETWEEN
	public function onBetween($field, $a, $b, $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$this->on[] = "$_Switch $field BETWEEN ? AND ? ";
		$this->params[] = $a;
		$this->params[] = $b;
		return $this;
	}


	//ON IN 
	public function onIn($filed, array $list, $oprSwitch = null)
	{
		if ($oprSwitch) { $_Switch = "AND"; }
		if ($oprSwitch === false) { $_Switch = "OR";}
		if ($oprSwitch === null) { $_Switch = "";}

		$inVal = $this->makeOnIn($list);

		$this->on[] = "$_Switch $filed IN $inVal";
		$this->params = array_merge($this->params, $list);

		return $this;
	} 

	//ON JOIN 
	public function onJoin($table, $alias, $type = '')
	{
		if ($type === 'left')  $type = "LEFT JOIN";
		if ($type === 'right') $type = "RIGHT JOIN";
		if ($type === 'inner') $type = "INNER JOIN";
		if ($type === 'outer') $type = "OUTER JOIN";
		if ($type === '') $type = "JOIN";

		$this->on[] = "$type $table as $alias";
		return $this;
	}


	//GROUP BY
	public function groupBy($field)
	{
		$this->groupBy = " $field ";
		return $this;
	}

	//ORDER BY
	public function orderBy($field, $asc = true)
	{
		if($asc) {
			$this->order = "  $field ASC ";
		}else{
			$this->order = "  $field DESC ";
		}

		return $this;

	}

	//LIMIT
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	//JOIN
	public function join($table, $alias, $type = '')
	{

		if ($type === 'left')  $type = "LEFT JOIN";
		if ($type === 'right') $type = "RIGHT JOIN";
		if ($type === 'inner') $type = "INNER JOIN";
		if ($type === 'outer') $type = "OUTER JOIN";
		if ($type === '') $type = "JOIN";

		$this->join = "$type $table as $alias";
		return $this;
	}

		private function makeOnIn($list)
	{
		array_map(function($i){

			return "?";

		}, $list);

		$list = implode(",", $list);

		return "($list)";
	}

	//changing fields values to ? for pdo statement.
	private function inClause($list){

		$list = array_map(function($i){

		 	return "?";

		 }, $list);

		$list = implode(",", $list);

		return "($list)";
	}


    //building sql
	private function BuildSQL()
	{
		$sql = "SELECT $this->fields"."\n";
		$sql .="FROM $this->table as $this->alias"."\n";

		if (!empty($this->join)) {
			$sql .= $this->join."\n";
		}

		if (!empty($this->leftJoin)) {
			$sql .= "LEFT JOIN $this->leftJoin"."\n";
		}

		if (!empty($this->rightJoin)) {
			$sql .= "RIGHT JOIN $this->rightJoin"."\n";
		}

		if (!empty($this->where )) {

			$sql .= "WHERE ". implode(" ",$this->where). "\n";
		}

		if (!empty($this->on)) {

			$sql .= "ON ".implode(" ", $this->on)."\n";
		}

		if (!empty($this->groupBy)) {

			$sql .= "GROUP BY $this->groupBy"."\n";
		}
		
		if (!empty($this->order)) {
			$sql .= "ORDER BY $this->order"."\n";
		}

		if (!empty($this->limit)) {

			$sql .= "LIMIT $this->limit "."\n";
		}

		
		return $sql;
	}


	//Executeing query
	private function RunSQL($debug = false)
	{
		$sql = $this->BuildSQL();
		
		if($debug){

			Log::query($sql,$this->params);
		}

		try {

			$query = $this->dbh->prepare($sql);

			foreach ($this->params as $key => $val) {
				
				$type = is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR;
				$type = is_bool($val) ? PDO::PARAM_BOOL : PDO::PARAM_STR;
				$type = is_integer($val) ? PDO::PARAM_INT : PDO::PARAM_STR;

				$query->bindValue($key+1, $val, $type);
			}
			
			$query->execute();
			return $query;
			
		} catch (PDOException $e) {

			Log::error($e);
		}

	}

	//Select Select Method
    public function getAll($debug = false)
    {
        return $this->RunSQL($debug)->fetchAll(PDO::FETCH_ASSOC);
    }

    //select One Row Method
    public function getOne($debug = false)
    {
    	return $this->RunSQL($debug)->fetch(PDO::FETCH_ASSOC);
    }
}
