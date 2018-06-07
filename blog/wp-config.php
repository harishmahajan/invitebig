<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'invitebig_blog');

/** MySQL database username */
define('DB_USER', 'buser');

/** MySQL database password */
define('DB_PASSWORD', 'bpass!');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '7?y|Wt8We%F0OUTw}..~]U>fuSdDM7_ZyhAk!n%-;a43uVfxEa9>:diVh{1tT%Q2');
define('SECURE_AUTH_KEY',  '!fcx0Y_iW.@5/H2t6!yJOtobKQ,TW%}arGv&iF[lZI#5N6UVX&DRhN]9T}cJgMY=');
define('LOGGED_IN_KEY',    '3G!`/87q_[7`tW$t&tcR9:+#~H>ZL>w&+m|K:EV!;/fGb[GI+q%|C?Uj+?Yct`ET');
define('NONCE_KEY',        'K.:xz(E+KuBtZ(z9%-vK0vk$,,YD_W:;S!(Big%<q}Zl#jKKFl-vh+qfb-aSB^%>');
define('AUTH_SALT',        '|+Id-JOJ@0EWaQ0>k:w,V<.]R->]2h8<E*FyN/AtU^D8J}8=6FZV]4up~Qm*%h[5');
define('SECURE_AUTH_SALT', 'f4Yu%NoATgX#P*gkD(3eo5.R(]4JU^PZ1U$|QP9Y|st<YTAq28:RWKau6S_UA.Qu');
define('LOGGED_IN_SALT',   'kF)[-;`:LkVD3jGOX*JO*I?Uu)bKP!9^^De:59?7;O`dWB<Cs<`HOXG/i-^Tufl1');
define('NONCE_SALT',       'ieTPL14!1Z&/(*PF#*BR!C0Xyq+kY)<8wHAMS)@W* 9~cuT-fHQ|IiB.TNQL%aPY');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
