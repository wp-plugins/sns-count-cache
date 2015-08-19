<?php
/*
class-follow-crawler.php

Description: This class is a data crawler whitch get share count using given API and cURL
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

class Follow_Crawler extends Data_Crawler {
      
  	/**
	 * Initialization
	 *
	 * @since 0.5.1
	 */
  	public function initialize( $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	//$this->throttle = new Sleep_Throttle( 0.9 );
	  
	  	if ( isset( $options['crawl_method'] ) ) $this->crawl_method = $options['crawl_method'];
	  	if ( isset( $options['timeout'] ) ) $this->timeout = $options['timeout'];
	  	if ( isset( $options['ssl_verification'] ) ) $this->ssl_verification = $options['ssl_verification'];
	  	if ( isset( $options['crawl_retry'] ) ) $this->crawl_retry = $options['crawl_retry'];
	  	if ( isset( $options['retry_limit'] ) ) $this->retry_limit = $options['retry_limit'];
	}  
    
  	/**
	 * Implementation of abstract method. this method gets each share count
	 *
	 * @since 0.1.1
	 */	    
  	public function get_data( $target_sns, $url ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	Common_Util::log( '[' . __METHOD__ . '] Feedly' );
	  
	  	$url = rawurlencode( $url );
	  
	  	$query_urls = $this->build_query_urls( $target_sns, $url );
	  	  
	  	$data = array();
	  
	  	$throttle = new Sleep_Throttle( 0.9 );
	  	  		  
		$throttle->reset();
		$throttle->start();
	  
	  	if ( $this->crawl_method === SNS_Count_Cache::OPT_COMMON_CRAWLER_METHOD_CURL ) {
		  	$data = Common_Util::multi_remote_get( $query_urls, $this->timeout, $this->ssl_verification, true );
		} else {
			$data = Common_Util::multi_remote_get( $query_urls, $this->timeout, $this->ssl_verification, false );  
		}
	  
	  	$throttle->stop();
	  
	  	$retry_count = 0;
	  	
	  	while( true ) {
	  
	  		$target_sns_retry = array();
	  
	  		$tmp_count = $this->extract_count( $target_sns, $data );
	  
	  		foreach ( $target_sns as $sns => $active ){
		  		if ( $active ) {
			  		if( $tmp_count[$sns] === -1 ) {
				  		$target_sns_retry[$sns] = true;
			  		}
		  		}
			}
	  		  		  		  
		  	if ( empty( $target_sns_retry ) ) {
		  		break;
			  
			} else {

	  			Common_Util::log( '[' . __METHOD__ . '] crawl failure' );
	  			Common_Util::log( $target_sns_retry );
			  
			  	if ( $retry_count < $this->retry_limit ) {
				  
				  	Common_Util::log( '[' . __METHOD__ . '] sleep before crawl retry: ' . $throttle->get_sleep_time() . ' sec.' );
				  
		  			$throttle->sleep();
			  
			  		++$retry_count;

			  		Common_Util::log( '[' . __METHOD__ . '] count of crawl retry: ' . $retry_count );
		  			  
		  			$query_urls_retry = $this->build_query_urls( $target_sns_retry, $url );
		  
		  			$data_retry = array();
			  
			  		$throttle->reset();
					$throttle->start();
		  
	  				if ( $this->crawl_method === SNS_Count_Cache::OPT_COMMON_CRAWLER_METHOD_CURL ) {
		  				$data_retry = Common_Util::multi_remote_get( $query_urls_retry, $this->timeout, $this->ssl_verification, true );
					} else {
						$data_retry = Common_Util::multi_remote_get( $query_urls_retry, $this->timeout, $this->ssl_verification, false );  
					}
		  
			  		$throttle->stop();
			  
		  			$data = array_merge( $data, $data_retry );
				} else {
				  	Common_Util::log( '[' . __METHOD__ . '] crawling: retry failed' );
				  	break;
				}
			}		  
		}
	  
	  	return $this->extract_count( $target_sns, $data );
  	}
	  
 	/**
	 * build query
	 *
	 * @since 0.5.1
	 */	     
  	private function build_query_urls( $target_sns, $url ) {
		Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$query_urls = array();
	  
	  	if ( isset( $url ) ) {
			if ( isset( $target_sns[SNS_Count_Cache::REF_FOLLOW_FEEDLY] ) && $target_sns[SNS_Count_Cache::REF_FOLLOW_FEEDLY] ) {
	  			$query_urls[SNS_Count_Cache::REF_FOLLOW_FEEDLY] = 'http://cloud.feedly.com/v3/feeds/feed%2F' . $url;
			}
	  	}
	    
	  	return $query_urls;
	  	
  	}  	

  	/**
	 * extract count data from retrieved content
	 *
	 * @since 0.5.1
	 */
  	private function extract_count( $target_sns, $contents ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$sns_counts = array();

	  	$extract_date = date_i18n( 'Y/m/d H:i:s' );
	  
	  	Common_Util::log( $contents );
	  
		foreach ( $contents as $sns => $content ) {  
	  
			if ( $sns == SNS_Count_Cache::REF_FOLLOW_FEEDLY ) {
			  
			  	if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
				  	$json = json_decode( $content['data'], true );
				  
			  		if ( isset( $json['subscribers'] ) && is_numeric( $json['subscribers'] ) ) {
				  		$count = (int) $json['subscribers'];
					} else {
				  		$count = (int) -1;
					}
				} else {
				  	$count = (int) -1;
				}
			  
			  	$sns_counts[SNS_Count_Cache::REF_FOLLOW_FEEDLY] = $count;
			  
			} 
		}

	  	if ( isset( $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] ) && $target_sns[SNS_Count_Cache::REF_CRAWL_DATE] ) {
	  		$sns_counts[SNS_Count_Cache::REF_CRAWL_DATE] = $extract_date;
		} else {
	  		$sns_counts[SNS_Count_Cache::REF_CRAWL_DATE] = '';
		}
	  
	  	return $sns_counts;
	  
  	}  
  
  
}

?>
