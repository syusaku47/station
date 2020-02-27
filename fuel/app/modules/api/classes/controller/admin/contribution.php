<?php

namespace Api;

use Oil\Package;

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
            $this->data = \Model_Station::query()->select('id', 'name','order_id')->where('route_id', $route_id)->order_by('order_id', 'asc')->get();
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

    //Add 200113 冨岡 担当会社追加
    public function post_contribute()
    {
        try {

            if (!$user = \Auth_User::get_user()) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '認証エラーです'
                ];
                return;
            }

            if ($user->group_id != 2) {
                $this->failed();
                $this->error = [
                    E::INVALID_REQUEST,
                    '権限がありません'
                ];
                return;
            }
            $route_id = \Input::post('route_id'); //MEMO route_id 取得
            $station_id = \Input::post('station_id');//MEMO station_id取得
            $site_id = \Input::post('site_id'); //MEMO site_id取得
            $site_text = \Input::post('site_text');//MEMO site_text取得
            $parent_id = \Input::post('parent_id');//MEMO parent_id取得


            if (mb_strlen($site_text) > 100) { //MEMO site_text 制限
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '場所情報は100字以内で入力してください'
                ];
                return;
            }
            $facility_id = \Input::post('facility_id'); //MEMO facility_id取得
            $facility_text = \Input::post('facility_text'); //MEMO facility_text取得
            if (mb_strlen($facility_text) > 100) { //MEMO facility_text文字制限
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '設備情報は100字以内で入力してください'
                ];
                return;
            }
            $overview = \Input::post('overview'); //MEMO overview取得
            if (mb_strlen($overview) > 200) { //MEMO overview 文字制限
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '事象は200字以内で入力してください'
                ];
                return;
            }
            $remarks = \Input::post('remarks'); //MEMO remarks取得
            if (mb_strlen($remarks) > 200) { //MEMO remarks文字制限
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
            $contributor_id = $user->to_array()['id']; //MEMO user id取得

            $post = \Model_Post::forge(); //MEMO postテーブルforge
            $post->contributor_id = $contributor_id; //MEMO 以下リクエストで送られてきたpostデータをそれぞれ格納
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
            $post->repairer_id = '1';


            if (!empty($_FILES)) {
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
            $contribution_url = \Input::post('contribution_url') . $latest_post['id'];
            $tmp = \Model_Repairer::query()->select('email')->where('id', '=', '1')->get_one()->to_array();
            $email = $tmp['email'];
            $info['url'] = $contribution_url;

//            \Email::forge()
//                ->from('info')
//                ->to($email)
//                ->subject('【みんなの駅】担当に設定されました')
//                ->body(\View::forge('to_repairer', $info))
//                ->send(false);
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
        if (!$user = \Auth_User::get_user()) {
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                '認証エラーです'
            ];
            return;
        }

        if($user->group_id != 2){
            $this->failed();
            $this->error = [
                E::INVALID_REQUEST,
                '権限がありません'
            ];
            return;
        }

        $title = \Input::post('title');
        $body = \Input::post('body');

        if (mb_strlen($title) == 0) {
            $this->failed();
            $this->error = [
                E::INVALID_PARAM,
                'タイトルを入力してください'
            ];
            return;
        }
        if (mb_strlen($title) > 50) {
            $this->failed();
            $this->error = [
                E::INVALID_PARAM,
                'タイトルは50字以内で入力してください'
            ];
            return;
        }
        if (mb_strlen($body) > 500) {
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

            if (!$user = \Auth_User::get_user()) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '認証エラーです'
                ];
                return;
            }

            if($user->group_id != 2){
                $this->failed();
                $this->error = [
                    E::INVALID_REQUEST,
                    '権限がありません'
                ];
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

            if (!$user = \Auth_User::get_user()) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '認証エラーです'
                ];
                return;
            }

            if($user->group_id != 2){
                $this->failed();
                $this->error = [
                    E::INVALID_REQUEST,
                    '権限がありません'
                ];
                return;
            }

            $title = \Input::patch('title');
            $body = \Input::patch('body');
            $information = \Model_Information::find(\Input::patch('information_id'));
            if (mb_strlen($title) == 0) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'タイトルを入力してください'
                ];
                return;
            }
            if (mb_strlen($title) > 50) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'タイトルは50字以内で入力してください'
                ];
                return;
            }
            if (mb_strlen($body) > 500) {
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

            if (!$user = \Auth_User::get_user()) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '認証エラーです'
                ];
                return;
            }

            if($user->group_id != 2){
                $this->failed();
                $this->error = [
                    E::INVALID_REQUEST,
                    '権限がありません'
                ];
                return;
            }

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
        if (!$user = \Auth_User::get_user()) {
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                '認証エラーです'
            ];
            return;
        }

        if($user->group_id != 2){
            $this->failed();
            $this->error = [
                E::INVALID_REQUEST,
                '権限がありません'
            ];
            return;
        }

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

            if ($contribute->status == '完了' && $status != '完了') {
                $contribute->complete_id = null;
                $contribute->thumbnail_after1 = null;
                $contribute->thumbnail_after2 = null;
                $contribute->thumbnail_after3 = null;
                $needs_send_mail = true;
            }
            if ($contribute->status == 'リジェクト' && $status != 'リジェクト') {
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

//                \Email::forge()
//                    ->from('info')
//                    ->to($email)
//                    ->subject('【みんなの駅】担当に設定されました')
//                    ->body(\View::forge('to_repairer', $info))
//                    ->send(false);
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


    //Add 200115 冨岡 絞り込み検索ステータス非表示
    public function get_contribution_list($status = false, $route = false, $station = false, $status_order = 'asc', $created_at_order = 'desc', $route_order = 'asc', $station_order = 'asc',$created_search = "",$routes_search = "",$stations_search = "",$facility_search = "",$repairer_search = "",$status_valid="")
    {
        try {

            if (!$user = \Auth_User::get_user()) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '認証エラーです'
                ];
                return;
            }

            if ($user->group_id != 2) {
                $this->failed();
                $this->error = [
                    E::INVALID_REQUEST,
                    '権限がありません'
                ];
                return;
            }

            if (!$data = $this->verify([
                'limit',
                'p',
            ])) {
                return;
            }
            $status = \Input::get('status'); //MEMO status 取得
            $route = \Input::get('route');//MEMO route 取得
            $station = \Input::get('station');//MEMO station 取得
            $status_order = \Input::get('status_order'); //MEMO status_order取得
            $created_at_order = \Input::get('created_at_order'); //MEMO created_at_order取得
            $route_order = \Input::get('route_order'); //MEMO route_order取得
            $station_order = \Input::get('station_order'); //MEMO station_order取得
            $created_search = \Input::get('created_search');
            $routes_search = \Input::get('routes_search');
            $stations_search = \Input::get('stations_search');
            $facility_search = \Input::get('facility_search');
            $repairer_search = \Input::get('repairer_search');
            $status_valid = \Input::get('status_valid');
            $order_base = array(); //MEMO 配列作成
            $search_material = array();


            //MEMO 以下昇順降順処理
            if ($route == 'true') {
                if ($route_order == 'desc') {
                    $order_base[] = ' r.name_kana desc ';
                } else {
                    $order_base[] = ' r.name_kana asc ';
                }
            }

            if ($station == 'true') {
                if ($station_order == 'desc') {
                    $order_base[] = ' s.name_kana desc ';
                } else {
                    $order_base[] = ' s.name_kana asc ';
                }
            }

            if ($status == 'true') {
                if ($status_order == 'desc') {
                    $order_base[] = ' p.status desc ';
                } else {
                    $order_base[] = ' p.status asc ';
                }
            }

            if ($created_at_order == 'asc') {
                $order_base[] = ' p.created_at asc ';
            } else {
                $order_base[] = ' p.created_at desc ';
            }

            if ($created_search == "") {
                $search_material['created_search'] = "";
            } else {
                $search_material['created_search'] = $created_search;
            }

            if ($routes_search == "") {
                $search_material['routes_search'] = "";
            } else {
                $search_material['routes_search'] = $routes_search;
            }

            if ($stations_search == "") {
                $search_material['stations_search'] = "";
            } else {
                $search_material['stations_search'] = $stations_search;
            }

            if ($facility_search == "") {
                $search_material['facility_search'] = "";
            } else {
                $search_material['facility_search'] = $facility_search;
            }

            if ($repairer_search == "") {
                $search_material['repairer_search'] = "";
            } else {
                $search_material['repairer_search'] = $repairer_search;
            }

            if ($status_valid == "") {
                $search_material['status_valid'] = "";
            } else {
                $search_material['status_valid'] = $status_valid;
            }

            $order = implode(' , ', $order_base); //MEMO 各項目の内昇順降順の適応は一つ
            $contributes = \Model_Post::get_contribution_list($data, $order, $search_material);
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
        if (!$user = \Auth_User::get_user()) {
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                '認証エラーです'
            ];
            return;
        }

        if ($user->group_id != 2) {
            $this->failed();
            $this->error = [
                E::INVALID_REQUEST,
                '権限がありません'
            ];
            return;
        }

        $contribution_id = \Input::post('contribution_id');
        $comment_id = \Input::post('comment_id');
        $reject_url = \Input::post('reject_url');
        $reject_text = \Input::post('reject_text');

        try {
            $contribute = \Model_Post::find($contribution_id);
            if (!$contribute) {
                $this->failed();
                $this->error = [
                    E::SERVER_ERROR,
                    'リジェクト処理に失敗しました'
                ];
                return;
            }
            if(mb_strlen($reject_text) > 50){
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'リジェクトコメントは50字以内で入力してください'
                ];
                return;
            }

            $user = \Auth_User::find($contribute->contributor_id);
            $email = $user->email;
            $info = array();
            $info['url'] = $reject_url;

            if (!empty($comment_id)) {
                $comment = \Model_Comment::find($comment_id);
                $info['comment'] = $comment->comment;
            } else {
                if (empty($reject_text)) {
                    $this->failed();
                    $this->error = [
                        E::INVALID_REQUEST,
                        'リジェクト理由を記入してください。'
                    ];
                    return;
                }
                $info['comment'] = $reject_text;
            }

//            \Email::forge()
//                ->from('info')
//                ->to($email)
//                ->subject('【みんなの駅】投稿がリジェクトされました')
//                ->body(\View::forge('reject', $info))
//                ->send(false);

            $contribute->status = 'リジェクト';
            if (!empty($comment_id)) {
                $contribute->reject_id = $comment_id;
            } else {
                if (empty($reject_text)) {
                    $this->failed();
                    $this->error = [
                        E::INVALID_REQUEST,
                        'リジェクト理由を記入してください。'
                    ];
                    return;
                } else {
                    $contribute->reject_text = $reject_text;
                }
            }
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
        if (!$user = \Auth_User::get_user()) {
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                '認証エラーです'
            ];
            return;
        }

        if($user->group_id != 2){
            $this->failed();
            $this->error = [
                E::INVALID_REQUEST,
                '権限がありません'
            ];
            return;
        }

        $contribution_id = \Input::post('contribution_id');
        $comment_id = \Input::post('comment_id');
        $complete_url = \Input::post('complete_url');
        $complete_text = \Input::post('complete_text');

        try {

            $contribute = \Model_Post::find($contribution_id);
            if (!$contribute) {
                $this->failed();
                $this->error = [
                    E::SERVER_ERROR,
                    '完了処理に失敗しました'
                ];
                return;
            }

            if(mb_strlen($complete_text) > 50){
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '完了コメントは50字以内で入力してください'
                ];
                return;
            }

            $user = \Auth_User::find($contribute->contributor_id);
            $email = $user->email;
            $info = array();
            $info['url'] = $complete_url;
            if(!empty($comment_id)){
                $comment = \Model_Comment::find($comment_id);
                $info['comment'] = $comment->comment;
            }else{
                if(empty($complete_text)){
                    $this->failed();
                    $this->error= [
                        E::INVALID_REQUEST,
                        '完了理由を記入してください。'
                    ];
                    return;
                }
                $info['comment'] = $complete_text;
            }

            if (!empty($_FILES)) {
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

//            \Email::forge()
//                ->from('info')
//                ->to($email)
//                ->subject('【みんなの駅】修繕が完了しました')
//                ->body(\View::forge('complete', $info))
//                ->send(false);

            $contribute->status = '完了';
            if(!empty($comment_id)){
                $contribute->complete_id = $comment_id;
            } else {
                if(empty($complete_text)){
                    $this->failed();
                    $this->error = [
                        E::INVALID_REQUEST,
                        '完了理由を記入してください。'
                    ];
                    return;
                } else {
                    $contribute->complete_text = $complete_text;
                }
            }
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
