<?php

/**
 * Controller for the `/aloud/v1/delete` route
 * in the REST API.
 */
class Aloud_Auth_Delete extends WP_REST_Controller {

	/**
	 * Route's namespace.
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * Route's base name.
	 *
	 * @var string
	 */
	public $rest_base;

	/**
	 * Constructor that sets up the namespace and the route.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $plugin_version The version of the plugin.
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		$this->namespace = "{$plugin_name}/${plugin_version}";
		$this->rest_base = 'delete';
	}

	/**
	 * Registers `POST /aloud/v1/delete` route.
	 * Registers `POST /aloud/v1/delete/passwordless` route.
	 *
	 * @return void
	 */
	public function register_routes() {
		// Registers `/aloud/v1/delete`.
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'  => WP_Rest_Server::DELETABLE,
				'callback' => array( $this, 'delete' ),
				'args'     => array(
					'password' => array(
						'required'          => false,
						'type'              => 'string',
						'description'       => esc_html( "The user's password." ),
						'validate_callback' => array( $this, 'validate_password' ),
						'sanitize_callback' => function ( $password ) {
							return sanitize_text_field( $password );
						},
					),
					'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			)
		);

		// Registers `/aloud/v1/delete/passwordless`.
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/passwordless',
			array(
				'methods'  => WP_Rest_Server::DELETABLE,
				'callback' => array( $this, 'delete_passwordless' ),
				'args'     => array(
					'code'    => array(
						'required'          => false,
						'type'              => 'string',
						'description'       => esc_html( 'The verification code sent to the email address.' ),
						'validate_callback' => array( $this, 'validate_code' ),
						'sanitize_callback' => function ( $code ) {
							return sanitize_text_field( $code );
						},
					),
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			)
		);
	}

	/**
	 * Delete the logged in user.
	 *
	 * @param WP_REST_Request $request A WP request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		if ( ! is_user_logged_in() ) {
			return Aloud_Auth_Errors::not_authenticated();
		}

		$params = $request->get_params();

		if ( ! isset( $params['password'] ) ) {
			return Aloud_Auth_Errors::missing_params(
				'The param `password` must be provided.'
			);
		}

		$user = wp_get_current_user();

		if ( ! wp_check_password( $params['password'], $user->user_pass, $user->ID ) ) {
			return Aloud_Auth_Errors::invalid_credentials();
		};

		require_once ABSPATH . 'wp-admin/includes/user.php';

		if ( ! wp_delete_user( $user->ID ) ) {
			return Aloud_Auth_Errors::cannot_delete_account();
		};

		wp_clear_auth_cookie();

		$response = $this->prepare_item_for_response( $user, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Delete the logged in user.
	 *
	 * @param WP_REST_Request $request A WP request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete_passwordless( $request ) {
		if ( ! is_user_logged_in() ) {
			return Aloud_Auth_Errors::not_authenticated();
		}

		$params = $request->get_params();
		$user   = wp_get_current_user();

		$transient_hash = wp_hash( $user->user_email );
		$transient      = 'aloud_auth_delete_' . $transient_hash;
		$expiration     = 60 * 5;

		if ( isset( $params['code'] ) ) {
			$is_valid_code = aloud_auth_validate_code( $transient, $params['code'] );
			if ( $is_valid_code ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';

				if ( ! wp_delete_user( $user->ID ) ) {
					return Aloud_Auth_Errors::cannot_delete_account();
				};

				wp_clear_auth_cookie();

				$response = $this->prepare_item_for_response( $user, $request );

				return $response;
			} else {
				return Aloud_Auth_Errors::invalid_credentials();
			}
		}

		$code = aloud_auth_generate_code( $transient, $expiration );

		$email_sent = wp_mail(
			$user->user_email,
			'Aloud: Verification code to delete account',
			'This is the verification code to delete account: ' . $code
		);

		$response = rest_ensure_response(
			array(
				'username'   => $user->user_login,
				'email'      => $user->user_email,
				'expiration' => $expiration,
				'email_sent' => $email_sent,
			)
		);
		$response->set_headers( array('X-Aloud-Step' => 'generation' ) );

		return $response;
	}

	/**
	 * Modifies the new user data according to the schema
	 * to send it in the response.
	 *
	 * @param WP_User         $user An object with the user's data.
	 * @param WP_REST_Request $request An object with the request's data.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $user, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( in_array( 'id', $fields, true ) ) {
			$data['id'] = $user->ID;
		}

		if ( in_array( 'username', $fields, true ) ) {
			$data['username'] = $user->user_login;
		}

		if ( in_array( 'name', $fields, true ) ) {
			$data['name'] = $user->display_name;
		}

		if ( in_array( 'first_name', $fields, true ) ) {
			$data['first_name'] = $user->first_name;
		}

		if ( in_array( 'last_name', $fields, true ) ) {
			$data['last_name'] = $user->last_name;
		}

		if ( in_array( 'email', $fields, true ) ) {
			$data['email'] = $user->user_email;
		}

		if ( in_array( 'url', $fields, true ) ) {
			$data['url'] = $user->user_url;
		}

		if ( in_array( 'description', $fields, true ) ) {
			$data['description'] = $user->description;
		}

		if ( in_array( 'link', $fields, true ) ) {
			$data['link'] = get_author_posts_url( $user->ID, $user->user_nicename );
		}

		if ( in_array( 'locale', $fields, true ) ) {
			$data['locale'] = get_user_locale( $user );
		}

		if ( in_array( 'nickname', $fields, true ) ) {
			$data['nickname'] = $user->nickname;
		}

		if ( in_array( 'slug', $fields, true ) ) {
			$data['slug'] = $user->user_nicename;
		}

		if ( in_array( 'roles', $fields, true ) ) {
			// Defensively call array_values() to ensure an array is returned.
			$data['roles'] = array_values( $user->roles );
		}

		if ( in_array( 'registered_date', $fields, true ) ) {
			$data['registered_date'] = gmdate( 'c', strtotime( $user->user_registered ) );
		}

		if ( in_array( 'capabilities', $fields, true ) ) {
			$data['capabilities'] = (object) $user->allcaps;
		}

		if ( in_array( 'extra_capabilities', $fields, true ) ) {
			$data['extra_capabilities'] = (object) $user->caps;
		}

		if ( in_array( 'avatar_urls', $fields, true ) ) {
			$data['avatar_urls'] = rest_get_avatar_urls( $user );
		}

		if ( in_array( 'meta', $fields, true ) ) {
			$data['meta'] = $this->meta->get_value( $user->ID, $request );
		}

		$data = $this->filter_response_by_context( $data, $request['context'] );

		$data = array(
			'deleted'  => true,
			'previous' => $data,
		);

		$response = rest_ensure_response( $data );

		return apply_filters( 'aloud_auth_delete_prepare_user', $response, $user, $request );
	}

	/**
	 * Validates the password parameter.
	 *
	 * @param string $password The user's password.
	 *
	 * @return WP_Error|void
	 */
	public function validate_password( $password ) {
		if ( empty( $password ) ) {
			return Aloud_Auth_Errors::invalid_params(
				'The `password` parameter cannot be empty.'
			);
		}

		if ( false !== strpos( $password, '\\' ) ) {
			return Aloud_Auth_Errors::invalid_params(
				'Passwords cannot contain the `\` (backslash) character.'
			);
		}
	}

	/**
	 * Validates the code parameter.
	 *
	 * @param string $code Code sent to the user email for registration.
	 *
	 * @return WP_Error|void
	 */
	public function validate_code( $code ) {
		if ( empty( $code ) ) {
			return Aloud_Auth_Errors::invalid_params(
				'The `code` parameter cannot be empty.'
			);
		}

		if ( ! ctype_alnum( $code ) ) {
			return Aloud_Auth_Errors::invalid_params(
				'The `code` parameter is invalid.'
			);
		}
	}

	/**
	 * Generates the response's schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'user',
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'description' => __( 'Unique identifier for the user.' ),
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
				'username'           => array(
					'description' => __( 'Login name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'name'               => array(
					'description' => __( 'Display name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'first_name'         => array(
					'description' => __( 'First name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'last_name'          => array(
					'description' => __( 'Last name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'email'              => array(
					'description' => __( 'The email address for the user.' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'url'                => array(
					'description' => __( 'URL of the user.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'description'        => array(
					'description' => __( 'Description of the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'link'               => array(
					'description' => __( 'Author URL of the user.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
				'locale'             => array(
					'description' => __( 'Locale for the user.' ),
					'type'        => 'string',
					'enum'        => array_merge( array( '', 'en_US' ), get_available_languages() ),
					'context'     => array( 'edit' ),
				),
				'nickname'           => array(
					'description' => __( 'The nickname for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'slug'               => array(
					'description' => __( 'An alphanumeric identifier for the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'registered_date'    => array(
					'description' => __( 'Registration date for the user.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'roles'              => array(
					'description' => __( 'Roles assigned to the user.' ),
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'context'     => array( 'edit' ),
				),
				'password'           => array(
					'description' => __( 'Password for the user (never included).' ),
					'type'        => 'string',
					'context'     => array(), // Password is never displayed.
					'required'    => true,
				),
				'capabilities'       => array(
					'description' => __( 'All capabilities assigned to the user.' ),
					'type'        => 'object',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'extra_capabilities' => array(
					'description' => __( 'Any extra capabilities assigned to the user.' ),
					'type'        => 'object',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
			),
		);

		if ( get_option( 'show_avatars' ) ) {
			$avatar_properties = array();

			$avatar_sizes = rest_get_avatar_sizes();

			foreach ( $avatar_sizes as $size ) {
				$avatar_properties[ $size ] = array(
					/* translators: %d: Avatar image size in pixels. */
					'description' => sprintf( __( 'Avatar URL with image size of %d pixels.' ), $size ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
				);
			}

			$schema['properties']['avatar_urls'] = array(
				'description' => __( 'Avatar URLs for the user.' ),
				'type'        => 'object',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $avatar_properties,
			);
		}

		$this->schema = $schema;

		return $this->schema;
	}
}
