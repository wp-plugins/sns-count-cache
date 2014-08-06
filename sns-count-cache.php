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

if (!class_exists('SNSCountCache')){

class SNSCountCache {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 */	
	private $version = '0.1.0';
	
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
	const TRANSIENT_PREFIX = 'sns_count_cache_';

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
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 */
	function __construct() {
				
	  	//load_plugin_textdomain(self::DOMAIN, false, basename(dirname( __FILE__ )) . '/languages');
	  
	  	require_once (dirname(__FILE__) . '/module/data-cache-engine.php');
	  	require_once (dirname(__FILE__) . '/module/data-crawler.php');
		require_once (dirname(__FILE__) . '/module/sns-count-crawler.php');
		
		$crawler = new SNSCountCrawler();
	  
	  	$options = array(
			'check_interval' => 600,
			'posts_per_check' => 20,
		  	'transient_prefix' => self::TRANSIENT_PREFIX,
  			'cron_cache_prime' => 'scc_cntcache_prime',
  			'cron_cache_execute' => 'scc_cntcache_exec',
		  	'event_schedule' => 'cache_event',
			'event_description' => '[SCC] Share Count Cache Interval'
			);
	  
	  	$this->cache_engine = new DataCacheEngine($crawler, $options);
	  
	  	add_action('admin_menu', array($this, 'action_admin_menu'));

		add_action('admin_print_styles', array($this, 'register_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
			
		register_activation_hook( __FILE__, array($this, 'activate_plugin'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
	  
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 */
	public function register_admin_styles() {
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
	 */
	public function register_admin_scripts() {

		if (!isset( $this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			  wp_enqueue_script(self::DOMAIN . '-admin-script-1' ,plugins_url(ltrim('/js/jquery.sns-count-cache.js', '/') , __FILE__ ),array( 'jquery' ));
		  	  wp_enqueue_script(self::DOMAIN . '-admin-script-2' ,plugins_url(ltrim('/js/prettify.js', '/') , __FILE__ ),array( 'jquery' ));
		}

	} 

	/**
	 * Activate base schedule cron
	 *
	 */
	function activate_plugin(){
		$this->cache_engine->register_base_schedule();
	}
	
	/**
	 * Deactivate base schedule cron
	 *
	 */
	function deactivate_plugin(){
		$this->cache_engine->unregister_base_schedule();
	}
	
    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
     */
    public function action_admin_menu() {
	    $this->plugin_screen_hook_suffix = add_options_page('SNS Count Cache', 'SNS Count Cache', 8, 'sns_count_cache_options_page',array($this, 'option_page'));
    }
		
    /**
     * Option page implementation
     *
     */	
	public function option_page(){
	  	include_once(dirname(__FILE__) . '/admin.php');
	}
	
	public static function init() {

		static $instance = null;

		if ( !$instance )
			$instance = new SNSCountCache;

		return $instance;

	}
}

SNSCountCache::init();
  
/**
 * Get share count from cache (Hatena Bookmark).
 *
 */	  
function get_scc_hatebu($post_ID='') {
  	$transient_id ='';
  
  	if(!empty($post_ID)){
  		$transient_id = SNSCountCache::TRANSIENT_PREFIX . $post_ID;
	}else{
	  	$transient_id = SNSCountCache::TRANSIENT_PREFIX . get_the_ID();
	}
  
	$sns_counts = get_transient($transient_id);
	
  	return $sns_counts[SNSCountCache::REF_HATEBU]; 
}
  
/**
 * Get share count from cache (Twitter)
 *
 */	
function get_scc_twitter($post_ID='') {
  	$transient_id ='';
  
  	if(!empty($post_ID)){
  		$transient_id = SNSCountCache::TRANSIENT_PREFIX . $post_ID;
	}else{
	  	$transient_id = SNSCountCache::TRANSIENT_PREFIX . get_the_ID();
	}
  
	$sns_counts = get_transient($transient_id);
	  
  	return $sns_counts[SNSCountCache::REF_TWITTER]; 
}

/**
 * Get share count from cache (Facebook)
 *
 */	
function get_scc_facebook($post_ID='') {
  	$transient_id ='';
  
  	if(!empty($post_ID)){
  		$transient_id = SNSCountCache::TRANSIENT_PREFIX . $post_ID;
	}else{
	  	$transient_id = SNSCountCache::TRANSIENT_PREFIX . get_the_ID();
	}
  
	$sns_counts = get_transient($transient_id);

  	return $sns_counts[SNSCountCache::REF_FACEBOOK]; 
}
  
/**
 * Get share count from cache (Google Plus)
 *
 */	
function get_scc_gplus($post_ID='') {
  	$transient_id ='';
  
	if(!empty($post_ID)){
  		$transient_id = SNSCountCache::TRANSIENT_PREFIX . $post_ID;
	}else{
	  	$transient_id = SNSCountCache::TRANSIENT_PREFIX . get_the_ID();
	}
  
	$sns_counts = get_transient($transient_id);

  	return $sns_counts[SNSCountCache::REF_GPLUS]; 
}

/**
 * Get share count from cache
 *
 */	
function get_scc($post_ID='') {
  	$transient_id ='';
  
	if(!empty($post_ID)){
  		$transient_id = SNSCountCache::TRANSIENT_PREFIX . $post_ID;
	}else{
	  	$transient_id = SNSCountCache::TRANSIENT_PREFIX . get_the_ID();
	}
  
	$sns_counts = get_transient($transient_id);
	  
	return $sns_counts;
}  
  
}

?>
