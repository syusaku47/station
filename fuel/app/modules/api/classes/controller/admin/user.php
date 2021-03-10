<?php

namespace Api;
/**
 * Class Controller_User
 * @package Api
 * @author 津山
 * @modifier 冨岡 2020/03/16 [Modify]ユーザ文字数バリデーション
 */

class Controller_Admin_User extends Controller_Base
{
    public function post_sign_up()
    {
        try {

            $username = \Input::post('username');
            if (mb_strlen($username) == 0) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'ユーザ名を正しく入力してください'
                ];

                return;
            }

            if (mb_strlen($username) > 255) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'ユーザ名は255文字以内で入力してください'
                ];

                return;
            }


            $password = \Input::post('password');
            $pwlength = strlen($password);
            if ($pwlength == 0) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'パスワードを入力してください'
                ];
                return;
            }

            //            if ($pwlength < 8 || $pwlength > 16) {
            //                $this->failed();
            //                $this->error = [
            //                    E::INVALID_PARAM,
            //                    'パスワードは8文字以上16文字以内で入力してください'
            //                ];
            //                return;
            //            }
            if (!preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{6,16}+\z/i', $password)) {
                // 英数字ではない場合
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'パスワードは6文字以上16文字以内、英数混在で入力してください'
                ];
                return;
            }

            if (!$data = $this->verify([
                'email' => [
                    'label' => 'メールアドレス',
                    'validation' => [
                        'required',
                        'valid_email'
                    ]
                ]
            ])) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'メールアドレスの形式が不正です'
                ];

                return;
            }

            if ($user = \Auth_User::by_email(\Input::post('email'))) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '既に登録しているメールアドレスです'
                ];
                return;
            }

            \Auth::create_user(
                $username,
                $password,
                $data['email'],
                2,
                array('nickname' => $username)
            );
            unset($this->body['data']);
            $this->success();
        } catch (\Exception $e) {
            $this->failed();
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
                //        'email' => [
                //          'label' => 'メールアドレス',
                //          'validation' => [
                //            'required',
                //            'valid_email'
                //          ]
                //        ],
                'username' => [
                    'label' => 'ニックネーム',
                    'validation' => [
                        'required',
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
                    'ニックネームまたはパスワードが違います'
                ];

                return;
            }
            //        if (!$user = \Auth_User::by_email($data['email'])) {
            //            $this->failed();
            //            $this->error = [
            //                E::UNAUTHNTICATED,
            //                'メールアドレスまたはパスワードが違います'
            //            ];
            //            return;
            //        }
            if (!$user = \Auth_User::by_username($data['username'])) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    'ニックネームまたはパスワードが違います'
                ];
                return;
            }

            if ($user->group_id != 2) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '権限がありません'
                ];
                return;
            }

            //      if (\Auth::login($data['email'], \Input::post('password'))) {
            //        unset($this->body['data']);
            //        $this->success();
            //        return;
            //      }
            //      $this->failed();
            //      $this->error = [
            //        E::UNAUTHNTICATED,
            //        'メールアドレスまたはパスワードが違います'
            //      ];

            if (\Auth::login($data['username'], \Input::post('password'))) {
                unset($this->body['data']);
                $this->success();
                return;
            }
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                'ニックネームまたはパスワードが違います'
            ];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                'ニックネームまたはパスワードが違います'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
            if (mb_strlen($nickname) == 0) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'ユーザ名を入力してください'
                ];

                return;
            }
            if (mb_strlen($nickname) > 20) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'ユーザ名は20文字以内で入力してください'
                ];

                return;
            }
            $age = \Input::patch('age');
            $sex = \Input::patch('sex');
            if (!empty($email)) {
                if (!$data = $this->verify([
                    'email' => [
                        'label' => 'メールアドレス',
                        'validation' => [
                            'required',
                            'valid_email'
                        ]
                    ]
                ])) {
                    $this->failed();
                    $this->error = [
                        E::UNAUTHNTICATED,
                        'メールアドレスの形式が不正です'
                    ];
                    return;
                }
                $params['email'] = $email;
            }
            if (!empty($nickname)) {
                $params['nickname'] = $nickname;
            }
            if (!empty($age)) {
                if (!is_numeric($age)) {
                    $this->failed();
                    $this->error = [
                        E::INVALID_REQUEST,
                        '年齢を正しく入力してください'
                    ];
                    return;
                } else {
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
                '更新に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    //ユーザー情報取得
    public function get_my_info()
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

            $user->to_array();
            $info['id'] = $user['id'];
            $info['nickname'] = $user['nickname'];
            $info['email'] = $user['email'];
            $info['group_id'] = $user['group_id'];
            $info['age'] = $user['age'] ? $user['age'] : '';
            $info['sex'] = $user['sex'] ? $user['sex'] : '';
            $info['last_login'] = $user['last_login'] == '0' ? $user['last_login'] : date("Y-m-d H:i:s", $user['last_login']);
            $info['previous_login'] = $user['previous_login'] == '0' ? $user['previous_login'] : date("Y-m-d H:i:s", $user['previous_login']);
            $info['created_at'] = date("Y-m-d H:i:s", $user['created_at']);
            $info['updated_at'] = $user['updated_at'] == '0' ? $user['updated_at'] : date("Y-m-d H:i:s", $user['updated_at']);
            $query =
                \DB::select('selected_start_date', 'selected_end_date', 'selected_route', 'selected_station', 'selected_facility', 'selected_repairer', 'selected_status', 'selected_architecture_ward')
                ->from('users')->where('id', $user['id'])->execute()->as_array();
            $info['selected_start_date'] = $query[0]['selected_start_date'] ? $query[0]['selected_start_date'] : null;
            $info['selected_end_date'] = $query[0]['selected_end_date'] ? $query[0]['selected_end_date'] : null;
            $info['selected_route'] = $query[0]['selected_route'] ? unserialize($query[0]['selected_route']) : null;
            $info['selected_station'] = $query[0]['selected_station'] ? unserialize($query[0]['selected_station']) : null;
            $info['selected_facility'] = $query[0]['selected_facility'] ? unserialize($query[0]['selected_facility']) : null;
            $info['selected_repairer'] = $query[0]['selected_repairer'] ? unserialize($query[0]['selected_repairer']) : null;
            $info['selected_status'] = $query[0]['selected_status'] ? unserialize($query[0]['selected_status']) : null;
            $info['selected_architecture_ward'] = $query[0]['selected_architecture_ward'] ? unserialize($query[0]['selected_architecture_ward']) : null;

            $this->success();
            $this->data = $info;
        } catch (\Exception $e) {
            $this->failed();
            $this->error = [
                E::UNAUTHNTICATED,
                '認証エラーです'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
                '該当するユーザ情報がありませんでした'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    /**
     * @Author 津山
     * @Modifier 冨岡 2020/03/25 変更前後で同じパスワード不可
     */
    public function patch_change_password()
    {
        try {
            $pwlength = strlen(\Input::patch('new_password'));
            if ($pwlength == 0) {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'パスワードを入力してください'
                ];
                return;
            }

            //            if ($pwlength < 8 || $pwlength > 16) {
            //                $this->failed();
            //                $this->error = [
            //                    E::INVALID_PARAM,
            //                    'パスワードは8文字以上16文字以内で入力してください'
            //                ];
            //                return;
            //            }
            // 英数字チェック
            if (!preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{6,16}+\z/i', \Input::patch('new_password'))) {
                // 英数字ではない場合
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    'パスワードは6文字以上16文字以内、英数混在で入力してください'
                ];
                return;
            }

            $old_password = \Input::patch('old_password');
            $old_password_hash = \Auth::hash_password($old_password);
            $user = \Auth_User::get_user();
            if ($user->password == $old_password_hash) {
                $password = \Input::patch('new_password');
                $new_password = \Auth::hash_password($password);
                if ($old_password == $password) {
                    $this->failed();
                    $this->error = [
                        E::INVALID_REQUEST,
                        '変更前と異なるパスワードを設定してください'
                    ];
                }
                $user = \Auth_User::get_user();
                $user->password = $new_password;
                $user->save();
                unset($this->body['data']);
                $this->success();
            } else {
                $this->failed();
                $this->error = [
                    E::INVALID_PARAM,
                    '現在のパスワードが間違っています'
                ];
            }
        } catch (\Exception $e) {
            $this->failed();
            $this->error = [
                E::INVALID_REQUEST,
                'パスワードの変更に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
                '該当するユーザ情報がありませんでした'
            ];
            return;
        }

        try {

            $user->create_hash($user->id);
            $path = \Input::post('path');

            $email_user = [];
            $email_user['user'] = $user;
            //            $url_base = \Fuel::$env == \FUEL::PRODUCTION ? 'https://www.minnanoeki.jp/' : \Uri::base(false);
            $url_base = \Uri::base(false);
            $reissue_url = $url_base . $path;
            $email_user['reissue_url'] = $reissue_url;
            $this->data = $reissue_url;
            \Email::forge()
                ->from('info')
                ->to($user->email)
                ->subject('【みんなの駅】パスワード再発行のご案内')
                ->body(\View::forge('preissue', $email_user))
                ->send(false);
            unset($this->body['data']);
            $this->success();
        } catch (\Exception $e) {
            $this->failed();
            \Log::error($e->getMessage());
            $this->error = [
                E::INVALID_REQUEST,
                'メールの送信に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    public function get_by_hash()
    {
        $hash = \Input::get('hash');
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
        ])) {
            $this->failed();
            $this->error = [
                E::INVALID_PARAM,
                '不正なidです'
            ];
            return;
        }

        $pwlength = strlen(\Input::post('password'));
        if ($pwlength == 0) {
            $this->failed();
            $this->error = [
                E::INVALID_PARAM,
                'パスワードを入力してください'
            ];
            return;
        }
        //        if ($pwlength < 8 || $pwlength > 16) {
        //            $this->failed();
        //            $this->error = [
        //                E::INVALID_PARAM,
        //                'パスワードは8文字以上16文字以内で入力してください'
        //            ];
        //            return;
        //        }
        if (!preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{6,16}+\z/i', \Input::post('password'))) {
            // 英数字ではない場合
            $this->failed();
            $this->error = [
                E::INVALID_PARAM,
                'パスワードは6文字以上16文字以内、英数混在で入力してください'
            ];
            return;
        }

        $password = \Auth::hash_password(\Input::post('password'));

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
                $user->delete_hash($data['id']);
                $this->success();
            }
        } catch (\Exception $e) {
            $this->failed();
            \Log::error($e->getMessage());
            $this->error = [
                E::SERVER_ERROR,
                '更新に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
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
            if ($user->group_id != 2) {
                $this->failed();
                $this->error = [
                    E::UNAUTHNTICATED,
                    '権限がありません'
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
                '削除に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    public function get_user_list()
    {

        try {
            $list = array();
            $users = \Auth_User::find('all');
            foreach ($users as $user) {
                $user->to_array();
                $tmp = array();
                $tmp['id'] = $user['id'];
                $tmp['nickname'] = $user['nickname'];
                $tmp['email'] = $user['email'];
                $tmp['group_id'] = $user['group_id'];
                $tmp['age'] = $user['age'] ? $user['age'] : '';
                $tmp['sex'] = $user['sex'] ? $user['sex'] : '';
                $tmp['last_login'] = $user['last_login'] == '0' ? $user['last_login'] : date("Y-m-d H:i:s", $user['last_login']);
                $tmp['previous_login'] = $user['previous_login'] == '0' ? $user['previous_login'] : date("Y-m-d H:i:s", $user['previous_login']);
                $tmp['created_at'] = date("Y-m-d H:i:s", $user['created_at']);
                $tmp['updated_at'] = $user['updated_at'] == '0' ? $user['updated_at'] : date("Y-m-d H:i:s", $user['updated_at']);
                $list[] = $tmp;
            }

            $this->data = $list;
        } catch (\Exception $e) {
            $this->failed();
            \Log::error($e->getMessage());
            $this->error = [
                E::SERVER_ERROR,
                'ユーザ情報の取得に失敗しました'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    public function post_export()
    {
        try {
            $sql = <<<h
SELECT 
	users.id as user_id,
	users.username as user_username,
	users.email as user_email,
	users.created_at as user_created_at
FROM
	users
h;

            $data = \DB::query($sql)->execute();
            if (count($data) === 0) {
                $this->error = [
                    E::NOT_FOUND,
                    '情報が存在しません。'
                ];
            }
            foreach ($data as $value) {
                $data_list[] = [
                    'ID' => $value['user_id'],
                    'ユーザ名' => $value['user_username'],
                    'メールアドレス' => $value['user_email'],
                    '登録日' => date('Y/m/d H:i:s', $value['user_created_at'])
                ];
            }

            $head = [
                'ID',
                'ユーザ名',
                'メールアドレス',
                '登録日'
            ];

            // 書き込み用ファイル開く
            $csv_file_path = 'userss_list' . time() . rand() . '.csv';
            $f = fopen($csv_file_path, 'w');
            if ($f) {
                // カラムの書き込み
                mb_convert_variables('SJIS-win', 'UTF-8', $head);
                fputcsv($f, $head);

                // データ書き込み
                foreach ($data_list as $data) {
                    mb_convert_variables('SJIS-win', 'UTF-8', $data);
                    mb_convert_encoding("髙﨑纊①㈱㌔ｱｲｳｴｵあいうえおabc", "UTF-8", "SJIS-win");
                    fputcsv($f, $data);
                }
            }
            // ファイルを閉じる
            fclose($f);

            $filename = "users_list.csv";
            //ホワイトスペース相当の文字をアンダースコアに
            $filename = preg_replace('/\\s/u', '_', $filename);
            //ファイル名に使えない文字をアンダースコアに
            $filename = str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '_', $filename);
            header('Content-Encoding: UTF-8');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            readfile($csv_file_path);
            unlink($csv_file_path);
            exit();
        } catch (\Exception $e) {
            $this->failed();
            $this->error = [
                E::SERVER_ERROR,
                'CSVエクスポート処理に失敗しました。'
            ];
            $this->body['errorlog'] = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    public function get_test()
    {
        $reissue_url = '/getri';
        if (substr($reissue_url, 0, 1) == '/') {
            echo 'hello';
        }
        //        $tmp = ltrim($reissue_url,'/');
        //        var_dump($tmp);
        //        if (substr($reissue_url, 0 )){
        //            echo 'hello';
        //            $reissue_url = ltrim($reissue_url, '/');
        //        }
    }
}
