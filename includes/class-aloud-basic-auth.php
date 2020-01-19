<?php

/**
 * Implementation of HTTP Basic Authentication for
 * WordPress REST API.
 */
class Aloud_Basic_Auth extends Aloud_Auth {

	/**
	 * Filter for `determine_current_user`.
	 * Checks for credentials and authenticates the user.
	 *
	 * @param number|false $user_id The user id if authenticated.
	 *
	 * @return number|false
	 */
	public function authenticate( $user_id ) {
		// Checks that the user is not already authenticated.
		if ( ! empty( $user_id ) ) {
			return $user_id;
		}

		// Checks that the request is for the REST API.
		if ( ! isset( $_SERVER['REQUEST_URI'] ) || ! strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), rest_get_url_prefix() ) ) {
			return $user_id;
		};

		// Checks that the request carries credentials.
		if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			return $user_id;
		}

		$username = sanitize_user( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) );
		$password = sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );

		$user = wp_authenticate(
			$username,
			$password
		);

		if ( is_wp_error( $user ) ) {
			$this->error = $user;
			add_filter( 'rest_authentication_errors', array( $this, 'populate_error' ) );
			return false;
		}

		return $user->ID;
	}
}
