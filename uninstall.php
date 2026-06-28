<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}agocontact_submissions" );
delete_option( 'agocontact_settings' );
