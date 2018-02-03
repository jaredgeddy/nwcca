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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_nwcca');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9yw{Ll~d]?g;vrV/>PA~*y_<(~Ed(p:j)_ztR-db_5dJnLoAKZ1d[!:07gOMEt:.');
define('SECURE_AUTH_KEY',  'K1Il;qt;zf{1N+lj*d?`Fx|R^4>raxm(X0%W_pa~o7*>~S*)#_^7/YduMK 3ek{o');
define('LOGGED_IN_KEY',    'X*h-J?8si2E&$<@$8B=im7F7{@(eA!fU9W~S/% RS}R|)lLhZQT|h}:R-,_f=G>*');
define('NONCE_KEY',        'V2+7u3=U!mJ,oAHsM{H&E5*)?G&06:9hv}Sf-S TdoG*P:uZG=e/<O3;B^i*I=[U');
define('AUTH_SALT',        'QNulC2I3NQ2#3ms`(wG*@PNo)N#uUoGt*G[cAZPooAM9.Vv==0|uGcr)kU<Iru^<');
define('SECURE_AUTH_SALT', 'P.]E7[-zh Et>JD6R|9{A7t/86:gY19xO.qZ.-Z$yKc5kf,+ls&:>>c[FC{F2j,y');
define('LOGGED_IN_SALT',   'w:OCQFIj16.IMsBZO>t[Vim0S;Dr+~S&KVkH0N.[v]<G&),?s *R.!JS{Q/6ME<C');
define('NONCE_SALT',       'HTo:C4S,;xZe =WR/jd*MKB+fG=iPyk+3KrLof I_vKpd.ZwKQ6pQ]V4B$x9QQEN');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
