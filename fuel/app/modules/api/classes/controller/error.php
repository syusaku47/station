<?php
namespace Api;

class Controller_Error extends Controller_Base
{
  public function post_error()
  {
    switch(\Input::post('errorcode')){
      case '4010':
        $this->error = [
  				E::UNAUTHNTICATED,
  				'IDまたはパスワードが違います'
  			];
        break;

      case '4011':
        $this->error = [
  				E::INVALID_TOKEN,
  				'トークンが不正です'
  			];
        break;

      case '4030':
        $this->error = [
  				E::FORBIDDEN,
  				'権限がありません'
  			];
        break;

      case '4000':
        $this->error = [
  				E::INVALID_REQUEST,
  				'不正なリクエストです'
  			];
        break;

      case '4001':
        $this->error = [
  				E::INVALID_PARAM,
  				'不正なパラメータです'
  			];
        break;

      case '4002':
        $this->error = [
  				E::INVALID_CSRF_TOKEN,
  				'不正なCSRFトークンです'
  			];
        break;

      case '4040':
        $this->error = [
  				E::NOT_FOUND,
  				'リソースが見つかりません'
  			];
        break;

      case '4090':
        $this->error = [
  				E::CONFRICT,
  				'リクエストが競合しています'
  			];
        break;

      case '5000':
        $this->error = [
  				E::SERVER_ERROR,
  				'サーバーエラーです'
  			];
        break;

      default:
        $this->data = 'OK';
        break;
    }
  }
}
