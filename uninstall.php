<?php
/**
 * Uninstaller for WP Rest API Cache.
 *
 * @package wp-rest-api-cache
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Delete Our Options.
delete_option( 'api_cache_pro' );

	// WPDB.
	global $wpdb;

	// Delete All Cache.
	$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
					'_transient_api_cache_pro_%',
					'_transient_timeout_api_cache_pro_%',
					'_site_transient_api_cache_pro_%',
					'_site_transient_timeout_api_cache_pro_%'
				)
			);

	// Cache Flush.
	wp_cache_flush();


