<?php
use Auth\Model\Auth_Metadata;

class Auth_User extends \Auth\Model\Auth_User
{
  use Model_Base_Plugin_Search;

//  protected static $_belongs_to = array(
//    'group' => array(
//      'model_to' => 'Auth_Group',
//      'key_from' => 'group_id',
//      'key_to' => 'id',
//      'cascade_delete' => false
//    )
//  );

  protected static $_has_one = array(

//        'metadata' => array(
//            'model_to' => 'Model\\Auth_Metadata',
//            'key_from' => 'id',
//            'key_to'   => 'parent_id',
//            'cascade_delete' => true,
//        ),
//        'userpermission' => array(
//            'model_to' => 'Model\\Auth_Userpermission',
//            'key_from' => 'id',
//            'key_to'   => 'user_id',
//            'cascade_delete' => false,
//        ),
//        'providers' => array(
//            'model_to' => 'Model\\Auth_Provider',
//            'key_from' => 'id',
//            'key_to'   => 'parent_id',
//            'cascade_delete' => true,
//        ),
//    'nickname' => array(
//      'model_to' => 'Model\\Auth_Metadata',
//      'key_from' => 'id',
//      'key_to'   => 'parent_id',
//      'cascade_delete' => true,
//      'conditions' => [
//        'where' => [
//          [
//            'key', '=', 'nickname'
//          ]
//        ]
//      ]
//    ),
  );

  protected static $_to_array_exclude = [
    'password',
    'user_id',
    'login_hash',
    'previous_login',
    'last_login',
    'created_at',
    'updated_at'
  ];

  /*
   * ユーザー画像アイコン
   */
  const DEFAULT_ICON = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAIAAAC1nk4lAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAghJREFUeNrsmLFKA0EQhpNFQURESBGbENTCQsilUrA3WAXMW9ibpzh730IhleQBBLtEsLARgo0WAQsRweL8qyAqejPz766nN1wVMpMvezv//rPVLMsqRQtXKWCU0KFijljr/uYCz3Qyfn58mH24uFKvNZPVzV08rB+qUhpxOrkaDdL3rJ8D9O1uv9Zs/Qro6+HJ7eVZzi+v7xxsdQ4jQ48Gx3fjoSilkXTa3aNojYg1lhIjkILEONDYx/l3xYdAItIjQKPzbPsqDQ0NafteK34MpKNIaGiKrgeFxgnCUPdxUGjj3jAWiew9dNyRoXG2l9bUwwqxiiih4Tbt0OoiSmiKOVYX0UMbdwjSQ0Mj4Ogt0JZ0Z9iRLTh6XS4SLSOMSfIwg8DRS7OQYhxe/uW4VdTBtqhXCMU4xuOG4IbpPO29vjx54phfWNrvn/JXmrgpjcUF0OqjhF5cAL1c36A40i/VEMV9NeLads8HtLSsDLqR7PmAlpZ10h6ntyMKoqxfnVY4JHpBp1gYYjvqRgGne6FR5NkETdQQXSmne6esaTzoZQ1F+9RF9NO4VKeI6unC/6T9b+v9tNE/WdL10Bb/JHVIzMlFrX1G0XRR2t8oPi68AtiVxzrY6m6YIk/jUv9kuSxlXiGIIChmiwAtkgKK2SJA5/dPaofEh84vYawRs7zLK6H/GvSbAAMA3065U2Z3vtEAAAAASUVORK5CYII=';

  /**
   * Sort
   */
  public static $is_descendings = [
    0 => [
      'label' => '降順',
      'value' => 'desc'
    ],
    1 => [
      'label' => '昇順',
      'value' => 'asc'
    ]
  ];

  public static $default_sort = [];

  public static $sorts = [
    '1' => [
      'label' => 'ID順',
      'value' => 'id'
    ],
    '2' => [
      'label' => 'メールアドレス順',
      'value' => 'email'
    ],
    '3' => [
      'label' => '...名前順',
      'value' => 'fullname.value'
    ],
    '4' => [
      'label' => '支店順',
      'value' => 'store_id.value'
    ],
  ];

  public static function get_query($d)
  {
    $q = self::query();

    $q = $q->related('first_name', []);

    $q = $q->related('last_name', []);

    $q = $q->related('fullname', []);

    $q = $q->related('store_id', []);

    // フリーワード - ユーザ検索
    if ($tmp = @$d['q']) {
      $v = '%' . $tmp . '%';
      $q = $q->and_where_open()
        ->where('email', 'like', $v)
        ->or_where('last_name.value', 'like', $v)
        ->or_where('first_name.value', 'like', $v)
        ->or_where('fullname.value', 'like', $v)
        ->and_where_close();
    }

    // 無効状況の判定は、gruop_idで判定
    if ($d['valid_flag'] == 1) {
      $q = $q->and_where_open()
        ->where('group_id', 'not like', 8)
        ->where('group_id', 'not like', 9)
        ->and_where_close();
    }

    if ($tmp = @$d['store_id']) {
      $q = $q->where('store_id.value', $tmp);
    }

    if ($tmp = @$d['group_id']) {
      $q = $q->where('group_id', 'in', $tmp);
    }
    if ($tmp = @$d['default']['group_id']) {
      $q = $q->where('group_id', $tmp);
    }

    if ($tmp = @$d['not']) {

      $q = $q->where('id', 'not in', $tmp);
    }

    return $q;
  }

  public static function get_user()
  {
    return self::find(Auth::get_user_id()[1]);
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
   * Hash認証
   *
   * ***************************************************
   */
  public function create_hash()
  {
    $this->hash =  Str::random('alnum', 64);
    $this->save();
  }

  public function delete_hash()
  {
    $meta = Auth_Metadata::query()->select('value')
      ->where('parent_id', $this->id)
      ->where('key', 'hash')
      ->get_one();
    $tmp = array('created_at', strtotime('-1 day'));
    $meta->set($tmp)->save();
  }

  /*
   * ハッシュからユーザ情報取得
   * ハッシュの有効期間は、1日(過ぎたものはfalse)
   */
  public static function by_hash($hash, $name = 'hash', $time = '-1 day')
  {
    $meta = Auth_Metadata::query()->where('key', $name)
      ->where('value', $hash)
      ->where('created_at', '>=', strtotime($time))
      ->get_one();
    if (! $meta) {
      return false;
    }
    return $meta->user;
  }

  /*
   * ***************************************************
   *
   * OAuth2用
   *
   * ***************************************************
   */
  public function create_token($expires_in = null)
  {
    if (! $expires_in) {
      $expires_in = 60 * 60 * 24; // 1day
    }

    $this->access_token = Str::hash();
    $this->access_token_expires = now($expires_in . ' second');
    $this->refresh_token = Str::hash();
    $this->save();

    return [
      'expires_in' => $expires_in,
      'access_token' => $this->access_token,
      'refresh_token' => $this->refresh_token,
      'token_type' => 'bearer'
    ];
  }

  public static function by_access_token()
  {
    if (! $token = \Input::headers('Authorization', false)) {
      return false;
    }
    $access_token = trim(str_replace('Bearer', '', $token));

    // トークン存在チェック
    $meta = Auth_Metadata::query()->where('key', 'access_token')
      ->where('value', $access_token)
      ->get_one();
    if (! $meta) {
      return false;
    }

    // トークンの期限チェック
    $meta = Auth_Metadata::query()->where('parent_id', $meta->parent_id)
      ->where('key', 'access_token_expires')
      ->where('value', '>=', now())
      ->get_one();
    if (! $meta) {
      return false;
    }

    return $meta->user;
  }

  public static function by_refresh_token($refresh_token)
  {
    $meta = Auth_Metadata::query()->where('key', 'refresh_token')
      ->where('value', $refresh_token)
      ->get_one();
    if (! $meta) {
      return false;
    }
    return $meta->user;
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

  /*
   * ***************************************************
   *
   * その他
   *
   * ***************************************************
   */
  public static function invite($username, $email, $last_name, $first_name, $fullname, $store_id, &$msg = null)
  {
    try {
      $user_id = \Auth::create_user($username, 'password', $email, \Auth_Group::INVITEES, [
        'last_name' => $last_name,
        'first_name' => $first_name,
        'fullname' => $fullname,
        'store_id' => $store_id,
        'hash' => Str::random('alnum', 64),
      ]);

      $user = self::find($user_id);
    } catch (\SimpleUserUpdateException $e) {
      if ($e->getCode() == 2) {
        $msg = 'このメールアドレスは使用されています';
      } else if ($e->getCode() == 11) {
        $msg = '11';
      } else {
        $msg = $e->getMessage();
      }
      return false;
    }

    return $user;
  }

  public static function unique_email($email, $user = null)
  {
    $q = self::query()->where('email', $email);
    if ($user) {
      if (! is_object($user)) {
        $user = self::find($user);
      }
      $q = $q->where('id', '<>', $user->id);
    }

    return $q->count() == 0;
  }

  public function change_group($group_id)
  {
    $this->group_id = $group_id;
    $this->save();
  }

  public function get_file($type)
  {
    foreach ((array) $this->files as $file) {
      if ($file->type == $type) {
        return $file;
      }
    }
    return null;
  }

  public function &__get($name)
  {
    $tmp = null;
    switch ($name) {
      case 'hash':
      case 'new_email':
      case 'icon':
      case 'raw_password':
        // これらの項目はなぜかEAVとして取得できないのでAuth_Metadataから直接取得するようにした
        // try {
        // $tmp = parent::_get_eav($name);
        // $tmp = \Auth\Model\Auth_User::_get_eav($name);
        // } catch (\OutOfBoundsException $e) {
        // $tmp = null;
        // }
        $meta = Auth_Metadata::query()->select('value')
          ->where('parent_id', $this->id)
          ->where('key', $name)
          ->get_one();
        $tmp = $meta ? $meta->value : null;
        break;

      default:
        $tmp = \Auth\Model\Auth_User::__get($name);
      //$tmp = parent::__get($name);
    }
    return $tmp;
  }

  public function __set($name, $value)
  {
    $bt = debug_backtrace();
    if ($bt[1]['class'] == 'Orm\Model') {
      parent::__set($name, $value);
    } else {
      switch ($name) {
        case 'hash':
        case 'new_email':
        case 'icon':
          // EAV効かないのでここで
          $meta = Auth_Metadata::forge();
          $meta->parent_id = $this->id;
          $meta->key = $name;
          $meta->value = $value;
          $meta->save();
          break;

        default:
          parent::__set($name, $value);
          break;
      }
    }
  }

  /**
   * 退会
   */
  public function withdraw()
  {
    \Auth::delete_user($this->username);
    // \Auth::update_user(['group_id' => \Auth_Group::INVALIDS], $user->username);
  }

  public static function dummy($count = 3)
  {
    foreach ([
               [
                 'alice@app.localhost',
                 'アリス'
               ],
               [
                 'bob@app.localhost',
                 'ボブ'
               ],
               [
                 'carol@app.localhost',
                 'キャロル'
               ]
             ] as $u) {

      try {
        $id = \Auth::create_user($u[0], '123456', $u[0], \Auth_Group::USERS, [
          'fullname' => $u[1]
          // 'icon' => \Auth_User::DEFAULT_ICON
        ]);
      } catch (\Exception $e) {
        // メアド重複の場合はスキップ
        continue;
      }
      $user = \Auth_User::find($id);
// 			\Model_Letter::dumy($user);
    }

    // for ($u = 1; $u <= $count; $u ++) {

    // $person = \Dummy::i()->person;
    // try {
    // $id = \Auth::create_user(\Str::random('alnum', 16), '123456', 'user' . $u . '@foo.bar', \Auth_Group::USERS, [
    // 'fullname' => $person->name,
    // 'icon' => \Auth_User::DEFAULT_ICON
    // ]);
    // } catch (\Exception $e) {
    // // メアド重複の場合はスキップ
    // continue;
    // }
    // $user = \Auth_User::find($id);

    // \Model_Plan::dummy($user);
    // }
  }
}
