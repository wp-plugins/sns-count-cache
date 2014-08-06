<?php
/*
admin.php

Description: Option page implementation
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

	$count = 1;
	$query_args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'nopaging' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
		);

	$posts_query = new WP_Query($query_args);
	?>
	<div class="wrap">
		<h2>SNS Count Cache</h2>
			<div class="sns-cnt-cache">
		  	<ul class="tab">
				<li class="select"><?php _e('Cache Status', self::DOMAIN) ?></li>
			  	<li><?php _e('Setting', self::DOMAIN) ?></li>
 				<li><?php _e('Help', self::DOMAIN) ?></li>
		  	</ul>
		  	<ul class="content">
				<li>
		  			<table>
						<thead>
			  				<tr>
								<th>No.</th>
								<th><?php _e('Post', self::DOMAIN) ?></th>
								<th><?php _e('Cache Status', self::DOMAIN) ?></th>
			  				</tr>
						</thead>
						<tbody>

						<?php
						if($posts_query->have_posts()) {
							while($posts_query->have_posts()){
								$posts_query->the_post();			  
						?>
			  				<tr>
								<td><?php echo $count; ?></td>
								<td><?php echo get_permalink(get_the_ID()); ?></td>
							<?php  
								$transient_id = self::OPT_TRANSIENT_PREFIX . get_the_ID();
								if (false === ($sns_counts = get_transient($transient_id))) {
					  				echo '<td class="not-cached">';
									_e('not cached', self::DOMAIN);
					  				echo '</td>';
								} else {
					  				echo '<td class="cached">';
									_e('cached', self::DOMAIN);
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
				  	<h3>Setting</h3>
				  	<?php
				   	  	if(isset($_POST["action"]) && $_POST["action"]==='register'){
		  
		  					$check_interval = $_POST["check_interval"];
		  					$posts_per_check = $_POST["posts_per_check"];
		  					
							if(isset($check_interval) && $check_interval && is_numeric($check_interval)){
			  					update_option(self::DB_CHECK_INTERVAL,$check_interval);
							}
							if(isset($posts_per_check) && $posts_per_check && is_numeric($posts_per_check)){
			  					update_option(self::DB_POSTS_PER_CHECK,$posts_per_check);
							}
						  
						  	$this->reactivate_plugin();
						}

						$check_interval = get_option(self::DB_CHECK_INTERVAL);
						$posts_per_check = get_option(self::DB_POSTS_PER_CHECK);

	  					$check_interval = !empty($check_interval) ? intval($check_interval) : self::OPT_CHECK_INTERVAL;
	  					$posts_per_check = !empty($posts_per_check) ? intval($posts_per_check) : self::OPT_POSTS_PER_CHECK; 

					?>
				  	<h4><?php _e('Current Parameter', self::DOMAIN) ?></h4>
		  			<p><?php _e('The following describes registered parameters.', self::DOMAIN) ?></p>
		  			<table>
						<thead>
			  			<tr>
							<th><?php _e('Name', self::DOMAIN) ?></th>
							<th><?php _e('Value', self::DOMAIN) ?></th>
			  			</tr>
						</thead>
						<tbody>
			  			<tr>
							<td><?php _e('Interval cheking and caching target data (sec)', self::DOMAIN) ?></td><td><?php echo $check_interval ?></td>
			  			</tr>
			  			<tr>
							<td><?php _e('Number of posts to check at a time (posts)', self::DOMAIN) ?></td><td><?php echo $posts_per_check ?></td>
			  			</tr>
						</tbody>
		  			</table>
				  	<h4><?php _e('Register New Parameter', self::DOMAIN) ?></h4>
		  			<p><?php _e('You can register or modify required parameters at the following form.', self::DOMAIN) ?></p>
					<form action="" method="post">
			  			<div>
							<label><?php _e('Interval cheking and caching target data', self::DOMAIN) ?></label><br />
							<input type="text" class="text" name="check_interval" size="60" value="" />
			  			</div>
			  			<div>
							<label><?php _e(' Number of posts to check at a time', self::DOMAIN) ?></label><br />
							<input type="text" class="text" name="posts_per_check" size="60" value="" />
			  			</div>
			  			<input type="hidden" class="text" name="action" value="register" />
			  			<div class="submit-button">
							<input type="submit" class="button button-primary" value="<?php _e('Register', self::DOMAIN) ?>" />
			  			</div>
					</form>	  	
				  
			  	</li>
				<li class="hide">
				  <div>
					<h3><?php _e('What is SNS Cout Cache?', self::DOMAIN) ?></h3>
					<p><?php _e('SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Hatena Bookmark and caches these count in the background. This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.', self::DOMAIN) ?></p>
					<h3><?php _e('How often does this plugin get and cache share count?', self::DOMAIN) ?></h3>
					<p><?php _e('This plugin gets share count of 20 posts at a time every 10 minutes.', self::DOMAIN) ?></p>
					<h3><?php _e('How can I know whether share cout of each post is cached or not?', self::DOMAIN) ?></h3>
					<p><?php _e('Cache status is described in the "Cache Status" tab in the setting page.', self::DOMAIN) ?></p>
					<h3><?php _e('How can I get share count from the cache?', self::DOMAIN) ?></h3>
					<p><?php _e('The share cout is retrieved from the cache using the following functions in the WordPress loop such as query_posts(), get_posts() and WP_Query().', self::DOMAIN) ?></p>
		  			<table>
						<thead>
			  				<tr>
								<th><?php _e('Function', self::DOMAIN) ?></th>
								<th><?php _e('Description', self::DOMAIN) ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>get_scc_twitter()</td><td><?php _e('Twitter share count is returned from cache.', self::DOMAIN) ?></td></tr>
			  				<tr><td>get_scc_facebook()</td><td><?php _e('Facebook share count is returned from cache.', self::DOMAIN) ?></td></tr>
			  				<tr><td>get_scc_gplus()</td><td><?php _e('Google Plus share count is returned from cache.', self::DOMAIN) ?></td></tr>
			  				<tr><td>get_scc_hatebu()</td><td><?php _e('Hatena Bookmark share count is returned from cache.', self::DOMAIN) ?></td></tr>
						</tbody>
		  			</table>
					<h3><?php _e('Example Code', self::DOMAIN) ?></h3>
					<?php _e('The code below describes a simple example which displays share count of Twitter, Facebook, Google Plus for each post.', self::DOMAIN) ?>
<pre class="prettyprint">&lt;?php
    $query_args = array(
        &#039;post_type&#039; =&gt; &#039;post&#039;,
        &#039;post_status&#039; =&gt; &#039;publish&#039;,
        &#039;posts_per_page&#039; =&gt; 5
        );

    $posts_query = new WP_Query($query_args);

    if($posts_query-&gt;have_posts()) {
        while($posts_query-&gt;have_posts()){
            $posts_query-&gt;the_post();
            ?&gt;

            &lt;!-- 
            In WordPress loop, you can use the given function
            in order to get share count for current post. 
            --&gt;
            &lt;p&gt;Twitter: &lt;?php echo get_scc_twitter(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Facebook: &lt;?php echo get_scc_facebook(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Google Plus: &lt;?php echo get_scc_gplus(); ?&gt;&lt;/p&gt;

            &lt;?php        
        }
    }
    wp_reset_postdata();
?&gt;</pre>
				  </div>
				</li>
	  		</ul>
		</div>
     </div>
