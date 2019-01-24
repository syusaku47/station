<?php

namespace Orm;

class Observer_Uuid extends Observer
{
    public function before_insert(Model $model)
    {
        if (empty($model->id)) {
            $model->id = \Str::random('uuid');
        }
    }
}
