<?php
/*
class-engine.php

Description: This class is a engine
Version: 0.4.0
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


abstract class Engine {
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	protected $prime_cron = NULL;

	/**
	 * Cron name to execute cache processing
	 */	        
  	protected $execute_cron = NULL;

	/**
	 * Schedule name for cache processing
	 */	          
  	protected $event_schedule = NULL;

  	/**
	 * Schedule description for cache processing
	 */	          
  	protected $event_description = NULL;
  
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
	}
  
  	/**
	 * Get instance
	 *
	 * @since 0.1.1
	 */	 	
  	public static function get_instance() {

	  	$class_name = get_called_class();
		if ( ! isset( self::$instance[$class_name] ) ) {
			self::$instance[$class_name] = new $class_name();
		  	//self::$instance[$class_name]->initialize($crawler, $options=array());
		}

		return self::$instance[$class_name];
	}
	
  	/**
	 * Register base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function register_schedule() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	
		if ( ! wp_next_scheduled( $this->prime_cron ) ) {
			wp_schedule_event( time(), $this->event_schedule, $this->prime_cron );
		}
	}
  
  	/**
	 * Unregister base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function unregister_schedule() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	wp_clear_scheduled_hook( $this->prime_cron );
	  	WP_Cron_Util::clear_scheduled_hook( $this->execute_cron );
	}
  
  	/**
	 * Get name of prime cron
	 *
	 * @since 0.1.1
	 */  	  
  	public function get_prime_cron() {
	  	return $this->prime_cron;
  	}  

    /**
	 * Get name of execute cron
	 *
	 * @since 0.1.1
	 */  	  
  	public function get_excute_cron() {
	  	return $this->execute_cron;
  	}  

  	/**
	 * Initialization
	 *
	 * @since 0.1.1
	 */
  	abstract public function initialize( $options = array() );

  
}

?>