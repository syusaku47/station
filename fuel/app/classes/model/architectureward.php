<?php

class Model_Architectureward extends Model_Base
{
  protected static $_table_name = 'architecture_wards';

  protected static $_properties = [
    'id',
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

}