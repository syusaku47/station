<?php

namespace Api;

class Controller_User extends Controller_Base
{
  public function post_signup()
  {
    \Auth::create_user(
      \Input::post('username'),
      \Input::post('password'),
      \Input::post('email'),
      null,
      array(
        'type' => 'test',
      )
    );
    if (!$data = $this->verify([
      'email' => [
        'label' => 'メールアドレス',
        'validation' => [
          'required',
          'valid_email'
        ]
      ]
    ])) {
    $this->error = [
      E::INVALID_REQUEST,
      '既に登録しているユーザです。'
    ];
    }
  }
  // if(!$industries = \Model_Industry::get_by_industry_id(\Input::get('industry_id'))){
  // $this->data = $industries;
}

