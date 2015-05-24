<?php
/*
class-cache-engine.php

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


abstract class Cache_Engine extends Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	protected $cache_prefix = NULL;
  
  	/**
	 * instance for delegation
	 */	   
  	protected $delegate = NULL;  
      	
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
  	abstract public function cache( $options = array() );

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
	 * Get cache key
	 *
	 * @since 0.6.0
	 */  	  
  	public function get_cache_key( $suffix ) {
	  	return $this->cache_prefix . strtolower( $suffix );
  	}    
  
  	/**
	 * Order cache
	 *
	 * @since 0.5.1
	 */  	    
  	protected function delegate_cache( $options = array() ) {
  		if ( ! is_null( $this->delegate ) && method_exists( $this->delegate, 'order_cache' ) ) {
			$this->delegate->order_cache( $this, $options );
	  	}
	}
  
}

?>
