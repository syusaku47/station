<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(

	/**
	 * DB write connection, leave null to use same value as db_connection
	 */
	'db_write_connection' => null,

	/**
	 * DB table name for the user table
	 */
	'table_name' => 'users',

	/**
	 * Array, choose which columns from the users table are selected.
	 *  must include: username, password, email, last_login,
	 * login_hash, group & profile_fields
	 */
	'table_columns' => array('*'),

	/**
	 * prefix to use for the cache
	 */
	'cache_prefix' => 'auth',

	/**
	 * This will allow you to use the group & acl driver for non-logged in users
	 */
	'guest_login' => true,

	/**
	 * Remember-me functionality
	 */

      'remember_me' => array(
        'enabled' => true,
      ),
      'login_hash_salt' => 'xCgK4hd4s?H-',
      'username_post_key' => 'email',
      'password_post_key' => 'password',


	/**
	 * This will allow the same user to be logged in multiple times.
	 *
	 * Note that this is less secure, as session hijacking countermeasures have to
	 * be disabled for this to work!
	 */
	'multiple_logins' => false,

	/**
	 * $_POST key for login username
	 */
	'username_post_key' => 'email',

	/**
	 * $_POST key for login password
	 */
	'password_post_key' => 'password',
);
