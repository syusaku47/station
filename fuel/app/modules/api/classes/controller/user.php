<?php

namespace Api;

class Controller_User extends Controller_Base
{
  public function post_sign_up()
  {
    try {
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
      if (\Auth::login(\Input::post('email'), \Input::post('password'))) {
        unset($this->body['data']);
        $this->success();
        return;
      }
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED
      ];

    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        $e->getMessage()
      ];
    }
  }

  public function post_logout()
  {
    try {
      if (\Auth::logout()) {
        unset($this->body['data']);
        $this->success();
        return;
      }
      $this->failed();
      $this->error = [
        E::SERVER_ERROR
      ];

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
          'need to login.'
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
      $this->failed();
      $this->error = [
        E::INVALID_REQUEST,
        $e->getMessage()
      ];
    }
  }

  public function get_my_info()
  {
    try {
      if (!$user = \Auth_User::get_user()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          'no user found.'
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
        $this->not_found();
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
}

