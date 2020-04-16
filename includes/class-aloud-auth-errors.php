<?php

/**
 * Creates errors for different scenarios.
 */
class Aloud_Auth_Errors {

	/**
	 * Returns an error for missing params.
	 *
	 * @param string $message Error message.
	 *
	 * @return WP_Error
	 */
	public static function missing_params( $message ) {
		return new WP_Error(
			'aloud_auth_missing_params',
			$message,
			array('status' => 400 )
		);
	}

	/**
	 * Returns an error for invalid params.
	 *
	 * @param string $message Error message.
	 *
	 * @return WP_Error
	 */
	public static function invalid_params( $message ) {
		return new WP_Error(
			'aloud_auth_invalid_params',
			$message,
			array('status' => 400 )
		);
	}

	/**
	 * Returns an error for existing credencial.
	 *
	 * @param string $credential Type of credential used.
	 *
	 * @return WP_Error
	 */
	public static function existing_credential( $credential ) {
		return new WP_Error(
			"aloud_auth_existing_{$credential}",
			"That {$credential} is already in use.",
			array('status' => 400 )
		);
	}

	/**
	 * Returns an error for invalid credentials.
	 *
	 * @return WP_Error
	 */
	public static function invalid_credentials() {
		return new WP_Error(
			'aloud_auth_invalid_credentials',
			'The credentials provided are invalid.',
			array('status' => 401 )
		);
	}

	/**
	 * Returs an error for invalid host.
	 *
	 * @return WP_Error
	 */
	public static function invalid_host() {
		return new WP_Error(
			'aloud_auth_invalid_host',
			'The host of the request is invalid.',
			array('status' => 403 )
		);
	}
}
