<?php

class Model_Industry extends Model_Base
{
  protected static $_connection  = 'lisb';
  protected static $_table_name = 'industry';

  protected static $_properties = [
    'id',
    'industry_id',
    'branch_id',
    'name',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_to_array_exclude = [
	'created_at',
	'updated_at',
	'deleted_at',
	];

  public static function get_by_industry_id($industry_id)
  {
    $results = self::query()->where('industry_id', $industry_id)->get();
    if(!$results){
      return false;
    }

    $resultarray = array();

    foreach($results as $result){
      $resultsarray[] = $result->to_array();
    }

    return $resultsarray;
  }
}
