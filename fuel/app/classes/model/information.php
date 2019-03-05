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

  public static function search($input, $to_array = false)
  {

    $limit = min((int) @$input['limit'] ?: 50, 10000);
    $offset = ((is_numeric(@$input['p']) ? $input['p'] : 1) - 1) * $limit;
    $list = self::query()->where('is_private', 0)->order_by('date', 'desc')
      ->rows_limit($limit)
      ->rows_offset($offset)
      ->get();
    $count = \DB::count_last_query();

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

}
