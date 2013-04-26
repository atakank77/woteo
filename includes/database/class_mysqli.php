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
  'sqltype' => 'mysqli',
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

    if(!$this->db_link = @mysqli_connect($this->config['servername'].':'.$this->config['port'],$this->config['username'],$this->config['password'], $this->config['database']))
    {
      $this->halt('Can not connect to MySQLi server');
    }

    if (function_exists('mysqli_set_charset'))
    {
      mysqli_set_charset($this->db_link, $this->config['charset']);
    }
    else
    {
      $this->query("SET NAMES ".$this->config['charset']);
    }
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
    $queryresult = mysqli_query($this->db_link, $query);

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
    $queryresult = mysqli_query($this->db_link, $query, MYSQLI_USE_RESULT);

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
    return mysqli_fetch_array($queryresult, $type);
  }

  /***********************************************************************
  * Get number of rows in result
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function num_rows ($queryresult)
  {
    return mysqli_num_rows($queryresult);
  }

  /***********************************************************************
  * Get the ID generated in the last query
  ***********************************************************************/
  function insert_id()
  {
    return mysqli_insert_id($this->db_link);
  }

  /***********************************************************************
  * Escapes a string for use in a sql_query
  * @param	string : The string that is to be escaped.
  ***********************************************************************/
  function escape_string($string)
  {
    return mysqli_escape_string($this->db_link,$string);
  }

  /***********************************************************************
  * Escapes special characters in a string for use in an SQL statement
  * @param	string : The string that is to be escaped.
  ***********************************************************************/
  function real_escape_string($string)
  {
    return mysqli_real_escape_string($this->db_link,$string);
  }

  /***********************************************************************
  * Free result memory
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function free_result($queryresult)
  {
    $this->sql = '';
    mysqli_free_result($queryresult);
  }

  /***********************************************************************
  * numerical value of the error message from previous SQL operation
  ***********************************************************************/
  function errno()
  {
    return $this->db_link ? mysqli_errno($this->db_link) : false;
  }

  /***********************************************************************
  * Returns the text of the error message from previous SQL operation
  ***********************************************************************/
  function error()
  {
    return $this->db_link ? mysqli_error($this->db_link) : false;
  }

  /***********************************************************************
  * Get number of affected rows in previous SQL operation
  ***********************************************************************/
  function affected_rows()
  {
    return mysqli_affected_rows($this->db_link);
  }

  /***********************************************************************
  * Move internal result pointer
  * @param	string : The result resource that is being evaluated.
  * @param	num : The desired row number of the new result pointer.
  ***********************************************************************/
  function data_seek($queryresult, $row_number)
  {
    return mysqli_data_seek($queryresult, $row_number);
  }

  /***********************************************************************
  * Fetch a result row as an associative array
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_assoc($queryresult)
  {
    return mysqli_fetch_assoc($queryresult);
  }

  /***********************************************************************
  * Fetch a result row as an object
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_object($queryresult)
  {
    return mysqli_fetch_object($queryresult);
  }

  /***********************************************************************
  * Get a result row as an enumerated array
  * @param	string : The result resource that is being evaluated.
  ***********************************************************************/
  function fetch_row($queryresult)
  {
    return mysqli_fetch_row($queryresult);
  }

  /***********************************************************************
  * Get a result row as an enumerated array
  * @param	string : The result resource that is being evaluated.
  * @param	num : The numerical field offset.
  ***********************************************************************/
  function fetch_field($queryresult, $field_offset = 0)
  {
    return mysqli_fetch_field($queryresult, $field_offset);
  }

  /***********************************************************************
  * Set result pointer to a specified field offset
  * @param	string : The result resource that is being evaluated.
  * @param	num : The numerical field offset.
  ***********************************************************************/
  function field_seek($queryresult, $field_offset = 0)
  {
    return mysqli_field_seek($queryresult, $field_offset);
  }

  /***********************************************************************
  * Get MySQL client info
  ***********************************************************************/
  function get_client_info()
  {
    return mysqli_get_client_info($this->db_link);
  }

  /***********************************************************************
  * Get information about the most recent query
  ***********************************************************************/
  function info()
  {
    return mysqli_info($this->db_link);
  }

  /***********************************************************************
  * Ping a server connection or reconnect if there is no connection
  ***********************************************************************/
  function ping()
  {
    return mysqli_ping($this->db_link);
  }

  /***********************************************************************
  * Get current system status
  ***********************************************************************/
  function stat()
  {
    return mysqli_stat($this->db_link);
  }

  /***********************************************************************
  * Return the current thread ID
  ***********************************************************************/
  function thread_id()
  {
    return mysqli_thread_id($this->db_link);
  }

  /***********************************************************************
  * error handler function
  ***********************************************************************/
  function close()
  {
    return mysqli_close($this->db_link);
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
    $message = '<div style="width:600px;padding:10px;border:5px solid gray;">';
    $message .= str_replace(array('<', '>', '"','&lt;br /&gt;'),array('&lt;', '&gt;', '&quot;', '<br />'), $errortext)."<br /><br />";
    if ($text_error or $text_errno)
    {
      $message .= "MySQL Error : $text_error<br />";
      $message .= "Error Number : $text_errno<br />";
    }

    $message .= "Error Date : $date<br />";
    $message .= "</div>";

    if ($this->db_link)
    {
      $this->close();
    }

    die($message);
  }
}
?>