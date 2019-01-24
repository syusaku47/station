<?php

class Log extends Fuel\Core\Log
{

	public static function params()
	{
		foreach (Input::post() as $key => $value) {
			l('[Param] ' . $key . ' = ' . (is_array($value) ? "\r\n" . var_export($value, true) : substr($value, 0, 100)));
		}
		
		// routes.phpの名前付きパラメータ確認
		/*
		 * foreach ($params as $k => $v)
		 * {
		 * Log::info($k . ' : ' . $v);
		 * }
		 */
	}

	public static function debug($obj, $all = false)
	{
		$msg = '';
		if ($obj instanceof Exception) {
			self::error($obj);
			return;
		} else if (is_object($obj)) {
			$msg .= var_export($obj, true);
		} else if (is_array($obj)) {
			$msg .= var_export($obj, true);
		} else if (is_null($obj)) {
			$msg .= 'null';
		} else {
			$msg .= $all ? $obj : substr($obj, 0, 500);
		}
		parent::debug($msg);
	}

	public static function error($e, $dummy = null)
	{
		if ($e instanceof Exception) {
			parent::error('----------------------------------------------------------------------------');
			parent::error('Message\t:' . $e->getMessage());
			parent::error('File\t:' . $e->getFile());
			parent::error('Line\t:' . $e->getLine());
			parent::error('Trace\t:\r\n' . $e->getTraceAsString());
			parent::error('----------------------------------------------------------------------------');
		} else {
			parent::error($e);
		}
	}
	
	// public static function operation($user, $action, $message, $reference_type = null, $reference = null)
	// {
	// $log = Model_Userlog::forge();
	// $log->user_ = empty($user) ? 'unknown' : $user->id;
	// $log->action = $action;
	// $log->message = $message;
	// $log->reference_type = $reference_type;
	// $log->reference_ = $reference;
	// $log->ip = $_SERVER['REMOTE_ADDR'];
	// $log->save();
	// }
}

if (! function_exists('l')) {

	function l($obj, $all = false)
	{
		Log::debug($obj, $all);
	}
}