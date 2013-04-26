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

/* ----------------------------------------------------------------------------
 * Alternative PHP Cache (APC)
 * ----------------------------------------------------------------------------
 * Alternative PHP Cache is a free, open source (PHP license) framework that
 * optimizes PHP intermediate code and caches data and compiled code from the 
 * PHP bytecode compiler in shared memory.
 * Website: http://pecl.php.net/package/APC
 * PHP version: works with all PHP versions including PHP 5.2-5.4
 * ----------------------------------------------------------------------------
 * eAccelerator
 * ----------------------------------------------------------------------------
 * eAccelerator was born in December 2004 as a fork of the Turck MMCache
 * project. Turck MMCache was created by Dmitry Stogov and much of the 
 * eAccelerator code is still based on his work. eAccelerator also contained 
 * a PHP encoder and loader, but the development staff discontinued the 
 * encoder and removed this feature after December 2006.   
 * Website: http://eaccelerator.net/
 * PHP version: Supports PHP 4 and all PHP 5 thread-safe releases
 * including 5.3 from version 0.9.6; breaks on 5.4. In older releases, 
 * the encoder will only work with PHP versions from the 4.x.x branch. 
 * eAccelerator will not work with any other versions of PHP. eAccelerator 
 * can only be used with the thread-safe version of PHP.
 * ---------------------------------------------------------------------------- 
 * Windows Cache Extension for PHP      
 * ----------------------------------------------------------------------------
 * A free, open source (New BSD License), PHP accelerator developed by 
 * Microsoft for PHP under Windows. The extension includes PHP opcode 
 * cache, file cache, resolve file path cache, object/session cache, 
 * file change notifications and lock/unlock API's. Combination of all 
 * these caches results in significant performance improvements for PHP 
 * applications hosted on Windows. The extension is primarily used with 
 * Internet Information Services and non-thread-safe build of PHP via 
 * FastCGI protocol.
 * Website: http://www.iis.net/expand/WinCacheForPHP
 * PHP version: works with PHP 5.2 (VC6 NTS) and 5.3 (VC9 NTS)
 * ---------------------------------------------------------------------------- 
 * XCache
 * ----------------------------------------------------------------------------
 * XCache is a fast, stable PHP opcode cacher that has been tested and is 
 * now running on production servers under high load. It is tested on Linux 
 * and FreeBSD and supported under Windows, for thread-safe and 
 * non-thread-safe versions of PHP. This relatively new opcode caching 
 * software has been developed by mOo, one of developers of Lighttpd,
 * to overcome some of the limitations of the existing solutions at that 
 * time; such as being able to use it with new PHP versions as they arrive.
 * Website: http://xcache.lighttpd.net/
 * PHP version: full support for PHP 5.4
 * ---------------------------------------------------------------------------- 
 * Zend Platform
 * ----------------------------------------------------------------------------
 * Zend Platform (formerly Zend Cache and then Zend Accelerator) is a 
 * commercial Web Application Server product. It has a complete set of 
 * performance capabilities that includes more than a simple PHP accelerator. 
 * Features include code caching/acceleration, data caching, content 
 * (html output) caching, download optimization and off-line (asynchronous) 
 * processing capabilities that can result in significant performance 
 * improvements for most PHP applications. It also includes detailed PHP 
 * monitoring and root cause analysis support to help in tuning and 
 * debugging, session fail-over support for HA (High Availability) 
 * needs and other integration capabilities including Java integration.
 * Website: http://www.zend.com/products/platform
 * ----------------------------------------------------------------------------      
 */
 
class woteo_cache
{
  var $config = array();
  var $cache = array();
  var $cache_enable = false;
  var $unique_id = '';
  var $cache_link = null;
  
  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config['cache'];
    $this->unique_id = md5($this->config['uniqueid']);

		if(function_exists("apc_store") and $this->config['type'] == 'apc') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_apc($this->config);
		}
		elseif(function_exists("eaccelerator_get") and $this->config['type'] == 'eaccelerator') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_eaccelerator($this->config);
		}
		elseif(is_writable($this->config['cache_dir']) and $this->config['type'] == 'file') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_file($this->config);
		}
		elseif(function_exists("wincache_ucache_get") and $this->config['type'] == 'wincache') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_wincache($this->config);
		}
		elseif(function_exists("xcache_get") and $this->config['type'] == 'xcache') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_xcache($this->config);
		}
		elseif(function_exists("zend_file_cache_store") and $this->config['type'] == 'zend_file') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_zend_file($this->config);
		}
		elseif(function_exists("zend_shm_cache_store") and $this->config['type'] == 'zend_shm') {
      $this->cache_enable = true;
      $this->cache_link = new woteo_cache_zend_shm($this->config);
		}
    else {
      $this->cache_enable = false;
    }
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    if ($this->cache_enable)
    {
      $result = $this->cache_link->fetch($name);
      $this->cache[$name] = $result;
      return $result;
    }
    else
    {
      return false;
    }  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    if ($this->cache_enable)
    {
      $result = $this->cache_link->build($name, $data);
      $this->cache[$name] = $data;
      return $result;
    }
    else
    {
      return false;
    }
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    if ($this->cache_enable)
    {
      $result = $this->cache_link->delete($name);
      return $result;
    }
    else
    {
      return false;
    }
	}

  /***********************************************************************
  * Get Cache
  * @param	string : cache name
  ***********************************************************************/  
  function get_cache($name)
  {
    if (isset($this->cache[$name]))
    {
      return $this->cache[$name];
    }
    else
    {
      return false;
    }  
  }
  
  /***********************************************************************
  * Cache Disconnect
  ***********************************************************************/
	function disconnect()
	{
		return true;
	}
} 

class woteo_cache_apc
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = apc_fetch($this->unique_id."_".$name);
    return unserialize($result);  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    return apc_store($this->unique_id."_".$name, serialize($data));
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return apc_delete($this->unique_id."_".$name);s
	}  
} 
 
class woteo_cache_eaccelerator
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = eaccelerator_get($this->unique_id."_".$name);
    return unserialize($result);  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    eaccelerator_lock($this->unique_id."_".$name);
    $result = eaccelerator_put($this->unique_id."_".$name, serialize($data));
    eaccelerator_unlock($this->unique_id."_".$name);
    return $result;
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return eaccelerator_rm($this->unique_id."_".$name);
	}  
}
  
class woteo_cache_wincache
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = wincache_ucache_get($this->unique_id."_".$name);
    return unserialize($result);  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    return wincache_ucache_add($this->unique_id."_".$name, serialize($data));
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return wincache_ucache_delete($this->unique_id."_".$name);
  }
}
 
class woteo_cache_xcache
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = xcache_get($this->unique_id."_".$name);
    return unserialize($result);  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    return xcache_set($this->unique_id."_".$name, serialize($data));
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return xcache_unset($this->unique_id."_".$name);
	}  
}
  
class woteo_cache_zend_file
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = zend_file_cache_fetch($this->unique_id."_".$name);
    return unserialize($result); 
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    return zend_file_cache_store($this->unique_id."_".$name, serialize($data));
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return zend_file_cache_delete($this->unique_id."_".$name);
	} 
}

class woteo_cache_zend_shm
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    $result = zend_shm_cache_fetch($this->unique_id."_".$name);
    return unserialize($result);  
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    return zend_shm_cache_store($this->unique_id."_".$name, serialize($data));
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return zend_shm_cache_delete($this->unique_id."_".$name);
	}  
}

class woteo_cache_file
{
  var $config = array();
  var $unique_id = '';

  /***********************************************************************
  * Cache Start
  * @param	array : cache connection details
  ***********************************************************************/  
  function __construct($config)
  {
    $this->config = $config;
    $this->unique_id = md5($this->config['uniqueid']);
  }

  /***********************************************************************
  * Fetches the contents of cache
  * @param	string : cache name
  ***********************************************************************/  
  function fetch($name)
  {
    if(file_exists($this->config['url'].'/'.$name.'.php'))
    {
      require_once($this->config['url'].'/'.$name.'.php');
      return $$name;
    }
    else
    {
      return false;
    }
  }
  
  /***********************************************************************
  * cache updates or new cache
  * @param	string : cache name
  * @param	array : data content  
  ***********************************************************************/  
  function build($name, $data)
  {
    $cache_temp = fopen($this->config['url'].'/'.$name.'.php', "w");
    flock($cache_temp, LOCK_EX);
    $contents .= "<?php\n\n";
    $contents .= "// Woteo Cache File : ".$name."\n\n";
    $contents .= "\$$name = ".var_export($data, true).";\n\n ?".">";
    fwrite($cache_temp, $contents);
    flock($cache_temp, LOCK_UN);
    fclose($cache_temp);
    return true;
  }

  /***********************************************************************
  * cache delete
  * @param	string : cache name
  ***********************************************************************/  
	function delete($name)
	{
    return unlink($this->config['url'].'/'.$name.'.php');
	} 
}

?>
