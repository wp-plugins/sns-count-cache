<?php
/*
admin.php

Description: Option page implementation
Version: 0.3.0
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


				  	
	if( isset( $_POST["action"] ) && $_POST["action"] === 'register' ) {
		$check_interval = $_POST["check_interval"];
		$posts_per_check = $_POST["posts_per_check"];
		$dynamic_cache = $_POST["dynamic_cache"];
		$new_content_term = $_POST["new_content_term"];
						  
		$base_cache_target_twitter = $_POST["base_cache_target_twitter"];
		$base_cache_target_facebook = $_POST["base_cache_target_facebook"];
		$base_cache_target_gplus = $_POST["base_cache_target_gplus"];
		$base_cache_target_pocket = $_POST["base_cache_target_pocket"];
		$base_cache_target_hatebu = $_POST["base_cache_target_hatebu"];
		  					
		if ( isset( $check_interval ) && $check_interval && is_numeric( $check_interval ) ) {
			update_option(self::DB_CHECK_INTERVAL, $check_interval);
		}
		if ( isset( $posts_per_check ) && $posts_per_check && is_numeric( $posts_per_check ) ) {
			update_option(self::DB_POSTS_PER_CHECK, $posts_per_check);
		}
		if ( isset( $dynamic_cache ) ) {
			update_option(self::DB_DYNAMIC_CACHE, $dynamic_cache);
		} 
		if ( isset( $new_content_term ) && $new_content_term && is_numeric( $new_content_term ) ) {
			update_option(self::DB_NEW_CONTENT_TERM, $new_content_term);
		}
						  
		if (isset($base_cache_target_twitter) && $base_cache_target_twitter) {
			$base_cache_target[self::REF_TWITTER] = true;
		}
		if ( isset( $base_cache_target_facebook ) && $base_cache_target_facebook ) {
			$base_cache_target[self::REF_FACEBOOK] = true;
		}
		if ( isset( $base_cache_target_gplus ) && $base_cache_target_gplus ) {
			$base_cache_target[self::REF_GPLUS] = true;
		}
		if ( isset( $base_cache_target_pocket ) && $base_cache_target_pocket ) {
		  	if ( extension_loaded( 'xml' ) ) {
				$base_cache_target[self::REF_POCKET] = true;
			}
		}
		if ( isset( $base_cache_target_hatebu ) && $base_cache_target_hatebu ) {
			$base_cache_target[self::REF_HATEBU] = true;
		}						  
						  
		if ( ! empty( $base_cache_target ) ) {
			update_option(self::DB_CACHE_TARGET,$base_cache_target);
		}
						  
		$this->reactivate_plugin();
	}

	$check_interval = get_option( self::DB_CHECK_INTERVAL );
	$posts_per_check = get_option( self::DB_POSTS_PER_CHECK );
	$dynamic_cache = get_option( self::DB_DYNAMIC_CACHE );
	$new_content_term = get_option( self::DB_NEW_CONTENT_TERM );
	$base_cache_target = get_option( self::DB_CACHE_TARGET );

	$check_interval = ! empty( $check_interval ) ? intval( $check_interval ) : self::OPT_BASE_CHECK_INTERVAL;
	$posts_per_check = ! empty( $posts_per_check ) ? intval( $posts_per_check ) : self::OPT_BASE_POSTS_PER_CHECK; 
	$dynamic_cache = isset( $dynamic_cache ) ? intval( $dynamic_cache ) : self::OPT_ACCESS_BASED_CACHE_NONE;
	$new_content_term = ! empty( $new_content_term ) ? intval( $new_content_term ) : self::OPT_RUSH_NEW_CONTENT_TERM;

	if ( empty( $base_cache_target ) ) {
		$base_cache_target[self::REF_TWITTER] = true;
		$base_cache_target[self::REF_FACEBOOK] = true;
		$base_cache_target[self::REF_GPLUS] = true;
		$base_cache_target[self::REF_POCKET] = true;
		$base_cache_target[self::REF_HATEBU] = true;
	}
						
	$count = 1;
	$query_args = array(
		'post_type' => array( 'post', 'page' ),
		'post_status' => 'publish',
		'nopaging' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
		);

	$posts_query = new WP_Query( $query_args );
	?>
	<div class="wrap">
		<h2>SNS Count Cache</h2>
			<div class="sns-cnt-cache">
		  	<ul class="tab">
				<li class="select"><?php _e( 'Cache Status', self::DOMAIN ) ?></li>
			  	<li><?php _e( 'Share Count', self::DOMAIN ) ?></li>
			  	<li><?php _e( 'Setting', self::DOMAIN ) ?></li>
 				<li><?php _e( 'Help', self::DOMAIN ) ?></li>
		  	</ul>
		  	<ul class="content">
				<li>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th>No.</th>
								<th><?php _e( 'Target Content', self::DOMAIN ) ?></th>
								<th><?php _e( 'Primary Cache', self::DOMAIN ) ?></th>
							  	<th><?php _e( 'Secondary Cache', self::DOMAIN ) ?></th>

			  				</tr>
						</thead>
						<tbody>

						<?php
						if ( $posts_query->have_posts() ) {
							while ( $posts_query->have_posts() ) {
								$posts_query->the_post();			  
						?>
			  				<tr>
								<td><?php echo $count; ?></td>
							  	<td><a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" target="_blank"><?php echo esc_html( get_permalink( get_the_ID() ) ); ?></a></td>
							<?php  
								$transient_id = self::OPT_BASE_TRANSIENT_PREFIX . get_the_ID();
							  							  
								if ( false === ( $sns_counts = get_transient( $transient_id ) ) ) {								  
					  				echo '<td class="not-cached">';
									_e( 'not cached', self::DOMAIN );
					  				echo '</td>';
								} else {
					  				echo '<td class="cached">';
									_e( 'cached', self::DOMAIN );
					  				echo '</td>';
								}
							  
							  	$second_cache_flag = true;
							  	foreach ( $base_cache_target as $key => $value ) {
									if ( $value ) {								
							  
							    		$meta_key = SNS_Count_Cache::OPT_2ND_META_KEY_PREFIX . strtolower( $key );
							  			$sns_count = get_post_meta( get_the_ID(), $meta_key, true );
									 	
									  	if ( $sns_count ==  -1 ) {
										  	$second_cache_flag = false;
									  	}
									}
								}
								if ( $second_cache_flag ) {
					  				echo '<td class="cached">';
									_e( 'cached', self::DOMAIN );
					  				echo '</td>';
								} else {
					  				echo '<td class="not-cached">';
									_e( 'not cached', self::DOMAIN );
					  				echo '</td>';
								}										  
				
							?>
			  				</tr>

						<?php
								$count++;

							}
						}
						wp_reset_postdata();
						?>
						</tbody>
		  			</table>
				</li>
				<li class="hide">
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th>No.</th>
								<th><?php _e( 'Content', self::DOMAIN ) ?></th>
							  	<?php
						  
						  			foreach ( $base_cache_target as $key => $value ){
									  	if ( $value ) {
											echo '<th>' . $key . '</th>';		
									  	}
									}

					  			?>
			  				</tr>
						</thead>
						<tbody>

						<?php
						$count = 1;
						if ( $posts_query->have_posts() ) {
							while ( $posts_query->have_posts() ) {
								$posts_query->the_post();			  
						?>
			  				<tr>
								<td><?php echo $count; ?></td>
							  	<td><a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( get_the_ID() ) ); ?></a></td>
							<?php  
								$transient_id = self::OPT_BASE_TRANSIENT_PREFIX . get_the_ID();
							  							  
								if ( false === ( $sns_counts = get_transient( $transient_id ) ) ) {
						  
						  			foreach ( $base_cache_target as $key => $value ) {
									  	if( $value ){
										  /**
					  						echo '<td class="not-cached share-count">';
								  			_e( 'N/A', self::DOMAIN );
					  						echo '</td>';
											*/
											
							    			$meta_key = SNS_Count_Cache::OPT_2ND_META_KEY_PREFIX . strtolower( $key );
							  				$sns_counts[$key] = get_post_meta( get_the_ID(), $meta_key, true );
								  				
											if ( isset( $sns_counts[$key] ) && $sns_counts[$key] >= 0 ) {
					  							echo '<td class="share-count">';
								  				echo $sns_counts[$key];
					  							echo '</td>';
											} else {
					  							echo '<td class="not-cached share-count">';
												_e( 'N/A', self::DOMAIN );
					  							echo '</td>';
											}										  								 										  
									  	}
									}								  

								} else {
								  	  
						  			foreach ( $base_cache_target as $key => $value ) {
									  	if ( $value ) {										  
								  			if ( isset( $sns_counts[$key] ) ) {
					  							echo '<td class="share-count">';
								  				echo $sns_counts[$key];
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
								$count++;

							}
						}
						wp_reset_postdata();
						?>
						</tbody>
		  			</table>
				</li>
			  	<li class="hide">
				  	<h3><?php _e( 'Current Parameter', self::DOMAIN ) ?></h3>
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
						 	<td><?php _e( 'Base Cache', self::DOMAIN) ?></td><td><?php _e('Target SNS', self::DOMAIN ) ?></td>
						  	<td>
							  	<?php
					  				$target_sns = array();
					  				if ( isset( $base_cache_target[self::REF_TWITTER] ) && $base_cache_target[self::REF_TWITTER] ) {
									  	$target_sns[] = 'Twitter';
									}
					  				if ( isset( $base_cache_target[self::REF_FACEBOOK] ) && $base_cache_target[self::REF_FACEBOOK] ) {
									  	$target_sns[] = 'Facebook';
									}
					  				if ( isset( $base_cache_target[self::REF_GPLUS] ) && $base_cache_target[self::REF_GPLUS] ) {
									  	$target_sns[] = 'Google+';
									}
					  				if ( isset( $base_cache_target[self::REF_POCKET] ) && $base_cache_target[self::REF_POCKET] ) {
									  	$target_sns[] = 'Pocket';
									}
					  				if ( isset( $base_cache_target[self::REF_HATEBU] ) && $base_cache_target[self::REF_HATEBU] ) {
									  	$target_sns[] = 'Hatena Bookmark';
									}
							  		echo implode( ", ", $target_sns );
							  	?>
						  	</td>
			  			</tr>						  
			  			<tr>
						 	<td><?php _e( 'Base Cache', self::DOMAIN ) ?></td>
						  	<td><?php _e( 'Interval cheking and caching SNS share count', self::DOMAIN ) ?></td>
						  	<td><?php echo $check_interval . ' seconds'; ?></td>
			  			</tr>
			  			<tr>
						  	<td><?php _e( 'Base Cache', self::DOMAIN ) ?></td>
						  	<td><?php _e( 'Number of posts to check at a time', self::DOMAIN ) ?></td>
						  	<td><?php echo $posts_per_check . ' posts'; ?></td>
			  			</tr>
			  			<tr>
						  	<td><?php _e( 'Rush Cache', self::DOMAIN ) ?></td>
						  	<td><?php _e( 'Term considering posted content as new content', self::DOMAIN ) ?></td>
						  	<td>
							  	<?php 
								  if ( $new_content_term == 1 ) {
								  	echo $new_content_term . ' day';
								  } else if ( $new_content_term > 1 ) {
									echo $new_content_term . ' days';
								  }
							  	?>
						  	</td>
			  			</tr>						  
			  			<tr>
							<td><?php _e( 'Dynamic Cache', self::DOMAIN) ?></td><td><?php _e( 'Dynamic caching based on user access', self::DOMAIN ) ?></td><td>
						  	<?php
					  			switch ( $dynamic_cache ) {
		  							case SNS_Count_Cache::OPT_ACCESS_BASED_CACHE_NONE:
		  								_e( 'disabled', self::DOMAIN );
		  								break;
		  							case SNS_Count_Cache::OPT_ACCESS_BASED_SYNC_CACHE:
		  								_e( 'enabled (Synchronous Cache)', self::DOMAIN );
		  								break;
		  							case SNS_Count_Cache::OPT_ACCESS_BASED_ASYNC_CACHE:
		  								_e( 'enabled (Asynchronous Cache)', self::DOMAIN );
								  	case SNS_Count_Cache::OPT_ACCESS_BASED_2ND_CACHE:
										_e( 'enabled (Asynchronous 2nd Cache)', self::DOMAIN );								  
		  								break;
								}
						  	?>
						  	</td>
			  			</tr>						  
						</tbody>
		  			</table>
				  	<h3><?php _e( 'Register New Parameter', self::DOMAIN ) ?></h3>
		  			<p><?php _e( 'You can register or modify required parameters at the following form.', self::DOMAIN ) ?></p>
					<form action="" method="post">
					  	<table class="form-table">
						<tr><th class="section-label"><?php _e('Base Cache', self::DOMAIN) ?></th></tr>
						<tr>
						  	<th><label><?php _e( 'Target SNS', self::DOMAIN ) ?></label></th>
						  	<td>
							  	<div class="sns-check">
									<input type="checkbox" value="1" name="base_cache_target_twitter"<?php if ( $base_cache_target[self::REF_TWITTER] ) echo ' checked="checked"'; ?> />
						 			<label><?php _e( 'Twitter', self::DOMAIN ) ?></label>						  
							  	</div>
							  	<div class="sns-check">
								 	<input type="checkbox" value="1" name="base_cache_target_facebook"<?php if ( $base_cache_target[self::REF_FACEBOOK] ) echo ' checked="checked"'; ?> />
							  		<label><?php _e( 'Facebook', self::DOMAIN ) ?></label>						 
							  	</div>
							  	<div class="sns-check">
									<input type="checkbox" value="1" name="base_cache_target_gplus"<?php if ( $base_cache_target[self::REF_GPLUS] ) echo ' checked="checked"'; ?> />
						  			<label><?php _e( 'Google+', self::DOMAIN ) ?></label>
							  	</div>
							  	<div class="sns-check">
								  	<input type="checkbox" value="1" name="base_cache_target_pocket"<?php if ( $base_cache_target[self::REF_POCKET] ) echo ' checked="checked"'; ?> />
							  		<label><?php _e( 'Pocket', self::DOMAIN ) ?></label>
							  	</div>
							  	<div class="sns-check">
								  	<input type="checkbox" value="1" name="base_cache_target_hatebu"<?php if ( $base_cache_target[self::REF_HATEBU] ) echo ' checked="checked"'; ?> />
							  		<label><?php _e( 'Hatena Bookmark', self::DOMAIN ) ?></label>
							  	</div>
						  	</td>
						</tr>						  
			  			<tr>
						  	<th><label><?php _e( 'Interval cheking and caching SNS share count (sec)', self::DOMAIN ) ?></label></th>
						  	<td>
							  	<input type="text" class="text" name="check_interval" size="20" value="" />
							  	<label><?php _e( 'Default: 600', self::DOMAIN ) ?></label>
						  	</td>
			  			</tr>
			  			<tr>
						  	<th><label><?php _e( 'Number of posts to check at a time (posts)', self::DOMAIN ) ?></label></th>
						 	<td>
							  	<input type="text" class="text" name="posts_per_check" size="20" value="" />
							  	<label><?php _e( 'Default: 20', self::DOMAIN ) ?></label>
						  	</td>
			  			</tr>
						<tr><th class="section-label"><?php _e('Rush Cache', self::DOMAIN) ?></th></tr>
			  			<tr>
						  	<th><label><?php _e( 'Term considering posted content as new content', self::DOMAIN ) ?></label></th>
						  	<td>
							  <select name="new_content_term">
								<option value="1"<?php if ( $new_content_term == 1 ) echo ' selected="selected"'; ?>>1 day</option>
								<option value="2"<?php if ( $new_content_term == 2 ) echo ' selected="selected"'; ?>>2 days</option>
								<option value="3"<?php if ( $new_content_term == 3 ) echo ' selected="selected"'; ?>>3 days</option>
								<option value="4"<?php if ( $new_content_term == 4 ) echo ' selected="selected"'; ?>>4 days</option>
								<option value="5"<?php if ( $new_content_term == 5 ) echo ' selected="selected"'; ?>>5 days</option>
								<option value="6"<?php if ( $new_content_term == 6 ) echo ' selected="selected"'; ?>>6 days</option>
								<option value="7"<?php if ( $new_content_term == 7 ) echo ' selected="selected"'; ?>>7 days</option>
								<option value="8"<?php if ( $new_content_term == 8 ) echo ' selected="selected"'; ?>>8 days</option>
								<option value="9"<?php if ( $new_content_term == 9 ) echo ' selected="selected"'; ?>>9 days</option>
								<option value="10"<?php if ( $new_content_term == 10 ) echo ' selected="selected"'; ?>>10 days</option>
								<option value="11"<?php if ( $new_content_term == 11 ) echo ' selected="selected"'; ?>>11 days</option>
								<option value="12"<?php if ( $new_content_term == 12 ) echo ' selected="selected"'; ?>>12 days</option>
								<option value="13"<?php if ( $new_content_term == 13 ) echo ' selected="selected"'; ?>>13 days</option>
								<option value="14"<?php if ( $new_content_term == 14 ) echo ' selected="selected"'; ?>>14 days</option>
							  </select>
							  <label><?php _e( 'Default: 3 days', self::DOMAIN ) ?></label>
						  	</td>
						</tr>
						<tr><th class="section-label"><?php _e( 'Dynamic Cache', self::DOMAIN ) ?></th></tr>
					  	<tr>
						  	<th><label><?php _e( 'Dynamic caching based on user access', self::DOMAIN ) ?></label></th>
						  	<td>
							  <select name="dynamic cache">
								<option value="0"<?php if ( $dynamic_cache == 0 ) echo ' selected="selected"'; ?>><?php _e( 'None', self::DOMAIN ) ?></option>
								<option value="1"<?php if ( $dynamic_cache == 1 ) echo ' selected="selected"'; ?>><?php _e( 'Synchronous Cache', self::DOMAIN ) ?></option>
								<option value="2"<?php if ( $dynamic_cache == 2 ) echo ' selected="selected"'; ?>><?php _e( 'Asynchronous Cache', self::DOMAIN ) ?></option>
								<option value="3"<?php if ( $dynamic_cache == 3 ) echo ' selected="selected"'; ?>><?php _e( 'Asynchronous 2nd Cache', self::DOMAIN ) ?></option>
							  </select>
							  <label><?php _e('Default: None', self::DOMAIN) ?></label>
						  	</td>
					  	</tr>
						</table>
			  			<input type="hidden" class="text" name="action" value="register" />
			  			<div class="submit-button">
							<input type="submit" class="button button-primary" value="<?php _e( 'Update Options', self::DOMAIN ) ?>" />
			  			</div>
					</form>	  	
				  
			  	</li>
				<li class="hide">
				  <div>
					<h3><?php _e( 'What is SNS Cout Cache?', self::DOMAIN ) ?></h3>
					<p><?php _e( 'SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.', self::DOMAIN ) ?></p>
					<h3><?php _e( 'How often does this plugin get and cache share count?', self::DOMAIN) ?></h3>
					<p><?php _e( 'Although this plugin gets share count of 20 posts at a time every 10 minutes by default, you can modify the setting in the "Setting" tab in the setting page.', self::DOMAIN ) ?></p>
					<h3><?php _e( 'How can I know whether share cout of each post is cached or not?', self::DOMAIN) ?></h3>
					<p><?php _e( 'Cache status is described in the "Cache Status" tab in the setting page.', self::DOMAIN ) ?></p>
					<h3><?php _e( 'How can I get share count from the cache?', self::DOMAIN) ?></h3>
					<p><?php _e( 'The share count is retrieved from the cache using the following functions in the WordPress loop such as query_posts(), get_posts() and WP_Query().', self::DOMAIN ) ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Function', self::DOMAIN ) ?></th>
								<th><?php _e( 'Description', self::DOMAIN ) ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>get_scc_twitter()</td><td><?php _e( 'Twitter share count is returned from cache.', self::DOMAIN ) ?></td></tr>
			  				<tr><td>get_scc_facebook()</td><td><?php _e( 'Facebook share count is returned from cache.', self::DOMAIN ) ?></td></tr>
			  				<tr><td>get_scc_gplus()</td><td><?php _e( 'Google Plus share count is returned from cache.', self::DOMAIN ) ?></td></tr>
			  				<tr><td>get_scc_hatebu()</td><td><?php _e( 'Hatena Bookmark share count is returned from cache.', self::DOMAIN ) ?></td></tr>
						  	<tr><td>get_scc_pocket()</td><td><?php _e( 'Pocket share count is returned from cache.', self::DOMAIN ) ?></td></tr>
						</tbody>
		  			</table>
					<h3><?php _e( 'Example Code', self::DOMAIN ) ?></h3>
					<?php _e( 'The code below describes a simple example which displays share count of Twitter, Facebook, Google Plus for each post.', self::DOMAIN ) ?>
<pre class="prettyprint">&lt;?php
    $query_args = array(
        &#039;post_type&#039; =&gt; &#039;post&#039;,
        &#039;post_status&#039; =&gt; &#039;publish&#039;,
        &#039;posts_per_page&#039; =&gt; 5
        );

    $posts_query = new WP_Query( $query_args );

    if ( $posts_query-&gt;have_posts() ) {
        while ( $posts_query-&gt;have_posts() ){
            $posts_query-&gt;the_post();
            ?&gt;

            &lt;!-- 
            In WordPress loop, you can use the given function
            in order to get share count for current post. 
            --&gt;
            &lt;p&gt;Twitter: &lt;?php echo get_scc_twitter(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Facebook: &lt;?php echo get_scc_facebook(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Google Plus: &lt;?php echo get_scc_gplus(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Pocket: &lt;?php echo get_scc_pocket(); ?&gt;&lt;/p&gt;	

            &lt;?php        
        }
    }
    wp_reset_postdata();
?&gt;</pre>
					<h3><?php _e( 'How can I access specific custom field containing each share count?', self::DOMAIN) ?></h3>
					<p><?php _e( 'The custom field including share count is accessed using the following meta keys.', self::DOMAIN ) ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Meta Key', self::DOMAIN ) ?></th>
								<th><?php _e( 'Description', self::DOMAIN ) ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>scc_share_count_twitter</td><td><?php _e( 'A meta key for Twitter share count', self::DOMAIN ) ?></td></tr>
			  				<tr><td>scc_share_count_facebook</td><td><?php _e( 'A meta key for Facebook share count', self::DOMAIN ) ?></td></tr>
			  				<tr><td>scc_share_count_google+</td><td><?php _e( 'A meta key for Google Plus share count', self::DOMAIN ) ?></td></tr>
			  				<tr><td>scc_share_count_hatebu</td><td><?php _e( 'A meta key for Hatena Bookmark share count', self::DOMAIN ) ?></td></tr>
						  	<tr><td>scc_share_count_pocket</td><td><?php _e( 'A meta key for Pocket share count', self::DOMAIN ) ?></td></tr>
						</tbody>
		  			</table>
				  </div>
				</li>
	  		</ul>
		</div>
     </div>
