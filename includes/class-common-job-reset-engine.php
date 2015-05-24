<?php
/*
class-common-job-reset-engine.php

Description: This class is a job reset engine whitch reset expired jobs  
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


class Common_Job_Reset_Engine extends Engine {
    
	/**
	 * Cron name to schedule reset processing
	 */	      
  	const DEF_PRIME_CRON = 'scc_common_jobreset_prime';

	/**
	 * Cron name to execute reset processing
	 */	        
  	const DEF_EXECUTE_CRON = 'scc_common_jobreset_exec';

	/**
	 * Schedule name for reset processing
	 */	          
  	const DEF_EVENT_SCHEDULE = 'common_job_reset_event';

  	/**
	 * Schedule description for reset processing
	 */	          
   	const DEF_EVENT_DESCRIPTION = '[SCC] Common Job Reset Interval';
  
	/**
	 * Interval cheking and caching target data
	 */	  
	private $check_interval = 600;
    
	/**
	 * Time expired
	 */	    
  	private $expiration_time = 1800;
  
  	/**
	 * Cache target
	 */	            
  	private $target_cron = array();  

  	/**
	 * instance for delegation
	 */	   
  	private $delegate = NULL;
    	
  	/**
	 * Initialization
	 *
	 * @since 0.1.1
	 */
  	public function initialize( $options = array() ) {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

	  	$this->prime_cron = self::DEF_PRIME_CRON;
	  	$this->execute_cron = self::DEF_EXECUTE_CRON;
	  	$this->event_schedule = self::DEF_EVENT_SCHEDULE;
	  	$this->event_description = self::DEF_EVENT_DESCRIPTION;

	    if ( isset( $options['delegate'] ) ) $this->delegate = $options['delegate'];	  
	  	if ( isset( $options['target_cron'] ) ) $this->target_cron = $options['target_cron'];
	  	if ( isset( $options['check_interval'] ) ) $this->check_interval = $options['check_interval'];
		if ( isset( $options['prime_cron'] ) ) $this->prime_cron = $options['prime_cron'];
		if ( isset( $options['execute_cron'] ) ) $this->execute_cron = $options['execute_cron'];
		if ( isset( $options['event_schedule'] ) ) $this->event_schedule = $options['event_schedule'];
	  	if ( isset( $options['event_description'] ) ) $this->event_description = $options['event_description'];
	  	if ( isset( $options['expiration_time'] ) ) $this->expiration_time = $options['expiration_time'];
	  
		add_filter( 'cron_schedules', array( $this, 'schedule_check_interval' ) ); 
		add_action( $this->prime_cron, array( $this, 'prime_reset' ) );
		add_action( $this->execute_cron, array( $this, 'execute_reset' ), 10, 0 );

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
	 * Schedule job reset processing
	 *
	 * @since 0.2.0
	 */	   
	public function prime_reset() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );

		$next_exec_time = time() + $this->check_interval;

		Common_Util::log( '[' . __METHOD__ . '] check_interval: ' . $this->check_interval );
		Common_Util::log( '[' . __METHOD__ . '] next_exec_time: ' . $next_exec_time );
		
	  	wp_schedule_single_event( $next_exec_time, $this->execute_cron, array() ); 
	  			  	  
	}

  	/**
	 * Reset expired jobs
	 *
	 * @since 0.2.0
	 */	    
	public function execute_reset() {
	  	Common_Util::log( '[' . __METHOD__ . '] (line='. __LINE__ . ')' );
			  
	  	foreach ( $this->target_cron as $key => $hook ) {
			Common_Util::log( '[' . __METHOD__ . '] hook: ' . $hook );
	  		WP_Cron_Util::clear_expired_scheduled_hook( $hook, $this->expiration_time );	
		}
	  
	}
    
}

?>