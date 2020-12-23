# Allowed WP Plugin

WordPress plugin for registration and authentication over REST API.

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

## Docs

### Routes

- `/allowed/v1/signup` (params: `username`, `email`, `password`)
- `/allowed/v1/signup/passwordless` (params: `username`, `email`, `code`)
- `/allowed/v1/signin` (params: `username`, `email`, `password`)
- `/allowed/v1/signin/passwordless` (params: `username`, `email`, `code`)
- `/allowed/v1/signout`
- `/allowed/v1/delete` (params: `password`)
- `/allowed/v1/delete/passwordless` (params: `code`)
- `/allowed/v1/validate/`

### List of error codes

#### Sign In

- `400` `allowed_missing_params`
- `400` `rest_invalid_param`
- `401` `allowed_invalid_credentials`
- `403` `allowed_invalid_host`

#### Sign Up

- `400` `allowed_missing_params`
- `400` `rest_invalid_param`
- `400` `allowed_existing_email`
- `400` `allowed_existing_username`
- `401` `allowed_invalid_credentials`
- `403` `allowed_invalid_host`

#### Sign Out

- `401` `allowed_not_authenticated`

#### Delete

- `400` `allowed_missing_params`
- `400` `rest_invalid_param`
- `401` `allowed_not_authenticated`
- `401` `allowed_invalid_credentials`
- `500` `allowed_cannot_delete_account`

#### Validate

- `401` `allowed_not_authenticated`
