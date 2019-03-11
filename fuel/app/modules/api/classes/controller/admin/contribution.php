<?php

namespace Api;

class Controller_Admin_Contribution extends Controller_Base
{
  public function get_site_list()
  {
    try {
      $this->data = \Model_Site::query()->select('id', 'name')->where('disp_flag', true)->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '場所情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function get_facility_list()
  {
    try {
      $site_id = \Input::get('site_id');
      $this->data = \Model_Facility::query()->select('id', 'name')->where('site_id', $site_id)->where('disp_flag', true)->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '設備情報の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function post_contribute()
  {
    try {
      $route_id = \Input::post('route_id');
      $station_id = \Input::post('station_id');
      $site_id = \Input::post('site_id');
      $site_text = \Input::post('site_text');
      $parent_id = \Input::post('parent_id');

      if (mb_strlen($site_text) > 100) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '場所情報は100字以内で入力してください'
        ];
        return;
      }
      $facility_id = \Input::post('facility_id');
      $facility_text = \Input::post('facility_text');
      if (mb_strlen($facility_text) > 100) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '設備情報は100字以内で入力してください'
        ];
        return;
      }
      $overview = \Input::post('overview');
      if (mb_strlen($overview) > 100) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '状況は200字以内で入力してください'
        ];
        return;
      }
      $remarks = \Input::post('remarks');
      if (mb_strlen($remarks) > 100) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '備考は200字以内で入力してください'
        ];
        return;
      }
      $thumbnail_before = null;
      if (!$user = \Auth_User::get_user()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          '認証エラーです'
        ];
        return;
      }
      $contributor_id = $user->to_array()['id'];

      $post = \Model_Post::forge();
      $post->contributor_id = $contributor_id;
      $post->child_id = 0;
      $post->route_id = $route_id;
      $post->station_id = $station_id;
      $post->status = '未対応';
      $post->site_id = $site_id;
      $post->site_text = $site_text;
      $post->facility_id = $facility_id;
      $post->facility_text = $facility_text;
      $post->overview = $overview;
      $post->remarks = $remarks;
      $post->repairer_id = 1;


      if (!empty($_FILES)) {
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
          $files = \Upload::get_files();

          // 正常保存された場合、アップロードファイル情報を取得
          if ($files) {
            //var_dump($files);
            switch (count($files)) {
              case 1:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $post->thumbnail_before1 = $thumbnail_before1;
                break;
              case 2:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $post->thumbnail_before1 = $thumbnail_before1;
                $post->thumbnail_before2 = $thumbnail_before2;
                break;
              case 3:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $thumbnail_before3 = \Uri::base(false) . 'contents/' . $files[2]['saved_as'];
                $post->thumbnail_before1 = $thumbnail_before1;
                $post->thumbnail_before2 = $thumbnail_before2;
                $post->thumbnail_before3 = $thumbnail_before3;
                break;
              default:
                break;
            }
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

      if (!empty($parent_id)) {
        $post->parent_id = $parent_id;
        $post->child_id = \Model_Post::numbering_child_id($parent_id);
        \Log::error('child_id : ' . $post->child_id);
      }
      $post->save();
      $latest_post = \Model_Post::get_contribution_history($contributor_id)[0];
      $contribution_url = \Input::post('contribution_url'). $latest_post['id'];
      $tmp = \Model_Repairer::query()->select('email')->where('id', '=', 1)->get_one()->to_array();
      $email = $tmp['email'];
      $info['url'] = $contribution_url;

      \Email::forge()
        ->from('info')
        ->to($email)
        ->subject('【みんなの駅】担当に設定されました')
        ->body(\View::forge('to_repairer', $info))
        ->send();
      unset($this->body['data']);
      $this->success();

    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function post_new_information()
  {
    $title = \Input::post('title');
    $body = \Input::post('body');

    if(mb_strlen($title) == 0){
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        'タイトルを入力してください'
      ];
      return;
    }
    if(mb_strlen($title) > 50){
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        'タイトルは50字以内で入力してください'
      ];
      return;
    }
    if(mb_strlen($body) > 500){
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        '本文は500字以内で入力してください'
      ];
      return;
    }

    try {
      $information = \Model_Information::forge();
      $information->title = $title;
      $information->date = date("Y-m-d H:i:s");
      $information->body = $body;
      $information->is_private = 0;
      $information->save();

      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'お知らせの作成に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function get_information_list()
  {
    try {
      if (!$data = $this->verify([
        'limit',
        'p',
      ])) {
        return;
      }

      $this->list = \Model_Information::search($data);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'お知らせの取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function patch_edit_information()
  {
    try {
      $title = \Input::patch('title');
      $body = \Input::patch('body');
      $information = \Model_Information::find(\Input::patch('information_id'));
      if(mb_strlen($title) == 0){
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          'タイトルを入力してください'
        ];
        return;
      }
      if(mb_strlen($title) > 50){
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          'タイトルは50字以内で入力してください'
        ];
        return;
      }
      if(mb_strlen($body) > 500){
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '本文は500字以内で入力してください'
        ];
        return;
      }

      $information->title = $title;
      $information->body = $body;
      $information->save();
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'お知らせの更新に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function patch_delete_information()
  {
    try {
      $information = \Model_Information::find(\Input::patch('information_id'));
      $information->is_private = true;
      $information->save();
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'お知らせの削除に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function post_edit()
  {
    $contribution_id = \Input::post('contribution_id');
    $status = \Input::post('status');
    $repairer_id = \Input::post('repairer_id');
    try {
      $contribute = \Model_Post::find($contribution_id);
      if (!$contribute) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          '該当する投稿がありませんでした'
        ];
        return;
      }

      if (!empty($_FILES)) {
        var_dump($_FILES);
        $config = array(
          'path' => DOCROOT . 'contents/', //保存先のパス
          'randomize' => true, //ファイル名をランダム生成
          //'new_name' => $data['file_name'],
          'auto_rename' => true,
          //'ext_whitelist' => array('jpg', 'jpeg', 'png'),
          'max_size' => 0,//制限なし
          'suffix' => '_' . date("Ymd"), //ファイル名の最後に文字列を付与
          //'auto_rename' => true, //ファイル名が重複した場合、連番を付与
          'auto_process' => false
        );
        mb_convert_variables('UTF-8', 'UTF-8', $config);
        \Upload::process($config);
        if (\Upload::is_valid()) {
          \Upload::save();
          $files = \Upload::get_files();

          // 正常保存された場合、アップロードファイル情報を取得
          if ($files) {
            //var_dump($files);
            switch (count($files)) {
              case 1:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $contribute->thumbnail_before1 = $thumbnail_before1;
                $contribute->thumbnail_before2 = null;
                $contribute->thumbnail_before3 = null;
                break;
              case 2:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $contribute->thumbnail_before1 = $thumbnail_before1;
                $contribute->thumbnail_before2 = $thumbnail_before2;
                $contribute->thumbnail_before3 = null;
                break;
              case 3:
                $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $thumbnail_before3 = \Uri::base(false) . 'contents/' . $files[2]['saved_as'];
                $contribute->thumbnail_before1 = $thumbnail_before1;
                $contribute->thumbnail_before2 = $thumbnail_before2;
                $contribute->thumbnail_before3 = $thumbnail_before3;
                break;
              default:
                break;
            }
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

      $needs_send_mail = $contribute->repairer_id != $repairer_id ? true : false;

      if($contribute->status == '完了' && $status != '完了'){
        $contribute->complete_id = null;
        $needs_send_mail = true;
      }
      if($contribute->status == 'リジェクト' && $status != 'リジェクト'){
        $contribute->reject_id = null;
        $needs_send_mail = true;
      }

      $contribute->status = $status;
      $contribute->repairer_id = $repairer_id;
      $contribute->save();

      if ($needs_send_mail) {
        $contribution_url = \Input::post('contribution_url');
        $tmp = \Model_Repairer::query()->select('email')->where('id', '=', $repairer_id)->get_one()->to_array();
        $email = $tmp['email'];
        $info['url'] = $contribution_url;

        \Email::forge()
          ->from('info')
          ->to($email)
          ->subject('【みんなの駅】担当に設定されました')
          ->body(\View::forge('to_repairer', $info))
          ->send();
      }

      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '更新に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function get_contribution_list($order = 'desc')
  {
    try {
      if (!$data = $this->verify([
        'limit',
        'p',
      ])) {
        return;
      }
      $order = \Input::get('order');
      $contributes = \Model_Post::get_contribution_list($data, $order);
      $this->data = $contributes;
      $this->success();

    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }

  }

  public function get_comment_list()
  {
    try {
      $type = \Input::get('type');
      $this->data = \Model_Comment::query()->where('type', $type)->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '定型文の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function post_reject_contribution()
  {
    $contribution_id = \Input::post('contribution_id');
    $comment_id = \Input::post('comment_id');
    $reject_url = \Input::post('reject_url');
    try {
      $contribute = \Model_Post::find($contribution_id);
      if(!$contribute){
        $this->failed();
        $this->error = [
          E::SERVER_ERROR,
          'リジェクト処理に失敗しました'
        ];
        return;
      }
      $comment = \Model_Comment::find($comment_id);
      $user = \Auth_User::find($contribute->contributor_id);
      $email = $user->email;
      $info = array();
      $info['url'] = $reject_url;
      $info['comment'] = $comment->comment;

      \Email::forge()
        ->from('info')
        ->to($email)
        ->subject('【みんなの駅】投稿がリジェクトされました')
        ->body(\View::forge('reject', $info))
        ->send();

      $contribute->status = 'リジェクト';
      $contribute->reject_id = $comment_id;
      $contribute->save();
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'リジェクト処理に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  public function post_complete_contribution()
  {
    $contribution_id = \Input::post('contribution_id');
    $comment_id = \Input::post('comment_id');
    $complete_url = \Input::post('complete_url');

    try {
      
      $contribute = \Model_Post::find($contribution_id);
      if(!$contribute){
        $this->failed();
        $this->error = [
          E::SERVER_ERROR,
          '完了処理に失敗しました'
        ];
        return;
      }
      $comment = \Model_Comment::find($comment_id);
      $user = \Auth_User::find($contribute->contributor_id);
      $email = $user->email;
      $info = array();
      $info['url'] = $complete_url;
      $info['comment'] = $comment->comment;

      if (!empty($_FILES)) {
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
          $files = \Upload::get_files();

          // 正常保存された場合、アップロードファイル情報を取得
          if ($files) {
            //var_dump($files);
            switch (count($files)) {
              case 1:
                $thumbnail_after1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $contribute->thumbnail_after1 = $thumbnail_after1;
                break;
              case 2:
                $thumbnail_after1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_after2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $contribute->thumbnail_after1 = $thumbnail_after1;
                $contribute->thumbnail_after2 = $thumbnail_after2;
                break;
              case 3:
                $thumbnail_after1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
                $thumbnail_after2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
                $thumbnail_after3 = \Uri::base(false) . 'contents/' . $files[2]['saved_as'];
                $contribute->thumbnail_after1 = $thumbnail_after1;
                $contribute->thumbnail_after2 = $thumbnail_after2;
                $contribute->thumbnail_after3 = $thumbnail_after3;
                break;
              default:
                break;
            }
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

      \Email::forge()
        ->from('info')
        ->to($email)
        ->subject('【みんなの駅】修繕が完了しました')
        ->body(\View::forge('complete', $info))
        ->send();

      $contribute->status = '完了';
      $contribute->complete_id = $comment_id;
      $contribute->save();
      unset($this->body['data']);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '完了処理に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }
}
