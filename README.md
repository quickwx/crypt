```php
use quickwx\wxTool;

$appid = '';
$token = '';
$aeskey = '';

$wx = new wxTool($token,$aeskey,$appid);


// 微信开放平台推送到第三方平台的消息

$msgSignature = '';
$timestamp = '';
$nonce = '';
$postData = '';


// 解密消息
$dec = $wx->decryptMsg($msgSignature,$timestamp,$nonce,$postData,$plaintext);
if($dec == 0){
    echo $plaintext;
}
```
