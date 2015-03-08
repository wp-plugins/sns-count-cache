<?php
/*
admin-setting.php

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

	if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] ) {
		if( check_admin_referer( __FILE__, '_wpnonce' ) ) {
		  
			if( isset( $_POST["update_all_options"] ) && $_POST["update_all_options"] === 'Update All Options' ) {
			 
			  	$share_base_cache_target = array();
		  		$follow_base_cache_target = array();	
			  
			  	if ( isset( $_POST["share_base_custom_post_types"] ) && $_POST["share_base_custom_post_types"] ) { 
				  	$share_base_custom_post_types = explode( ',', $_POST["share_base_custom_post_types"] );
			  		update_option( self::DB_SHARE_CUSTOM_POST_TYPES, $share_base_custom_post_types );
				} else {
				  	update_option( self::DB_SHARE_CUSTOM_POST_TYPES, array() );
				}

				if ( isset( $_POST["share_base_check_interval"] ) && $_POST["share_base_check_interval"] && is_numeric( $_POST["share_base_check_interval"] ) ) {
					update_option( self::DB_SHARE_CHECK_INTERVAL, $_POST["share_base_check_interval"] );
				}
			  
				if ( isset( $_POST["share_base_posts_per_check"] ) && $_POST["share_base_posts_per_check"] && is_numeric( $_POST["share_base_posts_per_check"] ) ) {
					update_option( self::DB_SHARE_POSTS_PER_CHECK, $_POST["share_base_posts_per_check"] );
				}
			  
				if ( isset( $_POST["dynamic_cache_mode"] ) ) {
					update_option( self::DB_COMMON_DYNAMIC_CACHE, $_POST["dynamic_cache_mode"] );
				} 
			  
				if ( isset( $_POST["share_rush_new_content_term"] ) && $_POST["share_rush_new_content_term"] && is_numeric( $_POST["share_rush_new_content_term"] ) ) {
					update_option( self::DB_SHARE_NEW_CONTENT_TERM, $_POST["share_rush_new_content_term"] );
				}
					
				if ( isset( $_POST["data_export_mode"] ) ) {
					update_option( self::DB_COMMON_DATA_EXPORT, $_POST["data_export_mode"] );
				} 			  

				if ( isset( $_POST["data_export_interval"] ) && $_POST["data_export_interval"] && is_numeric( $_POST["data_export_interval"] ) ) {
					update_option( self::DB_COMMON_DATA_EXPORT_INTERVAL, $_POST["data_export_interval"] );
				}
			  
				if ( isset( $_POST["share_base_cache_target_twitter"] ) && $_POST["share_base_cache_target_twitter"] ) {
					$share_base_cache_target[self::REF_SHARE_TWITTER] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_TWITTER] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_facebook"] ) && $_POST["share_base_cache_target_facebook"] ) {
				  	if ( Common_Util::extension_loaded_php_xml() ) {
						$share_base_cache_target[self::REF_SHARE_FACEBOOK] = true;
					} else {
					  	$share_base_cache_target[self::REF_SHARE_FACEBOOK] = false;
					}
				} else {
				  	$share_base_cache_target[self::REF_SHARE_FACEBOOK] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_gplus"] ) && $_POST["share_base_cache_target_gplus"] ) {
					$share_base_cache_target[self::REF_SHARE_GPLUS] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_GPLUS] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_pocket"] ) && $_POST["share_base_cache_target_pocket"] ) {
				  	if ( Common_Util::extension_loaded_php_xml() ) {
						$share_base_cache_target[self::REF_SHARE_POCKET] = true;
					} else {
					  	$share_base_cache_target[self::REF_SHARE_POCKET] = false;
					}
				} else {
				  	$share_base_cache_target[self::REF_SHARE_POCKET] = false;
				}
			  
				if ( isset( $_POST["share_base_cache_target_hatebu"] ) && $_POST["share_base_cache_target_hatebu"] ) {
					$share_base_cache_target[self::REF_SHARE_HATEBU] = true;
				} else {
				  	$share_base_cache_target[self::REF_SHARE_HATEBU] = false;
				}
						  
				if ( ! empty( $share_base_cache_target ) ) {
					update_option( self::DB_SHARE_CACHE_TARGET, $share_base_cache_target );
				}
	  
	  			if ( isset( $_POST["follow_base_check_interval"] ) && $_POST["follow_base_check_interval"] && is_numeric( $_POST["follow_base_check_interval"] ) ) {
					update_option( self::DB_FOLLOW_CHECK_INTERVAL, $_POST["follow_base_check_interval"] );
				}
	  			  
				if ( isset( $_POST["follow_base_cache_target_feedly"] ) && $_POST["follow_base_cache_target_feedly"] ) {
					$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = true;
				} else {
				  	$follow_base_cache_target[self::REF_FOLLOW_FEEDLY] = false;
				}
	  
				if ( ! empty( $follow_base_cache_target ) ) {
					update_option( self::DB_FOLLOW_CACHE_TARGET, $follow_base_cache_target );
				}
			  			  
				if ( isset( $_POST["scheme_migration_mode"] ) && $_POST["scheme_migration_mode"] ) {
				  	update_option( self::DB_COMMON_SCHEME_MIGRATION_MODE, self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON );
				} else {
				  	update_option( self::DB_COMMON_SCHEME_MIGRATION_MODE, self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF );
				}			  				  
			  
			  	if ( isset( $_POST['cronbtype'] ) && $_POST['cronbtype'] == 'mon' ) {
					update_option( self::DB_COMMON_DATA_EXPORT_SCHEDULE, $_POST['moncronminutes'] . ' ' . $_POST['moncronhours'] . ' ' . $_POST['moncronmday'] . ' * *' );
				}
			  	if ( isset( $_POST['cronbtype'] ) && $_POST['cronbtype'] == 'week' ) {
				  	update_option( self::DB_COMMON_DATA_EXPORT_SCHEDULE, $_POST['weekcronminutes'] . ' ' . $_POST['weekcronhours'] . ' * * ' . $_POST['weekcronwday'] );
				}
			  	if ( isset( $_POST['cronbtype'] ) && $_POST['cronbtype'] == 'day' ) {
				  	update_option( self::DB_COMMON_DATA_EXPORT_SCHEDULE, $_POST['daycronminutes'] . ' ' . $_POST['daycronhours'] . ' * * *' );
				}
			  	if ( isset( $_POST['cronbtype'] ) && $_POST['cronbtype'] == 'hour' ) {
				  	update_option( self::DB_COMMON_DATA_EXPORT_SCHEDULE, $_POST['hourcronminutes'] . ' * * * *' );				  	
				}
			  			  	  
				$this->reactivate_plugin();
			  
			}

			if( isset( $_POST["reset_data"] ) && $_POST["reset_data"] === 'Reset' ) {
			  	Common_Util::log( '[' . __METHOD__ . '] reset' );
			  
	  			$this->export_engines[self::REF_COMMON_EXPORT]->reset_export();
			}
		  
			if( isset( $_POST["export_data"] ) && $_POST["export_data"] === 'Export' ) {
			  	Common_Util::log( '[' . __METHOD__ . '] export' );
			  
	  			$this->export_engines[self::REF_COMMON_EXPORT]->execute_export( NULL );
			}
		  
			if( isset( $_POST["clear_share_base_cache"] ) && $_POST["clear_share_base_cache"] === 'Clear Cache' ) {
			  	Common_Util::log( '[' . __METHOD__ . '] clear cache' );
			  
	  			$this->cache_engines[self::REF_SHARE_BASE]->clear_cache();
			  	//$this->cache_engines[self::REF_SHARE_2ND]->clear_cache();
			  
			  	set_time_limit( $this->extended_max_execution_time );
			  
			  	$this->cache_engines[self::REF_SHARE_2ND]->initialize_cache();
			  
			  	set_time_limit( $this->original_max_execution_time ); 
			}

			if( isset( $_POST["clear_follow_base_cache"] ) && $_POST["clear_follow_base_cache"] === 'Clear Cache' ) {
			  	Common_Util::log( '[' . __METHOD__ . '] clear cache' );
			  
	  			$this->cache_engines[self::REF_FOLLOW_BASE]->clear_cache();
			  	//$this->cache_engines[self::REF_FOLLOW_2ND]->clear_cache();
			  
			  	set_time_limit( $this->extended_max_execution_time ); 
			  
			  	$this->cache_engines[self::REF_FOLLOW_2ND]->initialize_cache();
			  
			  	set_time_limit( $this->original_max_execution_time  ); 
			}			  
		}  
	}

	?>
	<div class="wrap">
	  	<h2><a href="admin.php?page=scc-setting"><?php _e( 'SNS Count Cache', self::DOMAIN ) ?></a></h2>
			<div class="sns-cnt-cache">

			  		<h3 class="nav-tab-wrapper">
					  	<a class="nav-tab" href="admin.php?page=scc-dashboard">Dashboard</a>
					  	<a class="nav-tab" href="admin.php?page=scc-cache-status">Cache Status</a>
					  	<a class="nav-tab" href="admin.php?page=scc-share-count">Share Count</a>
					  	<a class="nav-tab nav-tab-active" href="admin.php?page=scc-setting">Setting</a>
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
						<form action="admin.php?page=scc-setting" method="post">
					  		<?php wp_nonce_field( __FILE__, '_wpnonce' ); ?>
					  		<div id="share-base-cache" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e('Share Base Cache', self::DOMAIN) ?></span></h3>  
								<div class="inside">
									<table class="form-table">
										<tr>
						  					<th><label><?php _e( 'Target SNS', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<div class="sns-check">
													<input type="checkbox" value="1" name="share_base_cache_target_twitter"<?php if ( $this->share_base_cache_target[self::REF_SHARE_TWITTER] ) echo ' checked="checked"'; ?> />
						 							<label><?php _e( 'Twitter', self::DOMAIN ) ?></label>						  
							  					</div>
							  					<div class="sns-check">
								 					<input type="checkbox" value="1" name="share_base_cache_target_facebook"<?php if ( $this->share_base_cache_target[self::REF_SHARE_FACEBOOK] ) echo ' checked="checked"'; ?> />
							  						<label><?php _e( 'Facebook', self::DOMAIN ) ?></label>						 
							  					</div>
							  					<div class="sns-check">
													<input type="checkbox" value="1" name="share_base_cache_target_gplus"<?php if ( $this->share_base_cache_target[self::REF_SHARE_GPLUS] ) echo ' checked="checked"'; ?> />
						  							<label><?php _e( 'Google+', self::DOMAIN ) ?></label>
							  					</div>
							  					<div class="sns-check">
								  					<input type="checkbox" value="1" name="share_base_cache_target_pocket"<?php if ( $this->share_base_cache_target[self::REF_SHARE_POCKET] ) echo ' checked="checked"'; ?> />
							  						<label><?php _e( 'Pocket', self::DOMAIN ) ?></label>
							  					</div>
							  					<div class="sns-check">
								  					<input type="checkbox" value="1" name="share_base_cache_target_hatebu"<?php if ( $this->share_base_cache_target[self::REF_SHARE_HATEBU] ) echo ' checked="checked"'; ?> />
							  						<label><?php _e( 'Hatena Bookmark', self::DOMAIN ) ?></label>
							  					</div>
						  					</td>
										</tr>
			  							<tr>
						  					<th><label><?php _e( 'Additional custom post types', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<input type="text" class="text" name="share_base_custom_post_types" size="60" value="<?php echo implode( ',', $this->share_base_custom_post_types );  ?>" />
											  	<br>
							  					<label><?php _e( 'e.g. aaa, bbb, ccc (comma-delimited)', self::DOMAIN ) ?></label>
						  					</td>
			  							</tr>									  
			  							<tr>
						  					<th><label><?php _e( 'Interval cheking and caching share count (sec)', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<input type="text" class="text" name="share_base_check_interval" size="20" value="<?php echo $this->share_base_check_interval; ?>" />
							  					<label><?php _e( 'Default: 600', self::DOMAIN ) ?></label>
						  					</td>
			  							</tr>
			  							<tr>
						  					<th><label><?php _e( 'Number of posts to check at a time (posts)', self::DOMAIN ) ?></label></th>
						 					<td>
							  					<input type="text" class="text" name="share_base_posts_per_check" size="20" value="<?php echo $this->share_base_posts_per_check; ?>" />
							  					<label><?php _e( 'Default: 20', self::DOMAIN ) ?></label>
						  					</td>
			  							</tr>
			  							<tr>
						  					<th><label><?php _e( 'Migration mode from http to https', self::DOMAIN ) ?></label></th>
						 					<td>
							  					<select name="scheme_migration_mode">
												  	<option value="0"<?php if ( $this->scheme_migration_mode == self::OPT_COMMON_SCHEME_MIGRATION_MODE_OFF ) echo ' selected="selected"'; ?>><?php _e( 'Off', self::DOMAIN ) ?></option>
												  	<option value="1"<?php if ( $this->scheme_migration_mode == self::OPT_COMMON_SCHEME_MIGRATION_MODE_ON ) echo ' selected="selected"'; ?>><?php _e( 'On', self::DOMAIN ) ?></option>
							  					</select>
							  					<label><?php _e('Default: Off', self::DOMAIN) ?></label>											  
						  					</td>
			  							</tr>									  
						  			</table>
			  						<div class="submit-button">
										<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ) ?>" />
									  	<input type="submit" class="button" name="clear_share_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ) ?>">
			  						</div>								  								  
						  		</div>
						  	</div>
							<div id="share-rush-cache" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e('Share Rush Cache', self::DOMAIN) ?></span></h3>  
								<div class="inside">
								  	<table class="form-table">
			  							<tr>
						  					<th><label><?php _e( 'Term considering posted content as new content', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<select name="share_rush_new_content_term">
													<option value="1"<?php if ( $this->share_rush_new_content_term == 1 ) echo ' selected="selected"'; ?>>1 day</option>
													<option value="2"<?php if ( $this->share_rush_new_content_term == 2 ) echo ' selected="selected"'; ?>>2 days</option>
													<option value="3"<?php if ( $this->share_rush_new_content_term == 3 ) echo ' selected="selected"'; ?>>3 days</option>
													<option value="4"<?php if ( $this->share_rush_new_content_term == 4 ) echo ' selected="selected"'; ?>>4 days</option>
													<option value="5"<?php if ( $this->share_rush_new_content_term == 5 ) echo ' selected="selected"'; ?>>5 days</option>
													<option value="6"<?php if ( $this->share_rush_new_content_term == 6 ) echo ' selected="selected"'; ?>>6 days</option>
													<option value="7"<?php if ( $this->share_rush_new_content_term == 7 ) echo ' selected="selected"'; ?>>7 days</option>
													<option value="8"<?php if ( $this->share_rush_new_content_term == 8 ) echo ' selected="selected"'; ?>>8 days</option>
													<option value="9"<?php if ( $this->share_rush_new_content_term == 9 ) echo ' selected="selected"'; ?>>9 days</option>
													<option value="10"<?php if ( $this->share_rush_new_content_term == 10 ) echo ' selected="selected"'; ?>>10 days</option>
													<option value="11"<?php if ( $this->share_rush_new_content_term == 11 ) echo ' selected="selected"'; ?>>11 days</option>
													<option value="12"<?php if ( $this->share_rush_new_content_term == 12 ) echo ' selected="selected"'; ?>>12 days</option>
													<option value="13"<?php if ( $this->share_rush_new_content_term == 13 ) echo ' selected="selected"'; ?>>13 days</option>
													<option value="14"<?php if ( $this->share_rush_new_content_term == 14 ) echo ' selected="selected"'; ?>>14 days</option>
							  					</select>
							  					<label><?php _e( 'Default: 3 days', self::DOMAIN ) ?></label>
						  					</td>
										</tr>
									</table>
			  						<div class="submit-button">
										<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ) ?>" />
			  						</div>								  								  
						  		</div>
						  	</div>
							<div id="follow-base-cache" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e('Follow Base Cache', self::DOMAIN) ?></span></h3>  
								<div class="inside">
								  	<table class="form-table">
										<tr>
						  					<th><label><?php _e( 'Target SNS', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<div class="sns-check">
													<input type="checkbox" value="1" name="follow_base_cache_target_feedly"<?php if ( $this->follow_base_cache_target[self::REF_FOLLOW_FEEDLY] ) echo ' checked="checked"'; ?> />
						 							<label><?php _e( 'Feedly', self::DOMAIN ) ?></label>						  
							  					</div>
						  					</td>
										</tr>						  
			  							<tr>
						  					<th><label><?php _e( 'Interval cheking and caching follower count (sec)', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<input type="text" class="text" name="follow_base_check_interval" size="20" value="<?php echo $this->follow_base_check_interval; ?>" />
							  					<label><?php _e( 'Default: 1800', self::DOMAIN ) ?></label>
						  					</td>
			  							</tr>
									</table>
			  						<div class="submit-button">
										<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ) ?>" />
									  	<input type="submit" class="button" name="clear_follow_base_cache" value="<?php _e( 'Clear Cache', self::DOMAIN ) ?>">									  
			  						</div>								  								  
						  		</div>
						  	</div>									 		  
							<div id="common-dynamic-cache" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e( 'Dynamic Cache', self::DOMAIN ) ?></span></h3>  
								<div class="inside">
								  	<table class="form-table">
					  					<tr>
										  	<th><label><?php _e( 'Dynamic caching based on user access', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<select name="dynamic_cache_mode">
												  	<option value="0"<?php if ( $this->dynamic_cache_mode == self::OPT_COMMON_ACCESS_BASED_CACHE_NONE ) echo ' selected="selected"'; ?>><?php _e( 'None', self::DOMAIN ) ?></option>
												  	<option value="1"<?php if ( $this->dynamic_cache_mode == self::OPT_COMMON_ACCESS_BASED_SYNC_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Synchronous Cache', self::DOMAIN ) ?></option>
												  	<option value="2"<?php if ( $this->dynamic_cache_mode == self::OPT_COMMON_ACCESS_BASED_ASYNC_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Asynchronous Cache', self::DOMAIN ) ?></option>
												  	<option value="3"<?php if ( $this->dynamic_cache_mode == self::OPT_COMMON_ACCESS_BASED_2ND_CACHE ) echo ' selected="selected"'; ?>><?php _e( 'Asynchronous 2nd Cache', self::DOMAIN ) ?></option>
							  					</select>
							  					<label><?php _e('Default: None', self::DOMAIN) ?></label>
						  					</td>
					  					</tr>
									</table>
			  						<div class="submit-button">
										<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ) ?>" />
			  						</div>								  								  
						  		</div>
						  	</div>
							<div id="common-data-export" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e('Data Export', self::DOMAIN) ?></span></h3>  
								<div class="inside">
								  	<table class="form-table">						  
					  					<tr>
										  	<th><label><?php _e( 'Method of data export', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<select name="data_export_mode">
												  	<option value="0"<?php if ( $this->data_export_mode == self::OPT_COMMON_DATA_EXPORT_MANUAL ) echo ' selected="selected"'; ?>><?php _e( 'Manual', self::DOMAIN ) ?></option>
												  	<option value="1"<?php if ( $this->data_export_mode == self::OPT_COMMON_DATA_EXPORT_AUTO ) echo ' selected="selected"'; ?> disabled="disabled"><?php _e( 'Auto', self::DOMAIN ) ?></option>
							  					</select>
							  					<label><?php _e('Default: Manual', self::DOMAIN) ?></label>
						  					</td>
					  					</tr>
									  	<?php
									  		if ( $this->data_export_mode == self::OPT_COMMON_DATA_EXPORT_AUTO ) {
									  	?>
									  
			  							<tr>
						  					<th><label><?php _e( 'Interval exporting share count to a csv file', self::DOMAIN ) ?></label></th>
						  					<td>
							  					<select name="data_export_interval">
													<option value="600"<?php if ( $this->data_export_interval == 600 ) echo ' selected="selected"'; ?>>10 minites</option>
													<option value="10800"<?php if ( $this->data_export_interval == 10800 ) echo ' selected="selected"'; ?>>3 hours</option>
													<option value="21600"<?php if ( $this->data_export_interval == 21600 ) echo ' selected="selected"'; ?>>6 hours</option>
													<option value="43200"<?php if ( $this->data_export_interval == 43200 ) echo ' selected="selected"'; ?>>12 hours</option>
													<option value="86400"<?php if ( $this->data_export_interval == 86400 ) echo ' selected="selected"'; ?>>24 hours</option>
							  					</select>
							  					<label><?php _e( 'Default: 12 hours', self::DOMAIN ) ?></label>
						  					</td>
										</tr>

										<?php

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
									  
                    					<tr class="wpcronbasic">
                        					<th scope="row"><?php _e( 'Scheduler', self::DOMAIN ); ?></th>
                        					<td>
                            					<table id="wpcronbasic">
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
														  		<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $mday[ 0 ] ), FALSE ) . ' name="cronbtype" id="idcronbtype-mon" value="mon" /> ' . __( 'monthly', self::DOMAIN ); ?>
														  	</label>
													  	</td>
                                    					<td>
														  	<select name="moncronmday">
															  	<?php for ( $i = 1; $i <= 31; $i ++ ) {
																	echo '<option ' . selected( in_array( "$i", $mday, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . __( 'on',  self::DOMAIN ) . ' ' . $i . '.</option>';
																} ?>
														  	</select>
													  	</td>
                                    					<td>
														  	<select name="moncronhours">
															  	<?php for ( $i = 0; $i < 24; $i ++ ) {
																	echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?>
														  	</select>
													  	</td>
                                    					<td>
														  	<select name="moncronminutes">
															  	<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
																	echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?>
														  	</select>
													  	</td>
                                					</tr>
                                					<tr>
                                    					<td>
													  		<label for="idcronbtype-week">
														  		<?php echo '<input class="radio" type="radio"' . checked( TRUE, is_numeric( $wday[ 0 ] ), FALSE ) . ' name="cronbtype" id="idcronbtype-week" value="week" /> ' . __( 'weekly', self::DOMAIN ); ?>
													  		</label>
														</td>
                                    					<td>
														  	<select name="weekcronwday">
																<?php echo '<option ' . selected( in_array( "0", $wday, TRUE ), TRUE, FALSE ) . '  value="0" />' . __( 'Sunday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "1", $wday, TRUE ), TRUE, FALSE ) . '  value="1" />' . __( 'Monday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "2", $wday, TRUE ), TRUE, FALSE ) . '  value="2" />' . __( 'Tuesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "3", $wday, TRUE ), TRUE, FALSE ) . '  value="3" />' . __( 'Wednesday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "4", $wday, TRUE ), TRUE, FALSE ) . '  value="4" />' . __( 'Thursday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "5", $wday, TRUE ), TRUE, FALSE ) . '  value="5" />' . __( 'Friday', self::DOMAIN ) . '</option>';
																	echo '<option ' . selected( in_array( "6", $wday, TRUE ), TRUE, FALSE ) . '  value="6" />' . __( 'Saturday', self::DOMAIN ) . '</option>'; ?>
                                    						</select>
													  	</td>
                                    					<td>
														  	<select name="weekcronhours">
															  	<?php for ( $i = 0; $i < 24; $i ++ ) {
																	echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?>
														  	</select>
													  	</td>
                                    					<td>
														  	<select name="weekcronminutes">
															  	<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
																	echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?></select>
													  	</td>
                                					</tr>
                                					<tr>
                                    					<td>
														  	<label for="idcronbtype-day">
															  	<?php echo '<input class="radio" type="radio"' . checked( "**", $mday[ 0 ] . $wday[ 0 ], FALSE ) . ' name="cronbtype" id="idcronbtype-day" value="day" /> ' . __( 'daily', self::DOMAIN ); ?>
														  	</label>
													  	</td>
                                    					<td>
													  	</td>
                                    					<td>
														  	<select name="daycronhours">
															 	<?php for ( $i = 0; $i < 24; $i ++ ) {
																	echo '<option ' . selected( in_array( "$i", $hours, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?>
														  	</select>
													  	</td>
                                    					<td>
														  	<select name="daycronminutes">
															  	<?php for ( $i = 0; $i < 60; $i = $i + 5 ) {
																	echo '<option ' . selected( in_array( "$i", $minutes, TRUE ), TRUE, FALSE ) . '  value="' . $i . '" />' . $i . '</option>';
																} ?>
														  	</select>
													  	</td>
                                					</tr>
                                					<tr>
                                    					<td>
														  	<label for="idcronbtype-hour">
															  	<?php echo '<input class="radio" type="radio"' . checked( "*", $hours[ 0 ], FALSE, FALSE ) . ' name="cronbtype" id="idcronbtype-hour" value="hour" /> ' . __( 'hourly', self::DOMAIN ); ?>
														  	</label>
													  	</td>
                                    					<td></td>
                                    					<td></td>
                                    					<td>
														  	<select name="hourcronminutes">
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
										<input type="submit" class="button button-primary" name="update_all_options" value="<?php _e( 'Update All Options', self::DOMAIN ) ?>" />
			  						</div>								  
						  		</div>
						  	</div>
						</form>
					</div>  
					<div class="metabox-holder">
					  	<div id="exported-file" class="postbox">					  
							<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle"><span><?php _e('Exported File', self::DOMAIN) ?></span></h3>  
							<div class="inside">
							  	<table class="form-table">
								  	<tbody>
									  	<tr>
										  	<th><?php _e( 'Disk usage of exported file', self::DOMAIN ) ?></th>
										  	<td>
							  				<?php
												$abs_path = WP_PLUGIN_DIR . '/sns-count-cache/data/sns-count-cache-data.csv'; 
												$file_size = Common_Util::get_file_size( $abs_path );

												if ( isset( $file_size ) ) {
												  	echo $file_size;
												} else {
												  	_e('No exported file', self::DOMAIN);
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
								  				<th><?php _e( 'Manual export', self::DOMAIN ) ?></th>
												<td>
                              						<input type="submit" class="button" name="export_data" value="<?php _e( 'Export', self::DOMAIN ) ?>" />
							  						<br>
							  						<span class="description">Export share count to a csv file.</span>
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
								  				<th>Reset of exported file</th>
												<td>
                              						<input type="submit" class="button" name="reset_data" value="<?php _e( 'Reset', self::DOMAIN ) ?>" />
							  						<br>
							  						<span class="description">Clear exported csv file.</span>
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
								  				<th>Download of exported file</th>
												<td>
                              						<input type="submit" class="button" name="download_data" value="<?php _e( 'Download', self::DOMAIN ) ?>" />
							  						<br>
							  						<span class="description">Download the exported csv file.</span>
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
