<?php

class Signup_Controller extends WP_REST_Controller
{
  function __construct()
  {
    $this->namespace = 'auth/v1';
  }

  function register_routes()
  {
    register_rest_route($this->namespace, 'signup', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_item'),
      'args' => array(
        'username' => array(
          'required' => true,
          'type' => 'string',
          'description' => esc_html('The username entered by the user.'),
          'validate_callback' => array($this, 'validate_username'),
          'sanitize_callback' => function ($username) {
            return sanitize_user($username, true);
          },
        ),
        'password' => array(
          'required' => true,
          'type' => 'string',
          'description' => esc_html('The password entered by the user.'),
          'validate_callback' => array($this, 'validate_password'),
          'sanitize_callback' => function ($password) {
            return sanitize_text_field($password);
          },
        ),
        'email' => array(
          'required' => true,
          'type' => 'string',
          'description' => esc_html('The email entered by the user.'),
          'validate_callback' => array($this, 'validate_email'),
          'sanitize_callback' => function ($email) {
            return sanitize_email($email);
          },
        )
      ),
    ));
  }

  function create_item($request)
  {
    return rest_ensure_response($request->get_params());
  }

  function validate_username($username)
  {
    if (empty($username)) {
      return new WP_Error('username_empty', 'The `username` parameter cannot be empty.');
    }

    if (username_exists($username)) {
      return new WP_Error('username_exists', 'The `username` already exists.');
    }
  }

  function validate_password($password)
  {
    if (empty($password)) {
      return new WP_Error('password_empty', 'The `password` parameter cannot be empty.');
    }
  }

  function validate_email($email)
  {
    if (!is_email($email)) {
      return new WP_Error('email_invalid', 'The `email` parameter must be a valid email address.');
    }

    if (email_exists($email)) {
      return new WP_Error('email_exists', 'The `email` already exists.');
    }
  }
}
