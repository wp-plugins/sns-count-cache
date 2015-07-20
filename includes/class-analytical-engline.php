<?php
/*
class-analytical-engline.php

Description: This class is a data analytical engine.  
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


abstract class Analytical_Engine extends Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	protected $cache_prefix = NULL;

    /**
	 * Prefix of base ID
	 */	    
  	protected $base_prefix = NULL;

    /**
	 * Prefix of base ID
	 */	    
  	protected $delta_prefix = NULL;
  
  	/**
	 * instance for delegation
	 */	   
  	protected $delegate = NULL;  
      	   
   	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	abstract public function analyze( $options = array() );

    /**
	 * Initialize cache 
	 *
	 * @since 0.3.0
	 */	     
  	abstract public function initialize_base();

    /**
	 * Clear cache 
	 *
	 * @since 0.3.0
	 */	     
  	abstract public function clear_base();
  
  	/**
	 * Get cache key
	 *
	 * @since 0.6.0
	 */  	  
  	public function get_cache_key( $suffix ) {
	  	return $this->cache_prefix . strtolower( $suffix );
  	}    

  	/**
	 * Get base key
	 *
	 * @since 0.6.1
	 */  	  
  	public function get_base_key( $suffix ) {
	  	return $this->base_prefix . strtolower( $suffix );
  	}    

  	/**
	 * Get delta key
	 *
	 * @since 0.6.1
	 */  	  
  	public function get_delta_key( $suffix ) {
	  	return $this->delta_prefix . strtolower( $suffix );
  	}   
  
}

?>
