<?php

trait Model_Base_Plugin_Search
{

  /*
   * Sample
   *
   * public static $sorts = [
   * 'anything' => [
   * 'value' => ['column name' => 'asc or desc'],
   * 'label' => 'ラベル'
   * ]
   * ];
   *
   * protected static $default_sort = [
   * 'column name' => 'asc or desc'
   * ];
   */
  public static function search($input, $to_array = false)
  {
    // 各ModelでQueryを生成
    $query = static::get_query($input);
    $count = $query->count();

    // 並び替え
    if (@$input['sort']) {
      if (property_exists(get_called_class(), 'sorts')) {
        if (is_array($input['sort'])) {
          $query = $query->order_by($input['sort']);
        } else {
          if (array_key_exists($input['sort'], static::$sorts)) {
            $query = $query->order_by(static::$sorts[$input['sort']]['value'], static::$is_descendings[$input['is_descending']]['value']);
          }
        }
      }
    }
    if (property_exists(get_called_class(), 'default_sort')) {
      $query = $query->order_by(static::$default_sort);
    }

    $limit = min((int) @$input['limit'] ?: 50, 10000);
    $offset = ((is_numeric(@$input['p']) ? $input['p'] : 1) - 1) * $limit;

    $list = $query->rows_limit($limit)
      ->rows_offset($offset)
      ->get();

    //\Log::error($query->get_query()->__toString(), true);

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
      'list' => array_values($list)
    ];
  }

  public static function get_query($d)
  {
    return static::query();
  }

  // TODO 使う時に再検討
  public static function search_manually($d, $path = '/')
  {
    list ($qc, $q) = self::get_manual_query($d);

    $result['count'] = $count = count($qc->execute());
    $result['pagination'] = $page = \Pagination::get($path, $count, $d);

    $q = DB::query((string) $q . ' limit ' . $page->per_page . ' offset ' . $page->offset);

    $result[self::$_table_name] = $q->as_object(get_called_class())->execute();

    // $res = $q->execute()->as_array();
    // Log::debug(DB::last_query());
    return $result;
  }

  public static function exec($sql, $p = [], $limit = 10000, $offset = 0)
  {
    $result = DB::query($sql . ' limit ' . $limit . ' offset ' . $offset)->parameters($p)
      ->
      // ->as_object()
      execute()
      ->as_array();
    // Log::debug(DB::last_query());
    return $result;
  }

  public static function get_random($count = 1, $col = null)
  {
    $query = self::query()->order_by(DB::expr('rand()'));
    if ($count == 1) {
      $bean = $query->get_one();
      return $col ? $bean->$col : $bean;
    } else {
      $ret = [];
      foreach ($query->get() as $bean) {
        if ($count == 0) {
          break;
        }
        $ret[] = $col ? $bean->$col : $bean;
        $count --;
      }
      return $ret;
    }
  }
}
