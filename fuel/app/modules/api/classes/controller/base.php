<?php
namespace Api;

class Controller_Base extends \Controller_Rest
{

	protected $format = 'json';

	protected $response_code = 200;

	protected $err;

	// protected $body = ['data' => ['result' => 'succeeded']];
	protected $body = [
		'data' => []
	];

	protected $no_auth_actions = [];

	public function before()
	{
		parent::before();
	}

	public function after($response)
	{
		$response = parent::after($response);

		if ($this->err) {
			$this->body['error'] = $this->err;
			$this->body['data'] = '';
		}

		$this->response->set_headers([
			'Content-Type' => 'application/json',
			'Cache-Control' => 'no-store',
			'Pragma' => 'no-cache'
		]);

		return $this->response($this->body, $this->response_code);
	}

	public function __set($name, $value)
	{
		switch ($name) {
			case 'data':
				$this->body['data'] = ($value instanceof \Orm\Model) ? $value->to_array() : $value; // 不要列除去
				break;
			case 'list':
				$this->body['data'] = $value;
				break;

			case 'error':
				list ($code, $message) = is_array($value) ? $value : [
					$value,
					''
				];

				$this->response_code = substr($code, 0, 3);
				$this->err = [
					'code' => $code,
					'developer_message' => E::$messages[$code],
					'messages' => is_array($message) ? $message : [
						$message
					]
				];
				break;

			case 'upload_error':
				$this->error = [
					E::INVALID_PARAM,
					$value
				];
				break;
			default:
				$this->response = $value; // これがないとエラーに
		}
	}

	protected function verify_csrf()
	{
		if (! \Security::check_token()) {
			$this->error = E::INVALID_CSRF_TOKEN;
			return false;
		}
		return true;
	}

	protected function verify($fields = [], $search = false, $param = [])
	{
		$val = \Validation::forge();
		if ($search) {
			$fields = array_merge([
				'q' => [
					'validation' => [
						'max_length' => [
							255
						]
					]
				],
				'limit' => [
					'validation' => [
						'match_pattern' => [
							'/^\d+$/'
						]
					]
				],
				'p' => [
					'validation' => [
						'match_pattern' => [
							'/^\d+$/'
						]
					]
				]
			], $fields);
		}

		foreach ($fields as $k => $v) {
			if (is_numeric($k)) {
				$val->add($v);
			} else if ($k == 'model') {
				$val->add_model('Model_' . ucfirst($v));
			} else {
				$name = $k;
				$rules = [];
				@$v['label'] and $name = $v['label'];
				if (@$v['validation']) {
					foreach ($v['validation'] as $key => $rule) {
						if (is_numeric($key)) {
							$rules[] = $rule;
						} else {
							$rules[] = array_merge([
								$key
							], $rule);
						}
					}
				}
				$val->add($k, $name, [], $rules);
			}
		}

		$param = array_merge(\Input::all(), $param);
		if (! $val->run($param)) {
			$messages = [];
			foreach ($val->error() as $k => $m) {
				$messages[$k] = (string) $m;
			}

			$this->error = [
				E::INVALID_PARAM,
				$messages
			];
			return false;
		}
		return $val->validated();
	}

	protected function has_error()
	{
		return ! empty($this->err);
	}
}

class E
{

	const UNAUTHNTICATED = '4010';

	const INVALID_TOKEN = '4011';

	const FORBIDDEN = '4030';

	const INVALID_REQUEST = '4000';

	const INVALID_PARAM = '4001';

	const INVALID_CSRF_TOKEN = '4002';

	const NOT_FOUND = '4040';

	const CONFRICT = '4090';

	const SERVER_ERROR = '5000';

	public static $messages = [
		self::UNAUTHNTICATED => 'unauthenticated',
		self::INVALID_TOKEN => 'invalid_token',
		self::FORBIDDEN => 'forbidden',
		self::INVALID_REQUEST => 'invalid_request',
		self::INVALID_PARAM => 'invalid_parameter',
		self::INVALID_CSRF_TOKEN => 'invalid_csrf_token',
		self::NOT_FOUND => 'resource_not_found',
		self::CONFRICT => 'duplicate_resources',
		self::SERVER_ERROR => 'internal_server_error'
	];
}
