<?php

/**
 * The core plugin class.
 *
 * Contains all the logic needed to interact with WordPress.
 * Loads dependencies, registers actions and filters, and
 * provides with static functions to handle the WordPress
 * plugin hooks (activate, deactivate and uninstall).
 */
class Aloud_Auth {
	/**
	 * Plugin's name.
	 *
	 * @var string
	 */
	public $name = 'aloud';

	/**
	 * Plugin's version.
	 *
	 * @var string
	 */
	public $version = 'v1';

	/**
	 * Allowed hosts.
	 *
	 * @var array
	 */
	public $allowed_hosts = array('wp.aloud.local', 'www.aloud.local', 'aloud.local' );

	/**
	 * Is valid origin.
	 *
	 * @var bool
	 */
	public $is_valid_origin;

	/**
	 * Is valid referer.
	 *
	 * @var bool
	 */
	public $is_valid_referer;

	/**
	 * Is valid host.
	 *
	 * @var bool
	 */
	public $is_allowed_host;

	/**
	 * Runs the plugin.
	 *
	 * @return void
	 */
	public static function run() {
		$instance = new static();

		$instance->is_valid_origin  = $instance->validate_origin();
		$instance->is_valid_referer = $instance->validate_referer();
		$instance->is_allowed_host  = $instance->is_valid_origin || $instance->is_valid_referer;

		$instance->load_dependencies();
		$instance->register_actions();
		$instance->register_filters();
		$instance->remove_filters();
	}

	/**
	 * Load the dependencies for the plugin to work.
	 *
	 * @return void
	 */
	public function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-errors.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-cookie.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-signup.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-signin.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-signout.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-validate.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth-delete.php';
	}

	/**
	 * Registers the plugin's actions.
	 *
	 * @return void
	 */
	public function register_actions() {
		add_action( 'init', array($this, 'redirect_blog_to_admin' ) );
		add_action( 'init', array($this, 'block_non_admin_users' ) );

		if ( is_rest() ) {
			add_action( 'rest_api_init', array(new Aloud_Auth_Signup( $this->name, $this->version, $this->is_allowed_host ), 'register_routes' ) );
			add_action( 'rest_api_init', array(new Aloud_Auth_Signin( $this->name, $this->version, $this->is_allowed_host ), 'register_routes' ) );
			add_action( 'rest_api_init', array(new Aloud_Auth_Signout( $this->name, $this->version ), 'register_routes' ) );
			add_action( 'rest_api_init', array(new Aloud_Auth_Validate( $this->name, $this->version ), 'register_routes' ) );
			add_action( 'rest_api_init', array(new Aloud_Auth_Delete( $this->name, $this->version ), 'register_routes' ) );
		}
	}

	/**
	 * Registers the plugin's filters.
	 *
	 * @return void
	 */
	public function register_filters() {
		add_filter( 'allowed_redirect_hosts', array($this, 'add_allowed_redirect_hosts' ) );

		if ( is_rest() ) {
			add_filter( 'determine_current_user', array( new Aloud_Auth_Cookie( $this->is_allowed_host ), 'authenticate' ), 10 );
			add_filter( 'rest_pre_serve_request', array( $this, 'expose_custom_headers' ) );
		}
	}

	/**
	 * Remove default filters.
	 *
	 * @return void
	 */
	public function remove_filters() {
		if ( is_rest() ) {
			// Removes the filters related to the default cookie authentication.
			remove_filter( 'determine_current_user', 'wp_validate_auth_cookie' );
			remove_filter( 'determine_current_user', 'wp_validate_logged_in_cookie', 20 );
			remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
		}
	}

	/**
	 * Validate the Origin header of the request.
	 *
	 * @return boolean
	 */
	private function validate_origin() {
		if ( ! isset( $_SERVER['HTTP_ORIGIN'] ) ) {
			return false;
		}

		$origin = esc_url_raw( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) );

		list('host' => $origin) = wp_parse_url( $origin );

		if ( empty( $origin ) ) {
			return false;
		}

		if ( ! in_array( $origin, $this->allowed_hosts, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate the Referer header of the request.
	 *
	 * @return boolean
	 */
	private function validate_referer() {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );

		list('host' => $referer) = wp_parse_url( $referer );

		if ( empty( $referer ) ) {
			return false;
		}

		if ( ! in_array( $referer, $this->allowed_hosts, true ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Hook for `allowed_redirect_hosts` filter.
	 *
	 * @param array $hosts Array of already allowed hosts.
	 *
	 * @return array
	 */
	public function add_allowed_redirect_hosts( $hosts ) {
		return array_merge( $hosts, $this->allowed_hosts );
	}

	/**
	 * Redirect non admin pages to /wp-admin.
	 *
	 * @return void
	 */
	public function redirect_blog_to_admin() {
		if ( ! is_admin() && ! is_login() && ! is_rest() ) {
			wp_safe_redirect( site_url() . '/wp-admin' );
			exit;
		}
	}

	/**
	 * Redirect non admin users from /wp-admin to aloud.local
	 *
	 * @return void
	 */
	public function block_non_admin_users() {
		if ( is_admin() && is_user_logged_in() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( 'https://aloud.local:3000' );
			exit;
		}
	}

	/**
	 * Expose Aloud custom headers on the responses.
	 *
	 * @return void
	 */
	public function expose_custom_headers() {
		header( 'Access-Control-Expose-Headers: X-Aloud-Step', false );
	}


	/**
	 * Handler for `register_activation_hook()`.
	 *
	 * @return void
	 */
	public static function activate() {
	}

	/**
	 * Handler for `register_deactivation_hook()`.
	 *
	 * @return void
	 */
	public static function deactivate() {
	}

	/**
	 * Handler for `register_uninstall_hook()`.
	 *
	 * @return void
	 */
	public static function uninstall() {
	}
}
