<?php

namespace angel\db\schema\mysqli;


use angel\base\Db;
use angel\exception\DbConnectionException;
use angel\exception\DbException;
use angel\Angel;
use angel\base\Logger;


class MysqliSchema extends Db{
	
	public $host = "localhost";
	public $port = 3306;
	public $username="root";
	public $password = "";
	public $database = "";
	public $charset = "utf-8";
	public $prefix = "";
	
	/**
	 * 
	 * @var mysqli
	 */
	private $_db = null;
	private $_error = null;

	private $_tableName = "";
	private $_fields = "*";
	private $_query = "";
	private $_join = [];
	private $_groupBy = [];
	private $_orderBy = [];
	private $_where = [];
	
	/**
	 * mysqli statement
	 * @var mysqli_stmt
	 */
	private $_statement = null;
	private $_transaction_in_progress = false;
	
	public function init(){
		parent::init();
	}	
	/**
	 * get table name
	 * @param string $tableName
	 * @return string
	 */
	public function getTableName($tableName){
		return $this->prefix.$tableName;
	}
	
	public function getLastError(){
		return $this->_error;
	}
	
	private function setLastError($error){
		$this->log($error,Logger::LEVEL_ERROR);
		$this->_error = $error;
	}
	
	/**
	 * 
	 * @return \mysqli
	 */
	public function getDb(){
		return $this->_db;
	}
	
	/**
	 * This methods returns the ID of the last inserted item
	 */
	public function getLastInsertId()
	{
		return $this->getDb()->insert_id;
	}
	
	public function connection(){
		$this->_db = new \mysqli($this->host, $this->username, $this->password, $this->database, $this->port);	
		if ($this->getDb()->connect_error)
			throw new DbConnectionException('Connect Error ' . $this->_db->connect_errno . ': ' . $this->_db->connect_error);
		
		if ($this->charset)
			$this->getDb()->set_charset ($this->charset);
		return true;
	}
	
	/**
	 * This method allows you to concatenate joins for the final SQL statement.
	 * @param string $joinTable
	 * @param string $joinCondition
	 * @param string $joinType
	 * @return boolean|\angel\db\schema\mysqli\MysqliSchema
	 */
 	public function join($joinTable, $joinCondition, $joinType = '')
     {
        $allowedTypes = array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER');
        $joinType = strtoupper (trim ($joinType));

        if ($joinType && !in_array ($joinType, $allowedTypes)){
            $this->setLastError('Wrong JOIN type: '.$joinType);
            return false;
        }

        if (!is_object ($joinTable))
            $joinTable = self::$prefix . $joinTable;
        $this->_join[] = [$joinType,  $joinTable, $joinCondition];        
        return $this;
    }
    
    /**
     * This method allows you to specify multiple (method chaining optional) AND WHERE statements for SQL queries.
     * @param unknown $where
     * @return \angel\db\schema\mysqli\MysqliSchema
     */
    public function where($where){
    	$this->_where = $where;
    	return $this;
    }  
	
    public function having($havingProp, $havingValue = null, $operator = null)
    {
    	if ($operator)
    		$havingValue = Array ($operator => $havingValue);
    
    	$this->_having[] = Array ("AND", $havingValue, $havingProp);
    	return $this;
    }
    
    
    /**
     * This method allows you to specify multiple (method chaining optional) ORDER BY statements for SQL queries.
     *
     * @uses $MySqliDb->orderBy('id', 'desc')->orderBy('name', 'desc');
     *
     * @param string $orderByField The name of the database field.
     * @param string $orderByDirection Order direction.
     *
     * @return MysqliDb
     */
    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null)
    {
    	$allowedDirection = Array ("ASC", "DESC");
    	$orderbyDirection = strtoupper (trim ($orderbyDirection));
    	$orderByField = preg_replace ("/[^-a-z0-9\.\(\),_`\*\'\"]+/i",'', $orderByField);
    
    	// Add table prefix to orderByField if needed.
    	//FIXME: We are adding prefix only if table is enclosed into `` to distinguish aliases
    	// from table names
    	$orderByField = preg_replace('/(\`)([`a-zA-Z0-9_]*\.)/', '\1' . self::$prefix.  '\2', $orderByField);
    
    
    	if (empty($orderbyDirection) || !in_array ($orderbyDirection, $allowedDirection))
    		die ('Wrong order direction: '.$orderbyDirection);
    
    	if (is_array ($customFields)) {
    		foreach ($customFields as $key => $value)
    			$customFields[$key] = preg_replace ("/[^-a-z0-9\.\(\),_`]+/i",'', $value);
    
    		$orderByField = 'FIELD (' . $orderByField . ', "' . implode('","', $customFields) . '")';
    	}
    
    	$this->_orderBy[$orderByField] = $orderbyDirection;
    	return $this;
    }
    
    /**
     * This method allows you to specify multiple (method chaining optional) GROUP BY statements for SQL queries.
     *
     * @uses $MySqliDb->groupBy('name');
     *
     * @param string $groupByField The name of the database field.
     *
     * @return MysqliDb
     */
    public function groupBy($groupByField)
    {
    	$groupByField = preg_replace ("/[^-a-z0-9\.\(\),_\*]+/i",'', $groupByField);
    
    	$this->_groupBy[] = $groupByField;
    	return $this;
    }
    
    /**
     * transaction functions
     * @return boolean
     */
    
	public function beginTransaction()
	{
		if($this->_transaction_in_progress)
			return false;
		$this->_transaction_in_progress = $this->getDb()->autocommit (false);
		return $this->_transaction_in_progress;
	}
	
	public function commit(){
		$result = $this->getDb()->commit ();
		$this->_transaction_in_progress = false;
		$this->getDb()->autocommit (true);
		return $result;
	}
	
	public function table($tableName){
		$this->_tableName = $this->getTableName($tableName);
		return $this;
	}
	
	public function fields($fields="*"){
		$this->_fields = $fields;
		return $this;
	}
	
	public function rollback(){
		$result = $this->getDb()->rollback ();
		$this->_transaction_in_progress = false;
		$this->getDb()->autocommit (true);
		return $result;
	}
		
	public function insert($tableName,$columns){
		$tableName = $this->getTableName($tableName);
		return $this->buildInsertSchema($tableName, $columns);
	}
	
	public function update($tableName,$columns){
		$tableName = $this->getTableName($tableName);
		return $this->buildUpdateSchema($tableName, $columns);
	}
	
	public function execute(){	
		$this->buildQuery();		
		$this->_statement = $this->prepareQuery();	
		if($this->_statement->execute()){
			$rows = $this->_statement->affected_rows;
			$this->reset();
			return $rows;
		}else{
			$this->setLastError($stmt->error);
			$this->reset();
			return false;
		}
	}
	
	public function query($sql = null){
		if($sql)
			$this->_query = $sql;
		else 
			$this->buildQuery();
		
		$this->_statement = $this->prepareQuery();
		if($this->_statement->execute()	){
			$data = $this->processResult();
			$this->reset();
			return $data;
		}else{
			$this->setLastError($stmt->error);
			$this->reset();
			return false;
		}
	}
	
	protected function processResult(){
		
		if(empty($this->_statement))
			return null;
		
		$metadata = $this->_statement->result_metadata();
		
		/**
		 * not result
		 */
		if(empty($metadata) && $this->_statement->sqlstate)
			return [];
		/**
		 * mysqli get query
		 */
		while ($field = $metadata->fetch_field()){
			var_dump($field);
		}
	}
    
	/**
	 * build inser sql schema
	 * @param string $tableName
	 * @param mixed $columns
	 * @return mysqli_stmt
	 */
	protected function buildInsertSchema($tableName,$columns){
		$this->_query = "INSERT INTO {$tableName} ";
		$this->buildTableData($columns);
		return $this;
	}
	
	/**
	 *  build update sql schema
	 * @param string $tableName
	 * @param mixed $columns
	 * @return mysqli_stmt
	 */
	protected function buildUpdateSchema($tableName,$columns){
		$this->_query = "UPDATE {$tableName} ";
		$this->buildTableData($columns);
		return $this;
	}
	
	/**
	 * 
	 * @param string $numRows
	 */
	protected function buildQuery($numRows = null){
		$this->buildTable();
		$this->buildJoin();	
		$this->buildWhere();			
		$this->buildGroupBy();
		$this->buildOrderBy();
		$this->buildLimit($numRows);
	}
		
	protected function prepareQuery()
	{
		$this->log("prepare sql statement : {$this->_query}");
		if (!$stmt = $this->getDb()->prepare($this->_query))
			throw new DbException("Problem preparing query ($this->_query) " . $this->getDb()->error);	
		return $stmt;
	}
	
	protected function buildTableData ($columns) {
		if (!is_array ($columns))
			return;	
		$isInsert = preg_match ('/^[INSERT|REPLACE]/', $this->_query);
		$dataColumns = array_keys ($columns);
		if ($isInsert)
			$this->_query .= ' (`' . implode ($dataColumns, '`, `') . '`)  VALUES (';
		else
			$this->_query .= " SET ";
		
		if($isInsert){
			foreach ($columns as $row){
				$this->_query .= "'{$row}',";
			}
		}else{
			foreach ($columns as $key=>$val){
				$val = $this->getDb()->real_escape_string($val);
				$this->_query .= "`{$key}`='{$val}',";
			}
		}
		$this->_query = rtrim($this->_query,",");
		if ($isInsert)
			$this->_query .= ')';
	}
	
	protected function buildTable () {
		if(empty($this->_query) && !empty($this->_tableName)){
			$this->_query = "SELECT {$this->_fields} FROM {$this->_tableName}";
		}
	}
	protected function buildJoin () {
        if (empty ($this->_join))
            return;

        foreach ($this->_join as $data) {
            list ($joinType,  $joinTable, $joinCondition) = $data;
            $this->_query .= " " . $joinType. " JOIN " . $joinTable ." on " . $joinCondition;
        }
    }
    
    protected function buildWhere(){
    	$this->_query .= $this->parseWhere($this->_where);
    }
    
    protected function buildGroupBy () {
    	if (empty ($this->_groupBy))
    		return;
    
    	$this->_query .= " GROUP BY ";
    	foreach ($this->_groupBy as $key => $value)
    		$this->_query .= $value . ", ";
    
    	$this->_query = rtrim($this->_query, ', ') . " ";
    }
    
    protected function buildOrderBy () {
    	if (empty ($this->_orderBy))
    		return;
    
    	$this->_query .= " ORDER BY ";
    	foreach ($this->_orderBy as $prop => $value) {
    		if (strtolower (str_replace (" ", "", $prop)) == 'rand()')
    			$this->_query .= "rand(), ";
    		else
    			$this->_query .= $prop . " " . $value . ", ";
    	}
    
    	$this->_query = rtrim ($this->_query, ', ') . " ";
    }
        
    protected function buildLimit ($numRows) {
    	if (!isset ($numRows))
    		return;
    
    	if (is_array ($numRows))
    		$this->_query .= ' LIMIT ' . (int)$numRows[0] . ', ' . (int)$numRows[1];
    	else
    		$this->_query .= ' LIMIT ' . (int)$numRows;
    }
    
    
    
	function __destruct(){
		if($this->_db){
			$this->_db->close();
			unset($this->_db);
		}
	}
	
	protected $comparison = array (
			'eq' => '=',
			'neq' => '<>',
			'gt' => '>',
			'egt' => '>=',
			'lt' => '<',
			'elt' => '<=',
			'notlike' => 'NOT LIKE',
			'like' => 'LIKE'
	);
	
	
	public function escapeString($str) {
		return addslashes ( $str );
	}
	
	
	protected function parseKey(&$key) {
		return $key;
	}
	
	
	protected function parseValue($value) {
		if (is_string ( $value )) {
			$value = '\'' . $this->escapeString ( $value ) . '\'';
		} elseif (isset ( $value [0] ) && is_string ( $value [0] ) && strtolower ( $value [0] ) == 'exp') {
			$value = $this->escapeString ( $value [1] );
		} elseif (is_array ( $value )) {
			$value = array_map ( array (
					$this,
					'parseValue'
			), $value );
		} elseif (is_null ( $value )) {
			$value = 'null';
		}
	
		return $value;
	}
	public function parseWhere($where, $addWhere = true) {
		if (empty ( $where ))
			return NUll;
		$whereStr = '';
		if (is_string ( $where )) {
			$whereStr = $where;
		} else {
			if (isset ( $where ['_logic'] )) {
				$operate = ' ' . strtoupper ( $where ['_logic'] ) . ' ';
				unset ( $where ['_logic'] );
			} else {
				$operate = ' AND ';
			}
			foreach ( $where as $key => $val ) {
				if ($val === null) {
					continue;
				}
				$whereStr .= '( ';
				if (! preg_match ( '/^[A-Z_\|\&\-.a-z0-9]+$/', trim ( $key ) )) {
					return false;
				}
				$multi = is_array ( $val ) && isset ( $val ['_multi'] );
				$key = trim ( $key );
				if (strpos ( $key, '|' )) {
					$array = explode ( '|', $key );
					$str = array ();
					foreach ( $array as $m => $k ) {
						$v = $multi ? $val [$m] : $val;
						$str [] = '(' . $this->parseWhereItem ( $this->parseKey ( $k ), $v ) . ')';
					}
					$whereStr .= implode ( ' OR ', $str );
				} elseif (strpos ( $key, '&' )) {
					$array = explode ( '&', $key );
					$str = array ();
					foreach ( $array as $m => $k ) {
						$v = $multi ? $val [$m] : $val;
						$str [] = '(' . $this->parseWhereItem ( $this->parseKey ( $k ), $v ) . ')';
					}
					$whereStr .= implode ( ' AND ', $str );
				} else {
					$whereStr .= $this->parseWhereItem ( $this->parseKey ( $key ), $val );
				}
				$whereStr .= ' )' . $operate;
			}
				
			$whereStr = substr ( $whereStr, 0, - strlen ( $operate ) );
		}
		if ($addWhere)
			return empty ( $whereStr ) ? '' : ' WHERE ' . $whereStr;
		else
			return empty ( $whereStr ) ? '' : $whereStr;
	}
	
	
	protected function parseWhereItem($key, $val) {
		$whereStr = '';
		if (is_array ( $val )) {
				
			if (is_string ( $val [0] )) {
				if (preg_match ( '/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i', $val [0] )) {
					$whereStr .= $key . ' ' . $this->comparison [strtolower ( $val [0] )] . ' ' . $this->parseValue ( $val [1] );
				} elseif ('exp' == strtolower ( $val [0] )) {
					$whereStr .= ' (' . $key . ' ' . $val [1] . ') ';
				} elseif (preg_match ( '/IN/i', $val [0] )) {
					if (isset ( $val [2] ) && 'exp' == $val [2]) {
						$whereStr .= $key . ' ' . strtoupper ( $val [0] ) . ' ' . $val [1];
					} else {
						if (is_string ( $val [1] )) {
							$val [1] = explode ( ',', $val [1] );
						}
						$zone = implode ( ',', $this->parseValue ( $val [1] ) );
						$whereStr .= $key . ' ' . strtoupper ( $val [0] ) . ' (' . $zone . ')';
					}
				} elseif (preg_match ( '/BETWEEN/i', $val [0] )) {
					$data = is_string ( $val [1] ) ? explode ( ',', $val [1] ) : $val [1];
					$whereStr .= ' (' . $key . ' ' . strtoupper ( $val [0] ) . ' ' . $this->parseValue ( $data [0] ) . ' AND ' . $this->parseValue ( $data [1] ) . ' )';
				} else {
					return false;
				}
			} else {
	
				$count = count ( $val );
				if (in_array ( strtoupper ( trim ( $val [$count - 1] ) ), array (
						'AND',
						'OR',
						'XOR'
				) )) {
					$rule = strtoupper ( trim ( $val [$count - 1] ) );
					$count = $count - 1;
				} else {
					$rule = 'AND';
				}
				for($i = 0; $i < $count; $i ++) {
					$data = is_array ( $val [$i] ) ? $val [$i] [1] : $val [$i];
					if ('exp' == strtolower ( $val [$i] [0] )) {
						$whereStr .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
					} else {
						$op = is_array ( $val [$i] ) ? $this->comparison [strtolower ( $val [$i] [0] )] : '=';
						$whereStr .= '(' . $key . ' ' . $op . ' ' . $this->parseValue ( $data ) . ') ' . $rule . ' ';
					}
				}
				$whereStr = substr ( $whereStr, 0, - 4 );
			}
		} else {
				
			$whereStr .= $key . ' = ' . $this->parseValue ( $val );
		}
		return $whereStr;
	}
	
	protected function reset()
	{
		$this->_tableName = null;
		$this->_fields = "*";
		$this->_where = array();
		$this->_join = array();
		$this->_orderBy = array();
		$this->_groupBy = array();
		$this->_query = null;
		$this->_statement = null;
	}
	
	protected function log($msg,$level=Logger::LEVEL_DEBUG){
		if($level < Logger::LEVEL_WARNING){
			Angel::debug($msg);
		}else{
			Angel::error($msg);
		}	
	}
	
}

?>