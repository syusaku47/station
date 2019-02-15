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
      if (!\Auth::check()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          'ログインしてください'
        ];
        return;
      }
      $params = array();
      $email = \Input::patch('email');
      $nickname = \Input::patch('nickname');

      if (!empty($email)) {
        $params['email'] = $email;
      }
      if (!empty($nickname)) {
        $params['email'] = $nickname;
      }
      \Auth::update_user($params
      );
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      \Log::error($e->getMessage());
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        'ログインしてください'
      ];
    }
  }

  public function get_my_info()
  {
    try {
      if (!$user = \Auth_User::get_user()) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          '該当するユーザ情報がありませんでした'
        ];
        return;
      }
      $result = $user->to_array();
      $result['nickname'] = $user->nickname;
      $this->success();
      $this->data = $result;
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        $e->getMessage()
      ];
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
          '該当するユーザ情報がありませんでした'
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
        $e->getMessage()
      ];
    }
  }

  public function patch_change_password()
  {
    try {
      if (!\Auth::check()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          'need to login.'
        ];
        return;
      }
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
        $e->getMessage()
      ];
    }
  }

  public function post_password_reissue_request()
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
        '該当するユーザ情報がありませんでした'
      ];
      return;
    }

    try {

      $user->create_hash();

      $email_user = [];
      $email_user['user'] = $user;
      $reissue_url = \Uri::base(false) . "#/setting-pass/";
      $email_user['reissue_url'] = $reissue_url;
      $this->data = $reissue_url;
//      \Email::forge()
//        ->from('info')
//        ->to($user->email)
//        ->subject('【みんなの駅】パスワード再発行のご案内')
//        ->body(\View::forge('preissue', $email_user))
//        ->send();
      // unset($this->body['data']);
      $this->success();

    } catch (\Exception $e) {
      $this->failed();
      \Log::error($e->getMessage());
      $this->error = [
        E::SERVER_ERROR,
        $e->getMessage() . ' ' . $e->getFile() . ' ', $e->getLine()
      ];
    }
  }

  public function get_by_hash($hash)
  {
    if (!$user = \Auth_User::by_hash($hash)) {
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        '有効ではありません'
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
      return;
    }

    $password = \Auth::hash_password($data['password']);

    try {
      $user = \Auth_User::find($data['id']);
      if ($user == null) {
        $this->error = [
          E::INVALID_PARAM,
          '該当するユーザ情報がありませんでした'
        ];
      } else {
        $user->password = $password;

        $user->save();
        $this->success();

      }

    } catch (\Exception $e) {
      $this->failed();
      \Log::error($e->getMessage());
      $this->error = [
        E::SERVER_ERROR,
        '更新に失敗しました'
      ];
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
          '該当するユーザ情報がありませんでした'
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
        $e->getMessage()
      ];
    }
  }

  public function get_user_list()
  {
    $result = \Auth_User::find('all');
    $this->success();
    $this->data = $result;
  }
}

