<?php

class Pagination extends Fuel\Core\Pagination
{

	public static function init($url, $data, $count, $limit, $config = [])
	{
		return \Pagination::forge('aa', [
			'total_items' => $count,
			'per_page' => $limit,
			'uri_segment' => 'p',
			'pagination_url' => $url . '?' . http_build_query($data),
			'num_links' => 8,
			'show_first' => true,
			'show_last' => true,
			'name' => 'bootstrap3'
			// 'current_page' => is_numeric(@$d[$segment]) ? $d[$segment] : 1,
		] + $config);
	}

	public function __get($name)
	{
		switch ($name) {
			case 'count':
				return $this->config['total_items'];
			case 'from':
				return ($this->config['calculated_page'] - 1) * $this->config['per_page'] + 1;
			case 'to':
				return min($this->config['total_items'], $this->config['calculated_page'] * $this->config['per_page']);
			default:
				return parent::__get($name);
		}
	}
}
