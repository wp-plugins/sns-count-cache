<?php
/*
class-share-rescue-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals  
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


class Share_Rescue_Cache_Engine extends Share_Cache_Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_TRANSIENT_PREFIX = 'scc_share_count_';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_share_rescuecache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_share_rescuecache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'share_rescue_cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Share Rescue Cache Interval';
  
	/**
	 * Interval cheking and caching target data
	 */	  
	private $check_interval = 600;

 	/**
	 * Number of posts to check at a time
	 */	  
	private $posts_per_check = 20;
   
	/**
	 * Prefix of cache ID
	 */	    
  	private $meta_key_prefix = 'scc_share_count_';  
  
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

	    if ( isset( $options['delegate'] ) ) $this->delegate = $options['delegate'];
	  	if ( isset( $options['crawler'] ) ) $this->crawler = $options['crawler'];
	  	if ( isset( $options['target_sns'] ) ) $this->target_sns = $options['target_sns'];
	  	if ( isset( $options['check_interval'] ) ) $this->check_interval = $options['check_interval'];
	  	if ( isset( $options['posts_per_check'] ) ) $this->posts_per_check = $options['posts_per_check'];
	  	if ( isset( $options['transient_prefix'] ) ) $this->transient_prefix = $options['transient_prefix'];
		if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
		if ( isset( $options['event_schedule'] ) ) $this->event_schedule = $options['event_schedule'];
	  	if ( isset( $options['event_description'] ) ) $this->event_description = $options['event_description'];
	  	if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];
	  	if ( isset( $options['meta_key_prefix'] ) ) $this->meta_key_prefix = $options['meta_key_prefix']; 
	  	if ( isset( $options['scheme_migration_mode'] ) ) $this->scheme_migration_mode = $options['scheme_migration_mode'];
	  	if ( isset( $options['scheme_migration_exclude_keys'] ) ) $this->scheme_migration_exclude_keys = $options['scheme_migration_exclude_keys'];
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_check_interval' ) ); 
		add_action( $this->prime_cron, array( $this, 'prime_cache' ) );
		add_action( $this->execute_cron, array( $this, 'execute_cache' ), 10, 1 );

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
	 * @since 0.1.0
	 */	   
	public function prime_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->check_interval;
		$posts_total = $this->get_posts_total();

		Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
	  	Common_Util::log( '[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check );
		Common_Util::log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );	  	
		Common_Util::log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
		
	  	wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( Common_Util::short_hash( $next_exec_time ) ) ); 
	  		  
	}
    
  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.1.0
	 */	    
	public function execute_cache( $hash ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
	  	Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
		Common_Util::log( '[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check );

	  	$no_cache_post_IDs = array();
	  
	  	$cache_expiration = $this->get_cache_expiration();
		  
		Common_Util::log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

	  	$check_range_min = 0;
		$check_range_max = 0;
	  
	  	$crons  = WP_Cron_Util::get_scheduled_hook( 'scc_share_basecache_exec' );
	  
	  	foreach ( $crons as $key => $cron ) {
		  	$hook = $cron['hook'];
		  	$timestamp = $cron['timestamp'];
		  	$offset = $cron['args'][0];
		  
		  	Common_Util::log( '[' . __METHOD__ . '] hook: ' . $hook . ' offset: ' . $offset . ' timestamp: ' . $timestamp );
		  
		  	//if ( time() + 300 < $timestamp ) {
		  	$check_range_min = $offset;
		  	$check_range_max = $offset + $this->posts_per_check;
		  	//}
	  	}
	  	  
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
			  
				$full_cache_flag = true;
				$partial_cache_flag = false;	  
	  
				foreach ( $this->target_sns as $key => $value ) {
					  		  
					if ( $value ) {								
							  
						$meta_key = $this->meta_key_prefix . strtolower( $key );
			  
						$sns_count = get_post_meta( get_the_ID(), $meta_key, true );
									 	
						if ( isset( $sns_count ) && $sns_count >= 0 ) {
							$partial_cache_flag  = true;
						} else {
							$full_cache_flag = false;
						}
									  
					}
				}
			  
				if ( $partial_cache_flag && $full_cache_flag ) {
				  	//full cache
					$transient_ID = $this->get_transient_ID( $post_ID );
			  
			  		if ( false === ( $sns_counts = get_transient( $transient_ID ) ) ) {	
				  		if ( $post_ID < $check_range_min || $post_ID > $check_range_max ) {
							$no_cache_post_IDs[$post_ID] = 1;
						}
					}
				} else if ( $partial_cache_flag && ! $full_cache_flag ) {
				  	//partial cache
					$transient_ID = $this->get_transient_ID( $post_ID );
			  
			  		if ( false === ( $sns_counts = get_transient( $transient_ID ) ) ) {	
				  		if ( $post_ID < $check_range_min || $post_ID > $check_range_max ) {
							$no_cache_post_IDs[$post_ID] = 2;
						}
					}
				} else {
				  	if ( $post_ID < $check_range_min || $post_ID > $check_range_max ) {
						$no_cache_post_IDs[$post_ID] = 3;
					}
				}

			}
		}
	  	wp_reset_postdata();

		Common_Util::log( '[' . __METHOD__ . '] no cache post IDs:');	
	  
		Common_Util::log( $no_cache_post_IDs );
	  
	  	arsort( $no_cache_post_IDs, SORT_NUMERIC );
	  	
	  	$rescue_post_IDs = array_slice( $no_cache_post_IDs, 0, $this->posts_per_check, true );
	  
	  	unset( $no_cache_post_IDs );
	  
	  	Common_Util::log( $rescue_post_IDs );
	  
	  	foreach ( $rescue_post_IDs as $post_ID => $priority ) {
		  	Common_Util::log( '[' . __METHOD__ . '] post_id: ' . $post_ID );	

			$transient_ID = $this->get_transient_ID( $post_ID );
	  
	  		$url = get_permalink( $post_ID );		  

			$options = array(
				'transient_id' => $transient_ID,
			  	'post_id' => $post_ID,
				'target_url' => $url,
		  		'target_sns' => $this->target_sns,
				'cache_expiration' => $cache_expiration
			);
		  
			$this->cache( $options );
			  
			if ( ! is_null( $this->delegate ) && method_exists( $this->delegate, 'order_cache' ) ) {
		  		$this->delegate->order_cache( $this, $options );
	  		}
		}
	  
	}

  	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.1.1
	 */	      
  	protected function get_cache_expiration() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$posts_total = $this->get_posts_total();
	  
		Common_Util::log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
	  
		return ( ( ceil( $posts_total / $this->posts_per_check ) + 2 ) * $this->check_interval ) + 2 * $this->check_interval;
  	}

    /**
	 * Initialize meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function initialize_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
  	}  

    /**
	 * Clear meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function clear_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
  	}   
  
  	/**
	 * Get total count of current published post and page
	 *
	 * @since 0.1.0
	 */	    
	private function get_posts_total() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$query_args = array(
				'post_type' => $this->post_types,
				'post_status' => 'publish',
				'nopaging' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query( $query_args );

		return $posts_query->found_posts;		
	}
  
}

?>