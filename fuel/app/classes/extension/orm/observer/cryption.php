<?php
namespace Orm;

/**
 * （未使用）
 */
class Observer_Cryption extends Observer
{

	public function before_save(Model $model)
	{
		if ($model->encrypted) {
			$model->encrypted = openssl_encrypt($model->encrypted, 'aes-256-ecb', \Config::get('AES_Key'));
		}
	}

	public function after_load(Model $model)
	{
		if ($model->encrypted) {
			$model->encrypted = openssl_decrypt($model->encrypted, 'aes-256-ecb', \Config::get('AES_Key'));
		}
	}
}
