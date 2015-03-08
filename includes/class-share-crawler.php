<?php
/*
class-share-crawler.php

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

class Share_Crawler extends Data_Crawler {
  
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
	  	
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_HATEBU] ) && $cache_target[SNS_Count_Cache::REF_SHARE_HATEBU] ) {
	  		$sns_counts[SNS_Count_Cache::REF_SHARE_HATEBU] = ( int )$this->get_hatebu_count( $url );
	  	}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_TWITTER] ) && $cache_target[SNS_Count_Cache::REF_SHARE_TWITTER] ) {
	  		$sns_counts[SNS_Count_Cache::REF_SHARE_TWITTER] = ( int )$this->get_twitter_count( $url );
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_FACEBOOK] ) && $cache_target[SNS_Count_Cache::REF_SHARE_FACEBOOK] ) {
		  	if ( Common_Util::extension_loaded_php_xml() ) {
		  		$sns_counts[SNS_Count_Cache::REF_SHARE_FACEBOOK] = ( int )$this->get_facebook_count( $url );
			} else {
			  	Common_Util::log( '[' . __METHOD__ . '] php-xml is not installed.' );
			}
		  
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_GPLUS] ) && $cache_target[SNS_Count_Cache::REF_SHARE_GPLUS] ) {
	  		$sns_counts[SNS_Count_Cache::REF_SHARE_GPLUS] = ( int )$this->get_gplus_count( $url );		  
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_POCKET] ) && $cache_target[SNS_Count_Cache::REF_SHARE_POCKET] ) {
		  	if ( Common_Util::extension_loaded_php_xml() ) {
		  		$sns_counts[SNS_Count_Cache::REF_SHARE_POCKET] = ( int )$this->get_pocket_count( $url );
			} else {
			  	Common_Util::log( '[' . __METHOD__ . '] php-xml is not installed.' );
			}
		}
	  
	  	if ( isset( $cache_target[SNS_Count_Cache::REF_SHARE_TOTAL] ) && $cache_target[SNS_Count_Cache::REF_SHARE_TOTAL] ) {
		 
		  	$total = 0;
		  
		  	foreach ( $sns_counts as $key => $value ) {
			  	if ( isset( $value) && $value >= 0 ) {
			  		$total = $total + $value;
				}
		  	}
		  
		  	$sns_counts[SNS_Count_Cache::REF_SHARE_TOTAL] = $total;
		}	  

	  	if ( isset( $cache_target[SNS_Count_Cache::REF_CRAWL_DATE] ) && $cache_target[SNS_Count_Cache::REF_CRAWL_DATE] ) {
	  		$sns_counts[SNS_Count_Cache::REF_CRAWL_DATE] = date_i18n( 'Y/m/d H:i:s' );
		}
	  
		return $sns_counts;	
  	}
  
  	/**
	 * Get share count for Hatena Bookmark
	 *
	 * @since 0.1.0
	 */	      
	public function get_hatebu_count( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	$query = 'http://api.b.st-hatena.com/entry.count?url=' . $url;
	  
		$hatebu = $this->remote_get( $query );
	  
		return isset( $hatebu ) ? intval($hatebu) : -1;
	}

  	/**
	 * Get share count for Twitter
	 *
	 * @since 0.1.0
	 */	        
	public function get_twitter_count( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  	  
	  	$query = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url;
	  
		$json = $this->remote_get( $query );
	  
		$twitter = json_decode( $json, true );
	  
		return isset( $twitter['count'] ) ? intval( $twitter['count'] ) : -1;
	}

  	/**
	 * Get share count for Facebook
	 *
	 * @since 0.1.0
	 */	        
	public function get_facebook_count( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$query = 'http://www.facebook.com/plugins/like.php?href=' . $url . '&width&layout=standard&action=like&show_faces=false&share=false&height=35&locale=ja_JP';
	  
	  	Common_Util::log( '[' . __METHOD__ . '] facebookquery: ' . $query );
	  
		$html = $this->remote_get( $query );
	  
	  	$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
	  
	  	$dom->loadHTML( $html );
	  	
		$xpath = new DOMXPath( $dom );
	  
	  	$result = $xpath->query( '//*[@id="u_0_2"]' )->item(0);
	  
		Common_Util::log( '[' . __METHOD__ . '] count element: ' . $result->nodeValue );
	  	
	  	if ( preg_match( '/([0-9,]+)/', $result->nodeValue, $match ) ) {
	  		$count = str_replace( ',', '', $match[1] );
		  	Common_Util::log( '[' . __METHOD__ . '] count: ' . $count );
		} else {
		  	$count = 0;
		}
	  
		return isset( $count ) ? intval( $count ) : -1;	  
	  
	}

  	/**
	 * Get share count for Google Plus
	 *
	 * @since 0.1.0
	 */	          
	public function get_gplus_count( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$query = 'https://apis.google.com/_/+1/fastbutton?url=' . $url;
	  
	  	$html = $this->remote_get( $query );
    
    	preg_match( '/\[2,([0-9.]+),\[/', $html, $count );
	  
	  	return isset( $count[1] ) ? intval( $count[1] ) : -1;
	}
  
  	/**
	 * Get share count for Pocket
	 *
	 * @since 0.1.0
	 */	            
  	public function get_pocket_count( $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$query = 'http://widgets.getpocket.com/v1/button?v=1&count=horizontal&url=' . $url;
	  
		$html = $this->remote_get( $query );
	  	
	  	$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXml( $html );
 
		$xpath = new DOMXPath( $dom );
	  
	  	$result = $xpath->query( '//em[@id = "cnt"]' )->item(0);
	  
		Common_Util::log( '[' . __METHOD__ . '] count: ' . $result->nodeValue );
	  
		return isset( $result->nodeValue ) ? intval( $result->nodeValue ) : -1;
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
