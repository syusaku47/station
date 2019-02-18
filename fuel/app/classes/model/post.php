<?php

class Model_Post extends Model_Base
{
  protected static $_table_name = 'posts';

  protected static $_properties = [
    'id',
    'parent_id',
    'contributor_id',
    'route_id',
    'station_id',
    'status',
    'site_id',
    'facility_id',
    'overview',
    'remarks',
    'thumbnail_before',
    'thumbnail_after',
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
