<?php
/*
Plugin Name: SNS Count Cache
Description: SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.
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

require_once ( dirname( __FILE__ ) . '/includes/class-data-cache-engine.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-data-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-sns-count-crawler.php' );
require_once ( dirname( __FILE__ ) . '/includes/class-common-util.php' );

if ( ! class_exists( 'SNS_Count_Cache' ) ) {

class SNS_Count_Cache {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 */	
	private $version = '0.3.0';

	/**
	 * Instance of module class of crawler 
	 */	
	private $crawler = NULL;
  
	/**
	 * Instance of module class of schedule and cache processing 
	 */	
	private $cache_engine = NULL;
	
	/**
	 * Slug of the plugin screen
	 */		
	private $plugin_screen_hook_suffix = NULL;
  
	/**
	 * Option flag of dynamic cache processing
	 */		  
  	private $dynamic_cache = 0;
  	
	/**
	 * Option flag of dynamic cache processing
	 */		  
  	private $base_cache_target = array();  
  
	/**
	 * Prefix of cache ID
	 */
	const OPT_BASE_TRANSIENT_PREFIX = 'sns_count_cache_';

	/**
	 * Cron name to schedule cache processing
	 */	       
  	const OPT_BASE_CACHE_PRIME_CRON = 'scc_basecache_prime';
  
	/**
	 * Cron name to execute cache processing
	 */	 
  	const OPT_BASE_CACHE_EXECUTE_CRON = 'scc_basecache_exec';
  
	/**
	 * Schedule name for cache processing
	 */	      
  	const OPT_BASE_EVENT_SCHEDULE = 'base_cache_event';
  
  	/**
	 * Schedule description for cache processing
	 */	    
  	const OPT_BASE_EVENT_DESCRIPTION = '[SCC] Share Count Cache Basic Interval';

	/**
	 * Interval cheking and caching target data
	 */	  
	const OPT_BASE_CHECK_INTERVAL = 600;
  
	/**
	 * Number of posts to check at a time
	 */	    	
  	const OPT_BASE_POSTS_PER_CHECK = 20;

	/**
	 * Cron name to schedule cache processing
	 */	       
  	const OPT_RUSH_CACHE_PRIME_CRON = 'scc_rushcache_prime';
  
	/**
	 * Cron name to execute cache processing
	 */	 
  	const OPT_RUSH_CACHE_EXECUTE_CRON = 'scc_rushcache_exec';
  
	/**
	 * Schedule name for cache processing
	 */	      
  	const OPT_RUSH_EVENT_SCHEDULE = 'rush_cache_event';
  
  	/**
	 * Schedule description for cache processing
	 */	    
  	const OPT_RUSH_EVENT_DESCRIPTION = '[SCC] Share Count Cache Rush Interval';

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_RUSH_NEW_CONTENT_TERM = 3;  

	/**
	 * Interval ranking
	 */	  
	const OPT_2ND_CHECK_INTERVAL = 600;
  
	/**
	 * Cron name to schedule rank processing
	 */	       
  	const OPT_2ND_CACHE_PRIME_CRON = 'scc_2ndcache_prime';
  
	/**
	 * Cron name to execute rank processing
	 */	 
  	const OPT_2ND_CACHE_EXECUTE_CRON = 'scc_2ndcache_exec';
  
	/**
	 * Schedule name for rank processing
	 */	      
  	const OPT_2ND_EVENT_SCHEDULE = '2nd_cache_event';
  
  	/**
	 * Schedule description for rank processing
	 */	    
  	const OPT_2ND_EVENT_DESCRIPTION = '[SCC] Share Count Cache 2nd Cache Interval';

  	/**
	 * Meta key for rank processing
	 */	    
  	const OPT_2ND_META_KEY_PREFIX = 'scc_share_count_';
 
	/**
	 * Cron name to execute cache processing
	 */	 
  	const OPT_LAZY_CACHE_EXECUTE_CRON = 'scc_lazycache_exec';

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_ACCESS_BASED_CACHE_NONE = 0;  
  
	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_ACCESS_BASED_SYNC_CACHE = 1;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_ACCESS_BASED_ASYNC_CACHE = 2;

	/**
	 * Type of dynamic cache processing
	 */	 
  	const OPT_ACCESS_BASED_2ND_CACHE = 3;
    
	/**
	 * Option key for interval cheking and caching target data
	 */  
	const DB_CHECK_INTERVAL = 'scc_check_interval';
  
	/**
	 * Option key for Number of posts to check at a time
	 */	    
  	const DB_POSTS_PER_CHECK = 'scc_posts_per_check';

	/**
	 * Option key for dynamic cache processing
	 */	    
  	const DB_DYNAMIC_CACHE = 'scc_dynamic_cache';

	/**
	 * Option key for dynamic cache processing
	 */	    
  	const DB_NEW_CONTENT_TERM = 'scc_new_content_term';

	/**
	 * Option key of cache target
	 */	    
  	const DB_CACHE_TARGET = 'scc_cache_target';
  
	/**
	 * Slug of the plugin
	 */		
	const DOMAIN = 'sns-count-cache';

  	/**
	 * ID of share count (Twitter)
	 */	
  	const REF_TWITTER = 'Twitter';

  	/**
	 * ID of share count (Facebook)
	 */	  
  	const REF_FACEBOOK = 'Facebook';
  
  	/**
	 * ID of share count (Google Plus)
	 */	  
  	const REF_GPLUS = 'Google+';

  	/**
	 * ID of share count (Hatena Bookmark)
	 */	    
  	const REF_HATEBU = 'Hatebu';	

  	/**
	 * ID of share count (Pocket)
	 */	    
	const REF_POCKET = 'Pocket';
  
  	/**
	 * Instance
	 */
  	private static $instance = NULL;
  
	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 */
	private function __construct() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
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
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$check_interval = get_option( self::DB_CHECK_INTERVAL );
		$posts_per_check = get_option( self::DB_POSTS_PER_CHECK );
	  
	  	$check_interval = ! empty( $check_interval ) ? intval( $check_interval ) : self::OPT_BASE_CHECK_INTERVAL;
	  	$posts_per_check = ! empty( $posts_per_check ) ? intval( $posts_per_check ) : self::OPT_BASE_POSTS_PER_CHECK; 

	  	$dynamic_cache = get_option( self::DB_DYNAMIC_CACHE );
	  	$this->dynamic_cache = ! empty( $dynamic_cache ) ? $dynamic_cache : false;
	  
	  	$new_content_term = get_option( self::DB_NEW_CONTENT_TERM );
	  	$new_content_term = ! empty( $new_content_term ) ? intval( $new_content_term ) : self::OPT_RUSH_NEW_CONTENT_TERM;

		$this->base_cache_target = get_option( self::DB_CACHE_TARGET );
	  
		if ( empty( $this->base_cache_target ) ) {
			$this->base_cache_target[self::REF_TWITTER] = true;
			$this->base_cache_target[self::REF_FACEBOOK] = true;
			$this->base_cache_target[self::REF_GPLUS] = true;
		  	if ( extension_loaded( 'xml' ) ) {
				$this->base_cache_target[self::REF_POCKET] = true;
			}
			$this->base_cache_target[self::REF_HATEBU] = true;
		}	  	
	  
	  	$this->log( '[' . __METHOD__ . '] check_interval: ' . $check_interval );
	  	$this->log( '[' . __METHOD__ . '] posts_per_check: ' . $posts_per_check );
	  
	  	$this->crawler = SNS_Count_Crawler::get_instance();
	  
	  	$options = array(
		  	'base_cache_target' => $this->base_cache_target,
			'base_check_interval' => $check_interval,
			'base_posts_per_check' => $posts_per_check,
		  	'base_transient_prefix' => self::OPT_BASE_TRANSIENT_PREFIX,
		  	'base_cache_prime_cron' => self::OPT_BASE_CACHE_PRIME_CRON,
		  	'base_cache_execute_cron' => self::OPT_BASE_CACHE_EXECUTE_CRON,
		  	'base_event_schedule' => self::OPT_BASE_EVENT_SCHEDULE,
		  	'base_event_description' => self::OPT_BASE_EVENT_DESCRIPTION,
		  	'rush_cache_prime_cron' => self::OPT_RUSH_CACHE_PRIME_CRON,
		  	'rush_cache_execute_cron' => self::OPT_RUSH_CACHE_EXECUTE_CRON,
		  	'rush_event_schedule' => self::OPT_RUSH_EVENT_SCHEDULE,
		  	'rush_event_description' => self::OPT_RUSH_EVENT_DESCRIPTION,
		  	'rush_new_content_term' => $new_content_term,
		  	'lazy_cache_execute_cron' => self::OPT_LAZY_CACHE_EXECUTE_CRON,
		  	'second_check_interval' => self::OPT_2ND_CHECK_INTERVAL,
		  	'second_cache_prime_cron' => self::OPT_2ND_CACHE_PRIME_CRON,
		  	'second_cache_execute_cron' => self::OPT_2ND_CACHE_EXECUTE_CRON,
		  	'second_event_schedule' => self::OPT_2ND_EVENT_SCHEDULE,
		  	'second_event_description' => self::OPT_2ND_EVENT_DESCRIPTION,
		  	'second_meta_key_prefix' => self::OPT_2ND_META_KEY_PREFIX
			);
	  
	  	$this->cache_engine = Data_Cache_Engine::get_instance();
		$this->cache_engine->initialize( $this->crawler, $options ); 	  
	  	
  	}
  
	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_styles() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
		  	return;
		}

		$screen = get_current_screen();
	  
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
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
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
	  
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( self::DOMAIN . '-admin-script-1' , plugins_url( ltrim( '/js/jquery.sns-count-cache.js', '/' ) , __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( self::DOMAIN . '-admin-script-2' , plugins_url( ltrim( '/js/prettify.js', '/' ) , __FILE__ ), array( 'jquery' ) );
		}
	  
	} 

	/**
	 * Activate cache engine (base schedule cron)
	 *
	 * @since 0.1.1
	 */
	function activate_plugin() {	  
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->initialize();
	  	$this->cache_engine->register_base_schedule();
	}
	
	/**
	 * Deactivate cache engine (base schedule cron)
	 *
	 * @since 0.1.1
	 */
	function deactivate_plugin() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engine->unregister_base_schedule();
	}

	/**
	 * Reactivate cache engine
	 *
	 * @since 0.1.1
	 */  
  	function reactivate_plugin() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engine->unregister_base_schedule();
	  	$this->initialize();
	  	$this->cache_engine->register_base_schedule();
	}  
  
    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
	 *
	 * @since 0.1.0
	 */
    public function action_admin_menu() {
	    $this->plugin_screen_hook_suffix = add_options_page( 'SNS Count Cache', 'SNS Count Cache', 8, 'sns_count_cache_options_page', array( $this, 'option_page' ) );
    }
		
    /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	
	public function option_page() {
	  	include_once( dirname( __FILE__ ) . '/includes/admin.php' );
	}
  
  	/**
	 * Output log message according to WP_DEBUG setting
	 *
	 * @since 0.1.0
	 */	    
	private function log( $message ) {
    	if (WP_DEBUG === true) {
      		if ( is_array( $message ) || is_object( $message ) ) {
        		error_log( print_r( $message, true ) );
      		} else {
        		error_log( $message );
      		}
    	}
  	}
    
  	/**
	 * Return type of dynamic cache processing
	 *
	 * @since 0.2.0
	 */
  	public function get_dynamic_cache_type() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$this->log( '[' . __METHOD__ . '] dynamic cache: ' . $this->dynamic_cache );
	  	
	  	return $this->dynamic_cache;
  	}

  
  	/**
	 * Get and cache data for a given post ID
	 *
	 * @since 0.2.0
	 */  
  	public function retrieve_count_cache( $post_ID ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	return $this->cache_engine->execute_direct_data_cache( $post_ID );
  	}
  
  	/**
	 * Reserve cache processing for a given post ID
	 *
	 * @since 0.2.0
	 */    
  	public function reserve_count_cache( $post_ID ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_engine->prime_lazy_data_cache( $post_ID );
	}

  	/**
	 * Return base cache target
	 *
	 * @since 0.2.0
	 */
  	public function get_base_cache_target() {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
 	
	  	return $this->base_cache_target;
  	}

  
}

SNS_Count_Cache::get_instance();

/**
 * Get share count from cache
 *
 * @since 0.1.0
 */     
function get_scc( $options = array( 'id' => '', 'url' => '', 'sns' => '' ) ) {
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
  
  	$transient_ID = SNS_Count_Cache::OPT_BASE_TRANSIENT_PREFIX . $post_ID;
  
  	if ( false !== ( $sns_counts = get_transient( $transient_ID ) ) ) {
	  	if ( $sns_key ) {
		  	return $sns_counts[$sns_key];
		} else {
		  	return $sns_counts;
		}	  	
	} else {
	  	$sns_count_cache = SNS_Count_Cache::get_instance();
	  	
	  	switch ( $sns_count_cache->get_dynamic_cache_type() ) {
		  	case SNS_Count_Cache::OPT_ACCESS_BASED_CACHE_NONE:
					if ( $sns_key ) {
		  				return $sns_counts[$sns_key];
					} else {
		  				return $sns_counts;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_ACCESS_BASED_SYNC_CACHE:
		  			$sns_counts = $sns_count_cache->retrieve_count_cache( $post_ID );
					if ( $sns_key ) {
		  				return $sns_counts[$sns_key];
					} else {
		  				return $sns_counts;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_ACCESS_BASED_ASYNC_CACHE:
		  			$sns_count_cache->reserve_count_cache( $post_ID );
					if ( $sns_key ) {
		  				return $sns_counts[$sns_key];
					} else {
		  				return $sns_counts;
					}	  
		  			break;
		  	case SNS_Count_Cache::OPT_ACCESS_BASED_2ND_CACHE:
		  			$sns_count_cache->reserve_count_cache( $post_ID );
		  			if ( $sns_key ) {
					    $meta_key = SNS_Count_Cache::OPT_2ND_META_KEY_PREFIX . strtolower( $sns_key );
					  	$sns_counts[$sns_key] = get_post_meta( $post_ID, $meta_key, true );
		  				return $sns_counts[$sns_key];
					} else {
					  	$base_cache_target = $sns_count_cache->get_base_cache_target();
					  	
					  	foreach ( $base_cache_target as $key => $value ) {
					  		if ( $value ) {
							    $meta_key = SNS_Count_Cache::OPT_2ND_META_KEY_PREFIX . strtolower( $key );
							  
							  	$sns_count = get_post_meta( $post_ID, $meta_key, true );
							  	
							  	if ( isset( $sns_count ) && $sns_count >= 0 ) { 
							  		$sns_counts[$key] = get_post_meta( $post_ID, $meta_key, true );
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
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.1.0
 */       
function get_scc_hatebu( $options = array( 'id' => '', 'url' => '' ) ) {
   
  	$options['sns'] = SNS_Count_Cache::REF_HATEBU;
  	return get_scc( $options );
  
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.1.0
 */     
function get_scc_twitter( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_TWITTER;
  	return get_scc( $options );
  
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.1.0
 */     
function get_scc_facebook( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_FACEBOOK;
  	return get_scc( $options );
  
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.1.0
 */     
function get_scc_gplus( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_GPLUS;
  	return get_scc( $options );
    
}

/**
 * Get share count from cache (Pocket)
 *
 * @since 0.2.1
 */     
function get_scc_pocket( $options = array( 'id' => '', 'url' => '' ) ) {
  
  	$options['sns'] = SNS_Count_Cache::REF_POCKET;
  	return get_scc( $options );
    
}

}

?>
