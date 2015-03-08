﻿=== SNS Count Cache ===
Contributors: marubon
Donate link: 
Tags: performance, SNS, social, cache
Requires at least: 3.7
Tested up to: 4.1.1
Stable tag: 0.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin gets and caches SNS share count in the background, and provides functions to access the cache.

== Description ==

SNS Count Cache gets share count for Twitter and Facebook, Google Plus, Pocket, Hatena Bookmark and caches these count in the background. 
This plugin may help you to shorten page loading time because the share count can be retrieved not through network but through the cache using given functions.

Notice: PHP Version 5.3+ is required in order to activate and execute this plugin.

The following shows functions to get share count from the cache:

* scc_get_share_twitter()
* scc_get_share_facebook()
* scc_get_share_gplus()
* scc_get_share_pocket()
* scc_get_share_hatebu()
* scc_get_share_total()

The following shows function to get follower count from the cache:

* scc_get_follow_feedly()

The following describes meta keys to get share count from custom field.

* scc_share_count_twitter
* scc_share_count_facebook
* scc_share_count_google+
* scc_share_count_pocket
* scc_share_count_hatebu
* scc_share_count_total

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
1. Dashboard to overview cache status and share count
2. Cache status is described in setting page
3. Share count for each post can be verviewed
4. Described parameters can be modified in this page
5. Help page shows available functions to access the cache  

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

= 0.4.0 =
* Added: admin page was totally improved.
* Added: function to sort contents based on share count was added to admin page of share count.
* Added: content of custom post type was added as share count cache target. 
* Added: number of Feedly follower was included as one of cache targets.
* Added: function to export share count data was added.
* Added: cache logic was improved.

= 0.5.0 =
* Added: function to cache share count for both old and new url in https migration. 

== Upgrade Notice ==
The following functions are deprecated.

* get_scc_twitter()
* get_scc_facebook()
* get_scc_gplus()
* get_scc_pocket()
* get_scc_hatebu()

== Arbitrary section ==


