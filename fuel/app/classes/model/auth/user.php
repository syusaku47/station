<?php
use Auth\Model\Auth_Metadata;

class Auth_User extends \Auth\Model\Auth_User
{


	protected static $_to_array_exclude = [
		'login_hash',
		'previous_login',
		'created_at',
		'updated_at'
	];

	public static $sorts = [
		'name' => [
			'value' => [
				'metadata.value' => 'asc'
			],
			'label' => '名前順'
		],
		'email' => [
			'value' => [
				'email' => 'asc'
			],
			'label' => 'メールアドレス順'
		]
	];

	public static function get_query($d)
	{
		$q = self::query()->related('metadata', [
			'join_on' => [
				[
					'key',
					'=',
					'fullname'
				]
			]
		]);

		if ($tmp = @$d['q']) {
			$v = '%' . $tmp . '%';
			$q = $q->where_open()
				->
			// ->where('username', 'like', $v)
			or_where('email', 'like', $v)
				->or_where_open()
				->
			// ->where('metadata.key', 'fullname') fullnameのみのJoinのため
			where('metadata.value', 'like', $v)
				->or_where_close()
				->where_close();
		}

		if ($tmp = @$d['group_id']) {
			$q = $q->where('group_id', 'in', $tmp);
		}
		if ($tmp = @$d['default']['group_id']) {
			$q = $q->where('group_id', $tmp);
		}

		l($d);
		if ($tmp = @$d['not']) {

			$q = $q->where('id', 'not in', $tmp);
		}

		return $q;
	}

	public static function get_user()
	{
		return self::find(Auth::get_user_id()[1]);
	}

	public static function get_waiting($id = null)
	{
		$q = self::query()->where('group_id', \Auth_Group::WAITING);
		if ($id) {
			$q = $q->where('id', $id);
		}
		return $q->get();
	}

	public static function by_email($email, $group = null)
	{
		$query = self::query()->where('email', $email);
		if (is_array($group)) {
			$query = $query->where('group_id', 'in', $group);
		} else if ($group) {
			$query = $query->where('group_id', $group);
		}
		return $query->get_one();
	}

	/*
	 * ***************************************************
	 *
	 * パスワード変更履歴管理
	 *
	 * ***************************************************
	 */
	public function valid_password($password)
	{
		$history = $this->get_password_history();
		return ! in_array(\Auth::hash_password($password), $history);
	}

	public function set_password($password, $capacity = 3)
	{
		$new = \Auth::hash_password($password);

		$history = $this->get_password_history();
		array_unshift($history, $new); // 先頭に追加
		$history = array_chunk($history, $capacity)[0]; // 先頭3つ取得

		$this->password_once = serialize($history);
		$this->password = $new;
		$this->save();
	}

	private function get_password_history()
	{
		return property_exists($this, 'password_once') ? unserialize($this->password_once) : [];
	}

	public function change_group($group_id)
	{
		$this->group_id = $group_id;
		$this->save();
	}

	public function &__get($name)
	{
		$tmp = null;
		switch ($name) {
			case 'is_superuser':
				$tmp = $this->group_id == Auth_Group::SUPERUSERS;
				break;
			default:
				$tmp = \Auth\Model\Auth_User::__get($name);
		}
		return $tmp;
	}

}
