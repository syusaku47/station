<?php

class Model_Repairer extends Model_Base
{
  protected static $_table_name = 'repairers';

  protected static $_properties = [
    'id',
    'name',
    'email',
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
