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

  public function logout()
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
}

