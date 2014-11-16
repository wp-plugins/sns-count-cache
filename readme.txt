=== SNS Count Cache ===
Contributors: marubon
Donate link: 
Tags: performance, SNS, social, cache
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 0.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
PHP Version: 5.3+

This plugin gets and caches SNS share count in the background, and provides functions to access the cache.


== Description ==

SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. 
This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.

The following shows functions to get share count from the cache:

* get_scc_twitter()
* get_scc_facebook()
* get_scc_gplus()
* get_scc_pocket()
* get_scc_hatebu()

The following describes meta keys to get share count from custom field.

* scc_share_count_twitter
* scc_share_count_facebook
* scc_share_count_google+
* scc_share_count_pocket
* scc_share_count_hatebu

== Installation ==

1. Download zip archive file from this repository.

2. Login as an administrator to your WordPress admin page. 
   Using the "Add New" menu option under the "Plugins" section of the navigation, 
   Click the "Upload" link, find the .zip file you download and then click "Install Now". 
   You can also unzip and upload the plugin to your plugins directory (i.e. wp-content/plugins/) through FTP/SFTP. 

3. Finally, activate the plugin on the "Plugins" page.

== Frequently Asked Questions ==
There are no questions.

== Screenshots ==
1. Cache status is described in setting page
2. Share count for each post can be verviewed
3. Described parameters can be modified in this page
4. Help page shows available functions to access the cache  

== Changelog ==

= 0.1.0 =
* Initial working version.

= 0.2.0 =
* Added: function to modify check interval of SNS share count and its number of target posts and pages at a time
* Added: function to cache SNS share count for latest posts and pages preferentially
* Added: function to cache SNS share count based on user access dynamically 

= 0.3.0 =
* Added: Pocket was included as one of cache targets.
* Added: function to modify target SNS that share count is cached
* Added: function to modify term considering posted content as new content in the rush cache.
* Added: page to display share count for specified all tagets.
* Added: function to query pages and posts based on SNS share count using specific custom fields in WP_Query and so on.

== Upgrade Notice ==
There is no upgrade notice.

== Arbitrary section ==


