<?php
namespace Orm;

class Observer_File extends Observer
{

	public function before_delete(Model $model)
	{
		if ($model->encrypted) {
			return;
		}
		
		if (property_exists($model, 'strage')) {
			$model->strage->delete($model->saved_to . $model->saved_as);
		} else {
			if (is_file($path = $model->saved_to . $model->saved_as)) {
				\File::delete($path);
			}
			
			if (is_dir($path = $model->saved_to)) {
				$iterator = new \GlobIterator($model->saved_to . '*');
				if ($iterator->count() == 0) {
					\File::delete_dir($path);
				}
			}
		}
	}
}
