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

class woteo_filter
{
  var $config = array(
  'expire' => 31536000,
  'path' => '/',
  'domain' => '',
  'secure' => false,
  'httponly', => false,
  'prefix' => 'woteo_');
  
  var $stripslashes_counter = 0;
  var $vars = array();
  
  function __construct($config)
  {
    $this->config = array_merge ($this->config,config['cookies']);

    if (version_compare(phpversion(), '5.4', '<'))
    {
      if (get_magic_quotes_gpc()) 
      {
        stripslashes_array($_GET);
        stripslashes_array($_POST);
        stripslashes_array($_COOKIE);
        stripslashes_array($_REQUEST);
      }
    }
  }

  /***********************************************************************
   * Un-quotes a quoted string
   * @param	array : The input string
   ***********************************************************************/  
  function stripslashes_array(&$value, $sslashes = true)
	{
    $this->stripslashes_counter++;
    
    if ($this->stripslashes_counter > 1000)
    {
      die('strip slashes attack');
    }
    
		foreach($value as $key => $val)
		{
			if(is_array($value[$key]))
			{
				$this->stripslashes_array($value[$key]);
			}
			else
			{
				$value[$key] = trim(stripslashes($val));
			}
		}
	}
    
  /***********************************************************************
   * Send a cookie without urlencoding the cookie value
   * @param	string : The name of the cookie.
   * @param	string : The value of the cookie.
   * @param	integer : The time the cookie expires.      
   ***********************************************************************/
  function cookie_putraw ($name, $value, $expire = -1)
  {
    return $this->put($name, $value, $expire, true); 
  }
  
  /***********************************************************************
   * Send a cookie
   * @param	string : The name of the cookie.
   * @param	string : The value of the cookie.
   * @param	integer : The time the cookie expires.      
   ***********************************************************************/
  function cookie_put ($name, $value, $expire = -1, $raw = false)
  {
    if ($expire == -1)
    {
      $expire = time()+ $this->config['expire'];
    }
    else
    {
      $expire = time() + intval($expire);
    }
    if ($raw)
    {
      $result = setrawcookie($this->config['prefix'] . $name, $value,$expire,$this->config['path'],$this->config['domain'],$this->config['secure'],$this->config['httponly']);
    }
    else
    {
      $result = setcookie($this->config['prefix'] . $name, $value,$expire,$this->config['path'],$this->config['domain'],$this->config['secure'],$this->config['httponly']);
    }
    return $result; 
  }

  /***********************************************************************
   * Get Cookie
   * @param	string : cookie name
   ***********************************************************************/	
  function cookie_get ($name)
  {
    return $_COOKIE[$this->config['prefix'] . $name];
  }

  /***********************************************************************
   * Delete cookie
   * @param	string : The name of the cookie.
   ***********************************************************************/
  function cookie_delete($name)
  {
    $expires = -3600;
    $result = setcookie($this->config['prefix'] . $name, '',$expire,$this->config['path'],$this->config['domain'],$this->config['secure'],$this->config['httponly']);
    return $result; 
  }
  
  /***********************************************************************
   * Gets a specific external variable by name and optionally filters it
   * Type : Input GET   
   * @param	string : Name of a variable to get.
   * @param	number : Types of filters    
   ***********************************************************************/ 
  function input_get ($name, $filter = 0)
  {
    return filter_input(INPUT_GET, $name , $filter);
  }

  /***********************************************************************
   * Gets a specific external variable by name and optionally filters it
   * Type : Input POST   
   * @param	string : Name of a variable to get.
   * @param	number : Types of filters	
   ***********************************************************************/ 
  function input_post ($name, $filter = 0)
  {
    return filter_input(INPUT_POST, $name, $filter);
  }

  /***********************************************************************
   * Gets a specific external variable by name and optionally filters it
   * Type : Input COOKIE   
   * @param	string : Name of a variable to get.
   * @param	number : Types of filters	
   ***********************************************************************/ 
  function input_cookie ($name, $filter = 0)
  {
    return filter_server(INPUT_COOKIE, $name, $filter);
  }

  /***********************************************************************
   * Gets a specific external variable by name and optionally filters it
   * Type : Input ENV   
   * @param	string : Name of a variable to get.
   * @param	number : Types of filters	
   ***********************************************************************/ 
  function input_env ($name, $filter = 0)
  {
    return filter_input(INPUT_ENV, $name, $filter);
  }

  /***********************************************************************
   * Gets a specific external variable by name and optionally filters it
   * Type : Input SERVER   
   * @param	string : Name of a variable to get.
   * @param	number : Types of filters	
   ***********************************************************************/ 
  function input_server ($name, $filter = 0)
  {
    return filter_input(INPUT_SERVER, $name, $filter);
  }

  /***********************************************************************
   * Filter validates value as an e-mail address.
   * @param	string : Email address
   ***********************************************************************/ 
  function var_email ($email)
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  /***********************************************************************
   * Validate value as IP address, optionally only IPv4 or 
   * IPv6 or not from private or reserved ranges
   * @param string : Ip address
   * @param number : Ip Flag IPV4-IPV6
   ***********************************************************************/ 
  function var_ip ($var, $ipflag = FILTER_FLAG_IPV4)
  {
    return filter_var($var, FILTER_VALIDATE_IP, $ipflag);
  }

  /***********************************************************************
   * Validate value as integer, optionally from the specified range
   * @param number : value
   * @param number : min value
   * @param number : max value       	
   ***********************************************************************/ 
  function var_int ($var, $min, $max)
  {
    $int_options = array("options"=>
    array("min_range"=>$min, "max_range"=>$max));
    
    return filter_var($var, FILTER_VALIDATE_INT, $int_options);
  }

  /***********************************************************************
   * Filter validates value as a boolean option.
   * @param	boolean : value
   ***********************************************************************/ 
  function var_boolean ($var)
  {
    return filter_var($var, FILTER_VALIDATE_BOOLEAN);
  }

  /***********************************************************************
   * Validate value as float
   * @param	number : value
   ***********************************************************************/ 
  function var_float ($var)
  {
    return filter_var($var, FILTER_VALIDATE_FLOAT);
  }

  /***********************************************************************
   * Validate value against regexp, a Perl-compatible regular expression
   * @param string : Text
   * @param string : regexp value   	
   ***********************************************************************/ 
  function var_regexp ($var, $regexp)
  {
    $regexp_options = array("options"=>array("regexp"=>$regexp));
    
    return filter_var($var, FILTER_VALIDATE_REGEXP, $regexp_options);
  }

  /***********************************************************************
   * Validate value as URL, optionally with required components
   * @param string : url address
   ***********************************************************************/ 
  function var_url ($var)
  {
    return filter_var($var, FILTER_VALIDATE_URL);
  }

  /***********************************************************************
   * Strip tags, optionally strip or encode special characters
   * @param string	
   ***********************************************************************/
  function var_string ($var)
  {
    return filter_var($var, FILTER_SANITIZE_STRING);
  }

  /***********************************************************************
   * URL-encode string, optionally strip or encode special characters
   * @param string
   ***********************************************************************/
  function var_encoded ($var)
  {
    return filter_var($var,FILTER_SANITIZE_ENCODED);
  }

  /***********************************************************************
   * HTML-escape '"<>& and characters with ASCII value less than 32
   * @param string	
   ***********************************************************************/
  function var_special_chars ($var)
  {
    return filter_var($var,FILTER_SANITIZE_SPECIAL_CHARS);
  }

  /***********************************************************************
   * XSS Cleaner 
   * @param	string : text.
   ***********************************************************************/
  function xss_clean($str)
  {
    $words = array(
    'javascript' => 'java script',
    'vbscript' => 'vb script', 
    'base64' => 'base 64',
    'applet' => 'app let',
    'alert' => 'al ert',
    'cookie' => 'coo kie',
    'window' => 'win dow');
    $str = strtr($str, $words);
    $str = htmlspecialchars($str, ENT_QUOTES);  
  }                      
}
?>
