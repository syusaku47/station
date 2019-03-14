<?php

class Model_Station extends Model_Base
{
  protected static $_table_name = 'stations';

  protected static $_properties = [
    'id',
    'route_id',
    'name',
    'latitude',
    'longitude',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_belongs_to = array(
    'route' => array(
      'key_from' => 'route_id',
      'model_to' => 'Model_Route',
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

  public static function get_closest_station($lat, $long){
    $query = \DB::query('SELECT r.id as route_id, r.name as route_name, s.id as station_id, s.name as station_name  FROM stations s inner join routes r on s.route_id = r.id order by abs((latitude - :lat)) + abs((longitude - :long))');
    $query->bind('lat', $lat);
    $query->bind('long', $long);
    return $query->execute();
  }
}
