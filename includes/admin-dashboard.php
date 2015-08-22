<?php
/*
admin-dashboard.php

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
		<h2><a href="admin.php?page=scc-dashboard"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<div class="sns-cnt-cache">
		  	<div id="scc-dashboard">
			  		<h3 class="nav-tab-wrapper">
					  	<a class="nav-tab nav-tab-active" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
					  	<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
					  	<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
					  	<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
					  	<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
					  	<?php } ?>
					  	<a class="nav-tab" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
					  	<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			  		</h3>
			  
					<div class="metabox-holder">
						<div id="site-summary-cache" class="site-summary postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Cache Status', self::DOMAIN ); ?></span></h3>  	
							<div class="inside">			  
								<table class="view-table">
									<thead>
										<tr>
											<th><?php _e( 'Cache Type', self::DOMAIN ); ?></th>
											<th><?php _e( 'Cache Progress', self::DOMAIN ); ?></th>
											<th><?php _e( 'Total Content', self::DOMAIN ); ?></th>
											<th><?php _e( 'State - Full Cache', self::DOMAIN ); ?></th>
											<th><?php _e( 'State - Partial Cache', self::DOMAIN ); ?></th>
											<th><?php _e( 'State - No Cache', self::DOMAIN ); ?></th>
										</tr>
									</thead>
									<tbody>							  
										<tr>
											<td><?php _e( 'Primary Cache', self::DOMAIN ); ?></td>
											<td>
											  	<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="pcs"></span>
											</td>
										  	<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='pc'></span></td>
											<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='pfcc'></span></td>
											<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='ppcc'></span></td>
											<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='pncc'></span></td>							  	
										</tr>
										<tr>
											<td><?php _e( 'Secondary Cache', self::DOMAIN ); ?></td>
											<td>
												<img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc="scs"></span>
											</td>							  
										  	<td class="share-count"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='pc'></span></td>
											<td class="share-count full-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='sfcc'></span></td>
											<td class="share-count partial-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='spcc'></span></td>
											<td class="share-count no-cache"><img class="loading" src="<?php echo $this->loading_img_url; ?>" /><span data-scc='sncc'></span></td>							  	
										</tr>
									</tbody>
								</table>
						  	</div>								  								  
						 </div>
			  		</div>				  
		  
					<div class="metabox-holder">
						<div id="site-summary-count" class="site-summary postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Share Count', self::DOMAIN ); ?></span></h3>  	
							<div class="inside">
								<table class="view-table">
									<thead>
										<tr>
											<?php
									  			$share_base_cache_target = $this->share_base_cache_target ;
												unset( $share_base_cache_target[self::REF_CRAWL_DATE] );
																		
												foreach ( $share_base_cache_target as $sns => $active ){	
													if ( $active ) {
													  
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
										  	
										  				echo '<th>' . esc_html( $sns_name ) . '</th>';
													}
												}
											?>
										</tr>
									</thead>
									<tbody>
										<tr>
									<?php
			
									foreach ( $share_base_cache_target as $sns => $active ) {
										if ( $active ) {
										  	if ( $sns === self::REF_SHARE_GPLUS ){
												echo '<td class="share-count">';
											  	echo '<img class="loading" src="' . $this->loading_img_url . '" /><span data-scc="gplus"></span>';
												echo '</td>';													  	
											} else {
												echo '<td class="share-count">';
											  echo '<img class="loading" src="' . $this->loading_img_url . '" /><span data-scc="' . strtolower( $sns ) . '"></span>';
												echo '</td>';													  
											}
					  	
										}
									}

									?>
										</tr>
									</tbody>
								</table>
						  	</div>								  								  
						 </div>
			  		</div>
		  	</div>
		</div>
     </div>
