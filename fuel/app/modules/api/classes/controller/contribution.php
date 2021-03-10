<?php

namespace Api;
//pushしてる？
class Controller_Contribution extends Controller_Base
{
  //ホーム、エレベーターなどの場所
  //ユーザーの投稿で呼び出し
  public function get_site_list()
  {
    try {
      //disp_flag : true なら表示する
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

  //通路、天井など
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

  //路線取得（奈良線など）
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

  //路線から駅を取得（奈良駅など）
  public function get_station_list()
  {
    try {
      $route_id = \Input::get('route_id');
      $this->data = \Model_Station::query()->select('id', 'name', 'order_id', 'created_at')->where('route_id', $route_id)->order_by('name', 'asc')->get();
      // $this->list = "wow";
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
  // public function get_station_list()
  // {
  //   try {
  //     $route_id = \Input::get('route_id');
  //     $this->data = \Model_Station::query()->select('id', 'name', 'order_id')->where('route_id', $route_id)->order_by('order_id', 'asc')->get();
  //     $this->success();
  //   } catch (\Exception $e) {
  //     $this->failed();
  //     $this->error = [
  //       E::SERVER_ERROR,
  //       '駅情報の取得に失敗しました'
  //     ];
  //     $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
  //   }
  // }

  // 2021/03/05 片渕 投稿一覧用の駅一覧取得
  public function get_postslist_station_list()
  {
    try {
      $stations = \Model_Station::query()->select('id', 'name', 'order_id')->get();
      $array_tmp = $array_result = [];
      // 駅名の重複を除く
      foreach ($stations as $station) {
        if (!in_array($station['name'], $array_tmp)) {
          $array_tmp[] = $station['name'];
          $array_result[] = $station;
        }
      }
      $this->data = $array_result;
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

  // 2021/02/19 片渕 情報担当一覧取得API実装
  public function get_repairers_list()
  {
    try {
      $this->data = \Model_Repairer::query()->select('id', 'name')->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '情報担当の取得に失敗しました'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
  }

  // 2021/02/22 建築区一覧取得API実装
  public function get_architecturewards_list()
  {
    try {
      $this->data = \Model_Architectureward::query()->select('id', 'name')->get();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '建築区の取得に失敗しました'
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

  // 桝田作成 以前の投稿をデータベース登録
  public function post_contribute()
  {
    try {
      \Log::debug("■■■■■■■");
      $route_id = \Input::post('route_id');
      $station_id = \Input::post('station_id');
      $site_id = \Input::post('site_id');
      $site_text = \Input::post('site_text');
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
      if (mb_strlen($overview) > 200) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '事象は200字以内で入力してください'
        ];
        return;
      }
      $remarks = \Input::post('remarks');
      if (mb_strlen($remarks) > 200) {
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

      // $before_post = \DB::select('*')->from("posts")->where("id", "=", $contributor_id)->get_one()->to_array()->get();


      $before_post = \DB::select('*')->from('posts')->where('id', '=', $contributor_id)->execute()->as_array();


      \Log::debug($before_post);
      if (!$before_post) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          '以前に登録していません'
        ];
        return;
      }




      $post = \Model_Post::forge();
      //     $post = \Model_Post::forge();
      $post->child_id = 0;
      $post->contributor_id = $contributor_id;

      \Log::debug($before_post[0]['contributor_id']);
      $post->route_id = $before_post[0]['route_id'];
      $post->station_id = $before_post->station_id;
      $post->status = '未対応';
      $post->site_id = $before_post->site_id;
      $post->site_text = $before_post->site_text;
      $post->facility_id = $before_post->facility_id;
      $post->facility_text = $before_post->facility_text;
      $post->overview = $before_post->overview;
      $post->remarks = $before_post->remarks;
      $post->repairer_id = 1;

      if (!empty($_FILES)) {
        $config = array(
          'path' => DOCROOT . 'contents/', //保存先のパス
          'randomize' => true, //ファイル名をランダム生成
          //'new_name' => $data['file_name'],
          'auto_rename' => true,
          //'ext_whitelist' => array('jpg', 'jpeg', 'png'),
          'max_size' => 0, //制限なし
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


      $post->save();
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

  //ユーザー入力内容登録
  // public function post_contribute()
  // {
  //   try {
  //     $route_id = \Input::post('route_id');
  //     $station_id = \Input::post('station_id');
  //     $site_id = \Input::post('site_id');
  //     $site_text = \Input::post('site_text');
  //     if (mb_strlen($site_text) > 100) {
  //       $this->failed();
  //       $this->error = [
  //         E::INVALID_PARAM,
  //         '場所情報は100字以内で入力してください'
  //       ];
  //       return;
  //     }
  //     $facility_id = \Input::post('facility_id');
  //     $facility_text = \Input::post('facility_text');
  //     if (mb_strlen($facility_text) > 100) {
  //       $this->failed();
  //       $this->error = [
  //         E::INVALID_PARAM,
  //         '設備情報は100字以内で入力してください'
  //       ];
  //       return;
  //     }
  //     $overview = \Input::post('overview');
  //     if (mb_strlen($overview) > 200) {
  //       $this->failed();
  //       $this->error = [
  //         E::INVALID_PARAM,
  //         '事象は200字以内で入力してください'
  //       ];
  //       return;
  //     }
  //     $remarks = \Input::post('remarks');
  //     if (mb_strlen($remarks) > 200) {
  //       $this->failed();
  //       $this->error = [
  //         E::INVALID_PARAM,
  //         '備考は200字以内で入力してください'
  //       ];
  //       return;
  //     }
  //     $thumbnail_before = null;
  //     if (!$user = \Auth_User::get_user()) {
  //       $this->failed();
  //       $this->error = [
  //         E::UNAUTHNTICATED,
  //         '認証エラーです'
  //       ];
  //       return;
  //     }
  //     $contributor_id = $user->to_array()['id'];

  //     $contributor_name = $user->to_array();

  //     $post = \Model_Post::forge();
  //     $post->child_id = 0;
  //     $post->contributor_id = $contributor_id;
  //     $post->route_id = $route_id;
  //     $post->station_id = $station_id;
  //     $post->status = '未対応';
  //     $post->site_id = $site_id;
  //     $post->site_text = $site_text;
  //     $post->facility_id = $facility_id;
  //     $post->facility_text = $facility_text;
  //     $post->overview = $overview;
  //     $post->remarks = $remarks;
  //     $post->repairer_id = 1;

  //     if (!empty($_FILES)) {
  //       $config = array(
  //         'path' => DOCROOT . 'test/', //保存先のパス
  //         'randomize' => true, //ファイル名をランダム生成
  //         //'new_name' => $data['file_name'],
  //         'auto_rename' => true,
  //         //'ext_whitelist' => array('jpg', 'jpeg', 'png'),
  //         'max_size' => 0, //制限なし
  //         'suffix' => '_' . date("Ymd"), //ファイル名の最後に文字列を付与
  //         //'auto_rename' => true, //ファイル名が重複した場合、連番を付与
  //         'auto_process' => false
  //       );
  //       mb_convert_variables('UTF-8', 'UTF-8', $config);
  //       \Upload::process($config);
  //       if (\Upload::is_valid()) {
  //         \Upload::save();
  //         $files = \Upload::get_files();

  //         // 正常保存された場合、アップロードファイル情報を取得
  //         if ($files) {
  //           //var_dump($files);
  //           switch (count($files)) {
  //             case 1:
  //               $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
  //               $post->thumbnail_before1 = $thumbnail_before1;
  //               break;
  //             case 2:
  //               $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
  //               $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
  //               $post->thumbnail_before1 = $thumbnail_before1;
  //               $post->thumbnail_before2 = $thumbnail_before2;
  //               break;
  //             case 3:
  //               $thumbnail_before1 = \Uri::base(false) . 'contents/' . $files[0]['saved_as'];
  //               $thumbnail_before2 = \Uri::base(false) . 'contents/' . $files[1]['saved_as'];
  //               $thumbnail_before3 = \Uri::base(false) . 'contents/' . $files[2]['saved_as'];
  //               $post->thumbnail_before1 = $thumbnail_before1;
  //               $post->thumbnail_before2 = $thumbnail_before2;
  //               $post->thumbnail_before3 = $thumbnail_before3;
  //               break;
  //             default:
  //               break;
  //           }
  //         } else {
  //           $this->failed();
  //           $this->error = [
  //             E::SERVER_ERROR,
  //             'サムネイルの保存に失敗しました'
  //           ];
  //         }
  //       } else {
  //         $this->failed();
  //         $this->error = [
  //           E::SERVER_ERROR,
  //           '不正なファイルです'
  //         ];
  //       }
  //     }


  //     $post->save();
  //     //直近の投稿を
  //     // $latest_post = \Model_Post::find($contributor_id);
  //     $latest_post = \Model_Post::get_contribution_history($contributor_id)[0];
  //     \Log::debug($latest_post);
  //     $contribution_url = \Input::post('contribution_url') . $latest_post['id'];
  //     $tmp = \Model_Repairer::query()->select('email')->where('id', '=', 1)->get_one()->to_array();
  //     $email = $tmp['email'];
  //     $info['url'] = $contribution_url;

  //     //      \Email::forge()
  //     //        ->from('info')
  //     //        ->to($email)
  //     //        ->subject('【みんなの駅】担当に設定されました')
  //     //        ->body(\View::forge('to_repairer', $info))
  //     //        ->send(false);
  //     unset($this->body['data']);
  //     $this->success();
  //   } catch (\Exception $e) {
  //     $this->failed();
  //     $this->error = [
  //       E::SERVER_ERROR,
  //       '投稿に失敗しました'
  //     ];
  //     $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
  //   }
  // }


  public function post_questionnaire()
  {
    $question1_list = [
      'TMNj5RPv',
      '良い',
      '普通',
      '悪い'
    ];

    $question2_list = [
      'TMNj5RPv',
      '利用する',
      '利用しない'
    ];

    $question3_list = [
      'TMNj5RPv',
      '項目が多い(入力が面倒)',
      '文字の入力が使いにくい',
      '写真の投稿が使いにくい',
      '駅員に口答で伝えた方が早い'
    ];

    if (!$data = $this->verify([
      'question1' => [
        'label' => '設問1',
        'validation' => [
          'required'
        ]
      ],
      'question2' => [
        'label' => '設問2',
        'validation' => [
          'required'
        ]
      ],
      'question3' => [
        'label' => '設問3',
        'default' => null
      ],
      'question4' => [
        'label' => '設問4',
        'default' => null
      ]

    ])) {
      return;
    }
    try {
      if (!$user = \Auth_User::get_user()) {
        $this->failed();
        $this->error = [
          E::UNAUTHNTICATED,
          '認証エラーです。'
        ];
        return;
      }
      $user_id = $user->id;
      $question1 = \Input::post('question1');
      $question2 = \Input::post('question2');
      $question3 = \Input::post('question3');
      $question4 = \Input::post('question4');

      if (array_search($question1, $question1_list) == false) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '「設問1」は選択肢から回答を選んで下さい'
        ];
        return;
      }
      if (array_search($question2, $question2_list) == false) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          '「設問2」は選択肢から回答を選んで下さい'
        ];
        return;
      }
      if (!is_null($question3)) {
        if (!empty($question3)) {
          if (array_search($question3, $question3_list) == false) {
            $this->failed();
            $this->error = [
              E::INVALID_PARAM,
              '「設問3」は選択肢から回答を選んで下さい'
            ];
            return;
          }
        }
      }
      if (mb_strlen($question4) > 100) {
        $this->failed();
        $this->error = [
          E::INVALID_PARAM,
          'ご意見は100字以内で入力してください'
        ];
        return;
      }

      $questionnaire = \Model_Questionnaire::forge();
      $questionnaire->user_id = $user_id;
      $questionnaire->question1 = $question1;
      $questionnaire->question2 = $question2;
      $questionnaire->question3 = $question3;
      $questionnaire->question4 = $question4;
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'アンケートの送信に失敗しました。'
      ];
      $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
    }
    $questionnaire->save();
    $this->success();
  }

  public function get_questionnaires_csv()
  {
    try {
      \Model_Questionnaire::csv_export();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        'アンケートCSVの出力に失敗しました'
      ];
    }
    $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
  }

  // 2021/02/22 片渕 投稿一覧CSV出力実装
  public function get_postslist_csv(
    $status = false,
    $route = false,
    $station = false,
    $status_order = 'asc',
    $created_at_order = 'desc',
    $route_order = 'asc',
    $station_order = 'asc',
    $routes_search = "",
    $stations_search = "",
    $facility_search = "",
    $repairer_search = "",
    $status_search = "",
    $start_date = "",
    $end_date = "",
    $architecture_ward_search = ""
  ) {
    try {
      $status = \Input::get('status'); //MEMO status 取得
      $route = \Input::get('route'); //MEMO route 取得
      $station = \Input::get('station'); //MEMO station 取得
      $status_order = \Input::get('status_order'); //MEMO status_order取得
      $created_at_order = \Input::get('created_at_order'); //MEMO created_at_order取得
      $route_order = \Input::get('route_order'); //MEMO route_order取得
      $station_order = \Input::get('station_order'); //MEMO station_order取得
      $routes_search = \Input::get('routes_search');
      $stations_search = \Input::get('stations_search');
      $facility_search = \Input::get('facility_search');
      $repairer_search = \Input::get('repairer_search');
      $status_search = \Input::get('status_search');
      $start_date = \Input::get('start_date');
      $end_date = \Input::get('end_date');
      $architecture_ward_search = \Input::get('architecture_ward_search');
      $order_base = array(); //MEMO 配列作成
      $search_material = array();


      //MEMO 以下昇順降順処理
      if ($route == 'true') {
        if ($route_order == 'desc') {
          $order_base[] = ' r.id desc ';
        } else {
          $order_base[] = ' r.id asc ';
        }
      }

      if ($station == 'true') {
        if ($station_order == 'desc') {
          $order_base[] = ' s.display_id desc ';
        } else {
          $order_base[] = ' s.display_id asc ';
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

      if ($status_search == "") {
        $search_material['status_search'] = "";
      } else {
        $search_material['status_search'] = $status_search;
      }

      if ($start_date == "") {
        $search_material['start_date'] = "";
      } else {
        $search_material['start_date'] = $start_date;
      }

      if ($end_date == "") {
        $search_material['end_date'] = "";
      } else {
        $search_material['end_date'] = $end_date;
      }

      if ($architecture_ward_search == "") {
        $search_material['architecture_ward_search'] = "";
      } else {
        $search_material['architecture_ward_search'] = $architecture_ward_search;
      }

      $order = implode(' , ', $order_base); //MEMO 各項目の内昇順降順の適応は一つ
      \Model_Post::csv_export($order, $search_material);
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        '投稿一覧CSVの出力に失敗しました'
      ];
    }
    $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
  }

  public function get_contribution_history()
  {
    if (!$user = \Auth_User::get_user()) {
      $this->failed();
      $this->error = [
        E::UNAUTHNTICATED,
        '認証エラーです'
      ];
      return;
    }
    try {
      $history = \Model_Post::get_contribution_history($user->id);
      $this->data = $history;
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

  //何してる？
  public function get_one()
  {
    $id = \Input::get('contribution_id');
    \Log::debug("get_one");
    try {
      $contribute = \Model_Post::get_contribution_by_id($id);
      $this->data = $contribute;
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

  public function get_other_contributes()
  {

    try {
      $status = \Input::get('status');
      $station_id = \Input::get('station_id');

      if (empty($status)) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          'ステータスを入力してください'
        ];
        return;
      }

      if (empty($station_id)) {
        $this->failed();
        $this->error = [
          E::INVALID_REQUEST,
          '駅を入力してください'
        ];
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
      $contributor_id = $user->to_array()['id'];
      $contributes = \Model_Post::get_other_contributes($status, $station_id, $contributor_id);
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

  public function get_information_list()
  {
    try {
      $this->data = \Model_Information::find('all', array(
        'where' => array(
          array('is_private', 0),
        ),
        'order_by' => array('date' => 'desc'),
      ));
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

  //投稿修正
  public function post_edit_remarks()
  {
    $contribution_id = \Input::post('contribution_id');
    $remarks = \Input::post('remarks');
    if (mb_strlen($remarks) > 200) {
      $this->failed();
      $this->error = [
        E::INVALID_PARAM,
        '備考は200字以内で入力してください'
      ];
      return;
    }
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
          'max_size' => 0, //制限なし
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

      $contribute->remarks = $remarks;
      $contribute->save();
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
}
