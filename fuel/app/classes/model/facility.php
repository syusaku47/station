<?php

class Model_Facility extends Model_Base
{
  protected static $_table_name = 'facilities';

  protected static $_properties = [
    'id',
    'site_id',
    'name',
    'disp_flag',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_belongs_to = array(
    'site' => array(
      'key_from' => 'site_id',
      'model_to' => 'Model_Site',
      'key_to' => 'id',
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
