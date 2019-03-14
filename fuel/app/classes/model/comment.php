<?php

class Model_Comment extends Model_Base
{
  protected static $_table_name = 'comments';

  protected static $_properties = [
    'id',
    'type',
    'comment',
  ];

}
