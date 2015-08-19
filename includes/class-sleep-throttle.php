<?php
/*
class-sleep-throttle.php

Description: sleep utility  
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

final class Sleep_Throttle {
  
    const SECOND_TO_MICRO_SECONDS = 1000000;
  
  	private $load_ratio = 0.9;
  
  	private $start_time = null;

 	private $stop_time = null;
  
  	private $sleep_time = null;
  
    function __construct( $load_ratio ) {  
        $this->load_ratio = $load_ratio;
    }

  	public function reset() {
	  	$this->start_time = null;
	  	$this->stop_time = null;
	  	$this->sleep_time = null;
  	}
  
    public function start() {  
        $this->start_time = gettimeofday( true );
    }

    public function stop() {  	  
        $this->stop_time = gettimeofday( true );
	  
	  	if ( ! is_null( $this->start_time ) && ! is_null( $this->stop_time ) ) {		  		  
        	$this->sleep_time = $this->calculate_sleep_time( $this->load_ratio, $this->stop_time - $this->start_time );
		}	  	
    }

    public function sleep() {  
        if ( ! is_null( $this->sleep_time) && $this->sleep_time > 0 ) {
            usleep( $this->sleep_time * self::SECOND_TO_MICRO_SECONDS );
        }		
    }  
  
  	public function get_sleep_time() {
	  	if ( ! is_null( $this->sleep_time) && $this->sleep_time > 0 ) {
	  		return $this->sleep_time;
		} else {
		  	return 0;
		}
  	}
  
    private function calculate_sleep_time( $load_ratio, $time ) {  
        if ( $time > 0.0 ) {
            return $time * ( 1 - $load_ratio ) / $load_ratio;
        } else {
            return 0;
        }
    }

}

?>