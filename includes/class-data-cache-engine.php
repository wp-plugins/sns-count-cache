<?php
/*
class-data-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals  
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

class Data_Cache_Engine {

  	/**
	 * Instance of crawler to get data
	 */	
	private $crawler = NULL;

	/**
	 * Interval cheking and caching target data
	 */	  
	private $base_check_interval = 600;
  
	/**
	 * Number of posts to check at a time
	 */	  
	private $base_posts_per_check = 20;

	/**
	 * Prefix of cache ID
	 */	    
  	private $base_transient_prefix = 'base_data_cache';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	private $base_cache_prime_cron = 'base_data_cache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	private $base_cache_execute_cron = 'base_data_cache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	private $base_event_schedule = 'base_cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
  	private $base_event_description = 'base cache event';
  
  	/**
	 * Cache target
	 */	            
  	private $base_cache_target = array();  
  
    /**
	 * Offset suffix
	 */	    
  	private $base_offset_suffix = 'base_offset';

	/**
	 * Interval cheking and caching target data
	 */	  
	private $rush_check_interval = 300;

	/**
	 * Number of posts to check at a time
	 */	  
	private $rush_posts_per_check = 20;

	/**
	 * Prefix of cache ID
	 */	    
  	private $rush_transient_prefix = 'rush_data_cache';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	private $rush_cache_prime_cron = 'rush_data_cache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	private $rush_cache_execute_cron = 'rush_data_cache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	private $rush_event_schedule = 'rush_cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
  	private $rush_event_description = 'rush cache event';

    /**
	 * Offset suffix
	 */	  
  	private $rush_offset_suffix = 'rush_offset';

    /**
	 * Term considered as new content
	 */	    	
  	private $rush_new_content_term = 3;
  
    /**
	 * Latency suffix
	 */	  
  	private $lazy_check_latency = 10;

	/**
	 * Cron name to execute cache processing
	 */	        
  	private $lazy_cache_execute_cron = 'lazy_data_cache_exec';

	/**
	 * Interval checking ranking
	 */	  
	private $second_check_interval = 600;
  
	/**
	 * Prefix of cache ID
	 */	    
  	private $second_meta_key_prefix = 'second_cache_processing';
  
	/**
	 * Cron name to schedule rank processing
	 */	      
  	private $second_cache_prime_cron = 'second_cache_prime';

	/**
	 * Cron name to execute rank processing
	 */	        
  	private $second_cache_execute_cron = 'second_cache_exec';

	/**
	 * Schedule name for rank processing
	 */	          
  	private $second_event_schedule = 'second cache event';

  	/**
	 * Schedule description for rank processing
	 */	          
  	private $second_event_description = 'second cache event';
  
  
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
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

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
	 * Initialization
	 *
	 * @since 0.1.1
	 */
  	public function initialize( $crawler, $options = array() ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->crawler = $crawler;
	  
	  	if ( isset( $options['base_cache_target'] ) ) $this->base_cache_target = $options['base_cache_target'];
	  	if ( isset( $options['base_check_interval'] ) ) $this->base_check_interval = $options['base_check_interval'];
	  	if ( isset( $options['base_posts_per_check'] ) ) $this->base_posts_per_check = $options['base_posts_per_check'];
	  	if ( isset( $options['base_transient_prefix'] ) ) $this->base_transient_prefix = $options['base_transient_prefix'];
		if ( isset( $options['base_cache_prime_cron'] ) ) $this->base_cache_prime_cron = $options['base_cache_prime_cron'];
		if ( isset( $options['base_cache_execute_cron'] ) ) $this->base_cache_execute_cron = $options['base_cache_execute_cron'];
		if ( isset( $options['base_event_schedule'] ) ) $this->base_event_schedule = $options['base_event_schedule'];
	  	if ( isset( $options['base_event_description'] ) ) $this->base_event_description = $options['base_event_description'];
	  	  
		add_filter( 'cron_schedules', array( $this, 'schedule_base_check_interval' ) ); 
		add_action( $this->base_cache_prime_cron, array( $this, 'prime_base_data_cache' ) );
		add_action( $this->base_cache_execute_cron, array( $this, 'execute_base_data_cache' ), 10, 1 );

	  	if ( isset( $options['rush_check_interval'] ) ) $this->rush_check_interval = $options['rush_check_interval'];
	  	if ( isset( $options['rush_posts_per_check'] ) ) $this->rush_posts_per_check = $options['rush_posts_per_check'];
		if ( isset( $options['rush_cache_prime_cron'] ) ) $this->rush_cache_prime_cron = $options['rush_cache_prime_cron'];
		if ( isset( $options['rush_cache_execute_cron'] ) ) $this->rush_cache_execute_cron = $options['rush_cache_execute_cron'];
		if ( isset( $options['rush_event_schedule'] ) ) $this->rush_event_schedule = $options['rush_event_schedule'];
	  	if ( isset( $options['rush_event_description'] ) ) $this->rush_event_description = $options['rush_event_description'];
	  	if ( isset( $options['rush_new_content_term'] ) ) $this->rush_new_content_term = $options['rush_new_content_term'];
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_rush_check_interval' ) ); 
		add_action( $this->rush_cache_prime_cron, array( $this, 'prime_rush_data_cache' ) );
		add_action( $this->rush_cache_execute_cron, array( $this, 'execute_rush_data_cache' ), 10, 2 );
	  
		if ( isset( $options['lazy_cache_execute_cron'] ) ) $this->lazy_cache_execute_cron = $options['lazy_cache_execute_cron'];
	  
	  	//add_action($this->lazy_cache_execute_cron, array($this, 'execute_lazy_data_cache'),10,2);
	  	add_action( $this->lazy_cache_execute_cron, array( $this, 'execute_lazy_data_cache' ), 10, 1 );
	  
	  	if ( isset( $options['second_check_interval'] ) ) $this->second_check_interval = $options['second_check_interval'];
	  	if ( isset( $options['second_cache_prime_cron'] ) ) $this->second_cache_prime_cron = $options['second_cache_prime_cron'];
	  	if ( isset( $options['second_cache_execute_cron'] ) ) $this->second_cache_execute_cron = $options['second_cache_execute_cron'];
		if ( isset( $options['second_meta_key_prefix'] ) ) $this->second_meta_key_prefix = $options['second_meta_key_prefix'];
	  	if ( isset( $options['second_event_schedule'] ) ) $this->second_event_schedule = $options['second_event_schedule'];
	  	if ( isset( $options['second_event_description'] ) ) $this->second_event_description = $options['second_event_description'];
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_second_check_interval' ) ); 
		add_action( $this->second_cache_prime_cron, array( $this, 'prime_second_data_cache' ) );
		add_action( $this->second_cache_execute_cron, array( $this, 'execute_second_data_cache' ), 10, 0 );	  
	  
  	}
  	
  
  	/**
	 * Register base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function register_base_schedule() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	
		if ( ! wp_next_scheduled( $this->base_cache_prime_cron ) ) {
			wp_schedule_event( time(), $this->base_event_schedule, $this->base_cache_prime_cron );
		}
		if ( ! wp_next_scheduled( $this->rush_cache_prime_cron ) ) {
			wp_schedule_event( time(), $this->rush_event_schedule, $this->rush_cache_prime_cron );
		}
	  	if ( ! wp_next_scheduled( $this->second_cache_prime_cron ) ) {
		  	$this->initialize_second_cache();
			wp_schedule_event( time(), $this->second_event_schedule, $this->second_cache_prime_cron );
		}
	  	
	}

  	/**
	 * Unregister base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function unregister_base_schedule() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	wp_clear_scheduled_hook( $this->base_cache_prime_cron );
	  	$this->clear_scheduled_hook( $this->base_cache_execute_cron );

	  	wp_clear_scheduled_hook( $this->rush_cache_prime_cron );
	  	$this->clear_scheduled_hook( $this->rush_cache_execute_cron );
	  
	  	wp_clear_scheduled_hook( $this->second_cache_prime_cron );
	  	$this->clear_scheduled_hook( $this->second_cache_execute_cron );
	  	$this->clear_second_cache();
	}
  
  	/**
	 * Clear scheduled hook based related to specified hook name 
	 *
	 * @since 0.1.1
	 */	     
  	private function clear_scheduled_hook( $hook ) {
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
	 * Initialize meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	private function initialize_second_cache() {
		$query_args = array(
			'post_type' => array( 'post', 'page' ),
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
			  	
				foreach ( $this->base_cache_target as $key => $value ) {
					  
					$meta_key = $this->second_meta_key_prefix . strtolower( $key );
					  
					if ( $value ) {
						update_post_meta($post_ID, $meta_key, -1);
					}
				}		  	 
			}
		}
		wp_reset_postdata(); 	
	  
  	}
  
    /**
	 * Clear meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	private function clear_second_cache() {
		$query_args = array(
			'post_type' => array( 'post', 'page' ),
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
			  	
				foreach ( $this->base_cache_target as $key => $value ) {
					  
					$meta_key = $this->second_meta_key_prefix . strtolower( $key );
					  
					if ( $value ) {
						delete_post_meta($post_ID, $meta_key);
					}
				}		  	 
			}
		}
		wp_reset_postdata(); 	
	  
  	}
  
  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function schedule_base_check_interval( $schedules ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->base_event_schedule] = array(
			'interval' => $this->base_check_interval,
			'display' => $this->base_event_description
		);
		return $schedules;
	}

  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.1.0
	 */	   
	public function prime_base_data_cache() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->base_check_interval;
		$posts_total = $this->get_base_posts_total();

		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->base_check_interval );
		$this->log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		$this->log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
		
	  	$transient_ID = $this->get_transient_ID( $this->base_offset_suffix );
		  
		if ( false === ( $posts_offset = get_transient( $transient_ID ) ) ) {
			$posts_offset = 0;
		}

		$this->log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );
		
		wp_schedule_single_event( $next_exec_time, $this->base_cache_execute_cron, array( $posts_offset ) ); 
	  	
		$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $this->base_posts_per_check );
			  
		$posts_offset = $posts_offset + $this->base_posts_per_check;
	  
		if ( $posts_offset > $posts_total ) {
			$posts_offset = 0;
		}

		set_transient( $transient_ID, $posts_offset, $this->base_check_interval + $this->base_check_interval ); 
		
	}
    
  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.1.0
	 */	    
	public function execute_base_data_cache( $posts_offset ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$this->log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );
		$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $this->base_posts_per_check );
		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->base_check_interval );
		
	  	$cache_expiration = $this->get_base_cache_expiration();
		  
		$this->log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );
		
		$query_args = array(
				'post_type' => array( 'post', 'page' ),
				'post_status' => 'publish',
				'offset' => $posts_offset,
				'posts_per_page' => $this->base_posts_per_check,
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query( $query_args );

		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();
			  
			  	$post_ID = get_the_ID();
			  	
			  	$this->log( '[' . __METHOD__ . '] post_id: ' . $post_ID );	
			  
			  	$this->cache_data( $post_ID, $this->base_cache_target, $cache_expiration );			  
			}
		}
		wp_reset_postdata();
	}
    
  	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.1.1
	 */	      
  	private function get_base_cache_expiration() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$posts_total = $this->get_base_posts_total();
	  
		$this->log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
	  
		return ( ( ceil( $posts_total / $this->base_posts_per_check ) + 2 ) * $this->base_check_interval ) + 2 * $this->base_check_interval;
  	}
 
  	/**
	 * Get total count of current published post and page
	 *
	 * @since 0.1.0
	 */	    
	private function get_base_posts_total() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$query_args = array(
				'post_type' => array( 'post', 'page' ),
				'post_status' => 'publish',
				'nopaging' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query( $query_args );

		return $posts_query->found_posts;		
	}
  
  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.2.0
	 */	     
	public function schedule_rush_check_interval( $schedules ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->rush_event_schedule] = array(
			'interval' => $this->rush_check_interval,
			'display' => $this->rush_event_description
		);
		return $schedules;
	}

  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.2.0
	 */	   
	public function prime_rush_data_cache() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->rush_check_interval;
		$posts_total = $this->get_rush_posts_total();

		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->rush_check_interval );
		$this->log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		$this->log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
		
	  	$transient_ID = $this->get_transient_ID($this->rush_offset_suffix);
		  
		if ( false === ( $posts_offset = get_transient( $transient_ID ) ) ) {
			$posts_offset = 0;
		}

		$this->log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );
		
		wp_schedule_single_event( $next_exec_time, $this->rush_cache_execute_cron, array( $posts_offset, $this->short_hash( $next_exec_time ) ) ); 
	  	
		$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $this->rush_posts_per_check );
			  
		$posts_offset = $posts_offset + $this->rush_posts_per_check;
	  
		if ( $posts_offset > $posts_total ) {
			$posts_offset = 0;
		}

	  	//delete_transient($transient_id);
		set_transient( $transient_ID, $posts_offset, $this->rush_check_interval + $this->rush_check_interval ); 
	}

  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.2.0
	 */	    
	public function execute_rush_data_cache( $posts_offset, $hash ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$this->log( '[' . __METHOD__ . '] posts_offset: ' . $posts_offset );
		$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $this->rush_posts_per_check );
		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->rush_check_interval );
		
	  	$cache_expiration = $this->get_rush_cache_expiration();
		  
		$this->log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

	  	$term_threshold = '3 days ago'; 
	  
	  	if ( $this->rush_new_content_term > 1 ) {
		  	$term_threshold = $this->rush_new_content_term . ' days ago'; 
		} else if ( $this->rush_new_content_term == 1 ) {
		  	$term_threshold = $this->rush_new_content_term . ' day ago'; 
		}	  
	  
	  	$this->log( '[' . __METHOD__ . '] term_threshold: ' . $term_threshold );
	  
		$query_args = array(
				'post_type' => array( 'post', 'page' ),
				'post_status' => 'publish',
				'offset' => $posts_offset,
				'posts_per_page' => $this->rush_posts_per_check,
				'date_query' => array(
					'column' => 'post_date_gmt',
					'after' => $term_threshold
					),				
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query( $query_args );

		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();
			  
			  	$post_ID = get_the_ID();
			  	
			  	$this->log( '[' . __METHOD__ . '] post_id: ' . $post_ID );
			  
			  	$this->cache_data( $post_ID, $this->base_cache_target, $cache_expiration );			  
			}
		}
		wp_reset_postdata();
	}

  	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.2.0
	 */	      
  	private function get_rush_cache_expiration() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$posts_total = $this->get_rush_posts_total();
	  
		$this->log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
	  
		return ( ( ceil( $posts_total / $this->rush_posts_per_check ) + 2 ) * $this->rush_check_interval ) + 2 * $this->rush_check_interval;
	}  

  	/**
	 * Get total count of current published post and page
	 *
	 * @since 0.2.0
	 */	    
	private function get_rush_posts_total() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$term_threshold = '3 days ago'; 
	  
	  	if ( $this->rush_new_content_term > 1 ) {
		  	$term_threshold = $this->rush_new_content_term . ' days ago'; 
		} else if ( $this->rush_new_content_term == 1 ) {
		  	$term_threshold = $this->rush_new_content_term . ' day ago'; 
		}

		$query_args = array(
				'post_type' => array( 'post', 'page' ),
				'post_status' => 'publish',
				'date_query' => array(
					'column' => 'post_date_gmt',
					'after' => $term_threshold
					),
				'nopaging' => true,
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query( $query_args );

		return $posts_query->found_posts;		
	}
  
  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.2.0
	 */	   
	public function prime_lazy_data_cache( $post_ID ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->lazy_check_latency;
		
		$this->log( '[' . __METHOD__ . '] check_latency: ' . $this->lazy_check_latency );
		$this->log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		
	  	//wp_schedule_single_event($next_exec_time, $this->lazy_cache_execute_cron, array($post_ID, $this->short_hash($next_exec_time)));
	  	wp_schedule_single_event( $next_exec_time, $this->lazy_cache_execute_cron, array( $post_ID ) ); 
	  	
	}

   	/**
	 * Get and cache data of each published post
	 *
	 * @since 0.2.0
	 */	    
	public function execute_lazy_data_cache( $post_ID ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
	  	$cache_expiration = $this->get_base_cache_expiration();
		  
		$this->log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );

	  	$this->cache_data( $post_ID, $this->base_cache_target, $cache_expiration );
	}

   	/**
	 * Get and cache data of each published post
	 *
	 * @since 0.2.0
	 */
  	/*
	public function execute_lazy_data_cache($post_ID, $hash){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		
	  	$cache_expiration = $this->get_base_cache_expiration();
		  
		$this->log('[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration);

	  	$this->cache_data($post_ID, $cache_expiration);
	}
	*/
  
  	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */
  	public function execute_direct_data_cache( $post_ID ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $this->base_posts_per_check );
		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->base_check_interval );

	  	$cache_expiration = $this->get_base_cache_expiration();
		  
		$this->log( '[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration );	
	  		  	  
	  	return $this->cache_data( $post_ID, $this->base_cache_target, $cache_expiration );
	}

  	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	private function cache_data( $post_ID, $cache_target, $cache_expiration ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->log( '[' . __METHOD__ . '] post_id: ' . $post_ID );

		$transient_ID = $this->get_transient_ID( $post_ID );
	  
		$url = get_permalink( $post_ID );
								
		$data = $this->crawler->get_data( $cache_target, $url );
			  
		$this->log( $data );
		
	  	if ( $data ) {	  
			$result = set_transient( $transient_ID, $data, $cache_expiration ); 
			  
			$this->log( '[' . __METHOD__ . '] set_transient result: ' . $result );
	  	}
	  
	  	return $data;
  	}


  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.3.0
	 */	     
	public function schedule_second_check_interval( $schedules ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->second_event_schedule] = array(
			'interval' => $this->second_check_interval,
			'display' => $this->second_event_description
		);
		return $schedules;
	}

  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.3.0
	 */	   
	public function prime_second_data_cache() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->second_check_interval;
		$posts_total = $this->get_base_posts_total();

		$this->log( '[' . __METHOD__ . '] check_interval: ' . $this->second_check_interval );
		$this->log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		$this->log( '[' . __METHOD__ . '] posts_total: ' . $posts_total );
				
		wp_schedule_single_event( $next_exec_time, $this->second_cache_execute_cron); 
	  			
	}
    
  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.3.0
	 */	    
	public function execute_second_data_cache() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
	  //$data = array();
  
		$query_args = array(
			'post_type' => array( 'post', 'page' ),
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
			  	
  				$transient_ID = $this->base_transient_prefix . $post_ID;
  
  				if ( false !== ( $sns_counts = get_transient( $transient_ID ) ) ) {
				  
				  	foreach ( $this->base_cache_target as $key => $value ) {
					  
					  	$meta_key = $this->second_meta_key_prefix . strtolower( $key );
					  
					  	if ( $value ) {
						  	$this->log( '[' . __METHOD__ . '] meta_key: ' . $meta_key . ' SNS: ' . $key . ' post_ID: ' . $post_ID . ' - ' . $sns_counts[$key] );
				  			update_post_meta($post_ID, $meta_key, $sns_counts[$key]);
						  	//$data[$key][$post_ID] = $sns_counts[$key];
						}
					}
				  
				} 
			  /*
			  else {
				  	foreach ( $this->base_cache_target as $key => $value ) {
					  	if ( $value ) {
							update_post_meta($post_ID, $meta_key, 0);
						}
					}
				}
				*/
			  	 
			}
		}
		wp_reset_postdata(); 	  
	  

	  /*
		if ( $posts_query->have_posts() ) {
			while( $posts_query->have_posts() ) {
				$posts_query->the_post();
			  
				$post_ID = get_the_ID();
			  	
  				$transient_ID = $this->base_transient_prefix . $post_ID;
  
  				if ( false !== ( $sns_counts = get_transient( $transient_ID ) ) ) {
				  
				  	foreach ( $this->base_cache_target as $key => $value ) {
					  	if ( $value ) {
							$data[$key][$post_ID] = $sns_counts[$key];
						}
					}
				  
				} else {
				  	foreach ( $this->base_cache_target as $key => $value ) {
					  	if ( $value ) {
							$data[$key][$post_ID] = 0;
						}
					}
				}			  
			  	 
			}
		}
		wp_reset_postdata(); 
			
		foreach ( $this->base_cache_target as $key => $value ) {
			if ( $value ) {
			  	arsort( $data[$key] );
			  
				$meta_key = $this->second_meta_key_prefix . strtolower( $key );
				//update_post_meta($post_ID, $meta_key, );
			  	$rank = 1;
			  	foreach ( $data[$key] as $post_ID => $num ) {
				  	$this->log( '[' . __METHOD__ . '] meta_key: ' . $meta_key . ' SNS: ' . $key . ' post_ID: ' . $post_ID . ' - ' . $rank );
				  	update_post_meta($post_ID, $meta_key, $rank);
				  	$rank++;
			  	}
			}
		}
		*/		
 
	}

  
  	/**
	 * Get transient ID
	 *
	 * @since 0.1.1
	 */  	  
  	private function get_transient_ID( $suffix ) {
	  	return $this->base_transient_prefix . $suffix;
  	}

  	/**
	 * Get short hash code
	 *
	 * @since 0.2.0
	 */	   
  	private function short_hash( $data, $algo = 'CRC32' ) {
	  	return strtr( rtrim( base64_encode( pack('H*', $algo($data) ) ), '=' ), '+/', '-_' );
	}  
  
  	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */	    
	private function log( $message ) {
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
