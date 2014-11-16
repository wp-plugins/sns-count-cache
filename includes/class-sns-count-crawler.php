<?php
/*
class-sns-count-crawler.php

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

class SNS_Count_Crawler extends Data_Crawler {
  
  	/**
	 * Timeout for cURL data retrieval
	 */	  
	private $timeout = 10;
    
	protected function __construct( $url='', $timeout=10 ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$this->url = rawurlencode( $url );
		$this->timeout = $timeout;
	}
  
	public function set_timeout( $timeout ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$this->timeout = $timeout;
	}
    
  	/**
	 * Implementation of abstract method. this method gets each share count
	 *
	 * @since 0.1.1
	 */	    
  	public function get_data( $cache_target, $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$url = rawurlencode( $url );
	  
		$sns_counts = array();
	  	
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_HATEBU] ) && $cache_target[SNS_Count_Cache::REF_HATEBU] ) {
	  		$sns_counts[SNS_Count_Cache::REF_HATEBU] = $this->get_hatebu_count( $url );
	  	}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_TWITTER] ) && $cache_target[SNS_Count_Cache::REF_TWITTER] ) {
	  		$sns_counts[SNS_Count_Cache::REF_TWITTER] = $this->get_twitter_count( $url );
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_FACEBOOK] ) && $cache_target[SNS_Count_Cache::REF_FACEBOOK] ) {
		  	$sns_counts[SNS_Count_Cache::REF_FACEBOOK] = $this->get_facebook_count( $url );
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_GPLUS] ) && $cache_target[SNS_Count_Cache::REF_GPLUS] ) {
	  		$sns_counts[SNS_Count_Cache::REF_GPLUS] = $this->get_gplus_count( $url );		  
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_POCKET] ) && $cache_target[SNS_Count_Cache::REF_POCKET] ) {
		  	$sns_counts[SNS_Count_Cache::REF_POCKET] = $this->get_pocket_count( $url );
		}
	  
		return $sns_counts;	
  	}
  
  	/**
	 * Get share count for Hatena Bookmark
	 *
	 * @since 0.1.0
	 */	      
	public function get_hatebu_count( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	$query = 'http://api.b.st-hatena.com/entry.count?url=' . $url;
	  
		$hatebu = $this->remote_get( $query );
	  
		return isset( $hatebu ) ? intval($hatebu) : 0;
	}

  	/**
	 * Get share count for Twitter
	 *
	 * @since 0.1.0
	 */	        
	public function get_twitter_count( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	$query = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url;
	  
		$json = $this->remote_get( $query );
	  
		$twitter = json_decode( $json, true );
	  
		return isset( $twitter['count'] ) ? intval( $twitter['count'] ) : 0;
	}

  	/**
	 * Get share count for Facebook
	 *
	 * @since 0.1.0
	 */	        
	public function get_facebook_count( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$query = 'http://graph.facebook.com/' . $url;
	  
		$json = $this->remote_get( $query );
	  
	  	$facebook = json_decode( $json, true );
	  
	  	return isset( $facebook['shares'] ) ? intval( $facebook['shares'] ) : 0;
	}

  	/**
	 * Get share count for Google Plus
	 *
	 * @since 0.1.0
	 */	          
	public function get_gplus_count( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$curl = curl_init();
	  
		curl_setopt( $curl, CURLOPT_URL, "https://clients6.google.com/rpc" );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode( $url ) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
	  	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) );
	  
		$curl_results = curl_exec ( $curl );
	  
		if ( curl_error( $curl ) ) {
		  	$this->log( '[' . __METHOD__ . '] curl_error: ' + curl_error( $curl ) );
			die( curl_error( $curl ) ) ;
		}	  
	  
		curl_close( $curl );
	  
		$json = json_decode( $curl_results, true );
	  
		return isset( $json[0]['result']['metadata']['globalCounts']['count'] ) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : 0;
	}
  
  	/**
	 * Get share count for Pocket
	 *
	 * @since 0.1.0
	 */	            
  	public function get_pocket_count( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$query = 'http://widgets.getpocket.com/v1/button?v=1&count=horizontal&url=' . $url;	  
		$html = $this->remote_get( $query );
	  	
	  	$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXml( $html );
 
		$xpath = new DOMXPath( $dom );
	  
	  	$result = $xpath->query( '//em[@id = "cnt"]' )->item(0);
	  
		$this->log( '[' . __METHOD__ . '] count: ' . $result->nodeValue );
	  
		return isset( $result->nodeValue ) ? intval( $result->nodeValue ) : 0;
  	}
  
  	/**
	 * Get content from given URL using cURL
	 *
	 * @since 0.1.0
	 */	          
	private function remote_get( $url ) {
	  	$this->log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
		$curl = curl_init();
	  
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
	  	//curl_setopt( $curl, CURLOPT_FAILONERROR, true );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->timeout );
	  
		$curl_results = curl_exec( $curl );
	  
		if ( curl_error( $curl ) ) {
		  	$this->log( '[' . __METHOD__ . '] curl_error: ' + curl_error( $curl ) );
			die( curl_error( $curl ) );
		}
	  
	  	curl_close( $curl );
	  
		return $curl_results;
	}

}

?>
