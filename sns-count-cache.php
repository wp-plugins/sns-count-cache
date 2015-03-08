<?php
/*
Plugin Name: SNS Count Cache
Description: SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.
Version: 0.5.0
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

require_once ( dirname( __FILE__ ) . '/includes/class-common-util.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-wp-cron-util.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-share-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-rush-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-second-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-rescue-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-follow-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-second-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/interface-cache-order.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-export-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-common-data-export-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-common-job-reset-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-data-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-crawler.php' );

if ( ! class_exists( 'SNS_Count_Cache' ) ) {

class SNS_Count_Cache implements Cache_Order {

	/**
	 * Prefix of share cache ID
	 */
	const OPT_SHARE_BASE_TRANSIENT_PREFIX = 'scc_share_count_';
  
  	/**
	 * Meta key for share second cache
	 */	    
  	const OPT_SHARE_2ND_META_KEY_PREFIX = 'scc_share_count_';  
  
	/**
	 * Prefix of follow cache ID
	 */
	const OPT_FOLLOW_BASE_TRANSIENT_PREFIX = 'scc_follow_count_';    

  	/**
	 * Meta key for follow second cache
	 */	    
  	const OPT_FOLLOW_2ND_META_KEY_PREFIX = 'scc_follow_count_';
  
	/**
	 * Interval cheking and caching share count for share base cache
	 */	  
	const OPT_SHARE_BASE_CHECK_INTERVAL = 600;
  
	/**
	 * Number of posts to check at a time for share base cache
	 */	    	
  	const OPT_SHARE_BASE_POSTS_PER_CHECK = 20;

	/**
	 * Interval cheking and caching share count for share rush cache
	 */	  
	const OPT_SHARE_RUSH_CHECK_INTERVAL = 300;  
  
	/**
	 * Term that a content is considered as new content in share rush cache
	 */	 
  	const OPT_SHARE_RUSH_NEW_CONTENT_TERM = 3;  

	/**
	 * Interval for share second cache
	 */	  
	const OPT_SHARE_2ND_CHECK_INTERVAL = 600;
  
	/**
	 * Interval cheking and caching share count for follow base cache
	 */	  
	const OPT_FOLLOW_BASE_CHECK_INTERVAL = 1800;
  
	/**
	 * Interval for follow second cache
	 */	  
	const OPT_FOLLOW_2ND_CHECK_INTERVAL = 600;
  
	/**
	 * Type of data export
	 */	 
  	const OPT_COMMON_DATA_EXPORT_MANUAL = 0; 
  
	/**
	 * Type of data export
	 */	 
  	const OPT_COMMON_DATA_EXPORT_AUTO = 1;
  
	/**
	 * File name of data export
	 */	   	
	const OPT_COMMON_DATA_EXPORT_FILE_NAME = 'sns-count-cache-data.csv';  

	/**
	 * Data export interval
	 */	  
	const OPT_COMMON_DATA_EXPORT_INTERVAL = 43200;

	/**
	 * Data export schedule
	 */	  
	const OPT_COMMON_DATA_EXPORT_SCHEDULE = '0 0 * * *';
    
	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_CACHE_NONE = 0;  
  
	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_SYNC_CACHE = 1;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_ASYNC_CACHE = 2;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_2ND_CACHE = 3;
  
	/**
	 * Type of scheme migration mode
	 */	 
  	const OPT_COMMON_SCHEME_MIGRATION_MODE_OFF = false;

	/**
	 * Type of scheme migration mode
	 */	   
  	const OPT_COMMON_SCHEME_MIGRATION_MODE_ON = true;
    
	/**
	 * Option key for custom post types for share base cache
	 */  
	const DB_SHARE_CUSTOM_POST_TYPES = 'scc_custom_post_types';
    
	/**
	 * Option key for check interval of share base cache
	 */  
	const DB_SHARE_CHECK_INTERVAL = 'scc_check_interval';
  
	/**
	 * Option key for number of posts to check at a time for share base cache
	 */	    
  	const DB_SHARE_POSTS_PER_CHECK = 'scc_posts_per_check';

	/**
	 * Option key for dynamic cache 
	 */	    
  	const DB_COMMON_DYNAMIC_CACHE = 'scc_dynamic_cache_mode';

	/**
	 * Option key for new content term for share rush cache
	 */	    
  	const DB_SHARE_NEW_CONTENT_TERM = 'scc_new_content_term';

	/**
	 * Option key of cache target for share base cache
	 */	    
  	const DB_SHARE_CACHE_TARGET = 'scc_cache_target';

	/**
	 * Option key of cache target for follow base cache
	 */	    
  	const DB_FOLLOW_CACHE_TARGET = 'scc_follow_cache_target';

	/**
	 * Option key of checking interval for follow base cache
	 */  
	const DB_FOLLOW_CHECK_INTERVAL = 'scc_follow_check_interval';

	/**
	 * Option key of data export
	 */  
	const DB_COMMON_DATA_EXPORT = 'scc_data_export_mode';
 
	/**
	 * Option key of data export interval
	 */  
	const DB_COMMON_DATA_EXPORT_INTERVAL = 'scc_data_export_mode_interval';

	/**
	 * Option key of data export schedule
	 */  
	const DB_COMMON_DATA_EXPORT_SCHEDULE = 'scc_data_export_schedule';
  
	/**
	 * Option key of http migration
	 */  
	const DB_COMMON_SCHEME_MIGRATION_MODE = 'scc_scheme_migration_mode';
  
	/**
	 * Slug of the plugin
	 */		
	const DOMAIN = 'sns-count-cache';

 	/**
	 * ID of share base cache
	 */
	const REF_SHARE_BASE = 'share-base'; 
  
 	/**
	 * ID of share rush cache
	 */
	const REF_SHARE_RUSH = 'share-rush'; 

 	/**
	 * ID of share lazy cache
	 */
	const REF_SHARE_LAZY = 'share-lazy'; 

 	/**
	 * ID of share second cache
	 */
	const REF_SHARE_2ND = 'share-second'; 

 	/**
	 * ID of share second cache
	 */
	const REF_SHARE_RESCUE = 'share-rescue'; 
  
 	/**
	 * ID of follow base cache
	 */
	const REF_FOLLOW_BASE = 'follow-base'; 

  	/**
	 * ID of follow lazy cache
	 */
	const REF_FOLLOW_LAZY = 'follow-lazy'; 

 	/**
	 * ID of follow second cache
	 */
	const REF_FOLLOW_2ND = 'follow-second'; 

 	/**
	 * ID of common data export
	 */
	const REF_COMMON_EXPORT = 'common-export'; 

 	/**
	 * ID of common data export
	 */
	const REF_COMMON_CONTROL = 'common-control'; 
    
 	/**
	 * ID of share
	 */
	const REF_SHARE = 'share'; 
    
 	/**
	 * ID of follow
	 */
	const REF_FOLLOW = 'follow'; 
    
  	/**
	 * ID of share count (Twitter)
	 */	
  	const REF_SHARE_TWITTER = 'Twitter';

  	/**
	 * ID of share count (Facebook)
	 */	  
  	const REF_SHARE_FACEBOOK = 'Facebook';
  
  	/**
	 * ID of share count (Google Plus)
	 */	  
  	const REF_SHARE_GPLUS = 'Google+';

  	/**
	 * ID of share count (Hatena Bookmark)
	 */	    
  	const REF_SHARE_HATEBU = 'Hatebu';	

  	/**
	 * ID of share count (Pocket)
	 */	    
	const REF_SHARE_POCKET = 'Pocket';
  
  	/**
	 * ID of share count (Total)
	 */	    
	const REF_SHARE_TOTAL = 'Total';  

  	/**
	 * ID of follow count (Feedly)
	 */	    
	const REF_FOLLOW_FEEDLY = 'Feedly';

 	/**
	 * ID of crawl date
	 */
	const REF_CRAWL_DATE = 'CrawlDate'; 
  
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 */	
	private $version = '0.4.0';

	/**
	 * Instances of crawler
	 */	
	private $crawlers = array();
  
	/**
	 * Instance of cache engine 
	 */	
	private $cache_engines = array();  

	/**
	 * Instance of export engine 
	 */	
	private $export_engines = array();  

	/**
	 * Instance of control engine 
	 */	
	private $control_engines = array();  
  
	/**
	 * Slug of the plugin screen
	 */		
	private $plugin_screen_hook_suffix = array();
  
	/**
	 * Cache target for share base cache
	 */		  
  	private $share_base_cache_target = array();  
  
	/**
	 * Post types to be cached
	 */		  
  	private $share_base_cache_post_types = array( 'post', 'page' );  

	/**
	 * Post types to be cached
	 */		  
  	private $share_base_custom_post_types = array();  
    
	/**
	 * Check interval for share base cahce
	 */		    
  	private $share_base_check_interval = 600;

	/**
	 * Post per check for share base cache
	 */		      
  	private $share_base_posts_per_check = 20;

	/**
	 * Term considering content as new one
	 */  
  	private $share_rush_new_content_term = 3;
  
	/**
	 * Cache target for follow base cache
	 */		  
  	private $follow_base_cache_target = array();  

	/**
	 * Check interval for follow base cache
	 */  
  	private $follow_base_check_interval = 1800;
  
	/**
	 * Dynamic cache mode
	 */		  
  	private $dynamic_cache_mode = 0;
  	  
	/**
	 * Data export mode
	 */		  
  	private $data_export_mode = 0;

	/**
	 * Data export interval
	 */		      
	private $data_export_interval = 3600;

	/**
	 * Data export schedule
	 */		      
  	private $data_export_schedule  = '* * * * *';

	/**
	 * Migration mode from http to https
	 */		      
  	private $scheme_migration_mode = false;

	/**
	 * Excluded key in migration from http to https
	 */		      
  	private $scheme_migration_exclude_keys = array();

  	/**
	 * Max execution time
	 */
  	private $original_max_execution_time = 0;

  	/**
	 * Extended max execution time
	 */
	private $extended_max_execution_time = 300;
  
  	/**
	 * Instance
	 */
  	private static $instance = NULL;
    
	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 */
	private function __construct() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	//load_plugin_textdomain(self::DOMAIN, false, basename(dirname( __FILE__ )) . '/languages');

		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );	  	  
	  
	  	add_action( 'admin_menu', array($this, 'action_admin_menu' ) );
	  
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'register_admin_scripts' ) );

		add_action( 'plugins_loaded', array( $this, 'initialize' ) );  
	}

    /**
     * Get instance
     *
	 * @since 0.1.1
	 */		
	public static function get_instance() {

		$class_name = get_called_class();
		if ( ! self::$instance ) {
			self::$instance = new $class_name();
		}
		
		return self::$instance;
	}
  
    /**
     * Initialization 
     *
	 * @since 0.1.1
	 */		  
  	public function initialize() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$share_base_check_interval = get_option( self::DB_SHARE_CHECK_INTERVAL );
	  	$this->share_base_check_interval = ! empty( $share_base_check_interval ) ? intval( $share_base_check_interval ) : self::OPT_SHARE_BASE_CHECK_INTERVAL;
	  
		$share_base_posts_per_check = get_option( self::DB_SHARE_POSTS_PER_CHECK );	
	  	$this->share_base_posts_per_check = ! empty( $share_base_posts_per_check ) ? intval( $share_base_posts_per_check ) : self::OPT_SHARE_BASE_POSTS_PER_CHECK; 

	  	$follow_base_check_interval = get_option( self::DB_FOLLOW_CHECK_INTERVAL );
	  	$this->follow_base_check_interval = ! empty( $follow_base_check_interval ) ? intval( $follow_base_check_interval ) : self::OPT_FOLLOW_BASE_CHECK_INTERVAL;
	  	  
	  	$dynamic_cache_mode = get_option( self::DB_COMMON_DYNAMIC_CACHE );
	  	$this->dynamic_cache_mode = ! empty( $dynamic_cache_mode ) ? $dynamic_cache_mode : self::OPT_COMMON_ACCESS_BASED_CACHE_NONE;
	  
	  	$share_rush_new_content_term = get_option( self::DB_SHARE_NEW_CONTENT_TERM );
	  	$this->share_rush_new_content_term = ! empty( $share_rush_new_content_term ) ? intval( $share_rush_new_content_term ) : self::OPT_SHARE_RUSH_NEW_CONTENT_TERM;

		$this->share_base_cache_target = get_option( self::DB_SHARE_CACHE_TARGET );
	  	$this->follow_base_cache_target = get_option( self::DB_FOLLOW_CACHE_TARGET );
	  
	  	$data_export_mode = get_option( self::DB_COMMON_DATA_EXPORT );
	  	$this->data_export_mode = isset( $data_export_mode ) ? intval( $data_export_mode ) : self::OPT_COMMON_DATA_EXPORT_MANUAL;

		$data_export_interval = get_option( self::DB_COMMON_DATA_EXPORT_INTERVAL );
		$this->data_export_interval = ! empty( $data_export_interval ) ? intval( $data_export_interval ) : self::OPT_COMMON_DATA_EXPORT_INTERVAL;
	  
	  	$scheme_migration_mode = get_option( self::DB_COMMON_SCHEME_MIGRATION_MODE );
	  	$this->scheme_migration_mode = isset( $scheme_migration_mode ) ? $scheme_migration_mode : self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF;
	  
	  	$this->scheme_migration_exclude_keys = array( self::REF_SHARE_POCKET, self::REF_SHARE_GPLUS );
	  	  
		if ( ! $this->share_base_cache_target ) {
			$this->share_base_cache_target[self::REF_SHARE_TWITTER] = true;
			$this->share_base_cache_target[self::REF_SHARE_GPLUS] = true;
		  	if ( Common_Util::extension_loaded_php_xml() ) {
			  	$this->share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
				$this->share_base_cache_target[self::REF_SHARE_POCKET] = true;
			}
			$this->share_base_cache_target[self::REF_SHARE_HATEBU] = true;
		}
	  
	  	$this->share_base_cache_target[self::REF_CRAWL_DATE] = true;
	  	$this->share_base_cache_target[self::REF_SHARE_TOTAL] = true;
	  	  
	  	if ( ! $this->follow_base_cache_target ) {
	 		$this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
		}
	  
	  	$this->share_base_custom_post_types = get_option( self::DB_SHARE_CUSTOM_POST_TYPES );
	
		if ( ! $this->share_base_custom_post_types ) {
	  		$this->share_base_custom_post_types = array();
		}
	    	
	  	$this->share_base_cache_post_types = array_merge( $this->share_base_cache_post_types, $this->share_base_custom_post_types );

		$data_export_schedule = get_option( self::DB_COMMON_DATA_EXPORT_SCHEDULE );
		$this->data_export_schedule = ! empty( $data_export_schedule  ) ? $data_export_schedule  : self::OPT_COMMON_DATA_EXPORT_SCHEDULE;
	  
	  	// Crawler
	  	$this->crawlers[self::REF_SHARE] = Share_Crawler::get_instance();
	  	$this->crawlers[self::REF_FOLLOW] = Follow_Crawler::get_instance();
	 
	  	// Share base cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
		  	'post_types' => $this->share_base_cache_post_types,		  
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
		  	);
	  
	  	$this->cache_engines[self::REF_SHARE_BASE] = Share_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_BASE]->initialize( $options );
	  
	  	// Share rush cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
		  	'check_interval' => self::OPT_SHARE_RUSH_CHECK_INTERVAL,
			'posts_per_check' => $this->share_base_posts_per_check,
		  	'new_content_term' => $this->share_rush_new_content_term,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
			);

	  	$this->cache_engines[self::REF_SHARE_RUSH] = Share_Rush_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_RUSH]->initialize( $options );
	  
	  	// Share lazy cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
			);	  
			  
	  	$this->cache_engines[self::REF_SHARE_LAZY] = Share_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_LAZY]->initialize( $options );
	  
	  	// Share second cache engine
	  	$options = array(
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => self::OPT_SHARE_2ND_CHECK_INTERVAL,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'meta_key_prefix' => self::OPT_SHARE_2ND_META_KEY_PREFIX,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
			);	 
	  
	  	$this->cache_engines[self::REF_SHARE_2ND] = Share_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_2ND]->initialize( $options );	  

	  	// Share rescue cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
		  	);
	  
	  	$this->cache_engines[self::REF_SHARE_RESCUE] = Share_Rescue_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_RESCUE]->initialize( $options );	  
	  
	  	// Follow base cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => $this->follow_base_check_interval,
		  	'post_types' => $this->share_base_cache_post_types,
			'scheme_migration_mode' => $scheme_migration_mode
		  	);
	  
	  	$this->cache_engines[self::REF_FOLLOW_BASE] = Follow_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_BASE]->initialize( $options );

	  	// Follow lazy cache engine	  
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => $this->follow_base_check_interval, 
		  	'scheme_migration_mode' => $this->scheme_migration_mode
		  	);	  
	  
	  	$this->cache_engines[self::REF_FOLLOW_LAZY] = Follow_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_LAZY]->initialize( $options );

	  	// Follow second cache engine	  	  
	  	$options = array(
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => self::OPT_FOLLOW_2ND_CHECK_INTERVAL,  
		  	'meta_key_prefix' => self::OPT_FOLLOW_2ND_META_KEY_PREFIX
		  	);	  	  
	  
	  	$this->cache_engines[self::REF_FOLLOW_2ND] = Follow_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_2ND]->initialize( $options );
	  
	  	// Data export engine  	  
	  	$options = array(
		  	'export_activation' => $this->data_export_mode,
		  	'export_interval' => $this->data_export_interval,
		  	'export_schedule' => $this->data_export_schedule,
		  	'share_target_sns' => $this->share_base_cache_target,
		  	'follow_target_sns' => $this->follow_base_cache_target,
		  	'export_file_name' => self::OPT_COMMON_DATA_EXPORT_FILE_NAME,
		  	'export_exclude_keys' => array( self::REF_SHARE_TOTAL, self::REF_CRAWL_DATE ),
		  	'post_types' => $this->share_base_cache_post_types
		  	);	  	  
	  
	  	$this->export_engines[self::REF_COMMON_EXPORT] = Common_Data_Export_Engine::get_instance();
		$this->export_engines[self::REF_COMMON_EXPORT]->initialize( $options );

	  	// Job reset engine
	  	$target_crons = array();
	  
	  	foreach ( $this->cache_engines as $key => $cache_engine ) {
		  	$target_crons[] = $cache_engine->get_excute_cron();
	  	}

	  	foreach ( $this->control_engines as $key => $control_engine ) {
		  	$target_crons[] = $control_engine->get_excute_cron();
	  	}
	  	  	  
	  	if ( $this->data_export_mode ) {
	  		$target_crons[] = $this->export_engines[self::REF_COMMON_EXPORT]->get_excute_cron();
	  	}
	  
	  	$options = array(
		  	'delegate' => $this,
		  	'check_interval' => 600,  
		  	'expiration_time ' => 1800,
		  	'target_cron' => $target_crons
		  	);
	  
	  	$this->control_engines[self::REF_COMMON_CONTROL] = Common_Job_Reset_Engine::get_instance();
		$this->control_engines[self::REF_COMMON_CONTROL]->initialize( $options );

	  	// delete old hooks
	  	WP_Cron_Util::clear_scheduled_hook( 'scc_basecache_prime' );
	  	WP_Cron_Util::clear_scheduled_hook( 'scc_rushcache_prime' );	  
		WP_Cron_Util::clear_scheduled_hook( 'scc_2ndcache_prime' );
	  
	  	$tmp_max_execution_time = ini_get( 'max_execution_time' );
	  
	  	if ( isset( $tmp_max_execution_time ) && $tmp_max_execution_time > 0 ) {
	  		$this->original_max_execution_time = $tmp_max_execution_time;
		} else {
		  	$this->original_max_execution_time = 30;
		}
	  
  	}
  
	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_styles() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
		  	return;
		}

		$screen = get_current_screen();
	  
		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
		  	wp_enqueue_style( self::DOMAIN .'-admin-style-1' , plugins_url( ltrim( '/css/sns-count-cache.css', '/' ), __FILE__) );
		  	wp_enqueue_style( self::DOMAIN .'-admin-style-2' , plugins_url( ltrim( '/css/prettify.css', '/' ), __FILE__ ) );
		} 
	} 

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_scripts() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
	  
		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
			wp_enqueue_script( self::DOMAIN . '-admin-script-1' , plugins_url( ltrim( '/js/jquery.sns-count-cache.js', '/' ) , __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( self::DOMAIN . '-admin-script-2' , plugins_url( ltrim( '/js/prettify.js', '/' ) , __FILE__ ), array( 'jquery' ) );
		}
	} 

	/**
	 * Activate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	function activate_plugin() {	  
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->initialize();
  	  
	  	set_time_limit( $this->extended_max_execution_time ); 
	  
	  	foreach ( $this->cache_engines as $key => $cache_engine ) {
			switch ( $key ) {
				case self::REF_SHARE_2ND:
		  			$cache_engine->initialize_cache();
					break;
				case self::REF_FOLLOW_2ND:
		  			$cache_engine->initialize_cache();
					break;			
				default:
		  			$cache_engine->initialize_cache();
		  			$cache_engine->register_schedule();
		  	}
	  	}

	  	foreach ( $this->control_engines as $key => $control_engine ) {
		  	$control_engine->register_schedule();
	  	}
	  	  	  
	  	if ( $this->data_export_mode ) {
	  		$this->export_engines[self::REF_COMMON_EXPORT]->register_schedule();
	  	}
	  
	  	set_time_limit( $this->original_max_execution_time  ); 

	}
  	
	/**
	 * Deactivate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	function deactivate_plugin() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	set_time_limit( $this->extended_max_execution_time ); 
	  	  
	  	foreach ( $this->cache_engines as $key => $cache_engine ) {
		  	$cache_engine->unregister_schedule();
		  	$cache_engine->clear_cache();
	  	}
	  
	  	foreach ( $this->control_engines as $key => $control_engine ) {
		  	$control_engine->unregister_schedule();
	  	}
	  	  
	  	$this->export_engines[self::REF_COMMON_EXPORT]->unregister_schedule();
	  
	  	set_time_limit( $this->original_max_execution_time  ); 

	}

	/**
	 * Reactivate cache engine
	 *
	 * @since 0.1.1
	 */  
  	function reactivate_plugin() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->deactivate_plugin(); 
	  	$this->activate_plugin();
	  			  	  
	}  
  
    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
	 *
	 * @since 0.1.0
	 */
    public function action_admin_menu() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->plugin_screen_hook_suffix[] = add_menu_page( 'SNS Count Cache', 'SNS Count Cache', 8, 'scc-dashboard', array( $this, 'dashboard_page' ), 'dashicons-share' );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', 'Dashboard', 'Dashboard', 8, 'scc-dashboard', array( $this, 'dashboard_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', 'Cache Status', 'Cache Status', 8, 'scc-cache-status', array( $this, 'cache_status_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', 'Share Count', 'Share Count', 8, 'scc-share-count', array( $this, 'share_count_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', 'Setting', 'Setting', 8, 'scc-setting', array( $this, 'setting_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', 'Help', 'Help', 8, 'scc-help', array( $this, 'help_page' ) );
    }

   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	  
    public function dashboard_page() {
	  	include_once( dirname( __FILE__ ) . '/includes/admin-dashboard.php' );
 	}
  
  
   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	  
    public function cache_status_page() {
	  	include_once( dirname( __FILE__ ) . '/includes/admin-cache-status.php' );
 	}
  
   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	
    public function share_count_page() {
		include_once( dirname( __FILE__ ) . '/includes/admin-share-count.php' );
  	}

   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	    
    public function setting_page() {
		include_once( dirname( __FILE__ ) . '/includes/admin-setting.php' );
  	}

   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	    
    public function help_page() {
		include_once( dirname( __FILE__ ) . '/includes/admin-help.php' );
  	}
        
  	/**
	 * Return type of dynamic cache processing
	 *
	 * @since 0.2.0
	 */
  	public function get_dynamic_cache_mode() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->dynamic_cache_mode;
  	}

  	/**
	 * Cache share count for a given post ID
	 *
	 * @since 0.2.0
	 */  
  	public function retrieve_share_cache( $post_ID,  $second_sync = false ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->cache_engines[self::REF_SHARE_BASE]->direct_cache( $post_ID, $second_sync );
  	}

  	/**
	 * Cache follow count 
	 *
	 * @since 0.2.0
	 */  
  	public function retrieve_follow_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->cache_engines[self::REF_FOLLOW_BASE]->direct_cache();
  	}
  
  	/**
	 * Reserve cache processing of share count for a given post ID
	 *
	 * @since 0.2.0
	 */    
  	public function reserve_share_cache( $post_ID ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
	}

  	/**
	 * Reserve cache processing of follow count
	 *
	 * @since 0.4.0
	 */    
  	public function reserve_follow_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
	}  
  
  	/**
	 * Return cache target of share count
	 *
	 * @since 0.2.0
	 */
  	public function get_share_base_cache_target() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
 	
	  	return $this->share_base_cache_target;
  	}

  	/**
	 * Return cache target of follow count
	 *
	 * @since 0.4.0
	 */
  	public function get_follow_base_cache_target() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
 	
	  	return $this->follow_base_cache_target;
  	}
  
  	/**
	 * Method call between one cache engine and another
	 *
	 * @since 0.4.0
	 */  	  
  	public function order_cache( Cache_Engine $engine, $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	switch ( get_class( $engine ) ) {
			case 'Share_Lazy_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options['post_id'], $this->share_base_cache_target, NULL );
		 		break;
		  	case 'Share_Rescue_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options['post_id'], $this->share_base_cache_target, NULL );
		  		break;
		  	case 'Share_Base_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options['post_id'], $this->share_base_cache_target, NULL );
		  		break;
		  	case 'Share_Rush_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options['post_id'], $this->share_base_cache_target, NULL );
		  		break;
		  	case 'Follow_Lazy_Cache_Engine':
		  		$this->cache_engines[self::REF_FOLLOW_2ND]->cache( NULL, $this->follow_base_cache_target, NULL );
		  		break;		  
		  	case 'Follow_Base_Cache_Engine':
		  		$this->cache_engines[self::REF_FOLLOW_2ND]->cache( NULL, $this->follow_base_cache_target, NULL );
		  		break;
		}
	  
  	}
  
  	private function pagination( $numpages = '', $pagerange = '', $paged='', $inherit_param = true ) {
 
  		if ( empty( $pagerange ) ) {
    		$pagerange = 2;
  		}
 
	  	if ( $paged == '' ) { 
	  		global $paged;
	  
  			if ( empty( $paged ) ) {
    			$paged = 1;
  			}
		}
	  
  		if ( $numpages == '' ) {
    		global $wp_query;
		  
    		$numpages = $wp_query->max_num_pages;
    		
		  	if( ! $numpages ) {
        		$numpages = 1;
    		}
  		}
	  
	  	if ( $inherit_param ) {
	  		$pagination_args = array(
    			'base' => get_pagenum_link(1) . '%_%',
    			'format' => '&paged=%#%',
    			'total' => $numpages,
    			'current' => $paged,
    			'show_all' => False,
    			'end_size' => 1,
    			'mid_size' => $pagerange,
    			'prev_next' => True,
    			'prev_text' => __('&laquo;'),
    			'next_text' => __('&raquo;'),
    			'type' => 'plain',
    			'add_args' => false,
    			'add_fragment' => ''
  				);
		} else {
		  
		  	$url = parse_url( get_pagenum_link(1) );
		  	$base_url = $url['scheme'] . '://' . $url['host'] . $url['path'];
		  
		  	parse_str ( $url['query'], $query );
		  
		  	$base_url = $base_url . '?page=' . $query['page'];

		  	$pagination_args = array(
    			'base' => $base_url . '%_%',
    			'format' => '&paged=%#%',
    			'total' => $numpages,
    			'current' => $paged,
    			'show_all' => False,
    			'end_size' => 1,
    			'mid_size' => $pagerange,
    			'prev_next' => True,
    			'prev_text' => __('&laquo;'),
    			'next_text' => __('&raquo;'),
    			'type' => 'plain',
    			'add_args' => false,
    			'add_fragment' => ''
  			);
		  
		}
			
	  	$paginate_links = paginate_links($pagination_args);
  
  		if ( $paginate_links ) {
    		echo "<nav class='pagination'>";
      		echo "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
      		echo $paginate_links;
    		echo "</nav>";
  		}
 
	}
  
}

SNS_Count_Cache::get_instance();

/**
 * Get share count from cache
 *
 * @since 0.4.0
 */     
function scc_get_share( $options = array( 'id' => '', 'url' => '', 'sns' => '' ) ) {
	$transient_ID ='';
  	$sns_key = '';
  	$sns_counts = array();
    
  	if ( $options['id'] ) {
	  	$post_ID = $options['id'];
	} else {
	  	$post_ID = get_the_ID();
	}
  
  	if ( $options['sns'] ) {
	  	$sns_key = $options['sns'];
  	}
  
  	$transient_ID = SNS_Count_Cache::OPT_SHARE_BASE_TRANSIENT_PREFIX . $post_ID;
  
  	if ( false !== ( $sns_counts = get_transient( $transient_ID ) ) ) {
	  	if ( $sns_key ) {
		  
		  	$sns_count = $sns_counts[$sns_key];
		  			  	
			if ( isset( $sns_count ) && $sns_count >= 0 ) { 
				$sns_counts[$sns_key] = $sns_count;
			} else {
				$sns_counts[$sns_key] = 0;
			}					  						  		  	
		  	return $sns_counts[$sns_key];
		} else {
		  	
		  	foreach ( $sns_counts as $key => $value ) {
			  	if ( isset( $value ) && $value >= 0 ) {
				  	$sns_counts[$key] = $value;
				} else {
				  	$sns_counts[$key] = 0;
				}
		  	}		  
		  	return $sns_counts;
		}	  	
	} else {
	  	$sns_count_cache = SNS_Count_Cache::get_instance();
	  	
	  	switch ( $sns_count_cache->get_dynamic_cache_mode() ) {
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
					if ( $sns_key ) {
					  	$sns_counts[$sns_key] = 0;
		  				return $sns_counts[$sns_key];
					} else {					  					  
					  	$base_cache_target = $sns_count_cache->get_share_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
						  	if ( $value ) {
								$sns_counts[$key] = 0;
							}
						}
					  	return $sns_counts;					  
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
		  			$sns_counts = $sns_count_cache->retrieve_share_cache( $post_ID, true );
					if ( $sns_key ) {
		  				return $sns_counts[$sns_key];
					} else {
		  				return $sns_counts;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
		  			$sns_count_cache->reserve_share_cache( $post_ID );
					if ( $sns_key ) {
					  	$sns_counts[$sns_key] = 0;
		  				return $sns_counts[$sns_key];
					} else {					  					  
					  	$base_cache_target = $sns_count_cache->get_share_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
						  	if ( $value ) {
								$sns_counts[$key] = 0;
							}
						}
					  	return $sns_counts;					  
					}  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
		  			$sns_count_cache->reserve_share_cache( $post_ID );
		  			if ( $sns_key ) {
					    $meta_key = SNS_Count_Cache::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $sns_key );
					  	
						$sns_count = get_post_meta( $post_ID, $meta_key, true );
							  	
						if ( isset( $sns_count ) && $sns_count >= 0) { 
							$sns_counts[$sns_key] = $sns_count;
						} else {
							$sns_counts[$sns_key] = 0;
						}					  						  
		  				return $sns_counts[$sns_key];
					} else {
					  	$base_cache_target = $sns_count_cache->get_share_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
					  		if ( $value ) {
							    $meta_key = SNS_Count_Cache::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $key );
							  
							  	$sns_count = get_post_meta( $post_ID, $meta_key, true );
							  	
							  	if ( isset( $sns_count ) && $sns_count >= 0 ) { 
							  		$sns_counts[$key] = $sns_count;
								} else {
								  	$sns_counts[$key] = 0;
								}
							}
						}
					  	return $sns_counts;
					}
		  			break;
		} 
	}
}


/**
 * Get follow count from cache
 *
 * @since 0.4.0
 */     
function scc_get_follow( $options = array( 'id' => '', 'sns' => '' ) ) {
	$transient_ID ='';
  	$sns_key = '';
  	$sns_followers = array();

  	if ( $options['id'] ) {
	  	$post_ID = $options['id'];
	} else {
	  	$post_ID = get_the_ID();
	}  
  
  	if ( $options['sns'] ) {
	  	$sns_key = $options['sns'];
  	}
  
  	$transient_ID = SNS_Count_Cache::OPT_FOLLOW_BASE_TRANSIENT_PREFIX . 'follow';
  
  	if ( false !== ( $sns_followers = get_transient( $transient_ID ) ) ) {
	  	if ( $sns_key ) {
		  	$sns_follower = $sns_followers[$sns_key];
		  	
		  	if ( isset( $sns_follower ) && $sns_follower >= 0 ){
			  	$sns_followers[$sns_key] = $sns_follower;
			} else {
			  	$sns_followers[$sns_key] = 0;
			}
		  
		  	return $sns_followers[$sns_key];
		} else {
		  	foreach ( $sns_followers as $key => $value ) {
			  	if ( isset( $value ) && $value >= 0 ) {
				  	$sns_followers[$key] = $value;
				} else {
				  	$sns_followers[$key] = 0;
				}
		  	}		  		  
		  	return $sns_followers;
		}	  	
	} else {
	  	$sns_count_cache = SNS_Count_Cache::get_instance();
	  	
	  	switch ( $sns_count_cache->get_dynamic_cache_mode() ) {
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
					if ( $sns_key ) {
					  	$sns_followers[$sns_key] = 0;
		  				return $sns_followers[$sns_key];
					} else {
					  	$base_cache_target = $sns_count_cache->get_follow_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
					  		if ( $value ) {
								 $sns_followers[$key] = 0;
							}
						}					  					  	
		  				return $sns_followers;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
		  			$sns_followers = $sns_count_cache->retrieve_follow_cache();
					if ( $sns_key ) {
		  				return $sns_followers[$sns_key];
					} else {
		  				return $sns_followers;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
		  			$sns_count_cache->reserve_follow_cache();
					if ( $sns_key ) {
					  	$sns_followers[$sns_key] = 0;
		  				return $sns_followers[$sns_key];
					} else {
					  	$base_cache_target = $sns_count_cache->get_follow_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
					  		if ( $value ) {
								 $sns_followers[$key] = 0;
							}
						}						  
		  				return $sns_followers;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
		  			$sns_count_cache->reserve_follow_cache();
		  
		  			if ( $sns_key ) {
					    $meta_key = SNS_Count_Cache::OPT_FOLLOW_2ND_META_KEY_PREFIX . strtolower( $sns_key );
					  					  
					  	$sns_follower = get_option( $meta_key );
					  
					  	if ( isset( $sns_follower ) && $sns_follower >= 0 ) { 
					  		$sns_followers[$sns_key] = $sns_follower;
						} else {
						  	$sns_followers[$sns_key] = 0;
						}					  
		  				return $sns_followers[$sns_key];
					} else {
					  	$base_cache_target = $sns_count_cache->get_follow_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
					  		if ( $value ) {
							    $meta_key = SNS_Count_Cache::OPT_FOLLOW_2ND_META_KEY_PREFIX . strtolower( $key );
							  
							  	$sns_follower = get_option( $meta_key );
							  
							  	if ( isset( $sns_follower ) && $sns_follower >= 0 ) { 
							  		$sns_followers[$key] = $sns_follower;
								} else {
								  	$sns_followers[$key] = 0;
								}
							}
						}
					  	return $sns_followers;
					}		
		  			break;
		} 
	}
}  
  

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.4.0
 */       
function scc_get_share_hatebu( $options = array( 'id' => '', 'url' => '' ) ) {
   
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_HATEBU;
  	return scc_get_share( $options );
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.4.0
 */     
function scc_get_share_twitter( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_TWITTER;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.4.0
 */     
function scc_get_share_facebook( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_FACEBOOK;
  	return scc_get_share( $options );
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.4.0
 */     
function scc_get_share_gplus( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_GPLUS;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */     
function scc_get_share_pocket( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_POCKET;
  	return scc_get_share( $options );
    
}
  
/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */     
function scc_get_share_total( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_TOTAL;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */       
function get_scc_hatebu( $options = array( 'id' => '', 'url' => '' ) ) {
   
  	return scc_get_share_hatebu( $options );
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_twitter( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_twitter( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_facebook( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_facebook( $options );
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_gplus( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_gplus( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.2.1
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_pocket( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_pocket( $options );
}
 
/**
 * Get follow count from cache (Feedly)
 *
 * @since 0.2.1
 */     
function scc_get_follow_feedly() {
  
  	$options['sns'] = SNS_Count_Cache::REF_FOLLOW_FEEDLY;
  	return scc_get_follow( $options );
}
  
  
  
  
}

?>
