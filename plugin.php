<?php
/**
 * Plugin Name: Aloud
 * Description: Registration and authentication for the REST API.
 * Author: Eduardo Campaña
 */

if ( ! function_exists( 'is_rest' ) ) {
	/**
	 * Checks if the request is a REST API request.
	 *
	 * @return boolean
	 */
	function is_rest() {
		$prefix = rest_get_url_prefix();

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$uri     = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$pattern = "/^\/{$prefix}/";

			if ( preg_match( $pattern, $uri ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'is_login' ) ) {
	/**
	 * Check if the current request is for the login page.
	 *
	 * @return boolean
	 */
	function is_login() {
		return isset( $_SERVER['SCRIPT_NAME'] ) && stripos( esc_url_raw( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ), strrchr( wp_login_url(), '/' ) ) !== false;
	}
}

require_once plugin_dir_path( __FILE__ ) . '/includes/class-aloud-plugin.php';

register_activation_hook( __FILE__, array( 'Aloud_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aloud_Plugin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Aloud_Plugin', 'uninstall' ) );

Aloud_Plugin::run();
