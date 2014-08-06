<?php
/*
data-crawler.php

Description: This class is abstract class of a data crawler
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

abstract class DataCrawler{
  
  	/**
	 * URL for data crawling
	 */	    
	protected $url = '';

  	/**
	 * Set URL for data crawling
	 */	      
	public function set_url($url){
		$this->url = rawurlencode($url);
	}
  
  	/**
	 * Abstract method for data crawling
	 *
	 */  
	abstract public function get_data();
}

?>