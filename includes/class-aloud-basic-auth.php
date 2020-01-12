<?php

class Aloud_Basic_Auth
{
  function authenticate($user_id)
  {
    // Checks that the user is not already authenticated.
    if (!empty($user_id)) {
      return $user_id;
    }

    // Checks that the request is for the REST API.
    if (!strpos($_SERVER['REQUEST_URI'], rest_get_url_prefix())) {
      return $user_id;
    };

    // Checks that the request carries credentials.
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
      return $user_id;
    }

    $user = wp_authenticate(
      $_SERVER['PHP_AUTH_USER'],
      $_SERVER['PHP_AUTH_PW']
    );

    if (is_wp_error($user)) {
      $this->error = $user;
      add_filter('rest_authentication_errors', array($this, 'populate_error'));
      return false;
    }

    return $user->ID;
  }

  function populate_error($error)
  {
    if (!empty($error)) {
      return $error;
    }

    return $this->error;
  }
}
