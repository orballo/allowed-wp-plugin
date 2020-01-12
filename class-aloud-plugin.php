<?php

class Aloud_Plugin
{
  function run()
  {
    $this->load_dependencies();
    $this->set_actions();
    $this->set_filters();
  }

  private function load_dependencies()
  {
    require_once dirname(__FILE__) . '/includes/class-signup-controller.php';
  }

  private function set_actions()
  {
    add_action('rest_api_init', array(new Signup_Controller(), 'register_routes'));
  }

  private function set_filters()
  {
  }

  static function install()
  {
    add_action('init', array(new static(), 'run'));

    register_activation_hook(__FILE__, array('Aloud_Plugin', 'activate'));
    register_deactivation_hook(__FILE__, array('Aloud_Plugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('Aloud_Plugin', 'uninstall'));
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
