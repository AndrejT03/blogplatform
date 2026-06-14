<?php
/**
 * Example WordPress configuration for BlogPlatform / Aperture.
 *
 * Copy this file to wp-config.php and fill in the values for your local or
 * hosting environment. Do not commit the real wp-config.php file.
 */

define( 'DB_NAME', getenv( 'DB_NAME' ) ?: 'database_name_here' );
define( 'DB_USER', getenv( 'DB_USER' ) ?: 'username_here' );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) ?: 'password_here' );
define( 'DB_HOST', getenv( 'DB_HOST' ) ?: 'localhost' );

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         getenv( 'AUTH_KEY' ) ?: 'change-me' );
define( 'SECURE_AUTH_KEY',  getenv( 'SECURE_AUTH_KEY' ) ?: 'change-me' );
define( 'LOGGED_IN_KEY',    getenv( 'LOGGED_IN_KEY' ) ?: 'change-me' );
define( 'NONCE_KEY',        getenv( 'NONCE_KEY' ) ?: 'change-me' );
define( 'AUTH_SALT',        getenv( 'AUTH_SALT' ) ?: 'change-me' );
define( 'SECURE_AUTH_SALT', getenv( 'SECURE_AUTH_SALT' ) ?: 'change-me' );
define( 'LOGGED_IN_SALT',   getenv( 'LOGGED_IN_SALT' ) ?: 'change-me' );
define( 'NONCE_SALT',       getenv( 'NONCE_SALT' ) ?: 'change-me' );

$table_prefix = 'wp_';

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', getenv( 'WP_ENVIRONMENT_TYPE' ) ?: 'local' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
