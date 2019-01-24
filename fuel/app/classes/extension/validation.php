<?php

class Validation extends Fuel\Core\Validation
{

	public function add($name, $label = '', array $attributes = array(), array $rules = array())
	{
		$label = $label ?: $name;
		return $this->fieldset->add($name, $label, $attributes, $rules);
	}

	public function _validation_match_list($val, $list)
	{
		if (empty($val)) {
			return true;
		}
		
		$ptn = '/^(' . implode('|', array_keys($list)) . ')$/';
		if (is_array($val)) {
			foreach ($val as $v) {
				if (! preg_match($ptn, $v)) {
					return false;
				}
			}
		} else {
			if (! preg_match($ptn, $val)) {
				return false;
			}
		}
		return true;
	}

	public function _validation_valid_password($val, $min = 6, $max = 30)
	{
		// return mb_ereg('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{{$min},{$max}}+\z/i', $val);
		if (mb_ereg("^[a-zA-Z\d]{{$min},{$max}}$", $val)) {
			return true;
		} else {
			return false;
		}
	}

	public function _validation_valid_uuid($val)
	{
		if (empty($val)) {
			return true;
		}
		
		if (is_array($val)) {
			foreach ($val as $v) {
				if (! $this->is_uuid($v)) {
					return false;
				}
			}
		} else {
			if (! $this->is_uuid($val)) {
				return false;
			}
		}
		return true;
	}

	private function is_uuid($val)
	{
		// return preg_match('/\A[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[0-5][a-fA-F0-9]{3}-[089aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}\z/', $val);
		return preg_match('/\A[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}\z/', $val);
	}

	public function _validation_valid_postcode($val, $hyphen = false)
	{
		if (empty($val)) {
			return true;
		}
		$ptn = $hyphen ? '^\d{3}-\d{4}$' : '^\d{7}$';
		if (mb_ereg($ptn, $val)) {
			return true;
		} else {
			return false;
		}
	}

	public function _validation_valid_after($to, $field)
	{
		if (! $to) {
			return true;
		}
		
		if (! $from = $this->input($field)) {
			return true;
		}
		
		if ($from > $to) {
			$validating = $this->active_field();
			throw new \Validation_Error($validating, $to, [
				'valid_after' => [
					$field
				]
			], [
				$this->field($field)->label
			]);
		}
		
		return true;
	}

	/*
	 * public function _validation_valid_date($val)
	 * {
	 * if (empty($val))
	 * {
	 * return true;
	 * }
	 *
	 * if (mb_ereg('^\d{4}-\d{2}-\d{2}$', $val))
	 * {
	 * return true;
	 * }
	 * else
	 * {
	 * return false;
	 * }
	 * }
	 */
	public function _validation_valid_tel($val, $hyphen = true)
	{
		if (empty($val)) {
			return true;
		}
		$ptn = $hyphen ? '^\d{1,4}-\d{1,4}-\d{1,4}$' : '^\d{12}$';
		if (mb_ereg($ptn, $val)) {
			return true;
		} else {
			return false;
		}
	}

	public function _validation_valid_katakana($val)
	{
		mb_regex_encoding('UTF-8');
		$val = trim($val);
		if (mb_ereg('^[ア-ン゛゜ァ-ォャ-ョー「」、　]+$', $val)) {
			return true;
		} else {
			return false;
		}
	}

	public function _validation_valid_zenkaku($val)
	{
		$encoding = mb_internal_encoding();
		$len = mb_strlen($val, $encoding);
		for ($i = 0; $i < $len; $i ++) {
			$char = mb_substr($val, $i, 1, $encoding);
			if ($this->is_hankaku($char, true, true, $encoding)) {
				return false;
			}
		}
		return true;
	}

	public function _validation_valid_hankaku($val)
	{
		return $this->is_hankaku($val);
	}

	private function is_hankaku($str, $include_kana = false, $include_controls = false, $encoding = null)
	{
		if (! $include_controls && ! ctype_print($str)) {
			return false;
		}
		
		if (is_null($encoding)) {
			$encoding = mb_internal_encoding();
		}
		if ($include_kana) {
			$to_encoding = 'SJIS';
		} else {
			$to_encoding = 'UTF-8';
		}
		$str = mb_convert_encoding($str, $to_encoding, $encoding);
		
		if (strlen($str) === mb_strlen($str, $to_encoding)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 配列対応
	 */
	public function _validation_match_pattern($val, $pattern)
	{
		if (! is_array($val)) {
			return $this->_empty($val) || preg_match($pattern, $val) > 0;
		} else {
			foreach ($val as $v) {
				if (! $this->_empty($v)) {
					if (preg_match($pattern, $v) == 0) {
						return false;
					}
				}
			}
			return true;
		}
	}

	/**
	 * メールアドレスRF2822非準拠チェック
	 */
	public function _validation_valid_email($val)
	{
		if (! $val) {
			return true;
		}
		if ($this->ignore_email($val)) {
			return true;
		}
		$val = $this->parse($val);
		return $this->_empty($val) || filter_var($val, FILTER_VALIDATE_EMAIL);
		// return $this->_empty($val) || $this->filter_var_mail($val);
	}

	/**
	 * メールアドレス（複数）RF2822非準拠チェック
	 */
	public function _validation_valid_emails($val, $separator = ',')
	{
		if ($this->_empty($val)) {
			return true;
		}
		
		$emails = explode($separator, $val);
		
		foreach ($emails as $e) {
			if ($this->ignore_email($e)) {
				continue;
			}
			
			$e = $this->parse($e);
			if (! filter_var(trim($e, FILTER_VALIDATE_EMAIL))) 
			// if (!$this->filter_var_mail(trim($e)))
			{
				return false;
			}
		}
		return true;
	}

	private function ignore_email($email)
	{
		return in_array(@explode('@', $email)[1], [
			'localhost',
			'dummy'
		]);
	}

	/**
	 * RF2822非準拠の部分を除去
	 *
	 * @param type $email
	 * @return type
	 */
	private function parse($email)
	{
		$email = explode('@', $email);
		$name = @$email[0];
		$domain = @$email[1];
		
		// ドットの連続を除去
		for ($i = 0; $i < 5; $i ++) {
			$name = str_replace('..', '.', $name);
		}
		
		// @の前のドットを除去
		for ($i = 0; $i < 5; $i ++) {
			$last = substr($name, - 1);
			if ($last == '.') {
				$name = substr($name, 0, - 1);
			}
		}
		
		return $name . '@' . $domain;
	}
}
