<?php
/**
 * Uninstall JeiSEO
 *
 * Removes all plugin data when uninstalled.
 *
 * @package JeiSEO
 */

// Exit if accessed directly or not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete options.
$options = array(
    'jeiseo_license_key',
    'jeiseo_license_status',
    'jeiseo_api_provider',
    'jeiseo_api_key',
    'jeiseo_auto_fix',
    'jeiseo_weekly_report',
    'jeiseo_last_audit',
    'jeiseo_audit_results',
    'jeiseo_free_audits_count',
    'jeiseo_free_content_count',
    'jeiseo_quota_reset_date',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete transients.
delete_transient( 'jeiseo_audit_cache' );
delete_transient( 'jeiseo_dashboard_stats' );

// Clear scheduled events.
$timestamp = wp_next_scheduled( 'jeiseo_weekly_report_cron' );
if ( $timestamp ) {
    wp_unschedule_event( $timestamp, 'jeiseo_weekly_report_cron' );
}

// Clean up any user meta if needed.
delete_metadata( 'user', 0, 'jeiseo_dismissed_notices', '', true );
