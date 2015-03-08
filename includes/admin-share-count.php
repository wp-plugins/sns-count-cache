<?php
/*
admin-share-count.php

Description: Option page implementation
Version: 0.4.0
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

	$posts_per_page = 50;

	$sort_mode = false;
	$sort_exec_key = '';
/*
	if ( isset( $_GET['_wpnonce'] ) && $_GET['_wpnonce'] ) {
		$nonce = $_GET['_wpnonce'];	  
	  
	 	if ( wp_verify_nonce( $nonce, __FILE__ ) ) {
		  	if( isset( $_GET["action"] ) && $_GET["action"] === 'sort' ) {
			  
				if( isset( $_GET["key"] ) ) {
				  	$sort_mode = true;
				  	$key = $_GET["key"];
				    										  	
					if ( $key === 'Google' ) {
						$key =  $key . '+';
					}
				  
				  	$sort_exec_key = $key;
				  	$meta_key = self::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $key );
				}
			  
			}
	  	} 	  	  
	}
	*/

	if( isset( $_GET["action"] ) && $_GET["action"] === 'sort' ) {
			  
		if( isset( $_GET["key"] ) ) {
			$sort_mode = true;
			$key = $_GET["key"];
				    										  	
			if ( $key === 'Google' ) {
				$key =  $key . '+';
			}
				  
			$sort_exec_key = $key;
			$meta_key = self::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $key );
		}
			  
	}

	$paged = 1;

	if ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] > 0 ) {
		$paged = $_GET['paged'];
	} else {
	  	$paged = 1;
	}

	$share_base_cache_target = $this->share_base_cache_target ;
	unset( $share_base_cache_target[self::REF_CRAWL_DATE] );

	?>
	<div class="wrap">
	  	<h2><a href="admin.php?page=scc-share-count"><?php _e( 'SNS Count Cache', self::DOMAIN ) ?></a></h2>
			<div class="sns-cnt-cache">
			  		<h3 class="nav-tab-wrapper">
					  	<a class="nav-tab" href="admin.php?page=scc-dashboard">Dashboard</a>
					  	<a class="nav-tab" href="admin.php?page=scc-cache-status">Cache Status</a>
					  	<a class="nav-tab nav-tab-active" href="admin.php?page=scc-share-count">Share Count</a>
					  	<a class="nav-tab" href="admin.php?page=scc-setting">Setting</a>
					  	<a class="nav-tab" href="admin.php?page=scc-help">Help</a>
			  		</h3>

					<div class="metabox-holder">
						<div id="share-each-content" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Share Count', self::DOMAIN ) ?></span></h3>  	
							<div class="inside">

				  
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th>No.</th>
								<th><?php _e( 'Content', self::DOMAIN ) ?></th>
							  	<?php
						  						  			
						  			foreach ( $share_base_cache_target as $key => $value ){	
									  
									  	if ( $value ) {
										  
						  					$nonce = wp_create_nonce( __FILE__ );
										  	
										  	$sort_key = $key;
										  	
										  	if ( $sort_key === self::REF_SHARE_GPLUS ) {
											  	$sort_key =  str_replace( '+', '', $sort_key );
										  	}
										  	
										  	//$sort_url = esc_url( 'admin.php?page=scc-share-count&_wpnonce=' . $nonce . '&action=sort&key=' . $sort_key ); 
										  	$sort_url = esc_url( 'admin.php?page=scc-share-count&action=sort&key=' . $sort_key );
										  
										  	if ( $key === $sort_exec_key ){
												echo '<th><a class="sort-exec-key" href="' . $sort_url . '">' . $key . '</th>';		
											} else {
											  	echo '<th><a href="' . $sort_url . '">' . $key . '</th>';	
											}
									  	}
									  
									}

									/*
									if ( function_exists( 'wpp_get_views' ) ) {
									  	echo '<th>Views</th>';	
									}
									*/

					  			?>
			  				</tr>
						</thead>
						<tbody>

						<?php

						if( $sort_mode ) {
							$query_args = array(
									'post_type' => $this->share_base_cache_post_types,
									'post_status' => 'publish',
		  							'posts_per_page' => $posts_per_page,
      								'paged' => $paged,
									'meta_key' =>  $meta_key,
 									'orderby'  =>  'meta_value_num',
									'update_post_term_cache' => false,
									'order' => 'DESC'
									);
	  
						} else {

							$query_args = array(
									'post_type' => $this->share_base_cache_post_types,
									'post_status' => 'publish',
		  							'posts_per_page' => $posts_per_page,
      								'paged' => $paged,
									'update_post_term_cache' => false
									);	  
						}

						$posts_query = new WP_Query( $query_args );

						$count = ($paged - 1) * $posts_per_page + 1;

						if ( $posts_query->have_posts() ) {
							while ( $posts_query->have_posts() ) {
								$posts_query->the_post();			  
						?>
			  				<tr>
								<td><?php echo $count; ?></td>
							  	<td><a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( get_the_ID() ) ); ?></a></td>
							<?php  
								$transient_id = self::OPT_SHARE_BASE_TRANSIENT_PREFIX . get_the_ID();
							  							  
								if ( $sort_mode || false === ( $sns_counts = get_transient( $transient_id ) ) ) {
						  
						  			foreach ( $share_base_cache_target as $key => $value ) {
									  	if( $value ){
										  /**
					  						echo '<td class="not-cached share-count">';
								  			_e( 'N/A', self::DOMAIN );
					  						echo '</td>';
											*/
											
										  	$meta_key = self::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $key );
							  				$sns_counts[$key] = get_post_meta( get_the_ID(), $meta_key, true );
								  				
											if ( isset( $sns_counts[$key] ) &&  $sns_counts[$key] >= 0 ) {
					  							echo '<td class="share-count">';
								  				echo number_format( (int) $sns_counts[$key] );
					  							echo '</td>';
											} else {
					  							echo '<td class="not-cached share-count">';
												_e( 'N/A', self::DOMAIN );
					  							echo '</td>';
											}										  								 										  
									  	}
									}
								  
								  /*
								  	// WordPress Popular Posts
									if ( function_exists( 'wpp_get_views' ) ) {
									  	$views = wpp_get_views( get_the_ID(), 'all', true);
									  	
									  	if ( $views >= 0 ) {
										  	echo '<td class="share-count">';
								  			echo $views;
					  						echo '</td>';
										} else {
					  						echo '<td class="not-cached share-count">';
											_e( 'N/A', self::DOMAIN );
					  						echo '</td>';										  	
										}
									}								  
									*/
								} else {
								  	  
						  			foreach ( $share_base_cache_target as $key => $value ) {
									  	if ( $value ) {										  
								  			if ( isset( $sns_counts[$key] ) && $sns_counts[$key] >= 0 ) {
					  							echo '<td class="share-count">';
								  				echo number_format( (int) $sns_counts[$key] );
					  							echo '</td>';
											} else {
					  							echo '<td class="not-cached share-count">';
												_e( 'N/A', self::DOMAIN );
					  							echo '</td>';
											}										  
									  	}
									}
								  /*
								  	// WordPress Popular Posts
									if ( function_exists( 'wpp_get_views' ) ) {
									  	$views = wpp_get_views( get_the_ID(), 'all', true );
									  	
									  	if ( $views >= 0 ) {
										  	echo '<td class="share-count">';
								  			echo $views;
					  						echo '</td>';
										} else {
					  						echo '<td class="not-cached share-count">';
											_e( 'N/A', self::DOMAIN );
					  						echo '</td>';										  	
										}
									}
									*/
								  								  
								}
							?>
			  				</tr>

						<?php
								$count++;

							}
						}
						?>
						</tbody>
		  			</table>
						<?php
						$this->pagination( $posts_query->max_num_pages, '', $paged, true );
						  
						wp_reset_postdata();
						?>
							  
						  	</div>								  								  
						 </div>
			  		</div>				  
		</div>
     </div>
