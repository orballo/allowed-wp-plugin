<?php

class Aloud_Plugin
{

  function activate()
  {
  }

  function deactivate()
  {
  }

  static function install()
  {
    $instance = new static();

    register_activation_hook(__FILE__, array($instance, 'activate'));
    register_deactivation_hook(__FILE__, array($instance, 'deactivate'));
    register_uninstall_hook(__FILE__, array('Aloud_Plugin', 'uninstall'));
  }

  static function uninstall()
  {
    set_transient("aloud_uninstall", "toma ya", 30);
  }
}
