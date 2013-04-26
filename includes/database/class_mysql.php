<?php
/* 
 *  __    __         _                
 * / / /\ \ \  ___  | |_   ___   ___  
 * \ \/  \/ / / _ \ | __| / _ \ / _ \ 
 *  \  /\  / | (_) || |_ |  __/| (_) |
 *   \/  \/   \___/  \__| \___| \___/ 
 *                                    
 * Copyright ©2013 WoTeo All Rights Reserved.
 * This file is part of the woteo package.
 * 
 * For the full copyright and license information, 
 * please view the LICENSE file that was distributed with this source code.
 * This file may not be redistributed in whole or significant part.  
 * WebSite : http://www.woteo.com
 * Email : admin@woteo.com        
 */

define('DB_BOTH', 0);
define('DB_ASSOC', 1);
define('DB_NUM', 2);



class database
{
  var $config = array(
  'sqltype' => 'mysql',
  'servername' => 'localhost',
  'port' => 3306,
  'database' => '',
  'username' => '',
  'password' => '',
  'prefix' => '',
  'charset' => 'utf8',
  'pconnect' => false,
  'showerror' => true
  );

  // A count of the number of queries.
  var $query_count = 0;

  // A list of the performed queries.
  var $query_list = array();

  // SQL link identifier on success or FALSE on failure.
  var $db_link = false;

  // SQL query string
  var $sql = '';

  /***********************************************************************
  * Open a new connection to the SQL server
  * @param	array : database connection details
  ***********************************************************************/
  function __construct($config)
  {
    // set_error_handler(array($this, 'db_error'));
    $this->config = array_merge($this->config, $config['db']);

    if ($this->config['pconnect'])
    {
      $this->db_link = @mysql_pconnect ($this->config['servername'].':'.$this->config['port'],$this->config['username'],$this->config['password']);
    }
    else
    {
      $this->db_link = @mysql_connect ($this->config['servername'].':'.$this->config['port'],$this->config['username'],$this->config['password']);
    }

    if(!$this->db_link)
    {
      $this->halt('Can not connect to MySQL server');
    }

    if (function_exists('mysql_set_charset'))
    {
      mysql_set_charset($this->config['charset'], $this->db_link);
    }
    else
    {
      $this->query("SET NAMES ".$this->config['charset']);
    }

    mysql_select_db($this->config['database'], $this->db_link);
  }

  /***********************************************************************
  * Send a SQL query
  * @param	string : SQL query
  ***********************************************************************/
  function query ($query)
  {
    if (!$this->db_link)
    {
      return false;
    }

    $this->query_count++;
    $this->sql = preg_replace('#\s+#', ' ', $query);
    $this->query_list[] = $this->sql;
    $queryresult = mysql_query($query, $this->db_link);

    if ($queryresult)
    {
      return $queryresult;
    }
    else
    {
      $this->halt();
    }
  }

  /***********************************************************************
  * Send a SQL query
  * @param	string : SQL query
  ***********************************************************************/
  function query_first ($query)
  {
    $queryresult = $this->query($query);
    $returnarray = $this->fetch_array($queryresult);
    $this->free_result($queryresult);
    return $returnarray;
  }

  /***********************************************************************
  * Send an SQL query without fetching and buffering the result rows.
  * @param	string : SQL query
  ***********************************************************************/
  function query_unbuffered ($query)
  {
    $this->query_count++;
    $this->sql = preg_replace('#\s+#', ' ', $query);
    $this->query_list[] = $this->sql;
    $queryresult = mysql_unbuffered_query($query, $this->db_link);

    if ($queryresult)
    {
      return $queryresult;
    }
    else
    {
      $this->halt();
    }
  }

  /***********************************************************************
  * Executes an INSERT INTO query
  * @param	string : Table name
  * @param	string : Values
  ***********************************************************************/
  function query_insert($table, $array_values = array())
  {
    $count = 0;
    foreach($array_values as $key => $value)
    {
      $count++;

      $fields .= ($count > 1 ? ',':'') . '\''.$key.'\'';
      $values .= ($count > 1 ? ',':'') . '\''.$this->real_escape_string($value).'\'';
    }
    $queryresult = $this->query("INSERT INTO ".$this->config['prefix']."$table ($fields) VALUES ($values)");
    return $this->insert_id();
  }

  /***********************************************************************
  * Executes an update query
  * @param	string : Table name
  * @param	string : Values
  * @param	string : Where
  ***********************************************************************/
  function query_update($table, $array_values = array(), $where="")
  {
    $count = 0;
    foreach($array_values as $key => $value)
    {
      $count++;
      $values .= ($count > 1 ? ',':'') . '\''.$key.'\' = ' . '\''.$this->real_escape_string($value).'\'';
    }
    $queryresult = $this->query("UPDATE ".$this->config['prefix']."$table set $values ".($where ? " WHERE $where":""));
    return $queryresult;
  }

  /***********************************************************************
  * Executes an DELETE query
  * @param	string : Table name
  * @param	string : Where
  * @param	string : Limit
  ***********************************************************************/
  function query_delete($table, $where="", $limit="")
  {
    $queryresult = $this->query("DELETE FROM ".$this->config['prefix']."$table".($where ? " WHERE $where":"").($limit ? " LIMIT $limit":""));
    return $queryresult;
  }

  /***********************************************************************
  * Fetch a result row as an associative array, a numeric array, or both
  * @param	string : The result resource that is being evaluated.
  * @param	num : The type of array that is to be fetched.
  ***********************************************************************/
  function fetch_array ($queryresult, $type=DB_ASSOC)
  {
    return mysql_fetch_array($queryresult, $type);
  }

  /***********************************************************************
  * Get number of rows in result
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function num_rows ($queryresult)
  {
    return mysql_num_rows($queryresult);
  }

  /***********************************************************************
  * Get the ID generated in the last query
  ***********************************************************************/
  function insert_id()
  {
    return mysql_insert_id($this->db_link);
  }

  /***********************************************************************
  * Escapes a string for use in a sql_query
  * @param	string : The string that is to be escaped.
  ***********************************************************************/
  function escape_string($string)
  {
    return mysql_escape_string($string);
  }

  /***********************************************************************
  * Escapes special characters in a string for use in an SQL statement
  * @param	string : The string that is to be escaped.
  ***********************************************************************/
  function real_escape_string($string)
  {
    return mysql_real_escape_string($string,$this->db_link);
  }

  /***********************************************************************
  * Free result memory
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function free_result($queryresult)
  {
    $this->sql = '';
    mysql_free_result($queryresult);
  }

  /***********************************************************************
  * numerical value of the error message from previous SQL operation
  ***********************************************************************/
  function errno()
  {
    return mysql_errno();
  }

  /***********************************************************************
  * Returns the text of the error message from previous SQL operation
  ***********************************************************************/
  function error()
  {
    return mysql_error();
  }

  /***********************************************************************
  * Get number of affected rows in previous SQL operation
  ***********************************************************************/
  function affected_rows()
  {
    return mysql_affected_rows($this->db_link);
  }

  /***********************************************************************
  * Move internal result pointer
  * @param	string : The result resource that is being evaluated.
  * @param	num : The desired row number of the new result pointer.
  ***********************************************************************/
  function data_seek($queryresult, $row_number)
  {
    return mysql_data_seek($queryresult, $row_number);
  }

  /***********************************************************************
  * Fetch a result row as an associative array
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_assoc($queryresult)
  {
    return mysql_fetch_assoc($queryresult);
  }

  /***********************************************************************
  * Fetch a result row as an object
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_object($queryresult)
  {
    return mysql_fetch_object($queryresult);
  }

  /***********************************************************************
  * Get a result row as an enumerated array
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_row($queryresult)
  {
    return mysql_fetch_row($queryresult);
  }

  /***********************************************************************
  * Get a result row as an enumerated array
  * @param	string : The result resource that is being evaluated.
  * @param	num : The numerical field offset.
  ***********************************************************************/
  function fetch_field($queryresult, $field_offset = 0)
  {
    return mysql_fetch_field($queryresult, $field_offset);
  }

  /***********************************************************************
  * Set result pointer to a specified field offset
  * @param	string : The result resource that is being evaluated.
  * @param	num : The numerical field offset.
  ***********************************************************************/
  function field_seek($queryresult, $field_offset = 0)
  {
    return mysql_field_seek($queryresult, $field_offset);
  }

  /***********************************************************************
  * Get MySQL client info
  ***********************************************************************/
  function get_client_info()
  {
    return mysql_get_client_info();
  }

  /***********************************************************************
  * Get information about the most recent query
  ***********************************************************************/
  function info()
  {
    return mysql_info($this->db_link);
  }

  /***********************************************************************
  * Ping a server connection or reconnect if there is no connection
  ***********************************************************************/
  function ping()
  {
    return mysql_ping($this->db_link);
  }

  /***********************************************************************
  * Get current system status
  ***********************************************************************/
  function stat()
  {
    return mysql_stat($this->db_link);
  }

  /***********************************************************************
  * Return the current thread ID
  ***********************************************************************/
  function thread_id()
  {
    return mysql_thread_id($this->db_link);
  }

  /***********************************************************************
  * error handler function
  ***********************************************************************/
  function close()
  {
    return mysql_close($this->db_link);
  }

  /***********************************************************************
  * sql cause of the error shows on the screen
  * @param	string : Error text.
  * @param	string : sql query string.
  ***********************************************************************/
  function halt($errortext = '', $sql = '')
  {
    if (!$this->config['showerror'])
    {
      return true;
    }

    if ($sql or $this->sql)
    {
      $errortext = "Invalid SQL: ".$errortext."\r<br />" . chop($this->sql) . ';';
    }

    $text_errno = $this->errno();
    $text_error = $this->error();

    $date = date('l, F jS Y @ h:i:s A');
    
    $messagex = '<div style="width:600px;padding:10px;border:5px solid gray;">';
    
    $messagex .= str_replace(array('<', '>', '"','&lt;br /&gt;'),array('&lt;', '&gt;', '&quot;', '<br />'), $errortext)."<br /><br />";
    if ($text_error or $text_errno)
    {
      $messagex .= "MySQL Error : $text_error<br />";
      $messagex .= "Error Number : $text_errno<br />";
    }

    $messagex .= "Error Date : $date<br />";
    $messagex .= "</div>";

    if ($this->db_link)
    {
      $this->close();
    }

    die($messagex);
  }
}
?>