<?php
/**
 * Plugin Name: API Cache Pro
 * Description: A simple plugin to cache WP Rest API Requests.
 * Author: Hubbard Labs
 * Author URI: https://github.com/hubbardlabs/
 * Version: 0.0.4
 * Text Domain: api-cache-pro
 * Domain Path: /languages/
 * Plugin URI: https://github.com/hubbardlabs/api-cache-pro
 * License: GPL3+
 *
 * @package api-cache-pro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'API_CACHE_PRO' ) ) {

	/**
	 * API_CACHE_PRO class.
	 */
	class API_CACHE_PRO {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {

			// Include Customizer Support.
			include_once 'includes/class-api-cache-pro-customizer.php';

			// Include CLI Support.
			include_once 'includes/class-api-cache-pro-cli.php';

			$cache_options = $this->get_option();

			$disable_cache = $cache_options['disable'] ?? false;

			if ( ! is_admin() && false === $disable_cache ) {

				add_filter( 'rest_pre_dispatch', array( $this, 'cache_requests_headers' ), 10, 3 );

				add_filter( 'rest_request_after_callbacks', array( $this, 'cache_requests' ), 10, 3 );

				// Delete Cache when we save a post.
				add_action( 'save_post', array( $this, 'delete_all_cache' ) );

			}

			// Delete All Cache on Deactivation.
			register_deactivation_hook( __FILE__, array( $this, 'delete_all_cache' ) );

		}

		/**
		 * Get Option
		 *
		 * @access public
		 */
		public function get_option() {
			$option = get_option( 'api_cache_pro' ) ?? array();
			return $option;
		}

		/**
		 * Get Timeout.
		 *
		 * @access public
		 */
		public function get_timeout() {

			$cache_options = $this->get_option();

			$default_timout = $cache_options['default_timeout'] ?? 300;

			return $default_timout;

		}

		/**
		 * Cache Key.
		 *
		 * @access public
		 * @param mixed $request_uri Request URI.
		 */
		public function get_cache_key( $request_uri ) {

			if ( ! empty( $request_uri ) || null !== $request_uri || '' !== $request_uri || false !== $request_uri ) {
				$cache_key = apply_filters( 'api_cache_pro_key', 'api_cache_pro_' . md5( $request_uri ) ) ?? false;
			} else {
				$cache_key = false;
			}

			return $cache_key;
		}

		/**
		 * Cache our Request.
		 *
		 * @access public
		 * @param mixed $response Response.
		 * @param mixed $handler Handler.
		 * @param mixed $request Request.
		 */
		public function cache_requests( $response, $handler, $request ) {

			// Get Request URI.
			$request_uri = esc_url( $_SERVER['REQUEST_URI'] ) ?? null;

			// Set Cache Param.
			$request->set_param( 'cache', true );

			// Check if Response is Error.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$endpoint = $request->get_route();
			$method   = $request->get_method();

			// Set Cache Key if we have a Request URI.
			if ( ! is_wp_error( $response ) ) {
				$cache_key = $this->get_cache_key( $request_uri ) ?? null;
			}

			// Return Response if no Cache Key.
			if ( empty( $cache_key ) || null === $cache_key || '' === $cache_key || false === $cache_key ) {
				return $response;
			}

			// Get Cache Results.
			$cache_results = $this->get_cache_results( $cache_key ) ?? false;

			// Check Cache Results.
			if ( false === $cache_results || '' === $cache_results || empty( $cache_results ) || null === $cache_results ) {

				$save_cache = $this->set_cache( $cache_key, $response );

				// Return Response - Cache Not Ready.
				if ( true === $save_cache ) {
					$cache_results = $this->get_cache_results( $cache_key ) ?? false;
					return $cache_results;
				} else {
					return $response;
				}
			} else {

				// Return Cache Results.
				return $cache_results;

			}

		}


		/**
		 * Set Cache.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 * @param mixed $response Response.
		 */
		public function set_cache( $cache_key, $response ) {

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( null !== $response || '' !== $response || ! empty( $response ) || false !== $response ) {
				$result = $response->get_data() ?? null;
			} else {
				$result = null;
			}

				// Set Transient.
			if ( ! empty( $result ) || null !== $result || '' !== $result || false !== $result || false !== $response ) {
				$timeout   = $this->get_timeout() ?? 300; // Get Timeout.
				$set_cache = set_transient( $cache_key, $result, $timeout );
			}

				return $result;

		}

		/**
		 * Get Cache Results.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 */
		public function get_cache_results( $cache_key ) {

			if ( ! empty( $cache_key ) || '' !== $cache_key || null !== $cache_key || false !== $cache_key ) {
				$cache_results = get_transient( $cache_key ) ?? false;
			} else {
				$cache_results = false;
			}

			return $cache_results;

		}

		/**
		 * Cache Request Headers.
		 *
		 * @access public
		 * @param mixed $response Response.
		 * @param mixed $server Server.
		 * @param mixed $request Request.
		 */
		public function cache_requests_headers( $response, $server, $request ) {

			// Get Request URI.
			$request_uri = esc_url( $_SERVER['REQUEST_URI'] ) ?? null;

			// Set Display Cache Header Filter.
			$display_cache_header = apply_filters( 'api_cache_pro_header', true );

			// Get Path & Method.
			$path   = $request->get_route() ?? null;
			$method = $request->get_method() ?? 'GET';

			// Get Timeout.
			$timeout = $this->get_timeout() ?? 300;

			// Set Cache Control Header.
			$max_age   = apply_filters( 'api_cache_pro_max_age', $timeout ) ?? null;
			$s_max_age = apply_filters( 'api_cache_pro_s_max_age', $timeout ) ?? null;

			$display_cache_control_header = apply_filters( 'api_cache_pro_control_header', true );

			// Send Cache Control Header.
			if ( null !== $max_age && null !== $s_max_age && true === $display_cache_control_header ) {
				$server->send_header( 'Cache-Control', 'public s-maxage=' . $s_max_age . ' max-age=' . $max_age );
			}

			// Get Cache Key.
			if ( ! is_wp_error( $response ) ) {
				$cache_key = $this->get_cache_key( $request_uri ) ?? null;
			}

			// Check for Cache from Transient.
			$cache_results = $this->get_cache_results( $cache_key ) ?? false;

			// Checks before we send our header.
			if ( false !== $cache_results || '' !== $cache_results || null !== $cache_results || ! empty( $cache_results ) || 'disabled' !== $request->get_param( 'cache' ) || ! is_wp_error( $response ) ) {

					$cache_headers = $this->display_cache_header( $cache_key, $server, $request );

					$key_headers = $this->display_key_headers( $cache_key, $server, $request );

					$expire_headers = $this->display_expires_headers( $cache_key, $server, $request );

			}

		}

		/**
		 * Display Main Cache Header.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 * @param mixed $server Server.
		 * @param mixed $request Request.
		 */
		public function display_cache_header( $cache_key, $server, $request ) {

			$cache_timeout = $this->get_cache_timeout( $cache_key ) ?? null;

			// Set to Display Cache Control Header.
			$display_cache_header = apply_filters( 'api_cache_pro_header', true );

			if ( null !== $cache_timeout && true === $display_cache_header && 'disabled' !== $request->get_param( 'cache' ) ) {

					$server->send_header( 'X-API-CACHE-PRO', esc_html( 'Cached', 'api-cache-pro' ) );

			} else {
				$server->send_header( 'X-API-CACHE-PRO', esc_html( 'Not Cached', 'api-cache-pro' ) );
			}

		}

		/**
		 * Display Key Headers.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 * @param mixed $server Server.
		 * @param mixed $request Request.
		 */
		public function display_key_headers( $cache_key, $server, $request ) {

			$cache_timeout = $this->get_cache_timeout( $cache_key ) ?? null;

			$display_cache_key = apply_filters( 'api_cache_pro_key_header', true );

			if ( null !== $cache_timeout && true === $display_cache_key && 'disabled' !== $request->get_param( 'cache' ) ) {
				$server->send_header( 'X-API-CACHE-PRO-KEY', $cache_key );
			}
		}

		/**
		 * Display Expire Headers.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 * @param mixed $server Server.
		 * @param mixed $request Request.
		 */
		public function display_expires_headers( $cache_key, $server, $request ) {

			// Get Transient Timeout.
			$cache_timeout = $this->get_cache_timeout( $cache_key ) ?? null;

			// Display Cache Timout.
			$display_cache_timeout = apply_filters( 'api_cache_pro_expires_header', true );

			if ( null !== $cache_timeout && true === $display_cache_timeout && 'disabled' !== $request->get_param( 'cache' ) ) {

				// Get WordPress Time Zone Settings.
				$gmt_offset = get_option( 'gmt_offset' ) ?? 0;

				// Set Transient Timeout & Diff.
				$transient_timeout = date( 'F j, Y, g:i A T', current_time( $cache_timeout, $gmt_offset ) ) ?? null;
				$timeout_diff      = human_time_diff( current_time( $cache_timeout, $gmt_offset ), current_time( 'timestamp', $gmt_offset ) ) ?? null;

				// Send Cache Expires Header.
				if ( null !== $transient_timeout ) {
					$server->send_header( 'X-API-CACHE-PRO-EXPIRES', $transient_timeout );
				}

				// Send Cache Expires Diff Header.
				$display_cache_expires_diff = apply_filters( 'api_cache_pro_expires_diff_header', true );
				if ( null !== $timeout_diff && true === $display_cache_expires_diff ) {
					$server->send_header( 'X-API-CACHE-PRO-EXPIRES-DIFF', $timeout_diff );
				}
			}
		}

		/**
		 * Get Single Cache Item.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 */
		public function get_single_cache_item( $cache_key ) {

			if ( ! empty( $cache_key ) || '' !== $cache_key || null !== $cache_key || false !== $cache_key ) {

				global $wpdb;

				$results = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT option_value FROM $wpdb->options WHERE option_name = %s",
						'_transient_' . $cache_key
					)
				);

				if ( ! empty( $results ) ) {
					return $results->option_value ?? '';
				} else {
					return false;
				}
			} else {
				return new WP_Error( 'missing_cache_key', __( 'Please provide the Cache Key (Transient Name).', 'api-cache-pro' ) );
			}
		}

		/**
		 * Delete Cache.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 */
		public function delete_cache( $cache_key ) {

			if ( ! empty( $cache_key ) ) {

				// Delete Transient.
				delete_transient( $cache_key );

				// Sometimes Transient are not in DB. So Flush.
				$flush_cache = wp_cache_flush();

			} else {
				return new WP_Error( 'missing_cache_key', __( 'Please provide the Cache Key (Transient Name).', 'api-cache-pro' ) );
			}
		}

		/**
		 * Delete All Cache.
		 *
		 * @access public
		 */
		public function delete_all_cache() {

			global $wpdb;

			$results = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
					'_transient_api_cache_pro_%',
					'_transient_timeout_api_cache_pro_%',
					'_site_transient_api_cache_pro_%',
					'_site_transient_timeout_api_cache_pro_%'
				)
			);

			// Sometimes Transient are not in DB. So Flush.
			$flush_cache = wp_cache_flush();

			return $results;

		}

		/**
		 * Get Cache Timeout.
		 *
		 * @access public
		 * @param mixed $cache_key Cache Key.
		 */
		public function get_cache_timeout( $cache_key ) {

			if ( ! empty( $cache_key ) ) {

				global $wpdb;

				$timeout_key = '_transient_timeout_' . $cache_key;

				$cache_timeout = $wpdb->get_col( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE %s", $timeout_key ) );

				if ( ! empty( $cache_timeout ) ) {
					return $cache_timeout[0];
				} else {
					return null;
				}
			} else {
				return new WP_Error( 'missing_cache_key', __( 'Please provide the Cache Key (Transient Name).', 'api-cache-pro' ) );
			}

		}

	} // End Class.

	new API_CACHE_PRO();

}
