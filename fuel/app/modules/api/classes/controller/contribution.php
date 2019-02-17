<?php

namespace Api;

class Controller_Contribution extends Controller_Base
{
  public function get_site_list()
  {
    try {
      $tmp = \DB::query('select id, name from sites')->execute();
      $this->data = $tmp;
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        $e->getMessage()
      ];
    }
  }

  public function get_facility_list()
  {
    try {
      $site_id = \Input::get('site_id');
      $query = \DB::query('select id, name from facilities where site_id = :site_id');
      $query->bind('site_id', $site_id);
      $this->data = $query->execute();
      $this->success();
    } catch (\Exception $e) {
      $this->failed();
      $this->error = [
        E::SERVER_ERROR,
        $e->getMessage()
      ];
    }
  }
}
