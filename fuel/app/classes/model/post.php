<?php

class Model_Post extends Model_Base
{
  protected static $_table_name = 'posts';

  protected static $_properties = [
    'id',
    'parent_id',
    'contributor_id',
    'child_id',
    'route_id',
    'station_id',
    'status',
    'site_id',
    'site_text',
    'facility_id',
    'facility_text',
    'overview',
    'remarks',
    'repairer_id',
    'thumbnail_before',
    'thumbnail_after1',
    'thumbnail_after2',
    'thumbnail_after3',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_to_array_exclude = [
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  public static function get_contribution_history($contributor_id)
  {
    $query = \DB::query('select p.id as id,p.parent_id as parent_id,p.contributor_id as contributor_id, um.value as nickname, p.route_id as route_id, r.name as route_name, p.station_id as station_id,s.name as station_name,p.status as status,p.site_id as site_id,
site.name as site_name,p.site_text as site_text,p.facility_id as facility_id, f.name as facility_name, p.facility_text as facility_text,p.overview as overview,p.remarks as remarks,p.repairer_id as repairer_id, rp.name as repairer_name,p.thumbnail_before as thumbnail_before,p.thumbnail_after1 as thumbnail_after1,p.thumbnail_after2 as thumbnail_after2,p.thumbnail_after3 as thumbnail_after3,p.created_at as created_at,p.updated_at as updated_at 
from posts p inner join routes r on p.route_id = r.id  inner join  stations s on p.station_id = s.id
  inner join sites site on p.site_id = site.id  inner join facilities f on p.facility_id = f.id inner join users_metadata um on p.contributor_id = um.parent_id inner join repairers rp on p.repairer_id = rp.id where um.key = \'nickname\' and contributor_id = :contributor_id '
    );
    $query->bind('contributor_id', $contributor_id);
    return $query->execute();
  }

  public static function get_contribution_by_id($contribution_id)
  {
    $query = \DB::query('select p.id as id,p.parent_id as parent_id,p.contributor_id as contributor_id, um.value as nickname, p.route_id as route_id, r.name as route_name, p.station_id as station_id,s.name as station_name,p.status as status,p.site_id as site_id,
site.name as site_name,p.site_text as site_text,p.facility_id as facility_id, f.name as facility_name, p.facility_text as facility_text,p.overview as overview,p.remarks as remarks,p.repairer_id as repairer_id, rp.name as repairer_name,p.thumbnail_before as thumbnail_before,p.thumbnail_after1 as thumbnail_after1,p.thumbnail_after2 as thumbnail_after2,p.thumbnail_after3 as thumbnail_after3,p.created_at as created_at,p.updated_at as updated_at 
from posts p inner join routes r on p.route_id = r.id  inner join  stations s on p.station_id = s.id
  inner join sites site on p.site_id = site.id  inner join facilities f on p.facility_id = f.id inner join users_metadata um on p.contributor_id = um.parent_id inner join repairers rp on p.repairer_id = rp.id where um.key = \'nickname\' and p.id = :contribution_id '
    );
    $query->bind('contribution_id', $contribution_id);
    return $query->execute();
  }

  public static function get_other_contributes($status, $station_id)
  {
    $query = \DB::query('select p.id as id,p.parent_id as parent_id,p.contributor_id as contributor_id, um.value as nickname, p.route_id as route_id, r.name as route_name, p.station_id as station_id,s.name as station_name,p.status as status,p.site_id as site_id,
site.name as site_name,p.site_text as site_text,p.facility_id as facility_id, f.name as facility_name, p.facility_text as facility_text,p.overview as overview,p.remarks as remarks,p.repairer_id as repairer_id, rp.name as repairer_name,p.thumbnail_before as thumbnail_before,p.thumbnail_after1 as thumbnail_after1,p.thumbnail_after2 as thumbnail_after2,p.thumbnail_after3 as thumbnail_after3,p.created_at as created_at,p.updated_at as updated_at 
from posts p inner join routes r on p.route_id = r.id  inner join  stations s on p.station_id = s.id
  inner join sites site on p.site_id = site.id  inner join facilities f on p.facility_id = f.id inner join users_metadata um on p.contributor_id = um.parent_id inner join repairers rp on p.repairer_id = rp.id where um.key = \'nickname\' and p.status = :status and p.station_id = :station_id order by p.updated_at'
    );
    $query->bind('status', $status);
    $query->bind('station_id', $station_id);
    return $query->execute();
  }

  public static function numbering_child_id($parent_id){
    $query = \DB::select(\DB::expr('MAX(`child_id`) + 1 as next_id'))->from('posts')->where('parent_id', $parent_id);
    $result = $query->execute();
    \Log::error('next_id : '.$result[0]['next_id']);
    //\Log::error(var_dump($result));
    return $result[0]['next_id'];


  }
}
