<?php

class Email_Driver_Mail extends Email\Email_Driver_Mail
{

	protected function _send()
	{
		$cfg = Config::get('email');

		$to = $this->get_to();
		if (array_key_exists('', $to))
		{
			// toのアドレスが未設定の場合
			return true;
		}

		// Prefix
		$this->subject($cfg['prefix'] . $this->get_subject());

		// from
		$from = $this->get_from()['email'];
		if (strpos($from, '@') === false)
		{
			list($email, $name) = $this->n2a($from, true);
			$this->from($email, $name);
		}

		$message = $this->build_message();
		$return_path = ($this->config['return_path'] !== false) ? $this->config['return_path'] : $this->config['from']['email'];
		if (!@mail(static::format_addresses($this->to), $this->subject, $message['body'], $message['header'], '-oi -f ' . $return_path))
		{
			$from = $this->get_from();
			$from = $from['email'];
			$to = $this->get_to();
			$to = (count($to) == 0) ? '' : key($to);
			$cc = $this->get_cc();
			$cc = (count($cc) == 0) ? '' : key($cc);
			Log::error('Mail transmission failed. from:' . $from . ' to:' . $to . ' cc:' . $cc . ' subject:' . $em->get_subject());

			throw new \EmailSendingFailedException('Failed sending email');
		}
		return true;



		/* Original
		  $message = $this->build_message();
		  $return_path = ($this->config['return_path'] !== false) ? $this->config['return_path'] : $this->config['from']['email'];
		  if ( ! @mail(static::format_addresses($this->to), $this->subject, $message['body'], $message['header'], '-oi -f '.$return_path))
		  {
		  throw new \EmailSendingFailedException('Failed sending email');
		  }
		  return true; */
	}

	private function n2a($name, $single = false)
	{
		$accounts = Config::get('email.account.' . $name);
		if (!$accounts)
		{
			throw new Exception();
		}
		$accounts = explode(';', $accounts);

		if ($single)
		{
			$tmp = explode(',', $accounts[0]);
			return [$tmp[0], @$tmp[1]];
		}
		else
		{
			$addr = [];
			foreach ($accounts as $ac)
			{
				$tmp = explode(',', $ac);
				$addr[$tmp[0]] = @$tmp[1];
			}
			return $addr;
		}
	}

}
