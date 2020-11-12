<?php
/**
 * Plugin Name: Aloud Auth
 * Description: Registration and authentication for the REST API.
 * Author: Eduardo Campaña
 * Version: 0.1
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

if ( ! function_exists( 'aloud_auth_generate_code' ) ) {
	/**
	 * Generate code for passwordless authentication.
	 *
	 * @param string $transient The name of the transient where the code will be stored.
	 *
	 * @param int    $expiration (optional) The expiration date of the transient.
	 *
	 * @return string
	 */
	function aloud_auth_generate_code( $transient, $expiration = 60 * 5 ) {
		$code      = strtoupper( wp_generate_password( 16, false ) );
		$hash_code = wp_hash_password( $code );
		set_transient( $transient, $hash_code, $expiration );
		return $code;
	}
}

if ( ! function_exists( 'aloud_auth_validate_code' ) ) {
	/**
	 * Validate code for passwordless authentication.
	 *
	 * @param string $transient The name of the transient where the code is stored.
	 *
	 * @param string $code The code to be validated.
	 *
	 * @return boolean
	 */
	function aloud_auth_validate_code( $transient, $code ) {
		$hash_code     = get_transient( $transient );
		$is_valid_code = $hash_code ? wp_check_password( $code, $hash_code ) : false;
		if ( $is_valid_code ) {
			delete_transient( $transient );
		}
		return $is_valid_code;
	}
}

require_once plugin_dir_path( __FILE__ ) . '/includes/class-aloud-auth.php';

register_activation_hook( __FILE__, array( 'Aloud_Auth', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aloud_Auth', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Aloud_Auth', 'uninstall' ) );

Aloud_Auth::run();
