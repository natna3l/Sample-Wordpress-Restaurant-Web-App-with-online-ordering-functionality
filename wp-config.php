<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'restaurant' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'O_-;1Zp?]Tcrf7yj%G?Z`&}bmr!h4{LnZLQJ:0HXZ&CqULOvRdRAaX]&b71y+D|M' );
define( 'SECURE_AUTH_KEY',  'O_2Q3/PlT>%2m+`-1d8]4xXZoF}0H3,rp:x6.pB#-l(&Xyks7ZxN r8Qe;Go/1L0' );
define( 'LOGGED_IN_KEY',    '4~Tn>g@r*T+RwXVG@qEuJwFw~Q09I}7W<)1pthqY -bgkcnPC&bqJ-of)6[>HAoC' );
define( 'NONCE_KEY',        'vR`:/&G-Q,r!N]:zq(~%Oz3n2pm!4)T,,5|Y>/ :H(+bt!+PvWLM<,d}F6b2uGk8' );
define( 'AUTH_SALT',        'w:=g#IY.SR]Lb~)+<fR<[MNPbXoNl7q0vFsio-cR2x#12bzm^ItP9JjfL2(d(y4-' );
define( 'SECURE_AUTH_SALT', ')ASe`1/`&2%k^*gV1|nUDq[)ru.2Fa5c)1{6HVF8jMM0>{{w/6_t^]VPM4A6OQre' );
define( 'LOGGED_IN_SALT',   '+15gWpZh63r27Eys.FtSA+(foL?7QKtSmbP&Xk<z kekRZVrT>Z|m[uHHTi#bf]R' );
define( 'NONCE_SALT',       'yMfZ3v?e/5Kr%BE#u&12J.rnQJp`vowhgj)Nyq3X=[(j$v=.3M7ptD_{1^We:]|:' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
