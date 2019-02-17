<?php

class Model_Site extends Model_Base
{
  protected static $_table_name = 'sites';

  protected static $_properties = [
    'id',
    'name',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_has_many = array(
    'facility' => array(
      'key_from' => 'id',
      'model_to' => 'Model_Facility',
      'key_to' => 'site_id',
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
