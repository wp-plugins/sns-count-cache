<?php
/*
class-share-analytical-engine.php 

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


class Share_Analytical_Engine extends Analytical_Engine {

	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_TRANSIENT_PREFIX = 'scc_share_count_';

	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_BASE_PREFIX = 'scc_share_base_';

	/**
	 * Prefix of cache ID
	 */	    
  	const DEF_DELTA_PREFIX = 'scc_share_delta_';
  
	/**
	 * Cron name to schedule cache processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_share_updatebase_prime';

	/**
	 * Cron name to execute cache processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_share_updatebase_exec';

	/**
	 * Schedule name for cache processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'share_update_base_event';

  	/**
	 * Schedule description for cache processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Share Update Base Interval';
  
	/**
	 * Interval cheking and caching target data
	 */	  
	private $check_interval = 600;
  
    /**
	 * Offset suffix
	 */	    
  	private $base_schedule = '* * * 0 0';
  
    /**
	 * Base directory
	 */	  
  	private $base_dir = NULL;
  
    /**
	 * Crawl date key
	 */	  
  	private $crawl_date_key = NULL;
     
  	/**
	 * Initialization
	 *
	 * @since 0.1.1
	 */
  	public function initialize( $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  	$this->cache_prefix = self::DEF_TRANSIENT_PREFIX;
	  	$this->base_prefix = self::DEF_BASE_PREFIX;
	 	$this->delta_prefix = self::DEF_DELTA_PREFIX;
	  	$this->prime_cron = self::DEF_PRIME_CRON;
	  	$this->execute_cron = self::DEF_EXECUTE_CRON;
	  	$this->event_schedule = self::DEF_EVENT_SCHEDULE;
	  	$this->event_description = self::DEF_EVENT_DESCRIPTION;
		$this->base_dir = WP_PLUGIN_DIR . '/sns-count-cache/data/';
	  	  
	    if ( isset( $options['delegate'] ) ) $this->delegate = $options['delegate'];
	  	if ( isset( $options['target_sns'] ) ) $this->target_sns = $options['target_sns'];
	  	if ( isset( $options['check_interval'] ) ) $this->check_interval = $options['check_interval'];
	  	if ( isset( $options['base_schedule'] ) ) $this->base_schedule = $options['base_schedule'];
	  	if ( isset( $options['cache_prefix'] ) ) $this->cache_prefix = $options['cache_prefix'];
	  	if ( isset( $options['base_prefix'] ) ) $this->base_prefix = $options['base_prefix'];
	  	if ( isset( $options['delta_prefix'] ) ) $this->delta_prefix = $options['delta_prefix'];
		if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
	  	if ( isset( $options['post_types'] ) ) $this->post_types = $options['post_types'];
	  	if ( isset( $options['crawl_date_key'] ) ) $this->crawl_date_key = $options['crawl_date_key'];
	  
		add_action( $this->prime_cron, array( $this, 'prime_base' ) );
		add_action( $this->execute_cron, array( $this, 'execute_base' ), 10, 1 );
		add_filter( 'cron_schedules', array( $this, 'schedule_check_interval' ) ); 
  	}  

  	/**
	 * Register event schedule for this engine
	 *
	 * @since 0.1.0
	 */	     
	public function schedule_check_interval( $schedules ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		$schedules[$this->event_schedule] = array(
			'interval' => $this->check_interval,
			'display' => $this->event_description
		);
	  
		return $schedules;
	}    
  
  	/**
	 * Schedule data retrieval and cache processing
	 *
	 * @since 0.1.0
	 */	   
	public function prime_base() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$next_exec_time = WP_Cron_Util::next_exec_time( $this->base_schedule );
	  		
	  	Common_Util::log( '[' . __METHOD__ . '] next_exec_time (timesatamp): ' . $next_exec_time );
	  	Common_Util::log( '[' . __METHOD__ . '] next_exec_time (date): ' . date_i18n( 'Y/m/d H:i:s', $next_exec_time ) );
		
	  
	  	if( ! WP_Cron_Util::is_scheduled_hook( $this->execute_cron ) ) {
		  	wp_schedule_single_event( $next_exec_time, $this->execute_cron, array( Common_Util::short_hash( $next_exec_time ) ) );  
	  	}
	}
    
  	/**
	 * Get and cache data of each published post and page
	 *
	 * @since 0.1.0
	 */	    
	public function execute_base( $hash ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  		  
	  	$current_date = date_i18n( 'Y/m/d H:i:s' );
	  	  
	  	if( file_exists( $this->base_dir ) ) {
		   
	  		$base_file = $this->base_dir . $this->get_base_key( 'home' );
	  
	  		if ( ! file_exists( $base_file ) ) {
		  
		  		if ( touch( $base_file ) ) {
			  		Common_Util::log( '[' . __METHOD__ . '] file creation succeeded: ' . $base_file );  	
				} else {
			  		Common_Util::log( '[' . __METHOD__ . '] file creation failed: ' . $base_file );
				}  
	  		}
	  
	  		if ( file_exists( $base_file ) ) {
		  		Common_Util::log( '[' . __METHOD__ . '] file exists: ' . $base_file );
		  			  
			  	$option_key = $this->get_cache_key( 'home' );
			  
				if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {	

					foreach ( $this->target_sns as $sns => $active ) {
					  					  						  
						if ( $active ) {
							if ( $sns !== $this->crawl_date_key ) {
								if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
									  $sns_counts[$sns] = (int) -1;
								}
							} else {
								if ( ! isset( $sns_counts[$sns] ) ) {
									  $sns_counts[$sns] = '';
								}
							}
						}
					}
					
				  	if ( ! in_array( -1, $sns_counts, true ) ) {
			
				  		$data = serialize( $sns_counts ); 
						  
				  		$fp = fopen( $base_file, 'w' );
				  
						if ( fwrite( $fp, $data ) ) {
			  				Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ');
						} else {
			  				Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ');
						}	

		  				if ( fclose( $fp ) ) {
			  				Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $base_file );
						} else {
			  				Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $base_file );
						}
					}					  
				  
				}
			  
			}
		  
		  	$query_args = array(
				'post_type' => $this->post_types,
				'post_status' => 'publish',
				'nopaging' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false
			);

			$posts_query = new WP_Query( $query_args );
			  	
			if ( $posts_query->have_posts() ) {
				while ( $posts_query->have_posts() ) {
					$posts_query->the_post();
			  
					$post_id = get_the_ID();
				  				  
				  	$base_file = $this->base_dir . $this->get_base_key( $post_id );

	  				if ( ! file_exists( $base_file ) ) {
		  
		  				if ( touch( $base_file ) ) {
			  				Common_Util::log( '[' . __METHOD__ . '] export file creation succeeded: ' . $base_file );  	
						} else {
			  				Common_Util::log( '[' . __METHOD__ . '] export file creation failed: ' . $base_file );
						}  
	  				}
	  
	  				if ( file_exists( $base_file ) ) {
					  
				 		$sns_counts = array();	
				  					  
						foreach ( $this->target_sns as $sns => $active ) {
						  
						  	if ( $active ) {
					  					  						  						  
						  		if ( $sns !== $this->crawl_date_key ) {
						 					  
									$meta_key = $this->get_cache_key( $sns );

									$sns_count = get_post_meta( $post_id, $meta_key, true );
															
									if ( isset( $sns_count ) && $sns_count !== '' && $sns_count >= 0 ) {
										$sns_counts[$sns] = (int) $sns_count;
									} else {
										$sns_counts[$sns] = (int) -1;
									}
								} else {
							
									$meta_key = $this->get_cache_key( $sns );

									$sns_count = get_post_meta( $post_id, $meta_key, true );
																						
							  		if ( isset( $sns_count ) && $sns_count !== '' ) {
								  		$sns_counts[$sns] = $sns_count;
									} else {
								  		$sns_counts[$sns] = '';
									}
							  	
								}
							}
						}
											  
					  	if ( ! in_array( -1, $sns_counts, true ) ) {
						  
					  		$data = serialize( $sns_counts );
					  
					  		$fp = fopen( $base_file, 'w' );

				  			if ( fwrite( $fp, $data ) ) {
			  					Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ' );
							} else {
			  					Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ' );
							}					  	

		  					if ( fclose( $fp ) ) {
			  					Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $base_file );
							} else {
			  					Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $base_file );
							}
						}
					}		 
				}
			}
		  	wp_reset_postdata();
  		}
	  
	}

    /**
	 * Initialize meta key for ranking 
	 *
	 * @since 0.6.1
	 */	    
  	public function analyze( $options = array() ) {
	  
	  	$transient_id = $options['cache_key'];
	  	$target_sns = $options['target_sns'];
	  	$post_id = $options['post_id'];	  

		$base_file = $this->base_dir . $this->get_base_key( $post_id );
		  
	  	$sns_counts = array();
		$sns_base_counts = array();
					  	
		if ( file_exists( $base_file ) ) {

			$fp = fopen( $base_file, 'r' );
					  				  
		  	$data = fread( $fp, filesize( $base_file ) );

			if ( fclose( $fp ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $base_file );
			} else {
			  	Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $base_file );
			}		  
		  
		  	$sns_base_counts = unserialize( $data );
		  
		} else {
		  	// if there is no base file.
		  
		  	if ( touch( $base_file ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] file creation succeeded: ' . $base_file );  	
			} else {
			  	Common_Util::log( '[' . __METHOD__ . '] file creation failed: ' . $base_file );
			}
		  
	  		if ( file_exists( $base_file ) ) {
					  
				$sns_counts = array();	
			  
			  	if ( $post_id !== 'home' ) {
				  
					foreach ( $this->target_sns as $sns => $active ) {
					  					  						  
						if ( $active ) {
						  
						  	if ( $sns !== $this->crawl_date_key ) {
						 					  
								$meta_key = $this->get_cache_key( $sns );

								$sns_count = get_post_meta( $post_id, $meta_key, true );
															
								if ( isset( $sns_count ) && $sns_count !== '' && $sns_count >= 0 ) {
									$sns_counts[$sns] = (int) $sns_count;
								} else {
									$sns_count[$sns] = (int) -1;
								}
							} else {
							
								$meta_key = $this->get_cache_key( $sns );

								$sns_count = get_post_meta( $post_id, $meta_key, true );
																						
							  	if ( isset( $sns_count ) && $sns_count !== '' ) {
								  	$sns_counts[$sns] = $sns_count;
								} else {
								  	$sns_counts[$sns] = '';
								}
							  	
							}
						}
						  
					}
				} else {
				  	$option_key = $this->get_cache_key( 'home' );
				  
				  	if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {
					  
						foreach ( $this->target_sns as $sns => $active ) {
					  					  						  
							if ( $active ) {
							  	if ( $sns !== $this->crawl_date_key ) {
								  	if ( ! isset( $sns_counts[$sns] ) || $sns_counts[$sns] < 0 ) {
									  	$sns_counts[$sns] = (int) -1;
									}
								} else {
								  	if ( ! isset( $sns_counts[$sns] ) ) {
									  	$sns_counts[$sns] = '';
								  	}
								}
							}
						}
				  	}
				  	
				}
			  
			  	if ( ! in_array( -1, $sns_counts, true ) ) {

					$data = serialize( $sns_counts );

			  		$fp = fopen( $base_file, 'w' );

					if ( fwrite( $fp, $data ) ) {
			  			Common_Util::log( '[' . __METHOD__ . '] file write succeeded: ' );
					} else {
			  			Common_Util::log( '[' . __METHOD__ . '] file wrote failed: ' );
					}					  	

		  			if ( fclose( $fp ) ) {
			  			Common_Util::log( '[' . __METHOD__ . '] file close succeeded: ' . $base_file );
					} else {
			  			Common_Util::log( '[' . __METHOD__ . '] file close failed: ' . $base_file );
					}
				}
			}		 		  
		  
		}

	  	$sns_counts = array();
	  	$diffs = array();
	  
	  	if ( $post_id !== 'home' ) {
		  
			foreach ( $this->target_sns as $sns => $active ) {				  
				if( $active ){
				  	$meta_key = $this->get_cache_key( $sns );
				  	$sns_counts[$sns] = get_post_meta( $post_id, $meta_key, true );
				}
			}
		  
		  	if ( ! in_array( -1, $sns_counts, true ) ) {
		  			
				foreach ( $this->target_sns as $sns => $active ) {
					  	  							  						  
					if( $active ){
				  
				  		if ( $sns !== $this->crawl_date_key ) {
				  
				  			$diff = 0;
				  													  					  
							if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' && $sns_counts[$sns] >= 0 && isset( $sns_base_counts[$sns] ) && $sns_base_counts[$sns] !== '' && $sns_base_counts[$sns] >= 0 ) {
								$diff = $sns_counts[$sns] - $sns_base_counts[$sns];
							} else {
								$diff = 0;
							}
				  
				  			$meta_key = $this->get_delta_key( $sns );
				  
				  			update_post_meta( $post_id, $meta_key, (int) $diff );
											  
						} else {
																				  					  
							if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' && isset( $sns_base_counts[$sns] ) && $sns_base_counts[$sns] !== '' ) {
								$crawl_date = $sns_base_counts[$sns] . ',' . $sns_counts[$sns];
							} else {
						  		$crawl_date = '';
							}
					  
				  			$meta_key = $this->get_delta_key( $sns );
				  
				  			update_post_meta( $post_id, $meta_key, $crawl_date );
						
						}
					}												  
				}
			} else {
			  
				foreach ( $this->target_sns as $sns => $active ) {
					  	  							  						  
					if( $active ){
				  
					  	$meta_key = $this->get_delta_key( $sns );
					  
				  		if ( $sns !== $this->crawl_date_key ) {
				  				  
							$diff = 0;
						  
				  			update_post_meta( $post_id, $meta_key, (int) $diff );
											  
						} else {
																				  					  							
						  	$crawl_date = '';
											  
				  			update_post_meta( $post_id, $meta_key, $crawl_date );						
						}
					}												  
				}			  
			  
			}
		   		  
		} else {
		  				  	
			$option_key = $this->get_cache_key( 'home' );

			$diffs = array();

			if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {

		  		if ( ! in_array( -1, $sns_counts, true ) ) {			  
			  		foreach ( $this->target_sns as $sns => $active ) {
						if( $active ){  
				  			if ( $sns !== $this->crawl_date_key ) {					  
								if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 && isset( $sns_base_counts[$sns] ) && $sns_base_counts[$sns] !== '' && $sns_base_counts[$sns] >= 0 ) { 				
									$diffs[$sns] = (int)( $sns_counts[$sns] - $sns_base_counts[$sns] );
								} else {
					  				$diffs[$sns] = 0;
								}
							} else {
								if ( isset( $sns_counts[$sns] ) && isset( $sns_base_counts[$sns] ) && $sns_base_counts[$sns] !== '' ) { 				
									$diffs[$sns] = $sns_base_counts[$sns] . ',' . $sns_counts[$sns];
								} else {
					  				$diffs[$sns] = '';
								}						  	
							}
						}
					}
			  	
			  		$option_key = $this->get_delta_key( 'home' );
				  
					update_option( $option_key, $diffs );
				  
				} else {
					foreach ( $this->target_sns as $sns => $active ) {
				  		if( $active ){
					  		if ( $sns !== $this->crawl_date_key ) {
					  			$diffs[$sns] = (int) 0;
							} else {
						  		$diffs[$sns] = '';
							}
						}
					}
			  
			  		$option_key = $this->get_delta_key( 'home' );
				   					  	
			  		update_option( $option_key, $diffs );					  
				}
			  
			} else {
			  	
				foreach ( $this->target_sns as $sns => $active ) {
				  	if( $active ){
					  	if ( $sns !== $this->crawl_date_key ) {
					  		$diffs[$sns] = (int) 0;
						} else {
						  	$diffs[$sns] = '';
						}
					}
				}
			  
			  	$option_key = $this->get_delta_key( 'home' );
				   					  	
			  	update_option( $option_key, $diffs );		

			}			  	
			 
		}	  
	  
  	}
  
    /**
	 * Initialize meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function initialize_base() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
	  
	  /*
	  	$option_key = $this->get_cache_key( $this->offset_suffix );
	  
	  	update_option( $option_key, 0 );
		*/  
  	}  

    /**
	 * Clear meta key for ranking 
	 *
	 * @since 0.3.0
	 */	     
  	public function clear_base() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
  
		$option_key = $this->get_delta_key( 'home' );
		 	
		delete_option( $option_key );
			
	  /*
		$query_args = array(
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'nopaging' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);

		$posts_query = new WP_Query( $query_args );
	  
		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();
			  
				$post_id = get_the_ID();
			  	
				foreach ( $this->target_sns as $sns => $active ) {
					  					  
					if ( $active ) {
					  	$meta_key = $this->get_delta_key( $sns );

						delete_post_meta( $post_id, $meta_key );
					}
				}		  	 
			}
		}
		wp_reset_postdata();
		*/
	  
		foreach ( $this->target_sns as $sns => $active ) {
					  					  
			if ( $active ) {
				$meta_key = $this->get_delta_key( $sns );
					  
				delete_post_meta_by_key( $meta_key ); 
			}
		}	

  	}
  
    /**
	 * Clear meta key for ranking 
	 *
	 * @since 0.7.0
	 */	     
  	public function clear_base_by_post_id( $post_id ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
		
		foreach ( $this->target_sns as $sns => $active ) {
					  					  
			if ( $active ) {
				$meta_key = $this->get_delta_key( $sns );					  
			  	delete_post_meta( $post_id, $meta_key );
			}
		  
		}
	  
  	}   	  
  
	  
}

?>
