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


	public static function run() {
		$instance = new static();

		$instance->load_dependencies();
		$instance->register_actions();
		$instance->register_filters();
	}

	/**
	 * Load the different dependencies of this plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		include_once dirname( __FILE__ ) . '/class-aloud-signup-controller.php';
		include_once dirname( __FILE__ ) . '/class-aloud-basic-auth.php';
	}


	private function register_actions() {
		add_action( 'rest_api_init', array( new Aloud_Signup_Controller(), 'register_routes' ) );
	}

	private function register_filters() {
		add_filter( 'determine_current_user', array( new Aloud_Basic_Auth(), 'authenticate' ) );
	}

	public static function activate() {
	}

	public static function deactivate() {
	}

	public static function uninstall() {
	}
}
