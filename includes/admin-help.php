<?php
/*
admin-help.php

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

?>

	<div class="wrap">
	  	<h2><a href="admin.php?page=scc-help"><?php _e( 'SNS Count Cache', self::DOMAIN ); ?></a></h2>
		<div class="sns-cnt-cache">

			  		<h3 class="nav-tab-wrapper">
						<a class="nav-tab" href="admin.php?page=scc-dashboard"><?php _e( 'Dashboard', self::DOMAIN ); ?></a>
						<a class="nav-tab" href="admin.php?page=scc-cache-status"><?php _e( 'Cache Status', self::DOMAIN ); ?></a>
						<a class="nav-tab" href="admin.php?page=scc-share-count"><?php _e( 'Share Count', self::DOMAIN ); ?></a>
					  	<?php if ( $this->share_variation_analysis_mode !== self::OPT_SHARE_VARIATION_ANALYSIS_NONE ) { ?>
						<a class="nav-tab" href="admin.php?page=scc-hot-content"><?php _e( 'Hot Content', self::DOMAIN ); ?></a>
					  	<?php } ?>
						<a class="nav-tab" href="admin.php?page=scc-setting"><?php _e( 'Setting', self::DOMAIN ) ?></a>
						<a class="nav-tab nav-tab-active" href="admin.php?page=scc-help"><?php _e( 'Help', self::DOMAIN ); ?></a>
			  		</h3>
		  
					<div class="metabox-holder">
						<div id="share-site-summary" class="postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
						  	<h3 class="hndle"><span><?php _e( 'Help', self::DOMAIN ); ?></span></h3>  	
							<div class="inside">
		  
			
					<h4><?php _e( 'What is WordPress plugin SNS Cout Cache?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'WordPress plugin SNS Count Cache is a plugin whitch helps you to shorten page loading time when you display share counts. This plugin gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. The share count can be retrieved not through network but through the cache using given functions.', self::DOMAIN ); ?></p>
					<h4><?php _e( 'How often does this plugin get and cache share counts?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'Although this plugin gets share count of 20 posts at a time every 10 minutes by default, you can modify the setting in the "Setting" tab in the administration page.', self::DOMAIN ); ?></p>
					<h4><?php _e( 'How can I know whether share count of each post is cached or not?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'Cache status is described in the "Cache Status" tab in the administration page.', self::DOMAIN ); ?></p>
					<h4><?php _e( 'How can I get share count from the cache?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'The share count is retrieved from the cache using the following functions in the WordPress loop such as query_posts(), get_posts() and WP_Query().', self::DOMAIN ); ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Function', self::DOMAIN ); ?></th>
								<th><?php _e( 'Description', self::DOMAIN ); ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>scc_get_share_twitter()</td><td><?php _e( 'Twitter share count is returned from cache.', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_get_share_facebook()</td><td><?php _e( 'Facebook share count is returned from cache.', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_get_share_gplus()</td><td><?php _e( 'Google Plus share count is returned from cache.', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_get_share_hatebu()</td><td><?php _e( 'Hatena Bookmark share count is returned from cache.', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_get_share_pocket()</td><td><?php _e( 'Pocket share count is returned from cache.', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_get_share_total()</td><td><?php _e( 'Total share count of selected SNS is returned from cache.', self::DOMAIN ); ?></td></tr>
						</tbody>
		  			</table>
					<h4><?php _e( 'How can I get follow count from the cache?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'The follow count is retrieved from the cache using the following functions.', self::DOMAIN ); ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Function', self::DOMAIN ); ?></th>
								<th><?php _e( 'Description', self::DOMAIN ); ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>scc_get_follow_feedly()</td><td><?php _e( 'Feedly follow count is returned from cache.', self::DOMAIN ); ?></td></tr>
						</tbody>
		  			</table>					
					<h4><?php _e( 'Example Code', self::DOMAIN ); ?></h4>
					<?php _e( 'The code below describes a simple example which displays share count and follower count using the above functions for each post.', self::DOMAIN ); ?>
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

            if(function_exists('scc_get_share_twitter') &amp;&amp;
                function_exists('scc_get_share_facebook') &amp;&amp;
                function_exists('scc_get_share_gplus') &amp;&amp;
                function_exists('scc_get_share_pocket') &amp;&amp;
                function_exists('scc_get_share_total') &amp;&amp;
                function_exists('scc_get_follow_feedly') 
            ){				

            ?&gt;

            &lt;!-- 
            In WordPress loop, you can use the given function
            in order to get share count for current post and follower count. 
            --&gt;

            &lt;p&gt;Twitter: &lt;?php echo scc_get_share_twitter(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Facebook: &lt;?php echo scc_get_share_facebook(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Google Plus: &lt;?php echo scc_get_share_gplus(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Pocket: &lt;?php echo scc_get_share_pocket(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Total: &lt;?php echo scc_get_share_total(); ?&gt;&lt;/p&gt;
            &lt;p&gt;Feedly: &lt;?php echo scc_get_follow_feedly(); ?&gt;&lt;/p&gt;	

            &lt;?php
            }
        }
    }
    wp_reset_postdata();
?&gt;</pre>
					<h4><?php _e( 'How can I access specific custom field containing each share count?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'Custom fields including share count are accessed using the following meta keys.', self::DOMAIN ); ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Meta Key', self::DOMAIN ); ?></th>
								<th><?php _e( 'Description', self::DOMAIN ); ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>scc_share_count_twitter</td><td><?php _e( 'A meta key for Twitter share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_count_facebook</td><td><?php _e( 'A meta key for Facebook share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_count_google+</td><td><?php _e( 'A meta key for Google Plus share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_count_hatebu</td><td><?php _e( 'A meta key for Hatena Bookmark share count', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_share_count_pocket</td><td><?php _e( 'A meta key for Pocket share count', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_share_count_total</td><td><?php _e( 'A meta key for total share count', self::DOMAIN ); ?></td></tr>
						</tbody>
		  			</table>
					<h4><?php _e( 'How can I access specific custom field containing each variation of share count?', self::DOMAIN ); ?></h4>
					<p><?php _e( 'Custom fields including variation of share count are accessed using the following meta keys.', self::DOMAIN ); ?></p>
		  			<table class="view-table">
						<thead>
			  				<tr>
								<th><?php _e( 'Meta Key', self::DOMAIN ); ?></th>
								<th><?php _e( 'Description', self::DOMAIN ); ?></th>
			  				</tr>
						</thead>
						<tbody>
			  				<tr><td>scc_share_delta_twitter</td><td><?php _e( 'A meta key for variation of Twitter share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_delta_facebook</td><td><?php _e( 'A meta key for variation of Facebook share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_delta_google+</td><td><?php _e( 'A meta key for variation of Google Plus share count', self::DOMAIN ); ?></td></tr>
			  				<tr><td>scc_share_delta_hatebu</td><td><?php _e( 'A meta key for variation of Hatena Bookmark share count', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_share_delta_pocket</td><td><?php _e( 'A meta key for variation of Pocket share count', self::DOMAIN ); ?></td></tr>
						  	<tr><td>scc_share_delta_total</td><td><?php _e( 'A meta key for variation of total share count', self::DOMAIN ); ?></td></tr>
						</tbody>
		  			</table>
						  	</div>								  								  
						 </div>
			  		</div>				  
			
		</div>
	</div>
				

