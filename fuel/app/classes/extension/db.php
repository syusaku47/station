<?php

class DB extends Fuel\Core\DB
{

	public static function transaction($func, $args = null)
	{
		try {
			parent::start_transaction();
			
			$result = call_user_func_array($func, [
				$args
			]);
			
			if ($result === false) {
				throw new IntentionalException();
			}
			
			parent::commit_transaction();
			return $result ?: true;
		} catch (\IntentionalException $e) {
			// 意図的なエラー
			parent::rollback_transaction();
			return false;
		} catch (\HttpNotFoundException $e) {
			// リソースない場合（意図的）
			parent::rollback_transaction();
			throw $e;
		} catch (\Exception $e) {
			// 想定外のエラー
			parent::rollback_transaction();
			Log::error($e);
			throw $e;
		}
	}
}

class IntentionalException extends Exception
{
}