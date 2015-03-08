<?php
/*
admin-dashboard.php

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

	$query_args = array(
		'post_type' => $this->share_base_cache_post_types,
		'post_status' => 'publish',
		'nopaging' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
		);

	$site_query = new WP_Query( $query_args );

	?>
	<div class="wrap">
		<h2><a href="admin.php?page=scc-dashboard"><?php _e( 'SNS Count Cache', self::DOMAIN ) ?></a></h2>
		<div class="sns-cnt-cache">
			  		<h3 class="nav-tab-wrapper">
					  	<a class="nav-tab nav-tab-active" href="admin.php?page=scc-dashboard">Dashboard</a>
					  	<a class="nav-tab" href="admin.php?page=scc-cache-status">Cache Status</a>
					  	<a class="nav-tab" href="admin.php?page=scc-share-count">Share Count</a>
					  	<a class="nav-tab" href="admin.php?page=scc-setting">Setting</a>
					  	<a class="nav-tab" href="admin.php?page=scc-help">Help</a>
			  		</h3>
					<div class="metabox-holder">
						<div id="current-parameter" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Current Setting', self::DOMAIN ) ?></span></h3>  	
							<div class="inside">
							  	<p><?php _e( 'The following describes registered parameters.', self::DOMAIN ) ?></p>
								<table class="view-table">				  
									<thead>
			  							<tr>
						  					<th><?php _e( 'Function', self::DOMAIN ) ?></th>
											<th><?php _e( 'Parameter', self::DOMAIN ) ?></th>
											<th><?php _e( 'Value', self::DOMAIN ) ?></th>
			  							</tr>
									</thead>
									<tbody>
			  						<tr>
						 				<td><?php _e( 'Share Base Cache', self::DOMAIN) ?></td><td><?php _e('Target SNS', self::DOMAIN ) ?></td>
						  				<td>
							  				<?php
					  							$target_sns = array();
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) {
									  				$target_sns[] = 'Twitter';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) {
									  				$target_sns[] = 'Facebook';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) && $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) {
									  				$target_sns[] = 'Google+';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) && $this->share_base_cache_target[self::REF_SHARE_POCKET] ) {
									  				$target_sns[] = 'Pocket';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) && $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) {
									  				$target_sns[] = 'Hatena Bookmark';
												}
							  					echo implode( ", ", $target_sns );
							  				?>
						  				</td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Additional custom post types', self::DOMAIN ) ?></td>
						  				<td><?php								  
											  	if ( ! empty( $this->share_base_custom_post_types ) && $this->share_base_custom_post_types ) {
												  	echo implode( ',', $this->share_base_custom_post_types );
												} else {
											  		_e( 'N/A', self::DOMAIN );
												}
										  	?>
									  	</td>
			  						</tr>									  
			  						<tr>
						 				<td><?php _e( 'Share Base Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Interval cheking and caching share count', self::DOMAIN ) ?></td>
						  				<td><?php echo $this->share_base_check_interval . ' seconds'; ?></td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Number of posts to check at a time', self::DOMAIN ) ?></td>
						  				<td><?php echo $this->share_base_posts_per_check . ' posts'; ?></td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Migration mode from http to https', self::DOMAIN ) ?></td>
						  				<td>
										  	<?php 
											  if ( $this->scheme_migration_mode ) {
													echo 'On'; 
											  } else {
													echo 'Off';
											  }
										  	?>
									  	</td>
			  						</tr>									  
			  						<tr>
						  				<td><?php _e( 'Share Rush Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Term considering posted content as new content', self::DOMAIN ) ?></td>
						  				<td>
							  				<?php 
								  			if ( $this->share_rush_new_content_term == 1 ) {
								  				echo $this->share_rush_new_content_term . ' day';
								  			} else if ( $this->share_rush_new_content_term > 1 ) {
												echo $this->share_rush_new_content_term . ' days';
								  			}
							  				?>
						  				</td>
			  						</tr>
			  						<tr>
						 				<td><?php _e( 'Follow Base Cache', self::DOMAIN) ?></td><td><?php _e('Target SNS', self::DOMAIN ) ?></td>
						  				<td>
							  				<?php
					  							$target_sns = array();
					  							if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) {
									  				$target_sns[] = 'Feedly';
												}
							  					echo implode( ", ", $target_sns );
							  				?>
						  				</td>
			  						</tr>	
			  						<tr>
						 				<td><?php _e( 'Follow Base Cache', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Interval cheking and caching follower count', self::DOMAIN ) ?></td>
						  				<td><?php echo $this->follow_base_check_interval . ' seconds'; ?></td>
			  						</tr>						  
			  						<tr>
										<td><?php _e( 'Dynamic Cache', self::DOMAIN) ?></td><td><?php _e( 'Dynamic caching based on user access', self::DOMAIN ) ?></td><td>
						  				<?php
					  						switch ( $this->dynamic_cache_mode ) {
		  										case self::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
		  											_e( 'disabled', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
		  											_e( 'enabled (Synchronous Cache)', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
		  											_e( 'enabled (Asynchronous Cache)', self::DOMAIN );
											  		break;
								  				case self::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
													_e( 'enabled (Asynchronous 2nd Cache)', self::DOMAIN );								  
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>									  
			  						<tr>
										<td><?php _e( 'Data Export', self::DOMAIN) ?></td><td><?php _e( 'Method of data export', self::DOMAIN ) ?></td><td>
						  				<?php
					  						switch ( $this->data_export_mode ) {
		  										case self::OPT_COMMON_DATA_EXPORT_MANUAL:
		  											_e( 'Manual', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_DATA_EXPORT_AUTO:
		  											_e( 'Auto', self::DOMAIN );
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>
									<?php
										if ( $this->data_export_mode == self::OPT_COMMON_DATA_EXPORT_AUTO ) {
									?>
			  						<tr>
						 				<td><?php _e( 'Data Export', self::DOMAIN ) ?></td>
						  				<td><?php _e( 'Interval exporting share count to a csv file', self::DOMAIN ) ?></td>
									  	<td><?php echo $this->data_export_interval / 3600 . ' hours'; ?></td>
			  						</tr>
									<?php
										}
									?>
									</tbody>
		  						</table>
						  	</div>								  								  
						 </div>
			  		</div>
			  
					<div class="metabox-holder">
						<div id="share-site-summary" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Cache Status', self::DOMAIN ) ?></span></h3>  	
							<div class="inside">			  
								<table class="view-table">
									<thead>
										<tr>
											<th><?php _e( 'Cache Type', self::DOMAIN ) ?></th>
											<th><?php _e( 'Status Overview', self::DOMAIN ) ?></th>
											<th><?php _e( 'Total Content', self::DOMAIN ) ?></th>
											<th><?php _e( 'State - Full Cache', self::DOMAIN ) ?></th>
											<th><?php _e( 'State - Partial Cache', self::DOMAIN ) ?></th>
											<th><?php _e( 'State - No Cache', self::DOMAIN ) ?></th>
										</tr>
									</thead>
									<tbody>
	
									<?php
												  
									$share_base_cache_target = $this->share_base_cache_target ;
									unset( $share_base_cache_target[self::REF_CRAWL_DATE] );
			
									$count = 1;
									  
									$posts_count = 0;
									$primary_full_cache_count = 0;
									$primary_partial_cache_count = 0;
									$primary_no_cache_count = 0;
									
									$secondary_full_cache_count = 0;
									$secondary_partial_cache_count = 0;
									$secondary_no_cache_count = 0;
				  
									set_time_limit( $this->extended_max_execution_time  );
			
									if ( $site_query->have_posts() ) {
										while ( $site_query->have_posts() ) {
											$site_query->the_post();
										  
											$posts_count++;
									  
											$transient_id = self::OPT_SHARE_BASE_TRANSIENT_PREFIX . get_the_ID();
																	  
											if ( false === ( $sns_counts = get_transient( $transient_id ) ) ) {								  
												$primary_no_cache_count++;
											} else {
												$full_cache_flag = true;
												$partial_cache_flag = false;	
											  
												foreach ( $share_base_cache_target as $key => $value ) {
													if ( $value ) {
														if ( isset( $sns_counts[$key] ) && $sns_counts[$key] >= 0 ) {
															$partial_cache_flag = true;
														} else {
															$full_cache_flag = false;
														}
													}
												}
											  
												if ( $partial_cache_flag && $full_cache_flag ) {
													$primary_full_cache_count++;
												} else if ( $partial_cache_flag && ! $full_cache_flag ) {
													$primary_partial_cache_count++;
												} else {
													$primary_no_cache_count++;
												}				
											}
										  
											$full_cache_flag = true;
											$partial_cache_flag = false;
											foreach ( $share_base_cache_target as $key => $value ) {
												if ( $value ) {								
										  
													$meta_key = self::OPT_SHARE_2ND_META_KEY_PREFIX . strtolower( $key );
													$sns_count = get_post_meta( get_the_ID(), $meta_key, true );
													
													if ( isset( $sns_count ) && $sns_count >= 0  ) {
														$partial_cache_flag  = true;
													} else {
														$full_cache_flag = false;
													}
												  
												}
											}
											if ( $partial_cache_flag && $full_cache_flag ) {
												$secondary_full_cache_count++;
											} else if ( $partial_cache_flag && ! $full_cache_flag ) {
												$secondary_partial_cache_count++;								  
											} else {
												$secondary_no_cache_count++;
											}										  
										}
										wp_reset_postdata();
									}
							
									set_time_limit( $this->original_max_execution_time  );
			
									?>
							  
										<tr>
											<td><?php _e( 'Primary Cache', self::DOMAIN ); ?></td>
											<td>
												<?php
													if ( $primary_full_cache_count == $posts_count ) {
														_e( 'Completed', self::DOMAIN );
													} else if ( ( $primary_full_cache_count + $primary_partial_cache_count ) == $posts_count ) {
														_e( 'Partially Completed', self::DOMAIN );  	
													} else {
														_e( 'Ongoing', self::DOMAIN );
													}
												?>
											</td>
											<td class="share-count"><?php echo $posts_count; ?></td>
											<td class="share-count full-cache"><?php echo $primary_full_cache_count ; ?></td>
											<td class="share-count partial-cache"><?php echo $primary_partial_cache_count ; ?></td>
											<td class="share-count no-cache"><?php echo $primary_no_cache_count; ?></td>							  	
										</tr>
										<tr>
											<td><?php _e( 'Secondary Cache', self::DOMAIN ); ?></td>
											<td>
												<?php
													if ( $secondary_full_cache_count == $posts_count ) {
														_e( 'Completed', self::DOMAIN );
													} else if ( ( $secondary_full_cache_count + $secondary_partial_cache_count ) == $posts_count ) {
														_e( 'Partially Completed', self::DOMAIN );  	
													} else {
														_e( 'Ongoing', self::DOMAIN );
													}
												?>
											</td>							  
											<td class="share-count"><?php echo $posts_count; ?></td>
											<td class="share-count full-cache"><?php echo $secondary_full_cache_count; ?></td>
											<td class="share-count partial-cache"><?php echo $secondary_partial_cache_count; ?></td>
											<td class="share-count no-cache"><?php echo $secondary_no_cache_count; ?></td>							  	
										</tr>
									</tbody>
								</table>
						  	</div>								  								  
						 </div>
			  		</div>				  
		  
					<div class="metabox-holder">
						<div id="share-site-summary" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Share Count', self::DOMAIN ) ?></span></h3>  	
							<div class="inside">
								<table class="view-table">
									<thead>
										<tr>
											<?php
																		
												foreach ( $share_base_cache_target as $key => $value ){	
													if ( $value ) {
														echo '<th>' . $key . '</th>';	
													}
												}
											?>
										</tr>
									</thead>
									<tbody>
										<tr>
									<?php
			
									$sum = array();
									foreach ( $share_base_cache_target as $key => $value ) {
										if( $value ){
											$sum[$key] = 0;
										}
									}
			
									set_time_limit( $this->extended_max_execution_time  );
			
									if ( $site_query->have_posts() ) {
										while ( $site_query->have_posts() ) {
											$site_query->the_post();			  
			
											$transient_id = self::OPT_SHARE_BASE_TRANSIENT_PREFIX . get_the_ID();
																	  
											if ( false === ( $sns_counts = get_transient( $transient_id ) ) ) {
									  
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
															 $sum[$key] = $sum[$key] + $sns_counts[$key];	
														}								  								 										  
													}
												}
											  
											} else {
				  
												foreach ( $share_base_cache_target as $key => $value ) {
													if ( $value ) {										  
														if ( isset( $sns_counts[$key] ) && $sns_counts[$key] >= 0 ) {
															$sum[$key] = $sum[$key] + $sns_counts[$key];
														} 								  
													}
												}
																			  
											}
										}
									}
			
									foreach ( $share_base_cache_target as $key => $value ) {
										if ( $value ) {
											echo '<td class="share-count">';
											echo number_format( (int) $sum[$key] );
											echo '</td>';							  	
										}
									}
			
									wp_reset_postdata();
			
									set_time_limit( $this->original_max_execution_time  );
			
									?>
										</tr>
									</tbody>
								</table>
						  	</div>								  								  
						 </div>
			  		</div>		  		  
		</div>
     </div>
