<?php

class Model_Base extends \Orm\Model_Soft
{

	protected static $_observers = [
		'Orm\Observer_Uuid' => [
			'events' => [
				'before_insert'
			]
		],
		'Orm\Observer_CreatedAt' => [
			'events' => [
				'before_insert'
			],
			'mysql_timestamp' => true
		],
		'Orm\Observer_UpdatedAt' => [
			'events' => [
				'before_save'
			],
			'mysql_timestamp' => true
		],
		'Orm\\Observer_Self' => [
			'events' => [
				'after_save',
				'after_insert',
				'after_update'
			]
		]
	];

	protected static $_soft_delete = [
		'deleted_field' => 'deleted_at',
		'mysql_timestamp' => true
	];

	/**
	 * Model_Soft用
	 */
	public static function query($options = array())
	{
		$query = \Query_Soft::forge(get_called_class(), static::connection(), $options);

		if (static::get_filter_status()) {
			$query->set_soft_filter(static::soft_delete_property('deleted_field', static::$_default_field_name));
		}

		return $query;
	}

	public function get_file($type)
	{
		foreach ((array) $this->files as $file) {
			if ($file->type == $type) {
				return $file;
			}
		}
		return null;
	}

	public function delete_files($type = null)
	{
		if ($type) {
			foreach ((array) $this->files as $file) {
				if ($file->type == $type) {
					$file->delete();
					break;
				}
			}
		} else {
			foreach ((array) $this->files as $file) {
				$file->delete();
			}
		}
	}

	public static function init()
	{
		foreach ((array) static::find('all') as $bean) {
			$bean->delete();
		}
	}

	public static function bulk_to_array($list)
	{
		return array_map(function ($model) {
			return $model->to_array();
		}, $list);
	}

	/**
	 * 配列から一括設定する
	 */
	public function patch($data)
	{
		foreach ($data as $key => $value) {
			if (! in_array($key, [
				'id',
				'created_at',
				'updated_at',
				'deleted_at'
			])) {

				$this->{$key} = $value;
			}
		}
	}
}
