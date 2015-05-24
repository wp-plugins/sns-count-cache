<?php
/*
class-export-engine.php

Description: This class is a data export engine whitch exports cached data using wp-cron at regular intervals  
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


abstract class Export_Engine extends Engine {

	/**
	 * Meta key for share second cache
	 */	    
  	protected $share_meta_key_prefix = NULL;

	/**
	 * Meta key for follow second cache
	 */	    
  	protected $follow_meta_key_prefix = NULL;  
      	
}

?>