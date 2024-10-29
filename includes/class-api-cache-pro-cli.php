<?php
/**
 * WP-CLI Support for API Cache Pro.
 *
 * @package api-cache-pro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	if ( ! class_exists( 'API_Cache_Pro_CLI' ) ) {

		/**
		 * API Cache Pro.
		 *
		 * ## OPTIONS
		 *
		 * delete: Delete All Cache created by API Cache Pro.
		 *
		 *
		 * ## EXAMPLES
		 *
		 * wp api-cache-pro delete
		 */
		class API_Cache_Pro_CLI {

			/**
			 * Constructor.
			 *
			 * @access public
			 */
			public function __construct() {

			}

			/**
			 * Delete all Cache.
			 *
			 * @access public
			 * @param mixed $args Arguments.
			 * @param mixed $assoc_args Associated Arguments.
			 */
			public function delete( $args, $assoc_args ) {

				$api_cache_pro = new API_CACHE_PRO();
				$results       = $api_cache_pro->delete_all_cache();

				if ( ! empty( $results ) ) {
					WP_CLI::success( __( 'The Cache has been cleared.', 'api-cache-pro' ) );
				} else {
					WP_CLI::error( __( 'Cache is either empty, or there was an error.', 'api-cache-pro' ) );
				}

			}

		}

		WP_CLI::add_command( 'api-cache-pro', 'API_Cache_Pro_CLI' );

	}
}
