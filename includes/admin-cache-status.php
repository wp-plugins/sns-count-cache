<?php
/*
admin-cache-status.php

Description: Option page implementation
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

	$posts_per_page = 50;

	$paged = 1;

	if ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] > 0 ) {
		$paged = $_GET['paged'];
	} else {
	  	$paged = 1;
	}

	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], __FILE__ ) ) {
		  
		if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {  
	 	
		  	if( isset( $_GET["action"] ) && $_GET["action"] === 'cache' ) {
				if( isset( $_GET["post_id"] ) ) {
				  	$post_id = $_GET["post_id"];
				    										  	
				  	//$this->retrieve_share_cache( $post_id, true );
				  	$this->cache_engines[self::REF_SHARE_BASE]->direct_cache( $post_id, true );
				  
				  	//Common_Util::log( '[' . __METHOD__ . '] redirect destination: ' . menu_page_url( 'scc-cache-status', false ) );
				  	//wp_safe_redirect( menu_page_url( 'scc-cache-status', false ) ); 
				}		  
			}
		}
	  	
	}

	$query_args = array(
			'post_type' => $this->share_base_cache_post_types,
			'post_status' => 'publish',
		  	'posts_per_page' => $posts_per_page,
      		'paged' => $paged,
			'update_post_term_cache' => false
			);

	$posts_query = new WP_Query( $query_args );
	
	?>
	<div class="wrap">
	  	<h2><a href="admin.php?page=scc-cache-status"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<div class="sns-cnt-cache">
			<h3 class="nav-tab-wrapper">
				<a class="nav-tab" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
				<a class="nav-tab nav-tab-active" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
			  	<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
				<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
			  	<?php } ?>
				<a class="nav-tab" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			</h3>			  
			<div class="metabox-holder">
				<div id="share-site-summary" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php _e( 'Cache Status', self::DOMAIN ); ?></span></h3>  	
					<div class="inside">								  
					  	<table class="view-table">
							<thead>
			  					<tr>
									<th>No.</th>
									<th><?php _e( 'Content', self::DOMAIN ); ?></th>
									<th><?php _e( 'Primary Cache', self::DOMAIN ); ?></th>
							  		<th><?php _e( 'Secondary Cache', self::DOMAIN ); ?></th>
							  		<th><?php _e( 'Crawl Date', self::DOMAIN ); ?></th>
							  		<th></th>
			  					</tr>
							</thead>
							<tbody>

							<?php
	  
	  						$count = ( $paged - 1 ) * $posts_per_page + 1;

							$share_base_cache_target = $this->share_base_cache_target ;
							unset( $share_base_cache_target[self::REF_CRAWL_DATE] );

							if ( $paged === 1 ) {
							?>
			  					<tr class="home">
									<td><?php echo '-'; ?></td>
								  	<td><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/' ) ); ?></a></td>							  
														  
								<?php

									$share_base_cache_target = $this->share_base_cache_target ;
									unset( $share_base_cache_target[self::REF_CRAWL_DATE] );

							  		$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( 'home' );
									
							  		if ( false !== ( $sns_counts = get_transient( $transient_id ) ) ) {
								  		$full_cache_flag = true;
								  		$partial_cache_flag = false;	
								  
								  		foreach ( $share_base_cache_target as $sns => $active ) {
									  		if ( $active ) {
									  			if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
										  			$partial_cache_flag = true;
												} else {
										  			$full_cache_flag = false;
												}
											}
								  		}
								  
										if ( $partial_cache_flag && $full_cache_flag ) {
					  						echo '<td class="full-cache">';
											_e( 'full cache', self::DOMAIN );
					  						echo '</td>';
										} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
					  						echo '<td class="partial-cache">';
											_e( 'partial cache', self::DOMAIN );
					  						echo '</td>';								  
										} else {
					  						echo '<td class="no-cache">';
											_e( 'no cache', self::DOMAIN );
					  						echo '</td>';
										}				
									} else {
									  	Common_Util::log( '[' . __METHOD__ . '] : no transient' );
					  					echo '<td class="no-cache">';
										_e( 'no cache', self::DOMAIN );
					  					echo '</td>';
									}
							  	  
							  		$full_cache_flag = true;
							  		$partial_cache_flag = false;
							  
							  		$option_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( 'home' );
							  		
							  		if ( false !== ( $sns_counts = get_option( $option_key ) ) ) {	
									  	foreach ( $share_base_cache_target as $sns => $active ) {
											if ( $active ) {
											  	if ( $sns_counts[$sns] >= 0 ){
												  	$partial_cache_flag  = true;
												} else {
												  	$full_cache_flag = false;
												}
											}
										}
									  
									} else {
									  	$full_cache_flag = false;
									}
							  	
									if ( $partial_cache_flag && $full_cache_flag ) {
					  					echo '<td class="full-cache">';
										_e( 'full cache', self::DOMAIN );
					  					echo '</td>';
									} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
					  					echo '<td class="partial-cache">';
										_e( 'partial cache', self::DOMAIN );
					  					echo '</td>';								  
									} else {
					  					echo '<td class="no-cache">';
										_e( 'no cache', self::DOMAIN );
					  					echo '</td>';
									}
								
							 		if ( isset( $sns_counts[self::REF_CRAWL_DATE] ) && $sns_counts[self::REF_CRAWL_DATE] && $sns_counts[self::REF_CRAWL_DATE] !== -1 ) {
					  					echo '<td class="full-cache">';
										echo  esc_html( $sns_counts[self::REF_CRAWL_DATE] );
					  					echo '</td>';
									} else {
					  					echo '<td class="no-cache">';
										_e( 'no data', self::DOMAIN );
					  					echo '</td>';										  	
								 	}
							  
							  	 	$nonce = wp_create_nonce( __FILE__ );
							  	 	$cache_url = esc_url( 'admin.php?page=scc-cache-status&action=cache&post_id=' . 'home' . '&_wpnonce=' . $nonce . '&paged=' . $paged );			
							?>
									<td><a class="button" href="<?php echo $cache_url ?>">Cache</a></td>
			  					</tr>								  
							<?php
							}
							  
							if ( $posts_query->have_posts() ) {
								while ( $posts_query->have_posts() ) {
									$posts_query->the_post();			  
							?>
			  					<tr>
									<td><?php echo $count; ?></td>
							  		<td><a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" target="_blank"><?php echo esc_html( get_permalink( get_the_ID() ) ); ?></a></td>
								<?php  					
								  	$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( get_the_ID() );
							  							  
									if ( false === ( $sns_counts = get_transient( $transient_id ) ) ) {								  
					  					echo '<td class="no-cache">';
										_e( 'no cache', self::DOMAIN );
					  					echo '</td>';
									} else {
								  		$full_cache_flag = true;
								  		$partial_cache_flag = false;	
								  
								  		foreach ( $share_base_cache_target  as $sns => $active ) {
									  		if ( $active ) {
									  			if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
										  			$partial_cache_flag = true;
												} else {
										  			$full_cache_flag = false;
												}
											}
								  		}
								  
										if ( $partial_cache_flag && $full_cache_flag ) {
					  						echo '<td class="full-cache">';
											_e( 'full cache', self::DOMAIN );
					  						echo '</td>';
										} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
					  						echo '<td class="partial-cache">';
											_e( 'partial cache', self::DOMAIN );
					  						echo '</td>';								  
										} else {
					  						echo '<td class="no-cache">';
											_e( 'no cache', self::DOMAIN );
					  						echo '</td>';
										}				
									}
							  
							  		$full_cache_flag = true;
							  		$partial_cache_flag = false;
							  
							  		foreach ( $share_base_cache_target as $sns => $active ) {
										if ( $active ) {								
							  
										  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
										  
							  				$sns_count = get_post_meta( get_the_ID(), $meta_key, true );
									 	
									  		if ( isset( $sns_count ) && $sns_count !== '' && $sns_count >= 0 ) {
										  		$partial_cache_flag  = true;
											} else {
										  		$full_cache_flag = false;
											}
										}
									}
							  
									if ( $partial_cache_flag && $full_cache_flag ) {
					  					echo '<td class="full-cache">';
										_e( 'full cache', self::DOMAIN );
					  					echo '</td>';
									} elseif ( $partial_cache_flag && ! $full_cache_flag ) {
					  					echo '<td class="partial-cache">';
										_e( 'partial cache', self::DOMAIN );
					  					echo '</td>';								  
									} else {
					  					echo '<td class="no-cache">';
										_e( 'no cache', self::DOMAIN );
					  					echo '</td>';
									}
								
							 		if ( isset( $sns_counts[self::REF_CRAWL_DATE] ) ) {
					  					echo '<td class="full-cache">';
										echo  esc_html( $sns_counts[self::REF_CRAWL_DATE] );
					  					echo '</td>';
									} else {
									  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( self::REF_CRAWL_DATE );
									  
							  			$crawl_date = get_post_meta( get_the_ID(), $meta_key, true );

							 			if ( isset( $crawl_date ) && $crawl_date !== '' && $crawl_date !== '-1' ) {
					  						echo '<td class="full-cache">';
											echo esc_html( $crawl_date );
					  						echo '</td>';
										} else {
					  						echo '<td class="no-cache">';
											_e( 'no data', self::DOMAIN );
					  						echo '</td>';
								 		}
								 	}
							  
							  	 	$nonce = wp_create_nonce( __FILE__ );
							  	 	$cache_url = esc_url( 'admin.php?page=scc-cache-status&action=cache&post_id=' . get_the_ID() . '&_wpnonce=' . $nonce . '&paged=' . $paged );			
							?>
									<td><a class="button" href="<?php echo $cache_url ?>"><?php _e( 'Cache', self::DOMAIN ); ?></a></td>
			  					</tr>

							<?php
									++$count;

								}
							}
						?>
							</tbody>
		  				</table>
							<?php
							$this->pagination( $posts_query->max_num_pages, '', $paged, false );
						  
							wp_reset_postdata();
							?>
					</div>								  								  
				</div>
			</div>				  
		</div>
	</div>
