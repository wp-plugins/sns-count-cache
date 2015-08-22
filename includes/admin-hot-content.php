<?php
/*
admin-hot-content.php

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

	$sort_mode = false;
	$sort_exec_key = '';

	if( isset( $_GET["action"] ) && $_GET["action"] === 'sort' ) {
	  			  
		if ( current_user_can( self::OPT_COMMON_CAPABILITY ) ) {	  
			if( isset( $_GET["key"] ) ) {
				$sort_mode = true;
				$sns = $_GET["key"];
				    										  	
				if ( $sns === 'Google' ) {
					$sns =  $sns . '+';
				}
				  
				$sort_exec_key = $sns;
			
		  		$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );
			  	$meta_key2 = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );	 
			}
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
		<h2><a href="admin.php?page=scc-hot-content"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<div class="sns-cnt-cache">
			<h3 class="nav-tab-wrapper">
				<a class="nav-tab" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
				<a class="nav-tab nav-tab-active" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
				<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			</h3>
			<div class="metabox-holder">
				<div id="share-each-content" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php _e( 'Hot Content', self::DOMAIN ); ?></span></h3>  	
					<div class="inside">		  
		  				<table class="view-table">
							<thead>
			  					<tr>
									<th>No.</th>
									<th><?php _e( 'Content', self::DOMAIN ); ?></th>
							  		<?php
						  						  			
						  				foreach ( $share_base_cache_target as $sns => $active ) {	
									  
									  		if ( $active ) {
										  
										  		$sort_key = $sns;
										  	
										  		if ( $sort_key === self::REF_SHARE_GPLUS ) {
											  		$sort_key =  str_replace( '+', '', $sort_key );
										  		}
										  	
										  		$sort_url = esc_url( 'admin.php?page=scc-hot-content&action=sort&key=' . $sort_key );
											  
										  		$sns_name = '';
											  
										  		switch ( $sns ) {
										  			case self::REF_SHARE_TWITTER:
											  			$sns_name = __( 'Twitter', self::DOMAIN );
										  				break;
										  			case self::REF_SHARE_FACEBOOK:
											  			$sns_name = __( 'Facebook', self::DOMAIN );
										  				break;
										  			case self::REF_SHARE_GPLUS:
											  			$sns_name = __( 'Google+', self::DOMAIN );
										  				break;
										  			case self::REF_SHARE_POCKET:
											  			$sns_name = __( 'Pocket', self::DOMAIN );
										  				break;
										  			case self::REF_SHARE_HATEBU:
											  			$sns_name = __( 'Hatebu', self::DOMAIN );
										  				break;
										  			case self::REF_SHARE_TOTAL:
											  			$sns_name = __( 'Total', self::DOMAIN );
										  				break;
												}											  
										  
										  		if ( $sns === $sort_exec_key ){
													echo '<th><a class="sort-exec-key" href="' . $sort_url . '">' . esc_html( $sns_name ) . '</th>';		
												} else {
											  		echo '<th><a href="' . $sort_url . '">' . esc_html( $sns_name ) . '</th>';	
												}
									  		}
										}
					  				?>
			  					</tr>
							</thead>
							<tbody>

							<?php

								$meta_query = array();

								if( $sort_mode ) {
								  								  
								  	if ( version_compare( get_bloginfo('version'), '4.2', '>=') ) {
									  
									  
								  		$meta_query['relation'] = 'AND';			
								  
										$meta_query[0]['relation'] = 'OR';
								  
										foreach ( $share_base_cache_target as $sns => $active ) {
								  			if ( $active ) {
									  			$meta_query[0][] = array(
										  			'key' => 'scc_share_delta_' . strtolower( $sns ),
										  			'value' => 0,
										  			'compare'=>'>',
										  			'type'=>'NUMERIC'										  	
										  		);  
								  			}										  
										}								  
								  
										$meta_query['meta_primary'] = array(
											'key' => $meta_key,
											'type'=>'NUMERIC'
										);
								  
										$meta_query['meta_secondary'] = array(
											'key' => $meta_key2,
											'type'=>'NUMERIC'
										);	
								  
								  		Common_Util::log( $meta_query );
								  
								  		Common_Util::log( 'version: ' . get_bloginfo( 'version') );
								  
										$query_args = array(
											'post_type' => $this->share_base_cache_post_types,
											'post_status' => 'publish',
		  									'posts_per_page' => $posts_per_page,
      										'paged' => $paged,
											'update_post_term_cache' => false,
									  		'meta_query' => $meta_query,
									  		'orderby' => array(
										  		'meta_primary' => 'DESC',
										  		'meta_secondary' => 'DESC'
										  	)
										);								  											  
								  									  
									} else {
									  								  
										foreach ( $share_base_cache_target as $sns => $active ) {
								  			if ( $active ) {
									  			$meta_query[] = array(
										  			'key' => 'scc_share_delta_' . strtolower( $sns ),
										  			'value' => 0,
										  			'compare'=>'>',
										  			'type'=>'NUMERIC'										  	
										  		);
										  
								  			}
										}

										$meta_query['relation'] = 'OR';
									  
										$query_args = array(
											'post_type' => $this->share_base_cache_post_types,
											'post_status' => 'publish',
		  									'posts_per_page' => $posts_per_page,
      										'paged' => $paged,
											'meta_key' => $meta_key,
 											'orderby'  => 'meta_value_num',
											'update_post_term_cache' => false,
											'order' => 'DESC',
									  		'meta_query' => $meta_query						  
										);				  	
									}
								  
								} else {

									foreach ( $share_base_cache_target as $sns => $active ) {
								  		if ( $active ) {
									  		$meta_query[] = array(
										  		'key' => 'scc_share_delta_' . strtolower( $sns ),
										  		'value' => 0,
										  		'compare'=>'>',
										  		'type'=>'NUMERIC'										  	
										  	);
										  
								  		}
									}

									$meta_query['relation'] = 'OR';
								  
									$query_args = array(
										'post_type' => $this->share_base_cache_post_types,
										'post_status' => 'publish',
		  								'posts_per_page' => $posts_per_page,
      									'paged' => $paged,
									  	'meta_query' => $meta_query,
										'update_post_term_cache' => false
										);															  
								}

								$posts_query = new WP_Query( $query_args );

								$count = ( $paged - 1 ) * $posts_per_page + 1;

								if ( $posts_query->have_posts() ) {
									while ( $posts_query->have_posts() ) {
										$posts_query->the_post();			  
								?>
			  					<tr>
									<td><?php echo $count; ?></td>
							  		<td><a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( get_the_ID() ) ); ?></a></td>
									<?php  
									  	$transient_id = $this->cache_engines[self::REF_SHARE_BASE]->get_cache_key( get_the_ID() );
							  			
									  	if( ! $sort_mode && false !== ( $sns_counts = get_transient( $transient_id ) ) ) {

						  					foreach ( $share_base_cache_target as $sns => $active ) {
									  			if ( $active ) {
												  
												  	//delta
								  					$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );
													$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );
												  												  
								  					if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] >= 0 ) {
					  									echo '<td class="share-count">';
								  						echo esc_html( number_format( (int) $sns_counts[$sns] ) );
													  
												  		if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' && $sns_deltas[$sns] > 0 ) {
															echo ' (<span class="delta-rise">+' . esc_html( number_format( (int) $sns_deltas[$sns] ) ) . '</span>)';
														} elseif ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' && $sns_deltas[$sns] < 0 ) {
															echo ' (<span class="delta-fall">' . esc_html( number_format( (int) $sns_deltas[$sns] ) ) . '</span>)';
														}
													  
					  									echo '</td>';
													} else {
					  									echo '<td class="not-cached share-count">';
														_e( 'N/A', self::DOMAIN );
					  									echo '</td>';
													}										  
									  			}
											}										
										
										} else {

						  					foreach ( $share_base_cache_target as $sns => $active ) {
									  			if( $active ){
										  
												  	$meta_key = $this->cache_engines[self::REF_SHARE_2ND]->get_cache_key( $sns );
												  	
							  						$sns_counts[$sns] = get_post_meta( get_the_ID(), $meta_key, true );

												  	//delta
								  					$meta_key = $this->analytical_engines[self::REF_SHARE_ANALYSIS]->get_delta_key( $sns );
													$sns_deltas[$sns] = get_post_meta( get_the_ID(), $meta_key, true );												  
												  
													if ( isset( $sns_counts[$sns] ) && $sns_counts[$sns] !== '' &&  $sns_counts[$sns] >= 0 ) {
					  									echo '<td class="share-count">';
								  						echo esc_html( number_format( (int) $sns_counts[$sns] ) );
													  
												  		if ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' && $sns_deltas[$sns] > 0 ) {
															echo ' (<span class="delta-rise">+' . esc_html( number_format( (int) $sns_deltas[$sns] ) ) . '</span>)';
														} elseif ( isset( $sns_deltas[$sns] ) && $sns_deltas[$sns] !== '' && $sns_deltas[$sns] < 0 ) {
															echo ' (<span class="delta-fall">' . esc_html( number_format( (int) $sns_deltas[$sns] ) ) . '</span>)';
														}													  
													  
					  									echo '</td>';
													} else {
					  									echo '<td class="not-cached share-count">';
														_e( 'N/A', self::DOMAIN );
					  									echo '</td>';
													}										  								 										  
									  			}
											}

										}
									  
									?>
			  					</tr>
								<?php
										++$count;

									}
								} else {
								  	echo '<tr>';
								  	echo '<td>' . $count . '</td>';
								  	echo '<td>' . __( 'No hot content.', self::DOMAIN ) . '</td>';
								  	foreach ( $share_base_cache_target as $sns => $active ) {
									  	if ( $active ) {
					  						echo '<td class="not-cached share-count">';
											_e( 'N/A', self::DOMAIN );
					  						echo '</td>';										  	
										}
									}
								  	echo '</tr>';
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
