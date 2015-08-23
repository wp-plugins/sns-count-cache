<?php
/*
admin-setting.php

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

	if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] ) {
		if( check_admin_referer( __FILE__, '_wpnonce' ) ) {
		  
		 	 if( isset( $_POST["update_all_options"] ) && $_POST["update_all_options"] === __( 'Update All Options', self::DOMAIN ) ) {
			 
			  	$wp_error = new WP_Error();
			   
			   	$settings = array();
			  
			  	$share_base_cache_target = array();
		  		$follow_base_cache_target = array();	
			  
			  	if ( isset( $_POST["share_base_custom_post_types"] ) && $_POST["share_base_custom_post_types"] ) { 
				  	$share_base_custom_post_types = explode( ',', $_POST["share_base_custom_post_types"] );
				  	$settings[self::DB_SHARE_CUSTOM_POST_TYPES] = $share_base_custom_post_types;
				} else {
				  	$settings[self::DB_SHARE_CUSTOM_POST_TYPES] = array();
				}

				if ( isset( $_POST["share_base_check_interval"] ) && $_POST["share_base_check_interval"] && is_numeric( $_POST["share_base_check_interval"] ) ) {
				  	$settings[self::DB_SHARE_BASE_CHECK_INTERVAL] = $_POST["share_base_check_interval"];
				}
			  
				if ( isset( $_POST["share_base_posts_per_check"] ) && $_POST["share_base_posts_per_check"] && is_numeric( $_POST["share_base_posts_per_check"] ) ) {
				  	$settings[self::DB_SHARE_POSTS_PER_CHECK] = $_POST["share_base_posts_per_check"];
				}
			  
				if ( isset( $_POST["dynamic_cache_mode"] ) && $_POST["dynamic_cache_mode"] ) {
				  	$settings[self::DB_COMMON_DYNAMIC_CACHE_MODE] = $_POST["dynamic_cache_mode"];
				}
			   			   
				if ( isset( $_POST["share_variation_analysis_mode"] ) && $_POST["share_variation_analysis_mode"] ) {
				  	$settings[self::DB_SHARE_VARIATION_ANALYSIS_MODE] = $_POST["share_variation_analysis_mode"];
				}			   
			  			   
			   	if ( isset( $_POST["share_rush_new_content_term"] ) && $_POST["share_rush_new_content_term"] && is_numeric( $_POST["share_rush_new_content_term"] ) ) {
				  	$settings[self::DB_SHARE_RUSH_NEW_CONTENT_TERM] = $_POST["share_rush_new_content_term"];
				}

				if ( isset( $_POST["share_rush_check_interval"] ) && $_POST["share_rush_check_interval"] && is_numeric( $_POST["share_rush_check_interval"] ) ) {
				  	$settings[self::DB_SHARE_RUSH_CHECK_INTERVAL] = $_POST["share_rush_check_interval"];
				}			   
			   
				if ( isset( $_POST["data_export_mode"] ) && $_POST["data_export_mode"] ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_MODE] = $_POST["data_export_mode"];
				} 			  

				if ( isset( $_POST["data_export_interval"] ) && $_POST["data_export_interval"] && is_numeric( $_POST["data_export_interval"] ) ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_INTERVAL] = $_POST["data_export_interval"];
				}
			  
				if ( isset( $_POST["share_base_cache_target_twitter"] ) && $_POST["share_base_cache_target_twitter"] ) {
					$share_base_cache_target[self::REF_SHARE_TWITTER] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_TWITTER] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_facebook"] ) && $_POST["share_base_cache_target_facebook"] ) {
					$share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_FACEBOOK] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_gplus"] ) && $_POST["share_base_cache_target_gplus"] ) {
					$share_base_cache_target[self::REF_SHARE_GPLUS] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_GPLUS] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_pocket"] ) && $_POST["share_base_cache_target_pocket"] ) {
					$share_base_cache_target[self::REF_SHARE_POCKET] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_POCKET] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_hatebu"] ) && $_POST["share_base_cache_target_hatebu"] ) {
					$share_base_cache_target[self::REF_SHARE_HATEBU] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_HATEBU] = false;
				}
			   
			   /*
				if ( isset( $_POST["share_base_cache_target_pinterest"] ) && $_POST["share_base_cache_target_pinterest"] ) {
					$share_base_cache_target[self::REF_SHARE_PINTEREST] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_PINTEREST] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_linkedin"] ) && $_POST["share_base_cache_target_linkedin"] ) {
					$share_base_cache_target[self::REF_SHARE_LINKEDIN] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_LINKEDIN] = false;
				}
				*/
			   
				if ( ! empty( $share_base_cache_target ) ) {
				  	$settings[self::DB_SHARE_CACHE_TARGET] = $share_base_cache_target;
				}
	  
	  			if ( isset( $_POST["follow_base_check_interval"] ) && $_POST["follow_base_check_interval"] && is_numeric( $_POST["follow_base_check_interval"] ) ) {
				  	if ( $_POST["follow_base_check_interval"] >= self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN ) {
				  		$settings[self::DB_FOLLOW_CHECK_INTERVAL] = $_POST["follow_base_check_interval"];
					} else {
					  	$settings[self::DB_FOLLOW_CHECK_INTERVAL] = self::OPT_FOLLOW_BASE_CHECK_INTERVAL_MIN;
					}
				}
	  			  
				if ( isset( $_POST["follow_base_cache_target_feedly"] ) && $_POST["follow_base_cache_target_feedly"] ) {
					$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
				} else {
				  	$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = false;
				}
	  
				if ( ! empty( $follow_base_cache_target ) ) {
				  	$settings[self::DB_FOLLOW_CACHE_TARGET] = $follow_base_cache_target;
				}
			  			  
				if ( isset( $_POST["follow_feed_type"] ) && $_POST["follow_feed_type"] ) {
				  	switch ( $_POST["follow_feed_type"] ) {
					  	case 'default':
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;
					  		break;
					  	case 'rss2':
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RSS2;
					  		break;
					  	case 'rss':
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RSS;
					  		break;
					  	case 'rdf':
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_RDF;
					  		break;
					  	case 'atom':
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_ATOM;
					  		break;
					  	default:
					  		$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;					  
				  	}
					  
				} else {
					$settings[self::DB_FOLLOW_FEED_TYPE] = self::OPT_FEED_TYPE_DEFAULT;					  
				}
			   
				if ( isset( $_POST["scheme_migration_mode"] ) && $_POST["scheme_migration_mode"] ) {
				  	$settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] = self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON;
				} else {
				  	$settings[self::DB_COMMON_SCHEME_MIGRATION_MODE] = self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF;
				}			  				  

			   	if ( isset( $_POST["scheme_migration_date"] ) && $_POST["scheme_migration_date"] && strptime( $_POST["scheme_migration_date"], '%Y/%m/%d' ) ) {
				  	$settings[self::DB_COMMON_SCHEME_MIGRATION_DATE] = $_POST["scheme_migration_date"];
				}			   
			   			   
				if ( isset( $_POST["crawler_ssl_verification"] ) && $_POST["crawler_ssl_verification"] ) {
				  	$settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] = self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON;
				} else {
				  	$settings[self::DB_COMMON_CRAWLER_SSL_VERIFICATION] = self::OPT_COMMON_CRAWLER_SSL_VERIFY_OFF;
				}

			  	if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'mon' ) {
				  	$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_moncronminutes'] . ' ' . $_POST['a_moncronhours'] . ' ' . $_POST['a_moncronmday'] . ' * *';
				}
			  	if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'week' ) {
				  	$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_weekcronminutes'] . ' ' . $_POST['a_weekcronhours'] . ' * * ' . $_POST['a_weekcronwday'];
				}
			  	if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'day' ) {
				  	$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_daycronminutes'] . ' ' . $_POST['a_daycronhours'] . ' * * *';
				}
			  	if ( isset( $_POST['a_cronbtype'] ) && $_POST['a_cronbtype'] === 'hour' ) {
				  	$settings[self::DB_SHARE_VARIATION_ANALYSIS_SCHEDULE] = $_POST['a_hourcronminutes'] . ' * * * *';		  	
				}
			   
			  	if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'mon' ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_moncronminutes'] . ' ' . $_POST['e_moncronhours'] . ' ' . $_POST['e_moncronmday'] . ' * *';
				}
			  	if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'week' ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_weekcronminutes'] . ' ' . $_POST['e_weekcronhours'] . ' * * ' . $_POST['e_weekcronwday'];
				}
			  	if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'day' ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_daycronminutes'] . ' ' . $_POST['e_daycronhours'] . ' * * *';
				}
			  	if ( isset( $_POST['e_cronbtype'] ) && $_POST['e_cronbtype'] === 'hour' ) {
				  	$settings[self::DB_COMMON_DATA_EXPORT_SCHEDULE] = $_POST['e_hourcronminutes'] . ' * * * *';		  	
				}

			   	update_option( self::DB_SETTINGS, $settings );		
			   
				$this->reactivate_plugin();

			  	set_transient( self::OPT_COMMON_ERROR_MESSAGE, $wp_error->get_error_messages(), 10 );
			  
			   	//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );			  
			}

		  	if( isset( $_POST["reset_data"] ) && $_POST["reset_data"] === __( 'Reset', self::DOMAIN ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] reset' );
			  
	  			$this->export_engines[self::REF_COMMON_EXPORT]->reset_export();
			  
			  	//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
			}
		  
		  	if( isset( $_POST["export_data"] ) && $_POST["export_data"] === __( 'Export', self::DOMAIN ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] export' );
			  
			  	set_time_limit( $this->extended_max_execution_time );
			  
	  			$this->export_engines[self::REF_COMMON_EXPORT]->execute_export( NULL );
			  
			  	set_time_limit( $this->original_max_execution_time );
			  
			  	//wp_safe_redirect( menu_page_url('scc-setting', false ) );
			}

		  	if( isset( $_POST["update_share_comparison_base"] ) && $_POST["update_share_comparison_base"] === __( 'Update Basis of Comparison', self::DOMAIN ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] base' );
			  
			  	set_time_limit( $this->extended_max_execution_time );
			  
			  	$this->analytical_engines[self::REF_SHARE_ANALYSIS]->execute_base( NULL );
			  
			  	set_time_limit( $this->original_max_execution_time );
			  
			  	//wp_safe_redirect( menu_page_url( 'scc-setting', false ) );
			}
		  
		  	if( isset( $_POST["clear_share_base_cache"] ) && $_POST["clear_share_base_cache"] === __( 'Clear Cache', self::DOMAIN ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] clear cache' );
			  			  
			  	set_time_limit( $this->extended_max_execution_time );

	  			$this->cache_engines[self::REF_SHARE_BASE]->clear_cache();			  
			  	//$this->cache_engines[self::REF_SHARE_2ND]->clear_cache();
			  	$this->cache_engines[self::REF_SHARE_2ND]->initialize_cache();
			  	$this->analytical_engines[self::REF_SHARE_ANALYSIS]->clear_base();
			  
			  	set_time_limit( $this->original_max_execution_time );
			  
			  	//wp_safe_redirect( menu_page_url('scc-setting', false ) );
			}

		  	if( isset( $_POST["clear_follow_base_cache"] ) && $_POST["clear_follow_base_cache"] === __( 'Clear Cache', self::DOMAIN ) ) {
			  	Common_Util::log( '[' . __METHOD__ . '] clear cache' );
			  			  
			  	set_time_limit( $this->extended_max_execution_time ); 

	  			$this->cache_engines[self::REF_FOLLOW_BASE]->clear_cache();
			  	//$this->cache_engines[self::REF_FOLLOW_2ND]->clear_cache();			  
			  	$this->cache_engines[self::REF_FOLLOW_2ND]->initialize_cache();
			  
			  	set_time_limit( $this->original_max_execution_time );
			  
			  	//wp_safe_redirect( menu_page_url('scc-setting', false ) );
			}			  
		}  
	}

	?>
	<div class="wrap">
	  	<h2><a href="admin.php?page=scc-setting"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<?php
			if ( $messages = get_transient( self::OPT_COMMON_ERROR_MESSAGE  ) ) {
		?>
		<div class="error">
	  		<ul>
			<?php
	  			foreach( $messages as $message ) {
			?>
			  		<li><?php echo esc_html( $message ); ?></li>
			<?php
				}
	  		?>
	  		</ul>
		</div>
		<?php
	  			delete_option( self::OPT_COMMON_ERROR_MESSAGE );
			}
		?>
			<div class="sns-cnt-cache">
			  	<h3 class="nav-tab-wrapper">
					<a class="nav-tab" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
				  	<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
					<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
				  	<?php } ?>
					<a class="nav-tab nav-tab-active" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ); ?></a>
					<a class="nav-tab" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			  	</h3>
				<p id="options-menu">
                	<a href="#current-parameter"><?php _e( 'Current Setting', self::DOMAIN ); ?></a> | <a href="#share-base-cache"><?php _e( 'Share Base Cache', self::DOMAIN ); ?></a> | <a href="#share-rush-cache"><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></a> | <a href="#share-variation-analysis"><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></a> | <a href="#follow-base-cache"><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></a> | <a href="#common-dynamic-cache"><?php _e( 'Dynamic Cache', self::DOMAIN ); ?></a> | <a href="#common-data-crawler"><?php _e( 'Data Crawler', self::DOMAIN ); ?></a> | <a href="#common-data-export"><?php _e( 'Data Export', self::DOMAIN ); ?></a> | <a href="#common-exported-file"><?php _e( 'Exported File', self::DOMAIN ); ?></a>
			  	</p>
				<div class="metabox-holder">
					<div id="current-parameter" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Current Setting', self::DOMAIN ); ?></span></h3>  	
						<div class="inside">
							<table class="view-table">				  
								<thead>
			  						<tr>
						  				<th><?php _e( 'Capability', self::DOMAIN ); ?></th>
										<th><?php _e( 'Parameter', self::DOMAIN ); ?></th>
										<th><?php _e( 'Value', self::DOMAIN ); ?></th>
			  						</tr>
								</thead>
								<tbody>
			  						<tr>
						 				<td><?php _e( 'Share Base Cache', self::DOMAIN); ?></td>
									  	<td><?php _e( 'Target SNS', self::DOMAIN ); ?></td>
						  				<td>
							  				<?php
					  							$target_sns = array();
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) && $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) {
									  				$target_sns[] = 'Facebook';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) && $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) {
									  				$target_sns[] = 'Google+';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) && $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) {
									  				$target_sns[] = __( 'Hatena Bookmark', self::DOMAIN );
												}
												/*
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) && $this->share_base_cache_target[self::REF_SHARE_LINKEDIN] ) {
									  				$target_sns[] = 'Linkedin';
												}
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) && $this->share_base_cache_target[self::REF_SHARE_PINTEREST] ) {
									  				$target_sns[] = 'Pinterest';
												}
												*/
					  							if ( isset( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) && $this->share_base_cache_target[self::REF_SHARE_POCKET] ) {
									  				$target_sns[] = 'Pocket';
												}
												if ( isset( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) && $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) {
									  				$target_sns[] = 'Twitter';
												}
							  					echo esc_html( implode( ", ", $target_sns ) );
							  				?>
						  				</td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Custom post types', self::DOMAIN ); ?></td>
						  				<td><?php								  
											  	if ( ! empty( $this->share_base_custom_post_types ) && $this->share_base_custom_post_types ) {
												  	echo esc_html( implode( ',', $this->share_base_custom_post_types ) );
												} else {
											  		_e( 'N/A', self::DOMAIN );
												}
										  	?>
									  	</td>
			  						</tr>									  
			  						<tr>
						 				<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></td>
						  				<td><?php echo esc_html( $this->share_base_check_interval ) . ' ' . __( 'seconds', self::DOMAIN ); ?></td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Number of contents to check at a time', self::DOMAIN ) ?></td>
						  				<td><?php echo esc_html( $this->share_base_posts_per_check ) . ' ' . __( 'contents', self::DOMAIN ); ?></td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Scheme migration mode from http to https', self::DOMAIN ); ?></td>
						  				<td>
										  	<?php 
											  if ( $this->scheme_migration_mode ) {
													_e( 'On', self::DOMAIN );
											  } else {
													_e( 'Off', self::DOMAIN );
											  }
										  	?>
									  	</td>
			  						</tr>
								  	<?php if ( $this->scheme_migration_mode ) { ?>
			  						<tr>
						  				<td><?php _e( 'Share Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Scheme migration date from http to https', self::DOMAIN ); ?></td>
						  				<td>
										  	<?php 
											  if ( isset( $this->scheme_migration_date ) ) {
													echo esc_html( $this->scheme_migration_date );
											  } else {
											  		_e( 'N/A', self::DOMAIN );													
											  }
										  	?>
									  	</td>
			  						</tr>
								  <?php } ?>
			  						<tr>
						  				<td><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Term considering posted content as new content', self::DOMAIN ); ?></td>
						  				<td>
							  				<?php 
								  			if ( $this->share_rush_new_content_term == 1 ) {
								  				echo esc_html( $this->share_rush_new_content_term ) . ' ' . __( 'day', self::DOMAIN );
								  			} elseif ( $this->share_rush_new_content_term > 1 ) {
												echo esc_html( $this->share_rush_new_content_term ) . ' ' . __( 'days', self::DOMAIN );
								  			}
							  				?>
						  				</td>
			  						</tr>
			  						<tr>
						  				<td><?php _e( 'Share Rush Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></td>
						  				<td>
							  				<?php
											  	echo esc_html( $this->share_rush_check_interval ) . ' ' . __( 'seconds', self::DOMAIN );  
							  				?>
						  				</td>
			  						</tr>								  
								  
								  	<tr>
										<td><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></td>
									  	<td><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></td><td>
						  				<?php
					  						switch ( $this->share_variation_analysis_mode ) {
		  										case self::OPT_SHARE_VARIATION_ANALYSIS_NONE:
		  											_e( 'Disabled (None)', self::DOMAIN );
		  											break;
		  										case self::OPT_SHARE_VARIATION_ANALYSIS_MANUAL:
		  											_e( 'Enabled (Manual)', self::DOMAIN );
		  											break;
		  										case self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER:
		  											_e( 'Enabled (Scheduler)', self::DOMAIN );
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>
									<?php
										if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {
									?>
			  						<tr>
						 				<td><?php _e( 'Share Variation Analysis', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Schedule', self::DOMAIN ); ?></td>
									  	<td><?php echo esc_html( $this->share_variation_analysis_schedule ); ?></td>
			  						</tr>
									<?php
										}
									?>								  								  
			  						<tr>
						 				<td><?php _e( 'Follow Base Cache', self::DOMAIN); ?></td>
									  	<td><?php _e( 'Target SNS', self::DOMAIN ); ?></td>
						  				<td>
							  				<?php
					  							$target_sns = array();
					  							if ( isset( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) && $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) {
									  				$target_sns[] = 'Feedly';
												}
							  					echo esc_html( implode( ', ', $target_sns ) );
							  				?>
						  				</td>
			  						</tr>	
			  						<tr>
						 				<td><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Interval cheking follower count (sec)', self::DOMAIN ); ?></td>
						  				<td><?php echo esc_html( $this->follow_base_check_interval ) . ' ' . __( 'seconds', self::DOMAIN ); ?></td>
			  						</tr>								  
								  	<?php if ( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) { ?>
								  	<tr>
						 				<td><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Target feed type', self::DOMAIN ); ?></td>
						  				<td>
										<?php	  
				  							switch ( $this->follow_feed_type ) {
					  							case self::OPT_FEED_TYPE_DEFAULT:
					  								_e( 'Default', self::DOMAIN );
					  								break;
					  							case self::OPT_FEED_TYPE_RSS:
					  								_e( 'RSS', self::DOMAIN );
					  								break;
					  							case self::OPT_FEED_TYPE_RSS2:
					  								_e( 'RSS2', self::DOMAIN );
					  								break;
					  							case self::OPT_FEED_TYPE_RDF:
					  								_e( 'RDF', self::DOMAIN );
					  								break;
					  							case self::OPT_FEED_TYPE_ATOM:
					  								_e( 'ATOM', self::DOMAIN );
					  								break;
					  							default:
					  								_e( 'Default', self::DOMAIN );		  
				  							}
										?>
								  		</td>
								  	</tr>
								  	<tr>
						 				<td><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Target feed', self::DOMAIN ); ?></td>
						  				<td><?php echo get_feed_link( $this->follow_feed_type ); ?></td>
								  	</tr>									  
								  	<?php } ?>								  
			  						<tr>
										<td><?php _e( 'Dynamic Cache', self::DOMAIN); ?></td>
									  	<td><?php _e( 'Dynamic caching based on user access', self::DOMAIN ); ?></td><td>
						  				<?php
					  						switch ( $this->dynamic_cache_mode ) {
		  										case self::OPT_COMMON_ACCESS_BASED_CACHE_NONE:
		  											_e( 'Disabled (None)', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE:
		  											_e( 'Enabled (Synchronous Cache)', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE:
		  											_e( 'Enabled (Asynchronous Cache)', self::DOMAIN );
											  		break;
								  				case self::OPT_COMMON_ACCESS_BASED_2ND_CACHE:
													_e( 'Enabled (Asynchronous 2nd Cache)', self::DOMAIN );								  
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>
			  						<tr>
										<td><?php _e( 'Data Crawler', self::DOMAIN ); ?></td>
									  	<td><?php _e( 'Crawl method', self::DOMAIN ); ?></td>
									  	<td>
						  				<?php
					  						switch ( $this->crawler_method ) {
		  										case self::OPT_COMMON_CRAWLER_METHOD_NORMAL:
		  											_e( 'Normal (Sequential Retrieval)', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_CRAWLER_METHOD_CURL:
		  											_e( 'Extended (Parallel Retrieval)', self::DOMAIN );
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>								  
			  						<tr>
										<td><?php _e( 'Data Crawler', self::DOMAIN ); ?></td>
									  	<td><?php _e( 'SSL verification', self::DOMAIN ); ?></td>
									  	<td>
						  				<?php
					  						if ( $this->crawler_ssl_verification ) {
		  										_e( 'On', self::DOMAIN );
											} else {
		  										_e( 'Off', self::DOMAIN );	
											}
						  				?>
						  				</td>
			  						</tr>				  						
								  	<tr>
										<td><?php _e( 'Data Export', self::DOMAIN ); ?></td>
									  	<td><?php _e( 'Method of data export', self::DOMAIN ); ?></td><td>
						  				<?php
					  						switch ( $this->data_export_mode ) {
		  										case self::OPT_COMMON_DATA_EXPORT_MANUAL:
		  											_e( 'Manual', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_DATA_EXPORT_SCHEDULER:
		  											_e( 'Scheduler', self::DOMAIN );
		  											break;
											}
						  				?>
						  				</td>
			  						</tr>
									<?php
										if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
									?>
			  						<tr>
						 				<td><?php _e( 'Data Export', self::DOMAIN ); ?></td>
						  				<td><?php _e( 'Interval exporting share count to a csv file', self::DOMAIN ); ?></td>
									  	<td><?php echo esc_html( $this->data_export_interval / 3600 ) . ' ' . __( 'hours', self::DOMAIN ); ?></td>
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
					<form action="admin.php?page=scc-setting" method="post">
					  	<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
					  	<div id="share-base-cache" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e('Share Base Cache', self::DOMAIN); ?></span></h3>  
							<div class="inside">
								<table class="form-table">
									<tr>
						  				<th><label><?php _e( 'Target SNS', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<div class="sns-check">
								 				<input type="checkbox" value="1" name="share_base_cache_target_facebook"<?php if ( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) echo ' checked="checked"'; ?> />
							  					<label><?php _e( 'Facebook', self::DOMAIN ); ?></label>						 
							  				</div>
							  				<div class="sns-check">
												<input type="checkbox" value="1" name="share_base_cache_target_gplus"<?php if ( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) echo ' checked="checked"'; ?> />
						  						<label><?php _e( 'Google+', self::DOMAIN ); ?></label>
							  				</div>
							  				<div class="sns-check">
								  				<input type="checkbox" value="1" name="share_base_cache_target_hatebu"<?php if ( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) echo ' checked="checked"'; ?> />
							  					<label><?php _e( 'Hatena Bookmark', self::DOMAIN ); ?></label>
							  				</div>
							  				<div class="sns-check">
								  				<input type="checkbox" value="1" name="share_base_cache_target_pocket"<?php if ( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) echo ' checked="checked"'; ?> />
							  					<label><?php _e( 'Pocket', self::DOMAIN ); ?></label>
							  				</div>
							  				<div class="sns-check">
												<input type="checkbox" value="1" name="share_base_cache_target_twitter"<?php if ( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) echo ' checked="checked"'; ?> />
						 						<label><?php _e( 'Twitter', self::DOMAIN ); ?></label>						  
							  				</div>
						  				</td>
									</tr>
			  						<tr>
						  				<th><label><?php _e( 'Custom post types', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<input type="text" class="text" name="share_base_custom_post_types" size="60" value="<?php echo esc_html( implode( ',', $this->share_base_custom_post_types ) );  ?>" />
											<br>
							  				<label><?php _e( 'e.g. aaa, bbb, ccc (comma-delimited)', self::DOMAIN ); ?></label>
						  				</td>
			  						</tr>									  
			  						<tr>
						  				<th><label><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></label></th>
						  				<td>
							  			<input type="text" class="text" name="share_base_check_interval" size="20" value="<?php echo esc_html( $this->share_base_check_interval ); ?>" />
							  				<label><?php _e( 'Default: 600', self::DOMAIN ); ?></label>
						  				</td>
			  						</tr>
			  						<tr>
						  				<th><label><?php _e( 'Number of contents to check at a time', self::DOMAIN ); ?></label></th>
						 				<td>
							  				<input type="text" class="text" name="share_base_posts_per_check" size="20" value="<?php echo esc_html( $this->share_base_posts_per_check ); ?>" />
							  				<label><?php _e( 'Default: 20', self::DOMAIN ); ?></label>
						  				</td>
			  						</tr>
			  						<tr>
						  				<th><label><?php _e( 'Scheme migration mode from http to https', self::DOMAIN ); ?></label></th>
						 				<td>
							  				<select name="scheme_migration_mode">
												<option value="0"<?php if ( $this->scheme_migration_mode === self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Off', self::DOMAIN ); ?></option>
												<option value="1"<?php if ( $this->scheme_migration_mode === self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON ) echo ' selected="selected"'; ?>><?php _e( 'On', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e('Default: Off', self::DOMAIN ); ?></label>											  
						  				</td>
			  						</tr>
								  	<?php if ( $this->scheme_migration_mode ) { ?>
			  						<tr>
						  				<th><label><?php _e( 'Scheme migration date from http to https', self::DOMAIN ); ?></label></th>
						 				<td>
							  				<input id="scheme-migration-date" type="text" class="text" name="scheme_migration_date" size="20" value="<?php echo esc_html( $this->scheme_migration_date ); ?>" />
										  	<label><?php _e( 'Default: N/A', self::DOMAIN ); ?></label>
										  	<script>
												jQuery(document).ready(function() {
 													jQuery('#scheme-migration-date').datepicker({
													  	dateFormat : 'yy/mm/dd'
													});
												});
											</script>
						  				</td>
			  						</tr>
								  	<?php } ?>
						  		</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
									<input type="submit" class="button" name="clear_share_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ); ?>">
			  					</div>								  								  
						  	</div>
						</div>
						<div id="share-rush-cache" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Share Rush Cache', self::DOMAIN); ?></span></h3>  
							<div class="inside">
								<table class="form-table">
			  						<tr>
						  				<th><label><?php _e( 'Term considering posted content as new content', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<select name="share_rush_new_content_term">
												<option value="1"<?php if ( $this->share_rush_new_content_term === 1 ) echo ' selected="selected"'; ?>><?php _e( '1 day', self::DOMAIN ); ?></option>
												<option value="2"<?php if ( $this->share_rush_new_content_term === 2 ) echo ' selected="selected"'; ?>><?php _e( '2 days', self::DOMAIN ); ?></option>
												<option value="3"<?php if ( $this->share_rush_new_content_term === 3 ) echo ' selected="selected"'; ?>><?php _e( '3 days', self::DOMAIN ); ?></option>
												<option value="4"<?php if ( $this->share_rush_new_content_term === 4 ) echo ' selected="selected"'; ?>><?php _e( '4 days', self::DOMAIN ); ?></option>
												<option value="5"<?php if ( $this->share_rush_new_content_term === 5 ) echo ' selected="selected"'; ?>><?php _e( '5 days', self::DOMAIN ); ?></option>
												<option value="6"<?php if ( $this->share_rush_new_content_term === 6 ) echo ' selected="selected"'; ?>><?php _e( '6 days', self::DOMAIN ); ?></option>
												<option value="7"<?php if ( $this->share_rush_new_content_term === 7 ) echo ' selected="selected"'; ?>><?php _e( '7 days', self::DOMAIN ); ?></option>
												<option value="8"<?php if ( $this->share_rush_new_content_term === 8 ) echo ' selected="selected"'; ?>><?php _e( '8 days', self::DOMAIN ); ?></option>
												<option value="9"<?php if ( $this->share_rush_new_content_term === 9 ) echo ' selected="selected"'; ?>><?php _e( '9 days', self::DOMAIN ); ?></option>
												<option value="10"<?php if ( $this->share_rush_new_content_term === 10 ) echo ' selected="selected"'; ?>><?php _e( '10 days', self::DOMAIN ); ?></option>
												<option value="11"<?php if ( $this->share_rush_new_content_term === 11 ) echo ' selected="selected"'; ?>><?php _e( '11 days', self::DOMAIN ); ?></option>
												<option value="12"<?php if ( $this->share_rush_new_content_term === 12 ) echo ' selected="selected"'; ?>><?php _e( '12 days', self::DOMAIN ); ?></option>
												<option value="13"<?php if ( $this->share_rush_new_content_term === 13 ) echo ' selected="selected"'; ?>><?php _e( '13 days', self::DOMAIN ); ?></option>
												<option value="14"<?php if ( $this->share_rush_new_content_term === 14 ) echo ' selected="selected"'; ?>><?php _e( '14 days', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: 3 days', self::DOMAIN ); ?></label>
						  				</td>
									</tr>
								  
			  						<tr>
						  				<th><label><?php _e( 'Interval cheking share count (sec)', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<?php
											  	echo esc_html( $this->share_rush_check_interval ) . ' ' . __( 'seconds', self::DOMAIN );  
							  				?>
						  				</td>
									</tr>							  
								  
								</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
			  					</div>								  								  
						  	</div>
						</div>					  
						<div id="share-variation-analysis" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e('Share Variation Analysis', self::DOMAIN); ?></span></h3>  
							<div class="inside">
								<table class="form-table">						  
					  				<tr>
										<th><label><?php _e( 'Method to update basis of comparison', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<select name="share_variation_analysis_mode">
												<option value="1"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) echo ' selected="selected"'; ?>><?php _e( 'Disabled (None)', self::DOMAIN ); ?></option>
												<option value="2"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Manual)', self::DOMAIN ); ?></option>
												<option value="3"<?php if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Scheduler)', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: None', self::DOMAIN ); ?></label>
						  				</td>
					  				</tr>
								  
									<?php
									  	if ( $this->share_variation_analysis_mode === self::OPT_SHARE_VARIATION_ANALYSIS_SCHEDULER ) {

											list( $cronstr['minutes'], $cronstr['hours'], $cronstr['mday'], $cronstr['mon'], $cronstr['wday'] ) = explode( ' ', $this->share_variation_analysis_schedule, 5 );
											if ( strstr( $cronstr['minutes'], '*/' ) ) {
												$minutes = explode( '/', $cronstr['minutes'] );
											} else {
												$minutes = explode( ',', $cronstr['minutes'] );
											}
											if ( strstr( $cronstr['hours'], '*/' ) ) {
												$hours = explode( '/', $cronstr['hours'] );
											} else {
												$hours = explode( ',', $cronstr['hours'] );
											}
											if ( strstr( $cronstr['mday'], '*/' ) ) {
												$mday = explode( '/', $cronstr['mday'] );
											} else {
												$mday = explode( ',', $cronstr['mday'] );
											}
											if ( strstr( $cronstr['mon'], '*/' ) ) {
												$mon = explode( '/', $cronstr['mon'] );
											} else {
												$mon = explode( ',', $cronstr['mon'] );
											}
											if ( strstr( $cronstr['wday'], '*/' ) ) {
												$wday = explode( '/', $cronstr['wday'] );
											} else {
												$wday = explode( ',', $cronstr['wday'] );
											}
									?>
                    				<tr class="a_wpcron">
                        				<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
                        				<td>
                            				<table class="wpcron">
                                				<tr>
                                    				<th>
														<?php _e( 'Type', self::DOMAIN ); ?>
                                    				</th>
                                    				<th>
                                    				</th>
                                    				<th>
														<?php _e( 'Hour', self::DOMAIN ); ?>
                                    				</th>
                                    				<th>
														<?php _e( 'Minute', self::DOMAIN ); ?>
                                    				</th>
                                				</tr>
                                				<tr>
                                    				<td>
														<label for="idcronbtype-mon">
															<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[0] ), FALSE ) . ' name="a_cronbtype" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
														</label>
													</td>
                                    				<td>
														<select name="a_moncronmday">
														<?php for ( $i = 1; $i <= 31; $i ++ ) {
									  							$on_day = '';
									  							switch ( $i ) {
																  	case 1:
																  		$on_day = __( 'on 1.', self::DOMAIN );
																  		break;
																  	case 2:
																  		$on_day = __( 'on 2.', self::DOMAIN );
																  		break;
																  	case 3:
																  		$on_day = __( 'on 3.', self::DOMAIN );
																  		break;
																  	case 4:
																  		$on_day = __( 'on 4.', self::DOMAIN );
																  		break;
																  	case 5:
																  		$on_day = __( 'on 5.', self::DOMAIN );
																  		break;
																  	case 6:
																  		$on_day = __( 'on 6.', self::DOMAIN );
																  		break;
																  	case 7:
																  		$on_day = __( 'on 7.', self::DOMAIN );
																  		break;
																  	case 8:
																  		$on_day = __( 'on 8.', self::DOMAIN );
																  		break;
																  	case 9:
																  		$on_day = __( 'on 9.', self::DOMAIN );
																  		break;
																  	case 10:
																  		$on_day = __( 'on 10.', self::DOMAIN );
																  		break;
																  	case 11:
																  		$on_day = __( 'on 11.', self::DOMAIN );
																  		break;
																  	case 12:
																  		$on_day = __( 'on 12.', self::DOMAIN );
																  		break;
																  	case 13:
																  		$on_day = __( 'on 13.', self::DOMAIN );
																  		break;
																  	case 14:
																  		$on_day = __( 'on 14.', self::DOMAIN );
																  		break;
																  	case 15:
																  		$on_day = __( 'on 15.', self::DOMAIN );
																  		break;
																  	case 16:
																  		$on_day = __( 'on 16.', self::DOMAIN );
																  		break;
																  	case 17:
																  		$on_day = __( 'on 17.', self::DOMAIN );
																  		break;
																  	case 18:
																  		$on_day = __( 'on 18.', self::DOMAIN );
																  		break;
																  	case 19:
																  		$on_day = __( 'on 19.', self::DOMAIN );
																  		break;
																  	case 20:
																  		$on_day = __( 'on 20.', self::DOMAIN );
																  		break;
																  	case 21:
																  		$on_day = __( 'on 21.', self::DOMAIN );
																  		break;
																  	case 22:
																  		$on_day = __( 'on 22.', self::DOMAIN );
																  		break;
																  	case 23:
																  		$on_day = __( 'on 23.', self::DOMAIN );
																  		break;
																  	case 24:
																  		$on_day = __( 'on 24.', self::DOMAIN );
																  		break;
																  	case 25:
																  		$on_day = __( 'on 25.', self::DOMAIN );
																  		break;
																  	case 26:
																  		$on_day = __( 'on 26.', self::DOMAIN );
																  		break;
																  	case 27:
																  		$on_day = __( 'on 27.', self::DOMAIN );
																  		break;
																  	case 28:
																  		$on_day = __( 'on 28.', self::DOMAIN );
																  		break;
																  	case 29:
																  		$on_day = __( 'on 29.', self::DOMAIN );
																  		break;
																  	case 30:
																  		$on_day = __( 'on 30.', self::DOMAIN );
																  		break;
																  	case 31:
																  		$on_day = __( 'on 31.', self::DOMAIN );
																  		break;
									  							}
									  
																echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $on_day . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="a_moncronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="a_moncronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                				</tr>
                                				<tr>
                                    				<td>
													  	<label for="idcronbtype-week">
															<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[0] ), FALSE ) . ' name="a_cronbtype" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													  	</label>
													</td>
                                    				<td>
														<select name="a_weekcronwday">
															<?php 	echo '<option ' . selected( in_array( '0', $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '1', $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '2', $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '3', $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '4', $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '5', $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '6', $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>'; ?>
                                    					</select>
													</td>
                                    				<td>
														<select name="a_weekcronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="a_weekcronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?></select>
													</td>
                                				</tr>
                                				<tr>
                                    				<td>
														<label for="idcronbtype-day">
															<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[0] . $wday[0], FALSE ) . ' name="a_cronbtype" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
														</label>
													</td>
                                    				<td>
													</td>
                                    				<td>
														<select name="a_daycronhours">
															<?php for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															} ?>
														</select>
													</td>
                                    				<td>
														<select name="a_daycronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                				</tr>
                            				</table>
                        				</td>
                    				</tr>									  	  
									<?php
										}
									?>
								</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
								  	<input type="submit" class="button" name="update_share_comparison_base" value="<?php _e( 'Update Basis of Comparison', self::DOMAIN ); ?>" />
			  					</div>								  
						  	</div>
						</div>					  					  					  
						<div id="follow-base-cache" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Follow Base Cache', self::DOMAIN ); ?></span></h3>  
							<div class="inside">
								<table class="form-table">
									<tr>
						  				<th><label><?php _e( 'Target SNS', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<div class="sns-check">
												<input type="checkbox" value="1" name="follow_base_cache_target_feedly"<?php if ( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) echo ' checked="checked"'; ?> />
						 						<label><?php _e( 'Feedly', self::DOMAIN ); ?></label>						  
							  				</div>
						  				</td>
									</tr>						  
			  						<tr>
						  				<th><label><?php _e( 'Interval cheking follower count (sec)', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<input type="text" class="text" name="follow_base_check_interval" size="20" value="<?php echo esc_html( $this->follow_base_check_interval); ?>" />
										  	<label><?php _e( 'Default: 86400 Minimum: 3600', self::DOMAIN ); ?></label>
						  				</td>
			  						</tr>
								</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
									<input type="submit" class="button" name="clear_follow_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ); ?>">									  
			  					</div>								  								  
						  	</div>
						</div>
					  	<?php if ( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) { ?>
						<div id="follow-base-cache-feedly" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Follow Base Cache - Feedly', self::DOMAIN ); ?></span></h3>  
							<div class="inside">
								<table class="form-table">								  	
									<tr>
									  	<th><label><?php _e( 'Target feed type', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<select name="follow_feed_type">
												<option value="default"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_DEFAULT ) echo ' selected="selected"'; ?>><?php _e( 'Default', self::DOMAIN ) ?></option>
												<option value="rss"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RSS ) echo ' selected="selected"'; ?>><?php _e( 'RSS', self::DOMAIN ); ?></option>
												<option value="rss2"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RSS2 ) echo ' selected="selected"'; ?>><?php _e( 'RSS2', self::DOMAIN ); ?></option>
												<option value="rdf"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_RDF ) echo ' selected="selected"'; ?>><?php _e( 'RDF', self::DOMAIN ); ?></option>
												<option value="atom"<?php if ( $this->follow_feed_type === self::OPT_FEED_TYPE_ATOM ) echo ' selected="selected"'; ?>><?php _e( 'ATOM', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: Default', self::DOMAIN ); ?></label>
						  				</td>
									</tr>
								  	<tr>
									  	<th><label><?php _e( 'Target feed', self::DOMAIN ); ?></label></th>
									  	<td><?php echo get_feed_link( $this->follow_feed_type ); ?></td>
									</tr>
								</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />								  
			  					</div>								  								  
						  	</div>
						</div>			
					  	<?php } ?>		
						<div id="common-dynamic-cache" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Dynamic Cache', self::DOMAIN ); ?></span></h3>  
							<div class="inside">
								<table class="form-table">
					  				<tr>
										<th><label><?php _e( 'Dynamic caching based on user access', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<select name="dynamic_cache_mode">
												<option value="1"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_CACHE_NONE ) echo ' selected="selected"'; ?>><?php _e( 'Disabled (None)', self::DOMAIN ); ?></option>
												<option value="2"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Synchronous Cache)', self::DOMAIN ); ?></option>
												<option value="3"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Asynchronous Cache)', self::DOMAIN ); ?></option>
												<option value="4"<?php if ( $this->dynamic_cache_mode === self::OPT_COMMON_ACCESS_BASED_2ND_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Enabled (Asynchronous 2nd Cache)', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: Enabled (Asynchronous 2nd Cache)', self::DOMAIN ); ?></label>
						  				</td>
					  				</tr>
								</table>
			  				<div class="submit-button">
								<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
			  					</div>								  								  
						  	</div>
						</div>
					  	<div id="common-data-crawler" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Data Crawler', self::DOMAIN ); ?></span></h3>  
							<div class="inside">
								<table class="form-table">
									<tr>
						  				<th><label><?php _e( 'Crawl method', self::DOMAIN ); ?></label></th>
						  				<td>
						  				<?php
					  						switch ( $this->crawler_method ) {
		  										case self::OPT_COMMON_CRAWLER_METHOD_NORMAL:
		  											_e( 'Normal (Sequential Retrieval)', self::DOMAIN );
		  											break;
		  										case self::OPT_COMMON_CRAWLER_METHOD_CURL:
		  											_e( 'Extended (Parallel Retrieval)', self::DOMAIN );
		  											break;
											}
						  				?>
						  				</td>
									</tr>
			  						<tr>
						  				<th><label><?php _e( 'SSL verification', self::DOMAIN ); ?></label></th>
						 				<td>
							  				<select name="crawler_ssl_verification">
												<option value="0"<?php if ( $this->crawler_ssl_verification === self::OPT_COMMON_CRAWLER_SSL_VERIFY_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Off', self::DOMAIN ); ?></option>
												<option value="1"<?php if ( $this->crawler_ssl_verification === self::OPT_COMMON_CRAWLER_SSL_VERIFY_ON ) echo ' selected="selected"'; ?>><?php _e( 'On', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: On', self::DOMAIN ); ?></label>											  
						  				</td>
			  						</tr>									  
						  		</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
			  					</div>								  								  
						  	</div>
						</div>  
						<div id="common-data-export" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Data Export', self::DOMAIN ); ?></span></h3>  
							<div class="inside">
								<table class="form-table">						  
					  				<tr>
										<th><label><?php _e( 'Method of data export', self::DOMAIN ); ?></label></th>
						  				<td>
							  				<select name="data_export_mode">
												<option value="1"<?php if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Manual', self::DOMAIN ); ?></option>
												<option value="2"<?php if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) echo ' selected="selected"'; ?> disabled="disabled"><?php _e( 'Scheduler', self::DOMAIN ); ?></option>
							  				</select>
							  				<label><?php _e( 'Default: Manual', self::DOMAIN ); ?></label>
						  				</td>
					  				</tr>
									<?php
										if ( $this->data_export_mode === self::OPT_COMMON_DATA_EXPORT_SCHEDULER ) {
										
										  	list( $cronstr[ 'minutes' ], $cronstr[ 'hours' ], $cronstr[ 'mday' ], $cronstr[ 'mon' ], $cronstr[ 'wday' ] ) = explode( ' ', $this->data_export_schedule, 5 );
											if ( strstr( $cronstr[ 'minutes' ], '*/' ) ) {
												$minutes = explode( '/', $cronstr[ 'minutes' ] );
											} else {
												$minutes = explode( ',', $cronstr[ 'minutes' ] );
											}
											if ( strstr( $cronstr[ 'hours' ], '*/' ) ) {
												$hours = explode( '/', $cronstr[ 'hours' ] );
											} else {
												$hours = explode( ',', $cronstr[ 'hours' ] );
											}
											if ( strstr( $cronstr[ 'mday' ], '*/' ) ) {
												$mday = explode( '/', $cronstr[ 'mday' ] );
											} else {
												$mday = explode( ',', $cronstr[ 'mday' ] );
											}
											if ( strstr( $cronstr[ 'mon' ], '*/' ) ) {
												$mon = explode( '/', $cronstr[ 'mon' ] );
											} else {
												$mon = explode( ',', $cronstr[ 'mon' ] );
											}
											if ( strstr( $cronstr[ 'wday' ], '*/' ) ) {
												$wday = explode( '/', $cronstr[ 'wday' ] );
											} else {
												$wday = explode( ',', $cronstr[ 'wday' ] );
											}
									?>  
                    				<tr class="e_wpcron">
                        				<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
                        				<td>
                            				<table class="wpcron">
                                				<tr>
                                    				<th>
														<?php _e( 'Type', self::DOMAIN ); ?>
                                    				</th>
                                    				<th>
                                    				</th>
                                    				<th>
														<?php _e( 'Hour', self::DOMAIN ); ?>
                                    				</th>
                                    				<th>
														<?php _e( 'Minute', self::DOMAIN ); ?>
                                    				</th>
                                				</tr>
                                				<tr>
                                    				<td>
														<label for="idcronbtype-mon">
															<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[0] ), FALSE ) . ' name="e_cronbtype" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
														</label>
													</td>
                                    				<td>
														<select name="e_moncronmday">
														<?php for ( $i = 1; $i <= 31; $i ++ ) {
									  							$on_day = '';
									  							switch ( $i ) {
																  	case 1:
																  		$on_day = __( 'on 1.', self::DOMAIN );
																  		break;
																  	case 2:
																  		$on_day = __( 'on 2.', self::DOMAIN );
																  		break;
																  	case 3:
																  		$on_day = __( 'on 3.', self::DOMAIN );
																  		break;
																  	case 4:
																  		$on_day = __( 'on 4.', self::DOMAIN );
																  		break;
																  	case 5:
																  		$on_day = __( 'on 5.', self::DOMAIN );
																  		break;
																  	case 6:
																  		$on_day = __( 'on 6.', self::DOMAIN );
																  		break;
																  	case 7:
																  		$on_day = __( 'on 7.', self::DOMAIN );
																  		break;
																  	case 8:
																  		$on_day = __( 'on 8.', self::DOMAIN );
																  		break;
																  	case 9:
																  		$on_day = __( 'on 9.', self::DOMAIN );
																  		break;
																  	case 10:
																  		$on_day = __( 'on 10.', self::DOMAIN );
																  		break;
																  	case 11:
																  		$on_day = __( 'on 11.', self::DOMAIN );
																  		break;
																  	case 12:
																  		$on_day = __( 'on 12.', self::DOMAIN );
																  		break;
																  	case 13:
																  		$on_day = __( 'on 13.', self::DOMAIN );
																  		break;
																  	case 14:
																  		$on_day = __( 'on 14.', self::DOMAIN );
																  		break;
																  	case 15:
																  		$on_day = __( 'on 15.', self::DOMAIN );
																  		break;
																  	case 16:
																  		$on_day = __( 'on 16.', self::DOMAIN );
																  		break;
																  	case 17:
																  		$on_day = __( 'on 17.', self::DOMAIN );
																  		break;
																  	case 18:
																  		$on_day = __( 'on 18.', self::DOMAIN );
																  		break;
																  	case 19:
																  		$on_day = __( 'on 19.', self::DOMAIN );
																  		break;
																  	case 20:
																  		$on_day = __( 'on 20.', self::DOMAIN );
																  		break;
																  	case 21:
																  		$on_day = __( 'on 21.', self::DOMAIN );
																  		break;
																  	case 22:
																  		$on_day = __( 'on 22.', self::DOMAIN );
																  		break;
																  	case 23:
																  		$on_day = __( 'on 23.', self::DOMAIN );
																  		break;
																  	case 24:
																  		$on_day = __( 'on 24.', self::DOMAIN );
																  		break;
																  	case 25:
																  		$on_day = __( 'on 25.', self::DOMAIN );
																  		break;
																  	case 26:
																  		$on_day = __( 'on 26.', self::DOMAIN );
																  		break;
																  	case 27:
																  		$on_day = __( 'on 27.', self::DOMAIN );
																  		break;
																  	case 28:
																  		$on_day = __( 'on 28.', self::DOMAIN );
																  		break;
																  	case 29:
																  		$on_day = __( 'on 29.', self::DOMAIN );
																  		break;
																  	case 30:
																  		$on_day = __( 'on 30.', self::DOMAIN );
																  		break;
																  	case 31:
																  		$on_day = __( 'on 31.', self::DOMAIN );
																  		break;
									  							}									  
									  
																echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $on_day . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="e_moncronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="e_moncronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                				</tr>
                                				<tr>
                                    				<td>
													  	<label for="idcronbtype-week">
															<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[0] ), FALSE ) . ' name="e_cronbtype" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													  	</label>
													</td>
                                    				<td>
														<select name="e_weekcronwday">
															<?php 	echo '<option ' . selected( in_array( '0', $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '1', $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '2', $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '3', $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '4', $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '5', $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( '6', $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>'; ?>
                                    					</select>
													</td>
                                    				<td>
														<select name="e_weekcronhours">
														<?php for ( $i = 0; $i < 24; $i ++ ) {
															echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                    				<td>
														<select name="e_weekcronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?></select>
													</td>
                                				</tr>
                                				<tr>
                                    				<td>
														<label for="idcronbtype-day">
															<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[0] . $wday[0], FALSE ) . ' name="e_cronbtype" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
														</label>
													</td>
                                    				<td>
													</td>
                                    				<td>
														<select name="e_daycronhours">
															<?php for ( $i = 0; $i < 24; $i ++ ) {
																echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
															} ?>
														</select>
													</td>
                                    				<td>
														<select name="e_daycronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                				</tr>
                                				<tr>
                                    				<td>
														<label for="idcronbtype-hour">
															<?php echo '<input class="radio" type="radio"' . checked( "*", $hours[0], FALSE, FALSE ) . ' name="e_cronbtype" value="hour" /> ' . __( 'hourly', self::DOMAIN ); ?>
														</label>
													</td>
                                    				<td></td>
                                    				<td></td>
                                    				<td>
														<select name="e_hourcronminutes">
														<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
															echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
														} ?>
														</select>
													</td>
                                				</tr>
                            				</table>
                        				</td>
                    				</tr>									  	  
									<?php
										}
									?>
								</table>
			  					<div class="submit-button">
									<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ); ?>" />
			  					</div>								  
						  	</div>
						</div>	  
					</form>
				</div>  
				<div class="metabox-holder">
					<div id="common-exported-file" class="postbox">					  
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php _e( 'Exported File', self::DOMAIN ); ?></span></h3>  
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th><?php _e( 'Disk usage of exported file', self::DOMAIN ); ?></th>
										<td>
							  			<?php
											$abs_path = WP_PLUGIN_DIR . '/sns-count-cache/data/sns-count-cache-data.csv'; 
											$file_size = Common_Util::get_file_size( $abs_path );

											if ( isset( $file_size ) ) {
											  	echo $file_size;
											} else {
											  	_e( 'No exported file', self::DOMAIN );
											}
							  			?>
										</td>
									</tr>
								</tbody>
							</table>
							<form action="admin.php?page=scc-setting" method="post">
					  			<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
								<table class="form-table">						 
						 			<tbody>
						   				<tr>
								  			<th><?php _e( 'Manual export', self::DOMAIN ); ?></th>
											<td>
                              					<input type="submit" class="button" name="export_data" value="<?php _e( 'Export', self::DOMAIN ); ?>" />
							  					<br>
							  					<span class="description"><?php _e( 'Export share count to a csv file.', self::DOMAIN ); ?></span>
											</td>
							  			</tr>
						   			</tbody>
					  			</table>
                    		</form>
							<?php
								if ( file_exists( $abs_path ) ) {		  
							?>							  
				  			<form action="admin.php?page=scc-setting" method="post">
						 		<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
					  	 		<table class="form-table">
						 			<tbody>
						   				<tr>
								  			<th><?php _e( 'Reset of exported file', self::DOMAIN ); ?></th>
											<td>
                              					<input type="submit" class="button" name="reset_data" value="<?php _e( 'Reset', self::DOMAIN ); ?>" />
							  					<br>
							  					<span class="description"><?php _e( 'Clear exported csv file.', self::DOMAIN ); ?></span>
											</td>
							  			</tr>
						   			</tbody>
					  			</table>
                    		</form>
				  			<form action="<?php echo plugins_url(); ?>/sns-count-cache/includes/download.php" method="post">
						 		<?php wp_nonce_field( 'mynonce', '_wpnonce' ); ?>
					  	 		<table class="form-table">
						 			<tbody>
						   				<tr>
								  			<th><?php _e( 'Download of exported file', self::DOMAIN ); ?></th>
											<td>
                              					<input type="submit" class="button" name="download_data" value="<?php _e( 'Download', self::DOMAIN ); ?>" />
							  					<br>
							  					<span class="description"><?php _e( 'Download the exported csv file.', self::DOMAIN ); ?></span>
											</td>
							  			</tr>
								  	</tbody>
							  	</table>
						  	</form>
						  	<?php
								}
							?>
						</div>
					</div>
				</div>
			</div>
	</div>
