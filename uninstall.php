<?php
//if uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

$db_check_interval = 'scc_check_interval';
$db_follow_check_interval = 'scc_follow_check_interval';
$db_posts_per_check = 'scc_posts_per_check';
$db_dynamic_cache = 'scc_dynamic_cache';
$db_dynamic_cache_mode = 'scc_dynamic_cache_mode';
$db_new_content_term = 'scc_new_content_term';
$db_cache_target = 'scc_cache_target';
$db_data_export = 'scc_data_export';
$db_data_export_mode = 'scc_data_export_mode';
$db_data_export_interval = 'scc_data_export_interval';
$db_custom_post_types = 'scc_custom_post_types';
$db_follow_cache_target = 'scc_follow_cache_target';
$db_data_export_schedule = 'scc_data_export_schedule';
$db_http_migration_mode = 'scc_http_migration_mode';
$db_scheme_migration_mode = 'scc_scheme_migration_mode';

// For Single site
if( ! is_multisite() ) {
  	delete_option( $db_custom_post_types );
    delete_option( $db_check_interval );
  	delete_option( $db_follow_check_interval );
    delete_option( $db_posts_per_check );
  	delete_option( $db_dynamic_cache );
  	delete_option( $db_dynamic_cache_mode );
  	delete_option( $db_new_content_term );
  	delete_option( $db_cache_target );
  	delete_option( $db_data_export );
  	delete_option( $db_data_export_mode );  
  	delete_option( $db_data_export_interval );
  	delete_option( $db_follow_cache_target );
  	delete_option( $db_data_export_schedule );
	delete_option( $db_http_migration_mode );
  	delete_option( $db_scheme_migration_mode );    
} 
// For Multisite
else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

  	foreach( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
	  
	  	delete_option( $db_custom_post_types );
        delete_option( $db_check_interval );
	  	delete_option( $db_follow_check_interval );
        delete_option( $db_posts_per_check );
	  	delete_option( $db_dynamic_cache );
  		delete_option( $db_dynamic_cache_mode );
	    delete_option( $db_new_content_term );
  		delete_option( $db_cache_target );
		delete_option( $db_data_export );
  		delete_option( $db_data_export_mode );  
  		delete_option( $db_data_export_interval );
	  	delete_option( $db_follow_cache_target );
  		delete_option( $db_data_export_schedule );
		delete_option( $db_http_migration_mode );
		delete_option( $db_scheme_migration_mode );	  
    }
    switch_to_blog( $original_blog_id );
}
?>