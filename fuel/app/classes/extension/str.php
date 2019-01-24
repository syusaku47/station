<?php

class Str extends Fuel\Core\Str
{

	public static function hash()
	{
		return Str::random('alnum', 64);
		// $tmp = Auth::hash_password(parent::random());
		// return $url ? str_replace('/', '', $tmp) : $tmp;
		// return Str::random('sha1');
	}

	/*
	 * function truncate($value, $len, $safix = '..')
	 * {
	 * if (mb_strwidth($value) <= $len)
	 * {
	 * return $value;
	 * }
	 *
	 * $len = $len - mb_strwidth($safix);
	 *
	 * return mb_strimwidth($value, 0, $len) . $safix;
	 * }
	 */
	public static function elapsed($date_time)
	{
		if (! $date_time) {
			return '';
		}
		
		$time = strtotime($date_time);
		$sour = (func_num_args() == 1) ? time() : func_get_arg(1);
		
		$minute = 60;
		$hour = $minute * 60;
		$day = $hour * 24;
		$week = $day * 7;
		$month = $day * 30;
		$year = $day * 365;
		
		$tt = $time - $sour;
		// if ($tt / $year < -1) return abs(round($tt / ElapsedTime::SECYEAR)) . '年前';
		// if ($tt / $month < -1) return abs(round($tt / ElapsedTime::SECMONTH)) . 'ヶ月前';
		if ($tt / ($week * 3) < - 1) {
			return date('Y-m-d', strtotime($date_time));
		}
		if ($tt / $week < - 1) {
			return abs(round($tt / $week)) . '週間前';
		}
		if ($tt / $day < - 1) {
			return abs(round($tt / $day)) . '日前';
		}
		if ($tt / $hour < - 1) {
			return abs(round($tt / $hour)) . '時間前';
		}
		if ($tt / 60 < - 1) {
			return abs(round($tt / $minute)) . '分前';
		}
		return '0分前';
	}

	public static function file_unit($bytes, $precision = 0, $type = 'bin')
	{
		$units = array(
			defined('_HUMAN_FILE_SIZE_BYTES') ? _HUMAN_FILE_SIZE_BYTES : 'Bytes',
			defined('_HUMAN_FILE_SIZE_KB') ? _HUMAN_FILE_SIZE_KB : 'KB',
			defined('_HUMAN_FILE_SIZE_MB') ? _HUMAN_FILE_SIZE_MB : 'MB',
			defined('_HUMAN_FILE_SIZE_GB') ? _HUMAN_FILE_SIZE_GB : 'GB',
			defined('_HUMAN_FILE_SIZE_TB') ? _HUMAN_FILE_SIZE_TB : 'TB',
			defined('_HUMAN_FILE_SIZE_PB') ? _HUMAN_FILE_SIZE_PB : 'PB',
			defined('_HUMAN_FILE_SIZE_EB') ? _HUMAN_FILE_SIZE_EB : 'EB',
			defined('_HUMAN_FILE_SIZE_ZB') ? _HUMAN_FILE_SIZE_ZB : 'ZB',
			defined('_HUMAN_FILE_SIZE_YB') ? _HUMAN_FILE_SIZE_YB : 'YB'
		);
		
		if (abs($bytes) < 1024) {
			$precision = 0;
		}
		
		if ($bytes < 0) {
			$sign = '-';
			$bytes = abs($bytes);
		} else {
			$sign = '';
		}
		
		if (strtolower($type) == 'si') {
			$log = 1000;
		} else {
			$log = 1024;
		}
		
		$exp = intval(log($bytes) / log($log));
		$unit = $units[$exp];
		$bytes = $bytes / pow($log, floor($exp));
		$bytes = sprintf('%.' . $precision . 'f', $bytes);
		return $sign . $bytes . ' ' . $unit;
	}
}
