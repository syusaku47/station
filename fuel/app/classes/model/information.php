<?php

class Model_Information extends Model_Base
{
  //use Model_Base_Plugin_Search;

  protected static $_table_name = 'informations';

  protected static $_properties = [
    'id',
    'title',
    'date',
    'body',
    'is_private',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_to_array_exclude = [
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  public static function searchFilter()
  {
    // $list = self::query()
    //   // ->where('title', 'test')
    //   ->select('title', 'date', 'body')
    //   // ->select('date', 'title', 'body')
    //   ->where('date', '>', '2019-03-28 18:56:21')
    //   ->where('date', '<', '2021-03-11 19:11:40')
    //   ->order_by('date', 'desc')
    //   ->get();

    //クエリ取得
    // $list = DB::query('SELECT * FROM `informations`')->execute();

    //上記と同じ
    // $list = DB::query('SELECT * FROM `informations`', DB::SELECT)->execute();

    //id,titleのみ取得
    // $list = DB::select('id', 'title')->from('informations')->execute();

    // 配列でキーを渡してid,title取得
    // $columns = array('id', 'title');
    // $list = DB::select_array($columns)->from('informations')->execute();

    // titleカラムをisTitleで表示
    // $list = DB::select(array('title', 'isTitle'))->from('informations')->execute();
    // $list = DB::select('id', array('title', 'isTitle'))->from('informations')->distinct(true)->execute();

    // distinct（被りをなくする）はselectの第一引数のみ適用？空とtrueは重複をなくする。falseは機能しない
    // $list = DB::select(array('title', 'isTitle'))->from('informations')->distinct()->execute();
    // データ数（int）が返ってくる
    // $result = DB::select('id', 'title')->from('informations')->execute();
    // $num_rows = count($result);
    // \Log::debug($num_rows);
    // $list = $result;

    // id 1,2,3を取得する
    // $id_array = array(1, 2,3);
    // $list = DB::select()->from('informations')->where('id', 'in', $id_array)->execute();

    // id 1〜5までを取得する
    // $id_array = array(1, 5);
    // $list = DB::select()->from('informations')->where('id', 'between', $id_array)->execute();

    // id=1以外を取得
    // $list = DB::select()->from('informations')->where('id', '!=', 1)->execute();

    // testが先頭に含まれているもの取得
    // $list = DB::select()->from('informations')->where('title', 'like', 'test%')->execute();

    //データ2個取得
    // $list = DB::select()->from('informations')->limit(2)->execute();

    // データ2〜12まで取得 ※limitがないとoffsetは使用できない
    // $list = DB::select()->from('informations')->limit(10)->offset(2)->execute();

    // 60より大きい"id"を"update"更新 $listには更新したレコード数が返ってくる
    // $list = DB::update('informations')->value('title', 'update')->where('id', '>', '60')->execute();

    // 複数のカラム更新 $listには更新したレコード数が返ってくる
    // $list = DB::update('informations')
    //   ->set(array(
    //     'title' => 'Title',
    //     'body' => 'Body'
    //   ))->where('id', '>', '60')->execute();

    // レコード作成(すべてのカラムに入れないとエラー)
    // $num_record = DB::insert('informations')
    //   ->set(array(
    //     'title' => 'Title',
    //     'date' => '2021-03-04 16:59:14',
    //     'body' => 'Body',
    //     'is_private' => 0,
    //     'created_at' => date("Y/m/d H:i:s"),
    //     'updated_at' => date("Y/m/d H:i:s"),
    //     'deleted_at' => NULL

    //   ))
    //   ->execute();

    // DELETE
    // $list = DB::delete('informations')->where('title', 'like', 'お知らせ%')->execute();
    $list = DB::select()->from('informations')->execute();

    return (object)['list' => $list];
  }



  public static function search($input, $to_array = false)
  {

    $limit = min((int) @$input['limit'] ?: 50, 10000);
    $offset = ((is_numeric(@$input['p']) ? $input['p'] : 1) - 1) * $limit;

    $list = self::query()->where('is_private', 0)->order_by('date', 'asc')
      ->rows_limit($limit)
      ->rows_offset($offset)
      ->get();
    $count = \DB::count_last_query();
    \Log::debug($count);
    if ($to_array) {
      // $_to_array_exclude適用
      $list = array_map(function ($model) {
        return $model->to_array();
      }, $list);
    }

    return (object) [
      'count' => $count,
      'limit' => $limit,
      'offset' => $offset,
      'from' => $count ? $offset + 1 : 0,
      'to' => min($offset + $limit, $count),
      'list' => $list
    ];
  }

  // public static function search($input, $to_array = false)
  // {

  //   $limit = min((int) @$input['limit'] ?: 50, 10000);
  //   $offset = ((is_numeric(@$input['p']) ? $input['p'] : 1) - 1) * $limit;
  //   $list = self::query()->where('is_private', 0)->order_by('date', 'desc')
  //     ->rows_limit($limit)
  //     ->rows_offset($offset)
  //     ->get();
  //   $count = \DB::count_last_query();

  //   if ($to_array) {
  //     // $_to_array_exclude適用
  //     $list = array_map(function ($model) {
  //       return $model->to_array();
  //     }, $list);
  //   }

  //   return (object) [
  //     'count' => $count,
  //     'limit' => $limit,
  //     'offset' => $offset,
  //     'from' => $count ? $offset + 1 : 0,
  //     'to' => min($offset + $limit, $count),
  //     'list' => $list
  //   ];
  // }

}
