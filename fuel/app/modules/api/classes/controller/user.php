<?php

namespace Api;

class Controller_User extends Controller_Base
{
  public function post_sign_up()
  {
    try {
      if (!$data = $this->verify([
        'username' => [
          'label' => 'ユーザ名',
          'validation' => [
            'required',
            'max_length' => [
              255
            ]
          ]
        ],
        'password' => [
          'label' => 'パスワード',
          'validation' => [
            'required',
            'max_length' => [
              255
            ]
          ]
        ],
        'email' => [
          'label' => 'メールアドレス',
          'validation' => [
            'required',
            'valid_email'
          ]
        ]
      ])) {
        return;
      }
      \Auth::create_user(
        \Input::post('username'),
        \Input::post('password'),
        \Input::post('email'),
        1,
        array('nickname' => \Input::post('username'))
      );
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->body['errorlog'] = $e->getMessage();
      $this->error = [
        E::INVALID_REQUEST,
        $e->getMessage()
      ];
    }
  }

  public function post_login()
  {
    try {
      if (!$data = $this->verify([
        'email' => [
          'label' => 'メールアドレス',
          'validation' => [
            'required',
            'valid_email'
          ]
        ],
        'password' => [
          'label' => 'パスワード',
          'validation' => [
            'required',
            'max_length' => [
              255
            ]
          ]
        ]
      ])) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          'メールアドレスまたはパスワードが違います'
        ];

        return;
      }
      if (\Auth::login(\Input::post('email'), \Input::post('password'))) {
        unset($this->body['data']);
        $this->success();
        return;
      }
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        'メールアドレスまたはパスワードが違います'
      ];

    } catch (\Exception $e) {
      \Log::error($e->getMessage());
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        'メールアドレスまたはパスワードが違います'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function post_logout()
  {
    try {
      \Auth::logout();
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        $e->getMessage()
      ];
    }
  }

  public function patch_update()
  {
    try {
      $user = \Auth_User::get_user();

      $params = array();
      $nickname = \Input::patch('nickname');
      $age = \Input::patch('age');
      $sex = \Input::patch('sex');
      if (!empty($email)) {
        $params['email'] = $email;
      }
      if (!empty($nickname)) {
        $params['nickname'] = $nickname;
      }
      if (!empty($age)) {
        if(!is_numeric($age)){
          $this->failed();
          $this->error = [
            E::INVALID_REQUEST,
            '年齢を正しく入力してください。'
          ];
          return;
        }else{
          $params['age'] = $age;
        }
      }
      if (!empty($sex)) {
        $params['sex'] = $sex;
      }

      \Auth::update_user($params, $user->username);
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      \Log::error($e->getMessage());
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        '更新に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function get_my_info()
  {
    try {
      if (!$user = \Auth_User::get_user()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          '認証エラーです。'
        ];
        return;
      }
      $user->to_array();
      $info['id'] = $user['id'];
      $info['nickname'] = $user['nickname'];
      $info['email'] = $user['email'];
      $info['age'] = $user['age'];
      $info['sex'] = $user['sex'];
      $info['last_login'] = $user['last_login'] == '0' ? $user['last_login'] : date("Y-m-d H:i:s",$user['last_login']);
      $info['previous_login'] = $user['previous_login'] == '0' ? $user['previous_login'] : date("Y-m-d H:i:s",$user['previous_login']);
      $info['created_at'] = date("Y-m-d H:i:s",$user['created_at']);
      $info['updated_at'] = $user['updated_at'] == '0' ? $user['updated_at'] : date("Y-m-d H:i:s",$user['updated_at']);

      $this->success();
      $this->data = $info;
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        '認証エラーです。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function get_one()
  {
    $id = \Input::get('id');
    try {
      if (!$user = \Auth_User::find($id)) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          '該当するユーザ情報がありませんでした。'
        ];
        return;
      }
      $user->metadata;
      $this->success();
      $this->data = $user;


    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        '該当するユーザ情報がありませんでした。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function patch_change_password()
  {
    try {

      $old_password = \Input::patch('old_password');
      $new_password = \Input::patch('new_password');

      \Auth::change_password($old_password, $new_password
      );
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        'パスワードの変更に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function post_send_reissue_mail()
  {

    if (!$data = $this->verify([
      'email' => [
        'label' => 'メールアドレス',
        'validation' => [
          'required',
          'valid_email'
        ]
      ]
    ])) {
      return;
    }

    if (!$user = \Auth_User::by_email($data['email'])) {
      $this->failed();
      $this->error = [
        E::NOT_FOUND,
        '該当するユーザ情報がありませんでした。'
      ];
      return;
    }

    try {

      $user->create_hash();
      $path = \Input::post('path');

      $email_user = [];
      $email_user['user'] = $user;
      $reissue_url = \Uri::base(false) . $path;
      $email_user['reissue_url'] = $reissue_url;
      $this->data = $reissue_url;
      \Email::forge()
        ->from('info')
        ->to($user->email)
        ->subject('【みんなの駅】パスワード再発行のご案内')
        ->body(\View::forge('preissue', $email_user))
        ->send();
      unset($this->body['data']);
      $this->success();

    } catch (\Exception $e) {
      $this->failed();
      \Log::error($e->getMessage());
      $this->error = [
        E::INVALID_REQUEST,
        'メールの送信に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function get_by_hash()
  {
    $hash = \Input::get('hash');
    if (!$user = \Auth_User::by_hash($hash)) {
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        '有効ではありません。'
      ];
    }
    $keys = ['id', 'nickname', 'email'];

    $list = [];
    foreach ($keys as $key) {
      $list[$key] = $user[$key];
    }
    $this->success();
    $this->data = $list;
  }

  public function post_password_reissue()
  {

    if (!$data = $this->verify([
      'id',
      'password' => [
        'label' => 'パスワード',
        'validation' => [
          'required',
          'valid_password'
        ]
      ]
    ])) {
      $this->error = [
        E::INVALID_PARAM,
        '不正なパラメータです。'
      ];
      return;
    }

    $password = \Auth::hash_password($data['password']);

    try {
      $user = \Auth_User::find($data['id']);
      if ($user == null) {
        $this->error = [
          E::INVALID_PARAM,
          '該当するユーザ情報がありませんでした。'
        ];
      } else {
        $user->password = $password;

        $user->save();
        $user->hash = '';
        $this->success();

      }

    } catch (\Exception $e) {
      $this->failed();
      \Log::error($e->getMessage());
      $this->error = [
        E::SERVER_ERROR,
        '更新に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function delete_delete_one()
  {
    $id = \Input::delete('id');
    try {
      if (!$user = \Auth_User::find($id)) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          '該当するユーザ情報がありませんでした。'
        ];
        return;
      }
      \Auth::delete_user($user->username);
      unset($this->body['data']);
      $this->success();

    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        '削除に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

  public function get_user_list()
  {
    try {
      $list = array();
      $users  = \Auth_User::find('all');
      foreach ($users as $user){
        $user->to_array();
        $tmp = array();
        $tmp['id'] = $user['id'];
        $tmp['nickname'] = $user['nickname'];
        $tmp['email'] = $user['email'];
        $tmp['age'] = $user['age'];
        $tmp['sex'] = $user['sex'];
        $tmp['last_login'] = $user['last_login'] == '0' ? $user['last_login'] : date("Y-m-d H:i:s",$user['last_login']);
        $tmp['previous_login'] = $user['previous_login'] == '0' ? $user['previous_login'] : date("Y-m-d H:i:s",$user['previous_login']);
        $tmp['created_at'] = date("Y-m-d H:i:s",$user['created_at']);
        $tmp['updated_at'] = $user['updated_at'] == '0' ? $user['updated_at'] : date("Y-m-d H:i:s",$user['updated_at']);
        $list[] = $tmp;
      }

      $this->data = $list;
    } catch (\Exception $e) {
      $this->failed();
      \Log::error($e->getMessage());
      $this->error = [
        E::SERVER_ERROR,
        'ユーザ情報の取得に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage();
    }
  }

}

