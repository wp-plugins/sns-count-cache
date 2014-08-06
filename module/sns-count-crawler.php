<?php
/*
sns-count-crawler.php

Description: This class is a data crawler whitch get share count using given API and cURL
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

class SNSCountCrawler extends DataCrawler{
  
  	/**
	 * Timeout for cURL data retrieval
	 */	  
	private $timeout = 10;
  
	function __construct($url='',$timeout=10) {
		$this->url=rawurlencode($url);
		$this->timeout=$timeout;
	}

	function set_timeout($timeout){
		$this->timeout = $timeout;
	}

  	/**
	 * Implementation of abstract method. this method gets each share count
	 *
	 */	  
  	public function get_data(){
		$sns_counts = array();
	  
	  	$sns_counts[SNSCountCache::REF_HATEBU] = $this->get_hatebu_count();
	  	$sns_counts[SNSCountCache::REF_TWITTER] = $this->get_twitter_count();
	  	$sns_counts[SNSCountCache::REF_FACEBOOK] = $this->get_facebook_count();
	  	$sns_counts[SNSCountCache::REF_GPLUS] = $this->get_gplus_count();
	  
		return $sns_counts;
	}
  
  	/**
	 * Get share count for Hatena Bookmark
	 *
	 */	      
	public function get_hatebu_count() {
	  	$query = 'http://api.b.st-hatena.com/entry.count?url=' . $this->url;
	  
		$hatebu = $this->file_get_contents($query);
	  
		return isset($hatebu) ? intval($hatebu) : 0;
	}

  	/**
	 * Get share count for Twitter
	 *
	 */	        
	public function get_twitter_count() {
	  	$query = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $this->url;
	  
		$json = $this->file_get_contents($query);
	  
		$twitter = json_decode($json, true);
	  
		return isset($twitter['count']) ? intval($twitter['count']) : 0;
	}

  	/**
	 * Get share count for Facebook
	 *
	 */	        
	public function get_facebook_count() {
	  	$query = 'http://graph.facebook.com/' . $this->url;
	  
		$json = $this->file_get_contents($query);
	  
		$facebook = json_decode($json, true);
	  
		return isset($facebook['shares']) ? intval($facebook['shares']) : 0;
	}

  	/**
	 * Get share count for Google Plus
	 *
	 */	          
	public function get_gplus_count()	{
		$curl = curl_init();
	  
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode($this->url) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	  
		$curl_results = curl_exec ($curl);
	  
		if(curl_error($curl)) {
			die(curl_error($curl));
		}	  
	  
		curl_close ($curl);
	  
		$json = json_decode($curl_results, true);
	  
		return isset($json[0]['result']['metadata']['globalCounts']['count']) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : 0;
	}
  
  	/**
	 * Get content from given URL using cURL
	 *
	 */	          
	private function file_get_contents($url){
		$curl = curl_init();
	  
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
	  
		$curl_results = curl_exec($curl);
	  
		if(curl_error($curl)) {
			die(curl_error($curl));
		}
	  
	  	curl_close ($curl);
	  
		return $curl_results;
	}

  	/**
	 * Output log message according to WP_DEBUG setting
	 *
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

?>