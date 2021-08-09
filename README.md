```php
use quickwx\wxTool;

$component_appid = ''; //第三方平台app_id
$component_secret = ''; //第三方平台的APPSECRET
$token = ''; //消息校验Token
$encodingAesKey = ''; //消息加解密Key

$wx = new wxTool($component_appid,$component_secret,$token,$encodingAesKey);


// 微信开放平台推送到第三方平台的消息

$msgSignature = ''; //消息签名
$timestamp = ''; //随机时间戳
$nonce = '';  //随机字符串
$postXml = ''; //报文


// 解密消息
$dec = $wx->decryptMsg($msgSignature,$timestamp,$nonce,$postXml,$msg);
if($dec == 0){
    echo $msg;
}


//获取令牌
$ret = $wx->component_token($component_verify_ticket);
echo $ret;


//快速创建小程序



```
