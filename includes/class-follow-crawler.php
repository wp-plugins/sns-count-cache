<?php
/*
class-sns-follow-crawler.php

Description: This class is a data crawler whitch get share count using given API and cURL
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

class Follow_Crawler extends Data_Crawler {
  
  	/**
	 * Timeout for cURL data retrieval
	 */	  
	private $timeout = 10;
    
	protected function __construct( $url='', $timeout=10 ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$this->url = rawurlencode( $url );
		$this->timeout = $timeout;
	}
  
	public function set_timeout( $timeout ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$this->timeout = $timeout;
	}
    
  	/**
	 * Implementation of abstract method. this method gets each share count
	 *
	 * @since 0.1.1
	 */	    
  	public function get_data( $cache_target, $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$url = rawurlencode( $url );
	  
		$sns_counts = array();
	  	
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_FOLLOW_FEEDLY] ) && $cache_target[SNS_Count_Cache::REF_FOLLOW_FEEDLY] ) {
	  		$sns_counts[SNS_Count_Cache::REF_FOLLOW_FEEDLY] = ( int )$this->get_feedly_follow( $url );
	  	}

		return $sns_counts;	
  	}
  
  	/**
	 * Get share count for Twitter
	 *
	 * @since 0.1.0
	 */	        
	public function get_feedly_follow( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	$query = 'http://cloud.feedly.com/v3/feeds/feed%2F' . $url;
	  
		$json = $this->remote_get( $query );
	 
		$feedly = json_decode( $json, true );
	  	
		return isset( $feedly['subscribers'] ) ? intval( $feedly['subscribers'] ) : null;
	}

  	/**
	 * Get content from given URL using cURL
	 *
	 * @since 0.1.0
	 */	          
	private function remote_get( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	global $wp_version;
	  	  
		$curl = curl_init();
	  
		curl_setopt( $curl, CURLOPT_URL, $url );
  		curl_setopt( $curl, CURLOPT_USERAGENT, 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );	  	
		//curl_setopt( $curl, CURLOPT_FAILONERROR, true );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->timeout );
	  	//curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
	  	//curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
	  	  
		$curl_results = curl_exec( $curl );
	  
		if ( curl_error( $curl ) ) {
		  	Common_Util::log( '[' . __METHOD__ . '] curl_error: ' + curl_error( $curl ) );
			die( curl_error( $curl ) );
		}
	  
	  	curl_close( $curl );
	  
		return $curl_results;
	}

}

?>
