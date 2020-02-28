<?php

/**
 * Implementation of WP Cookie Authentication for
 * WordPress REST API.
 */
class Aloud_Cookie_Auth extends Aloud_Auth {
	/**
	 * Array of valid origins for authentication.
	 *
	 * @var array
	 */
	public $origins = array('wp.aloud.local', 'www.aloud.local', 'aloud.local' );

	/**
	 * Filter for `determine_current_user`.
	 * Checks for credentials and authenticates the user.
	 *
	 * @param int|false $user_id The user id if authenticated.
	 *
	 * @return int|false
	 */
	public function authenticate( $user_id ) {
		if ( ! empty( $user_id ) ) {
			return $user_id;
		}

		if ( ! is_ssl() ) {
			return $user_id;
		}

		if ( ! $this->is_valid_origin() && ! $this->is_valid_referer() ) {
			return $user_id;
		}

		$user_id = wp_validate_logged_in_cookie( $user_id );

		return $user_id;
	}

	/**
	 * Validate the Origin header of the request.
	 *
	 * @return boolean
	 */
	private function is_valid_origin() {
		if ( ! isset( $_SERVER['HTTP_ORIGIN'] ) ) {
			return false;
		}

		$origin = esc_url_raw( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) );

		list('host' => $origin) = wp_parse_url( $origin );

		if ( empty( $origin ) ) {
			return false;
		}

		if ( ! in_array( $origin, $this->origins, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate the Referer header of the request.
	 *
	 * @return boolean
	 */
	private function is_valid_referer() {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );

		list('host' => $referer) = wp_parse_url( $referer );

		if ( empty( $referer ) ) {
			return false;
		}

		if ( ! in_array( $referer, $this->origins, true ) ) {
			return false;
		}

		return true;
	}
}
