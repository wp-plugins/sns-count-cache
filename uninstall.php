<?php
//if uninstall not called from WordPress exit
if(!defined('WP_UNINSTALL_PLUGIN')) exit();

$db_check_interval = 'scc_check_interval';
$db_posts_per_check = 'scc_posts_per_check';
$db_dynamic_cache = 'scc_dynamic_cache';
$db_new_content_term = 'scc_new_content_term';
$db_cache_target = 'scc_cache_target';
  
// For Single site
if(!is_multisite()){
    delete_option($db_check_interval);
    delete_option($db_posts_per_check);
  	delete_option($db_dynamic_cache);
  	delete_option($db_new_content_term);
  	delete_option($db_cache_target);
} 
// For Multisite
else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

  	foreach($blog_ids as $blog_id){
        switch_to_blog($blog_id);
	  
        delete_option($db_check_interval);
        delete_option($db_posts_per_check);
	  	delete_option($db_dynamic_cache);
	    delete_option($db_new_content_term);
  		delete_option($db_cache_target);	  
    }
    switch_to_blog($original_blog_id);
}
?>