<?php

/**
 * The core plugin class.
 *
 * Contains all the logic needed to interact with WordPress.
 * Loads dependencies, registers actions and filters, and
 * provides with static functions that to handle the WordPress
 * plugin hooks (activate, deactivate and uninstall).
 */
class Aloud_Plugin {
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
	 * Runs the plugin.
	 *
	 * @return void
	 */
	public static function run() {
		$instance = new static();

		$instance->load_dependencies();
		$instance->register_actions();
		$instance->register_filters();
	}

	/**
	 * Load the dependencies for the plugin to work.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . '../vendor/autoload.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-auth.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-basic-auth.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-cookie-auth.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-bearer-auth.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-signup-controller.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-aloud-signin-controller.php';
	}

	/**
	 * Registers the plugin's actions.
	 *
	 * @return void
	 */
	private function register_actions() {
		add_action( 'rest_api_init', array(new Aloud_Signup_Controller( $this->name, $this->version ), 'register_routes' ) );
		add_action( 'rest_api_init', array(new Aloud_Signin_Controller( $this->name, $this->version ), 'register_routes' ) );
	}

	/**
	 * Registers the plugin's filters.
	 *
	 * @return void
	 */
	private function register_filters() {
		// Removes the filters related to the default cookie authentication.
		remove_filter( 'determine_current_user', 'wp_validate_auth_cookie' );
		remove_filter( 'determine_current_user', 'wp_validate_logged_in_cookie', 20 );
		remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );

		add_filter( 'determine_current_user', array( new Aloud_Cookie_Auth(), 'authenticate' ), 10 );
		add_filter( 'determine_current_user', array( new Aloud_Basic_Auth(), 'authenticate' ), 11 );
	}

	/**
	 * Handler for `register_actvation_hook()`.
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
