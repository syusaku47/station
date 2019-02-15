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

// Bootstrap the framework - THIS LINE NEEDS TO BE FIRST!
require COREPATH.'bootstrap.php';

// Add framework overload classes here
\Autoloader::add_classes(array(
	//'View' => APPPATH . 'classes/extension/parser/view.php',
	//'Validation' => APPPATH . 'classes/extension/validation.php',
	//'Pagination' => APPPATH . 'classes/extension/pagination.php',
	//'DB' => APPPATH . 'classes/extension/db.php',
	'Log' => APPPATH . 'classes/extension/log.php',
	'File' => APPPATH . 'classes/extension/file.php',
	'Str' => APPPATH . 'classes/extension/str.php',
	'Lang' => APPPATH . 'classes/extension/lang.php',
	'Cookie' => APPPATH . 'classes/extension/cookie.php',
	//'Email_Driver_Mail' => APPPATH . 'classes/extension/email/driver/mail.php',
	//'Email_Driver' => APPPATH . 'classes/extension/email/driver.php',
	'Query' => APPPATH . 'classes/extension/orm/query.php',
	'Query_Soft' => APPPATH . 'classes/extension/orm/query/soft.php',
	'Orm\Observer_Uuid' => APPPATH . 'classes/extension/orm/observer/uuid.php',
	'Orm\Observer_File' => APPPATH . 'classes/extension/orm/observer/file.php',
	'Orm\Observer_Timezone' => APPPATH . 'classes/extension/orm/observer/timezone.php',
	'Orm\Observer_Cryption' => APPPATH . 'classes/extension/orm/observer/cryption.php',
	// 'Auth_Login_Ormauth' => APPPATH . 'classes/extension/auth/login/ormauth.php',
	'Auth_User' => APPPATH . 'classes/model/auth/user.php',
	'Auth_Group' => APPPATH . 'classes/model/auth/group.php'
));

// Register the autoloader
\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
Fuel::$env = Arr::get($_SERVER, 'FUEL_ENV', Arr::get($_ENV, 'FUEL_ENV', getenv('FUEL_ENV') ?: Fuel::DEVELOPMENT));

// Initialize the framework with the config file.
\Fuel::init('config.php');
