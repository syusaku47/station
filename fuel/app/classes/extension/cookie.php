<?php

class Cookie extends Fuel\Core\Cookie
{

	public static function g($name = null, $default = null)
	{
		return unserialize(parent::get($name, $default = null));
	}

	public static function s($name, $value, $expiration = null, $path = null, $domain = null, $secure = null, $http_only = null)
	{
		return parent::set($name, serialize($value), $expiration, $path, $domain, $secure, $http_only);
	}

}
