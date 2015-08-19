<?php
/*
class-data-crawler.php

Description: This class is abstract class of a data crawler
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

/*

Copyright (C) 2014 - 2015 Daisuke Maruyama

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

abstract class Data_Crawler {
  
  	/**
	 * URL for data crawling
	 */	    
	protected $url = '';

  	/**
	 * method to crawl
	 */	      
  	protected $crawl_method = 1;
  
  	/**
	 * Timeout
	 */	      
  	protected $timeout = 10;  	

  	/**
	 * ssl verification
	 */	      
  	protected $ssl_verification = true;  
    
  	/**
	 * retry flag
	 */
  	protected $crawl_retry = false;

  	/**
	 * limit of crawl retry
	 */	     
  	protected $retry_limit = 2;
  
  	/**
	 * Instance
	 */
  	private static $instance = array();  

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
	  	Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  	//$this->get_object_id();
	}  
  
  	/**
	 * Get instance
	 *
	 * @since 0.1.1
	 */	        	
  	public static function get_instance() {

	  	$class_name = get_called_class();
	  
		if( ! isset( self::$instance[$class_name] ) ) {
			self::$instance[$class_name] = new $class_name();
		}

		return self::$instance[$class_name];
	}

    /**
     * Return object ID
     *
	 * @since 0.6.0
	 */	  
  	public function get_object_id() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$object_id = spl_object_hash( $this );
	  
	  	Common_Util::log( '[' . __METHOD__ . '] object ID: ' . $object_id );
	  
	  	return $object_id;
  	}  
  
    /**
     * Inhibit clone
     *
	 * @since 0.6.0
	 */	  
  	final public function __clone() {
	  	throw new Exception('Clone is not allowed against' . get_class( $this ) ); 
  	}    

  	/**
	 * Initialization
	 *
	 * @since 0.6.0
	 */
  	abstract public function initialize( $options = array() );
  
  	/**
	 * Abstract method for data crawling
	 *
	 * @since 0.1.1
	 */  
	abstract public function get_data( $target_sns, $url );  
  
}

?>