<?php

class Aloud_Plugin
{
  static function run()
  {
    $instance = new static();

    $instance->load_dependencies();
    $instance->register_actions();
    $instance->register_filters();
  }

  private function load_dependencies()
  {
    require_once dirname(__FILE__) . '/includes/class-aloud-signup-controller.php';
    require_once dirname(__FILE__) . '/includes/class-aloud-basic-auth.php';
  }

  private function register_actions()
  {
    add_action('rest_api_init', array(new Aloud_Signup_Controller(), 'register_routes'));
  }

  private function register_filters()
  {
    add_filter('determine_current_user', array(new Aloud_Basic_Auth(), 'authenticate'));
  }

  static function activate()
  {
  }

  static function deactivate()
  {
  }

  static function uninstall()
  {
  }
}
