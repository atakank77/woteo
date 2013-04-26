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

// cache setting
// apc, eaccelerator, file, wincache, xcache, zend_file, zend_shm
$config['cache']['type'] = 'file'; 
$config['cache']['url'] = 'cache';
$config['cache']['uniqueid'] = 'ddec3d86-bc07-47dd-8704-08d8fc7ac3b3';

// database setting
$config['db']['type'] = 'mysqli';
$config['db']['servername'] = 'localhost';
$config['db']['port'] = '3306';
$config['db']['username'] = 'root';
$config['db']['password'] = '';
$config['db']['database'] = 'woteo';
$config['db']['prefix'] = 'woteo_';
$config['db']['charset'] = 'utf8';
$config['db']['pconnect'] = false;
$config['db']['showerror'] = true;

// cookies setting
$config['cookies']['prefix'] = 'woteo_';
$config['cookies']['path'] = '/';
$config['cookies']['domain'] = '.domain.com';

?>
