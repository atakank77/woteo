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

class woteo_html
{

  function oparse ($options)
  {
    if (is_array($options))
    {
      return '';
    }  
    foreach($options as $key => $value)
    {
      $count++;
      $results[] = $key . ' = "'.$value.'"';
    }
    $result = implode(" ", $results);
    return $result;
  }
  
  /***********************************************************************
   * Defines a clickable button (mostly used with a JavaScript to activate a script)
   * @param
   ***********************************************************************/ 
  function input ($type, $value, $name, $options = '')
  {
    switch ($type) 
    {
      case 'button':
        $result = '<input type="button" value="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'checkbox':
        $result = '<input type="checkbox" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;

      case 'color':
        $result = '<input type="color" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;

      case 'datetime':
        $result = '<input type="datetime" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'datetime-local':
        $result = '<input type="datetime-local" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;

      case 'file':
        $result = '<input type="file" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;

      case 'hidden':
        $result = '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'image':
        $result = '<input type="image" src="'.$name.'" alt="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'month':
        $result = '<input type="month" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'number':
        $result = '<input type="number" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;

      case 'password':
        $result = '<input type="password" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;
      
      case 'radio':
        $result = '<input type="radio" name="'.$name.'" value="'.$value.'"'.$this->oparse($options).' />';
      break;                        
    } 
  }
}
?>
