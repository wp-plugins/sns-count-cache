<?php
/*
Plugin Name: SNS Count Cache
Description: SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.
Version: 0.1.0
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

require_once (dirname(__FILE__) . '/module/data-cache-engine.php');
require_once (dirname(__FILE__) . '/module/data-crawler.php');
require_once (dirname(__FILE__) . '/module/sns-count-crawler.php');

if (!class_exists('SNSCountCache')){

class SNSCountCache {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 */	
	private $version = '0.2.0';

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
	 * Prefix of cache ID
	 */
	const OPT_TRANSIENT_PREFIX = 'sns_count_cache_';

	/**
	 * Cron name to schedule cache processing
	 */	       
  	const OPT_CRON_CACHE_PRIME = 'scc_cntcache_prime';
  
	/**
	 * Cron name to execute cache processing
	 */	 
  	const OPT_CRON_CACHE_EXECUTE = 'scc_cntcache_exec';
  
	/**
	 * Schedule name for cache processing
	 */	      
  	const OPT_EVENT_SCHEDULE = 'cache_event';
  
  	/**
	 * Schedule description for cache processing
	 */	    
  	const OPT_EVENT_DESCRIPTION = '[SCC] Share Count Cache Interval';

	/**
	 * Interval cheking and caching target data
	 */	  
	const OPT_CHECK_INTERVAL = 600;
  
	/**
	 * Number of posts to check at a time
	 */	    	
  	const OPT_POSTS_PER_CHECK = 20;

	/**
	 * Option key for interval cheking and caching target data
	 */  
	const DB_CHECK_INTERVAL = 'scc_check_interval';
  
	/**
	 * Option key for Number of posts to check at a time
	 */	    
  	const DB_POSTS_PER_CHECK = 'scc_posts_per_check';
  
	/**
	 * Slug of the plugin
	 */		
	const DOMAIN = 'sns-count-cache';

  	/**
	 * ID of share count (Twitter)
	 */	
  	const REF_TWITTER = 'twitter';

  	/**
	 * ID of share count (Facebook)
	 */	  
  	const REF_FACEBOOK = 'facebook';
  
  	/**
	 * ID of share count (Google Plus)
	 */	  
  	const REF_GPLUS = 'gplus';

  	/**
	 * ID of share count (Hatena Bookmark)
	 */	    
  	const REF_HATEBU = 'hatebu';	
  
  	/**
	 * Instance
	 */
  	private static $instance = NULL;
  
	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 */
	private function __construct() {
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
	  	//load_plugin_textdomain(self::DOMAIN, false, basename(dirname( __FILE__ )) . '/languages');

		register_activation_hook( __FILE__, array($this, 'activate_plugin'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));	  	  
	  
	  	add_action('admin_menu', array($this, 'action_admin_menu'));
	  
		add_action('admin_print_styles', array($this, 'register_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));

		add_action('plugins_loaded', array($this,'initialize'));  
	}

    /**
     * Get instance
     *
	 * @since 0.1.1
	 */		
	public static function get_instance() {

		$class_name = get_called_class();
		if(!self::$instance) {
			self::$instance = new $class_name();
		}
		
		return self::$instance;
	}
  
    /**
     * Initialization 
     *
	 * @since 0.1.1
	 */		  
  	public function initialize(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');

	  	$check_interval = get_option(self::DB_CHECK_INTERVAL);
		$posts_per_check = get_option(self::DB_POSTS_PER_CHECK);
	  
	  	$check_interval = !empty($check_interval) ? intval($check_interval) : self::OPT_CHECK_INTERVAL;
	  	$posts_per_check = !empty($posts_per_check) ? intval($posts_per_check) : self::OPT_POSTS_PER_CHECK; 

	  	$this->log('[' . __METHOD__ . '] check_interval: ' . $check_interval);
	  	$this->log('[' . __METHOD__ . '] posts_per_check: ' . $posts_per_check);
	  
	  	$this->crawler = SNSCountCrawler::get_instance();
	  
	  	$options = array(
			'check_interval' => $check_interval,
			'posts_per_check' => $posts_per_check,
		  	'transient_prefix' => self::OPT_TRANSIENT_PREFIX,
		  	'cron_cache_prime' => self::OPT_CRON_CACHE_PRIME,
		  	'cron_cache_execute' => self::OPT_CRON_CACHE_EXECUTE,
		  	'event_schedule' => self::OPT_EVENT_SCHEDULE,
		  	'event_description' => self::OPT_EVENT_DESCRIPTION
			);
	  
	  	$this->cache_engine = DataCacheEngine::get_instance();
		$this->cache_engine->initialize($this->crawler, $options); 	  
	  	
  	}
  
	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_styles() {
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
		if (!isset($this->plugin_screen_hook_suffix)) {
		  	return;
		}

		$screen = get_current_screen();
	  
		if ($screen->id == $this->plugin_screen_hook_suffix) {
		  	wp_enqueue_style(self::DOMAIN .'-admin-style-1' , plugins_url(ltrim('/css/sns-count-cache.css', '/'), __FILE__));
		  	wp_enqueue_style(self::DOMAIN .'-admin-style-2' , plugins_url(ltrim('/css/prettify.css', '/'), __FILE__));
		}
	  
	} 

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since 0.1.0
	 */
	public function register_admin_scripts() {
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
		if (!isset( $this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
	  
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			wp_enqueue_script(self::DOMAIN . '-admin-script-1' , plugins_url(ltrim('/js/jquery.sns-count-cache.js', '/') , __FILE__ ), array( 'jquery' ));
			wp_enqueue_script(self::DOMAIN . '-admin-script-2' , plugins_url(ltrim('/js/prettify.js', '/') , __FILE__ ), array( 'jquery' ));
		}
	  
	} 

	/**
	 * Activate cache engine (base schedule cron)
	 *
	 * @since 0.1.1
	 */
	function activate_plugin(){	  
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
	  	$this->initialize();
	  	$this->cache_engine->register_base_schedule();
	}
	
	/**
	 * Deactivate cache engine (base schedule cron)
	 *
	 * @since 0.1.1
	 */
	function deactivate_plugin(){
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
	  	$this->cache_engine->unregister_base_schedule();
	}

	/**
	 * Reactivate cache engine
	 *
	 * @since 0.1.1
	 */  
  	function reactivate_plugin() {
	  	$this->log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	  
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
	    $this->plugin_screen_hook_suffix = add_options_page('SNS Count Cache', 'SNS Count Cache', 8, 'sns_count_cache_options_page',array($this, 'option_page'));
    }
		
    /**
     * Option page implementation
     *
	 * @since 0.1.0
	 */	
	public function option_page(){
	  	include_once(dirname(__FILE__) . '/admin.php');
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
  
}

SNSCountCache::get_instance();
  
/**
 * Get share count from cache (Hatena Bookmark).
 *
 * @since 0.1.0
 */       
function get_scc_hatebu($post_ID='') {
	$transient_id ='';
  
	if(!empty($post_ID)){
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . $post_ID;
    }else{
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . get_the_ID();
    }

  	$sns_counts = get_transient($transient_id);
     
	return $sns_counts[SNSCountCache::REF_HATEBU]; 
}
  
/**
 * Get share count from cache (Twitter)
 *
 * @since 0.1.0
 */     
function get_scc_twitter($post_ID='') {
	$transient_id ='';
  
	if(!empty($post_ID)){
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . $post_ID;
    }else{
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . get_the_ID();
    }

  	$sns_counts = get_transient($transient_id);
       
	return $sns_counts[SNSCountCache::REF_TWITTER]; 
}

/**
 * Get share count from cache (Facebook)
 *
 * @since 0.1.0
 */     
function get_scc_facebook($post_ID='') {
	$transient_id ='';
  
	if(!empty($post_ID)){
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . $post_ID;
    }else{
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . get_the_ID();
    }

  	$sns_counts = get_transient($transient_id);

  	return $sns_counts[SNSCountCache::REF_FACEBOOK]; 
}
  
/**
 * Get share count from cache (Google Plus)
 *
 * @since 0.1.0
 */     
function get_scc_gplus($post_ID='') {
	$transient_id ='';
  
	if(!empty($post_ID)){
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . $post_ID;
    }else{
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . get_the_ID();
    }

  	$sns_counts = get_transient($transient_id);

  	return $sns_counts[SNSCountCache::REF_GPLUS]; 
}

/**
 * Get share count from cache
 *
 * @since 0.1.0
 */     
function get_scc($post_ID='') {
	$transient_id ='';
  
	if(!empty($post_ID)){
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . $post_ID;
    }else{
	  	$transient_id = SNSCountCache::OPT_TRANSIENT_PREFIX . get_the_ID();
    }

  	$sns_counts = get_transient($transient_id);
  
	return $sns_counts;
}
  
}

?>
