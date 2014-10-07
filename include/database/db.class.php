<?
  /***
   A PHP Class turn the usage of MYSQL database to real simple and convenient.
   > Version:		1.0.2
   > Author:		MineS Chan (mineschan@gmail.com)
   > License: 		LGPL
   > Last updated: 	10 October ,2014
  ***/

  class DB
  {
  	//----------------User configable variables
    
    /** Change it to true if you want all queries to be debugged.**/
    var $defaultDebug = false;


  	//----------------INTERNAL VARIABLES: DO NOT CHANGE or REMOVE
  	var $qField = "*"; //e.g. "name,sex,age" , "firstname as fn"
  	var $qId	= "id";
  	var $magicword = "@plain:";
  	var $table;
  	
    var $mtStart;
    var $totalQueries;
    var $lastResult;


    function DB()
    {
      $this->mtStart = $this->getMicroTime();
      $this->totalQueries  = 0;
      $this->lastResult = NULL;
      
      if(function_exists("conn")){
        $link = conn();
      }
    }
    
    /** Connect to DB if you are not yet connected **/
    function connect($host,$user,$passwd,$database)
    {
		$conn = mysql_connect($host,$user,$passwd);
		$select_db;
		
		if($database) $select_db = mysql_select_db($database);
		mysql_query("SET NAMES 'utf8'"); 
        mysql_query("SET CHARACTER_SET_CLIENT=utf8"); 
        mysql_query("SET CHARACTER_SET_RESULTS=utf8");
         			
		if(!$conn || !$select_db)
			echo "<script>alert('DB:Connect to Database error')</script>";	
    }

    /** Select another db during your actions **/
    function selectDB($databse){
	     $select_db = mysql_select_db($database);
		 if(!$conn || !$select_db) echo "<script>alert('DB:Can not select the target Database')</script>";		    
    }
    
    /** Close the DB if you need to do it before PHP do it for you**/
    function close(){
      mysql_close();
    }   




    function query($query, $debug = -1)
    {
      $this->totalQueries++;
      $this->lastResult = mysql_query($query) or $this->debugAndDie($query);
      $this->debug($debug, $query, $this->lastResult);

      return $this->lastResult;
    }

    function execute($query, $debug = -1)
    {
      $this->totalQueries++;
      $result = mysql_query($query) or $this->debugAndDie($query);
      $this->debug($debug, $query);
      
      return $result;
    }
    
    function queryUniqueObject($query, $debug = -1)
    {
      $query = "$query LIMIT 1";

      $this->totalQueries++;
      $result = mysql_query($query) or $this->debugAndDie($query);
      $this->lastResult = $result;
      $this->debug($debug, $query, $result);
      return mysql_fetch_object($result);
    }
    
    function queryUniqueValue($query, $debug = -1)
    {
      $query = "$query LIMIT 1";

      $this->totalQueries++;
      $result = mysql_query($query) or $this->debugAndDie($query);
      $line = mysql_fetch_row($result);

      $this->debug($debug, $query, $result);

      return $line[0];
    }    
    
    function nextObject($result = NULL) //fetchNextObject
    {
      if ($result == NULL)
        $result = $this->lastResult;

      if ($result == NULL || mysql_num_rows($result) < 1)
        return NULL;
      else
        return mysql_fetch_object($result);
    }    
    
    
    
    
    function find($id,$table)
    {
    	$table = (isset($table))? $table:$this->table;
    	$sql = "SELECT {$this->qField} FROM $table WHERE `{$this->qId}` = '$id'";
    	return $this->queryUniqueObject($sql);    
    }
    
    function fetchAll($where = NULL,$order = NULL, $debug = -1){
		$sql = "SELECT {$this->qField} FROM $this->table";
		
		if($where != NULL && trim($where)!="")
			$sql .= " WHERE $where";
		if($order != NULL && trim($order)!="")
			$sql .= " ORDER by $order";		
			
		$this->result = $this->query($sql,$debug) or $this->debugAndDie(($sql));
	
		return $this->result;	    	
    }
    
    function fetchRow($where = NULL,$order = NULL, $debug = -1){
		$sql = "SELECT {$this->qField} FROM $this->table";
		
		if($where != NULL && trim($where)!="")
			$sql .= " WHERE $where";
		if($order != NULL && trim($order)!="")
			$sql .= " ORDER by $order";		
			
		$this->result = $this->queryUniqueObject($sql,$debug);
		
		if(is_object($this->result))
			return $this->result;	    	
    }      

    function insert($table, $data,$debug = -1) {
				
		$table;
		$q="INSERT INTO $table ";
		$v=''; $n='';
	
		foreach($data as $key=>$val) {
			$key = trim($key);
		
			$n.="$key, ";
			$v.= $this->formatValue($val).", ";
		}
	
		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
		
		if($this->execute($q,$debug)){
			//$this->free_result();
			return mysql_insert_id();
		}
		
		else return false;
		
	}
	
	function update($table, $data, $where='false',$debug = -1) {
		
		$q="UPDATE $table SET ";
		foreach($data as $key=>$val) {
			if($key == $this->qId)
				continue;
			
			$key = trim($key);
			$q .= "$key = ".$this->formatValue($val).", ";
		}
	
		$q = rtrim($q, ', ') . ' WHERE '.$where.';';
	
		return $this->execute($q,$debug);
	}	
    
    function delete($id,$table)
    {
    	$table = (isset($table))? $table:$this->table;
    	$sql = "DELETE FROM $table WHERE {$this->qId} = '$id'";
    	return $this->execute($sql);    
    }    
    
    function sort($order,$fieldName = 'order')
	{
		$i = 1;
		foreach ($order as $o){
			$result = mysql_query("UPDATE $this->table SET `$fieldName` = '$i' WHERE `{$this->qId}` = '$o'");
			$i++;
		}
		return $result;
	}
	
	
    function toDict($key,$value){
    	$dict = array();
    	while($row = $this->nextObject()){
    		$dict[$row->$key] = $row->$value;
    	}
		return $dict;
    }
	
    
  

    /** Get the number of rows of a query.
      * @param $result The ressource returned by query(). If NULL, the last result returned by query() will be used.
      * @return The number of rows of the query (0 or more).
      */
    function numRows($result = NULL)
    {
      if ($result == NULL)
        return mysql_num_rows($this->lastResult);
      else
        return mysql_num_rows($result);
    }


    /** Get the maximum value of a column in a table, with a condition.
      * @param $column The column where to compute the maximum.
      * @param $table The table where to compute the maximum.
      * @param $where The condition before to compute the maximum.
      * @return The maximum value (or NULL if result is empty).
      */
    function maxOf($column, $table, $where)
    {
      return $this->queryUniqueValue("SELECT MAX(`$column`) FROM `$table` WHERE $where");
    }
    /** Get the maximum value of a column in a table.
      * @param $column The column where to compute the maximum.
      * @param $table The table where to compute the maximum.
      * @return The maximum value (or NULL if result is empty).
      */
    function maxOfAll($column, $table)
    {
      return $this->queryUniqueValue("SELECT MAX(`$column`) FROM `$table`");
    }
    /** Get the count of rows in a table, with a condition.
      * @param $table The table where to compute the number of rows.
      * @param $where The condition before to compute the number or rows.
      * @return The number of rows (0 or more).
      */
    function countOf($table, $where)
    {
      return $this->queryUniqueValue("SELECT COUNT(*) FROM `$table` WHERE $where");
    }
    /** Get the count of rows in a table.
      * @param $table The table where to compute the number of rows.
      * @return The number of rows (0 or more).
      */
    function countOfAll($table)
    {
      return $this->queryUniqueValue("SELECT COUNT(*) FROM `$table`");
    }

    /**###########################
    #
    # Debug Functions
    #
    #############################**/


    /** Internal function to debug when MySQL encountered an error,
      * even if debug is set to Off.
      * @param $query The SQL query to echo before diying.
      */
    function debugAndDie($query)
    {
      if($this->defaultDebug){
	      $this->debugQuery($query, "Error");
	      die("<p style=\"margin: 2px;\">".mysql_error()."</p></div>");
      }
    }
    /** Internal function to debug a MySQL query.\n
      * Show the query and output the resulting table if not NULL.
      * @param $debug The parameter passed to query() functions. Can be boolean or -1 (default).
      * @param $query The SQL query to debug.
      * @param $result The resulting table of the query, if available.
      */
    function debug($debug, $query, $result = NULL)
    {
      if ($debug === -1 && $this->defaultDebug === false)
        return;
      if ($debug === false)
        return;

      $reason = ($debug === -1 ? "Default Debug" : "Debug");
      $this->debugQuery($query, $reason);
      if ($result == NULL)
        echo "<p style=\"margin: 2px;\">Number of affected rows: ".mysql_affected_rows()."</p></div>";
      else
        $this->debugResult($result);
    }
    /** Internal function to output a query for debug purpose.\n
      * Should be followed by a call to debugResult() or an echo of "</div>".
      * @param $query The SQL query to debug.
      * @param $reason The reason why this function is called: "Default Debug", "Debug" or "Error".
      */
    function debugQuery($query, $reason = "Debug")
    {
      $color = ($reason == "Error" ? "red" : "orange");
      echo "<div style=\"border: solid $color 1px; margin: 2px;\">".
           "<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">".
           "<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> ".
           "<span style=\"font-family: monospace;\">".htmlentities($query)."</span>"."</p>".mysql_error();
    }
    /** Internal function to output a table representing the result of a query, for debug purpose.\n
      * Should be preceded by a call to debugQuery().
      * @param $result The resulting table of the query.
      */
    function debugResult($result)
    {
      echo "<table border=\"1\" style=\"margin: 2px;\">".
           "<thead style=\"font-size: 80%\">";
      $numFields = mysql_num_fields($result);
      // BEGIN HEADER
      $tables    = array();
      $nbTables  = -1;
      $lastTable = "";
      $fields    = array();
      $nbFields  = -1;
      while ($column = mysql_fetch_field($result)) {
        if ($column->table != $lastTable) {
          $nbTables++;
          $tables[$nbTables] = array("name" => $column->table, "count" => 1);
        } else
          $tables[$nbTables]["count"]++;
        $lastTable = $column->table;
        $nbFields++;
        $fields[$nbFields] = $column->name;
      }
      for ($i = 0; $i <= $nbTables; $i++)
        echo "<th colspan=".$tables[$i]["count"].">".$tables[$i]["name"]."</th>";
      echo "</thead>";
      echo "<thead style=\"font-size: 80%\">";
      for ($i = 0; $i <= $nbFields; $i++)
        echo "<th>".$fields[$i]."</th>";
      echo "</thead>";
      // END HEADER
      while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        for ($i = 0; $i < $numFields; $i++)
          echo "<td>".htmlentities($row[$i])."</td>";
        echo "</tr>";
      }
      echo "</table></div>";
      $this->resetFetch($result);
    }


    /**###########################
    #
    # Helper Functions
    #
    #############################**/

    function resetFetch($result){
      if (mysql_num_rows($result) > 0) mysql_data_seek($result, 0);
    }
    
    
    /**###########################
    #
    # Stat Functions
    #
    #############################**/    

    function lastId(){
      return mysql_insert_id();
    }

    function getQueriesCount(){
      return $this->totalQueries;
    }

    function getExecTime(){
      return round(($this->getMicroTime() - $this->mtStart) * 1000) / 1000;
    }       
     
    /**###########################
    #
    # Private Functions
    #
    #############################**/
      
	private function escape($string) {
		if(get_magic_quotes_runtime()) $string = stripslashes($string);
		return mysql_real_escape_string($string);
	}       
    
	private function formatValue($value) {
	
		if(strtolower($value)=='null') return "NULL";
		elseif(strpos($value, $this->magicword) === 0) return substr(trim($value),strlen($this->magicword));
		else return "'".$this->escape($value)."'";
	}         
      
    private function getMicroTime(){
      list($msec, $sec) = explode(' ', microtime());
      return floor($sec / 1000) + $msec;
    }
    
    
    
  } // class DB
?>
