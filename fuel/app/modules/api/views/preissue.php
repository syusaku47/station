<?php

echo '下記URLにアクセスし、パスワードを再設定してください。';
echo PHP_EOL;
echo PHP_EOL;
echo $reissue_url.$user->hash;
echo PHP_EOL;

echo "※有効期限は本メール到着後、24時間です。";
