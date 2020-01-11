<?php

class Aloud_Plugin
{
  static function install()
  {
    $instance = new static();

    register_activation_hook(__FILE__, array($instance, 'activate'));
    register_deactivation_hook(__FILE__, array($instance, 'deactivate'));
    register_uninstall_hook(__FILE__, array($instance, 'uninstall'));
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
