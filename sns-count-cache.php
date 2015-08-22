<?php
/*
Plugin Name: SNS Count Cache
Description: SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.
Version: 0.8.0
Plugin URI: https://wordpress.org/plugins/sns-count-cache/
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

require_once ( dirname( __FILE__ ) . '/includes/class-common-util.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-wp-cron-util.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-sleep-throttle.php' );

require_once ( dirname( __FILE__ ) . '/includes/interface-order.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-share-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-rush-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-second-cache-engine.php' );
//require_once ( dirname( __FILE__ ) . '/includes/class-share-rescue-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-follow-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-base-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-lazy-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-second-cache-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-export-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-common-data-export-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-common-job-reset-engine.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-data-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-follow-crawler.php' );

require_once ( dirname( __FILE__ ) . '/includes/class-analytical-engline.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-share-analytical-engine.php' );

if ( ! class_exists( 'SNS_Count_Cache' ) ) {

final class SNS_Count_Cache implements Order {

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
	const OPT_FOLLOW_BASE_CHECK_INTERVAL = 86400;  
  	const OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN = 3600;
  
	/**
	 * Interval for follow second cache
	 */	  
	const OPT_FOLLOW_2ND_CHECK_INTERVAL = 600;
  
	/**
	 * Type of data export
	 */	 
  	const OPT_COMMON_DATA_EXPORT_MANUAL = 1; 
  
	/**
	 * Type of data export
	 */	 
  	const OPT_COMMON_DATA_EXPORT_SCHEDULER = 2;

	/**
	 * Type of share analysis
	 */	 
  	const OPT_SHARE_VARIATION_ANALYSIS_NONE = 1; 
    
	/**
	 * Type of share analysis
	 */	 
  	const OPT_SHARE_VARIATION_ANALYSIS_MANUAL = 2; 
  
	/**
	 * Type of share analysis
	 */	 
  	const OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER = 3;

	/**
	 * Type of share analysis
	 */	  
	const OPT_SHARE_VARIATION_ANALYSIS_SCHEDULE = '0 0 * * *';
  
	/**
	 * File name of data export
	 */	   	
	const OPT_COMMON_DATA_EXPORT_FILE_NAME = 'sns-count-cache-data.csv';  

	/**
	 * Data export schedule
	 */	  
	const OPT_COMMON_DATA_EXPORT_SCHEDULE = '0 0 * * *';
    
	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_CACHE_NONE = 1;  
  
	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_SYNC_CACHE = 2;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_ASYNC_CACHE = 3;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_COMMON_ACCESS_BASED_2ND_CACHE = 4;
  
	/**
	 * Type of scheme migration mode
	 */	 
  	const OPT_COMMON_SCHEME_MIGRATION_MODE_OFF = false;

	/**
	 * Type of scheme migration mode
	 */	   
  	const OPT_COMMON_SCHEME_MIGRATION_MODE_ON = true;
  
    /**
	 * Error message
	 */	    
  	const OPT_COMMON_ERROR_MESSAGE = 'scc_error_message';

    /**
	 * Update message
	 */	    
  	const OPT_COMMON_UPDATE_MESSAGE = 'scc_update_message';
  
	/**
	 * Type of crawl method
	 */	   
  	const OPT_COMMON_CRAWLER_METHOD_NORMAL = 1;

	/**
	 * Type of crawl method
	 */	   
  	const OPT_COMMON_CRAWLER_METHOD_CURL = 2; 

	/**
	 * Type of crawl ssl verification
	 */	   
  	const OPT_COMMON_CRAWLER_SSL_VERIFY_ON = true;

	/**
	 * Type of crawl ssl verification
	 */	   
  	const OPT_COMMON_CRAWLER_SSL_VERIFY_OFF = false; 

	/**
	 * crawler timeout
	 */	   
  	const OPT_COMMON_CRAWLER_TIMEOUT = 10;

	/**
	 * crawler retry limit
	 */	   
  	const OPT_COMMON_CRAWLER_RETRY_LIMIT = 2;   
  
	/**
	 * Type of feed
	 */	   
  	const OPT_FEED_TYPE_DEFAULT = ''; 
  
	/**
	 * Type of feed
	 */	   
  	const OPT_FEED_TYPE_RSS = 'rss'; 
  
	/**
	 * Type of feed
	 */	   
  	const OPT_FEED_TYPE_RSS2 = 'rss2'; 
  
	/**
	 * Type of feed
	 */	   
  	const OPT_FEED_TYPE_RDF = 'rdf'; 
  
	/**
	 * Type of feed
	 */	   
  	const OPT_FEED_TYPE_ATOM = 'atom'; 
  
	/**
	 * Capability for admin
	 */	   
  	const OPT_COMMON_CAPABILITY = 'manage_options'; 
    
	/**
	 * Option key for custom post types for share base cache
	 */  
	const DB_SHARE_CUSTOM_POST_TYPES = 'share_custom_post_types';
    
	/**
	 * Option key for check interval of share base cache
	 */  
	const DB_SHARE_BASE_CHECK_INTERVAL = 'share_base_check_interval';
  
	/**
	 * Option key for number of posts to check at a time for share base cache
	 */	    
  	const DB_SHARE_POSTS_PER_CHECK = 'share_posts_per_check';

	/**
	 * Option key for dynamic cache 
	 */	    
  	const DB_COMMON_DYNAMIC_CACHE_MODE = 'common_dynamic_cache_mode';

	/**
	 * Option key for new content term for share rush cache
	 */	    
  	const DB_SHARE_RUSH_NEW_CONTENT_TERM = 'share_new_content_term';

	/**
	 * Option key for check interval of share rush cache
	 */  
	const DB_SHARE_RUSH_CHECK_INTERVAL = 'share_rush_check_interval';
    
	/**
	 * Option key of cache target for share base cache
	 */	    
  	const DB_SHARE_CACHE_TARGET = 'share_cache_target';

	/**
	 * Option key of cache target for follow base cache
	 */	    
  	const DB_FOLLOW_CACHE_TARGET = 'follow_cache_target';

	/**
	 * Option key of cache target for follow base cache
	 */	    
  	const DB_FOLLOW_FEED_TYPE = '';
    
	/**
	 * Option key of checking interval for follow base cache
	 */  
	const DB_FOLLOW_CHECK_INTERVAL = 'follow_check_interval';

	/**
	 * Option key of data export
	 */  
	const DB_COMMON_DATA_EXPORT_MODE = 'common_data_export_mode';
 
	/**
	 * Option key of data export schedule
	 */  
	const DB_COMMON_DATA_EXPORT_SCHEDULE = 'common_data_export_schedule';
  
	/**
	 * Option key of http migration
	 */  
	const DB_COMMON_SCHEME_MIGRATION_MODE = 'common_scheme_migration_mode';

	/**
	 * Option key of http migration
	 */  
	const DB_COMMON_SCHEME_MIGRATION_DATE = 'common_scheme_migration_date';
    
	/**
	 * Option key of crawl ssl verification
	 */  
	const DB_COMMON_CRAWLER_SSL_VERIFICATION = 'common_crawler_ssl_verification';  

	/**
	 * Option key of share variation analysis
	 */  
	const DB_SHARE_VARIATION_ANALYSIS_MODE = 'share_variation_analysis_mode';  

	/**
	 * Option key of share variation analysis
	 */  
	const DB_SHARE_VARIATION_ANALYSIS_SCHEDULE = 'share_variation_analysis_schedule';
    
	/**
	 * Option key of setting
	 */  
	const DB_SETTINGS = 'scc_settings';
  
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
	const REF_SHARE_ANALYSIS = 'share-analysis'; 
    
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
	 * ID of share count (Pinterest)
	 */	    
  	const REF_SHARE_PINTEREST = 'Pinterest';	

  	/**
	 * ID of share count (LinkedIn)
	 */	    
	const REF_SHARE_LINKEDIN = 'Linkedin';
  
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
	private $version = '0.8.0';

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
	 * Instance of analytical engine 
	 */	
	private $analytical_engines = array();  
  
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
	 *Check interval for share rush cahce
	 */  
  	private $share_rush_check_interval = 300;
  
	/**
	 * Cache target for follow base cache
	 */		  
  	private $follow_base_cache_target = array();  

	/**
	 * Check interval for follow base cache
	 */  
  	private $follow_base_check_interval = 1800;

	/**
	 * Feed type to be followed
	 */  
  	private $follow_feed_type = '';
  
	/**
	 * Dynamic cache mode
	 */		  
  	private $dynamic_cache_mode = 1;
  	  
	/**
	 * Data export mode
	 */		  
  	private $data_export_mode = 1;

	/**
	 * Data export schedule
	 */		      
  	private $data_export_schedule  = '0 0 * * *';  
  
	/**
	 * Share variation analysis mode
	 */		  
  	private $share_variation_analysis_mode = 1;  
  
	/**
	 * Share variation analysis schedule
	 */		      
  	private $share_variation_analysis_schedule  = '0 0 * * *';

	/**
	 * Migration mode from http to https
	 */		      
  	private $scheme_migration_mode = false;

	/**
	 * Migration date from http to https
	 */		      
  	private $scheme_migration_date = NULL;
  
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
	private $extended_max_execution_time = 600;

  	/**
	 * URL of loding image
	 */  
  	private $loading_img_url = '';

  	/**
	 * ajax action
	 */  
  	private $ajax_action = 'scc_cache_info';
 
  	/**
	 * Cralwer method
	 */    
  	private $crawler_method = 1;

  	/**
	 * Cralwer SSL verification
	 */    
  	private $crawler_ssl_verification = true;
  
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

	  	load_plugin_textdomain( self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );

		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );	  	  
	  
	  	add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	  
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
	  
	  	//add_action( 'admin_notices', array( $this, 'notice_page' ) );
	  
	  	add_action( 'wp_ajax_' . $this->ajax_action, array( $this, 'get_cache_info' ) ); 
	  
	  	add_action( 'wp_dashboard_setup', array( $this, 'add_wp_dashboard_widget' ) );
	  
		add_action( 'plugins_loaded', array( $this, 'initialize' ) );
	  	
		add_action( 'deleted_post' , array( $this, 'clear_cache_deleted_post' ) );

	}

    /**
     * Get instance
     *
	 * @since 0.1.1
	 */		
	public static function get_instance() {
		
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		  
		return self::$instance;
	}

    /**
     * Return object ID
     *
	 * @since 0.6.0
	 */	  
  	public function get_object_id() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$object_id = spl_object_hash( $this );
	  
	  	Common_Util::log( '[' . __METHOD__ . '] object ID: ' . $object_id );
	  
	  	return $object_id;
  	}
  
    /**
     * Inhibit clone
     *
	 * @since 0.6.0
	 */	  
  	final public function __clone() {
	  	throw new Exception('Clone is not allowed against' . get_class( $this ) ); 
  	}
    
    /**
     * Initialization 
     *
	 * @since 0.1.1
	 */		  
  	public function initialize() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	    	  
	  	$settings = get_option( self::DB_SETTINGS );

	  	if ( isset( $settings[self::DB_SHARE_BASE_CHECK_INTERVAL] ) && $settings[self::DB_SHARE_BASE_CHECK_INTERVAL] ) {
		  	$this->share_base_check_interval = (int) $settings[self::DB_SHARE_BASE_CHECK_INTERVAL];
		} else {
		  	$this->share_base_check_interval = self::OPT_SHARE_BASE_CHECK_INTERVAL;
		}
	  
	  	if ( isset( $settings[self::DB_SHARE_POSTS_PER_CHECK] ) && $settings[self::DB_SHARE_POSTS_PER_CHECK] ) {
		  	$this->share_base_posts_per_check = (int) $settings[self::DB_SHARE_POSTS_PER_CHECK];
		} else {
		  	$this->share_base_posts_per_check = self::OPT_SHARE_BASE_POSTS_PER_CHECK;
		}
	  
	  	if ( isset( $settings[self::DB_FOLLOW_CHECK_INTERVAL] ) && $settings[self::DB_FOLLOW_CHECK_INTERVAL] ) {
		  	$this->follow_base_check_interval = (int) $settings[self::DB_FOLLOW_CHECK_INTERVAL];
		  	if ( $this->follow_base_check_interval < self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN ) {
			  	$this->follow_base_check_interval = self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN;
		  	}		  
		} else {
		   	$this->follow_base_check_interval = self::OPT_FOLLOW_BASE_CHECK_INTERVAL;
		}
  
	  	if ( isset( $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] ) && $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] ) {
		  	$this->dynamic_cache_mode = (int) $settings[self::DB_COMMON_DYNAMIC_CACHE_MODE];
		} else {
		  	$this->dynamic_cache_mode = self::OPT_COMMON_ACCESS_BASED_2ND_CACHE;
		}

	  	if ( isset( $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] ) && $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] ) {
		  	$this->share_rush_check_interval = (int) $settings[self::DB_SHARE_RUSH_CHECK_INTERVAL];
		} else {
		  	$this->share_rush_check_interval = self::OPT_SHARE_RUSH_CHECK_INTERVAL;
		}	  
	  
	 	if ( isset( $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] ) && $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] ) {
		  	$this->share_rush_new_content_term = (int) $settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM];
		} else {
		  	$this->share_rush_new_content_term = self::OPT_SHARE_RUSH_NEW_CONTENT_TERM;
		}

	  	if ( isset( $settings[self::DB_COMMON_DATA_EXPORT_MODE] ) && $settings[self::DB_COMMON_DATA_EXPORT_MODE] ) {
		  	$this->data_export_mode = (int) $settings[self::DB_COMMON_DATA_EXPORT_MODE];
		} else {
		  	$this->data_export_mode = self::OPT_COMMON_DATA_EXPORT_MANUAL;
		}
	  
	  	if ( isset( $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] ) && $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] ) {
		  	$this->data_export_schedule = $settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE];
		} else {
		  	$this->data_export_schedule = self::OPT_COMMON_DATA_EXPORT_SCHEDULE;
		}	 

	  	if ( isset( $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] ) && $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] ) {
		  	$this->share_variation_analysis_mode = (int) $settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE];
		} else {
		  	$this->share_variation_analysis_mode = self::OPT_SHARE_VARIATION_ANALYSIS_NONE;
		}

	  	if ( isset( $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] ) && $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] ) {
		  	$this->share_variation_analysis_schedule = $settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE];
		} else {
		  	$this->share_variation_analysis_schedule = self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULE;
		}	 
	  	  
	  	if ( isset( $settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] ) ) {
		  	$this->scheme_migration_mode = $settings[self::DB_COMMON_SCHEME_MIGRATION_MODE];
		} else {
		  	$this->scheme_migration_mode = self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF;
		}

	  	if ( isset( $settings[self::DB_COMMON_SCHEME_MIGRATION_DATE] ) ) {
		  	$this->scheme_migration_date = $settings[self::DB_COMMON_SCHEME_MIGRATION_DATE];
		} 
	  
	  	// Pocket and Google+ are excluded from migration target because they are migrated automatically.
	  	$this->scheme_migration_exclude_keys = array( self::REF_SHARE_POCKET, self::REF_SHARE_GPLUS );
	  
	  	if ( isset( $settings[self::DB_SHARE_CACHE_TARGET] ) && $settings[self::DB_SHARE_CACHE_TARGET] ) {
			$this->share_base_cache_target = $settings[self::DB_SHARE_CACHE_TARGET];
		} else {
			$this->share_base_cache_target[self::REF_SHARE_TWITTER] = true;
			$this->share_base_cache_target[self::REF_SHARE_GPLUS] = true;		  	
			$this->share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
			$this->share_base_cache_target[self::REF_SHARE_POCKET] = true;		  	
			$this->share_base_cache_target[self::REF_SHARE_HATEBU] = true;
		}
	  
	  	$this->share_base_cache_target[self::REF_CRAWL_DATE] = true;
	  	$this->share_base_cache_target[self::REF_SHARE_TOTAL] = true;	  	

	  	if ( isset( $settings[self::DB_FOLLOW_CACHE_TARGET] ) && $settings[self::DB_FOLLOW_CACHE_TARGET] ) {
		  	$this->follow_base_cache_target = $settings[self::DB_FOLLOW_CACHE_TARGET];
		} else {
		  	$this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
		} 
	  
	  	if ( isset( $settings[self::DB_FOLLOW_FEED_TYPE] ) && $settings[self::DB_FOLLOW_FEED_TYPE] ) {
		  	$this->follow_feed_type = $settings[self::DB_FOLLOW_FEED_TYPE];
		} else {
		  	$this->follow_feed_type = self::OPT_FEED_TYPE_DEFAULT;
		}	 	  
		  		 	  
	  	if ( isset( $settings[self::DB_SHARE_CUSTOM_POST_TYPES] ) && $settings[self::DB_SHARE_CUSTOM_POST_TYPES] ) {
		  	$this->share_base_custom_post_types = $settings[self::DB_SHARE_CUSTOM_POST_TYPES];
		} else {
		  	$this->share_base_custom_post_types = array();	  	
		}
	  	  	    	
	  	$this->share_base_cache_post_types = array_merge( $this->share_base_cache_post_types, $this->share_base_custom_post_types );
	  
		if ( extension_loaded( 'curl' ) ) {
		  	$this->crawler_method = self::OPT_COMMON_CRAWLER_METHOD_CURL;
		} else {
			$this->crawler_method = self::OPT_COMMON_CRAWLER_METHOD_NORMAL;
		}

	  	if ( isset( $settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] ) ) {
		  	$this->crawler_ssl_verification = $settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION];
		} else {
		  	$this->crawler_ssl_verification = self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON;
		}
	  
	  	// Crawler
	  	$options = array(
		  	'crawl_method' => $this->crawler_method,
		  	'timeout' => self::OPT_COMMON_CRAWLER_TIMEOUT,
		  	'retry_limit' => self::OPT_COMMON_CRAWLER_RETRY_LIMIT, 
		  	'ssl_verification' => $this->crawler_ssl_verification  	
		  	);
	  	
	  	$this->crawlers[self::REF_SHARE] = Share_Crawler::get_instance();
	  	$this->crawlers[self::REF_SHARE]->initialize( $options );
	  
	  	$this->crawlers[self::REF_FOLLOW] = Follow_Crawler::get_instance();
	  	$this->crawlers[self::REF_FOLLOW]->initialize( $options );
	 
	  	// Share base cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
			'posts_per_check' => $this->share_base_posts_per_check,
		  	'post_types' => $this->share_base_cache_post_types,		  
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_date' => $this->scheme_migration_date,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
		  	);
	  
	  	$this->cache_engines[self::REF_SHARE_BASE] = Share_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_BASE]->initialize( $options );
	  
	  	// Share rush cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_SHARE],
		  	'target_sns' => $this->share_base_cache_target,
		  	'check_interval' => $this->share_rush_check_interval,
			'posts_per_check' => 30,
		  	'new_content_term' => $this->share_rush_new_content_term,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_date' => $this->scheme_migration_date,
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
		  	'scheme_migration_date' => $this->scheme_migration_date,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys
			);	  
			  
	  	$this->cache_engines[self::REF_SHARE_LAZY] = Share_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_LAZY]->initialize( $options );
	  
	  	// Share second cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => self::OPT_SHARE_2ND_CHECK_INTERVAL,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'cache_prefix' => self::OPT_SHARE_2ND_META_KEY_PREFIX,
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'scheme_migration_exclude_keys' => $this->scheme_migration_exclude_keys,
		  	'crawl_date_key' => self::REF_CRAWL_DATE
			);	 
	  
	  	$this->cache_engines[self::REF_SHARE_2ND] = Share_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_SHARE_2ND]->initialize( $options );	  

	  /*
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
		*/
	  
	  	// Follow base cache engine
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => $this->follow_base_check_interval,
			'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'feed_type' => $this->follow_feed_type
		  	);
	  
	  	$this->cache_engines[self::REF_FOLLOW_BASE] = Follow_Base_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_BASE]->initialize( $options );

	  	// Follow lazy cache engine	  
	  	$options = array(
		  	'delegate' => $this,
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => $this->follow_base_check_interval, 
		  	'scheme_migration_mode' => $this->scheme_migration_mode,
		  	'feed_type' => $this->follow_feed_type
		  	);	  
	  
	  	$this->cache_engines[self::REF_FOLLOW_LAZY] = Follow_Lazy_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_LAZY]->initialize( $options );

	  	// Follow second cache engine	  	  
	  	$options = array(
		  	'crawler' => $this->crawlers[self::REF_FOLLOW],
		  	'target_sns' => $this->follow_base_cache_target,
		  	'check_interval' => self::OPT_FOLLOW_2ND_CHECK_INTERVAL,  
		  	'cache_prefix' => self::OPT_FOLLOW_2ND_META_KEY_PREFIX
		  	);	  	  
	  
	  	$this->cache_engines[self::REF_FOLLOW_2ND] = Follow_Second_Cache_Engine::get_instance();
		$this->cache_engines[self::REF_FOLLOW_2ND]->initialize( $options );
	  
	  	// Data export engine  	  
	  	$options = array(
		  	'export_activation' => $this->data_export_mode,
		  	'export_schedule' => $this->data_export_schedule,
		  	'share_target_sns' => $this->share_base_cache_target,
		  	'follow_target_sns' => $this->follow_base_cache_target,
		  	'export_file_name' => self::OPT_COMMON_DATA_EXPORT_FILE_NAME,
		  	'export_exclude_keys' => array( self::REF_SHARE_TOTAL, self::REF_CRAWL_DATE ),
		  	'post_types' => $this->share_base_cache_post_types
		  	);	  	  
	  
	  	$this->export_engines[self::REF_COMMON_EXPORT] = Common_Data_Export_Engine::get_instance();
		$this->export_engines[self::REF_COMMON_EXPORT]->initialize( $options );

	  	// Share analytical engine
	  	$options = array(
		  	'delegate' => $this,
		  	'target_sns' => $this->share_base_cache_target,
			'check_interval' => $this->share_base_check_interval,
		  	'post_types' => $this->share_base_cache_post_types,
		  	'base_schedule' => $this->share_variation_analysis_schedule,
		  	'crawl_date_key' => self::REF_CRAWL_DATE
		  	);
	  
	  	$this->analytical_engines[self::REF_SHARE_ANALYSIS] = Share_Analytical_Engine::get_instance();
	  	$this->analytical_engines[self::REF_SHARE_ANALYSIS]->initialize( $options );	  
	  
	  	// Job reset engine
	  	$target_crons = array();
	  
	  	foreach ( $this->cache_engines as $key => $cache_engine ) {
		  	$target_crons[] = $cache_engine->get_excute_cron();
	  	}

	  	foreach ( $this->control_engines as $key => $control_engine ) {
		  	$target_crons[] = $control_engine->get_excute_cron();
	  	}
	  	  	  
	  	if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
	  		$target_crons[] = $this->export_engines[self::REF_COMMON_EXPORT]->get_excute_cron();
	  	}
	  
	  	if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
		  	$target_crons[] = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_excute_cron();
		}
	  
	  	$options = array(
		  	'delegate' => $this,
		  	'check_interval' => 600,  
		  	'expiration_time ' => 1800,
		  	'target_cron' => $target_crons
		  	);
	  
	  	$this->control_engines[self::REF_COMMON_CONTROL] = Common_Job_Reset_Engine::get_instance();
		$this->control_engines[self::REF_COMMON_CONTROL]->initialize( $options );
	    	  
	  	$tmp_max_execution_time = ini_get( 'max_execution_time' );
	  
	  	if ( isset( $tmp_max_execution_time ) && $tmp_max_execution_time > 0 ) {
	  		$this->original_max_execution_time = $tmp_max_execution_time;
		} else {
		  	$this->original_max_execution_time = 30;
		}
	  
	  	$this->loading_img_url = plugins_url( '/images/loading.gif', __FILE__ );
	  
	  	// delete old hooks
	  	WP_Cron_Util::clear_scheduled_hook( 'scc_basecache_prime' );
	  	WP_Cron_Util::clear_scheduled_hook( 'scc_rushcache_prime' );	  
		WP_Cron_Util::clear_scheduled_hook( 'scc_2ndcache_prime' );
	  	WP_Cron_Util::clear_scheduled_hook( 'scc_share_rescuecache_prime' );
	
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
		  	wp_enqueue_style( self::DOMAIN .'-admin-style-1', plugins_url( ltrim( '/css/sns-count-cache.css', '/' ), __FILE__) );
		  	wp_enqueue_style( self::DOMAIN .'-admin-style-2', plugins_url( ltrim( '/css/prettify.css', '/' ), __FILE__ ) );
		  	wp_enqueue_style( 'jquery-ui-datepicker-style' , '//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css');
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
			wp_enqueue_script( self::DOMAIN . '-admin-script-1', plugins_url( ltrim( '/js/prettify.js', '/' ) , __FILE__ ), array( 'jquery' ) );
		  
		  	wp_enqueue_script( self::DOMAIN . '-admin-script-2', plugins_url( ltrim( '/js/jquery.scc-cache-info.min.js', '/' ), __FILE__ ), array( 'jquery' ) );
		  	wp_localize_script( self::DOMAIN . '-admin-script-2', 'scc', array( 'endpoint' => admin_url( 'admin-ajax.php' ), 'action' => $this->ajax_action, 'nonce' => wp_create_nonce( $this->ajax_action ) ) );

			wp_enqueue_script( 'jquery-ui-datepicker' );
		}
	} 

	/**
	 * Activate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	public function activate_plugin() {	  
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

	  	if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
		  	$this->analytical_engines[self::REF_SHARE_ANALYSIS]->register_schedule();
	  	}
	  
	  	if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
	  		$this->export_engines[self::REF_COMMON_EXPORT]->register_schedule();
	  	}
	  
	  	set_time_limit( $this->original_max_execution_time  ); 

	}
  	
	/**
	 * Deactivate cache engine (schedule cron)
	 *
	 * @since 0.1.1
	 */
	public function deactivate_plugin() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	set_time_limit( $this->extended_max_execution_time ); 
	  	  
	  	foreach ( $this->cache_engines as $key => $cache_engine ) {
		  	$cache_engine->unregister_schedule();
		  	$cache_engine->clear_cache();
	  	}
	  
	  	// compatibility for old version
		$query_args = array(
			'post_type' => $this->share_base_cache_post_types,
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
			  	
				foreach ( $this->follow_base_cache_target as $sns => $active ) {
				  					    
					if ( $active ) {
				  		$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
					  
						delete_post_meta( $post_ID, $meta_key );
					}
				}		  	 
			}
		}
		wp_reset_postdata();	  
	  
	  	foreach ( $this->control_engines as $key => $control_engine ) {
		  	$control_engine->unregister_schedule();
	  	}

		$this->analytical_engines[self::REF_SHARE_ANALYSIS]->unregister_schedule();
	  	$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base();
	  
	  	$this->export_engines[self::REF_COMMON_EXPORT]->unregister_schedule();
	  
	  	set_time_limit( $this->original_max_execution_time ); 

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
     * Delete related cache when the post was deleted.
     *
	 * @since 0.7.0
	 */	   
	public function clear_cache_deleted_post( $post_id ) {
	  
	  	if ( isset( $post_id ) ) {
		  	$this->cache_engines[self::REF_SHARE_BASE]->clear_cache_by_post_id( $post_id );
	  		$this->cache_engines[self::REF_SHARE_2ND]->clear_cache_by_post_id( $post_id );
	  		$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base_by_post_id( $post_id );
		}
	}  
  
    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
	 *
	 * @since 0.1.0
	 */
    public function register_admin_menu() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->plugin_screen_hook_suffix[] = 'dashboard';
	  	$this->plugin_screen_hook_suffix[] = add_menu_page( __( 'SNS Count Cache', self::DOMAIN ), __( 'SNS Count Cache', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-dashboard', array( $this, 'dashboard_page' ), 'dashicons-share' );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Dashboard | SNS Count Cache', self::DOMAIN ), __( 'Dashboard', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-dashboard', array( $this, 'dashboard_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Cache Status | SNS Count Cache', self::DOMAIN ), __( 'Cache Status', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-cache-status', array( $this, 'cache_status_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Share Count | SNS Count Cache', self::DOMAIN ), __( 'Share Count', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-share-count', array( $this, 'share_count_page' ) );

	  	if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
	  		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Hot Content | SNS Count Cache', self::DOMAIN ), __( 'Hot Content', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-hot-content', array( $this, 'hot_content_page' ) );
		}
	  
		$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Setting | SNS Count Cache', self::DOMAIN ), __( 'Setting', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-setting', array( $this, 'setting_page' ) );
	  	$this->plugin_screen_hook_suffix[] = add_submenu_page( 'scc-dashboard', __( 'Help | SNS Count Cache', self::DOMAIN ), __( 'Help', self::DOMAIN ), self::OPT_COMMON_CAPABILITY, 'scc-help', array( $this, 'help_page' ) );
    }

   /**
     * Add widget to wordpress dashboard
     *
	 * @since 0.5.1
	 */	    
  	public function add_wp_dashboard_widget() {
	  
		if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
    		return false;  
  		}
	  	
	  	wp_add_dashboard_widget( 'scc_dashboard', 'SNS Count Cache', array( $this, 'wp_dashboard_page' ) );	  	
	}
  
   /**
     * Option page implementation
     *
	 * @since 0.5.1
	 */	  	
 	public function wp_dashboard_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
		  	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-dashboard-widget.php' );
	}
  
  
   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	  
    public function dashboard_page() {
	  
	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-dashboard.php' );
 	}
  
  
   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	  
    public function cache_status_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-cache-status.php' );
 	}
  
   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	
    public function share_count_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-share-count.php' );
  	}

   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	    
    public function setting_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include( dirname( __FILE__ ) . '/includes/admin-setting.php' );
  	}

   /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	    
    public function help_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-help.php' );
  	}

   /**
     * Option page implementation
     *
	 * @since 0.5.1
	 */	     
  	public function notice_page() {

	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-notice.php' );
  	}	

   /**
    * Option page implementation
    *
	* @since 0.6.1
 	*/	       
  	public function hot_content_page() {
	  
	  	if ( ! current_user_can( self::OPT_COMMON_CAPABILITY ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	  	}
	  
	  	include_once( dirname( __FILE__ ) . '/includes/admin-hot-content.php' );  		
	}
  
  
  	/**
	 * Cache share count for a given post ID
	 *
	 * @since 0.2.0
	 */  
  	private function retrieve_share_cache( $post_ID,  $second_sync = false ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->cache_engines[self::REF_SHARE_BASE]->direct_cache( $post_ID, $second_sync );
  	}

  	/**
	 * Cache follow count 
	 *
	 * @since 0.2.0
	 */  
  	private function retrieve_follow_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->cache_engines[self::REF_FOLLOW_BASE]->direct_cache();
  	}
  
  	/**
	 * Reserve cache processing of share count for a given post ID
	 *
	 * @since 0.2.0
	 */    
  	private function reserve_share_cache( $post_ID ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
	}

  	/**
	 * Reserve cache processing of follow count
	 *
	 * @since 0.4.0
	 */    
  	private function reserve_follow_cache() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
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
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
		 		break;
		  	case 'Share_Rescue_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
		  		break;
		  	case 'Share_Base_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
		  		break;
		  	case 'Share_Rush_Cache_Engine':
		  		$this->cache_engines[self::REF_SHARE_2ND]->cache( $options );
		  		break;
		  	case 'Follow_Lazy_Cache_Engine':
		  		$this->cache_engines[self::REF_FOLLOW_2ND]->cache( $options );
		  		break;		  
		  	case 'Follow_Base_Cache_Engine':
		  		$this->cache_engines[self::REF_FOLLOW_2ND]->cache( $options );
		  		break;
		}
	  
  	}

  	/**
	 * Method call between one cache engine and another
	 *
	 * @since 0.6.1
	 */  	  
  	public function order_analysis( Cache_Engine $engine, $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
	  		switch ( get_class( $engine ) ) {
				case 'Share_Second_Cache_Engine':
		  			$this->analytical_engines[self::REF_SHARE_ANALYSIS]->analyze( $options );
		 			break;
			}
		}
	  
  	}  
  
  	/**
	 * Return pagination
	 *
	 * @since 0.4.0
	 */  
  	private function pagination( $numpages = '', $pagerange = '', $paged='', $inherit_param = true ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
 
  		if ( empty( $pagerange ) ) {
    		$pagerange = 2;
  		}
 
	  	if ( $paged === '' ) { 
	  		global $paged;
	  
  			if ( empty( $paged ) ) {
    			$paged = 1;
  			}
		}
	  
  		if ( $numpages === '' ) {
    		global $wp_query;
		  
    		$numpages = $wp_query->max_num_pages;
    		
		  	if( ! $numpages ) {
        		$numpages = 1;
    		}
  		}
	  
	  	$pagination_args = array();
	    	
	 	$url = parse_url( get_pagenum_link(1) );
		$base_url = $url['scheme'] . '://' . $url['host'] . $url['path'];
		  
		parse_str ( $url['query'], $query );
		 
		$base_url = $base_url . '?page=' . $query['page'];
		  
		Common_Util::log( '[' . __METHOD__ . '] base url: ' . $base_url );

		$pagination_args = array(
    		'base' => $base_url . '%_%',
    		'format' => '&paged=%#%',
    		'total' => $numpages,
    		'current' => $paged,
    		'show_all' => false,
    		'end_size' => 1,
    		'mid_size' => $pagerange,
    		'prev_next' => true,
    		'prev_text' => __('&laquo;'),
    		'next_text' => __('&raquo;'),
    		'type' => 'plain',
    		'add_args' => '',
    		'add_fragment' => ''
  			);
		
		$paginate_links = paginate_links( $pagination_args );
	  
	  	if ( $inherit_param ) {
		  	Common_Util::log( '[' . __METHOD__ . '] inherit param: true' );					  
		} else {
		  	Common_Util::log( '[' . __METHOD__ . '] inherit param: false' );
		  		  
		  	$pattern = '/(?:&#038;action=cache&#038;post_id=[0-9]+&#038;_wpnonce=.{10})/';
		  	$paginate_links = preg_replace( $pattern, '', $paginate_links );		  		  		  	  
		}
	    
  		if ( $paginate_links ) {
    		echo "<nav class='pagination'>";
      		echo "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
      		echo $paginate_links;
    		echo "</nav>";
  		}
			
	}

  	/**
	 * Return cache information through ajax interface
	 *
	 * @since 0.5.1
	 */    
  	public function get_cache_info() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], $this->ajax_action ) ) {
			if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {
			  
				$share_base_cache_target = $this->share_base_cache_target;
			  
				unset( $share_base_cache_target[self::REF_CRAWL_DATE] );
				  
				$posts_count = 0;
				$primary_full_cache_count = 0;
				$primary_partial_cache_count = 0;
				$primary_no_cache_count = 0;
									
				$secondary_full_cache_count = 0;
				$secondary_partial_cache_count = 0;
				$secondary_no_cache_count = 0;

				$sum = array();
			  	$delta = array();
				$return = array();
	  
				foreach ( $share_base_cache_target as $sns => $active ) {
					if( $active ){
						$sum[$sns] = 0;
			  			$delta[$sns] = 0;
					}
				}

				$query_args = array(
					'post_type' => $this->share_base_cache_post_types,
					'post_status' => 'publish',
					'nopaging' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false
					);

				set_time_limit( $this->extended_max_execution_time  );
	  
			  	//home
				++$posts_count;

			  	$full_cache_flag = true;
				$partial_cache_flag = false;		
			  
				$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( 'home' );
			  
				if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {

					foreach ( $share_base_cache_target as $sns => $active ) {
						if ( $active ) {										  
							if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
								$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
							  	$partial_cache_flag = true;
							} else {
							  	$full_cache_flag = false;
							}
						}
					}				  
				  
					if ( $partial_cache_flag && $full_cache_flag ) {
						++$primary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$primary_partial_cache_count;
					} else {
						++$primary_no_cache_count;
					}
				  
			  		$full_cache_flag = true;
					$partial_cache_flag = false;					  	
				  
				  	$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );
				  
					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {	
										  
						foreach ( $share_base_cache_target as $sns => $active ) {
							if( $active ){
										  
								if ( $sns_counts[$sns] >= 0 ) {
					  				$partial_cache_flag  = true;
								} else {
					  				$full_cache_flag = false;
								}
							}										  							  								 										  
						}
					} else {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if( $active ){
					  			$full_cache_flag = false;
							}
						}
					}				  
				  
					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;								  
					} else {
						++$secondary_no_cache_count;
					}						  
			
				} else {
			  				  
				  	$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );
				  
					if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {	
										  
						foreach ( $share_base_cache_target as $sns => $active ) {
							if( $active ){
										  
								if ( $sns_counts[$sns] >= 0 ) {
								  	$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
					  				$partial_cache_flag  = true;
								} else {
					  				$full_cache_flag = false;
								}
							}										  							  								 										  
						}
					} else {
						foreach ( $share_base_cache_target as $sns => $active ) {
							if( $active ){
					  			$full_cache_flag = false;
							}
						}
					}				  
				  						  
					if ( $partial_cache_flag && $full_cache_flag ) {
						++$secondary_full_cache_count;
					} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
						++$secondary_partial_cache_count;								  
					} else {
						++$secondary_no_cache_count;
					}

					++$primary_no_cache_count;	
						  
				}
			  
			  	$option_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( 'home' );
			  
				if ( false !== ( $sns_deltas = get_option( $option_key ) ) ) {	
				  
					foreach ( $share_base_cache_target as $sns => $active ) {
						if( $active ){
										  
							if ( isset( $sns_deltas[$sns] ) ) {
								$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
							} 
						}										  							  								 										  
					}
				} 				 			  	
			  
			  	//page, post
				$site_query = new WP_Query( $query_args );
			
				if ( $site_query->have_posts() ) {
					while ( $site_query->have_posts() ) {
						$site_query->the_post();
										  
						++$posts_count;

			  			$full_cache_flag = true;
						$partial_cache_flag = false;		
			  
					  	$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( get_the_ID() );
											  
						if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {

							foreach ( $share_base_cache_target as $sns => $active ) {
								if ( $active ) {
								  								  
									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
										$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
							  			$partial_cache_flag = true;
									} else {
							  			$full_cache_flag = false;
									}
								}
							}				  
		  						  
							if ( $partial_cache_flag && $full_cache_flag ) {
								++$primary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$primary_partial_cache_count;
							} else {
								++$primary_no_cache_count;
							}
				  
			  				$full_cache_flag = true;
							$partial_cache_flag = false;
						  
						  	$sns_counts = array();
						  	$sns_deltas = array();
				  
							foreach ( $share_base_cache_target as $sns => $active ) {
						  
								if ( $active ) {
							
								  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
									$sns_counts[$sns] = get_post_meta( get_the_ID(), $meta_key, true );
															
									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' &&  $sns_counts[$sns] >= 0 ) {
							  			$partial_cache_flag  = true;
									} else {
							  			$full_cache_flag = false;
									}
								  
								  	if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
								  		//delta
								  		$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );

								  		$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );
								  
										if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' ) {
									  		$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
										}
									}
								}
							}

							if ( $partial_cache_flag && $full_cache_flag ) {
								++$secondary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$secondary_partial_cache_count;								  
							} else {
								++$secondary_no_cache_count;
							}						  
			
						} else {
			  
						  	$sns_deltas = array();
						  	$sns_counts = array();
						  
							foreach ( $share_base_cache_target as $sns => $active ) {
						  
								if( $active ){
								  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );

								  	$sns_counts[$sns] = get_post_meta( get_the_ID(), $meta_key, true );
															
									if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' &&  $sns_counts[$sns] >= 0 ) {
										$sum[$sns] = $sum[$sns] + $sns_counts[$sns];
							  			$partial_cache_flag  = true;
									} else {
							  			$full_cache_flag = false;
									}
								  	
								  	if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
								  		//delta
								  		$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );

								  		$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );
								  
										if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' ) {
									  		$delta[$sns] = $delta[$sns] + $sns_deltas[$sns];
										}
									}
								}
							}

							if ( $partial_cache_flag && $full_cache_flag ) {
								++$secondary_full_cache_count;
							} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
								++$secondary_partial_cache_count;								  
							} else {
								++$secondary_no_cache_count;
							}

							++$primary_no_cache_count;	
						  
						}
					}
					wp_reset_postdata();
				}
		
				set_time_limit( $this->original_max_execution_time  );
	  
	  			foreach ( $share_base_cache_target as $sns => $active ) {
					
				  	if ( $active && isset( $sum[$sns] ) ) {
				  		if ( $sns === self::REF_SHARE_GPLUS ){
							$return['share_count']['gplus'] = number_format( (int) $sum[$sns] );
						  	$return['share_delta']['gplus'] = number_format( (int) $delta[$sns] );
						} else {
							$return['share_count'][strtolower( $sns )] = number_format( (int) $sum[$sns] );
							$return['share_delta'][strtolower( $sns )] = number_format( (int) $delta[$sns] );
						}
					}
				}	  	

	  			$return['post_count'] = $posts_count;
	  			$return['primary']['full_cache_count'] = $primary_full_cache_count; 
	  			$return['primary']['partial_cache_count'] = $primary_partial_cache_count; 
	  			$return['primary']['no_cache_count'] = $primary_no_cache_count; 
	  			$return['secondary']['full_cache_count'] = $secondary_full_cache_count; 
	  			$return['secondary']['partial_cache_count'] = $secondary_partial_cache_count; 
	  			$return['secondary']['no_cache_count'] = $secondary_no_cache_count; 	 	  
	  
				if ( $primary_full_cache_count === $posts_count ) {
					$return['primary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $primary_full_cache_count + $primary_partial_cache_count ) === $posts_count ) {
					$return['primary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );  	
				} else {
					$return['primary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}
	  
				if ( $secondary_full_cache_count === $posts_count ) {
					$return['secondary']['cache_status'] = __( 'Completed', self::DOMAIN );
				} elseif ( ( $secondary_full_cache_count + $secondary_partial_cache_count ) === $posts_count ) {
					$return['secondary']['cache_status'] = __( 'Partially Completed', self::DOMAIN );  	
				} else {
					$return['secondary']['cache_status'] = __( 'Ongoing', self::DOMAIN );
				}
			  
	  			$callback = $_REQUEST["callback"];
	  
	  			header( 'Content-type: application/javascript; charset=utf-8' );
	  	
	  			echo $callback . '(' . json_encode( $return ) . ')';
			} else {
        		status_header( '403' );
        		echo 'Forbidden';			  	
			}
		} else {
        	status_header( '403' );
        	echo 'Forbidden';
    	}
	  
    	die();
  	}

  	/**
	 * Return share count
	 *
	 * @since 0.5.1
	 */   
  	public function get_share_counts( $post_ID = '', $sns_key = '' ) {
	  	  
	  	$sns_counts = array();
	  
	  	$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( $post_ID );
	  
  		if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {
	  		if ( $sns_key ) {
				if ( ! isset( $sns_counts[$sns_key] ) || $sns_counts[$sns_key] < 0 ) { 				
					$sns_counts[$sns_key] = 0;
				}
			  
		  		return $sns_counts[$sns_key];
			} else {		  	
		  		foreach ( $this->share_base_cache_target as $sns => $active ) {
				  	if ( $active ) {
			  			if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
				  			$sns_counts[$sns] = 0;
						}
					}
		  		}
			  
		  		return $sns_counts;
			}	  	
		} else {	  	
	  		switch ( $this->dynamic_cache_mode ) {
		  		case self::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
			  		$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
			  
		  			if ( $sns_key ) {
					   					  	
					  	if ( $post_ID !== 'home' ) {
						  	
						  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns_key );
						  
					  		$sns_count = get_post_meta( $post_ID, $meta_key, true );
						
							if ( isset( $sns_count ) && $sns_count !== '' && $sns_count >= 0) { 
								$sns_counts[$sns_key] = $sns_count;
							} else {
								$sns_counts[$sns_key] = 0;
							} 
						} else {
						  	$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );
						  	
							if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {						
								if ( ! isset( $sns_counts[$sns_key] ) || $sns_counts[$sns_key] < 0 ) { 				
									$sns_counts[$sns_key] = 0;
								}
							}else {
								$sns_counts[$sns_key] = 0;
							}
						}
					  
		  				return $sns_counts[$sns_key];
					} else {
					  	if ( $post_ID !== 'home' ) {
					  
					  		foreach ( $this->share_base_cache_target as $sns => $active ) {
					  			if ( $active ) {
								  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
								  
							  		$sns_count = get_post_meta( $post_ID, $meta_key, true );
							  	
							  		if ( isset( $sns_count ) && $sns_count !== '' && $sns_count >= 0 ) { 
							  			$sns_counts[$sns] = $sns_count;
									} else {
								  		$sns_counts[$sns] = 0;
									}
								}
							}
						} else {
						  
							$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );
						  
							if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {						
						  
								foreach ( $this->share_base_cache_target as $sns => $active ) {
					  				if ( $active ) {
										if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) { 				
											$sns_counts[$sns] = 0;
										}
									}							  	
								}
							} else {
								foreach ( $this->share_base_cache_target as $sns => $active ) {
						  			if ( $active ) {
										$sns_counts[$sns] = 0;
									}
								}
							}
						}
					  
					  	return $sns_counts;
					}
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
			  		$this->cache_engines[self::REF_SHARE_LAZY]->prime_cache( $post_ID );
			  
					if ( $sns_key ) {
					  	$sns_counts[$sns_key] = 0;
					  
		  				return $sns_counts[$sns_key];
					} else {					  					  
					  	foreach ( $this->share_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
								$sns_counts[$sns] = 0;
							}
						}
					  
					  	return $sns_counts;					  
					}  
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
			  		$sns_counts = $this->cache_engines[self::REF_SHARE_BASE]->direct_cache( $post_ID, true );
			  
					if ( $sns_key ) {
					  	if ( ! isset( $sns_counts[$sns_key] ) || $sns_counts[$sns_key] < 0 ) { 				
							$sns_counts[$sns_key] = 0;
						}
					  
		  				return $sns_counts[$sns_key];
					} else {
		  				foreach ( $this->share_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
			  					if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
				  					$sns_counts[$sns] = 0;
								}
							}
		  				}
					  
		  				return $sns_counts;
					}	  
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
					if ( $sns_key ) {
					  	$sns_counts[$sns_key] = 0;
					  
		  				return $sns_counts[$sns_key];
					} else {	
					  	foreach ( $this->share_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
								$sns_counts[$sns] = 0;
							}
						}
					  
					  	return $sns_counts;					  
					}	  
		  			break;
			} 
		}
	}

  	/**
	 * Return follow count
	 *
	 * @since 0.5.1
	 */     
  	public function get_follow_counts( $sns_key = '' ) {
	  
	  	$sns_followers = array();
	  
	  	$transient_id = $this->cache_engines[self::REF_FOLLOW_BASE]->get_cache_key( 'follow' );
	  
  		if ( false !== ( $sns_followers = get_transient( $transient_id ) ) ) {
	  		if ( $sns_key ) {
				if ( ! isset( $sns_followers[$sns_key] ) || $sns_followers[$sns_key] < 0 ) { 				
					$sns_followers[$sns_key] = 0;
				}
			  
		  		return $sns_followers[$sns_key];
			} else {		  	
		  		foreach ( $this->follow_base_cache_target as $sns => $active ) {
				  	if ( $active ) {
			  			if ( ! isset( $sns_followers[$sns] ) || $sns_followers[$sns] < 0 ) {
				  			$sns_followers[$sns] = 0;
						}
					}
		  		}
			  
		  		return $sns_followers;
			}	  	
		} else {	  	
	  		switch ( $this->dynamic_cache_mode ) {
		  		case self::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
					$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
			  
					$option_key = $this->cache_engines[self::REF_FOLLOW_2ND]->get_cache_key( 'follow' );
			  
			  		if ( $sns_key ) {
					   					  		  	
						if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {						
							if ( ! isset( $sns_followers[$sns_key] ) || $sns_followers[$sns_key] < 0 ) { 				
								$sns_followers[$sns_key] = 0;
							}
						} else {
							$sns_followers[$sns_key] = 0;
						}
						
		  				return $sns_followers[$sns_key];
					} else {
						  
						if ( false !== ( $sns_followers = get_option( $option_key ) ) ) {						
						  
							foreach ( $this->follow_base_cache_target as $sns => $active ) {
					  			if ( $active ) {
									if ( ! isset( $sns_followers[$sns] ) || $sns_followers[$sns] < 0 ) { 				
										$sns_followers[$sns] = 0;
									}
								}							  	
							}
						} else {
							foreach ( $this->follow_base_cache_target as $sns => $active ) {
						  		if ( $active ) {
									$sns_followers[$sns] = 0;
								}
							}
						}
						
					  	return $sns_followers;
					}
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
			  		$this->cache_engines[self::REF_FOLLOW_LAZY]->prime_cache();
			  
					if ( $sns_key ) {
					  	$sns_followers[$sns_key] = 0;
					  
		  				return $sns_followers[$sns_key];
					} else {					  					  
					  	foreach ( $this->follow_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
								$sns_followers[$sns] = 0;
							}
						}
					  
					  	return $sns_followers;					  
					}  
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
			  		$sns_followers = $this->cache_engines[self::REF_FOLLOW_BASE]->direct_cache();
			  		
					if ( $sns_key ) {
					  	if ( ! isset( $sns_followers[$sns_key] ) || $sns_followers[$sns_key] < 0 ) { 				
							$sns_followers[$sns_key] = 0;
						}
					  
		  				return $sns_followers[$sns_key];
					} else {
		  				foreach ( $this->follow_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
			  					if ( ! isset( $sns_followers[$sns] ) || $sns_followers[$sns] < 0 ) {
				  					$sns_followers[$sns] = 0;
								}
							}
		  				}
					  
		  				return $sns_followers;
					}	  
		  			break;
		  	case self::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
					if ( $sns_key ) {
					  	$sns_followers[$sns_key] = 0;
					  
		  				return $sns_followers[$sns_key];
					} else {	
					  	foreach ( $this->follow_base_cache_target as $sns => $active ) {
						  	if ( $active ) {
								$sns_followers[$sns] = 0;
							}
						}
					  
					  	return $sns_followers;					  
					}	  
		  			break;
			} 
		}
	}

	/**
 	 * Return if variation alaysis is enabled or not.
 	 *
 	 * @since 0.7.0
 	 */      
  	public function is_variation_analysis_enabled() {
	  
	  	if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) {
		  	return true;
		} else {
		  	return false;
		}
  	}
  
	  
}

SNS_Count_Cache::get_instance();

/**
 * Get share count from cache
 *
 * @since 0.4.0
 */  
function scc_get_share( $options = array( 'post_id' => '', 'url' => '', 'sns' => '' ) ) {

  	$post_ID = '';
  	$sns_key = '';

  	if ( isset( $options['url'] ) && $options['url'] ) {
	  	$post_ID = url_to_postid( $options['url'] );	  	
	} elseif ( isset( $options['post_id'] ) && $options['post_id'] ) {
	  	$post_ID = $options['post_id'];
	} else {
	  	$post_ID = get_the_ID();
	}
  
  	if ( isset( $options['sns'] ) && $options['sns'] ) {
	  	$sns_key = $options['sns'];
  	}
    
  	$sns_count_cache = SNS_Count_Cache::get_instance();
    
  	return $sns_count_cache->get_share_counts( $post_ID, $sns_key );
}    

/**
 * Get follow count from cache
 *
 * @since 0.4.0
 */
function scc_get_follow( $options = array( 'sns' => '' ) ) {
  
  	$sns_key = '';

  	if ( isset( $options['sns'] ) && $options['sns'] ) {
	  	$sns_key = $options['sns'];
  	}

  	$sns_count_cache = SNS_Count_Cache::get_instance();
    
  	return $sns_count_cache->get_follow_counts( $sns_key );
  
}  

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.4.0
 */       
function scc_get_share_hatebu( $options = array( 'post_id' => '', 'url' => '' ) ) {
   
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_HATEBU;
  	return scc_get_share( $options );
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.4.0
 */     
function scc_get_share_twitter( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_TWITTER;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.4.0
 */     
function scc_get_share_facebook( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_FACEBOOK;
  	return scc_get_share( $options );
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.4.0
 */     
function scc_get_share_gplus( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_GPLUS;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */     
function scc_get_share_pocket( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_POCKET;
  	return scc_get_share( $options );
    
}
  
/**
 * Get share count from cache (Pocket)
 *
 * @since 0.4.0
 */     
function scc_get_share_total( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_SHARE_TOTAL;
  	return scc_get_share( $options );
}

/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */       
function get_scc_hatebu( $options = array( 'post_id' => '', 'url' => '' ) ) {
   
  	return scc_get_share_hatebu( $options );
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_twitter( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_twitter( $options );
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_facebook( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_facebook( $options );
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.1.0
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_gplus( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
  	return scc_get_share_gplus( $options );
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.2.1
 * @deprecated Function deprecated in Release 0.4.0
 */     
function get_scc_pocket( $options = array( 'post_id' => '', 'url' => '' ) ) {
  
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

/**
 * Return if variation alaysis is enabled or not.
 *
 * @since 0.7.0
 */     
function scc_is_variation_analysis_enabled() {
	return SNS_Count_Cache::get_instance()->is_variation_analysis_enabled();
}
  
  
  
}

?>
