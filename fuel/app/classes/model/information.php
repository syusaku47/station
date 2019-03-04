<?php

class Model_Information extends Model_Base
{
  use Model_Base_Plugin_Search;

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

}
