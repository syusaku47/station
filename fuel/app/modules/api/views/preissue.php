<?php

echo '下記URLにアクセスし、パスワードを再設定してください。';
echo "\n";
echo "\n";
echo $reissue_url.$user->hash;
echo "\n";

echo "※有効期限は本メール到着後、24時間です。";
