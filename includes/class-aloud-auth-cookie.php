<?php

/**
 * Implementation of WP Cookie Authentication for
 * WordPress REST API.
 */
class Aloud_Auth_Cookie {
	/**
	 * Authentication error.
	 *
	 * @var WP_Error
	 */
	public $error;

	/**
	 * Is allowed host.
	 *
	 * @var array
	 */
	public $is_allowed_host;

	/**
	 * Constructor that sets up the object.
	 *
	 * @param array $is_allowed_host Is an allowed host.
	 */
	public function __construct( $is_allowed_host ) {
		$this->is_allowed_host = $is_allowed_host;
	}

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

		if ( ! $this->is_allowed_host ) {
			return $user_id;
		}

		$user_id = wp_validate_logged_in_cookie( $user_id );

		return $user_id;
	}

	/**
	 * Filter for `rest_authentication_errors`.
	 * Populates the error if empty.
	 *
	 * @param WP_Error|null $error The error passed by `rest_authentication_errors`.
	 *
	 * @return WP_Error
	 */
	public function populate_error( $error ) {
		if ( ! empty( $error ) ) {
			return $error;
		}

		return $this->error;
	}
}
