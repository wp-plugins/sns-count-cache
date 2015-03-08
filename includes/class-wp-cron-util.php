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
	 * Clear expired scheduled hook based related to specified hook name 
	 *
	 * @since 0.4.1
	 */	     
  	public static function clear_expired_scheduled_hook( $hook, $elapsed_time ) {
		$crons = _get_cron_array();
	  
		if ( empty( $crons ) ) return;
	  
	  	$current_time = time();
			  
		foreach( $crons as $timestamp => $cron ) {		  
		  	if( isset( $cron[$hook] ) ) {
			  	$duration = $timestamp - $current_time;
			  
			  	if ( $duration > $elapsed_time ) {
			  		foreach ( $cron[$hook] as $signature => $data ) {
							wp_unschedule_event( $timestamp, $hook, $data['args'] );
			  		}
				}
		  	}
		}
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
	  	//Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$crons = _get_cron_array();
	  
	  	$info = array();
	  	$index = 0;
	  
		if ( empty( $crons ) ) return;
			  
		foreach( $crons as $timestamp => $cron ) {		  
		  	if( isset( $cron[$hook] ) ) {
			  	//Common_Util::log( $cron[$hook] );
			  	foreach ( $cron[$hook] as $signature => $data ) {
				  
				  	//Common_Util::log( '[' . __METHOD__ . '] hook: ' . $hook . ' offset: ' . $data['args'][0]. ' timestamp: ' . $timestamp );			  	
					
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

	/**
	 *
	 * Get the local time timestamp of the next cron execution
	 * This code is cited from wordpress plugin BackWPup
	 *
	 * @param string $cronstring  cron (* * * * *)
	 * @return int timestamp
	 */
	public static function next_exec_time( $cronstring ) {

		$cron      = array();
		$cronarray = array();
		//Cron string
		list( $cronstr[ 'minutes' ], $cronstr[ 'hours' ], $cronstr[ 'mday' ], $cronstr[ 'mon' ], $cronstr[ 'wday' ] ) = explode( ' ', $cronstring, 5 );

		//make arrays form string
		foreach ( $cronstr as $key => $value ) {
			if ( strstr( $value, ',' ) )
				$cronarray[ $key ] = explode( ',', $value );
			else
				$cronarray[ $key ] = array( 0 => $value );
		}

		//make arrays complete with ranges and steps
		foreach ( $cronarray as $cronarraykey => $cronarrayvalue ) {
			$cron[ $cronarraykey ] = array();
			foreach ( $cronarrayvalue as $value ) {
				//steps
				$step = 1;
				if ( strstr( $value, '/' ) )
					list( $value, $step ) = explode( '/', $value, 2 );
				//replace weekday 7 with 0 for sundays
				if ( $cronarraykey == 'wday' )
					$value = str_replace( '7', '0', $value );
				//ranges
				if ( strstr( $value, '-' ) ) {
					list( $first, $last ) = explode( '-', $value, 2 );
					if ( ! is_numeric( $first ) || ! is_numeric( $last ) || $last > 60 || $first > 60 ) //check
						return 2147483647;
					if ( $cronarraykey == 'minutes' && $step < 5 ) //set step minimum to 5 min.
						$step = 5;
					$range = array();
					for ( $i = $first; $i <= $last; $i = $i + $step ) {
						$range[ ] = $i;
					}
					$cron[ $cronarraykey ] = array_merge( $cron[ $cronarraykey ], $range );
				}
				elseif ( $value == '*' ) {
					$range = array();
					if ( $cronarraykey == 'minutes' ) {
						if ( $step < 10 ) //set step minimum to 5 min.
							$step = 10;
						for ( $i = 0; $i <= 59; $i = $i + $step ) {
							$range[ ] = $i;
						}
					}
					if ( $cronarraykey == 'hours' ) {
						for ( $i = 0; $i <= 23; $i = $i + $step ) {
							$range[ ] = $i;
						}
					}
					if ( $cronarraykey == 'mday' ) {
						for ( $i = $step; $i <= 31; $i = $i + $step ) {
							$range[ ] = $i;
						}
					}
					if ( $cronarraykey == 'mon' ) {
						for ( $i = $step; $i <= 12; $i = $i + $step ) {
							$range[ ] = $i;
						}
					}
					if ( $cronarraykey == 'wday' ) {
						for ( $i = 0; $i <= 6; $i = $i + $step ) {
							$range[ ] = $i;
						}
					}
					$cron[ $cronarraykey ] = array_merge( $cron[ $cronarraykey ], $range );
				}
				else {
					//Month names
					if ( strtolower( $value ) == 'jan' )
						$value = 1;
					if ( strtolower( $value ) == 'feb' )
						$value = 2;
					if ( strtolower( $value ) == 'mar' )
						$value = 3;
					if ( strtolower( $value ) == 'apr' )
						$value = 4;
					if ( strtolower( $value ) == 'may' )
						$value = 5;
					if ( strtolower( $value ) == 'jun' )
						$value = 6;
					if ( strtolower( $value ) == 'jul' )
						$value = 7;
					if ( strtolower( $value ) == 'aug' )
						$value = 8;
					if ( strtolower( $value ) == 'sep' )
						$value = 9;
					if ( strtolower( $value ) == 'oct' )
						$value = 10;
					if ( strtolower( $value ) == 'nov' )
						$value = 11;
					if ( strtolower( $value ) == 'dec' )
						$value = 12;
					//Week Day names
					if ( strtolower( $value ) == 'sun' )
						$value = 0;
					if ( strtolower( $value ) == 'sat' )
						$value = 6;
					if ( strtolower( $value ) == 'mon' )
						$value = 1;
					if ( strtolower( $value ) == 'tue' )
						$value = 2;
					if ( strtolower( $value ) == 'wed' )
						$value = 3;
					if ( strtolower( $value ) == 'thu' )
						$value = 4;
					if ( strtolower( $value ) == 'fri' )
						$value = 5;
					if ( ! is_numeric( $value ) || $value > 60 ) //check
						return 2147483647;
					$cron[ $cronarraykey ] = array_merge( $cron[ $cronarraykey ], array( 0 => $value ) );
				}
			}
		}

		//generate years
		for ( $i = gmdate( 'Y' ); $i < gmdate( 'Y', 2147483647 ); $i ++ ) {
			$cron[ 'year' ][ ] = $i;
		}

		//calc next timestamp
		$current_timestamp = current_time( 'timestamp' );
		foreach ( $cron[ 'year' ] as $year ) {
			foreach ( $cron[ 'mon' ] as $mon ) {
				foreach ( $cron[ 'mday' ] as $mday ) {
					if ( ! checkdate( $mon, $mday, $year ) )
						continue;
					foreach ( $cron[ 'hours' ] as $hours ) {
						foreach ( $cron[ 'minutes' ] as $minutes ) {
							$timestamp = gmmktime( $hours, $minutes, 0, $mon, $mday, $year );
							if ( $timestamp && in_array( gmdate( 'j', $timestamp ), $cron[ 'mday' ] ) && in_array( gmdate( 'w', $timestamp ), $cron[ 'wday' ] ) && $timestamp > $current_timestamp )
								return $timestamp - ( get_option( 'gmt_offset' ) * 3600 );
						}
					}
				}
			}
		}

		return 2147483647;
	} 
  
  
}



?>