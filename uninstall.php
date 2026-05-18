<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ago_contact_submissions" );
delete_option( 'ago_contact_settings' );
