<?php
/*
data-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals  
Version: 0.2.0
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

class DataCacheEngine {

  	/**
	 * Instance of crawler to get data
	 */	
	private $crawler = NULL;

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
  	private $transient_prefix = 'data_cache';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	private $cron_cache_prime = 'data_cache_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	private $cron_cache_execute = 'data_cache_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	private $event_schedule = 'cache_event';

  	/**
	 * Schedule description for cache processing
	 */	          
  	private $event_description = 'cache event';
  
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
		if( !isset( self::$instance[$class_name] ) ) {
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
  	public function initialize($crawler, $options=array()){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

	  	$this->crawler = $crawler;
	  
	  	if(isset($options['check_interval'])) $this->check_interval = $options['check_interval'];
	  	if(isset($options['posts_per_check'])) $this->posts_per_check = $options['posts_per_check'];
	  	if(isset($options['transient_prefix'])) $this->transient_prefix = $options['transient_prefix'];
		if(isset($options['cron_cache_prime'])) $this->cron_cache_prime = $options['cron_cache_prime'];
		if(isset($options['cron_cache_execute'])) $this->cron_cache_execute = $options['cron_cache_execute'];
		if(isset($options['event_schedule'])) $this->event_schedule = $options['event_schedule'];
	  	if(isset($options['event_description'])) $this->event_description = $options['event_description'];
	  	  
		add_filter('cron_schedules', array($this, 'schedule_check_interval')); 
		add_action($this->cron_cache_prime, array($this, 'prime_data_cache'));
		add_action($this->cron_cache_execute, array($this, 'execute_data_cache'),10,1);	  
  	}
  	
  
  	/**
	 * Register base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function register_base_schedule(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	
		if (!wp_next_scheduled($this->cron_cache_prime)) {
			wp_schedule_event( time(), $this->event_schedule, $this->cron_cache_prime);
		}
	}

  	/**
	 * Unregister base schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function unregister_base_schedule(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

	  	wp_clear_scheduled_hook($this->cron_cache_prime);
	  	$this->clear_scheduled_hook($this->cron_cache_execute);
	}
  
  	/**
	 * Clear scheduled hook based related to specified hook name 
	 *
	 * @since 0.1.1
	 */	     
  	private function clear_scheduled_hook($hook){
		$crons = _get_cron_array();
	  
		if(empty($crons)) return;
			  
		foreach($crons as $timestamp => $cron) {		  
		  	if(isset($cron[$hook])){
			  	foreach ($cron[$hook] as $signature => $data){
						wp_unschedule_event( $timestamp, $hook, $data['args']);
			  	}
		  	}
		}
	  
  	}
  
  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function schedule_check_interval($schedules) {
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		
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
	public function prime_data_cache(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

		$next_exec_time = time() + $this->check_interval;
		$posts_total = $this->get_posts_total();

		$this->log('[' . __METHOD__ . '] check_interval: ' . $this->check_interval);
		$this->log('[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time);
		$this->log('[' . __METHOD__ . '] posts_total: ' . $posts_total);
		
	  	$transient_ID = $this->get_transient_ID('offset');
		  
		if (false === ($posts_offset = get_transient($transient_ID))) {
			$posts_offset = 0;
		}

		$this->log('[' . __METHOD__ . '] posts_offset: ' . $posts_offset);
		
		wp_schedule_single_event($next_exec_time, $this->cron_cache_execute, array($posts_offset)); 
	  	
		$this->log('[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check);
			  
		$posts_offset = $posts_offset + $this->posts_per_check;
	  
		if($posts_offset > $posts_total){
			$posts_offset = 0;
		}

	  	//delete_transient($transient_id);
		set_transient($transient_ID, $posts_offset, $this->check_interval + $this->check_interval); 
		
	}
  
  	/**
	 * Get and cache data of each published post
	 *
	 * @since 0.1.0
	 */	    
	public function execute_data_cache($posts_offset){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		
		$this->log('[' . __METHOD__ . '] posts_offset: ' . $posts_offset);
		$this->log('[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check);
		$this->log('[' . __METHOD__ . '] check_interval: ' . $this->check_interval);
		
	  	$cache_expiration = $this->get_cache_expiration();
		  
		$this->log('[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration);
		
		$query_args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'offset' => $posts_offset,
				'posts_per_page' => $this->posts_per_check,
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query($query_args);

		if($posts_query->have_posts()) {
			while($posts_query->have_posts()){
				$posts_query->the_post();
			  			  
			  	$this->cache_data(get_the_ID(), $cache_expiration);			  
			}
		}
		wp_reset_postdata();
	}
  
  	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */
  	public function restock_data_cache($post_ID){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
		
		$this->log('[' . __METHOD__ . '] posts_per_check: ' . $this->posts_per_check);
		$this->log('[' . __METHOD__ . '] check_interval: ' . $this->check_interval);

	  	$cache_expiration = $this->get_cache_expiration();
		  
		$this->log('[' . __METHOD__ . '] cache_expiration: ' . $cache_expiration);	
	  		  	  
	  	return $this->cache_data($post_ID, $cache_expiration);
	}

  	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	private function cache_data($post_ID, $cache_expiration){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

	  	$this->log('[' . __METHOD__ . '] post_id: ' . $post_ID);

		$transient_ID = $this->get_transient_ID($post_ID);
	  
		$url = get_permalink($post_ID);
								
		$data = $this->crawler->get_data($url);
			  
		$this->log($data);
			  
		$result = set_transient($transient_ID, $data, $cache_expiration); 
			  
		$this->log('[' . __METHOD__ . '] set_transient result: ' . $result);
	  
	  	return $data;
  	}
  
  	public function retrieve_data($post_ID){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
	  	$this->log('[' . __METHOD__ . '] post_id: ' . $post_ID);

		$url = get_permalink($post_ID);
			  
		$data = $this->crawler->get_data($url);

	  	$this->log($data);

		return $data;
  	}	

  	/**
	 * Get transient ID
	 *
	 * @since 0.1.1
	 */  	  
  	private function get_transient_ID($suffix){
	  	return $this->transient_prefix . $suffix;
  	}
  
  	/**
	 * Get cache expiration based on current number of total posts
	 *
	 * @since 0.1.1
	 */	      
  	private function get_cache_expiration(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
	  	$posts_total = $this->get_posts_total();
	  
		$this->log('[' . __METHOD__ . '] posts_total: ' . $posts_total);
	  
		return ((ceil($posts_total / $this->posts_per_check) + 2) * $this->check_interval) + 2 * $this->check_interval;
  	}
  
  	/**
	 * Get total count of current published posts
	 *
	 * @since 0.1.0
	 */	    
	private function get_posts_total(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

		$query_args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'nopaging' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
				);

		$posts_query = new WP_Query($query_args);

		return $posts_query->found_posts;		
	}
  
  	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */	    
	private function log($message) {
    	if (WP_DEBUG === true) {
      		if (is_array($message) || is_object($message)) {
        		error_log(print_r($message, true));
      		} else {
        		error_log($message);
      		}
    	}
  	}
  
  	/**
	 * Set interval cheking and caching target data
	 *
	 * @since 0.1.1
	 */	  	  
  	public function set_check_interval($check_interval) {
	  	$this->check_interval = $check_interval;
	  	$this->unregister_base_schedule();
	  	$this->register_base_schedule();
  	}
  
  	/**
	 * Set number of posts to check at a time
	 *
	 * @since 0.1.1
	 */	  
  	public function set_posts_per_check($posts_per_check) {
	  	$this->posts_per_check = $posts_per_check;
	  	$this->unregister_base_schedule();
	  	$this->register_base_schedule();	  	
  	}
}

?>
