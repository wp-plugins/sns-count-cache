<?php
/*
class-data-crawler.php

Description: This class is abstract class of a data crawler
Version: 0.3.0
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

/*

Copyright (C) 2014 Daisuke Maruyama

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
	 * Instance
	 */
  	private static $instance = array();  

  	/**
	 * Get instance
	 *
	 * @since 0.1.1
	 */	        	
  	public static function get_instance() {

	  	$class_name = get_called_class();
	  
		if( ! isset( self::$instance[$class_name] ) ) {
			self::$instance[$class_name] = new $class_name();
		  	//self::$instance[ $c ]->init($crawler, $options=array());
		}

		return self::$instance[$class_name];
	}
  
  	/**
	 * Set URL for data crawling
	 *
	 * @since 0.1.0
	 */	      
	public function set_url( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$this->url = rawurlencode( $url );
	}
  
  	/**
	 * Abstract method for data crawling
	 *
	 * @since 0.1.1
	 */  
	abstract public function get_data( $cache_target, $url );  
  
  	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */	    
	protected function log( $message ) {
    	if ( WP_DEBUG === true ) {
      		if ( is_array( $message ) || is_object( $message ) ) {
        		error_log( print_r( $message, true ) );
      		} else {
        		error_log( $message );
      		}
    	}
  	}
}

?>