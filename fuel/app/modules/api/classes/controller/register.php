<?php
namespace Api;

class Controller_Register extends Controller_Base
{
  public function get_regist()
  {
    \Auth::create_user(
      \Input::get('username'),
      \Input::get('password'),
      \Input::get('email'),
      2,
      array(
        'type' => 'test',
      )
    );
    // if(!$industries = \Model_Industry::get_by_industry_id(\Input::get('industry_id'))){
    //   $this->error = [
    //     E::INVALID_REQUEST,
    //     'no industry found'
    //   ];
    // }
    // $this->data = $industries;
  }
}
