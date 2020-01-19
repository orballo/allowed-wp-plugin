<?php

/**
 * Interface for Auth classes.
 */
abstract class Aloud_Auth {
	/**
	 * Authentication error.
	 *
	 * @var WP_Error
	 */
	public $error;

	/**
	 * Filter for `determine_current_user`.
	 *
	 * @param int|false $user_id The user id if authenticated.
	 *
	 * @return int|false
	 */
	abstract public function authenticate( $user_id );

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
