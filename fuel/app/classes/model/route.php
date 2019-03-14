<?php

class Model_Route extends Model_Base
{
  protected static $_table_name = 'routes';

  protected static $_properties = [
    'id',
    'code',
    'name',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_has_many = array(
    'station' => array(
      'key_from' => 'id',
      'model_to' => 'Model_Station',
      'key_to' => 'route_id',
      'cascade_save' => true,
      'cascade_delete' => false,
    )
  );

  protected static $_to_array_exclude = [
    'created_at',
    'updated_at',
    'deleted_at',
  ];

}
