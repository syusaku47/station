<?php

class Model_Questionnaire extends Model_Base
{
  protected static $_table_name = 'questionnaires';

  protected static $_properties = [
    'id',
    'user_id',
    'question1',
    'question2',
    'question3',
    'question4',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected static $_to_array_exclude = [
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  public static function csv_export()
  {
      $questionnaires = \Model_Questionnaire::find('all');

      //CSV形式で情報をファイルに出力のための準備
      $csvFileName = '/tmp/' . time() . rand() . '.csv';
      $res = fopen($csvFileName, 'w');
      fwrite($res, "\xEF\xBB\xBF");

      if ($res === FALSE) {
          throw new Exception('ファイルの書き込みに失敗しました。');
      }
      $headerUser = 'ユーザー';
      $headerQuestion1 = '設問1';
      $headerQuestion2 = '設問2';
      $headerQuestion3 = '設問3';
      $headerQuestion4 = '設問4';
      $headerCreate_date = '作成日';

      $header_list = [
          [
              "$headerUser",
              "$headerQuestion1",
              "$headerQuestion2",
              "$headerQuestion3",
              "$headerQuestion4",
              "$headerCreate_date",
          ]
      ];

      foreach ($header_list as $headerinfo){
          fputcsv($res,$headerinfo);
      }
      foreach ($questionnaires as $questionnaire) {
          $tmp = $questionnaire;
//          $tmp = $questionnaire->to_array();
//          $questionnaire = $tmp;

          $user = \Auth_User::find($tmp['user_id']);
          $dataUser_name = $user->username;
          $dataQuestion1 = $tmp['question1'];
          $dataQuestion2 = $tmp['question2'];
          $dataQuestion3 = $tmp['question3'];
          $dataQuestion4 = $tmp['question4'];
          $dataCreate_date = $tmp['created_at'];

          $dataList = [
              [
                  "$dataUser_name",
                  "$dataQuestion1",
                  "$dataQuestion2",
                  "$dataQuestion3",
                  "$dataQuestion4",
                  "$dataCreate_date",

              ]
          ];

          foreach ($dataList as $dataInfo) {
              mb_convert_variables('UTF-8', 'UTF-8', $dataInfo);
              fputcsv($res, $dataInfo);
          }

      }
      fclose($res);

      header('Content-Type: text/csv');
      // ここで渡されるファイルがダウンロード時のファイル名になる
      header('Content-Disposition: attachment; filename=ExportCsv.csv');
      //echo 'test';//lettersリストの出力処理
      readfile($csvFileName);
      exit;
  }

}
