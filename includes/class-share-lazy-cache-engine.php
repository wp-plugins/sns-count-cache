<?php
/*
class-share-lazy-cache-engine.php

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


class Share_Lazy_Cache_Engine extends Share_Cache_Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_TRANSIENT_PREFIX = 'scc_share_count_';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_share_lazycache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_share_lazycache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'share_lazy_cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Share Lazy Cache Interval';
    
	/**
	 * Interval cheking and caching target data
	 */	  
	private $check_interval = 600;
  
	/**
	 * Number of posts to check at a time
	 */	  
	private $posts_per_check = 20;  
  
    /**
	 * Latency suffix
	 */	  
  	private $check_latency = 10;

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
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
	  	if ( isset( $options['check_latency'] ) ) $this->check_latency = $options['check_latency'];
		if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];
	  	if ( isset( $options['scheme_migration_mode'] ) ) $this->scheme_migration_mode = $options['scheme_migration_mode'];
	  	if ( isset( $options['scheme_migration_exclude_keys'] ) ) $this->scheme_migration_exclude_keys = $options['scheme_migration_exclude_keys'];
	  
		add_action( $this->execute_cron, array( $this, 'execute_cache' ), 10, 1 );

  	}  

   	/**
	 * Register base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function register_schedule() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	
	}

  	/**
	 * Unregister base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function unregister_schedule() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	WP_Cron_Util::clear_scheduled_hook( $this->execute_cron );
	}  
  
    /**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.2.0
	 */	   
	public function prime_cache( $post_ID ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->check_latency;
		
		Common_Util::log( '[' . __METHOD__ . '] check_latency: ' . $this->check_latency );
		Common_Util::log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		
	  	wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( $post_ID ) );
	}

   	/**
	 * Get and cache data of each published post
	 *
	 * @since 0.2.0
	 */	    
	public function execute_cache( $post_ID ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
	  	$cache_expiration = $this->get_cache_expiration();
		  
		Common_Util::log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

	  	$this->cache( $post_ID, $this->target_sns, $cache_expiration );

	  	if ( ! is_null( $this->delegate ) && method_exists( $this->delegate, 'order_cache' ) ) {
			$options = array( 'post_id' => $post_ID );
		  	$this->delegate->order_cache( $this, $options );
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
	  
	  	return ceil( $posts_total / $this->posts_per_check ) * $this->check_interval * 3;
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
  
}

?>