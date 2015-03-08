<?php
/*
class-cache-engine.php

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


abstract class Cache_Engine extends Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	protected $transient_prefix = NULL;
  
  	/**
	 * instance for delegation
	 */	   
  	protected $delegate = NULL;  
    
	/**
	 * Class constarctor
	 * Hook onto all of the actions and filters needed by the plugin.
	 *
	 */
	protected function __construct() {
	  	Common_Util::log('[' . __METHOD__ . '] (line='. __LINE__ . ')');
	}
  	
  	/**
	 * Get cache expiration based on current number of total post and page
	 *
	 * @since 0.1.1
	 */	      
  	abstract protected function get_cache_expiration();
   
   	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	abstract public function cache( $post_ID, $target_sns, $cache_expiration );

    /**
	 * Initialize cache 
	 *
	 * @since 0.3.0
	 */	     
  	abstract public function initialize_cache();

    /**
	 * Clear cache 
	 *
	 * @since 0.3.0
	 */	     
  	abstract public function clear_cache();
  
  	/**
	 * Get share transient ID
	 *
	 * @since 0.1.1
	 */  	  
  	protected function get_transient_ID( $suffix ) {
	  	return $this->transient_prefix . $suffix;
  	}  
   
}

?>