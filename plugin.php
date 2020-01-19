<?php
/**
 * Plugin Name: Aloud
 * Description: Registration and authentication for the REST API.
 * Author: Eduardo Campaña
 */

require_once plugin_dir_path( __FILE__ ) . '/includes/class-aloud-plugin.php';

register_activation_hook( __FILE__, array( 'Aloud_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aloud_Plugin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Aloud_Plugin', 'uninstall' ) );

Aloud_Plugin::run();
