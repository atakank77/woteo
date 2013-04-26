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
define('URL', (($getcwd = getcwd()) ? $getcwd : '.'));
define('TIMESTART', microtime(true));
define('TIMENOW', time());
define('DB_PREFIX', trim($config['database']['prefix']));
define('IPADDRESS', getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') : getenv('REMOTE_ADDR'));
define('USERAGENT', trim(strtolower($_SERVER['HTTP_USER_AGENT'])));
define('SAPINAME', php_sapi_name());
define('REFERRER', $_SERVER['HTTP_REFERER']);

class woteo_core
{
  var $cache = null;
  var $db = null;
  var $ci = null;
  var $config = array();
  var $data = array();
  
  function __construct($config) 
  {
    $this->config = $config;
    // require_once(URL . '/includes/cache/cache_'.$this->config['cache']['type'].'.php');
    // $this->cache = new woteo_cache($this->config);
    require_once(URL . '/includes/database/class_'.$this->config['db']['type'].'.php');
    $this->db = new database($this->config);
    require_once(URL . '/includes/class_input.php');
    $this->ci = new clean_input($this->config);
    $this->data = $this->get_data(USERAGENT);
  }

  /***********************************************************************
   * Get browser
   * @param	string : User agent string   
   ***********************************************************************/
  function get_browser($useragent = '')
  {
    if (!$useragent)
    {
      $useragent = USERAGENT;  
    }
    
    $browser_list = array (
    'flock','firefox','chrome',
    'opera','msie','camino',
    'netscape','safari','mozilla',
    'konqueror','maxthon','',
    '','','',
    
    );

  	preg_match_all ('#('. join("|", $browser_list) .')[/ ]+([0-9]+(?:\.[0-9]+)?)#', $useragent, $matches);
    $temp_data['browser_name'] = $matches[0][0];
    $temp_data['browser'] = $matches[1][0]; 
    $temp_data['browser_version'] = $matches[2][0];
    return $temp_data;
  }
  
  /***********************************************************************
   * Get Data
   ***********************************************************************/
  function get_data($useragent)
  {                
      
    $operating_systems = array(
    'windows', 'macintosh', 'linux', 'freebsd', 'unix', 'iphone', 'android'
    );
    
    preg_match('#'. join("|", $operating_systems) .'#', $useragent, $match);
    $temp_data['operating_system'] = $match[0];

    $mobilebrowsers = array(
    '240x320', '320x240', 'android', 'avantgo', 'bada', 'blackberry',
    'blackberry', 'blazer', 'chaifarer', 'cupcake', 'danger', 'digital',
    'elaine', 'epoc', 'froyo', 'handheld', 'incognito', 'iphone',
    'ipod', 'j2me', 'lynx', 'maemo', 'mazingo', 'midp', 'mobile',
    'netfront', 'nokia', 'palmos', 'proxinet', 'psp',
    'samsung', 'smartphone', 'symbian', 'symbianos', 'syncalot',
    't68', 'webos', 'webmate', 'webtv', 'wireless', 'xiino'
    );
    
    if(preg_match('/('.implode('|', $mobilebrowsers).')/i', $useragent, $match))
    {
      $temp_data['mobile'] = $match[0];
    }
    
    return $temp_data;
  }

	function timer_gettime()
	{
		$currenttime = microtime(true);
		$totaltime = $currenttime - TIMESTART;
		return number_format($totaltime, 4);
	}
}

require_once(URL . '/includes/config.php');

$woteo = new woteo_core($config);  
?>