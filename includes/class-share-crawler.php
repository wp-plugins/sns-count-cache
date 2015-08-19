<?php
/*
class-share-crawler.php

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

class Share_Crawler extends Data_Crawler {
  
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
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_HATEBU] ) && $target_sns[SNS_Count_Cache::REF_SHARE_HATEBU] ) {
	  			$query_urls[SNS_Count_Cache::REF_SHARE_HATEBU] = 'http://api.b.st-hatena.com/entry.count?url=' . $url;
		  }
		  
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_TWITTER] ) && $target_sns[SNS_Count_Cache::REF_SHARE_TWITTER] ) {
	  			$query_urls[SNS_Count_Cache::REF_SHARE_TWITTER] = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url;
		  }
	  
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_FACEBOOK] ) && $target_sns[SNS_Count_Cache::REF_SHARE_FACEBOOK] ) {
		  		$query_urls[SNS_Count_Cache::REF_SHARE_FACEBOOK] = 'https://api.facebook.com/method/links.getStats?urls=' . $url . '&format=json';		  
		  }
	  
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_GPLUS] ) && $target_sns[SNS_Count_Cache::REF_SHARE_GPLUS] ) {
	  			$query_urls[SNS_Count_Cache::REF_SHARE_GPLUS] = 'https://apis.google.com/_/+1/fastbutton?url=' . $url;
		  }
	  
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_POCKET] ) && $target_sns[SNS_Count_Cache::REF_SHARE_POCKET] ) {
		  		$query_urls[SNS_Count_Cache::REF_SHARE_POCKET] = 'http://widgets.getpocket.com/v1/button?v=1&count=horizontal&url=' . $url;
		  }
		
		  /*
		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_PINTEREST] ) && $target_sns[SNS_Count_Cache::REF_SHARE_PINTEREST] ) {
		  		$query_urls[SNS_Count_Cache::REF_SHARE_PINTEREST] = 'http://api.pinterest.com/v1/urls/count.json?url=' . $url;
		  }		  

		  if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_LINKEDIN] ) && $target_sns[SNS_Count_Cache::REF_SHARE_LINKEDIN] ) {
		  		$query_urls[SNS_Count_Cache::REF_SHARE_LINKEDIN] = 'http://www.linkedin.com/countserv/count/share?url=' . $url;
		  }
		  */
		  
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
		  
			switch ( $sns ) {
			  	case SNS_Count_Cache::REF_SHARE_HATEBU:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) && is_numeric( $content['data'] ) ) {
				  		$count = (int) $content['data'];
					} elseif ( empty( $content['data'] ) && empty( $content['error'] ) ) {
			  			$count = (int) 0;
					} else {
				  		$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_HATEBU] = $count;
			  		break;
			  	case SNS_Count_Cache::REF_SHARE_FACEBOOK:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
				  
				  		$json = json_decode( $content['data'], true );
			  
			  			if ( isset( $json[0]['total_count'] ) && is_numeric( $json[0]['total_count'] ) ) {
				  			$count = (int) $json[0]['total_count'];
						} else {
				  			$count = (int) -1;
						}
					} else {
						$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_FACEBOOK] = $count;			  
			  		break;
			  	case SNS_Count_Cache::REF_SHARE_TWITTER:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
						$json = json_decode( $content['data'], true );
	  
			  			if ( isset( $json['count'] ) && is_numeric( $json['count'] ) ) {
				  			$count = (int) $json['count'];
						} else {
				  			$count = (int) -1;
						}
					} else {
				  		$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_TWITTER] = $count;			  
			  		break;
			  	case SNS_Count_Cache::REF_SHARE_GPLUS:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
			  
			  			$return_code = preg_match( '/\[2,([0-9.]+),\[/', $content['data'], $matches );
			  	
			  			if ( $return_code && isset( $matches[1] ) && is_numeric( $matches[1] ) ) {
							$count = (int) $matches[1];   
						} else {
				  			$count = (int) -1;		
						}
					} else {
						$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_GPLUS] = $count;
			  		break;
			  	case SNS_Count_Cache::REF_SHARE_POCKET:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
			  
			  			$return_code = preg_match( '/<em\sid=\"cnt\">([0-9]+)<\/em>/i', $content['data'], $matches );
			  	
			  			if ( $return_code && isset( $matches[1] ) && is_numeric( $matches[1] ) ) {
							$count = (int) $matches[1];
						} else {
				  			$count = (int) -1;
						}
					} else {
						$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_POCKET] = $count;
			  		break;
			  /*
			  	case SNS_Count_Cache::REF_SHARE_PINTEREST:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
					  
					  	$json = rtrim( $content['data'], ')' );
						$json = ltrim( $json, 'receiveCount(' );
					  
						$json = json_decode( $json, true );
	  
			  			if ( isset( $json['count'] ) && is_numeric( $json['count'] ) ) {
				  			$count = (int) $json['count'];
						} else {
				  			$count = (int) -1;
						}
					} else {
				  		$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_PINTEREST] = $count;			  
			  		break;			  	
			  	case SNS_Count_Cache::REF_SHARE_LINKEDIN:
			  		if ( isset( $content['data'] ) && empty( $content['error'] ) ) {
					  
						$json = rtrim( $content['data'], ');' );
						$json = ltrim( $json, 'IN.Tags.Share.handleCount(' );
					  
						$json = json_decode( $json, true );
	  
			  			if ( isset( $json['count'] ) && is_numeric( $json['count'] ) ) {
				  			$count = (int) $json['count'];
						} else {
				  			$count = (int) -1;
						}
					} else {
				  		$count = (int) -1;
					}
			  
			  		$sns_counts[SNS_Count_Cache::REF_SHARE_LINKEDIN] = $count;			  
			  		break;					  
					*/
			  
		  	}
		  
		}
	  
	  	if ( isset( $target_sns[SNS_Count_Cache::REF_SHARE_TOTAL] ) && $target_sns[SNS_Count_Cache::REF_SHARE_TOTAL] ) {
		 
		  	$total = 0;
		  
		  	foreach ( $sns_counts as $sns => $count ) {
			  	if ( isset( $count ) && $count >= 0 ) {
			  		$total = $total + $count;
				}
		  	}
		  
		  	$sns_counts[SNS_Count_Cache::REF_SHARE_TOTAL] = (int) $total;
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
