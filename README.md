# Aloud Auth WP Plugin

## TODO

### Errors

- [x] Improve signin errors.
- [x] Improve signup errors.
- [ ] Improve signout errors.
- [ ] Improve delete errors.
- [ ] Improve validate errors.

## List of error codes

### Sign In

- `400` `aloud_auth_missing_params`
- `400` `rest_invalid_param`
- `401` `aloud_auth_invalid_credentials`
- `403` `aloud_auth_invalid_host`

### Sign Up

- `400` `aloud_auth_missing_params`
- `400` `rest_invalid_param`
- `400` `aloud_auth_existing_email`
- `400` `aloud_auth_existing_username`
- `401` `aloud_auth_invalid_credentials`
- `403` `aloud_auth_invalid_host`

## Goals

### Authentication Methods

- [x] Cookie and Origin Authentication.

### Sign-Up Methods

- [x] Sign-up with username, email and password.
- [x] Sign-up passwordless with temporary code.

### Sign-In Methods

- [x] Sign-in with username and password.
- [x] Sign-in with email and password.
- [x] Sign-in passwordless with temporary code.

### Sign-Out Methods

- [x] Allow users to sign out.

### Delete Account

- [x] Allow users to delete their own accounts.
- [x] Allow users to delete their own accounts passwordless with temporary code.
