<?php
/**
 * Create section for settings in customizer.
 *
 * @package api-cache-pro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'API_Cache_Pro_Customizer' ) ) {

	// Require Customizer Control.
	require_once ABSPATH . WPINC . '/class-wp-customize-control.php';

	/**
	 * API_Cache_Pro_Customizer
	 */
	class API_Cache_Pro_Customizer {

		/**
		 * Constructing a customizing running lemming.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'customize_register', array( $this, 'register' ) );
		}

		/**
		 * Register.
		 *
		 * @access public
		 * @param mixed $wp_customize Support Customizer.
		 */
		public function register( $wp_customize ) {

			// Rest API Cache Panel.
			$wp_customize->add_panel(
				'api_cache_pro_panel',
				array(
					'priority'       => 500,
					'capability'     => 'manage_options',
					'theme_supports' => '',
					'title'          => __( 'API Cache Pro', 'api-cache-pro' ),
					'description'    => __( 'Configure caching for the WordPress Rest API.', 'api-cache-pro' ),
				)
			);

			// Settings Section.
			$wp_customize->add_section(
				'api_cache_pro_settings_section',
				array(
					'title'       => __( 'General Settings', 'api-cache-pro' ),
					'description' => __( 'Fill out the following cache settings for rest api.', 'api-cache-pro' ),
					'priority'    => 500,
					'panel'       => 'api_cache_pro_panel',
				)
			);

			// Disable Settings.
			$wp_customize->add_setting(
				'api_cache_pro[disable]',
				array(
					'default'           => false,
					'type'              => 'option',
					'transport'         => 'refresh',
					'sanitize_callback' => array( $this, 'sanitize_disable_cache' ),
				)
			);

			// Disable Controls.
			$wp_customize->add_control(
				'api_cache_pro_disable',
				array(
					'label'       => __( 'Disable Cache', 'api-cache-pro' ),
					'description' => __( 'Check this box if you wish to disable the WP Rest API Cache. All current cache will be cleared if enabled.', 'api-cache-pro' ),
					'type'        => 'checkbox',
					'section'     => 'api_cache_pro_settings_section',
					'settings'    => 'api_cache_pro[disable]',
				)
			);

			// Timeout Settings.
			$wp_customize->add_setting(
				'api_cache_pro[default_timeout]',
				array(
					'default'           => apply_filters( 'api_cache_default_timeout', 300 ),
					'type'              => 'option',
					'transport'         => 'refresh',
					'sanitize_callback' => array( $this, 'sanitize_default_timeout' ),
				)
			);

			// Timeout Controls.
			$wp_customize->add_control(
				'api_cache_pro_default_timeout',
				array(
					'label'       => __( 'Default Timeout', 'api-cache-pro' ),
					'description' => __( 'Set the default timeout in seconds. All current cache will be cleared if updated. <br /><br /> Default: 300 (5 Minutes) <br /> Max: 604800 (7 Days)', 'api-cache-pro' ),
					'type'        => 'number',
					'section'     => 'api_cache_pro_settings_section',
					'settings'    => 'api_cache_pro[default_timeout]',
					'input_attrs' => array(
						'min'  => 300, // Set Min to 300.
						'max'  => 604800, // Max of 7 Days in Seconds.
						'step' => 1,
					),
				)
			);

		}

		/**
		 * Sanitize Disable Cache.
		 *
		 * @access public
		 * @param mixed $disable_cache Disable Cache.
		 */
		public function sanitize_disable_cache( $disable_cache ) {

			if ( true === $disable_cache ) {
				$cache = new API_CACHE_PRO();
				$cache->delete_all_cache();
			}

			return $disable_cache;

		}

		/**
		 * Sanitize Default Timeout.
		 *
		 * @access public
		 * @param mixed $default_timeout Default Timeout.
		 */
		public function sanitize_default_timeout( $default_timeout ) {

			if ( is_numeric( $default_timeout ) && $default_timeout <= 604800 && $default_timeout >= 300 ) {

				// Flush Cache to respect new timeouts.
				$cache = new API_CACHE_PRO();
				$cache->delete_all_cache();

				return $default_timeout;

			} else {
				return new WP_Error( 'invalid', __( 'You must supply a number no greater than the max default timeout allowed.', 'api-cache-pro' ) );
			}

		}
	}

	// Load to Customizer.
	new API_Cache_Pro_Customizer();

}
