<?php

class Lang extends Fuel\Core\Lang
{

	public static function translate($token)
	{
		$tmp = explode('.', $token);
		if (count($tmp) == 1)
		{
			return;
		}
		$id = $tmp[0];
		$key = $tmp[1];

		$ls = ['ja'];
		foreach ($ls as $l)
		{
			self::save_lang($l, $token, $id, $key);
		}
	}

	private static function save_lang($lang, $token, $id, $key)
	{
		$ext = '.yml';
		self::set_lang($lang, true);
		if (!self::get($token))
		{
			$val = Inflector::humanize($key); // 翻訳
			self::set($token, $val);
			self::save($id . $ext, $id);
		}
	}

}
