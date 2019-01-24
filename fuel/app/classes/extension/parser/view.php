<?php

class View extends \Parser\View
{

	// tpl名ほしいので保持する
	public $name;

	public static function forge($file = null, $data = null, $data_safe = null, $auto_encode = null)
	{
		if ($file == 'errors' . DS . 'production') { // productionのエラーの場合のテンプレートはスルー
			return parent::forge($file);
		}
		
		$view = parent::forge($file . '.tpl', $data, $auto_encode);
		if ($data) {
			if (is_array($data)) {
				$view->set($data);
			} else {
				$view->set([
					'data' => $data
				]);
			}
		}
		if ($data_safe) {
			if (is_array($data_safe)) {
				$view->set_safe($data_safe);
			} else {
				$view->set_safe([
					'data' => $data_safe
				]);
			}
		}
		
		// Presenterがあれば生成
		$presenter = array_reduce(explode('/', $file), function ($carry, $item) {
			return $carry . '_' . ucfirst($item);
		}, 'Presenter');
		if (class_exists($presenter)) {
			$view = Presenter::forge($file . '.tpl', 'view', null, $view);
		}
		
		$view->name = $file;
		
		return $view;
	}
}
