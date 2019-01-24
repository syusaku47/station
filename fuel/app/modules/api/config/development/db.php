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
 * -----------------------------------------------------------------------------
 *  Database settings for development environment
 * -----------------------------------------------------------------------------
 *
 *  These settings get merged with the global settings.
 *
 */

return array(
	'lisb' => array(
		'type'        => 'pdo',
		'connection'  => array(
			'dsn'        => 'mysql:host=db;dbname=lisb;',
			'username'   => 'lisb',
			'password'   => '123456',
		),
		'profiling'  => true,
	),
);
