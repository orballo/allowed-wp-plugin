<?php

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Implementation of Bearer Authentication with JWT
 * for WordPress REST API.
 */
class Aloud_Bearer_Auth extends Aloud_Auth {
	/**
	 * Filter for `determine_current_user`.
	 *
	 * @param int|false $user_id The user id if authenticated.
	 *
	 * @return int|false
	 */
	public function authenticate( $user_id ) {
		$auth = false;

		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$auth = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
		} elseif ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
			$auth = sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) );
		}

		if ( ! $auth ) {
			return $user_id;
		}

		list($token) = sscanf( $auth, 'Bearer %s' );

		if ( ! $token ) {
			$this->error = new WP_Error(
				'aloud_bad_auth_header',
				'Authorization header malformed.',
				array('status' => 401 )
			);

			add_filter( 'rest_authentication_errors', array( $this, 'populate_error' ) );

			return false;
		}
		var_dump( 'hello' );
		// try {
			$token = $this->validate_token( $token );
			var_dump( $token );
		// } catch ( Exception $_ ) {
		// $this->error = new WP_Error(
		// 'aloud_invalid_auth_token',
		// 'Authorization token invalid.',
		// array('status' => 401 )
		// );

		// add_filter( 'rest_authentication_errors', array( $this, 'populate_error' ) );

		// return false;
		// }

		return $user_id;
	}

	/**
	 * Generates a JWT token.
	 *
	 * @param int $user_id The id of the current user.
	 *
	 * @return string
	 */
	public function generate_token( int $user_id ): string {
		$secret_key = 'aloud-secret-key';
		$time       = time();
		$signer     = new Sha256();

		$token = ( new Builder() )
			->issuedAt( $time )
			->canOnlyBeUsedAfter( $time )
			->expiresAt( $time + ( 3600 * 24 * 365 ) )
			->withClaim( 'uid', $user_id )
			->getToken( $signer, new Key( $secret_key ) );

		return $token;
	}

	/**
	 * Validates an existing JWT tokeen.
	 *
	 * @param string $token The token to be validated.
	 *
	 * @return bool
	 */
	public function validate_token( string $token ): bool {
		$data  = new ValidationData();
		$token = ( new Parser() )->parse( $token );

		return $token->validate( $data );
	}
}
