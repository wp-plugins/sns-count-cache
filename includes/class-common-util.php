<?php
/*
class-common-util.php

Description: This class is a common utility  
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

class Common_Util {

	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
	  Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	}  

  	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */	    
	public static function log( $message ) {
    	if ( WP_DEBUG === true ) {
      		if ( is_array( $message ) || is_object( $message ) ) {
        		error_log( print_r( $message, true ) );
      		} else {
        		error_log( $message );
      		}
    	}
  	}
    
  	/**
	 * Get short hash code
	 *
	 * @since 0.2.0
	 */	   
  	public static function short_hash( $data, $algo = 'CRC32' ) {
	  	return strtr( rtrim( base64_encode( pack('H*', $algo($data) ) ), '=' ), '+/', '-_' );
	}  

  	/**
	 * Get file size of given file
	 *
	 * @since 0.4.0
	 */	    
	public static function get_file_size( $file ) {
	  	  	
	  	if ( file_exists( $file ) && is_file( $file ) ) {
			$filesize = filesize( $file );
			$s = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
 			$e = floor( log( $filesize ) / log( 1024 ) );
		  
		  	if( $e == 0 || $e == 1 ) { 
			  	$format = '%d '; 
			} else {
			  	$format = '%.1f ';
			}
		  
 			$filesize = sprintf( $format . $s[$e], ( $filesize / pow( 1024, floor( $e ) ) ) );
		  
			return $filesize;
		} else {
		  	return null;
		}
	}

   	/**
	 * Get custom post types
	 *
	 * @since 0.4.0
	 */	     
  	public static function get_custom_post_types() {
	  	
	  	global $wpdb;
	  	
	  	$custom_post_types = array();
	  
	  	$builtin_post_types = get_post_types( array( '_builtin' => true ) );
											 
	  	$exclude_post_types = "'";
	  	$exclude_post_types .= implode( "','", $builtin_post_types );
	  	$exclude_post_types .= "'";
	  	  
	  	$sql = 'SELECT DISTINCT post_type FROM ' . $wpdb->posts . ' WHERE post_type NOT IN ( ' . $exclude_post_types . ' )';
	  
	  	$results = $wpdb->get_results( $sql );
			  
	  	foreach ( $results as $value ) {
    		$custom_post_types[] = $value->post_type;
		}
	  
	  	return $custom_post_types;
	}

   	/**
	 * check if php-xml module is loaded or not
	 *
	 * @since 0.4.0
	 */	       
  	public static function extension_loaded_php_xml() {
		if ( extension_loaded( 'xml' ) && extension_loaded( 'xmlreader' ) && extension_loaded( 'xmlwriter' ) ) {
			return true;
		} else {
			return false;
		}	  	
  	}

   	/**
	 * convert url based on http into url based on https
	 *
	 * @since 0.4.0
	 */	         
  	public static function get_secure_url( $url ){
	  	
		$url = str_replace( 'http://', 'https://', $url );
	  
		return $url;
  	}

   	/**
	 * convert url based on https into url based on http
	 *
	 * @since 0.4.0
	 */	         
  	public static function get_normal_url( $url ){
	  	
		$url = str_replace( 'https://', 'http://', $url );
	  
		return $url;
  	}

   	/**
	 * check if a given url is based on https or not.
	 *
	 * @since 0.4.0
	 */	           
  	public static function is_secure_url( $url ){
	  	
	  	if ( preg_match( '/^(https)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url ) ) {
	  		return true;
		} else {
		  	return false;
		}	  
  	}

    /**
	 * check if a given url is based on http or not.
	 *
	 * @since 0.4.0
	 */	           
  	public static function is_normal_url( $url ){
	  	
	  	if ( preg_match( '/^(http)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url ) ) {
	  		return true;
		} else {
		  	return false;
		}	  
  	} 
  
  	/**
	 * get cout data from SNS
	 *
	 * @since 0.5.1
	 */
    public static function multi_remote_get( $urls, $timeout = 0, $sslverify = true, $curl = false ) {

	  	global $wp_version;

        $responses = array();

        if ( empty( $urls ) ) {
            return $responses; 
        }
	  
	  	if ( $curl ) {
	  
		  	Common_Util::log( '[' . __METHOD__ . '] cURL: On' );
		  
        	$mh = curl_multi_init();
        	$ch = array();

        	foreach ( $urls as $sns => $url ) {
            	$ch[$sns] = curl_init();
          
            	curl_setopt( $ch[$sns], CURLOPT_URL, $url );
            	curl_setopt( $ch[$sns], CURLOPT_USERAGENT, 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );
            	curl_setopt( $ch[$sns], CURLOPT_FOLLOWLOCATION, true );
            	curl_setopt( $ch[$sns], CURLOPT_RETURNTRANSFER, true );
			  			  
		  		if ( $sslverify ) {
            		curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYPEER, true );
            		curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYHOST, 2 );
				} else {
            		curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYPEER, false );
            		curl_setopt( $ch[$sns], CURLOPT_SSL_VERIFYHOST, 0 );
				}				
			  
            	if ( $timeout > 0 ) {
                	curl_setopt( $ch[$sns], CURLOPT_CONNECTTIMEOUT, $timeout ); 
                	curl_setopt( $ch[$sns], CURLOPT_TIMEOUT, $timeout );
            	}
            
            	curl_multi_add_handle( $mh, $ch[$sns] );
        	}

	  		/*
			$running = null;

			do {
		  		$mrc = curl_multi_exec( $mh, $running ); 
			} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
	  
			while ( $running && $mrc == CURLM_OK ) {
				if ( curl_multi_select( $mh ) != -1 ) {
					do {
						$mrc = curl_multi_exec( $mh, $running );
					} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
				}
			}
	  		*/	  
	  
        	$active = null;

        	do {
          		curl_multi_exec( $mh, $active );
          		curl_multi_select( $mh );
        	} while ( $active > 0);

        	foreach( $urls as $sns => $url ){
            	$responses[$sns]['error'] = curl_error( $ch[$sns] );

            	if( ! empty( $responses[$sns]['error'] ) ) {
                	$responses[$sns]['data']  = '';
            	} else {
                	$responses[$sns]['data']  = curl_multi_getcontent( $ch[$sns] );
            	}
            
            	curl_multi_remove_handle( $mh, $ch[$sns] );
            	curl_close( $ch[$sns] );
        	}

        	curl_multi_close( $mh );

        	return $responses; 
    	} else {
	  
		  	Common_Util::log( '[' . __METHOD__ . '] cURL: Off' );
		  
	  		$options = array(
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
				);
		  
		  	if ( $sslverify ) {
			  	$options['sslverify'] = true;
			} else {
			  	$options['sslverify'] = false;
			}
		  
	  		if ( $timeout > 0 ) {
		  		$options['timeout'] = $timeout;
			}
	  	
        	foreach ( $urls as $sns => $url ) {		  
		  		$response = wp_remote_get( $url, $options );
		  
				if ( ! is_wp_error( $response ) && $response['response']['code'] === 200 ) {
    				$responses[$sns]['data'] = $response['body'];
				} else {
    				$responses[$sns]['data'] = '';
			  		$responses[$sns]['error'] = $response->get_error_message();
				}
			}
	  
	  		return $responses;
		}
	}
  
	public static function serialize_base64_encode( $array ) {
		$data = serialize( $array );
		$data = base64_encode( $data );
	  
		return $data;
	}
 
	public static function unserialize_base64_decode( $data ) {
		$data = base64_decode( $data );
		$array = unserialize( $data );
	  
		return $array;
	}  
  
}

?>