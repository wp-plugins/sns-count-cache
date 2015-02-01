<?php
/*
class-wp-cron-util.php

Description: This class is a utility for WP-Cron  
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


class WP_Cron_Util {

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
	  
	}  

  	/**
	 * Clear scheduled hook based related to specified hook name 
	 *
	 * @since 0.1.1
	 */	     
  	public static function clear_scheduled_hook( $hook ) {
		$crons = _get_cron_array();
	  
		if ( empty( $crons ) ) return;
			  
		foreach( $crons as $timestamp => $cron ) {		  
		  	if( isset( $cron[$hook] ) ) {
			  	foreach ( $cron[$hook] as $signature => $data ) {
						wp_unschedule_event( $timestamp, $hook, $data['args'] );
			  	}
		  	}
		}
  	}

  	/**
	 * Return if there is the given hook or not  
	 *
	 * @since 0.1.1
	 */	     
  	public static function is_scheduled_hook( $hook ) {
		$crons = _get_cron_array();
	  
		if ( empty( $crons ) ) return false;
			  
		foreach( $crons as $timestamp => $cron ) {		  
		  	if( isset( $cron[$hook] ) ) {
				return true;
			}
		}
	  
	  	return false;
  	}

  	/**
	 * Get scheduled hook related to specified hook name 
	 *
	 * @since 0.1.1
	 */	     
  	public static function get_scheduled_hook( $hook ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$crons = _get_cron_array();
	  
	  	$info = array();
	  	$index = 0;
	  
		if ( empty( $crons ) ) return;
			  
		foreach( $crons as $timestamp => $cron ) {		  
		  	if( isset( $cron[$hook] ) ) {
				Common_Util::log( $cron[$hook] );
			  	foreach ( $cron[$hook] as $signature => $data ) {
				  
				  	Common_Util::log( '[' . __METHOD__ . '] hook: ' . $hook . ' offset: ' . $data['args'][0]. ' timestamp: ' . $timestamp );			  	
					
				  	$info[$index]['hook'] = $hook;
				  	$info[$index]['timestamp'] = $timestamp;
				  	$info[$index]['args'] = $data['args'];				  
				  	//wp_unschedule_event( $timestamp, $hook, $data['args'] );
			  	}
			  	$index++;
		  	}
		}
	  
	  	return $info; 
  	}  
  
}



?>