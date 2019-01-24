<?php

class Email_Driver extends Email\Email_Driver
{

	public function get_subject()
	{
		//return $this->subject;
		return mb_decode_mimeheader($this->subject);
	}

	public function body($body, $data = null)
	{
		//if (pathinfo($body, PATHINFO_EXTENSION) == 'tpl')
		if ($data)
		{
			$body = \View::forge($body, $data);
		}

		$this->body = (string) $body;

		return $this;
	}

	protected function validate_addresses()
	{
		$failed = array();

		foreach (array('to', 'cc', 'bcc') as $list)
		{
			foreach ($this->{$list} as $recipient)
			{
				if (in_array(@explode('@', $recipient['email'])[1], ['localhost']))
				{
					continue;
				}
				if (!filter_var($recipient['email'], FILTER_VALIDATE_EMAIL))
				{
					$failed[$list][] = $recipient;
				}
			}
		}

		if (count($failed) === 0)
		{
			return true;
		}

		return $failed;
	}

	protected function _send()
	{
		
	}

}
