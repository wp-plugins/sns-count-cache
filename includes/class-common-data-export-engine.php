<?php
/*
class-common-data-export.php

Description: This class is a data export engine whitch exports cached data using wp-cron at regular intervals  
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


class Common_Data_Export_Engine extends Export_Engine {

	/**
	 * Meta key for share second cache
	 */	    
  	const DEF_SHARE_META_KEY_PREFIX = 'scc_share_count_';

	/**
	 * Meta key for follow second cache
	 */	    
  	const DEF_FOLLOW_META_KEY_PREFIX = 'scc_follow_count_';

	/**
	 * Cron name to schedule cache processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_common_dataexport_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_common_dataexport_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'common_data_export_event';

  	/**
	 * Schedule description for cache processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Common Data Export Interval';
  
	/**
	 * Option flag of data export
	 */	
	private $export_activation = 0;
  
  	/**
	 * Interval for data export
	 */	  
	private $export_interval = 43200;
  
  	/**
	 * Schedule for data export
	 */	    
  	private $export_schedule = '0 0 * * *'; 
    
  	/**
	 * Excluded keys of data export
	 */	          
  	private $export_exclude_keys = array();  
  
  	/**
	 * File name of data export
	 */	          
  	private $export_file_name = 'sns-count-cache-data.csv';    

  	/**
	 * Cache post types
	 */	   
	private $post_types = array( 'post', 'page' );
    
  	/**
	 * Cache target of share count
	 */	            
  	private $share_target_sns = array();  

  	/**
	 * Cache target of follow count
	 */	            
  	private $follow_target_sns = array();  
    
	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
	  	Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	}
  
  	/**
	 * Initialization
	 *
	 * @since 0.1.1
	 */
  	public function initialize( $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->share_meta_key_prefix = self::DEF_SHARE_META_KEY_PREFIX;
	  	$this->follow_meta_key_prefix = self::DEF_FOLLOW_META_KEY_PREFIX;
	  	$this->prime_cron = self::DEF_PRIME_CRON;
	  	$this->execute_cron = self::DEF_EXECUTE_CRON;
	  	$this->event_schedule = self::DEF_EVENT_SCHEDULE;
	  	$this->event_description = self::DEF_EVENT_DESCRIPTION;	  
	  
	  	if ( isset( $options['export_activation'] ) ) $this->export_activation = $options['export_activation'];
	  	if ( isset( $options['export_interval'] ) ) $this->export_interval = $options['export_interval'];
	  	if ( isset( $options['export_schedule'] ) ) $this->export_schedule = $options['export_schedule'];
	  	if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
	  	if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
		if ( isset( $options['event_schedule'] ) ) $this->event_schedule = $options['event_schedule'];
	  	if ( isset( $options['event_description'] ) ) $this->event_description = $options['event_description'];
	  	if ( isset( $options['export_exclude_keys'] ) ) $this->export_exclude_keys = $options['export_exclude_keys'];
	  	if ( isset( $options['export_file_name'] ) ) $this->export_file_name = $options['export_file_name'];
		if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];
	  	if ( isset( $options['share_meta_key_prefix'] ) ) $this->share_meta_key_prefix = $options['share_meta_key_prefix']; 
	  	if ( isset( $options['follow_meta_key_prefix'] ) ) $this->follow_meta_key_prefix = $options['follow_meta_key_prefix']; 
	  	if ( isset( $options['share_target_sns'] ) ) $this->share_target_sns = $options['share_target_sns'];
	  	if ( isset( $options['follow_target_sns'] ) ) $this->follow_target_sns = $options['follow_target_sns'];
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_export_interval' ) ); 
		add_action( $this->prime_cron, array( $this, 'prime_export' ) );
		add_action( $this->execute_cron, array( $this, 'execute_export' ), 10, 1 );
  	}  
    
  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function schedule_export_interval( $schedules ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->event_schedule] = array(
			'interval' => $this->export_interval,
			'display' => $this->event_description
		);
	  
		return $schedules;
	}

  	/**
	 * Prime data export
	 *
	 * @since 0.4.0
	 */	     
  	public function prime_export() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	//$next_exec_time = time() + $this->export_interval;
	  
	  	//$next_exec_time = time() + 5;

	  	$next_exec_time = WP_Cron_Util::next_exec_time( $this->export_schedule );
	  
		Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->export_interval );
		
	  	Common_Util::log( '[' . __METHOD__ . '] next_exec_time (timesatamp): ' . $next_exec_time );
	  	Common_Util::log( '[' . __METHOD__ . '] next_exec_time (date): ' . date_i18n( 'Y/m/d H:i:s', $next_exec_time ) );
		
	  
	  	if( ! WP_Cron_Util::is_scheduled_hook( $this->execute_cron ) ) {
		  	wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( Common_Util::short_hash( $next_exec_time ) ) );  
	  	}
		

	  /*
		wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( Common_Util::short_hash( $next_exec_time ) ) );	  
		*/
  	}
  
  
  	/**
	 * Execute data export
	 *
	 * @since 0.4.0
	 */	      
  	public function execute_export( $hash ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  		  
	  	$base_dir = WP_PLUGIN_DIR . '/sns-count-cache/data/';

	  	$current_date = date_i18n( 'Y/m/d H:i:s' );
	  	  
	  	if( file_exists( $base_dir ) ) {
	  		$abs_path = $base_dir . $this->export_file_name;
	  
	  
	  		if ( ! file_exists( $abs_path ) ) {
		  
		  		if ( touch( $abs_path ) ) {
			  		Common_Util::log( '[' . __METHOD__ . '] export file creation succeeded: ' . $abs_path );  	
				} else {
			  		Common_Util::log( '[' . __METHOD__ . '] export file creation failed: ' . $abs_path );
				}
		  
		  		if ( file_exists( $abs_path ) ) {
					Common_Util::log( '[' . __METHOD__ . '] file exists: ' . $abs_path );
		  			$fp = fopen( $abs_path, 'a' );
			  
			  		$header = '"Retrieval Date","Post ID","Post Type","Post Title","Permalink","Post Date","SNS","Data Type","Data Value",' . "\r\n";
			  
					if ( fwrite( $fp, mb_convert_encoding( $header, "SJIS", "UTF-8" ) ) ) {
			  			Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ' . $header );
					} else {
			  			Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ' . $header );
					}
								  
		  			if ( fclose($fp) ) {
			  			Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $abs_path );
					} else {
			  			Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $abs_path );
					}			  	
				}		  
	  		}
	  
	  		if ( file_exists( $abs_path ) ) {
		  		Common_Util::log( '[' . __METHOD__ . '] file exists: ' . $abs_path );
		  
		  		$fp = fopen( $abs_path, 'a' );
		  
				$query_args = array(
					'post_type' => $this->post_types,
					'post_status' => 'publish',
					'nopaging' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false
				);

				$posts_query = new WP_Query( $query_args );
	  
				if ( $posts_query->have_posts() ) {
					while ( $posts_query->have_posts() ) {
						$posts_query->the_post();
			  
						$post_ID = get_the_ID();
				  
				 		$content = '"' . $current_date . '","' . $post_ID . '","' . get_post_type( $post_ID ) . '","' . get_the_title( $post_ID ) . '","' . get_permalink( $post_ID ) . '","' . get_post_time( 'Y/m/d H:i', false, $post_ID );
				  
						foreach ( $this->share_target_sns as $key => $value ) {
					  
					  		if ( ! in_array( $key, $this->export_exclude_keys ) ) {
					  
								$meta_key = $this->share_meta_key_prefix . strtolower( $key );
					  							  
								if ( $value ) {
								  
								  	$data_value = get_post_meta( $post_ID, $meta_key, true );
							  
							  		if ( $data_value == -1 ){
								  		$data_value = '';
							  		}
								  
									$data = $content . '","' . $key . '","' . 'Share' . '","' . $data_value . '",' . "\r\n";
								}
				  				if ( fwrite( $fp, mb_convert_encoding( $data, "SJIS", "UTF-8" ) ) ) {
			  						Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ' . $data );
								} else {
			  						Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ' . $data );
								}					  
							}
						}
					  	
					  /*
				  		foreach ( $this->follow_target_sns as $key => $value ) {
					  		$meta_key = $this->follow_meta_key_prefix . strtolower( $key );
					  
							if ( $value ) {
							  
							  	$data_value = get_post_meta( $post_ID, $meta_key, true );
							  
							  	if ( $data_value == -1 ){
								  	$data_value = '';
							  	}
							  
								$data = $content . '","' . $key . '","' . 'Follow' . '","' . $data_value . '",' . "\r\n";
							}
				  			if ( fwrite( $fp, mb_convert_encoding( $data, "SJIS", "UTF-8" ) ) ) {
			  					Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ' . $data );
							} else {
			  					Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ' . $data );
							}					  
					  
						}
						*/

					}
				}
				wp_reset_postdata(); 			  
		 
		  		if ( fclose($fp) ) {
			  		Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $abs_path );
				} else {
			  		Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $abs_path );
				}
			} 
  		}
	}

  	/**
	 * Reset exported file
	 *
	 * @since 0.4.0
	 */	       
  	public function reset_export() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  		  
	  	$base_dir = WP_PLUGIN_DIR . '/sns-count-cache/data/';
	  	$abs_path = $base_dir . $this->export_file_name;
	  
	  	if ( file_exists( $abs_path ) ) {		
		  	unlink( $abs_path );
		}
	}    
}

?>
