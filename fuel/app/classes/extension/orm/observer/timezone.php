<?php

namespace Orm;

class Observer_Timezone extends Observer
{

	public static $property = [];
	protected $_property;

	public function __construct($class)
	{
		$props = $class::observers(get_class($this));
		$this->_property = isset($props['property']) ? $props['property'] : static::$property;
	}

	public function after_load(Model $model)
	{
		$tz = 'UTC';
		if (class_exists('Client'))
		{
			$tz = \Client::timezone();
		}

		foreach ($this->_property as $p)
		{
			$date = new \DateTime($model[$p]);
			$date->setTimezone(new \DateTimeZone($tz));
			$model[$p] = $date->format('Y-m-d H:i:s');
		}
	}

}
