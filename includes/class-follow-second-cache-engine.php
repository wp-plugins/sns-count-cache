<?php
/*
class-follow-second-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals  
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


class Follow_Second_Cache_Engine extends Cache_Engine {
  
	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_TRANSIENT_PREFIX = 'scc_follow_count_';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_follow_2ndcache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_follow_2ndcache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'follow_second_cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Follow Second Cache Interval';  
	/**
	 * Interval cheking and caching target data
	 */	  
	private $check_interval = 600;
    
	/**
	 * Prefix of cache ID
	 */	    
  	private $meta_key_prefix = 'scc_follow_count_';
  
  	/**
	 * Cache target
	 */	            
  	private $target_sns = array();  

  	/**
	 * Cache post types
	 */	   
	private $post_types = array( 'post', 'page' );
  
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

	  	$this->transient_prefix = self::DEF_TRANSIENT_PREFIX;
	  	$this->prime_cron = self::DEF_PRIME_CRON;
	  	$this->execute_cron = self::DEF_EXECUTE_CRON;
	  	$this->event_schedule = self::DEF_EVENT_SCHEDULE;
	  	$this->event_description = self::DEF_EVENT_DESCRIPTION;	  
	  
	  	if ( isset( $options['target_sns'] ) ) $this->target_sns = $options['target_sns'];
	  	if ( isset( $options['check_interval'] ) ) $this->check_interval = $options['check_interval'];
	  	if ( isset( $options['transient_prefix'] ) ) $this->transient_prefix = $options['transient_prefix'];
		if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
		if ( isset( $options['event_schedule'] ) ) $this->event_schedule = $options['event_schedule'];
	  	if ( isset( $options['event_description'] ) ) $this->event_description = $options['event_description'];
	  	if ( isset( $options['meta_key_prefix'] ) ) $this->meta_key_prefix = $options['meta_key_prefix']; 
		if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];	  
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_check_interval' ) ); 
		add_action( $this->prime_cron, array( $this, 'prime_cache' ) );
		add_action( $this->execute_cron, array( $this, 'execute_cache' ), 10, 0 );

  	}  

  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function schedule_check_interval( $schedules ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->event_schedule] = array(
			'interval' => $this->check_interval,
			'display' => $this->event_description
		);
	  
		return $schedules;
	}  
  
  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.3.0
	 */	   
	public function prime_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->check_interval;

		Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
		Common_Util::log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
				
		wp_schedule_single_event( $next_exec_time, $this->execute_cron ); 		
	}
  
  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.4.0
	 */	    
	public function execute_cache() {
	 	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
	  	$this->cache( NULL, $this->target_sns, NULL );
	  	
	}
  
   	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	public function cache( $post_ID, $target_sns, $cache_expiration ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$transient_ID = $this->get_transient_ID( 'follow' );
			  		  
  		if ( false !== ( $sns_followers = get_transient( $transient_ID ) ) ) {
				  
			foreach ( $target_sns as $key => $value ) {
					  
				$meta_key = $this->meta_key_prefix . strtolower( $key );
					  
				if ( $value ) {
					if ( isset( $sns_followers[$key] ) && $sns_followers[$key] >= 0 ) {
						Common_Util::log( '[' . __METHOD__ . '] meta_key: ' . $meta_key . ' SNS: ' . $key . ' post_ID: ' . $post_ID . ' - ' . $sns_followers[$key] );
						  
					  	update_option( $meta_key, $sns_followers[$key] );
					}
				}
			}	  
		}
	  
	}  
  
  	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.2.0
	 */	      
  	protected function get_cache_expiration() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
		return 0;
	}
  
    /**
	 * Initialize meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function initialize_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		foreach ( $this->target_sns as $key => $value ) {
					  
			$meta_key = $this->meta_key_prefix . strtolower( $key );
					  
			if ( $value ) {
				update_option( $meta_key, -1 );
			}
		}	
	    	
  	}  

    /**
	 * Clear meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function clear_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		foreach ( $this->target_sns as $key => $value ) {
					  
			$meta_key = $this->meta_key_prefix . strtolower( $key );
					  
			if ( $value ) {
				delete_option( $meta_key );
			}
		}		
	  
	  	// compatibility for old version
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
			  	
				foreach ( $this->target_sns as $key => $value ) {
					  
					$meta_key = $this->meta_key_prefix . strtolower( $key );
					  
					if ( $value ) {
						delete_post_meta($post_ID, $meta_key);
					}
				}		  	 
			}
		}
		wp_reset_postdata();
	  
  
	  
  	}    
  
}

?>
