<?php
/*
class-share-cache-engine.php

Description: This class is a data cache engine whitch get and cache data using wp-cron at regular intervals  
Version: 0.4.0
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

abstract class Share_Cache_Engine extends Cache_Engine {
  
  	/**
	 * Crawler instance
	 */	            
	protected $crawler = NULL;

  	/**
	 * Cache target
	 */	            
  	protected $target_sns = array();  

  	/**
	 * Cache post types
	 */	   
	protected $post_types = array( 'post', 'page' );
  
  	/**
	 * migration between http and https
	 */	     
  	protected $scheme_migration_mode = false;

  	/**
	 * excluded keys in scheme migration
	 */	     
  	protected $scheme_migration_exclude_keys = array();  
       
   	/**
	 * Get and cache data for a given post
	 *
	 * @since 0.1.1
	 */  	
  	public function cache( $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$transient_id = $options['transient_id'];
		$target_url = $options['target_url'];
		$target_sns = $options['target_sns'];
		$cache_expiration = $options['cache_expiration'];

		Common_Util::log( '[' . __METHOD__ . '] current memory usage: ' . round( memory_get_usage( true )/1024/1024, 2 ) . ' MB' );	  
	  
	  	Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_url );	  
	  
	  	$data = $this->crawler->get_data( $target_sns, $target_url );
  
		Common_Util::log( $data );

	  	if ( $this->scheme_migration_mode && Common_Util::is_secure_url( $target_url ) ) {
		  	$target_url = Common_Util::get_normal_url( $target_url );

		  	$target_sns_migrated = $target_sns;
		  	
		  	foreach ( $this->scheme_migration_exclude_keys as $sns_key ) {
			  	unset( $target_sns_migrated[$sns_key] );
		  	}
		  
	  		Common_Util::log( '[' . __METHOD__ . '] target url: ' . $target_url );
		  
		  	$migrated_data = $this->crawler->get_data( $target_sns_migrated, $target_url );

			Common_Util::log( $migrated_data );

		  	foreach ( $target_sns_migrated as $key => $value ) {
				if ( $value && isset( $migrated_data[$key] ) && is_numeric( $migrated_data[$key] ) && $migrated_data[$key] > 0 ){
				  	$data[$key] = $data[$key] + $migrated_data[$key];
				}
			}
		  		  
		}	  
	  
	  	if ( $data ) {	  
			$result = set_transient( $transient_id, $data, $cache_expiration ); 
			  
			Common_Util::log( '[' . __METHOD__ . '] set_transient result: ' . $result );
	  	}
	  
	  	Common_Util::log( '[' . __METHOD__ . '] current memory usage: ' . round( memory_get_usage( true )/1024/1024, 2 ) . ' MB' );
		Common_Util::log( '[' . __METHOD__ . '] max memory usage: ' . round( memory_get_peak_usage( true )/1024/1024, 2 ) . ' MB' );
	  
	  	return $data;
  	} 
  
}



?>