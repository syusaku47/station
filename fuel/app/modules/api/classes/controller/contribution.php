<?php

namespace Api;

class Controller_Contribution extends Controller_Base
{
  public function get_site_list()
  {
    try {
      $this->data = \Model_Site::query()->select('id', 'name')->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '場所情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_facility_list()
  {
    try {
      $site_id = \Input::get('site_id');
      $this->data = \Model_Facility::query()->select('id', 'name')->where('site_id', $site_id)->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '設備情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_route_list()
  {
    try {
      $this->data = \Model_Route::query()->select('id', 'name')->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '路線情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_station_list()
  {
    try {
      $route_id = \Input::get('route_id');
      $this->data = \Model_Station::query()->select('id', 'name')->where('route_id', $route_id)->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '駅情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_closest_station()
  {
    try {
      $lat = \Input::get('lat');
      $long = \Input::get('long');
      $this->data = \Model_Station::get_closest_station($lat, $long);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '駅情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function post_contribute()
  {
    try {
      $route_id = \Input::post('route_id');
      $station_id = \Input::post('station_id');
      $site_id = \Input::post('site_id');
      $facility_id = \Input::post('facility_id');
      $overview = \Input::post('overview');
      $remarks = \Input::post('remarks');
      $thumbnail_before = null;
      if(!$user = \Auth_User::get_user()){
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          '認証エラーです'
        ];
        return;
      }
      $contributor_id = $user->to_array()['id'];

      if(!empty($_FILES)){
        $config = array(
          'path' => DOCROOT . 'contents/', //保存先のパス
          'randomize' => true, //ファイル名をランダム生成
          //'new_name' => $data['file_name'],
          'auto_rename' => true,
          'ext_whitelist' => array('jpg', 'jpeg', 'png'),
          'max_size' => 0,//制限なし
          'suffix' => '_' . date("Ymd"), //ファイル名の最後に文字列を付与
          //'auto_rename' => true, //ファイル名が重複した場合、連番を付与
          'auto_process' => false
        );
        mb_convert_variables('UTF-8', 'UTF-8', $config);
        \Upload::process($config);
        if (\Upload::is_valid()) {
          \Upload::save();
          $file = \Upload::get_files()[0];
          // 正常保存された場合、アップロードファイル情報を取得
          if ($file) {
            //$thumbnail_before = DOCROOT . 'contents/' . $file['name'] . '.' . $file['extension'];
            $thumbnail_before = \Uri::base(false).'contents/'.$file['saved_as'];
          } else {
            $this->failed();
            $this->error = [
              E::SERVER_ERROR,
              'サムネイルの保存に失敗しました'
            ];
          }
        } else {
          $this->failed();
          $this->error = [
            E::SERVER_ERROR,
            '不正なファイルです'
          ];
        }
      }


      $post = \Model_Post::forge();
      $post->contributor_id = $contributor_id;
      $post->route_id = $route_id;
      $post->station_id = $station_id;
      $post->status = '未対応';
      $post->site_id = $site_id;
      $post->facility_id = $facility_id;
      $post->overview = $overview;
      $post->remarks = $remarks;
      $post->thumbnail_before = $thumbnail_before;
      $post->save();
      unset($this->body['data']);
      $this->success();


    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_contribution_history(){
    if(!$user = \Auth_User::get_user()){
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        '認証エラーです'
      ];
      return;
    }
    try{
      $history = \Model_Post::get_contribution_history($user->id);
     $this->data = $history;
      $this->success();
    }catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_one(){
    $id = \Input::get('contribution_id');
    try{
      $contribute = \Model_Post::get_contribution_by_id($id);
      $this->data = $contribute;
      $this->success();
    }catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_other_contributes(){
    if(!$user = \Auth_User::get_user()){
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        '認証エラーです'
      ];
      return;
    }
    try{
      $contributes = \Model_Post::get_other_contributes($user->id);
      $this->data = $contributes;
      $this->success();
    }catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }

  public function get_information_list(){
    try{
      $this->data = \Model_Information::find('all');
      $this->success();
    }catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'お知らせの取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() .' '.$e->getFile().' '.$e->getLine();
    }
  }
}
